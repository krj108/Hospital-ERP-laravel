<?php

namespace Modules\Departments\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Departments\App\Models\Department;
use Modules\Auth\App\Models\User; 
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    // Setup method to prepare the environment before each test
    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin role if it doesn't exist
        Role::firstOrCreate(['name' => 'admin']);

        // Create an admin user and log in for testing
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin'); // Assign the admin role to the user

        // Authenticate the admin user using Sanctum
        Sanctum::actingAs($this->admin, ['*']);
    }

    /** @test */
    public function it_can_fetch_all_departments_with_rooms()
    {
        // Create a department with associated rooms for testing
        $department = Department::factory()->hasRooms(3)->create();

        // Send a request to the index method
        $response = $this->getJson('/api/departments');

        // Assert that the response status is 200 and that departments and rooms are returned
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'rooms' => [
                    '*' => ['id', 'number', 'status']
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_create_a_department()
    {
        // Data for creating a new department
        $data = ['name' => 'New Department'];

        // Send a request to create a new department
        $response = $this->postJson('/api/departments', $data);

        // Assert that the department was created and the response status is 201
        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'New Department']);

        // Assert that the department exists in the database
        $this->assertDatabaseHas('departments', ['name' => 'New Department']);
    }

    /** @test */
    public function it_can_update_a_department()
    {
        // Create a department to test
        $department = Department::factory()->create(['name' => 'Old Name']);

        // Data for updating the department
        $data = ['name' => 'Updated Name'];

        // Send a request to update the department
        $response = $this->putJson("/api/departments/{$department->id}", $data);

        // Assert that the department was updated and the response status is 200
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Updated Name']);

        // Assert that the name was updated in the database
        $this->assertDatabaseHas('departments', ['name' => 'Updated Name']);
    }

    /** @test */
    public function it_can_delete_a_department()
    {
        // Create a department to test
        $department = Department::factory()->create();

        // Send a request to delete the department
        $response = $this->deleteJson("/api/departments/{$department->id}");

        // Assert that the response status is 204 (No Content)
        $response->assertStatus(204);

        // Assert that the department was deleted from the database
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}
