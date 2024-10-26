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
         'surgery_date', // Include surgery date
    ];

    protected $casts = [
        'surgery_date' => 'datetime', // Ensure surgery date is cast to datetime

    ];

        // Automatically convert medical_staff to array when accessing it
        public function getMedicalStaffAttribute($value)
        {
            return json_decode($value, true); // Convert text to array
        }
    
        // Automatically convert medical_staff to json when saving it
        public function setMedicalStaffAttribute($value)
        {
            $this->attributes['medical_staff'] = json_encode($value); // Convert array to text (json format)
        }

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
