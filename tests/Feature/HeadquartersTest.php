<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Region;
use App\Models\Headquarters;

class HeadquartersTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_headquarters_index_route_exists()
    {
        $response = $this->get('/admin/headquarters');
        $response->assertStatus(302); // Should redirect to login since we're not authenticated
    }

    public function test_headquarters_create_route_exists()
    {
        $response = $this->get('/admin/headquarters/create');
        $response->assertStatus(302); // Should redirect to login since we're not authenticated
    }

    public function test_headquarters_store_requires_authentication()
    {
        $region = Region::create(['name' => 'Test Region']);
        
        $response = $this->post('/admin/headquarters', [
            'name' => 'Test Headquarters',
            'region_id' => $region->id
        ]);
        
        $response->assertStatus(302); // Should redirect to login
    }

    public function test_headquarters_model_can_be_created()
    {
        $region = Region::create(['name' => 'Test Region']);
        
        $headquarters = Headquarters::create([
            'name' => 'Test Headquarters',
            'description' => 'Test Description',
            'address' => 'Test Address',
            'phone' => '1234567890',
            'region_id' => $region->id
        ]);

        $this->assertDatabaseHas('headquarters', [
            'name' => 'Test Headquarters',
            'region_id' => $region->id
        ]);
    }
}