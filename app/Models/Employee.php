<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'sort_id','user_id','emp_id','salutation','name','gender','dob','doj',
        'designation','department','office','status','grade',
        'supervisor','manager','approver',
        'email','mobile','pan','aadhar','uan','lic_id','is_active',
    ];

    protected $casts = [
        'dob' => 'date',
        'doj' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // note: FK column names match your schema
    public function designationRef()
    {
        return $this->belongsTo(\App\Models\Designation::class, 'designation');
    }
    public function departmentRef()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department');
    }
    public function officeRef()
    {
        return $this->belongsTo(\App\Models\Office::class, 'office');
    }
}
