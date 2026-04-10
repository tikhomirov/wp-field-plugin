<?php

declare(strict_types=1);

use WpField\Traits\HasConditionals;

class TestHasConditionalsClass
{
    use HasConditionals;
}

beforeEach(function (): void {
    $this->instance = new TestHasConditionalsClass;
});

it('can add when condition with AND logic', function (): void {
    $this->instance->when('field_name', '=', 'value');

    $conditions = $this->instance->getConditions();

    expect($conditions)->toHaveCount(1)
        ->and($conditions[0])->toBe([
            'field' => 'field_name',
            'operator' => '=',
            'value' => 'value',
            'logic' => 'AND',
        ]);
});

it('can add orWhen condition with OR logic', function (): void {
    $this->instance->orWhen('field_name', '!=', 'value');

    $conditions = $this->instance->getConditions();

    expect($conditions)->toHaveCount(1)
        ->and($conditions[0])->toBe([
            'field' => 'field_name',
            'operator' => '!=',
            'value' => 'value',
            'logic' => 'OR',
        ]);
});

it('can chain multiple conditions', function (): void {
    $this->instance
        ->when('field1', '=', 'value1')
        ->when('field2', '>', 'value2')
        ->orWhen('field3', '<', 'value3');

    $conditions = $this->instance->getConditions();

    expect($conditions)->toHaveCount(3)
        ->and($conditions[0]['logic'])->toBe('AND')
        ->and($conditions[1]['logic'])->toBe('AND')
        ->and($conditions[2]['logic'])->toBe('OR');
});

it('can check if has conditions', function (): void {
    expect($this->instance->hasConditions())->toBeFalse();

    $this->instance->when('field', '=', 'value');

    expect($this->instance->hasConditions())->toBeTrue();
});

it('when is chainable', function (): void {
    $result = $this->instance->when('field', '=', 'value');

    expect($result)->toBe($this->instance);
});

it('orWhen is chainable', function (): void {
    $result = $this->instance->orWhen('field', '=', 'value');

    expect($result)->toBe($this->instance);
});
