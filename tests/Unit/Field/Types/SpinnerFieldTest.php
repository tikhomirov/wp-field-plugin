<?php

declare(strict_types=1);

use WpField\Field\Types\SpinnerField;

beforeEach(function (): void {
    $this->field = new SpinnerField('test_spinner');
});

it('renders with custom class', function (): void {
    $this->field->class('custom-spinner-class');
    $html = $this->field->render();

    expect($html)->toContain('custom-spinner-class');
});

it('renders with description', function (): void {
    $this->field->description('Select a number');
    $html = $this->field->render();

    expect($html)->toContain('Select a number');
});
