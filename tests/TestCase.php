<?php

namespace Tests;

use App\Exceptions\Handler;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(Throwable $e) {}
            public function render($request, Throwable $e) {
                throw $e;
            }
        });
    }
}
