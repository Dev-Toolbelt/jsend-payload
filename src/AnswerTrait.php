<?php

declare(strict_types=1);

namespace DevToolbelt\JsendPayload;

use DevToolbelt\Enums\Http\HttpStatusCode as Code;
use DevToolbelt\JsendPayload\Enums\JsendStatus;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

trait AnswerTrait
{
    public function answerSuccess(mixed $data, Code $code = Code::OK, array $meta = []): ResponseInterface
    {
        return $this->jsonResponse([
            'status' => JsendStatus::SUCCESS->value,
            'data' => $data,
            'meta' => $meta
        ], $code);
    }

    public function answerFail(array $data, Code $code = Code::BAD_REQUEST, array $meta = []): ResponseInterface
    {
        return $this->jsonResponse([
            'status' => JsendStatus::FAIL->value,
            'data' => $data,
            'meta' => $meta
        ], $code);
    }

    public function answerError(
        string $message,
        Code $code = Code::INTERNAL_SERVER_ERROR,
        mixed $data = null
    ): ResponseInterface {
        $response = [
            'status' => JsendStatus::ERROR->value,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->jsonResponse($response, $code);
    }

    public function answerNoContent(Code $code = Code::OK): ResponseInterface
    {
        return $this->jsonResponse(['status' => JsendStatus::SUCCESS->value, 'data' => null], $code);
    }

    public function answerInvalidUuid(Code $code = Code::BAD_REQUEST): ResponseInterface
    {
        return $this->answerFail([[
            'field' => 'id',
            'error' => 'invalidUuidFormat',
            'message' => 'The provided uuid format is invalid',
        ]], $code);
    }

    public function answerRecordNotFound(Code $code = Code::NOT_FOUND): ResponseInterface
    {
        return $this->answerFail([[
            'field' => 'id',
            'error' => 'recordNotFound',
            'message' => 'The record was not found with the given id',
        ]], $code);
    }

    public function answerEmptyPayload(Code $code = Code::BAD_REQUEST): ResponseInterface
    {
        return $this->answerFail([['error' => 'emptyPayload', 'message' => 'It was send a empty payload']], $code);
    }

    public function answerRequired(string $fieldName, Code $code = Code::BAD_REQUEST): ResponseInterface
    {
        return $this->answerFail([[
            'field' => $fieldName,
            'error' => 'required',
            'message' => "The \"{$fieldName}\" field is required",
        ]], $code);
    }

    public function answerColumnNotFound(string $columnName, Code $code = Code::BAD_REQUEST): ResponseInterface
    {
        return $this->answerFail([[
            'field' => $columnName,
            'error' => 'columnNotFound',
            'message' => "The \"{$columnName}\" column was not found",
        ]], $code);
    }

    private function jsonResponse(array $payload, Code $code): ResponseInterface
    {
        return new Response(
            $code->value,
            ['Content-Type' => 'application/json'],
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );
    }
}
