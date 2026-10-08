<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

it('applies and rolls back only the additive business migrations', function () {
    expect(Artisan::call('migrate:fresh', ['--force' => true]))->toBe(0)
        ->and(Schema::hasTable('employees'))->toBeTrue()
        ->and(Schema::hasTable('salary_records'))->toBeTrue();

    expect(Artisan::call('migrate:rollback', ['--step' => 3, '--force' => true]))->toBe(0)
        ->and(Schema::hasTable('salary_records'))->toBeFalse()
        ->and(Schema::hasTable('employees'))->toBeFalse()
        ->and(Schema::hasTable('users'))->toBeTrue();

    expect(Artisan::call('migrate', ['--force' => true]))->toBe(0)
        ->and(Schema::hasTable('employees'))->toBeTrue()
        ->and(Schema::hasTable('salary_records'))->toBeTrue();
});
