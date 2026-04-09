<?php

declare(strict_types=1);

beforeEach(function (): void {
    require_once dirname(__DIR__, 2).'/WP_Field.php';
});

it('renders group field', function (): void {
    $html = \WP_Field::make([
        'id' => 'address',
        'type' => 'group',
        'label' => 'Address',
        'fields' => [
            ['id' => 'city', 'type' => 'text', 'label' => 'City'],
            ['id' => 'street', 'type' => 'text', 'label' => 'Street'],
        ],
    ], false);

    expect($html)
        ->toContain('wp-field-group')
        ->toContain('City')
        ->toContain('Street');
});

it('renders repeater field', function (): void {
    $html = \WP_Field::make([
        'id' => 'work_times',
        'type' => 'repeater',
        'label' => 'Work Times',
        'min' => 1,
        'max' => 7,
        'fields' => [
            ['id' => 'day', 'type' => 'select', 'label' => 'Day', 'options' => ['mon' => 'Monday']],
            ['id' => 'from', 'type' => 'time', 'label' => 'From'],
        ],
    ], false);

    expect($html)
        ->toContain('wp-field-repeater')
        ->toContain('wp-field-repeater-add')
        ->toContain('Monday');
});

it('renders repeater with min max', function (): void {
    $html = \WP_Field::make([
        'id' => 'items',
        'type' => 'repeater',
        'label' => 'Items',
        'min' => 2,
        'max' => 5,
        'fields' => [
            ['id' => 'name', 'type' => 'text', 'label' => 'Name'],
        ],
    ], false);

    expect($html)
        ->toContain('data-min="2"')
        ->toContain('data-max="5"');
});

it('renders repeater add button', function (): void {
    $html = \WP_Field::make([
        'id' => 'items',
        'type' => 'repeater',
        'label' => 'Items',
        'add_text' => 'Add Item',
        'fields' => [
            ['id' => 'name', 'type' => 'text', 'label' => 'Name'],
        ],
    ], false);

    expect($html)
        ->toContain('Add Item')
        ->toContain('wp-field-repeater-add');
});

it('renders group with nested fields', function (): void {
    $html = \WP_Field::make([
        'id' => 'contact',
        'type' => 'group',
        'label' => 'Contact',
        'fields' => [
            ['id' => 'name', 'type' => 'text', 'label' => 'Name'],
            ['id' => 'email', 'type' => 'email', 'label' => 'Email'],
            ['id' => 'phone', 'type' => 'tel', 'label' => 'Phone'],
        ],
    ], false);

    expect($html)
        ->toContain('wp-field-group')
        ->toContain('Name')
        ->toContain('Email')
        ->toContain('Phone');
});

it('renders repeater with select options', function (): void {
    $html = \WP_Field::make([
        'id' => 'schedule',
        'type' => 'repeater',
        'label' => 'Schedule',
        'fields' => [
            [
                'id' => 'day',
                'type' => 'select',
                'label' => 'Day',
                'options' => ['mon' => 'Monday', 'tue' => 'Tuesday'],
            ],
        ],
    ], false);

    expect($html)
        ->toContain('Monday')
        ->toContain('Tuesday');
});
