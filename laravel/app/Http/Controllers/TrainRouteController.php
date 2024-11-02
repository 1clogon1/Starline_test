<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;

class TrainRouteController extends Controller
{
    /**
     * Получить маршрут поезда на основе входных параметров.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getRoute(Request $request)
    {
        $request->validate([
            'train' => 'required|string',
            'from' => 'required|string',
            'to' => 'required|string',
            'day' => 'required|integer|min:1|max:31',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $authParams = (object) [
            'login' => config('services.soap.login'),
            'psw' => config('services.soap.password'),
            'terminal' => config('services.soap.terminal'),
            'represent_id' => config('services.soap.represent_id'),
            'language' => config('services.soap.language'),
            'currency' => config('services.soap.currency')
        ];

        $requestParams = (object) [
            'auth' => $authParams,
            'train' => $request->train,
            'TravelInfo' => (object) [
                'from' => $request->from,
                'to' => $request->to,
                'day' => $request->day,
                'month' => $request->month
            ]
        ];

        Log::info('Получен запрос на маршрут', [
            'train' => $request->train,
            'from' => $request->from,
            'to' => $request->to,
            'day' => $request->day,
            'month' => $request->month,
        ]);

        try {
            $client = new SoapClient("https://test-api.starliner.ru/Api/connect/Soap/Train/1.1.0?wsdl", [
                'cache_wsdl' => WSDL_CACHE_NONE,
                'trace' => true,
                'exceptions' => true
            ]);

            $response = $client->trainRoute($requestParams);

            if (isset($response->return)) {
                $routeData = $this->parseResponse($response->return);
                return response()->json(['route' => $routeData, 'success' => true]);
            } else {
                return response()->json(['success' => false, 'message' => 'Нет данных маршрута'], 404);
            }
        } catch (SoapFault $e) {
            Log::error('Ошибка SOAP: ' . $e->getMessage()); // Логируем ошибку
            return response()->json(['success' => false, 'message' => 'Ошибка SOAP: ' . $e->getMessage()], 500);
        }
    }

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
