<?php
namespace Modules\SurgicalProcedures\App\Models;

use Modules\Rooms\App\Models\Room;
use Modules\Doctors\App\Models\Doctor;
use Illuminate\Database\Eloquent\Model;
use Modules\Departments\App\Models\Department;
use Modules\MedicalConditions\App\Models\MedicalCondition;

class SurgicalProcedure extends Model
{
    protected $fillable = [
        'medical_condition_id',
        'surgery_type',
        'department_id',
        'room_id',
        'medical_staff',
        'surgery_date',
    ];

    protected $casts = [
        'medical_staff' => 'array', // This will ensure it's treated as an array
        'surgery_date' => 'datetime', // Casting surgery date to datetime
    ];

    public function medicalCondition()
    {
        return $this->belongsTo(MedicalCondition::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_surgical_procedure', 'surgical_procedure_id', 'doctor_id');
    }
}
