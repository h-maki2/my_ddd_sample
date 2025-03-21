<?php

namespace packages\infrastructure\services\common\identityAccessApi;

use Illuminate\Http\Client\Response;

class IdentityAccessApiResponse
{
    private mixed $response;

    public function __construct(Response $response)
    {
        $this->response = $response->json();
    }

    public function errorResponse(): array
    {
        if (isset($this->response['error']['details'])) {
            return $this->response['error']['details'];
        }

        return [];
    }

    public function successResponse(): array
    {
        if (isset($this->response['data'])) {
            return $this->response['data'];
        }

        return [];
    }
}