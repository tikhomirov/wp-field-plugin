<?php

declare(strict_types=1);

use WpField\Field\Types\FileField;

beforeEach(function (): void {
    $this->field = new FileField('test_file');
});

it('can set button text', function (): void {
    $this->field->buttonText('Upload File');

    expect($this->field->getAttribute('button_text'))->toBe('Upload File');
});

it('buttonText is chainable', function (): void {
    $result = $this->field->buttonText('Upload');

    expect($result)->toBe($this->field);
});

it('can set library type', function (): void {
    $this->field->library('image');

    expect($this->field->getAttribute('library'))->toBe('image');
});

it('library is chainable', function (): void {
    $result = $this->field->library('video');

    expect($result)->toBe($this->field);
});

it('renders file field with url input by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-file-wrapper')
        ->and($html)->toContain('wp-field-file-url')
        ->and($html)->toContain('type="text"');
});

it('renders file field without url input when url is false', function (): void {
    $this->field->attribute('url', false);
    $html = $this->field->render();

    expect($html)->not->toContain('wp-field-file-url');
});

it('renders file field with hidden input', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('type="hidden"')
        ->and($html)->toContain('wp-field-file-id')
        ->and($html)->toContain('name="test_file"');
});

it('renders file field with button', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-file-button');
});

it('renders file field with custom button text', function (): void {
    $this->field->buttonText('Choose File');
    $html = $this->field->render();

    expect($html)->toContain('Choose File');
});

it('renders file field with library type', function (): void {
    $this->field->library('image');
    $html = $this->field->render();

    expect($html)->toContain('data-library="image"');
});

it('renders file field with value', function (): void {
    $this->field->value('/path/to/file.pdf');
    $html = $this->field->render();

    expect($html)->toContain('/path/to/file.pdf');
});

it('renders file field with file name when value is set', function (): void {
    $this->field->value('/path/to/document.pdf');
    $html = $this->field->render();

    expect($html)->toContain('document.pdf')
        ->and($html)->toContain('wp-field-file-name');
});

it('renders file field with label', function (): void {
    $this->field->label('Upload Document');
    $html = $this->field->render();

    expect($html)->toContain('Upload Document')
        ->and($html)->toContain('<label');
});

it('renders file field with description', function (): void {
    $this->field->description('Select a file to upload');
    $html = $this->field->render();

    expect($html)->toContain('Select a file to upload');
});

it('sanitizes string value', function (): void {
    $sanitized = $this->field->sanitize('/path/to/file.pdf');

    expect($sanitized)->toBe('/path/to/file.pdf');
});

it('sanitizes trims value', function (): void {
    $sanitized = $this->field->sanitize('  /path/to/file.pdf  ');

    expect($sanitized)->toBe('/path/to/file.pdf');
});

it('sanitizes filters HTML', function (): void {
    $sanitized = $this->field->sanitize('<script>alert("xss")</script>/path.pdf');

    expect($sanitized)->not->toContain('<script>');
});

it('sanitizes returns empty string for non-scalar value', function (): void {
    $sanitized = $this->field->sanitize(['invalid']);

    expect($sanitized)->toBe('');
});

it('sanitizes returns empty string for null', function (): void {
    $sanitized = $this->field->sanitize(null);

    expect($sanitized)->toBe('');
});
