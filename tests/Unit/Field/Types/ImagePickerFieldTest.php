<?php

declare(strict_types=1);

use WpField\Field\Types\ImagePickerField;

beforeEach(function (): void {
    $this->field = new ImagePickerField('test_image_picker');
});

it('can set options', function (): void {
    $options = ['image1' => '/path/to/image1.jpg', 'image2' => '/path/to/image2.jpg'];
    $this->field->options($options);

    expect($this->field->getAttribute('options'))->toBe($options);
});

it('options is chainable', function (): void {
    $result = $this->field->options(['img1' => '/path.jpg']);

    expect($result)->toBe($this->field);
});

it('renders image picker with options as strings', function (): void {
    $this->field->options(['image1' => '/path/to/image1.jpg', 'image2' => '/path/to/image2.jpg']);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-image-picker')
        ->and($html)->toContain('data-img-src="/path/to/image1.jpg"')
        ->and($html)->toContain('data-img-src="/path/to/image2.jpg"');
});

it('renders image picker with options as arrays', function (): void {
    $this->field->options([
        'image1' => ['src' => '/path/to/image1.jpg', 'label' => 'Image 1'],
        'image2' => ['src' => '/path/to/image2.jpg', 'label' => 'Image 2'],
    ]);
    $html = $this->field->render();

    expect($html)->toContain('data-img-src="/path/to/image1.jpg"')
        ->and($html)->toContain('Image 1')
        ->and($html)->toContain('Image 2');
});

it('renders image picker with label', function (): void {
    $this->field->options(['img1' => '/path.jpg']);
    $this->field->label('Select Image');
    $html = $this->field->render();

    expect($html)->toContain('Select Image')
        ->and($html)->toContain('<label');
});

it('renders image picker with description', function (): void {
    $this->field->options(['img1' => '/path.jpg']);
    $this->field->description('Choose an image from the list');
    $html = $this->field->render();

    expect($html)->toContain('Choose an image from the list');
});

it('renders selected option as selected', function (): void {
    $this->field->options(['image1' => '/path1.jpg', 'image2' => '/path2.jpg']);
    $this->field->value('image2');
    $html = $this->field->render();

    expect($html)->toContain('value="image2"')
        ->and($html)->toContain('selected');
});

it('renders with empty value when not set', function (): void {
    $this->field->options(['image1' => '/path.jpg']);
    $html = $this->field->render();

    expect($html)->toContain('data-img-src="/path.jpg"');
});

it('handles empty options', function (): void {
    $this->field->options([]);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-image-picker');
});

it('uses option value as label when array without label', function (): void {
    $this->field->options(['image1' => ['src' => '/path.jpg']]);
    $html = $this->field->render();

    expect($html)->toContain('image1');
});
