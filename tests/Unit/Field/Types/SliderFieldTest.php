<?php

declare(strict_types=1);

use WpField\Field\Types\SliderField;

beforeEach(function (): void {
    $this->field = new SliderField('test_slider');
});

it('renders with custom class', function (): void {
    $this->field->class('custom-slider-class');
    $html = $this->field->render();

    expect($html)->toContain('custom-slider-class');
});

it('renders with description', function (): void {
    $this->field->description('Select a value between 0 and 100');
    $html = $this->field->render();

    expect($html)->toContain('Select a value between 0 and 100');
});
