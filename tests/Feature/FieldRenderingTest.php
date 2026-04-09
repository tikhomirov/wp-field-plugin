<?php

declare(strict_types=1);

beforeEach(function (): void {
    require_once dirname(__DIR__, 2).'/WP_Field.php';
});

it('renders text field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_text',
        'type' => 'text',
        'label' => 'Test Text',
    ], false);

    expect($html)
        ->toContain('wp-field')
        ->toContain('test_text')
        ->toContain('Test Text')
        ->toContain('type="text"');
});

it('renders select field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_select',
        'type' => 'select',
        'label' => 'Test Select',
        'options' => ['a' => 'Option A', 'b' => 'Option B'],
    ], false);

    expect($html)
        ->toContain('<select')
        ->toContain('Option A')
        ->toContain('Option B')
        ->toContain('test_select');
});

it('renders radio field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_radio',
        'type' => 'radio',
        'label' => 'Test Radio',
        'options' => ['yes' => 'Yes', 'no' => 'No'],
    ], false);

    expect($html)
        ->toContain('type="radio"')
        ->toContain('Yes')
        ->toContain('No');
});

it('renders checkbox field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_checkbox',
        'type' => 'checkbox',
        'label' => 'Test Checkbox',
    ], false);

    expect($html)
        ->toContain('type="checkbox"')
        ->toContain('test_checkbox');
});

it('renders textarea field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_textarea',
        'type' => 'textarea',
        'label' => 'Test Textarea',
    ], false);

    expect($html)
        ->toContain('<textarea')
        ->toContain('test_textarea');
});

it('renders number field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_number',
        'type' => 'number',
        'label' => 'Test Number',
        'min' => 0,
        'max' => 100,
    ], false);

    expect($html)
        ->toContain('type="number"')
        ->toContain('min="0"')
        ->toContain('max="100"');
});

it('renders email field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_email',
        'type' => 'email',
        'label' => 'Test Email',
    ], false);

    expect($html)
        ->toContain('type="email"')
        ->toContain('test_email');
});

it('renders color field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_color',
        'type' => 'color',
        'label' => 'Test Color',
    ], false);

    expect($html)
        ->toContain('wp-color-picker-field')
        ->toContain('test_color');
});

it('renders date field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_date',
        'type' => 'date',
        'label' => 'Test Date',
    ], false);

    expect($html)
        ->toContain('type="date"')
        ->toContain('test_date');
});

it('renders time field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_time',
        'type' => 'time',
        'label' => 'Test Time',
    ], false);

    expect($html)
        ->toContain('type="time"')
        ->toContain('test_time');
});

it('renders field with placeholder', function (): void {
    $html = WP_Field::make([
        'id' => 'test_field',
        'type' => 'text',
        'label' => 'Test',
        'placeholder' => 'Enter value',
    ], false);

    expect($html)->toContain('placeholder="Enter value"');
});

it('renders field with description', function (): void {
    $html = WP_Field::make([
        'id' => 'test_field',
        'type' => 'text',
        'label' => 'Test',
        'desc' => 'This is a description',
    ], false);

    expect($html)
        ->toContain('This is a description')
        ->toContain('description');
});

it('renders field with custom class', function (): void {
    $html = WP_Field::make([
        'id' => 'test_field',
        'type' => 'text',
        'label' => 'Test',
        'class' => 'my-custom-class',
    ], false);

    expect($html)->toContain('my-custom-class');
});

it('renders field with custom attributes', function (): void {
    $html = WP_Field::make([
        'id' => 'test_field',
        'type' => 'text',
        'label' => 'Test',
        'custom_attributes' => ['data-test' => 'value', 'aria-label' => 'Test Label'],
    ], false);

    expect($html)
        ->toContain('data-test="value"')
        ->toContain('aria-label="Test Label"');
});

it('renders readonly field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_field',
        'type' => 'text',
        'label' => 'Test',
        'readonly' => true,
    ], false);

    expect($html)->toContain('readonly');
});

it('renders disabled field', function (): void {
    $html = WP_Field::make([
        'id' => 'test_field',
        'type' => 'text',
        'label' => 'Test',
        'disabled' => true,
    ], false);

    expect($html)->toContain('disabled');
});
