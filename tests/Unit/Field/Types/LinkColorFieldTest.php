<?php

declare(strict_types=1);

use WpField\Field\Types\LinkColorField;

beforeEach(function (): void {
    $this->field = new LinkColorField('test_link_color');
});

it('can set states', function (): void {
    $states = ['normal', 'hover', 'active', 'focus'];
    $this->field->states($states);

    expect($this->field->getAttribute('states'))->toBe($states);
});

it('states is chainable', function (): void {
    $result = $this->field->states(['normal', 'hover']);

    expect($result)->toBe($this->field);
});

it('renders link color with default states', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-link-color')
        ->and($html)->toContain('Normal')
        ->and($html)->toContain('Hover')
        ->and($html)->toContain('Active');
});

it('renders link color with custom states', function (): void {
    $this->field->states(['normal', 'hover', 'focus']);
    $html = $this->field->render();

    expect($html)->toContain('Normal')
        ->and($html)->toContain('Hover')
        ->and($html)->toContain('Focus');
});

it('renders link color with label', function (): void {
    $this->field->label('Link Colors');
    $html = $this->field->render();

    expect($html)->toContain('Link Colors')
        ->and($html)->toContain('<label');
});

it('renders link color with description', function (): void {
    $this->field->description('Set link colors for different states');
    $html = $this->field->render();

    expect($html)->toContain('Set link colors for different states');
});

it('renders link color with values', function (): void {
    $this->field->value(['normal' => '#ff0000', 'hover' => '#00ff00']);
    $html = $this->field->render();

    expect($html)->toContain('#ff0000')
        ->and($html)->toContain('#00ff00');
});

it('sanitizes link color array', function (): void {
    $input = ['normal' => '#ff0000', 'hover' => '#00ff00'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized['normal'])->toBe('#ff0000');
});

it('sanitizes trims values', function (): void {
    $input = ['normal' => '  #ff0000  '];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['normal'])->toBe('#ff0000');
});

it('sanitizes filters HTML', function (): void {
    $input = ['normal' => '<script>alert("xss")</script>#ff0000'];

    $sanitized = $this->field->sanitize($input);

    expect($sanitized['normal'])->not->toContain('<script>');
});

it('sanitizes non-array to defaults', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBeArray()
        ->and($sanitized)->toHaveKey('normal');
});

it('validates valid link color array', function (): void {
    $input = ['normal' => '#ff0000', 'hover' => '#00ff00'];

    expect($this->field->validate($input))->toBeTrue();
});

it('validates empty array when not required', function (): void {
    expect($this->field->validate([]))->toBeTrue();
});

it('validates non-array when not required', function (): void {
    expect($this->field->validate('invalid'))->toBeTrue();
});

it('validates non-array when required', function (): void {
    $this->field->required();

    expect($this->field->validate('invalid'))->toBeFalse();
});

it('validates array with string values', function (): void {
    $input = ['normal' => '#ff0000', 'hover' => '#00ff00'];

    expect($this->field->validate($input))->toBeTrue();
});

it('handles empty states array with defaults', function (): void {
    $this->field->states([]);
    $html = $this->field->render();

    expect($html)->toContain('Normal');
});

it('handles non-array states with defaults', function (): void {
    $this->field->attribute('states', 'invalid');
    $html = $this->field->render();

    expect($html)->toContain('Normal');
});

it('normalizes state names to lowercase', function (): void {
    $this->field->states(['NORMAL', 'Hover', 'ACTIVE']);
    $html = $this->field->render();

    expect($html)->toContain('normal')
        ->and($html)->toContain('hover')
        ->and($html)->toContain('active');
});

it('normalizes value filters non-scalar values', function (): void {
    $this->field->value(['normal' => '#ff0000', 'hover' => ['invalid']]);
    $html = $this->field->render();

    expect($html)->toContain('#ff0000')
        ->and($html)->toContain('value=""');
});

it('resolves states filters non-scalar states', function (): void {
    $this->field->states(['normal', ['invalid'], 'hover']);
    $html = $this->field->render();

    expect($html)->toContain('normal')
        ->and($html)->toContain('hover');
});
