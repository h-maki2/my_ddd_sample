<?php

namespace App\Services;

use App\Http\Controllers\Api\ApiController;

class ApiVersionResolver
{
    private const CONTROLLER_NAMESPACE = 'App\Http\Controllers\Api';

    public function resolve(string $version, string $controllerName): ApiController
    {
        // バージョンを解決し、該当するコントローラの完全修飾クラス名を返す
        $namespace = self::CONTROLLER_NAMESPACE . '\\' . $version;
        $controllerClass = $namespace . '\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller [$controllerClass] not found.");
        }

        return app($controllerClass);
    }
}