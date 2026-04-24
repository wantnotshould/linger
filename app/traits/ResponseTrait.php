<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\traits;

use app\core\Response;

trait ResponseTrait
{
    private function apiResponse(bool $status, string $message, int $code, mixed $data = null): Response
    {
        return $this->json([
            'status'  => $status,
            'message' => $message,
            'code'    => $code,
            'data'    => $data,
        ], $code);
    }

    public function success(mixed $data = null, string $message = 'Success', int $code = 200): Response
    {
        return $this->apiResponse(true, $message, $code, $data);
    }

    public function error(string $message = 'Error', int $code = 400, mixed $data = null): Response
    {
        return $this->apiResponse(false, $message, $code, $data);
    }

    public function json(array $data, int $statusCode = 200): Response
    {
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        return (new Response($content, $statusCode))
            ->header('Content-Type', 'application/json');
    }

    public function redirect(string $url, int $statusCode = 302): Response
    {
        return (new Response('', $statusCode))
            ->header('Location', $url);
    }

    public function html(string $html, int $statusCode = 200): Response
    {
        return (new Response($html, $statusCode))
            ->header('Content-Type', 'text/html; charset=utf-8');
    }
}
