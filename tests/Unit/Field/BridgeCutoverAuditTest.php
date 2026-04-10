<?php

declare(strict_types=1);

use WpField\Field\Field;
use WpField\Field\Types\LegacyWrapperField;

it('official registry types are routed to native classes, not vanilla wrapper fallback', function (): void {
    $officialTypes = [
        'text', 'password', 'email', 'url', 'tel', 'number', 'range', 'hidden', 'textarea',
        'select', 'multiselect', 'radio', 'checkbox', 'checkbox_group',
        'editor', 'media', 'image', 'file', 'gallery', 'color',
        'date', 'time', 'datetime', 'group', 'repeater', 'switcher', 'button_set', 'spinner', 'slider',
        'heading', 'subheading', 'notice', 'content', 'fieldset',
        'accordion', 'tabbed', 'typography', 'spacing', 'dimensions', 'border', 'background', 'link_color',
        'color_group', 'image_select', 'code_editor', 'icon', 'map',
        'sortable', 'sorter', 'palette', 'link', 'backup',
        'image_picker', 'imagepicker', 'date_time', 'datetime-local',
    ];

    foreach ($officialTypes as $type) {
        $field = Field::make($type, 'audit_field');
        expect($field)->not->toBeInstanceOf(LegacyWrapperField::class);
    }

    expect(Field::make('my_custom_type', 'payload'))->toBeInstanceOf(LegacyWrapperField::class);
});

it('no official field type class uses LegacyAdapterBridge anymore', function (): void {
    $files = glob(__DIR__.'/../../../src/Field/Types/*.php');
    expect($files)->toBeArray()->not->toBeEmpty();

    foreach ($files as $file) {
        $basename = basename($file);
        if ($basename === 'LegacyAdapterBridge.php' || $basename === 'LegacyWrapperField.php') {
            continue;
        }

        $content = file_get_contents($file);
        expect($content)->not->toContain('use LegacyAdapterBridge;')
            ->and($content)->not->toContain('renderViaLegacy(');
    }
});
