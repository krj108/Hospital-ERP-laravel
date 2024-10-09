<?php

// Modules/Departments/Models/Department.php

namespace Modules\Departments\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Rooms\App\Models\Room;

class Department extends Model
{
    protected $fillable = ['name'];


//  A department has many rooms
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}

