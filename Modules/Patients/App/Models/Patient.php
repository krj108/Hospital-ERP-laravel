<?php

// Modules/Patients/App/Models/Patient.php
namespace Modules\Patients\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\App\Models\User;

class Patient extends Model
{
    protected $fillable = ['user_id', 'national_id', 'residence_address', 'mobile_number', 'added_by'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}

