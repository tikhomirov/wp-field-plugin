<?php

declare(strict_types=1);

use WpField\Field\Field;

it('renders media field with baseline enhancement hooks', function (): void {
    $field = Field::make('media', 'hero_media')
        ->attribute('library', 'image')
        ->value('https://example.com/file.pdf');

    $html = $field->render();

    expect($html)
        ->toContain('wp-field-media-wrapper')
        ->toContain('wp-field-media-button')
        ->toContain('data-library="image"')
        ->toContain('name="hero_media"')
        ->and($field->sanitize(' <b>12</b> '))->toBe('12');
});

it('renders color field with color picker data attributes', function (): void {
    $field = Field::make('color', 'accent')->default('#ffffff')->alpha(false)->value('#123456');

    $html = $field->render();

    expect($html)
        ->toContain('wp-color-picker-field')
        ->toContain('data-default-color="#ffffff"')
        ->toContain('data-alpha="false"')
        ->and($field->sanitize(' <i>#abcdef</i> '))->toBe('#abcdef');
});

it('renders editor field as textarea baseline with data config', function (): void {
    $field = Field::make('editor', 'content')
        ->rows(14)
        ->mediaButtons(true)
        ->teeny(true)
        ->wpautop(true)
        ->value('<b>Hello</b>');

    $html = $field->render();

    expect($html)
        ->toContain('class="wp-editor-area"')
        ->toContain('rows="14"')
        ->toContain('data-media-buttons="1"')
        ->toContain('data-teeny="1"')
        ->toContain('data-wpautop="1"')
        ->and($field->sanitize(' <script>x</script>ok '))->toBe('xok');
});

it('renders image and file fields with hidden value contract', function (): void {
    $image = Field::make('image', 'hero_image')->value('https://example.com/image.jpg');
    $file = Field::make('file', 'manual')->attribute('library', 'application')->value('https://example.com/manual.pdf');

    expect($image->render())
        ->toContain('wp-field-image-wrapper')
        ->toContain('wp-field-image-id')
        ->toContain('wp-field-image-button')
        ->toContain('wp-field-image-preview')
        ->and($file->render())->toContain('wp-field-file-wrapper')
        ->and($file->render())->toContain('wp-field-file-id')
        ->and($file->render())->toContain('wp-field-file-button')
        ->and($file->render())->toContain('data-library="application"');
});

it('renders gallery with csv value shape and action buttons', function (): void {
    $field = Field::make('gallery', 'slides')->value(['11', '22', '22', '33']);

    $html = $field->render();

    expect($html)
        ->toContain('wp-field-gallery-wrapper')
        ->toContain('wp-field-gallery-ids')
        ->toContain('value="11,22,33"')
        ->toContain('wp-field-gallery-add')
        ->toContain('wp-field-gallery-edit')
        ->toContain('wp-field-gallery-clear')
        ->and($field->sanitize(['7', '7', '8']))->toBe('7,8')
        ->and($field->validate('1,2,3'))->toBeTrue();
});

it('renders code_editor and icon fields with js enhancement markers', function (): void {
    $codeEditor = Field::make('code_editor', 'css')->mode('text/css')->value('body { color: red; }');
    $icon = Field::make('icon', 'site_icon')->library('fontawesome')->value('fa fa-home');

    expect($codeEditor->render())
        ->toContain('wp-field-code-editor')
        ->toContain('data-mode="text/css"')
        ->and($icon->render())->toContain('wp-field-icon-wrapper')
        ->and($icon->render())->toContain('data-library="fontawesome"')
        ->and($icon->render())->toContain('wp-field-icon-preview fa fa-home');
});

it('renders map field with coordinate baseline and validates ranges', function (): void {
    $map = Field::make('map', 'store_map')
        ->attribute('center', ['lat' => '55.7558', 'lng' => '37.6173'])
        ->attribute('zoom', 13)
        ->value(['lat' => '55.76', 'lng' => '37.62']);

    $html = $map->render();

    expect($html)
        ->toContain('wp-field-map-wrapper')
        ->toContain('name="store_map[lat]"')
        ->toContain('name="store_map[lng]"')
        ->toContain('wp-field-map-geolocate')
        ->toContain('data-zoom="13"')
        ->and($map->sanitize(['lat' => '91', 'lng' => '37.62']))->toMatchArray([
            'lat' => '',
            'lng' => '37.62',
        ])
        ->and($map->validate(['lat' => '200', 'lng' => '20']))->toBeFalse()
        ->and($map->validate(['lat' => '55.7', 'lng' => '37.6']))->toBeTrue();
});
