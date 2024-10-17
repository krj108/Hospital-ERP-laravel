<?php



namespace Modules\Rooms\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Departments\App\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{

    use HasFactory;

    protected $fillable = ['number', 'status', 'department_id'];

    
    protected static function newFactory()
    {
        return \Modules\Rooms\Database\Factories\RoomFactory::new();
    }

//A room belongs to a department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

