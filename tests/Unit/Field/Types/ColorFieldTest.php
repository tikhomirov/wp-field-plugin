<?php

declare(strict_types=1);

use WpField\Field\Types\ColorField;

beforeEach(function (): void {
    $this->field = new ColorField('test_color');
});

it('can set alpha transparency', function (): void {
    $this->field->alpha(true);

    expect($this->field->getAttribute('alpha'))->toBeTrue();
});

it('can disable alpha transparency', function (): void {
    $this->field->alpha(false);

    expect($this->field->getAttribute('alpha'))->toBeFalse();
});

it('renders with label by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('test_color')
        ->and($html)->toContain('<label');
});

it('renders with description', function (): void {
    $this->field->description('Choose a color');
    $html = $this->field->render();

    expect($html)->toContain('Choose a color');
});

it('sanitizes non-scalar value to empty string', function (): void {
    $sanitized = $this->field->sanitize(['invalid']);

    expect($sanitized)->toBe('');
});

it('renders with alpha enabled by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('data-alpha="true"');
});

it('renders with alpha disabled when set to false', function (): void {
    $this->field->alpha(false);
    $html = $this->field->render();

    expect($html)->toContain('data-alpha="false"');
});

it('renders with default color', function (): void {
    $this->field->attribute('default', '#ffffff');
    $html = $this->field->render();

    expect($html)->toContain('data-default-color="#ffffff"');
});
