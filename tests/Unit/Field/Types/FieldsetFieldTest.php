<?php

declare(strict_types=1);

use WpField\Field\Types\FieldsetField;
use WpField\Field\Types\TextField;

beforeEach(function (): void {
    $this->fieldset = new FieldsetField('test_fieldset');
});

it('can set fields', function (): void {
    $fields = ['field1' => 'value1'];
    $this->fieldset->fields($fields);

    expect($this->fieldset->getAttribute('fields'))->toBe($fields);
});

it('fields is chainable', function (): void {
    $result = $this->fieldset->fields(['field1' => 'value1']);

    expect($result)->toBe($this->fieldset);
});

it('renders fieldset with legend from label', function (): void {
    $this->fieldset->label('Fieldset Label');
    $html = $this->fieldset->render();

    expect($html)->toContain('wp-field-fieldset')
        ->and($html)->toContain('<legend>Fieldset Label</legend>');
});

it('renders fieldset with legend from legend attribute', function (): void {
    $this->fieldset->attribute('legend', 'Custom Legend');
    $html = $this->fieldset->render();

    expect($html)->toContain('<legend>Custom Legend</legend>');
});

it('renders fieldset with custom class', function (): void {
    $this->fieldset->class('custom-class');
    $html = $this->fieldset->render();

    expect($html)->toContain('custom-class');
});

it('renders fieldset with description', function (): void {
    $this->fieldset->description('Fieldset description');
    $html = $this->fieldset->render();

    expect($html)->toContain('Fieldset description');
});

it('renders FieldInterface objects directly', function (): void {
    $field = new TextField('nested_field');
    $this->fieldset->fields([$field]);
    $html = $this->fieldset->render();

    expect($html)->toContain('nested_field');
});

it('creates fields from array config', function (): void {
    $config = [
        ['type' => 'text', 'id' => 'field1', 'label' => 'Field 1'],
        ['type' => 'text', 'id' => 'field2', 'label' => 'Field 2'],
    ];
    $this->fieldset->fields($config);
    $html = $this->fieldset->render();

    expect($html)->toContain('Field 1')
        ->and($html)->toContain('Field 2');
});

it('sets field value from config', function (): void {
    $config = [['type' => 'text', 'id' => 'field1', 'value' => 'test value']];
    $this->fieldset->fields($config);
    $html = $this->fieldset->render();

    expect($html)->toContain('test value');
});

it('sets field required from config', function (): void {
    $config = [['type' => 'text', 'id' => 'field1', 'required' => true]];
    $this->fieldset->fields($config);
    $html = $this->fieldset->render();

    expect($html)->toContain('required');
});

it('sets field name from config if withName method exists', function (): void {
    $config = [['type' => 'text', 'id' => 'field1', 'name' => 'custom_name']];
    $this->fieldset->fields($config);
    $html = $this->fieldset->render();

    expect($html)->toContain('custom_name');
});

it('sets custom attributes from config', function (): void {
    $config = [['type' => 'text', 'id' => 'field1', 'placeholder' => 'Enter value']];
    $this->fieldset->fields($config);
    $html = $this->fieldset->render();

    expect($html)->toContain('placeholder');
});

it('skips invalid field configs', function (): void {
    $config = [
        ['type' => 'text', 'id' => 'field1'],
        ['type' => '', 'id' => 'field2'],
        ['type' => 'text', 'id' => ''],
        'invalid',
    ];
    $this->fieldset->fields($config);
    $html = $this->fieldset->render();

    expect($html)->toContain('field1');
});

it('handles non-array fields', function (): void {
    $this->fieldset->fields(['invalid' => 'data']);
    $html = $this->fieldset->render();

    expect($html)->toContain('wp-field-fieldset');
});
