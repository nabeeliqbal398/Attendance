<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.attendances.index');
    }

    public function report(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'month' => 'nullable|date_format:Y-m',
            'week' => 'nullable',
            'startDate' => 'nullable|date_format:Y-m-d',
            'endDate' => 'nullable|date_format:Y-m-d',
            'division' => 'nullable|exists:divisions,id',
            'job_title' => 'nullable|exists:job_titles,id',
            'jobTitle' => 'nullable|exists:job_titles,id',
            'search' => 'nullable|string|max:255',
        ]);

        if (!$request->date && !$request->month && !$request->week && (!$request->startDate || !$request->endDate)) {
            return redirect()->back();
        }

        $carbon = new Carbon;
        $start = null;
        $end = null;
        $jobTitle = $request->input('job_title') ?: $request->input('jobTitle');

        if ($request->date) {
            $start = $carbon->parse($request->date)->settings(['formatFunction' => 'translatedFormat']);
            $end = $start->copy();
            $dates = [$start];
        } else if ($request->week) {
            $start = $carbon->parse($request->week)->settings(['formatFunction' => 'translatedFormat'])->startOfWeek();
            $end = $carbon->parse($request->week)->settings(['formatFunction' => 'translatedFormat'])->endOfWeek();
            $dates = $start->range($end)->toArray();
        } else if ($request->month) {
            $start = $carbon->parse($request->month)->settings(['formatFunction' => 'translatedFormat'])->startOfMonth();
            $end = $carbon->parse($request->month)->settings(['formatFunction' => 'translatedFormat'])->endOfMonth();
            $dates = $start->range($end)->toArray();
        } else if ($request->startDate && $request->endDate) {
            $start = $carbon->parse($request->startDate)->settings(['formatFunction' => 'translatedFormat']);
            $end = $carbon->parse($request->endDate)->settings(['formatFunction' => 'translatedFormat']);
            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
            }
            $dates = $start->range($end)->toArray();
        }

        $employees = User::where('group', 'user')
            ->when($request->search, function (Builder $q) use ($request) {
                $search = $request->search;
                $q->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nip', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when($request->division, fn (Builder $q) => $q->where('division_id', $request->division))
            ->when($jobTitle, fn (Builder $q) => $q->where('job_title_id', $jobTitle))
            ->with(['division', 'jobTitle'])
            ->get()
            ->map(function ($user) use ($request, $dates, $start, $end) {
                // Determine Range for Cache Key
                if ($request->date) {
                   $rangeKey = $request->date;
                   $qStart = $request->date;
                   $qEnd = $request->date;
                } elseif ($request->week) {
                   $rangeKey = $request->week;
                   $qStart = Carbon::parse($request->week)->startOfWeek()->toDateString();
                   $qEnd = Carbon::parse($request->week)->endOfWeek()->toDateString();
                } elseif ($request->month) {
                   $rangeKey = $request->month;
                   $qStart = Carbon::parse($request->month)->startOfMonth()->toDateString();
                   $qEnd = Carbon::parse($request->month)->endOfMonth()->toDateString();
                } else {
                   $qStart = $start->toDateString();
                   $qEnd = $end->toDateString();
                   $rangeKey = $qStart . ':' . $qEnd;
                }

                $attendances = new Collection(Cache::remember(
                    "attendance-$user->id-$rangeKey",
                    now()->addMinutes(5),
                    function () use ($user, $qStart, $qEnd) {
                        /** @var Collection<Attendance>  */
                        $attendances = Attendance::with('shift')
                            ->where('user_id', $user->id)
                            ->whereBetween('date', [$qStart, $qEnd])
                            ->get();

                        return $attendances->map(
                            function (Attendance $v) {
                                $v->setAttribute('coordinates', $v->lat_lng);
                                $v->setAttribute('lat', $v->latitude_in);
                                $v->setAttribute('lng', $v->longitude_in);
                                if ($v->attachment) {
                                    $v->setAttribute('attachment', $v->attachment_url);
                                }
                                if ($v->shift) {
                                    $v->setAttribute('shift', $v->shift->name);
                                }
                                return $v->getAttributes();
                            }
                        )->toArray();
                    }
                ) ?? []);
                
                $user->attendances = $attendances;
                return $user;
            });

        $data = [
            'employees' => $employees,
            'dates' => $dates ?? [],
            'date' => $request->date ?: (($start && $end && $start->isSameDay($end)) ? $start->toDateString() : null),
            'month' => $request->month,
            'week' => $request->week,
            'division' => $request->division,
            'jobTitle' => $jobTitle,
            'search' => $request->search,
            'start' => $start,
            'end' => $end
        ];

        if ($request->format === 'excel') {
            $data['isExcel'] = true;
            $filename = 'attendance-report-' . ($start?->format('Y-m-d') ?? now()->format('Y-m-d')) . '-to-' . ($end?->format('Y-m-d') ?? now()->format('Y-m-d')) . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AttendanceExport($data), $filename);
        }

        $pdf = Pdf::loadView('admin.attendances.report', $data)->setPaper('a3', 'landscape');
        
        return $pdf->stream();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }
}
