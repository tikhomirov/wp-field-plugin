<?php

declare(strict_types=1);

use WpField\Field\Types\RadioField;

beforeEach(function (): void {
    $this->field = new RadioField('test_radio');
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

it('resolves options from string with newlines', function (): void {
    $this->field->attribute('options', "Option 1\nOption 2\nOption 3");
    $html = $this->field->render();

    expect($html)->toContain('Option 1')
        ->and($html)->toContain('Option 2')
        ->and($html)->toContain('Option 3');
});
