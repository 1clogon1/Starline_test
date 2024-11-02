<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Создает JSON-ответ и добавляет отладочные заголовки.
     *
     * @param string $status Статус ответа ('success' или 'error').
     * @param mixed $data Данные для ответа.
     * @param array $debugData Данные отладки.
     * @param string|null $message Сообщение для ошибки (опционально).
     * @param int $statusCode Код состояния HTTP (по умолчанию 200).
     * @param string|null $details Дополнительные детали об ошибке (опционально).
     * @return \Illuminate\Http\JsonResponse Ответ с данными и заголовками.
     */
    protected function jsonResponse($status, $data, $debugData, $message = null, $statusCode = 200, $details = null)
    {
        $responseData = [
            'status' => $status
        ];
        if (!empty($data)) {
            $responseData['data'] = $data;
        }

        if ($message) {
            $responseData['message'] = $message;
        }

        if ($details) {
            $responseData['details'] = $details;
        }

        $response = response()->json($responseData, $statusCode);
        return $this->addDebugHeaders($response, $debugData);
    }

    /**
     * Запускает процесс отладки, возвращая начальное время и использование памяти.
     *
     * @return array Массив с начальным временем и использованием памяти.
     */
    protected function startDebug()
    {
        return [
            'startTime' => microtime(true),
            'startMemory' => memory_get_usage(),
        ];
    }

    /**
     * Добавляет заголовки с отладочной информацией о времени и использовании памяти в ответ.
     *
     * @param \Illuminate\Http\JsonResponse $response Ответ, к которому добавляются заголовки.
     * @param array $debugData Данные отладки, включая начальное время и использование памяти.
     * @return \Illuminate\Http\JsonResponse Ответ с добавленными заголовками.
     */
    protected function addDebugHeaders($response, $debugData)
    {
        $executionTime = round((microtime(true) - $debugData['startTime']) * 1000, 2);
        $memoryUsed = round((memory_get_usage() - $debugData['startMemory']) / 1024, 2);

        return $response
            ->header('X-Debug-Time', "{$executionTime} ms")
            ->header('X-Debug-Memory', "{$memoryUsed} KB");
    }

}
