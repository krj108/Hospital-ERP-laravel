<?php

namespace Modules\Rooms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Rooms\App\Models\Room;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition()
    {
        return [
            'number' => $this->faker->randomNumber(1),
            'status' => $this->faker->randomElement(['vacant', 'occupied', 'maintenance']),
            'department_id' => \Modules\Departments\App\Models\Department::factory(),
        ];
    }
}


