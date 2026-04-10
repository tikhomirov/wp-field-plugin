<?php

declare(strict_types=1);

use WpField\Field\Types\BackupField;

beforeEach(function (): void {
    $this->field = new BackupField('test_backup');
});

it('renders backup field without export data', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-backup')
        ->and($html)->toContain('Export Settings')
        ->and($html)->toContain('Import Settings')
        ->and($html)->toContain('No data to export');
});

it('renders backup field with export data', function (): void {
    $this->field->attribute('export_data', ['key1' => 'value1', 'key2' => 'value2']);
    $html = $this->field->render();

    expect($html)->toContain('wp-field-backup-export')
        ->and($html)->toContain('Copy to Clipboard')
        ->and($html)->toContain('Download JSON');
});

it('renders backup field with label', function (): void {
    $this->field->label('Backup Settings');
    $html = $this->field->render();

    expect($html)->toContain('Backup Settings')
        ->and($html)->toContain('<label');
});

it('renders backup field with description', function (): void {
    $this->field->description('Export and import settings');
    $html = $this->field->render();

    expect($html)->toContain('Export and import settings');
});

it('renders import textarea', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-backup-import')
        ->and($html)->toContain('Paste JSON data here');
});

it('renders validate button', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('Validate JSON');
});

it('renders status div', function (): void {
    $html = $this->field->render();

    expect($html)->toContain('wp-field-backup-status');
});

it('sanitizes string value', function (): void {
    $sanitized = $this->field->sanitize('  test value  ');

    expect($sanitized)->toBe('test value');
});

it('sanitizes non-scalar to empty string', function (): void {
    $sanitized = $this->field->sanitize(['invalid']);

    expect($sanitized)->toBe('');
});

it('validates empty string', function (): void {
    expect($this->field->validate(''))->toBeTrue();
});

it('validates valid JSON', function (): void {
    $json = json_encode(['key' => 'value']);
    expect($this->field->validate($json))->toBeTrue();
});

it('validates invalid JSON', function (): void {
    expect($this->field->validate('invalid json'))->toBeFalse();
});

it('validates non-scalar as false', function (): void {
    expect($this->field->validate(['invalid']))->toBeFalse();
});
