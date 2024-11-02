<?php

namespace Tests\Feature;

use SoapClient;
use SoapFault;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrainRouteControllerTest extends TestCase
{
    use RefreshDatabase; // Используем трейт для сброса базы данных после каждого теста

    /**
     * Тестирование успешного получения маршрута поезда.
     *
     * @return void
     */
    public function testGetRouteSuccessfully()
    {
        $response = $this->postJson('/get-route', [
            'train' => '123A',
            'from' => 'Москва',
            'to' => 'Санкт-Петербург',
            'day' => 10,
            'month' => 11,
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('route', $response->json());
    }

    /**
     * Тестирование обработки ошибки валидации при получении маршрута поезда.
     *
     * @return void
     */
    public function testGetRouteValidationFails()
    {
        $response = $this->postJson('/get-route', [
            'train' => '',
            'from' => 'Москва',
            'to' => 'Санкт-Петербург',
            'day' => 10,
            'month' => 11,
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('errors', $response->json());
    }

    /**
     * Тестирование обработки ошибок SOAP при получении маршрута поезда.
     *
     * @return void
     */
    public function testGetRouteSoapError()
    {
        $this->withoutExceptionHandling();

        $this->mock(SoapClient::class, function ($mock) {
            $mock->shouldReceive('trainRoute')
                ->once()
                ->andThrow(new SoapFault('Server', 'Some SOAP error message'));
        });

        $response = $this->postJson('/get-route', [
            'train' => '123A',
            'from' => 'Москва',
            'to' => 'Санкт-Петербург',
            'day' => 10,
            'month' => 11,
        ]);

        $response->assertStatus(500);
        $this->assertArrayHasKey('error', $response->json());
        $this->assertStringContainsString('Ошибка SOAP:', $response->json()['error']);
    }
}
