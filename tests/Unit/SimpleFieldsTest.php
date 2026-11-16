<?php

declare(strict_types=1);

beforeEach(function (): void {
    require_once dirname(__DIR__, 2).'/WP_Field.php';
});

// ===== SWITCHER =====
it('renders switcher with default parameters', function (): void {
    $html = WP_Field::make([
        'id' => 'test_switcher',
        'type' => 'switcher',
        'label' => 'Enable Feature',
    ], false);

    expect($html)
        ->toContain('wp-field-switcher')
        ->toContain('type="checkbox"')
        ->toContain('value="1"')
        ->toContain('On')
        ->toContain('Off');
});

it('renders switcher with custom text', function (): void {
    $html = WP_Field::make([
        'id' => 'test_switcher',
        'type' => 'switcher',
        'label' => 'Enable',
        'text_on' => 'Yes',
        'text_off' => 'No',
    ], false);

    expect($html)
        ->toContain('Yes')
        ->toContain('No');
});

it('renders switcher with checked state', function (): void {
    $html = WP_Field::make([
        'id' => 'test_switcher',
        'type' => 'switcher',
        'label' => 'Enable',
        'value' => '1',
    ], false);

    expect($html)->toContain('checked');
});

it('renders switcher with description', function (): void {
    $html = WP_Field::make([
        'id' => 'test_switcher',
        'type' => 'switcher',
        'label' => 'Enable',
        'desc' => 'Turn on/off this feature',
    ], false);

    expect($html)
        ->toContain('Turn on/off this feature')
        ->toContain('description');
});

// ===== SPINNER =====
it('renders spinner with default parameters', function (): void {
    $html = WP_Field::make([
        'id' => 'test_spinner',
        'type' => 'spinner',
        'label' => 'Quantity',
    ], false);

    expect($html)
        ->toContain('wp-field-spinner')
        ->toContain('wp-field-spinner-up')
        ->toContain('wp-field-spinner-down')
        ->toContain('type="number"');
});

it('renders spinner with min max step', function (): void {
    $html = WP_Field::make([
        'id' => 'test_spinner',
        'type' => 'spinner',
        'label' => 'Quantity',
        'min' => 1,
        'max' => 100,
        'step' => 5,
    ], false);

    expect($html)
        ->toContain('min="1"')
        ->toContain('max="100"')
        ->toContain('step="5"');
});

it('renders spinner with unit', function (): void {
    $html = WP_Field::make([
        'id' => 'test_spinner',
        'type' => 'spinner',
        'label' => 'Quantity',
        'unit' => 'kg',
    ], false);

    expect($html)
        ->toContain('wp-field-spinner-unit')
        ->toContain('kg');
});

// ===== BUTTON SET =====
it('renders button set with radio', function (): void {
    $html = WP_Field::make([
        'id' => 'test_button_set',
        'type' => 'button_set',
        'label' => 'Alignment',
        'options' => [
            'left' => 'Left',
            'center' => 'Center',
            'right' => 'Right',
        ],
    ], false);

    expect($html)
        ->toContain('wp-field-button-set')
        ->toContain('type="radio"')
        ->toContain('value="left"')
        ->toContain('Left')
        ->toContain('value="center"')
        ->toContain('value="right"');
});

it('renders button set with checkbox multiple', function (): void {
    $html = WP_Field::make([
        'id' => 'test_button_set',
        'type' => 'button_set',
        'label' => 'Alignment',
        'multiple' => true,
        'options' => [
            'left' => 'Left',
            'center' => 'Center',
            'right' => 'Right',
        ],
    ], false);

    expect($html)
        ->toContain('wp-field-button-set')
        ->toContain('type="checkbox"')
        ->toContain('name="test_button_set[]"');
});

// ===== SLIDER =====
it('renders slider with default parameters', function (): void {
    $html = WP_Field::make([
        'id' => 'test_slider',
        'type' => 'slider',
        'label' => 'Opacity',
    ], false);

    expect($html)
        ->toContain('wp-field-slider-wrapper')
        ->toContain('type="range"')
        ->toContain('wp-field-slider');
});

it('renders slider with show value', function (): void {
    $html = WP_Field::make([
        'id' => 'test_slider',
        'type' => 'slider',
        'label' => 'Opacity',
        'show_value' => true,
        'value' => 50,
    ], false);

    expect($html)
        ->toContain('wp-field-slider-value')
        ->toContain('50');
});

// ===== HEADING & SUBHEADING =====
it('renders heading with default tag', function (): void {
    $html = WP_Field::make([
        'id' => 'test_heading',
        'type' => 'heading',
        'label' => 'Main Section',
    ], false);

    expect($html)
        ->toContain('wp-field-heading')
        ->toContain('<h3')
        ->toContain('Main Section');
});

it('renders subheading with custom tag', function (): void {
    $html = WP_Field::make([
        'id' => 'test_subheading',
        'type' => 'subheading',
        'label' => 'Subsection',
        'tag' => 'h5',
    ], false);

    expect($html)
        ->toContain('wp-field-subheading')
        ->toContain('<h5')
        ->toContain('Subsection');
});

// ===== NOTICE =====
it('renders notice with type', function (): void {
    $html = WP_Field::make([
        'id' => 'test_notice',
        'type' => 'notice',
        'notice_type' => 'warning',
        'label' => 'This is a warning',
    ], false);

    expect($html)
        ->toContain('wp-field-notice')
        ->toContain('wp-field-notice-warning')
        ->toContain('This is a warning');
});

// ===== CONTENT =====
it('renders custom content', function (): void {
    $html = WP_Field::make([
        'id' => 'test_content',
        'type' => 'content',
        'label' => '<div class="custom-content">Hello World</div>',
    ], false);

    expect($html)
        ->toContain('custom-content')
        ->toContain('Hello World');
});

// ===== FIELDSET =====
it('renders fieldset with nested fields', function (): void {
    $html = WP_Field::make([
        'id' => 'test_fieldset',
        'type' => 'fieldset',
        'legend' => 'Group Info',
        'fields' => [
            [
                'id' => 'nested_text',
                'type' => 'text',
                'label' => 'Nested Text',
            ],
        ],
    ], false);

    expect($html)
        ->toContain('<fieldset')
        ->toContain('<legend>')
        ->toContain('Group Info')
        ->toContain('Nested Text')
        ->toContain('nested_text');
});
