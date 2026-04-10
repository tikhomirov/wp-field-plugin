<?php

declare(strict_types=1);

use WpField\Field\Types\IconField;

beforeEach(function (): void {
    $this->field = new IconField('test_icon');
});

it('can set library', function (): void {
    $this->field->library('fontawesome');

    expect($this->field->getAttribute('library'))->toBe('fontawesome');
});

it('library is chainable', function (): void {
    $result = $this->field->library('dashicons');

    expect($result)->toBe($this->field);
});

it('renders icon field HTML', function (): void {
    $html = $this->field->render();

    expect($html)->toBeString()
        ->and($html)->toContain('wp-field-icon-wrapper')
        ->and($html)->toContain('type="text"')
        ->and($html)->toContain('name="test_icon"')
        ->and($html)->toContain('data-library="dashicons"');
});

it('renders with custom library', function (): void {
    $this->field->library('custom');
    $html = $this->field->render();

    expect($html)->toContain('data-library="custom"');
});

it('renders with label', function (): void {
    $this->field->label('Test Icon');
    $html = $this->field->render();

    expect($html)->toContain('<label')
        ->and($html)->toContain('Test Icon');
});

it('renders with description', function (): void {
    $this->field->description('Choose an icon');
    $html = $this->field->render();

    expect($html)->toContain('description')
        ->and($html)->toContain('Choose an icon');
});

it('renders with value', function (): void {
    $this->field->value('dashicons-admin-site');
    $html = $this->field->render();

    expect($html)->toContain('value="dashicons-admin-site"');
});

it('sanitizes string value', function (): void {
    $sanitized = $this->field->sanitize('dashicons-admin-site');

    expect($sanitized)->toBe('dashicons-admin-site');
});

it('sanitizes value with extra spaces', function (): void {
    $sanitized = $this->field->sanitize('  dashicons-admin-site  ');

    expect($sanitized)->toBe('dashicons-admin-site');
});

it('sanitizes non-scalar value to empty string', function (): void {
    $sanitized = $this->field->sanitize(['invalid']);

    expect($sanitized)->toBe('');
});

it('sanitizes null to empty string', function (): void {
    $sanitized = $this->field->sanitize(null);

    expect($sanitized)->toBe('');
});

it('sanitizes HTML tags', function (): void {
    $sanitized = $this->field->sanitize('<script>alert("xss")</script>');

    expect($sanitized)->not->toContain('<script>');
});
