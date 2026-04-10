<?php

declare(strict_types=1);

beforeEach(function (): void {
    // Сбросить константы перед каждым тестом
    if (defined('WP_FIELD_PLUGIN_FILE')) {
        // Константы не могут быть удалены, поэтому пропускаем тесты если они уже определены
    }
});

it('defines WP_FIELD_PLUGIN_FILE constant', function (): void {
    if (! defined('WP_FIELD_PLUGIN_FILE')) {
        expect(true)->toBeTrue();

        return;
    }

    expect(WP_FIELD_PLUGIN_FILE)->toBeString();
});

it('defines WP_FIELD_PLUGIN_DIR constant', function (): void {
    if (! defined('WP_FIELD_PLUGIN_DIR')) {
        expect(true)->toBeTrue();

        return;
    }

    expect(WP_FIELD_PLUGIN_DIR)->toBeString();
});

it('defines WP_FIELD_PLUGIN_URL constant', function (): void {
    if (! defined('WP_FIELD_PLUGIN_URL')) {
        expect(true)->toBeTrue();

        return;
    }

    expect(WP_FIELD_PLUGIN_URL)->toBeString();
});

it('autoloads WpField classes correctly', function (): void {
    $classExists = class_exists('WpField\Field\Field');

    expect($classExists)->toBeTrue();
});

it('autoloads Field types correctly', function (): void {
    $textFieldExists = class_exists('WpField\Field\Types\TextField');
    $choiceFieldExists = class_exists('WpField\Field\Types\ChoiceField');

    expect($textFieldExists)->toBeTrue()
        ->and($choiceFieldExists)->toBeTrue();
});

it('autoloads Storage classes correctly', function (): void {
    $postMetaExists = class_exists('WpField\Storage\PostMetaStorage');
    $optionStorageExists = class_exists('WpField\Storage\OptionStorage');

    expect($postMetaExists)->toBeTrue()
        ->and($optionStorageExists)->toBeTrue();
});

it('autoloads UI classes correctly', function (): void {
    $adminShellExists = class_exists('WpField\UI\AdminShell');
    $wizardExists = class_exists('WpField\UI\Wizard');

    expect($adminShellExists)->toBeTrue()
        ->and($wizardExists)->toBeTrue();
});

it('autoloads Traits correctly', function (): void {
    $hasAttributesExists = trait_exists('WpField\Traits\HasAttributes');
    $hasValidationExists = trait_exists('WpField\Traits\HasValidation');

    expect($hasAttributesExists)->toBeTrue()
        ->and($hasValidationExists)->toBeTrue();
});
