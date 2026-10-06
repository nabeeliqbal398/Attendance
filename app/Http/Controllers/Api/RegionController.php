<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    /**
     * Get list of provinces (code length 2).
     */
    public function provinces(Request $request)
    {
        $search = $request->input('search');

        $query = Region::whereRaw('LENGTH(code) = 2');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Get list of districts (code length 5) by province code.
     */
    public function districts(Request $request, $provinceCode)
    {
        $search = $request->input('search');

        $query = Region::where('code', 'like', "{$provinceCode}.%")
            ->whereRaw('LENGTH(code) = 5');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Get list of sub-districts (code length 8) by district code.
     */
    public function subdistricts(Request $request, $districtCode)
    {
        $search = $request->input('search');

        $query = Region::where('code', 'like', "{$districtCode}.%")
            ->whereRaw('LENGTH(code) = 8');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Get list of villages (code length 13) by sub-district code.
     */
    public function villages(Request $request, $subdistrictCode)
    {
        $search = $request->input('search');

        $query = Region::where('code', 'like', "{$subdistrictCode}.%")
            ->whereRaw('LENGTH(code) = 13');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('name')->get());
    }
}
