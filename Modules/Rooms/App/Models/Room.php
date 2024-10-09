<?php



namespace Modules\Rooms\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Departments\App\Models\Department;

class Room extends Model
{
    protected $fillable = ['number', 'status', 'department_id'];

    
//A room belongs to a department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

