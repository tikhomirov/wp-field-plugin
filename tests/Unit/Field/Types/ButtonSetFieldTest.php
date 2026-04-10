<?php

declare(strict_types=1);

use WpField\Field\Types\ButtonSetField;

beforeEach(function (): void {
    $this->field = new ButtonSetField('test_button_set');
});

it('can set multiple mode', function (): void {
    $this->field->multiple(true);

    expect($this->field->getAttribute('multiple'))->toBeTrue();
});

it('multiple is chainable', function (): void {
    $result = $this->field->multiple(true);

    expect($result)->toBe($this->field);
});

it('renders button set with options', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-button-set')
        ->and($html)->toContain('Option 1')
        ->and($html)->toContain('Option 2');
});

it('renders button set with radio inputs by default', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $html = $this->field->render();

    expect($html)->toContain('type="radio"');
});

it('renders button set with checkbox inputs in multiple mode', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->multiple(true);
    $html = $this->field->render();

    expect($html)->toContain('type="checkbox"');
});

it('renders button set with checked option', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $this->field->value('opt2');
    $html = $this->field->render();

    expect($html)->toContain('checked')
        ->and($html)->toContain('active');
});

it('renders button set with custom class', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->class('custom-class');
    $html = $this->field->render();

    expect($html)->toContain('custom-class');
});

it('renders button set with description', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->description('Select an option');
    $html = $this->field->render();

    expect($html)->toContain('Select an option');
});

it('renders message when no options provided', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('No options provided');
});
