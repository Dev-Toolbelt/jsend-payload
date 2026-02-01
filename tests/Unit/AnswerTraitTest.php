<?php

declare(strict_types=1);

namespace DevToolbelt\JsendPayload\Tests\Unit;

use DevToolbelt\Enums\Http\HttpStatusCode;
use DevToolbelt\JsendPayload\AnswerTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class AnswerTraitTest extends TestCase
{
    private object $subject;

    protected function setUp(): void
    {
        $this->subject = new class {
            use AnswerTrait;
        };
    }

    public function testAnswerSuccessReturnsJsendSuccessPayload(): void
    {
        $response = $this->subject->answerSuccess(['id' => 10]);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame(
            ['status' => 'success', 'data' => ['id' => 10]],
            $this->decodeResponse($response)
        );
    }

    public function testAnswerFailReturnsJsendFailPayload(): void
    {
        $errors = [['field' => 'name', 'error' => 'required']];

        $response = $this->subject->answerFail($errors);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(
            ['status' => 'fail', 'data' => $errors],
            $this->decodeResponse($response)
        );
    }

    public function testAnswerErrorWithoutDataReturnsJsendErrorPayload(): void
    {
        $response = $this->subject->answerError('Something went wrong');

        self::assertSame(500, $response->getStatusCode());
        self::assertSame(
            ['status' => 'error', 'message' => 'Something went wrong'],
            $this->decodeResponse($response)
        );
    }

    public function testAnswerErrorWithDataReturnsJsendErrorPayloadWithData(): void
    {
        $response = $this->subject->answerError(
            'Validation failed',
            HttpStatusCode::UNPROCESSABLE_ENTITY,
            ['traceId' => 'abc']
        );

        self::assertSame(422, $response->getStatusCode());
        self::assertSame(
            ['status' => 'error', 'message' => 'Validation failed', 'data' => ['traceId' => 'abc']],
            $this->decodeResponse($response)
        );
    }

    public function testAnswerNoContentReturnsSuccessWithNullData(): void
    {
        $response = $this->subject->answerNoContent();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            ['status' => 'success', 'data' => null],
            $this->decodeResponse($response)
        );
    }

    public function testAnswerInvalidUuidReturnsExpectedFailPayload(): void
    {
        $response = $this->subject->answerInvalidUuid();

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(
            [
                'status' => 'fail',
                'data' => [[
                    'field' => 'id',
                    'error' => 'invalidUuidFormat',
                    'message' => 'The provided uuid format is invalid',
                ]],
            ],
            $this->decodeResponse($response)
        );
    }

    public function testAnswerRecordNotFoundReturnsExpectedFailPayload(): void
    {
        $response = $this->subject->answerRecordNotFound();

        self::assertSame(404, $response->getStatusCode());
        self::assertSame(
            [
                'status' => 'fail',
                'data' => [[
                    'field' => 'id',
                    'error' => 'recordNotFound',
                    'message' => 'The record was not found with the given id',
                ]],
            ],
            $this->decodeResponse($response)
        );
    }

    public function testAnswerEmptyPayloadReturnsExpectedFailPayload(): void
    {
        $response = $this->subject->answerEmptyPayload();

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(
            [
                'status' => 'fail',
                'data' => [[
                    'error' => 'emptyPayload',
                    'message' => 'It was send a empty payload',
                ]],
            ],
            $this->decodeResponse($response)
        );
    }

    public function testAnswerRequiredReturnsExpectedFailPayload(): void
    {
        $response = $this->subject->answerRequired('email');

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(
            [
                'status' => 'fail',
                'data' => [[
                    'field' => 'email',
                    'error' => 'required',
                    'message' => 'The "email" field is required',
                ]],
            ],
            $this->decodeResponse($response)
        );
    }

    public function testAnswerColumnNotFoundReturnsExpectedFailPayload(): void
    {
        $response = $this->subject->answerColumnNotFound('created_at');

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(
            [
                'status' => 'fail',
                'data' => [[
                    'field' => 'created_at',
                    'error' => 'columnNotFound',
                    'message' => 'The "created_at" column was not found',
                ]],
            ],
            $this->decodeResponse($response)
        );
    }

    private function decodeResponse(ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
