<?php

declare(strict_types=1);

use WpField\Field\Types\EditorField;

beforeEach(function (): void {
    $this->field = new EditorField('test_editor');
});

it('can set rows', function (): void {
    $this->field->rows(15);

    expect($this->field->getAttribute('rows'))->toBe(15);
});

it('rows is chainable', function (): void {
    $result = $this->field->rows(20);

    expect($result)->toBe($this->field);
});

it('can enable media buttons', function (): void {
    $this->field->mediaButtons(true);

    expect($this->field->getAttribute('media_buttons'))->toBeTrue();
});

it('can disable media buttons', function (): void {
    $this->field->mediaButtons(false);

    expect($this->field->getAttribute('media_buttons'))->toBeFalse();
});

it('mediaButtons is chainable', function (): void {
    $result = $this->field->mediaButtons(true);

    expect($result)->toBe($this->field);
});

it('can enable teeny mode', function (): void {
    $this->field->teeny(true);

    expect($this->field->getAttribute('teeny'))->toBeTrue();
});

it('can disable teeny mode', function (): void {
    $this->field->teeny(false);

    expect($this->field->getAttribute('teeny'))->toBeFalse();
});

it('teeny is chainable', function (): void {
    $result = $this->field->teeny(true);

    expect($result)->toBe($this->field);
});

it('can enable wpautop', function (): void {
    $this->field->wpautop(true);

    expect($this->field->getAttribute('wpautop'))->toBeTrue();
});

it('can disable wpautop', function (): void {
    $this->field->wpautop(false);

    expect($this->field->getAttribute('wpautop'))->toBeFalse();
});

it('wpautop is chainable', function (): void {
    $result = $this->field->wpautop(true);

    expect($result)->toBe($this->field);
});

it('renders editor field as textarea when wp_editor not available', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('textarea')
        ->and($html)->toContain('wp-editor-area')
        ->and($html)->toContain('name="test_editor"');
});

it('renders editor field with wp_editor when function exists', function (): void {
    if (! function_exists('wp_editor')) {
        $this->markTestSkipped('wp_editor function not available');
    }

    $this->field->value('Test content');
    $html = $this->field->render();

    expect($html)->toContain('Test content');
});

it('renders with custom rows', function (): void {
    $this->field->rows(20);
    $html = $this->field->render();

    expect($html)->toContain('rows="20"');
});

it('renders with label', function (): void {
    $this->field->label('Editor Content');
    $html = $this->field->render();

    expect($html)->toContain('Editor Content')
        ->and($html)->toContain('<label');
});

it('renders with description', function (): void {
    $this->field->description('Enter your content here');
    $html = $this->field->render();

    expect($html)->toContain('Enter your content here');
});

it('renders with value', function (): void {
    $this->field->value('Test content');
    $html = $this->field->render();

    expect($html)->toContain('Test content');
});

it('renders with data attributes for features', function (): void {
    $this->field->mediaButtons(true)->teeny(true)->wpautop(true);
    $html = $this->field->render();

    expect($html)->toContain('data-media-buttons="1"')
        ->and($html)->toContain('data-teeny="1"')
        ->and($html)->toContain('data-wpautop="1"');
});

it('enforces minimum rows of 1', function (): void {
    $this->field->rows(0);
    $html = $this->field->render();

    expect($html)->toContain('rows="1"');
});

it('sanitizes string value', function (): void {
    $sanitized = $this->field->sanitize('  test content  ');

    expect($sanitized)->toBe('test content');
});

it('sanitizes HTML tags', function (): void {
    $sanitized = $this->field->sanitize('<script>alert("xss")</script>');

    expect($sanitized)->not->toContain('<script>');
});

it('sanitizes non-scalar value to empty string', function (): void {
    $sanitized = $this->field->sanitize(['invalid']);

    expect($sanitized)->toBe('');
});

it('sanitizes null to empty string', function (): void {
    $sanitized = $this->field->sanitize(null);

    expect($sanitized)->toBe('');
});

it('renders with wp_editor when all features enabled', function (): void {
    if (! function_exists('wp_editor')) {
        $this->markTestSkipped('wp_editor function not available');
    }

    $this->field
        ->mediaButtons(true)
        ->teeny(true)
        ->wpautop(true)
        ->rows(15)
        ->value('Test content');

    $html = $this->field->render();

    expect($html)->toContain('Test content');
});

it('renders with wp_editor with default attributes', function (): void {
    if (! function_exists('wp_editor')) {
        $this->markTestSkipped('wp_editor function not available');
    }

    $html = $this->field->render();

    expect($html)->toBeString();
});
