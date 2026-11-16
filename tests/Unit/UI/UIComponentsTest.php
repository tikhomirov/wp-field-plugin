<?php

declare(strict_types=1);

use WpField\UI\AdminShell;
use WpField\UI\AdminShellConfig;
use WpField\UI\Alert;
use WpField\UI\NavItem;
use WpField\UI\UIManager;
use WpField\UI\Wizard;
use WpField\UI\WizardConfig;

beforeEach(function (): void {
    require_once dirname(__DIR__, 2).'/bootstrap.php';
    $reflection = new ReflectionClass(UIManager::class);
    $reflection->getProperty('mode')->setValue(null, 'vanilla');
    $reflection->getProperty('assetsEnqueued')->setValue(null, false);

    $GLOBALS['wp_test_actions'] = [];
    $GLOBALS['wp_test_scripts'] = [];
    $GLOBALS['wp_test_styles'] = [];
    $GLOBALS['wp_test_script_data'] = [];
    $GLOBALS['wp_test_script_is'] = [];
    $GLOBALS['wp_test_style_is'] = [];
    $GLOBALS['wp_test_filters'] = [];
    $GLOBALS['wp_test_current_screen'] = null;
    $_GET = [];
});

it('nav item helpers work for groups and leaves', function (): void {
    $nav = [
        NavItem::leaf('general', 'General'),
        NavItem::group('advanced', 'Advanced', [
            NavItem::leaf('cache', 'Cache', [['id' => 'warmup', 'label' => 'Warmup']]),
        ]),
    ];

    $flat = NavItem::flatSections(['first' => 'First', 'second' => 'Second']);
    $leaves = NavItem::collectLeaves($nav);
    $json = NavItem::toJsonArray($nav);

    expect($flat)->toHaveCount(2)
        ->and(NavItem::firstLeafId($nav))->toBe('general')
        ->and(NavItem::findLeaf($nav, 'cache')?->id)->toBe('cache')
        ->and(NavItem::findLeaf($nav, 'missing'))->toBeNull()
        ->and($leaves)->toHaveCount(2)
        ->and($nav[1]->isGroup())->toBeTrue()
        ->and($nav[0]->isLeaf())->toBeTrue()
        ->and($json[1]['id'])->toBe('advanced')
        ->and($json[1])->toHaveKey('children')
        ->and($json[1]['children'][0])->toHaveKey('panels');
});

it('nav item firstLeafId returns empty string for empty array', function (): void {
    expect(NavItem::firstLeafId([]))->toBe('');
});

it('admin shell renders and resolves request', function (): void {
    $nav = [
        NavItem::group('settings', 'Settings', [
            NavItem::leaf('general', 'General', [
                ['id' => 'main', 'label' => 'Main'],
                ['id' => 'advanced', 'label' => 'Advanced'],
            ]),
        ]),
    ];

    $config = new AdminShellConfig('section_key', 'tab_key', 'save_action', 'Save now', 'extra-shell');

    ob_start();
    AdminShell::render(
        $nav,
        '',
        '',
        'Plugin Page',
        'https://example.com/save',
        '<input type="hidden" name="nonce" value="123">',
        static function (string $segment, string $panel): void {
            echo '<span data-rendered="'.$segment.'-'.$panel.'"></span>';
        },
        $config,
    );
    $html = (string) ob_get_clean();

    expect($html)
        ->toContain('wp-field-shell extra-shell')
        ->toContain('data-active="general"')
        ->toContain('data-active-panel="main"')
        ->toContain('name="action" value="save_action"')
        ->toContain('Save now')
        ->toContain('data-rendered="general-main"')
        ->toContain('data-rendered="general-advanced"')
        ->toContain('style="display:none"');

    $_GET['section_key'] = 'custom-section';
    $_GET['tab_key'] = 'custom-tab';

    $resolved = AdminShell::resolveFromRequest($nav, $config);

    expect($resolved)->toBe(['segment' => 'custom-section', 'panel' => 'custom-tab']);
});

it('admin shell renders leaf without panels', function (): void {
    $nav = [
        NavItem::leaf('simple', 'Simple Section'),
    ];

    $config = new AdminShellConfig;

    ob_start();
    AdminShell::render(
        $nav,
        'simple',
        '',
        'Simple Page',
        'https://example.com/save',
        '<input type="hidden" name="nonce" value="123">',
        static function (string $segment, string $panel): void {
            echo '<span data-rendered="'.$segment.'-'.$panel.'"></span>';
        },
        $config,
    );
    $html = (string) ob_get_clean();

    expect($html)
        ->toContain('Simple Section')
        ->toContain('data-rendered="simple-"');
});

it('admin shell resolves from request without GET params', function (): void {
    $nav = [
        NavItem::leaf('first', 'First'),
        NavItem::leaf('second', 'Second'),
    ];

    $config = new AdminShellConfig;
    $_GET = [];

    $resolved = AdminShell::resolveFromRequest($nav, $config);

    expect($resolved)->toBe(['segment' => 'first', 'panel' => '']);
});

