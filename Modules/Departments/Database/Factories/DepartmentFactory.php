<?php
namespace Modules\Departments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Departments\App\Models\Department;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
