<?php

declare(strict_types=1);

use WpField\Field\Types\LinkField;

beforeEach(function (): void {
    $this->field = new LinkField('test_link');
});

it('renders link field with url input', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-link')
        ->and($html)->toContain('name="test_link[url]"');
});

it('renders link field with label', function (): void {
    $this->field->label('Link Label');
    $html = $this->field->render();

    expect($html)->toContain('Link Label')
        ->and($html)->toContain('<label');
});

it('renders link field with description', function (): void {
    $this->field->description('Enter link details');
    $html = $this->field->render();

    expect($html)->toContain('Enter link details');
});

it('sanitizes non-array value to defaults', function (): void {
    $sanitized = $this->field->sanitize('invalid');

    expect($sanitized)->toBeArray()
        ->and($sanitized['url'])->toBe('')
        ->and($sanitized['text'])->toBe('')
        ->and($sanitized['target'])->toBe('_self');
});

it('sanitizes url with filter', function (): void {
    $sanitized = $this->field->sanitize(['url' => 'https://example.com', 'text' => 'Test', 'target' => '_blank']);

    expect($sanitized['url'])->toBe('https://example.com')
        ->and($sanitized['text'])->toBe('Test')
        ->and($sanitized['target'])->toBe('_blank');
});

it('sanitizes simple url string', function (): void {
    $sanitized = $this->field->sanitize(['url' => 'invalid-url', 'text' => 'Test', 'target' => '_self']);

    expect($sanitized['url'])->toBe('invalid-url');
});

it('sanitizes target to _self when not _blank', function (): void {
    $sanitized = $this->field->sanitize(['url' => 'https://example.com', 'text' => 'Test', 'target' => '_custom']);

    expect($sanitized['target'])->toBe('_self');
});

it('renders with selected target _blank', function (): void {
    $this->field->value(['url' => 'https://example.com', 'text' => 'Test', 'target' => '_blank']);
    $html = $this->field->render();

    expect($html)->toContain('value="_blank" selected');
});

it('renders with selected target _self', function (): void {
    $this->field->value(['url' => 'https://example.com', 'text' => 'Test', 'target' => '_self']);
    $html = $this->field->render();

    expect($html)->toContain('value="_self" selected');
});
