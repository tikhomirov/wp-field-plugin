<?php

declare(strict_types=1);

use WpField\Traits\HasAttributes;

class TestHasAttributesClass
{
    use HasAttributes;
}

beforeEach(function (): void {
    $this->instance = new TestHasAttributesClass;
});

it('can set label', function (): void {
    $this->instance->label('Test Label');

    expect($this->instance->getAttribute('label'))->toBe('Test Label');
});

it('can set placeholder', function (): void {
    $this->instance->placeholder('Enter text');

    expect($this->instance->getAttribute('placeholder'))->toBe('Enter text');
});

it('can set description', function (): void {
    $this->instance->description('This is a description');

    expect($this->instance->getAttribute('description'))->toBe('This is a description');
});

it('can set default value', function (): void {
    $this->instance->default('default_value');

    expect($this->instance->getAttribute('default'))->toBe('default_value');
});

it('can set class', function (): void {
    $this->instance->class('custom-class');

    expect($this->instance->getAttribute('class'))->toBe('custom-class');
});

it('can set id', function (): void {
    $this->instance->id('custom-id');

    expect($this->instance->getAttribute('id'))->toBe('custom-id');
});

it('can set disabled', function (): void {
    $this->instance->disabled();

    expect($this->instance->getAttribute('disabled'))->toBeTrue();
});

it('can set disabled with false', function (): void {
    $this->instance->disabled(false);

    expect($this->instance->getAttribute('disabled'))->toBeFalse();
});

it('can set readonly', function (): void {
    $this->instance->readonly();

    expect($this->instance->getAttribute('readonly'))->toBeTrue();
});

it('can set readonly with false', function (): void {
    $this->instance->readonly(false);

    expect($this->instance->getAttribute('readonly'))->toBeFalse();
});

it('can get attribute with default', function (): void {
    expect($this->instance->getAttribute('nonexistent', 'default'))->toBe('default');
});

it('can get all attributes', function (): void {
    $this->instance->label('Label')->placeholder('Placeholder')->class('Class');

    $attributes = $this->instance->getAttributes();

    expect($attributes)->toBe([
        'label' => 'Label',
        'placeholder' => 'Placeholder',
        'class' => 'Class',
    ]);
});

it('can set custom attribute', function (): void {
    $this->instance->setAttribute('custom', 'value');

    expect($this->instance->getAttribute('custom'))->toBe('value');
});

it('can use attribute alias', function (): void {
    $this->instance->attribute('data-test', 'value');

    expect($this->instance->getAttribute('data-test'))->toBe('value');
});

it('attributes are chainable', function (): void {
    $result = $this->instance
        ->label('Label')
        ->placeholder('Placeholder')
        ->description('Description')
        ->default('default')
        ->class('class')
        ->id('id')
        ->disabled()
        ->readonly();

    expect($result)->toBe($this->instance);
});
