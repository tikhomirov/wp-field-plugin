<?php

declare(strict_types=1);

use WpField\Field\Types\SelectField;

beforeEach(function (): void {
    $this->field = new SelectField('test_select');
});

it('can set multiple attribute', function (): void {
    $this->field->multiple(true);

    expect($this->field->getAttribute('multiple'))->toBeTrue();
});

it('multiple is chainable', function (): void {
    $result = $this->field->multiple(true);

    expect($result)->toBe($this->field);
});

it('renders with label', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->label('Choose Option');
    $html = $this->field->render();

    expect($html)->toContain('Choose Option')
        ->and($html)->toContain('<label');
});

it('renders with description', function (): void {
    $this->field->options(['opt1' => 'Option 1']);
    $this->field->description('Select one option');
    $html = $this->field->render();

    expect($html)->toContain('Select one option');
});

it('renders multiple select with array value', function (): void {
    $this->field->options(['opt1' => 'Option 1', 'opt2' => 'Option 2']);
    $this->field->multiple(true);
    $this->field->value(['opt1', 'opt2']);
    $html = $this->field->render();

    expect($html)->toContain('multiple="multiple"')
        ->and($html)->toContain('name="test_select[]"')
        ->and($html)->toContain('selected');
});
