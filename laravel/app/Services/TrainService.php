<?php

namespace App\Services;

use Exception;
use SoapClient;
use SoapFault;

class TrainService
{
    private $client;

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
     * @param object $authParams Параметры аутентификации.
     * @param object $requestParams Параметры запроса для получения маршрута.
     * @return array|null Данные маршрута или null, если данные отсутствуют.
     * @throws \Exception Если произошла ошибка при выполнении запроса.
     */
    public function getRoute($authParams, $requestParams)
    {
        try {
            $response = $this->client->trainRoute($authParams, $requestParams->train, $requestParams->TravelInfo);

            if (isset($response->return)) {
                return $this->parseResponse($response->return);
            }

            return null;
        } catch (SoapFault $e) {
            throw new Exception('Ошибка SOAP: ' . $e->getMessage());
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
        $routeData = [];

        if (isset($response->train_description)) {
            $trainDesc = $response->train_description;
            $routeData['train_number'] = $trainDesc->number ?? '';
            $routeData['from_station'] = $trainDesc->from ?? '';
            $routeData['to_station'] = $trainDesc->to ?? '';
        }

        if (isset($response->route_list->stop_list)) {
            foreach ($response->route_list->stop_list as $stop) {
                $routeData['stops'][] = [
                    'station' => $stop->stop,
                    'arrival_time' => $stop->arrival_time,
                    'departure_time' => $stop->departure_time,
                    'stop_time' => $stop->stop_time
                ];
            }
        }

        return $routeData;
    }
}
