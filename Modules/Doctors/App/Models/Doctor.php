<?php

namespace Modules\Doctors\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Departments\App\Models\Department;
use Modules\Doctors\App\Models\Specialization;
use Modules\Auth\App\Models\User;

class Doctor extends Model
{
    protected $fillable = ['user_id', 'department_id', 'specialization_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }
}
