<?php

declare(strict_types=1);

use WpField\Field\Field;

beforeEach(function (): void {
    require_once dirname(__DIR__, 2).'/WP_Field.php';
});

it('maps flat when conditions to legacy dependency', function (): void {
    $html = Field::legacy('select', 'legacy_select')
        ->label('Legacy Select')
        ->attribute('options', [
            'yes' => 'Yes',
            'no' => 'No',
        ])
        ->when('delivery_type', '==', 'courier')
        ->render();

    expect($html)
        ->toContain('data-dependency')
        ->toContain('delivery_type')
        ->toContain('courier');
});

it('maps or conditions to legacy dependency relation', function (): void {
    $html = Field::legacy('select', 'legacy_select')
        ->label('Legacy Select')
        ->attribute('options', [
            'yes' => 'Yes',
            'no' => 'No',
        ])
        ->when('field_a', '==', '1')
        ->orWhen('field_b', '==', '2')
        ->render();

    expect($html)
        ->toContain('data-dependency')
        ->toContain('field_a')
        ->toContain('field_b')
        ->toContain('OR');
});
