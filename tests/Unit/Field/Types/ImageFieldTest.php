<?php

declare(strict_types=1);

use WpField\Field\Types\ImageField;

beforeEach(function (): void {
    $this->field = new ImageField('test_image');
});

it('can set button text', function (): void {
    $this->field->buttonText('Upload Image');

    expect($this->field->getAttribute('button_text'))->toBe('Upload Image');
});

it('buttonText is chainable', function (): void {
    $result = $this->field->buttonText('Upload');

    expect($result)->toBe($this->field);
});

it('can set remove text', function (): void {
    $this->field->removeText('Remove Image');

    expect($this->field->getAttribute('remove_text'))->toBe('Remove Image');
});

it('removeText is chainable', function (): void {
    $result = $this->field->removeText('Remove');

    expect($result)->toBe($this->field);
});

it('can enable preview', function (): void {
    $this->field->preview(true);

    expect($this->field->getAttribute('preview'))->toBeTrue();
});

it('can disable preview', function (): void {
    $this->field->preview(false);

    expect($this->field->getAttribute('preview'))->toBeFalse();
});

it('preview is chainable', function (): void {
    $result = $this->field->preview(true);

    expect($result)->toBe($this->field);
});

it('renders image field with url input by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-image-wrapper')
        ->and($html)->toContain('wp-field-image-url')
        ->and($html)->toContain('type="text"');
});

it('renders image field without url input when url is false', function (): void {
    $this->field->attribute('url', false);
    $html = $this->field->render();

    expect($html)->not->toContain('wp-field-image-url');
});

it('renders image field with hidden input', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('type="hidden"')
        ->and($html)->toContain('wp-field-image-id')
        ->and($html)->toContain('name="test_image"');
});

it('renders image field with upload button', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-image-button');
});

it('renders image field with custom button text', function (): void {
    $this->field->buttonText('Choose Image');
    $html = $this->field->render();

    expect($html)->toContain('Choose Image');
});

it('renders image field with remove button when value is set', function (): void {
    $this->field->value('/path/to/image.jpg');
    $html = $this->field->render();

    expect($html)->toContain('wp-field-image-remove');
});

it('renders image field with custom remove text', function (): void {
    $this->field->value('/path/to/image.jpg');
    $this->field->removeText('Delete');
    $html = $this->field->render();

    expect($html)->toContain('Delete');
});

it('renders image field with preview when value is set and preview enabled', function (): void {
    $this->field->value('/path/to/image.jpg');
    $html = $this->field->render();

    expect($html)->toContain('wp-field-image-preview')
        ->and($html)->toContain('src="/path/to/image.jpg"');
});

it('renders image field without preview when preview disabled', function (): void {
    $this->field->value('/path/to/image.jpg');
    $this->field->preview(false);
    $html = $this->field->render();

    expect($html)->not->toContain('wp-field-image-preview');
});

it('renders image field without preview when value is empty', function (): void {
    $html = $this->field->render();

    expect($html)->not->toContain('wp-field-image-preview');
});

it('renders image field with label', function (): void {
    $this->field->label('Upload Image');
    $html = $this->field->render();

    expect($html)->toContain('Upload Image')
        ->and($html)->toContain('<label');
});

it('renders image field with description', function (): void {
    $this->field->description('Select an image to upload');
    $html = $this->field->render();

    expect($html)->toContain('Select an image to upload');
});

it('sanitizes string value', function (): void {
    $sanitized = $this->field->sanitize('/path/to/image.jpg');

    expect($sanitized)->toBe('/path/to/image.jpg');
});

it('sanitizes trims value', function (): void {
    $sanitized = $this->field->sanitize('  /path/to/image.jpg  ');

    expect($sanitized)->toBe('/path/to/image.jpg');
});

it('sanitizes filters HTML', function (): void {
    $sanitized = $this->field->sanitize('<script>alert("xss")</script>/image.jpg');

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
