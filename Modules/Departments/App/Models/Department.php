<?php


namespace Modules\Departments\App\Models;

use Modules\Rooms\App\Models\Room;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name'];


//  A department has many rooms
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    protected static function newFactory()
    {
        return \Modules\Departments\Database\Factories\DepartmentFactory::new();
    }
}

