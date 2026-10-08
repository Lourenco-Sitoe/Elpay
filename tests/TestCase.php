<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // O sqlite dos testes congela o ENUM original (CHECK) das migrations
        // antigas; o MySQL já tem os novos valores (draft, app...). Relaxar o
        // CHECK só em sqlite para os testes cobrirem o código real.
        try {
            if (\DB::getDriverName() === 'sqlite') {
                \DB::statement('PRAGMA ignore_check_constraints=ON');
            }
        } catch (\Throwable) {
        }
    }
}
