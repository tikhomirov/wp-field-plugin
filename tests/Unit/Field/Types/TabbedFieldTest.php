<?php

declare(strict_types=1);

use WpField\Field\Types\TabbedField;

beforeEach(function (): void {
    $this->field = new TabbedField('test_tabbed');
});

it('can set tabs', function (): void {
    $tabs = [
        ['title' => 'Tab 1', 'content' => 'Content 1'],
        ['title' => 'Tab 2', 'content' => 'Content 2'],
    ];
    $this->field->tabs($tabs);

    expect($this->field->getAttribute('tabs'))->toBe($tabs);
});

it('tabs is chainable', function (): void {
    $result = $this->field->tabs([['title' => 'Tab 1']]);

    expect($result)->toBe($this->field);
});

it('renders tabbed field with tabs', function (): void {
    $this->field->tabs([
        ['title' => 'Tab 1', 'content' => 'Content 1'],
        ['title' => 'Tab 2', 'content' => 'Content 2'],
    ]);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-tabbed')
        ->and($html)->toContain('Tab 1')
        ->and($html)->toContain('Tab 2');
});

it('renders message when no tabs provided', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('No tabs provided');
});

it('renders tab with icon', function (): void {
    $this->field->tabs([
        ['title' => 'Tab 1', 'icon' => 'dashicons-admin-settings'],
    ]);
    $html = $this->field->render();

    expect($html)->toContain('dashicons-admin-settings');
});

it('renders active tab based on active flag', function (): void {
    $this->field->tabs([
        ['title' => 'Tab 1'],
        ['title' => 'Tab 2', 'active' => true],
    ]);
    $html = $this->field->render();

    expect($html)->toContain('active');
});

it('renders first tab as active by default', function (): void {
    $this->field->tabs([
        ['title' => 'Tab 1'],
        ['title' => 'Tab 2'],
    ]);
    $html = $this->field->render();

    expect($html)->toContain('active');
});

it('renders tab content', function (): void {
    $this->field->tabs([
        ['title' => 'Tab 1', 'content' => 'Tab content here'],
    ]);
    $html = $this->field->render();

    expect($html)->toContain('Tab content here');
});

it('renders tab with nested fields', function (): void {
    $this->field->tabs([
        ['title' => 'Tab 1', 'fields' => [['type' => 'text', 'id' => 'field1']]],
    ]);
    $html = $this->field->render();

    expect($html)->toContain('field1');
});

it('renders tabbed field with label', function (): void {
    $this->field->tabs([['title' => 'Tab 1']]);
    $this->field->label('Tabbed Content');
    $html = $this->field->render();

    expect($html)->toContain('Tabbed Content');
});

it('renders tabbed field with description', function (): void {
    $this->field->tabs([['title' => 'Tab 1']]);
    $this->field->description('Organize content in tabs');
    $html = $this->field->render();

    expect($html)->toContain('Organize content in tabs');
});

it('filters non-array tabs', function (): void {
    $this->field->tabs(['invalid']);
    $html = $this->field->render();

    expect($html)->toContain('No tabs provided');
});
