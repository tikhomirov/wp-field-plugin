<?php

declare(strict_types=1);

use WpField\Field\Types\FlexibleContentField;
use WpField\Field\Types\TextField;

beforeEach(function (): void {
    $this->flexible = new FlexibleContentField('test_flexible');
});

it('can add layout with fields', function (): void {
    $field1 = new TextField('text1');
    $field2 = new TextField('text2');

    $this->flexible->addLayout('layout1', 'Layout 1', [$field1, $field2]);

    $layouts = $this->flexible->getLayouts();

    expect($layouts)->toHaveCount(1)
        ->and($layouts)->toHaveKey('layout1')
        ->and($layouts['layout1']['label'])->toBe('Layout 1')
        ->and($layouts['layout1']['fields'])->toHaveCount(2);
});

it('can add multiple layouts', function (): void {
    $field1 = new TextField('text1');
    $field2 = new TextField('text2');

    $this->flexible
        ->addLayout('layout1', 'Layout 1', [$field1])
        ->addLayout('layout2', 'Layout 2', [$field2]);

    $layouts = $this->flexible->getLayouts();

    expect($layouts)->toHaveCount(2);
});

it('sets min limit', function (): void {
    $this->flexible->min(2);

    $array = $this->flexible->toArray();

    expect($array['min'])->toBe(2);
});

it('sets max limit', function (): void {
    $this->flexible->max(5);

    $array = $this->flexible->toArray();

    expect($array['max'])->toBe(5);
});

it('sets custom button label', function (): void {
    $this->flexible->buttonLabel('Add New Block');

    $array = $this->flexible->toArray();

    expect($array['button_label'])->toBe('Add New Block');
});

it('sanitizes array of blocks with valid layout', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);

    $input = [
        [
            'acf_fc_layout' => 'layout1',
            'text' => 'value',
        ],
    ];

    $sanitized = $this->flexible->sanitize($input);

    expect($sanitized)->toBeArray()
        ->and($sanitized)->toHaveCount(1)
        ->and($sanitized[0]['acf_fc_layout'])->toBe('layout1')
        ->and($sanitized[0]['text'])->toBe('value');
});

it('skips blocks without acf_fc_layout in sanitize', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);

    $input = [
        ['text' => 'value'],
        ['acf_fc_layout' => 'layout1', 'text' => 'value2'],
    ];

    $sanitized = $this->flexible->sanitize($input);

    expect($sanitized)->toHaveCount(1);
});

it('skips blocks with invalid layout in sanitize', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);

    $input = [
        ['acf_fc_layout' => 'invalid_layout', 'text' => 'value'],
    ];

    $sanitized = $this->flexible->sanitize($input);

    expect($sanitized)->toBeEmpty();
});

it('returns empty array for non-array input in sanitize', function (): void {
    $sanitized = $this->flexible->sanitize('invalid');

    expect($sanitized)->toBe([]);
});

it('validates min constraint', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);
    $this->flexible->min(2);

    $value = [
        ['acf_fc_layout' => 'layout1', 'text' => 'value'],
    ];

    expect($this->flexible->validate($value))->toBeFalse();
});

it('validates max constraint', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);
    $this->flexible->max(2);

    $value = [
        ['acf_fc_layout' => 'layout1', 'text' => 'value1'],
        ['acf_fc_layout' => 'layout1', 'text' => 'value2'],
        ['acf_fc_layout' => 'layout1', 'text' => 'value3'],
    ];

    expect($this->flexible->validate($value))->toBeFalse();
});

it('validates nested field values', function (): void {
    $field = new TextField('text');
    $field->required();
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);

    $value = [
        ['acf_fc_layout' => 'layout1', 'text' => ''],
    ];

    expect($this->flexible->validate($value))->toBeFalse();
});

it('returns false for blocks without acf_fc_layout in validation', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);

    $value = [
        ['text' => 'value'],
    ];

    expect($this->flexible->validate($value))->toBeFalse();
});

it('returns false for blocks with invalid layout in validation', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);

    $value = [
        ['acf_fc_layout' => 'invalid_layout', 'text' => 'value'],
    ];

    expect($this->flexible->validate($value))->toBeFalse();
});

it('returns true for valid data within constraints', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);
    $this->flexible->min(1)->max(5);

    $value = [
        ['acf_fc_layout' => 'layout1', 'text' => 'value1'],
        ['acf_fc_layout' => 'layout1', 'text' => 'value2'],
    ];

    expect($this->flexible->validate($value))->toBeTrue();
});

it('renders flexible content field', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);

    $html = $this->flexible->render();

    expect($html)->toContain('wp-field-flexible')
        ->and($html)->toContain('wp-field-flexible-add')
        ->and($html)->toContain('data-layout="layout1"')
        ->and($html)->toContain('wp-field-flexible-template');
});

it('renders with min and max constraints', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);
    $this->flexible->min(1)->max(5);

    $html = $this->flexible->render();

    expect($html)->toContain('data-min="1"')
        ->and($html)->toContain('data-max="5"');
});

it('renders blocks with data', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);
    $this->flexible->value([
        ['acf_fc_layout' => 'layout1', 'text' => 'value1'],
    ]);

    $html = $this->flexible->render();

    expect($html)->toContain('value1')
        ->and($html)->toContain('wp-field-flexible-block');
});

it('renders with label and description', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);
    $this->flexible->label('Flexible Label')->description('Flexible Description');

    $html = $this->flexible->render();

    expect($html)->toContain('Flexible Label')
        ->and($html)->toContain('Flexible Description');
});

it('toArray includes layouts and config', function (): void {
    $field = new TextField('text');
    $this->flexible->addLayout('layout1', 'Layout 1', [$field]);
    $this->flexible->min(1)->max(5)->buttonLabel('Add Block');

    $array = $this->flexible->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('layouts')
        ->and($array)->toHaveKey('min')
        ->and($array)->toHaveKey('max')
        ->and($array)->toHaveKey('button_label')
        ->and($array['layouts'])->toHaveKey('layout1');
});
