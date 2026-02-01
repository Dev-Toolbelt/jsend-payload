# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A PHP library implementing the [JSend specification](https://github.com/omniti-labs/jsend) for API responses. Provides a trait (`AnswerTrait`) that can be mixed into controllers or handlers to generate standardized JSON responses with PSR-7 `ResponseInterface`.

## Commands

```bash
# Run tests
composer test

# Run tests with coverage report
composer test:coverage

# Run a single test file
./vendor/bin/phpunit --configuration tests/phpunit.xml tests/Unit/AnswerTraitTest.php

# Run a single test method
./vendor/bin/phpunit --configuration tests/phpunit.xml --filter testAnswerSuccessReturnsJsendSuccessPayload

# Static analysis (PHPStan level 6)
composer phpstan

# Code style check
composer phpcs

# Code style fix
composer phpcs:fix
```

## Architecture

- **`src/AnswerTrait.php`**: Main trait providing JSend response methods (`answerSuccess`, `answerFail`, `answerError`, etc.)
- **`src/Enums/JsendStatus.php`**: Enum with JSend status values (`success`, `fail`, `error`)

The trait uses `nyholm/psr7` for PSR-7 response creation and `dev-toolbelt/enums` for HTTP status codes.

## Code Style

- PSR-12 with strict types declaration
- Line limit: 120 characters (absolute: 140)
- Short array syntax
- Imports ordered by length
