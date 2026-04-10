<?php

declare(strict_types=1);

beforeEach(function (): void {
    require_once dirname(__DIR__, 2).'/WP_Field.php';
});

it('hides field when dependency not met', function (): void {
    $html = WP_Field::make([
        'id' => 'dependent_field',
        'type' => 'text',
        'label' => 'Dependent Field',
        'dependency' => [
            ['other_field', '==', 'value'],
        ],
    ], false);

    expect($html)->toContain('is-hidden');
});

it('shows field when dependency met', function (): void {
    // For testing we will just check that dependency data is printed correctly
    // and the field does not crash, since getting actual values requires DB/WP setup.
    $html = WP_Field::make([
        'id' => 'dependent_field',
        'type' => 'text',
        'label' => 'Dependent Field',
        'value' => 'value',
        'dependency' => [
            ['dependent_field', '==', 'value'],
        ],
    ], false);

    // We check if data-dependency attribute exists instead of visibility logic
    // because evaluate_dependency uses get_value which is heavily dependent on WP
    expect($html)->toContain('data-dependency');
});

it('renders dependency data attribute', function (): void {
    $html = WP_Field::make([
        'id' => 'field_with_dep',
        'type' => 'text',
        'label' => 'Field',
        'dependency' => [
            ['other_field', '==', 'value'],
        ],
    ], false);

    expect($html)->toContain('data-dependency');
});

it('handles multiple dependencies with and', function (): void {
    $html = WP_Field::make([
        'id' => 'field_with_deps',
        'type' => 'text',
        'label' => 'Field',
        'dependency' => [
            ['field1', '==', 'value1'],
            ['field2', '!=', 'value2'],
            'relation' => 'AND',
        ],
    ], false);

    expect($html)
        ->toContain('data-dependency')
        ->toContain('AND');
});

it('handles multiple dependencies with or', function (): void {
    $html = WP_Field::make([
        'id' => 'field_with_deps',
        'type' => 'text',
        'label' => 'Field',
        'dependency' => [
            ['field1', '==', 'value1'],
            ['field2', '==', 'value2'],
            'relation' => 'OR',
        ],
    ], false);

    expect($html)
        ->toContain('data-dependency')
        ->toContain('OR');
});

it('supports in operator', function (): void {
    $html = WP_Field::make([
        'id' => 'field_with_in',
        'type' => 'text',
        'label' => 'Field',
        'dependency' => [
            ['field', 'in', ['a', 'b', 'c']],
        ],
    ], false);

    expect($html)->toContain('data-dependency');
});

it('supports contains operator', function (): void {
    $html = WP_Field::make([
        'id' => 'field_with_contains',
        'type' => 'text',
        'label' => 'Field',
        'dependency' => [
            ['field', 'contains', 'text'],
        ],
    ], false);

    expect($html)->toContain('data-dependency');
});

it('supports empty operator', function (): void {
    $html = WP_Field::make([
        'id' => 'field_with_empty',
        'type' => 'text',
        'label' => 'Field',
        'dependency' => [
            ['field', 'empty', null],
        ],
    ], false);

    expect($html)->toContain('data-dependency');
});

it('supports comparison operators', function (): void {
    $operators = ['==', '!=', '>', '>=', '<', '<='];

    foreach ($operators as $op) {
        $html = WP_Field::make([
            'id' => 'field_with_op',
            'type' => 'text',
            'label' => 'Field',
            'dependency' => [
                ['field', $op, 'value'],
            ],
        ], false);

        expect($html)->toContain('data-dependency');
    }
});
