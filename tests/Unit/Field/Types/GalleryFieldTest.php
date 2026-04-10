<?php

declare(strict_types=1);

use WpField\Field\Types\GalleryField;

beforeEach(function (): void {
    $this->field = new GalleryField('test_gallery');
});

it('can set add button text', function (): void {
    $this->field->addButton('Add Images');

    expect($this->field->getAttribute('add_button'))->toBe('Add Images');
});

it('addButton is chainable', function (): void {
    $result = $this->field->addButton('Add');

    expect($result)->toBe($this->field);
});

it('can set edit button text', function (): void {
    $this->field->editButton('Edit Gallery');

    expect($this->field->getAttribute('edit_button'))->toBe('Edit Gallery');
});

it('editButton is chainable', function (): void {
    $result = $this->field->editButton('Edit');

    expect($result)->toBe($this->field);
});

it('can set clear button text', function (): void {
    $this->field->clearButton('Clear Gallery');

    expect($this->field->getAttribute('clear_button'))->toBe('Clear Gallery');
});

it('clearButton is chainable', function (): void {
    $result = $this->field->clearButton('Clear');

    expect($result)->toBe($this->field);
});

it('renders gallery with placeholder when no images', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-gallery-wrapper')
        ->and($html)->toContain('wp-field-gallery-placeholder');
});

it('renders gallery with image IDs', function (): void {
    $this->field->value([1, 2, 3]);
    $html = $this->field->render();

    expect($html)->toContain('data-id="1"')
        ->and($html)->toContain('data-id="2"')
        ->and($html)->toContain('data-id="3"');
});

it('renders gallery with label', function (): void {
    $this->field->label('Gallery Images');
    $html = $this->field->render();

    expect($html)->toContain('Gallery Images')
        ->and($html)->toContain('<label');
});

it('renders gallery with description', function (): void {
    $this->field->description('Select images for gallery');
    $html = $this->field->render();

    expect($html)->toContain('Select images for gallery');
});

it('renders gallery with custom button texts', function (): void {
    $this->field->addButton('Add New')->editButton('Edit Now')->clearButton('Clear All');
    $html = $this->field->render();

    expect($html)->toContain('Add New')
        ->and($html)->toContain('Edit Now')
        ->and($html)->toContain('Clear All');
});

it('renders hidden input with comma-separated IDs', function (): void {
    $this->field->value([1, 2, 3]);
    $html = $this->field->render();

    expect($html)->toContain('type="hidden"')
        ->and($html)->toContain('value="1,2,3"');
});

it('sanitizes array of IDs to comma-separated string', function (): void {
    $sanitized = $this->field->sanitize([1, 2, 3]);

    expect($sanitized)->toBe('1,2,3');
});

it('sanitizes removes duplicate IDs', function (): void {
    $sanitized = $this->field->sanitize([1, 1, 2, 2, 3]);

    expect($sanitized)->toBe('1,2,3');
});

it('sanitizes removes empty IDs', function (): void {
    $sanitized = $this->field->sanitize([1, '', 2, ' ', 3]);

    expect($sanitized)->toBe('1,2,3');
});

it('sanitizes comma-separated string', function (): void {
    $sanitized = $this->field->sanitize('1,2,3');

    expect($sanitized)->toBe('1,2,3');
});

it('sanitizes string with spaces', function (): void {
    $sanitized = $this->field->sanitize('1, 2, 3');

    expect($sanitized)->toBe('1,2,3');
});

it('sanitizes array with non-numeric keys', function (): void {
    $sanitized = $this->field->sanitize(['invalid' => 'array']);

    expect($sanitized)->toBe('array');
});

it('sanitizes array filters non-scalar items', function (): void {
    $sanitized = $this->field->sanitize([1, ['invalid'], 2, 3]);

    expect($sanitized)->toBe('1,2,3');
});

it('validates array value', function (): void {
    expect($this->field->validate([1, 2, 3]))->toBeTrue();
});

it('validates string value', function (): void {
    expect($this->field->validate('1,2,3'))->toBeTrue();
});

it('validates non-array/non-string value when not required', function (): void {
    expect($this->field->validate(null))->toBeTrue();
});

it('validates non-array/non-string value when required', function (): void {
    $this->field->required();

    expect($this->field->validate(null))->toBeFalse();
});
