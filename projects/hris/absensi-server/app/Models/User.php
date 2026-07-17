<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'employee_id',
        'email',
        'phone',
        'title',
        'unit_name',
        'division_name',
        'office_type',
        'branch_code',
        'branch_name',
        'is_admin',
        'password',
        'pin',
        'office_id',
        'position_id',
        'last_login_at',
        'birth_date',
        'gender',
        'employment_status',
        'join_date'
    ];

    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function additionalOffices()
    {
        return $this->belongsToMany(Office::class, 'office_user');
    }

    public function getAllOfficesAttribute()
    {
        $offices = collect();
        if ($this->office) {
            $offices->push($this->office);
        }
        return $offices->merge($this->additionalOffices);
    }

    public function mutations()
    {
        return $this->hasMany(Mutation::class);
    }

    public function warnings()
    {
        return $this->hasMany(Warning::class);
    }

    public function files()
    {
        return $this->hasMany(UserFile::class);
    }

    public function profile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function employment()
    {
        return $this->hasOne(EmploymentDetail::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function approverSettings()
    {
        return $this->hasMany(OfficeApprover::class, 'approver_id');
    }

    public function directorSettings()
    {
        return $this->hasMany(OfficeApprover::class, 'director_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function permitRequests()
    {
        return $this->hasMany(PermitRequest::class);
    }

    public function overtimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function outsideDutyRequests()
    {
        return $this->hasMany(OutsideDutyRequest::class);
    }

    public function histories()
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    protected $appends = ['photo_profile', 'approver', 'is_approver'];

    public function getIsApproverAttribute()
    {
        return $this->approverSettings()->exists() || $this->directorSettings()->exists();
    }

    public function getPhotoProfileAttribute()
    {
        $file = $this->files()->where('file_type', 'PHOTO')->first();
        if ($file) {
            return url('storage/' . $file->file_path);
        }
        return null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'birth_date' => 'date:Y-m-d',
            'join_date' => 'date:Y-m-d',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the current approver for the user based on office settings.
     */
    public function getApproverAttribute()
    {
        $approver = \App\Models\OfficeApprover::where('office_id', $this->office_id)->first();
        if ($approver) {
            // If the user is the approver, return the director.
            $user = $approver->approver_id == $this->id ? $approver->director : $approver->approver;
            return $user ? $user->name : null;
        }
        return null;
    }


}
