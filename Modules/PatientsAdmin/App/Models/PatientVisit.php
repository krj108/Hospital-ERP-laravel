<?php

namespace Modules\PatientsAdmin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Patients\App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\PatientsAdmin\Database\factories\PatientVisitFactory;

class PatientVisit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'patient_national_id',
        'entry_time',
        'exit_time',
    ];
    
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_national_id', 'national_id');
    }
}
