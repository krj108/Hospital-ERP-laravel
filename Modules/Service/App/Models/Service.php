<?php

namespace Modules\Service\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Service\Database\factories\ServiceFactory;
use Modules\Departments\App\Models\Department;


class Service extends Model
{
    use HasFactory;

    
    protected $fillable = ['name', 'type', 'description', 'special_instructions', 'department_id'];

  
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    
    // protected static function newFactory(): ServiceFactory
    // {
    //     //return ServiceFactory::new();
    // }
}
