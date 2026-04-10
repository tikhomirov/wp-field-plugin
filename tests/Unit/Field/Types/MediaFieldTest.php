<?php

declare(strict_types=1);

use WpField\Field\Types\MediaField;

beforeEach(function (): void {
    $this->field = new MediaField('test_media');
});

it('can set library type', function (): void {
    $this->field->library('image');

    expect($this->field->getAttribute('library'))->toBe('image');
});

it('renders with label', function (): void {
    $this->field->label('Media File');
    $html = $this->field->render();

    expect($html)->toContain('Media File')
        ->and($html)->toContain('<label');
});

it('renders with description', function (): void {
    $this->field->description('Select a media file');
    $html = $this->field->render();

    expect($html)->toContain('Select a media file');
});

it('sanitizes non-scalar value to empty string', function (): void {
    $sanitized = $this->field->sanitize(['invalid']);

    expect($sanitized)->toBe('');
});

it('renders media field with url input by default', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-media-url');
});

it('renders media field without url input when url attribute is false', function (): void {
    $this->field->attribute('url', false);
    $html = $this->field->render();

    expect($html)->not->toContain('wp-field-media-url');
});

it('renders media field with preview when file url is set', function (): void {
    $this->field->value('http://example.com/image.jpg');
    $html = $this->field->render();

    expect($html)->toContain('wp-field-media-preview')
        ->and($html)->toContain('image.jpg');
});

it('renders media field without preview when preview attribute is false', function (): void {
    $this->field->value('http://example.com/image.jpg');
    $this->field->attribute('preview', false);
    $html = $this->field->render();

    expect($html)->not->toContain('wp-field-media-preview');
});

it('renders media field with custom button text', function (): void {
    $this->field->attribute('button_text', 'Choose File');
    $html = $this->field->render();

    expect($html)->toContain('Choose File');
});

it('renders media field with library attribute', function (): void {
    $this->field->library('video');
    $html = $this->field->render();

    expect($html)->toContain('data-library="video"');
});