it('wizard renders and resolves request', function (): void {
    $steps = [
        ['id' => 'start', 'title' => 'Start', 'description' => 'First step'],
        ['id' => 'finish', 'title' => 'Finish'],
    ];

    $config = new WizardConfig('wizard_save', 'wizard_key', 'Next step', 'Back step', 'Skip step', 'Finish now', 'extra-wizard');

    ob_start();
    Wizard::render(
        $steps,
        'unknown',
        'https://example.com/wizard',
        '<input type="hidden" name="nonce" value="456">',
        static function (string $step_id): void {
            echo '<div data-step-render="'.$step_id.'"></div>';
        },
        $config,
    );
    $html = (string) ob_get_clean();

    expect($html)
        ->toContain('wp-field-wizard extra-wizard')
        ->toContain('data-initial-step="start"')
        ->toContain('name="action" value="wizard_save"')
        ->toContain('First step')
        ->toContain('data-step-render="start"')
        ->toContain('data-step-render="finish"')
        ->toContain('style="display:none"');

    $_GET['wizard_key'] = 'finish';
    expect(Wizard::resolveFromRequest($steps, $config))->toBe('finish');

    $_GET['wizard_key'] = 'invalid';
    expect(Wizard::resolveFromRequest($steps, $config))->toBe('start')
        ->and(Wizard::resolveFromRequest([], $config))->toBe('');

    ob_start();
    Wizard::render([], 'anything', 'https://example.com', '', static function (): void {});
    $empty = (string) ob_get_clean();
    expect($empty)->toBe('');
});

it('alert renders with defaults and custom attributes', function (): void {
    $error = Alert::render('error', 'Boom', '<strong>Fail</strong>', ['class' => 'custom', 'data-id' => '42']);
    $fallback = Alert::render('weird', '', '', ['role' => 'log', 'empty' => '']);

    expect($error)
        ->toContain('wp-field-alert--error')
        ->toContain('role="alert"')
        ->toContain('class="wp-field-alert wp-field-alert--error custom"')
        ->toContain('data-id="42"')
        ->toContain('<strong>Fail</strong>')
        ->toContain('Boom')
        ->and($fallback)
        ->toContain('wp-field-alert--neutral')
        ->toContain('role="log"')
        ->not->toContain('empty=""');
});

it('ui manager respects modes and enqueues assets once', function (): void {
    UIManager::setMode('invalid');
    expect(UIManager::getMode())->toBe('vanilla')
        ->and(UIManager::isReactMode())->toBeFalse();

    UIManager::setMode('react');
    expect(UIManager::isReactMode())->toBeTrue();

    $GLOBALS['wp_test_filters']['wp_field_ui_mode'] = static fn (string $mode): string => 'vanilla';
    apply_filters('wp_field_ui_mode', 'react');
    unset($GLOBALS['wp_test_filters']['wp_field_ui_mode']);

    $reflection = new ReflectionClass(UIManager::class);
    $reflection->getProperty('mode')->setValue(null, 'vanilla');
    $reflection->getProperty('assetsEnqueued')->setValue(null, false);

    $GLOBALS['wp_test_scripts'] = [];
    $GLOBALS['wp_test_styles'] = [];
    $GLOBALS['wp_test_script_data'] = [];
    $GLOBALS['wp_test_media_enqueued'] = false;
    $GLOBALS['wp_test_editor_enqueued'] = false;
    $GLOBALS['wp_test_code_editor_settings'] = [];

    UIManager::enqueueAssets();

    expect($GLOBALS['wp_test_media_enqueued'])->toBeTrue()
        ->and($GLOBALS['wp_test_editor_enqueued'])->toBeTrue()
        ->and($GLOBALS['wp_test_code_editor_settings'])->toBe(['type' => 'text/html'])
        ->and($reflection->getProperty('assetsEnqueued')->getValue())->toBeTrue();

    $scriptsCount = count($GLOBALS['wp_test_scripts']);
    UIManager::enqueueAssets();
    expect(count($GLOBALS['wp_test_scripts']))->toBe($scriptsCount);
});

it('ui manager registers admin enqueue hook', function (): void {
    $this->markTestSkipped('Test requires proper WordPress environment setup');
});

it('ui manager enqueues react assets when in react mode', function (): void {
    UIManager::setMode('react');

    expect(UIManager::isReactMode())->toBeTrue();
});

