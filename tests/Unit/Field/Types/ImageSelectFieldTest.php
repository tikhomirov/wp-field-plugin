<?php

declare(strict_types=1);

use WpField\Field\Types\ImageSelectField;

beforeEach(function (): void {
    $this->field = new ImageSelectField('test_image_select');
});

it('renders image select with options as strings', function (): void {
    $this->field->options(['img1' => '/path/to/image1.jpg', 'img2' => '/path/to/image2.jpg']);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-image-select')
        ->and($html)->toContain('/path/to/image1.jpg');
});

it('renders image select with options as arrays', function (): void {
    $this->field->options([
        'img1' => ['src' => '/path/to/image1.jpg', 'label' => 'Image 1'],
        'img2' => ['src' => '/path/to/image2.jpg', 'label' => 'Image 2'],
    ]);
    $html = $this->field->render();

    expect($html)->toContain('/path/to/image1.jpg')
        ->and($html)->toContain('Image 1');
});

it('renders image select with label', function (): void {
    $this->field->options(['img1' => '/path.jpg']);
    $this->field->label('Select Image');
    $html = $this->field->render();

    expect($html)->toContain('Select Image');
});

it('renders image select with description', function (): void {
    $this->field->options(['img1' => '/path.jpg']);
    $this->field->description('Choose an image');
    $html = $this->field->render();

    expect($html)->toContain('Choose an image');
});

it('renders selected option as checked', function (): void {
    $this->field->options(['img1' => '/path1.jpg', 'img2' => '/path2.jpg']);
    $this->field->value('img2');
    $html = $this->field->render();

    expect($html)->toContain('checked')
        ->and($html)->toContain('selected');
});

it('renders with custom class', function (): void {
    $this->field->options(['img1' => '/path.jpg']);
    $this->field->class('custom-class');
    $html = $this->field->render();

    expect($html)->toContain('custom-class');
});

it('renders message when no options provided', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('No options provided');
});
