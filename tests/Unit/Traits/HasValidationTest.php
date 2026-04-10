<?php

declare(strict_types=1);

use WpField\Traits\HasValidation;

class TestHasValidationClass
{
    use HasValidation;
}

beforeEach(function (): void {
    $this->instance = new TestHasValidationClass;
});

it('can set required', function (): void {
    $this->instance->required();

    expect($this->instance->isRequired())->toBeTrue()
        ->and($this->instance->getValidationRules()['required'])->toBeTrue();
});

it('can set required with false', function (): void {
    $this->instance->required(false);

    expect($this->instance->isRequired())->toBeFalse()
        ->and($this->instance->getValidationRules()['required'])->toBeFalse();
});

it('can set min', function (): void {
    $this->instance->min(10);

    expect($this->instance->getValidationRules()['min'])->toBe(10);
});

it('can set max', function (): void {
    $this->instance->max(100);

    expect($this->instance->getValidationRules()['max'])->toBe(100);
});

it('can set pattern', function (): void {
    $pattern = '/^[a-z]+$/';
    $this->instance->pattern($pattern);

    expect($this->instance->getValidationRules()['pattern'])->toBe($pattern);
});

it('can set email validation', function (): void {
    $this->instance->email();

    expect($this->instance->getValidationRules()['email'])->toBeTrue();
});

it('can set url validation', function (): void {
    $this->instance->url();

    expect($this->instance->getValidationRules()['url'])->toBeTrue();
});

it('can get validation rules', function (): void {
    $this->instance
        ->required()
        ->min(5)
        ->max(100)
        ->pattern('/^[a-z]+$/')
        ->email()
        ->url();

    $rules = $this->instance->getValidationRules();

    expect($rules)->toBe([
        'required' => true,
        'min' => 5,
        'max' => 100,
        'pattern' => '/^[a-z]+$/',
        'email' => true,
        'url' => true,
    ]);
});

it('validation methods are chainable', function (): void {
    $result = $this->instance
        ->required()
        ->min(5)
        ->max(100)
        ->pattern('/^[a-z]+$/')
        ->email()
        ->url();

    expect($result)->toBe($this->instance);
});