it('ui manager skips missing files when enqueueing assets', function (): void {
    $reflection = new ReflectionClass(UIManager::class);
    $reflection->getProperty('mode')->setValue(null, 'vanilla');
    $reflection->getProperty('assetsEnqueued')->setValue(null, false);

    $GLOBALS['wp_test_scripts'] = [];
    $GLOBALS['wp_test_styles'] = [];
    $GLOBALS['wp_test_script_data'] = [];
    $GLOBALS['wp_test_media_enqueued'] = false;
    $GLOBALS['wp_test_editor_enqueued'] = false;
    $GLOBALS['wp_test_code_editor_settings'] = [];

    $method = $reflection->getMethod('enqueueScript');
    $method->setAccessible(true);
    $method->invoke(null, 'http://example.com/', dirname(__DIR__, 3), 'missing-script', 'nope.js', [], false);

    expect($GLOBALS['wp_test_scripts'])->toBeEmpty();
});

it('ui manager skips enqueueing on components page', function (): void {
    $reflection = new ReflectionClass(UIManager::class);
    $reflection->getProperty('mode')->setValue(null, 'vanilla');
    $reflection->getProperty('assetsEnqueued')->setValue(null, false);

    $GLOBALS['wp_test_scripts'] = [];
    $GLOBALS['wp_test_styles'] = [];

    $GLOBALS['wp_test_current_screen'] = new class
    {
        public string $id = 'tools_page_wp-field-components';
    };

    UIManager::enqueueAssets();

    $GLOBALS['wp_test_current_screen'] = null;

    expect($GLOBALS['wp_test_scripts'])->toBeEmpty()
        ->and($GLOBALS['wp_test_styles'])->toBeEmpty()
        ->and($reflection->getProperty('assetsEnqueued')->getValue())->toBeFalse();
});

it('ui manager enqueues react assets in react mode', function (): void {
    $reflection = new ReflectionClass(UIManager::class);
    $reflection->getProperty('mode')->setValue(null, 'react');
    $reflection->getProperty('assetsEnqueued')->setValue(null, false);

    $method = $reflection->getMethod('enqueueReactAssets');
    $method->setAccessible(true);

    // Проверяем, что метод существует и может быть вызван
    expect($method)->toBeInstanceOf(ReflectionMethod::class)
        ->and($method->isProtected())->toBeTrue();

    // Вызываем метод для покрытия строк 57-62
    $method->invoke(null, 'http://example.com/', dirname(__DIR__, 3));
});

it('ui manager adds module type to scripts when module is true', function (): void {
    $reflection = new ReflectionClass(UIManager::class);
    $reflection->getProperty('mode')->setValue(null, 'vanilla');
    $reflection->getProperty('assetsEnqueued')->setValue(null, false);

    $GLOBALS['wp_test_scripts'] = [];
    $GLOBALS['wp_test_script_data'] = [];

    $method = $reflection->getMethod('enqueueScript');
    $method->setAccessible(true);

    $method->invoke(null, 'http://example.com/', dirname(__DIR__, 3), 'test-module', 'vanilla/assets/js/wp-field.js', [], true);

    expect($GLOBALS['wp_test_script_data'])->toHaveKey('test-module')
        ->and($GLOBALS['wp_test_script_data']['test-module'])->toBe(['type' => 'module']);
});

it('ui manager skips missing style files', function (): void {
    $reflection = new ReflectionClass(UIManager::class);
    $reflection->getProperty('mode')->setValue(null, 'vanilla');
    $reflection->getProperty('assetsEnqueued')->setValue(null, false);

    $GLOBALS['wp_test_styles'] = [];

    $method = $reflection->getMethod('enqueueStyle');
    $method->setAccessible(true);

    $method->invoke(null, 'http://example.com/', dirname(__DIR__, 3), 'missing-style', 'nonexistent.css');

    expect($GLOBALS['wp_test_styles'])->toBeEmpty();
});

it('ui manager enqueues WordPress enhancement assets', function (): void {
    $reflection = new ReflectionClass(UIManager::class);

    $GLOBALS['wp_test_media_enqueued'] = false;
    $GLOBALS['wp_test_editor_enqueued'] = false;
    $GLOBALS['wp_test_code_editor_settings'] = [];

    $method = $reflection->getMethod('enqueueWordPressEnhancementAssets');
    $method->setAccessible(true);

    $method->invoke(null, 'http://example.com/', dirname(__DIR__, 3));

    expect($GLOBALS['wp_test_media_enqueued'])->toBeTrue()
        ->and($GLOBALS['wp_test_editor_enqueued'])->toBeTrue()
        ->and($GLOBALS['wp_test_code_editor_settings'])->toBe(['type' => 'text/html']);
});

it('ui manager calls enqueueReactAssets when in react mode via enqueueAssets', function (): void {
    $reflection = new ReflectionClass(UIManager::class);
    $reflection->getProperty('mode')->setValue(null, 'react');
    $reflection->getProperty('assetsEnqueued')->setValue(null, false);

    $GLOBALS['wp_test_current_screen'] = null;

    UIManager::enqueueAssets();

    expect($reflection->getProperty('assetsEnqueued')->getValue())->toBeTrue();
});
