<?php

declare(strict_types=1);

use WpField\Conditional\ConditionalLogic;

it('returns true for empty conditions', function (): void {
    expect(ConditionalLogic::evaluate([], ['field' => 'value']))->toBeTrue();
});

it('evaluates equality operator ==', function (): void {
    $conditions = [['field' => 'test', 'operator' => '==', 'value' => 'value']];
    $values = ['test' => 'value'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates inequality operator !=', function (): void {
    $conditions = [['field' => 'test', 'operator' => '!=', 'value' => 'other']];
    $values = ['test' => 'value'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates strict equality operator ===', function (): void {
    $conditions = [['field' => 'test', 'operator' => '===', 'value' => 'value']];
    $values = ['test' => 'value'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates strict inequality operator !==', function (): void {
    $conditions = [['field' => 'test', 'operator' => '!==', 'value' => 'other']];
    $values = ['test' => 'value'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates greater than operator >', function (): void {
    $conditions = [['field' => 'test', 'operator' => '>', 'value' => 5]];
    $values = ['test' => 10];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates greater than or equal operator >=', function (): void {
    $conditions = [['field' => 'test', 'operator' => '>=', 'value' => 10]];
    $values = ['test' => 10];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates less than operator <', function (): void {
    $conditions = [['field' => 'test', 'operator' => '<', 'value' => 15]];
    $values = ['test' => 10];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates less than or equal operator <=', function (): void {
    $conditions = [['field' => 'test', 'operator' => '<=', 'value' => 10]];
    $values = ['test' => 10];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates contains operator', function (): void {
    $conditions = [['field' => 'test', 'operator' => 'contains', 'value' => 'world']];
    $values = ['test' => 'hello world'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates not_contains operator', function (): void {
    $conditions = [['field' => 'test', 'operator' => 'not_contains', 'value' => 'foo']];
    $values = ['test' => 'hello world'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates starts_with operator', function (): void {
    $conditions = [['field' => 'test', 'operator' => 'starts_with', 'value' => 'hello']];
    $values = ['test' => 'hello world'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates ends_with operator', function (): void {
    $conditions = [['field' => 'test', 'operator' => 'ends_with', 'value' => 'world']];
    $values = ['test' => 'hello world'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates in operator', function (): void {
    $conditions = [['field' => 'test', 'operator' => 'in', 'value' => ['a', 'b', 'c']]];
    $values = ['test' => 'b'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates not_in operator', function (): void {
    $conditions = [['field' => 'test', 'operator' => 'not_in', 'value' => ['a', 'b', 'c']]];
    $values = ['test' => 'd'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates empty operator', function (): void {
    $conditions = [['field' => 'test', 'operator' => 'empty', 'value' => null]];
    $values = ['test' => ''];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates not_empty operator', function (): void {
    $conditions = [['field' => 'test', 'operator' => 'not_empty', 'value' => null]];
    $values = ['test' => 'value'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeTrue();
});

it('evaluates default operator as false', function (): void {
    $conditions = [['field' => 'test', 'operator' => 'invalid', 'value' => 'value']];
    $values = ['test' => 'value'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeFalse();
});

it('evaluates AND relation correctly', function (): void {
    $conditions = [
        ['field' => 'test1', 'operator' => '==', 'value' => 'value1'],
        ['field' => 'test2', 'operator' => '==', 'value' => 'value2'],
    ];
    $values = ['test1' => 'value1', 'test2' => 'value2'];

    expect(ConditionalLogic::evaluate($conditions, $values, 'AND'))->toBeTrue();
});

it('evaluates AND relation with false condition', function (): void {
    $conditions = [
        ['field' => 'test1', 'operator' => '==', 'value' => 'value1'],
        ['field' => 'test2', 'operator' => '==', 'value' => 'wrong'],
    ];
    $values = ['test1' => 'value1', 'test2' => 'value2'];

    expect(ConditionalLogic::evaluate($conditions, $values, 'AND'))->toBeFalse();
});

it('evaluates OR relation correctly', function (): void {
    $conditions = [
        ['field' => 'test1', 'operator' => '==', 'value' => 'value1'],
        ['field' => 'test2', 'operator' => '==', 'value' => 'wrong'],
    ];
    $values = ['test1' => 'value1', 'test2' => 'value2'];

    expect(ConditionalLogic::evaluate($conditions, $values, 'OR'))->toBeTrue();
});

it('evaluates OR relation with all false conditions', function (): void {
    $conditions = [
        ['field' => 'test1', 'operator' => '==', 'value' => 'wrong1'],
        ['field' => 'test2', 'operator' => '==', 'value' => 'wrong2'],
    ];
    $values = ['test1' => 'value1', 'test2' => 'value2'];

    expect(ConditionalLogic::evaluate($conditions, $values, 'OR'))->toBeFalse();
});

it('handles missing field in values', function (): void {
    $conditions = [['field' => 'missing', 'operator' => '==', 'value' => 'value']];
    $values = ['other' => 'value'];

    expect(ConditionalLogic::evaluate($conditions, $values))->toBeFalse();
});

it('shouldDisplay delegates to evaluate', function (): void {
    $conditions = [['field' => 'test', 'operator' => '==', 'value' => 'value']];
    $values = ['test' => 'value'];

    expect(ConditionalLogic::shouldDisplay($conditions, $values))->toBeTrue();
});

it('shouldSave delegates to evaluate', function (): void {
    $conditions = [['field' => 'test', 'operator' => '==', 'value' => 'value']];
    $values = ['test' => 'value'];

    expect(ConditionalLogic::shouldSave($conditions, $values))->toBeTrue();
});
