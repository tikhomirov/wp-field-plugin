<?php

declare(strict_types=1);

use WpField\Field\Types\CodeEditorField;

beforeEach(function (): void {
    $this->field = new CodeEditorField('test_code_editor');
});

it('can set mode', function (): void {
    $this->field->mode('javascript');

    expect($this->field->getAttribute('mode'))->toBe('javascript');
});

it('mode is chainable', function (): void {
    $result = $this->field->mode('css');

    expect($result)->toBe($this->field);
});

it('renders code editor with default mode', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-code-editor')
        ->and($html)->toContain('data-mode="text/html"');
});

it('renders code editor with custom mode', function (): void {
    $this->field->mode('javascript');
    $html = $this->field->render();

    expect($html)->toContain('data-mode="javascript"');
});

it('renders code editor with default rows', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('rows="12"');
});

it('renders code editor with label', function (): void {
    $this->field->label('Code Editor');
    $html = $this->field->render();

    expect($html)->toContain('Code Editor')
        ->and($html)->toContain('<label');
});

it('renders code editor with description', function (): void {
    $this->field->description('Enter your code here');
    $html = $this->field->render();

    expect($html)->toContain('Enter your code here');
});

it('renders code editor with value', function (): void {
    $this->field->value('test code');
    $html = $this->field->render();

    expect($html)->toContain('test code');
});

it('sanitizes string value', function (): void {
    $sanitized = $this->field->sanitize('  code  ');

    expect($sanitized)->toBe('code');
});

it('sanitizes filters HTML', function (): void {
    $sanitized = $this->field->sanitize('<script>alert("xss")</script>');

    expect($sanitized)->not->toContain('<script>');
});

it('sanitizes returns empty string for non-scalar value', function (): void {
    $sanitized = $this->field->sanitize(['invalid']);

    expect($sanitized)->toBe('');
});
