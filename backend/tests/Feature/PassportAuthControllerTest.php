<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\PassportAuthController;

class PassportAuthControllerTest extends TestCase
{
    public function testLogin()
    {
        // Simulate basic auth credentials
        $basicAuthHeader = 'Basic ' . base64_encode('pepe@gmail.com:PepePepe1234');

        // Simulate an HTTP request with basic auth header
        $response = $this->withHeader('Authorization', $basicAuthHeader)
                 ->json('POST', 'api/login');

        // Assert that the response is successful
        $response->assertStatus(200);

        // Assert the JSON structure of the response
        $response->assertJsonStructure([
            'data' => [
                'token',
                'name',
                'idUser',
            ],
            'message',
        ]);

        // Assert that authentication was successful
        $this->assertAuthenticated();

        // Get the instance of the controller
        $controller = app(PassportAuthController::class);

        // Call the extractBasicAuthData method directly
        $dataDecoded = $this->invokeMethod($controller, 'extractBasicAuthData', [$this->app['request']]);

        // Assert that the function returns the expected data
        $this->assertEquals('pepe@gmail.com', $dataDecoded['email']);
        $this->assertEquals('PepePepe1234', $dataDecoded['password']);
    }

    // Function to invoke private or protected methods
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}