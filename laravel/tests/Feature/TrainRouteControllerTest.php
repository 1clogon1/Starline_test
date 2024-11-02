<?php

namespace Tests\Feature;

use SoapClient;
use SoapFault;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Тестовый класс для проверки функциональности TrainRouteController.
 */
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
        // Отправляем запрос на получение маршрута поезда
        $response = $this->postJson('/get-route', [
            'train' => '123A', // Номер поезда
            'from' => 'Москва', // Станция отправления
            'to' => 'Санкт-Петербург', // Станция назначения
            'day' => 10, // День
            'month' => 11, // Месяц
        ]);

        // Проверяем статус ответа и наличие ключа 'route'
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
        // Отправляем запрос с некорректными данными
        $response = $this->postJson('/get-route', [
            'train' => '', // Пустой номер поезда (ошибка валидации)
            'from' => 'Москва',
            'to' => 'Санкт-Петербург',
            'day' => 10,
            'month' => 11,
        ]);

        // Проверяем статус ответа и наличие ошибок валидации
        $response->assertStatus(422); // Ошибка валидации
        $this->assertArrayHasKey('errors', $response->json());
    }

    /**
     * Тестирование обработки ошибок SOAP при получении маршрута поезда.
     *
     * @return void
     */
    public function testGetRouteSoapError()
    {
        // Создаем мок для SoapClient
        $this->withoutExceptionHandling(); // Отключаем глобальное исключение Laravel для отображения подробностей ошибки

        $this->mock(SoapClient::class, function ($mock) {
            // Настраиваем ожидание вызова метода trainRoute и выбрасываем исключение
            $mock->shouldReceive('trainRoute')
                ->once()
                ->andThrow(new SoapFault('Server', 'Some SOAP error message'));
        });

        // Отправляем запрос на получение маршрута поезда
        $response = $this->postJson('/get-route', [
            'train' => '123A',
            'from' => 'Москва',
            'to' => 'Санкт-Петербург',
            'day' => 10,
            'month' => 11,
        ]);

        // Проверяем статус ответа и наличие сообщения об ошибке
        $response->assertStatus(500);
        $this->assertArrayHasKey('error', $response->json());
        $this->assertStringContainsString('Ошибка SOAP:', $response->json()['error']);
    }
}
