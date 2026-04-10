<?php

declare(strict_types=1);

use WpField\Container\SettingsContainer;
use WpField\Field\AbstractField;

class TestSettingsField extends AbstractField
{
    public function __construct(string $name = 'test_field')
    {
        parent::__construct($name, 'text');
    }

    public function render(): string
    {
        return '<input type="text" name="'.$this->name.'">';
    }

    public function sanitize(mixed $value): mixed
    {
        return is_string($value) ? sanitize_text_field($value) : $value;
    }

    public function validate(mixed $value): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}

beforeEach(function (): void {
    $this->container = new SettingsContainer('test_settings', [
        'page_title' => 'Test Settings',
        'menu_title' => 'Test Menu',
        'capability' => 'manage_options',
        'menu_slug' => 'test-slug',
        'icon' => 'dashicons-admin-tools',
        'position' => 100,
    ]);
});

it('can construct with default OptionStorage', function (): void {
    expect($this->container->getId())->toBe('test_settings');
});

it('can get config values', function (): void {
    expect($this->container->getConfig('page_title'))->toBe('Test Settings')
        ->and($this->container->getConfig('menu_title'))->toBe('Test Menu')
        ->and($this->container->getConfig('capability'))->toBe('manage_options')
        ->and($this->container->getConfig('menu_slug'))->toBe('test-slug')
        ->and($this->container->getConfig('icon'))->toBe('dashicons-admin-tools')
        ->and($this->container->getConfig('position'))->toBe(100);
});

it('supports parent_slug for submenu', function (): void {
    $container = new SettingsContainer('submenu_settings', [
        'parent_slug' => 'options-general.php',
    ]);

    expect($container->getConfig('parent_slug'))->toBe('options-general.php');
});

it('registers hooks on register method', function (): void {
    global $wp_test_actions;
    $wp_test_actions = [];

    $this->container->register();

    $hooks = array_column($wp_test_actions, 'hook');
    expect(in_array('admin_menu', $hooks))->toBeTrue()
        ->and(in_array('admin_init', $hooks))->toBeTrue();
});

it('renders settings page structure', function (): void {
    $this->container->addField((new TestSettingsField)->label('Test Label'));

    ob_start();
    $this->container->render();
    $output = ob_get_clean();

    expect($output)->toContain('<div class="wrap">')
        ->and($output)->toContain('<h1>Test Settings</h1>')
        ->and($output)->toContain('<form method="post" action="options.php">')
        ->and($output)->toContain('<table class="form-table">')
        ->and($output)->toContain('Test Label');
});

it('renders with default page title when not configured', function (): void {
    $container = new SettingsContainer('test_settings');

    ob_start();
    $container->render();
    $output = ob_get_clean();

    expect($output)->toContain('<h1>Settings</h1>');
});

it('addSettingsPage adds menu page when parent_slug is not set', function (): void {
    global $wp_test_menu_pages;
    $wp_test_menu_pages = [];

    $this->container->addSettingsPage();

    expect(count($wp_test_menu_pages))->toBe(1)
        ->and($wp_test_menu_pages[0]['page_title'])->toBe('Test Settings')
        ->and($wp_test_menu_pages[0]['menu_title'])->toBe('Test Menu')
        ->and($wp_test_menu_pages[0]['capability'])->toBe('manage_options')
        ->and($wp_test_menu_pages[0]['menu_slug'])->toBe('test-slug')
        ->and($wp_test_menu_pages[0]['icon'])->toBe('dashicons-admin-tools')
        ->and($wp_test_menu_pages[0]['position'])->toBe(100);
});

it('addSettingsPage adds submenu page when parent_slug is set', function (): void {
    $container = new SettingsContainer('test_settings', [
        'parent_slug' => 'options-general.php',
    ]);

    global $wp_test_submenu_pages;
    $wp_test_submenu_pages = [];

    $container->addSettingsPage();

    expect(count($wp_test_submenu_pages))->toBe(1)
        ->and($wp_test_submenu_pages[0]['parent_slug'])->toBe('options-general.php')
        ->and($wp_test_submenu_pages[0]['page_title'])->toBe('Settings')
        ->and($wp_test_submenu_pages[0]['menu_title'])->toBe('Settings');
});

it('addSettingsPage uses default values when config not provided', function (): void {
    $container = new SettingsContainer('test_settings');

    global $wp_test_menu_pages;
    $wp_test_menu_pages = [];

    $container->addSettingsPage();

    expect($wp_test_menu_pages[0]['page_title'])->toBe('Settings')
        ->and($wp_test_menu_pages[0]['menu_title'])->toBe('Settings')
        ->and($wp_test_menu_pages[0]['capability'])->toBe('manage_options')
        ->and($wp_test_menu_pages[0]['menu_slug'])->toBe('test_settings')
        ->and($wp_test_menu_pages[0]['icon'])->toBe('dashicons-admin-generic')
        ->and($wp_test_menu_pages[0]['position'])->toBeNull();
});

it('registerSettings registers fields with WordPress', function (): void {
    $this->container->addField(new TestSettingsField);

    global $wp_test_registered_settings;
    $wp_test_registered_settings = [];

    $this->container->registerSettings();

    expect(count($wp_test_registered_settings))->toBe(1)
        ->and($wp_test_registered_settings[0]['option_group'])->toBe('test_settings')
        ->and($wp_test_registered_settings[0]['option_name'])->toBe('test_field');
});

it('save method does nothing as settings are saved automatically', function (): void {
    $this->container->save(0);

    expect(true)->toBeTrue();
});

it('uses default config values when not provided', function (): void {
    $container = new SettingsContainer('test_settings');

    expect($container->getConfig('page_title'))->toBeNull()
        ->and($container->getConfig('menu_title'))->toBeNull()
        ->and($container->getConfig('capability'))->toBeNull()
        ->and($container->getConfig('menu_slug'))->toBeNull()
        ->and($container->getConfig('icon'))->toBeNull()
        ->and($container->getConfig('position'))->toBeNull();
});

it('handles type casting for position config', function (): void {
    $container = new SettingsContainer('test_settings', [
        'position' => '100',
    ]);

    expect($container->getConfig('position'))->toBe('100');
});
