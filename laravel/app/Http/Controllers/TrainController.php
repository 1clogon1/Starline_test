<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TrainRouteService;

/**
 * Контроллер для обработки запросов на получение маршрутов поездов.
 */
class TrainRouteController extends Controller
{
    protected $trainRouteService;

    /**
     * Конструктор, в который внедряется сервис TrainRouteService.
     *
     * @param TrainRouteService $trainRouteService Сервис для работы с маршрутами поездов.
     */
    public function __construct(TrainRouteService $trainRouteService)
    {
        $this->trainRouteService = $trainRouteService; // Сохраняем экземпляр сервиса
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
        // Валидация входных данных
        $request->validate([
            'train' => 'required|string', // Номер поезда обязателен
            'from' => 'required|string', // Станция отправления обязательна
            'to' => 'required|string', // Станция назначения обязательна
            'day' => 'required|integer|min:1|max:31', // День месяца обязателен и должен быть в диапазоне
            'month' => 'required|integer|min:1|max:12', // Месяц обязателен и должен быть в диапазоне
        ]);

        // Параметры аутентификации для SOAP
        $authParams = (object) [
            'login' => config('services.soap.login'),
            'psw' => config('services.soap.password'),
            'terminal' => config('services.soap.terminal'),
            'represent_id' => config('services.soap.represent_id'),
            'language' => config('services.soap.language'),
            'currency' => config('services.soap.currency')
        ];

        // Параметры запроса для SOAP
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

        // Логируем информацию о запросе
        Log::info('Получен запрос на маршрут', [
            'train' => $request->train,
            'from' => $request->from,
            'to' => $request->to,
            'day' => $request->day,
            'month' => $request->month,
        ]);

        try {
            // Вызываем сервис для получения маршрута
            $routeData = $this->trainRouteService->getRoute($authParams, $requestParams);

            // Проверяем, получили ли данные маршрута
            if ($routeData) {
                return response()->json(['route' => $routeData, 'success' => true]); // Успех
            }

            return response()->json(['success' => false, 'message' => 'Нет данных маршрута'], 404); // Нет данных
        } catch (\Exception $e) {
            // Логируем ошибку и возвращаем ответ с сообщением об ошибке
            Log::error('Ошибка: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
