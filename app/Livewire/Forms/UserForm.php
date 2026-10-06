<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserForm extends Form
{
    public ?User $user = null;

    public $name = '';
    public $nip = '';
    public $cnic = '';
    public $bank_name = '';
    public $bank_account_title = '';
    public $bank_account_number = '';
    public $iban = '';
    public $email = '';
    public $phone = '';
    public $password = null;
    public $gender = null;
    public $address = '';
    public $province_code = null;
    public $district_code = null;
    public $subdistrict_code = null;
    public $village_code = null;
    public $group = 'user';
    public $birth_date = null;
    public $birth_place = '';
    public $division_id = null;
    public $education_id = null;
    public $job_title_id = null;
    public $photo = null;
    public $basic_salary = 0;
    public $hourly_rate = 0;

    public function rules()
    {
        $requiredOrNullable = $this->group === 'user' ? 'required' : 'nullable';
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'nip' => [$requiredOrNullable, 'string', 'max:255'],
            'cnic' => ['nullable', 'string', 'max:15', 'regex:/^(\d{5}-\d{7}-\d{1}|\d{13})$/'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_title' => ['nullable', 'string', 'max:150'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'iban' => ['nullable', 'string', 'max:34', 'regex:/^[A-Z]{2}[0-9A-Z]{13,32}$/'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user)
            ],
            'phone' => [
                'required', 'string', 'min:7', 'max:20',
                'regex:/^(\+?92\d{10}|0\d{10}|\+?\d{7,15})$/',
            ],
            'password' => ['nullable', 'string', 'min:4', 'max:255'],
            'gender' => [$requiredOrNullable, 'in:male,female'],
            'address' => [$requiredOrNullable, 'string', 'max:255'],
            'province_code' => [$requiredOrNullable, 'string', 'max:13'],
            'district_code' => ['nullable', 'string', 'max:13'],
            'subdistrict_code' => ['nullable', 'string', 'max:13'],
            'village_code' => ['nullable', 'string', 'max:13'],
            'group' => ['nullable', 'string', 'max:255', Rule::in(User::$groups)],
            'birth_date' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'education_id' => ['nullable', 'exists:educations,id'],
            'job_title_id' => ['nullable', 'exists:job_titles,id'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->nip = $user->nip;
        $this->cnic = $user->cnic;
        $this->bank_name = $user->bank_name;
        $this->bank_account_title = $user->bank_account_title;
        $this->bank_account_number = $user->bank_account_number;
        $this->iban = $user->iban;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->gender = $user->gender;
        $this->address = $user->address;
        $this->province_code = $user->province_code;
        $this->district_code = $user->district_code;
        $this->subdistrict_code = $user->subdistrict_code;
        $this->village_code = $user->village_code;
        $this->group = $user->group;
        $this->birth_date = $user->birth_date
            ? \Illuminate\Support\Carbon::parse($user->birth_date)->format('Y-m-d')
            : null;
        $this->birth_place = $user->birth_place;
        $this->division_id = $user->division_id;
        $this->education_id = $user->education_id;
        $this->job_title_id = $user->job_title_id;
        $this->basic_salary = $user->basic_salary;
        $this->hourly_rate = $user->hourly_rate;
        return $this;
    }

    public function store()
    {
        if (!$this->isAllowed()) {
            return abort(403);
        }
        $this->validate();
        $this->sanitize();

        /** @var User $user */
        $user = User::create([
            ...$this->all(),
            'password' => Hash::make($this->password ?? \Illuminate\Support\Str::random(16)),
        ]);
        if (isset($this->photo)) $user->updateProfilePhoto($this->photo);
        $this->reset();
    }

    public function update()
    {
        if (!$this->isAllowed()) {
            return abort(403);
        }

        // Demo User Protection: Cannot update password of Demo User
        if ($this->user->is_demo && $this->password) {
            $this->addError('password', 'Demo user password cannot be changed.');
            return;
        }
        $this->validate();
        $this->sanitize();

        $this->user->update([
            ...$this->all(),
            'password' => $this->password ? Hash::make($this->password) : $this->user?->password,
        ]);
        if (isset($this->photo)) $this->user->updateProfilePhoto($this->photo);
        $this->reset();
    }

    protected function sanitize()
    {
        $this->division_id = $this->division_id ?: null;
        $this->job_title_id = $this->job_title_id ?: null;
        $this->education_id = $this->education_id ?: null;
        $this->province_code = $this->province_code ?: null;
        $this->district_code = $this->district_code ?: null;
        $this->subdistrict_code = $this->subdistrict_code ?: null;
        $this->village_code = $this->village_code ?: null;
        $this->birth_date = $this->birth_date ?: null;
    }

    public function deleteProfilePhoto()
    {
        if (!$this->isAllowed()) {
            return abort(403);
        }
        return $this->user->deleteProfilePhoto();
    }

    public function delete()
    {
        if (!$this->isAllowed()) {
            return abort(403);
        }
        $this->user->delete();
        $this->deleteProfilePhoto();
        $this->reset();
    }

    private function isAllowed()
    {
        // Demo User cannot perform any mutations
        if (Auth::user()->is_demo) {
            return false;
        }

        if ($this->group === 'user') {
            return Auth::user()?->isAdmin;
        }
        return Auth::user()?->isSuperadmin || (Auth::user()?->isAdmin && Auth::user()?->id === $this->user?->id);
    }
}
