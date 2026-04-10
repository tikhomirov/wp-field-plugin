<?php

declare(strict_types=1);

use WpField\Field\Types\MapField;

beforeEach(function (): void {
    $this->field = new MapField('test_map');
});

it('can set zoom', function (): void {
    $this->field->zoom(15);

    expect($this->field->getAttribute('zoom'))->toBe(15);
});

it('can set center', function (): void {
    $this->field->center(['lat' => 55.7558, 'lng' => 37.6173]);

    expect($this->field->getAttribute('center'))->toBe(['lat' => 55.7558, 'lng' => 37.6173]);
});

it('can set api key', function (): void {
    $this->field->apiKey('test_api_key');

    expect($this->field->getAttribute('api_key'))->toBe('test_api_key');
});

it('can set provider', function (): void {
    $this->field->provider('openstreetmap');

    expect($this->field->getAttribute('provider'))->toBe('openstreetmap');
});

it('renders map with label', function (): void {
    $this->field->label('Map Location');
    $html = $this->field->render();

    expect($html)->toContain('Map Location')
        ->and($html)->toContain('<label');
});

it('renders map with description', function (): void {
    $this->field->description('Select location on map');
    $html = $this->field->render();

    expect($html)->toContain('Select location on map');
});

it('validates non-array value when not required', function (): void {
    expect($this->field->validate('invalid'))->toBeTrue();
});

it('validates non-array value when required', function (): void {
    $this->field->required();
    expect($this->field->validate('invalid'))->toBeFalse();
});

it('normalizes center when not array', function (): void {
    $this->field->attribute('center', 'invalid');
    $html = $this->field->render();

    expect($html)->toContain('55.7558')
        ->and($html)->toContain('37.6173');
});

it('sanitizes coordinate with empty value', function (): void {
    $sanitized = $this->field->sanitize(['lat' => '', 'lng' => '']);

    expect($sanitized['lat'])->toBe('')
        ->and($sanitized['lng'])->toBe('');
});

it('sanitizes coordinate with non-numeric value', function (): void {
    $sanitized = $this->field->sanitize(['lat' => 'invalid', 'lng' => 'invalid']);

    expect($sanitized['lat'])->toBe('')
        ->and($sanitized['lng'])->toBe('');
});

it('sanitizes coordinate with out of range value', function (): void {
    $sanitized = $this->field->sanitize(['lat' => '100', 'lng' => '200']);

    expect($sanitized['lat'])->toBe('')
        ->and($sanitized['lng'])->toBe('');
});

it('validates coordinate with empty value', function (): void {
    expect($this->field->validate(['lat' => '', 'lng' => '']))->toBeTrue();
});

it('validates coordinate with non-numeric value', function (): void {
    expect($this->field->validate(['lat' => 'invalid', 'lng' => 'invalid']))->toBeFalse();
});

it('renders map with google provider warning when no api key', function (): void {
    $this->field->provider('google');
    $html = $this->field->render();

    expect($html)->toContain('Google Maps API key required');
});

it('renders map without warning when api key provided', function (): void {
    $this->field->provider('google')->apiKey('test_key');
    $html = $this->field->render();

    expect($html)->not->toContain('Google Maps API key required');
});

it('renders map with custom zoom', function (): void {
    $this->field->zoom(18);
    $html = $this->field->render();

    expect($html)->toContain('data-zoom="18"');
});

it('enforces minimum zoom of 1', function (): void {
    $this->field->zoom(0);
    $html = $this->field->render();

    expect($html)->toContain('data-zoom="1"');
});
