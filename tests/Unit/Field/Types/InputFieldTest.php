<?php

declare(strict_types=1);

use WpField\Field\Types\InputField;

beforeEach(function (): void {
    $this->field = new InputField('test_input');
});

it('renders input with default text type', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('type="text"')
        ->and($html)->toContain('name="test_input"');
});

it('renders input with custom type', function (): void {
    $this->field = new InputField('test_input', 'email');
    $html = $this->field->render();

    expect($html)->toContain('type="email"');
});

it('renders input with value', function (): void {
    $this->field->value('test value');
    $html = $this->field->render();

    expect($html)->toContain('value="test value"');
});

it('renders input with label', function (): void {
    $this->field->label('Test Label');
    $html = $this->field->render();

    expect($html)->toContain('Test Label')
        ->and($html)->toContain('<label');
});

it('renders input with description', function (): void {
    $this->field->description('Test description');
    $html = $this->field->render();

    expect($html)->toContain('Test description');
});

it('renders input with placeholder', function (): void {
    $this->field->placeholder('Enter value');
    $html = $this->field->render();

    expect($html)->toContain('placeholder="Enter value"');
});

it('renders input with disabled attribute', function (): void {
    $this->field->disabled();
    $html = $this->field->render();

    expect($html)->toContain('disabled');
});

it('renders input with readonly attribute', function (): void {
    $this->field->readonly();
    $html = $this->field->render();

    expect($html)->toContain('readonly');
});

it('renders input with required attribute', function (): void {
    $this->field->required();
    $html = $this->field->render();

    expect($html)->toContain('required');
});

it('renders input with custom boolean attribute', function (): void {
    $this->field->attribute('autofocus', true);
    $html = $this->field->render();

    expect($html)->toContain('autofocus');
});

it('renders input with custom string attribute', function (): void {
    $this->field->attribute('maxlength', '100');
    $html = $this->field->render();

    expect($html)->toContain('maxlength="100"');
});

it('renders input with custom numeric attribute', function (): void {
    $this->field->attribute('min', 0);
    $this->field->attribute('max', 100);
    $html = $this->field->render();

    expect($html)->toContain('min="0"')
        ->and($html)->toContain('max="100"');
});

it('does not render boolean attribute when false', function (): void {
    $this->field->attribute('autofocus', false);
    $html = $this->field->render();

    expect($html)->not->toContain('autofocus');
});
