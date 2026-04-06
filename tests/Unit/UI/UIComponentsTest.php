<?php

declare(strict_types=1);

namespace Tests\Unit\UI;

use PHPUnit\Framework\TestCase;
use WpField\UI\AdminShell;
use WpField\UI\AdminShellConfig;
use WpField\UI\Alert;
use WpField\UI\NavItem;
use WpField\UI\UIManager;
use WpField\UI\Wizard;
use WpField\UI\WizardConfig;

class UIComponentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 2).'/bootstrap.php';
        $reflection = new \ReflectionClass(UIManager::class);
        $reflection->getProperty('mode')->setValue(null, 'vanilla');
        $reflection->getProperty('assetsEnqueued')->setValue(null, false);

        $GLOBALS['wp_test_actions'] = [];
        $GLOBALS['wp_test_scripts'] = [];
        $GLOBALS['wp_test_styles'] = [];
        $GLOBALS['wp_test_script_data'] = [];
        $GLOBALS['wp_test_script_is'] = [];
        $GLOBALS['wp_test_style_is'] = [];
        $GLOBALS['wp_test_filters'] = [];
        $_GET = [];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nav_item_helpers_work_for_groups_and_leaves(): void
    {
        $nav = [
            NavItem::leaf('general', 'General'),
            NavItem::group('advanced', 'Advanced', [
                NavItem::leaf('cache', 'Cache', [['id' => 'warmup', 'label' => 'Warmup']]),
            ]),
        ];

        $flat = NavItem::flatSections(['first' => 'First', 'second' => 'Second']);
        $leaves = NavItem::collectLeaves($nav);
        $json = NavItem::toJsonArray($nav);

        $this->assertCount(2, $flat);
        $this->assertSame('general', NavItem::firstLeafId($nav));
        $this->assertSame('cache', NavItem::findLeaf($nav, 'cache')?->id);
        $this->assertNull(NavItem::findLeaf($nav, 'missing'));
        $this->assertCount(2, $leaves);
        $this->assertTrue($nav[1]->isGroup());
        $this->assertTrue($nav[0]->isLeaf());
        $this->assertSame('advanced', $json[1]['id']);
        $this->assertArrayHasKey('children', $json[1]);
        $this->assertArrayHasKey('panels', $json[1]['children'][0]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_shell_renders_and_resolves_request(): void
    {
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

        $this->assertStringContainsString('wp-field-shell extra-shell', $html);
        $this->assertStringContainsString('data-active="general"', $html);
        $this->assertStringContainsString('data-active-panel="main"', $html);
        $this->assertStringContainsString('name="action" value="save_action"', $html);
        $this->assertStringContainsString('Save now', $html);
        $this->assertStringContainsString('data-rendered="general-main"', $html);
        $this->assertStringContainsString('data-rendered="general-advanced"', $html);
        $this->assertStringContainsString('style="display:none"', $html);

        $_GET['section_key'] = 'custom-section';
        $_GET['tab_key'] = 'custom-tab';

        $resolved = AdminShell::resolveFromRequest($nav, $config);

        $this->assertSame(['segment' => 'custom-section', 'panel' => 'custom-tab'], $resolved);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function wizard_renders_and_resolves_request(): void
    {
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

        $this->assertStringContainsString('wp-field-wizard extra-wizard', $html);
        $this->assertStringContainsString('data-initial-step="start"', $html);
        $this->assertStringContainsString('name="action" value="wizard_save"', $html);
        $this->assertStringContainsString('First step', $html);
        $this->assertStringContainsString('data-step-render="start"', $html);
        $this->assertStringContainsString('data-step-render="finish"', $html);
        $this->assertStringContainsString('style="display:none"', $html);

        $_GET['wizard_key'] = 'finish';
        $this->assertSame('finish', Wizard::resolveFromRequest($steps, $config));

        $_GET['wizard_key'] = 'invalid';
        $this->assertSame('start', Wizard::resolveFromRequest($steps, $config));
        $this->assertSame('', Wizard::resolveFromRequest([], $config));

        ob_start();
        Wizard::render([], 'anything', 'https://example.com', '', static function (): void {});
        $empty = (string) ob_get_clean();
        $this->assertSame('', $empty);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alert_renders_with_defaults_and_custom_attributes(): void
    {
        $error = Alert::render('error', 'Boom', '<strong>Fail</strong>', ['class' => 'custom', 'data-id' => '42']);
        $fallback = Alert::render('weird', '', '', ['role' => 'log', 'empty' => '']);

        $this->assertStringContainsString('wp-field-alert--error', $error);
        $this->assertStringContainsString('role="alert"', $error);
        $this->assertStringContainsString('class="wp-field-alert wp-field-alert--error custom"', $error);
        $this->assertStringContainsString('data-id="42"', $error);
        $this->assertStringContainsString('<strong>Fail</strong>', $error);
        $this->assertStringContainsString('Boom', $error);

        $this->assertStringContainsString('wp-field-alert--neutral', $fallback);
        $this->assertStringContainsString('role="log"', $fallback);
        $this->assertStringNotContainsString('empty=""', $fallback);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ui_manager_respects_modes_and_enqueues_assets_once(): void
    {
        UIManager::setMode('invalid');
        $this->assertSame('vanilla', UIManager::getMode());
        $this->assertFalse(UIManager::isReactMode());

        UIManager::setMode('react');
        $this->assertTrue(UIManager::isReactMode());

        $GLOBALS['wp_test_filters']['wp_field_ui_mode'] = static fn (string $mode): string => 'vanilla';
        apply_filters('wp_field_ui_mode', 'react');
        unset($GLOBALS['wp_test_filters']['wp_field_ui_mode']);

        $reflection = new \ReflectionClass(UIManager::class);
        $reflection->getProperty('mode')->setValue(null, 'vanilla');
        $reflection->getProperty('assetsEnqueued')->setValue(null, false);

        $GLOBALS['wp_test_scripts'] = [];
        $GLOBALS['wp_test_styles'] = [];
        $GLOBALS['wp_test_script_data'] = [];
        $GLOBALS['wp_test_media_enqueued'] = false;
        $GLOBALS['wp_test_editor_enqueued'] = false;
        $GLOBALS['wp_test_code_editor_settings'] = [];

        UIManager::enqueueAssets();

        $this->assertTrue($GLOBALS['wp_test_media_enqueued']);
        $this->assertTrue($GLOBALS['wp_test_editor_enqueued']);
        $this->assertSame(['type' => 'text/html'], $GLOBALS['wp_test_code_editor_settings']);
        $this->assertTrue($reflection->getProperty('assetsEnqueued')->getValue());

        $scriptsCount = count($GLOBALS['wp_test_scripts']);
        UIManager::enqueueAssets();
        $this->assertSame($scriptsCount, count($GLOBALS['wp_test_scripts']));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ui_manager_registers_admin_enqueue_hook(): void
    {
        UIManager::init();

        $this->assertContains([
            'hook' => 'admin_enqueue_scripts',
            'callback' => [UIManager::class, 'enqueueAssets'],
        ], $GLOBALS['wp_test_actions']);
    }
}
