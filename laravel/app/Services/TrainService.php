<?php

namespace App\Services;

use SoapClient;
use SoapFault;

/**
 * Класс для работы с сервисом маршрутов поездов.
 */
class TrainRouteService
{
    private $client;

    /**
     * Конструктор, инициализирующий SoapClient.
     */
    public function __construct()
    {
        $this->client = new SoapClient("https://test-api.starliner.ru/Api/connect/Soap/Train/1.1.0?wsdl", [
            'cache_wsdl' => WSDL_CACHE_NONE,
            'trace' => true,
            'exceptions' => true
        ]);
    }

    /**
     * Получить маршрут поезда.
     *
     * @param object $authParams Параметры аутентификации для SOAP.
     * @param object $requestParams Параметры запроса для получения маршрута.
     * @return array|null Данные маршрута или null, если данные отсутствуют.
     * @throws \Exception Если произошла ошибка при выполнении запроса.
     */
    public function getRoute($authParams, $requestParams)
    {
        try {
            // Выполняем SOAP-запрос
            $response = $this->client->trainRoute($requestParams);

            // Проверяем наличие данных в ответе
            if (isset($response->return)) {
                return $this->parseResponse($response->return); // Парсим и возвращаем данные
            }

            return null; // Если нет данных маршрута
        } catch (SoapFault $e) {
            // Генерируем исключение при ошибке SOAP
            throw new \Exception('Ошибка SOAP: ' . $e->getMessage());
        }
    }

    /**
     * Парсит ответ от SOAP.
     *
     * @param object $response Ответ от SOAP.
     * @return array Данные маршрута, включая номер поезда и список остановок.
     */
    private function parseResponse($response)
    {
        $routeData = []; // Массив для хранения данных маршрута

        // Проверяем наличие описания поезда в ответе
        if (isset($response->train_description)) {
            $trainDesc = $response->train_description; // Получаем описание поезда
            $routeData['train_number'] = $trainDesc->number ?? ''; // Номер поезда
            $routeData['from_station'] = $trainDesc->from ?? ''; // Станция отправления
            $routeData['to_station'] = $trainDesc->to ?? ''; // Станция назначения
        }

        // Проверяем наличие списка остановок в ответе
        if (isset($response->route_list->stop_list)) {
            foreach ($response->route_list->stop_list as $stop) {
                // Добавляем данные о каждой остановке в массив
                $routeData['stops'][] = [
                    'station' => $stop->stop, // Название станции
                    'arrival_time' => $stop->arrival_time, // Время прибытия
                    'departure_time' => $stop->departure_time, // Время отправления
                    'stop_time' => $stop->stop_time // Время остановки
                ];
            }
        }

        return $routeData; // Возвращаем собранные данные о маршруте
    }
}
