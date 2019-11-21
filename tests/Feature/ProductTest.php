<?php

namespace Tests\Feature;

use App\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_client_can_create_a_product()
    {
        // Given
        $productData = [
            'name' => 'Super Product',
            'price' => '23.30'
        ];

        // When
        $response = $this->json('POST', '/api/products', $productData); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(201);
        
        // Assert the response has the correct structure
        $response->assertJsonStructure([
            'id',
            'name',
            'price'
        ]);

        // Assert the product was created
        // with the correct data
        $response->assertJsonFragment([
            'name' => 'Super Product',
            'price' => '23.30'
        ]);
        
        $body = $response->decodeResponseJson();

        // Assert product is on the database
        $this->assertDatabaseHas(
            'products',
            [
                'id' => $body['id'],
                'name' => 'Super Product',
                'price' => '23.30'
            ]
        );
    }

    public function test_client_can_see_list_of_products() //index - list
    {
        // Given
        $product = factory(Product::class, 2)->create();

        // When
        $response = $this->json('GET', '/api/products'); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(200);
        
        // Assert the list elements are returned
        $response->assertJsonFragment([
            'name' => $product[0]->name,
            'price' => strval($product[0]->price), 
            'name' => $product[1]->name,
            'price' => strval($product[1]->price)
        ]);        
    }

    public function test_client_show_product() //show 
    {
        // Given
        $product = factory(Product::class)->create();

        // When
        $response = $this->json('GET', '/api/products/'.$product->id ); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(200);
        
        // Assert the element is returned
        $response->assertJsonFragment([
            'name' => $product->name,
            'price' => strval($product->price)
        ]);        
    }

    public function test_client_update_product() //update
    {
        // Given
        $product = factory(Product::class)->create();

        $updatedData = [
            'name' => 'Super Product updated',
            'price' => '23.30'
        ];

        // When
        $response = $this->json('PUT', '/api/products/' . $product->id,  $updatedData); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(200);
        
        // Assert the element is updated and returned
        $response->assertJsonFragment([
            'name' => $updatedData['name'],
            'price' => strval($updatedData['price'])
        ]);        
    }

    public function test_client_delete_product() //delete
    {
        // Given
        $product = factory(Product::class)->create();

        // When
        $response = $this->json('DELETE', '/api/products/' . $product->id); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(204);      
    }
}
