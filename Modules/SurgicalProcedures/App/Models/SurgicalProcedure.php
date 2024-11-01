<?php
namespace Modules\SurgicalProcedures\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Departments\App\Models\Department;
use Modules\Doctors\App\Models\Doctor;
use Modules\MedicalConditions\App\Models\MedicalCondition;
use Modules\Rooms\App\Models\Room;

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
        'surgery_date' => 'datetime',
        'medical_staff' => 'array', // Casting to array to handle JSON format automatically
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
