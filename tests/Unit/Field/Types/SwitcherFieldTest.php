<?php

declare(strict_types=1);

use WpField\Field\Types\SwitcherField;

beforeEach(function (): void {
    $this->field = new SwitcherField('test_switcher');
});

it('can set text on', function (): void {
    $this->field->textOn('Enabled');

    expect($this->field->getAttribute('text_on'))->toBe('Enabled');
});

it('textOn is chainable', function (): void {
    $result = $this->field->textOn('On');

    expect($result)->toBe($this->field);
});

it('can set text off', function (): void {
    $this->field->textOff('Disabled');

    expect($this->field->getAttribute('text_off'))->toBe('Disabled');
});

it('textOff is chainable', function (): void {
    $result = $this->field->textOff('Off');

    expect($result)->toBe($this->field);
});

it('can set checked value', function (): void {
    $this->field->checkedValue('yes');

    expect($this->field->getAttribute('checked_value'))->toBe('yes');
});

it('checkedValue is chainable', function (): void {
    $result = $this->field->checkedValue('custom');

    expect($result)->toBe($this->field);
});

it('renders switcher with default texts', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-switcher')
        ->and($html)->toContain('wp-field-switcher-on')
        ->and($html)->toContain('On')
        ->and($html)->toContain('Off');
});

it('renders switcher with custom texts', function (): void {
    $this->field->textOn('Enabled')->textOff('Disabled');
    $html = $this->field->render();

    expect($html)->toContain('Enabled')
        ->and($html)->toContain('Disabled');
});

it('renders switcher as checked when value matches checked value', function (): void {
    $this->field->value('1');
    $html = $this->field->render();

    expect($html)->toContain('checked');
});

it('renders switcher as checked when boolean true', function (): void {
    $this->field->value(true);
    $html = $this->field->render();

    expect($html)->toContain('checked');
});

it('renders switcher as checked when non-null value', function (): void {
    $this->field->value(['array']);
    $html = $this->field->render();

    expect($html)->toContain('checked');
});

it('renders switcher as unchecked when value does not match', function (): void {
    $this->field->value('0');
    $html = $this->field->render();

    expect($html)->not->toContain('checked');
});

it('renders switcher as unchecked when boolean false', function (): void {
    $this->field->value(false);
    $html = $this->field->render();

    expect($html)->not->toContain('checked');
});

it('renders switcher as unchecked when null', function (): void {
    $this->field->value(null);
    $html = $this->field->render();

    expect($html)->not->toContain('checked');
});

it('renders switcher with disabled attribute', function (): void {
    $this->field->disabled();
    $html = $this->field->render();

    expect($html)->toContain('disabled');
});

it('renders switcher with readonly attribute', function (): void {
    $this->field->readonly();
    $html = $this->field->render();

    expect($html)->toContain('readonly');
});

it('renders switcher with custom class', function (): void {
    $this->field->class('custom-switcher');
    $html = $this->field->render();

    expect($html)->toContain('custom-switcher');
});

it('renders switcher with description', function (): void {
    $this->field->description('Toggle this option');
    $html = $this->field->render();

    expect($html)->toContain('Toggle this option');
});

it('renders switcher with custom checked value', function (): void {
    $this->field->checkedValue('yes');
    $this->field->value('yes');
    $html = $this->field->render();

    expect($html)->toContain('value="yes"')
        ->and($html)->toContain('checked');
});
