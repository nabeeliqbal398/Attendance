<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasUlids;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'nip', 'cnic', 'bank_name', 'bank_account_title', 'bank_account_number', 'iban',
        'name', 'email', 'password', 'group', 'phone', 'gender',
        'birth_date', 'birth_place', 'address', 'province_code', 'district_code',
        'subdistrict_code', 'village_code', 'education_id', 'division_id',
        'job_title_id', 'profile_photo_path', 'language', 'basic_salary',
        'hourly_rate', 'payslip_password', 'payslip_password_set_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password', 'payslip_password', 'remember_token',
        'two_factor_recovery_codes', 'two_factor_secret',
    ];

    protected $appends = ['profile_photo_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'datetime:Y-m-d',
            'password' => 'hashed',
        ];
    }

    public static $groups = ['user', 'admin', 'superadmin'];

    final public function getIsUserAttribute(): bool { return $this->group === 'user'; }
    final public function getIsAdminAttribute(): bool { return $this->group === 'admin' || $this->isSuperadmin; }
    final public function getIsSuperadminAttribute(): bool { return $this->group === 'superadmin'; }
    final public function getIsNotAdminAttribute(): bool { return !$this->isAdmin; }

    final public function getIsDemoAttribute(): bool
    {
        return in_array($this->email, [
            'admin@innovexify.com',
            'user@innovexify.com',
        ]);
    }

    public function education() { return $this->belongsTo(Education::class); }
    public function division() { return $this->belongsTo(Division::class); }
    public function jobTitle() { return $this->belongsTo(JobTitle::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }

    public function getSupervisorAttribute()
    {
        if (!$this->division_id || !$this->job_title_id || !$this->jobTitle || !$this->jobTitle->jobLevel) {
            return null;
        }
        $myRank = $this->jobTitle->jobLevel->rank;
        return User::where('division_id', $this->division_id)
            ->where('id', '!=', $this->id)
            ->whereHas('jobTitle', function ($q) use ($myRank) {
                $q->whereHas('jobLevel', function ($sq) use ($myRank) {
                    $sq->where('rank', '<', $myRank);
                });
            })
            ->with(['jobTitle.jobLevel'])
            ->get()
            ->sortByDesc(fn($u) => $u->jobTitle->jobLevel->rank)
            ->first();
    }

    public function getSubordinatesAttribute()
    {
        if (!$this->division_id || !$this->jobTitle || !$this->jobTitle->jobLevel) {
            return collect();
        }
        $myRank = $this->jobTitle->jobLevel->rank;
        return User::where('division_id', $this->division_id)
            ->whereHas('jobTitle.jobLevel', function ($q) use ($myRank) {
                $q->where('rank', '>', $myRank);
            })
            ->get();
    }

    public function hasValidPayslipPassword(): bool
    {
        if (!$this->payslip_password || !$this->payslip_password_set_at) {
            return false;
        }
        return \Illuminate\Support\Carbon::parse($this->payslip_password_set_at)->diffInMonths(now()) < 3;
    }

    public function faceDescriptor() { return $this->hasOne(FaceDescriptor::class); }
    public function hasFaceRegistered(): bool { return $this->faceDescriptor()->exists(); }
    public function cashAdvances() { return $this->hasMany(CashAdvance::class); }
    public function province()    { return $this->belongsTo(Region::class, 'province_code', 'code'); }
    public function district()    { return $this->belongsTo(Region::class, 'district_code', 'code'); }
    public function subdistrict() { return $this->belongsTo(Region::class, 'subdistrict_code', 'code'); }
    public function village()     { return $this->belongsTo(Region::class, 'village_code', 'code'); }

    public function getProvinceNameAttribute(): ?string
    {
        return $this->province_code ? (config('provinces.' . $this->province_code) ?? $this->province?->name) : null;
    }
}
