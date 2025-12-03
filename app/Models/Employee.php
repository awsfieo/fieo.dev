<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use App\Models\Designation;
use App\Models\Department;
use App\Models\Office;

class Employee extends Model
{
    protected $fillable = [
        'sort_id',
        'user_id',
        'employee_code',
        'salutation',
        'name',
        'gender',
        'dob',
        'doj',
        'designation_id',
        'department_id',
        'office_id',
        'status',
        'basic',
        'supervisor_code',
        'manager_code',
        'approver_code',
        'email',
        'mobile',
        'pan',
        'aadhar',
        'uan',
        'lic_id',
        'is_active',
    ];

    protected $casts = [
        'dob' => 'date',
        'doj' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // note: FK column names match your schema
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
    public function supervisor()
    {
        // local column: supervisor_code, related column: employee_code
        return $this->belongsTo(Employee::class, 'supervisor_code', 'employee_code');
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_code', 'employee_code');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_code', 'employee_code');
    }

    public function getDisplayNameAttribute(): string
    {
        $name = trim((string) $this->name);
        $sal  = $this->salutation ? trim((string) $this->salutation) : '';

        return $sal ? ($sal . ' ' . $name) : $name;
    }


    public function getLabelAttribute(): string
    {
        // try common column names, fallback to id
        return $this->attributes['name']
            ?? $this->attributes['title']
            ?? $this->attributes['designation']
            ?? (string) $this->attributes['id'];
    }
}
