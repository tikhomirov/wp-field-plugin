<?php

declare(strict_types=1);

use WpField\Field\Types\AccordionField;

beforeEach(function (): void {
    $this->field = new AccordionField('test_accordion');
});

it('can set sections', function (): void {
    $this->field->sections([['title' => 'Section 1']]);

    expect($this->field->getAttribute('sections'))->toBe([['title' => 'Section 1']]);
});

it('can set items', function (): void {
    $this->field->items([['title' => 'Item 1']]);

    expect($this->field->getAttribute('items'))->toBe([['title' => 'Item 1']]);
});

it('renders with label', function (): void {
    $this->field->label('Accordion Label');
    $this->field->items([['title' => 'Item 1']]);
    $html = $this->field->render();

    expect($html)->toContain('Accordion Label')
        ->and($html)->toContain('<label');
});

it('renders with description', function (): void {
    $this->field->description('Accordion description');
    $this->field->items([['title' => 'Item 1']]);
    $html = $this->field->render();

    expect($html)->toContain('Accordion description');
});

it('renders message when no items provided', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('No items provided');
});

it('normalizes items returns empty array when not array', function (): void {
    $this->field->attribute('items', 'invalid');
    $html = $this->field->render();

    expect($html)->toContain('No items provided');
});

it('normalizes items filters non-array items', function (): void {
    $this->field->items(['valid', ['title' => 'Valid Item'], 'invalid']);
    $html = $this->field->render();

    expect($html)->toContain('Valid Item')
        ->and($html)->not->toContain('No items provided');
});

it('renders item with open state', function (): void {
    $this->field->items([['title' => 'Open Item', 'open' => true]]);
    $html = $this->field->render();

    expect($html)->toContain('is-open')
        ->and($html)->toContain('aria-expanded="true"');
});

it('renders item with closed state by default', function (): void {
    $this->field->items([['title' => 'Closed Item']]);
    $html = $this->field->render();

    expect($html)->not->toContain('is-open')
        ->and($html)->toContain('aria-expanded="false"');
});
