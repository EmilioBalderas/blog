<?php

namespace Tests\Feature;

use App\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    
    //Create-1
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

    // CREATE-2
    public function create_product_without_name()
    {
        // Given
        $productData = [
            'price' => '23.30'
        ];

        // When
        $response = $this->json('POST', '/api/products', $productData);

        //Then
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'errors' => [[
                'code' => 'ERROR-1',
                'title' => 'Unprocessable Entity'
            ]]
        ]);
    }

    //CREATE-3
    public function create_product_without_price()
    {
        //Given
        $productData = [
            'name' => 'Super Product'
        ];

        //When
        $response = $this->json('POST', '/api/products', $productData);

        //Then
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'errors' => [[
                'code' => 'ERROR-1',
                'title' => 'Unprocessable Entity'
            ]]
        ]);
    }

    // CREATE-4
    public function create_product_with_price_not_a_number()
    {
        //Given
        $productData = [
            'name' => 'Super Product',
            'price' => 'two dollars'
        ];

        //When
        $response = $this->json('POST', '/api/products', $productData);
        
        //Then
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'errors' => [[
                'code' => 'ERROR-1',
                'title' => 'Unprocessable Entity'
            ]]
        ]);
    }

    // CREATE-5
    public function create_product_with_price_less_than_0()
    {
        // Given
        $productData = [
            'name' => 'Super Product',
            'price' => '-1'
        ];

        // When
        $response = $this->json('POST', '/api/products', $productData);
       
        // Then
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'errors' => [[
                'code' => 'ERROR-1',
                'title' => 'Unprocessable Entity'
            ]]
        ]);
    }

    //List-1
    public function test_client_can_see_list_of_products() 
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

    //LIST-2
    public function test_client_can_see_list_of_products_empty() 
    {
        // Given
        //no products

        // When
        $response = $this->json('GET', '/api/products'); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(200);
        
        // Assert there are no elements returned
        $response->assertJson([]);        
    }

    //SHOW-1
    public function test_client_show_product()
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

    //SHOW-2
    public function test_client_show_product_doesnt_exists()
    {
        // Given
        //no products in db

        // When
        $response = $this->json('GET', '/api/products/1' ); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(404);
        
        // Assert the element is returned
        $response->assertJsonFragment([
            'errors' => [[
                'code' => 'ERROR-2',
                'title' => 'Not Found'
            ]]
        ]);        
    }

    //UPDATE-1
    public function test_client_update_product() 
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
    
    //UPDATE-2
    public function test_client_update_product_with_price_not_number() 
    {
        // Given
        $product = factory(Product::class)->create();

        $updatedData = [
            'name' => 'Super Product updated',
            'price' => 'two dollars'
        ];

        // When
        $response = $this->json('PUT', '/api/products/' . $product->id,  $updatedData); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(422);
        
        // Assert the error message is returned
        $response->assertJsonFragment([
            'errors' => [[
                'code' => 'ERROR-1',
                'title' => 'Unprocessable Entity'
            ]]
        ]);        
    }
    
    //UPDATE-3
    public function test_client_update_product_price_less_than_0() 
    {
        // Given
        $product = factory(Product::class)->create();

        $updatedData = [
            'name' => 'Super Product updated',
            'price' => '-23.30'
        ];

        // When
        $response = $this->json('PUT', '/api/products/' . $product->id,  $updatedData); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(422);
        
        // Assert the error message is returned
        $response->assertJsonFragment([
            'errors' => [[
                'code' => 'ERROR-1',
                'title' => 'Unprocessable Entity'
            ]]
        ]);        
    }

    //UPDATE-4
    public function test_client_update_product_doesnt_exists() 
    {
        // Given
        //no products in database

        // When
        $response = $this->json('PUT', '/api/products/1',  $updatedData); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(404);
        
        // Assert the error message is returned
        $response->assertJsonFragment([
            'errors' => [[
                'code' => 'ERROR-2',
                'title' => 'Not Found'
            ]]
        ]);        
    }

    //DELETE-1
    public function test_client_delete_product() 
    {
        // Given
        $product = factory(Product::class)->create();

        // When
        $response = $this->json('DELETE', '/api/products/' . $product->id); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(204);      
    }

    
    //DELETE-2
    public function test_client_delete_product() 
    {
        // Given
        //no products in database

        // When
        $response = $this->json('DELETE', '/api/products/1'); 

        // Then
        // Assert it sends the correct HTTP Status
        $response->assertStatus(404);    
        
        // Assert the error message is returned
        $response->assertJsonFragment([
            'errors' => [[
                'code' => 'ERROR-2',
                'title' => 'Not Found'
            ]]
        ]);  
    }
}
