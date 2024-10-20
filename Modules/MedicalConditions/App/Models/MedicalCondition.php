<?php

namespace Modules\MedicalConditions\App\Models;

use Modules\Rooms\App\Models\Room;
use Modules\Auth\App\Models\User;
use Modules\Doctors\App\Models\Doctor;
use Illuminate\Database\Eloquent\Model;
use Modules\Service\App\Models\Service;
use Modules\Departments\App\Models\Department;
use Modules\SurgicalProcedures\App\Models\SurgicalProcedure;

class MedicalCondition extends Model
{
    protected $fillable = [
        'patient_national_id',
        'condition_description',
        'department_id',
        'room_id',
        'medications',
        'follow_up',
        'follow_up_date',
        'doctor_id',
        'surgery_required',
       
    ];

   

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function surgery()
    {
        return $this->hasOne(SurgicalProcedure::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'medical_condition_service');
    }



}
