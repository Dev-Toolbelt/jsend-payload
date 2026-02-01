# JSend Payload

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dev-toolbelt/jsend-payload.svg?style=flat-square)](https://packagist.org/packages/dev-toolbelt/jsend-payload)
[![Total Downloads](https://img.shields.io/packagist/dt/dev-toolbelt/jsend-payload.svg?style=flat-square)](https://packagist.org/packages/dev-toolbelt/jsend-payload)
[![License](https://img.shields.io/packagist/l/dev-toolbelt/jsend-payload.svg?style=flat-square)](https://packagist.org/packages/dev-toolbelt/jsend-payload)
[![PHP Version](https://img.shields.io/packagist/php-v/dev-toolbelt/jsend-payload.svg?style=flat-square)](https://packagist.org/packages/dev-toolbelt/jsend-payload)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%206-brightgreen.svg?style=flat-square)](https://phpstan.org/)

A framework-agnostic PHP library for building standardized API responses following the [JSend specification](https://github.com/omniti-labs/jsend). Provides a simple trait that can be mixed into any controller or handler to generate consistent JSON responses with PSR-7 support.

## Features

- **Framework Independence** - Works with Laravel, Symfony, Slim, Mezzio, or any PSR-7 compatible framework
- **JSend Compliant** - Follows the JSend specification for standardized API responses
- **PSR-7 Support** - Returns `Psr\Http\Message\ResponseInterface` for maximum interoperability
- **Type-Safe HTTP Codes** - Uses enums for HTTP status codes, preventing invalid values
- **Common Response Helpers** - Pre-built methods for validation errors, not found, empty payloads, and more
- **Zero Configuration** - Just use the trait and start returning standardized responses

## Requirements

- PHP 8.1 or higher

## Installation

```bash
composer require dev-toolbelt/jsend-payload
```

## Quick Start

Add the `AnswerTrait` to your controller and start returning standardized responses:

```php
<?php

use DevToolbelt\JsendPayload\AnswerTrait;
use Psr\Http\Message\ResponseInterface;

class UserController
{
    use AnswerTrait;

    public function show(string $id): ResponseInterface
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->answerRecordNotFound();
        }

        return $this->answerSuccess($user);
    }
}
```

## Understanding JSend

JSend is a specification for a simple, standardized format for JSON responses from web servers. It defines three response types:

| Status | Description | When to Use |
|--------|-------------|-------------|
| `success` | All went well, data is returned | Successful GET, POST, PUT, DELETE operations |
| `fail` | Problem with submitted data or call conditions | Validation errors, missing required fields |
| `error` | Server error occurred | Exceptions, database failures, unexpected errors |

## Usage

### Success Responses

Use for successful operations that return data:

```php
// Returns: {"status": "success", "data": {"id": 1, "name": "John"}}
return $this->answerSuccess(['id' => 1, 'name' => 'John']);

// With custom HTTP status code
use DevToolbelt\Enums\Http\HttpStatusCode;

return $this->answerSuccess($createdUser, HttpStatusCode::CREATED);
```

### Fail Responses

Use when there's a problem with the data submitted:

```php
// Returns: {"status": "fail", "data": [{"field": "email", "error": "required"}]}
return $this->answerFail([
    ['field' => 'email', 'error' => 'required', 'message' => 'Email is required']
]);
```

### Error Responses

Use for server-side errors:

```php
// Returns: {"status": "error", "message": "Database connection failed"}
return $this->answerError('Database connection failed');

// With additional data
return $this->answerError(
    'Validation failed',
    HttpStatusCode::UNPROCESSABLE_ENTITY,
    ['traceId' => 'abc-123']
);
```

### No Content Response

Use when operation succeeds but there's no data to return:

```php
// Returns: {"status": "success", "data": null}
return $this->answerNoContent();
```

## Helper Methods

The trait includes convenient methods for common API scenarios:

| Method | HTTP Code | Description |
|--------|-----------|-------------|
| `answerSuccess($data)` | 200 | Successful response with data |
| `answerFail($data)` | 400 | Client error with validation details |
| `answerError($message)` | 500 | Server error with message |
| `answerNoContent()` | 200 | Success with null data |
| `answerInvalidUuid()` | 400 | Invalid UUID format error |
| `answerRecordNotFound()` | 404 | Record not found error |
| `answerEmptyPayload()` | 400 | Empty request body error |
| `answerRequired($field)` | 400 | Required field missing error |
| `answerColumnNotFound($column)` | 400 | Database column not found error |

## Response Examples

### answerInvalidUuid()

```json
{
    "status": "fail",
    "data": [{
        "field": "id",
        "error": "invalidUuidFormat",
        "message": "The provided uuid format is invalid"
    }]
}
```

### answerRecordNotFound()

```json
{
    "status": "fail",
    "data": [{
        "field": "id",
        "error": "recordNotFound",
        "message": "The record was not found with the given id"
    }]
}
```

### answerRequired('email')

```json
{
    "status": "fail",
    "data": [{
        "field": "email",
        "error": "required",
        "message": "The \"email\" field is required"
    }]
}
```

## Framework Integration

### Slim Framework

```php
<?php

use DevToolbelt\JsendPayload\AnswerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CreateUserAction
{
    use AnswerTrait;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();

        if (empty($data)) {
            return $this->answerEmptyPayload();
        }

        if (empty($data['email'])) {
            return $this->answerRequired('email');
        }

        $user = $this->userService->create($data);

        return $this->answerSuccess($user, HttpStatusCode::CREATED);
    }
}
```

### Laravel

```php
<?php

namespace App\Http\Controllers;

use DevToolbelt\JsendPayload\AnswerTrait;
use Illuminate\Http\Request;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    use AnswerTrait;

    public function store(Request $request): ResponseInterface
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
        ]);

        $user = User::create($validated);

        return $this->answerSuccess($user, HttpStatusCode::CREATED);
    }
}
```

> **Note:** Laravel requires the `nyholm/psr7` bridge or similar PSR-7 adapter for full compatibility.

### Mezzio (formerly Zend Expressive)

```php
<?php

namespace App\Handler;

use DevToolbelt\JsendPayload\AnswerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserHandler implements RequestHandlerInterface
{
    use AnswerTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');
        $user = $this->repository->find($id);

        if (!$user) {
            return $this->answerRecordNotFound();
        }

        return $this->answerSuccess($user->toArray());
    }
}
```

### Symfony

```php
<?php

namespace App\Controller;

use DevToolbelt\JsendPayload\AnswerTrait;
use DevToolbelt\Enums\Http\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    use AnswerTrait;

    #[Route('/api/users/{id}', name: 'api_user_show', methods: ['GET'])]
    public function show(string $id, UserRepository $repository): ResponseInterface
    {
        $user = $repository->find($id);

        if (!$user) {
            return $this->answerRecordNotFound();
        }

        return $this->answerSuccess([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ]);
    }

    #[Route('/api/users', name: 'api_user_create', methods: ['POST'])]
    public function create(Request $request, UserRepository $repository): ResponseInterface
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return $this->answerEmptyPayload();
        }

        if (empty($data['email'])) {
            return $this->answerRequired('email');
        }

        $user = $repository->create($data);

        return $this->answerSuccess($user, HttpStatusCode::CREATED);
    }
}
```

> **Note:** Symfony requires the PSR-7 Bridge (`symfony/psr-http-message-bridge`) for PSR-7 response compatibility.

### Yii2

```php
<?php

namespace app\controllers;

use DevToolbelt\JsendPayload\AnswerTrait;
use DevToolbelt\Enums\Http\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;
use yii\web\Controller;

class UserController extends Controller
{
    use AnswerTrait;

    public function actionView(string $id): ResponseInterface
    {
        $user = User::findOne($id);

        if (!$user) {
            return $this->answerRecordNotFound();
        }

        return $this->answerSuccess($user->toArray());
    }

    public function actionCreate(): ResponseInterface
    {
        $data = \Yii::$app->request->getBodyParams();

        if (empty($data)) {
            return $this->answerEmptyPayload();
        }

        $user = new User();
        $user->load($data, '');

        if (!$user->validate()) {
            return $this->answerFail(
                array_map(fn($field, $errors) => [
                    'field' => $field,
                    'error' => 'validation',
                    'message' => implode(', ', $errors),
                ], array_keys($user->errors), $user->errors)
            );
        }

        $user->save();

        return $this->answerSuccess($user->toArray(), HttpStatusCode::CREATED);
    }
}
```

> **Note:** For Yii2, you may need to configure your application to handle PSR-7 responses or convert them using `yiisoft/yii2-psr7-bridge`.

### CakePHP 5

```php
<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use DevToolbelt\JsendPayload\AnswerTrait;
use DevToolbelt\Enums\Http\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;

class UsersController extends AppController
{
    use AnswerTrait;

    public function view(string $id): ResponseInterface
    {
        $user = $this->Users->find()
            ->where(['id' => $id])
            ->first();

        if (!$user) {
            return $this->answerRecordNotFound();
        }

        return $this->answerSuccess($user->toArray());
    }

    public function add(): ResponseInterface
    {
        $data = $this->request->getData();

        if (empty($data)) {
            return $this->answerEmptyPayload();
        }

        $user = $this->Users->newEntity($data);

        if ($user->hasErrors()) {
            $errors = [];
            foreach ($user->getErrors() as $field => $fieldErrors) {
                foreach ($fieldErrors as $error => $message) {
                    $errors[] = [
                        'field' => $field,
                        'error' => $error,
                        'message' => $message,
                    ];
                }
            }
            return $this->answerFail($errors);
        }

        $this->Users->save($user);

        return $this->answerSuccess($user->toArray(), HttpStatusCode::CREATED);
    }
}
```

> **Note:** CakePHP 5 has native PSR-7 support. For CakePHP 4, the integration works similarly.

### CodeIgniter 4

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use DevToolbelt\JsendPayload\AnswerTrait;
use DevToolbelt\Enums\Http\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;

class UserController extends ResourceController
{
    use AnswerTrait;

    protected $modelName = 'App\Models\UserModel';

    public function show($id = null): ResponseInterface
    {
        $user = $this->model->find($id);

        if (!$user) {
            return $this->answerRecordNotFound();
        }

        return $this->answerSuccess($user);
    }

    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        if (empty($data)) {
            return $this->answerEmptyPayload();
        }

        $validation = \Config\Services::validation();

        if (!$validation->run($data, 'userRules')) {
            $errors = [];
            foreach ($validation->getErrors() as $field => $message) {
                $errors[] = [
                    'field' => $field,
                    'error' => 'validation',
                    'message' => $message,
                ];
            }
            return $this->answerFail($errors);
        }

        $userId = $this->model->insert($data);
        $user = $this->model->find($userId);

        return $this->answerSuccess($user, HttpStatusCode::CREATED);
    }

    public function update($id = null): ResponseInterface
    {
        $user = $this->model->find($id);

        if (!$user) {
            return $this->answerRecordNotFound();
        }

        $data = $this->request->getJSON(true);

        if (empty($data)) {
            return $this->answerEmptyPayload();
        }

        $this->model->update($id, $data);

        return $this->answerSuccess($this->model->find($id));
    }

    public function delete($id = null): ResponseInterface
    {
        $user = $this->model->find($id);

        if (!$user) {
            return $this->answerRecordNotFound();
        }

        $this->model->delete($id);

        return $this->answerNoContent();
    }
}
```

> **Note:** CodeIgniter 4 supports PSR-7 through the `codeigniter4/psr7bridge` package for full compatibility.

## Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test:coverage

# Static analysis
composer phpstan

# Code style check
composer phpcs

# Fix code style
composer phpcs:fix
```

## Security

If you discover any security-related issues, please email dersonsena@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
