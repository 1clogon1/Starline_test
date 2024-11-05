<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TrainService;

class TrainController extends Controller
{
    protected $trainRouteService;

    public function __construct(TrainService $trainRouteService)
    {
        $this->trainRouteService = $trainRouteService;
    }

    /**
     * Обрабатывает запрос на получение маршрута поезда.
     *
     * @param Request $request Запрос от клиента.
     * @return \Illuminate\Http\JsonResponse Ответ в формате JSON с данными маршрута или сообщением об ошибке.
     * @throws \Illuminate\Validation\ValidationException Если входные данные не проходят валидацию.
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
            $routeData = $this->trainRouteService->getRoute($authParams, $requestParams);

            if ($routeData) {
                return response()->json(['route' => $routeData, 'success' => true]);
            }

            return response()->json(['success' => false, 'message' => 'Нет данных маршрута'], 404);
        } catch (Exception $e) {
            Log::error('Ошибка: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
