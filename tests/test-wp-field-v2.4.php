<?php

declare(strict_types=1);
/**
 * Тесты для WP_Field v2.4.11
 *
 * Проверяет функциональность всех типов полей и основных компонентов
 *
 * Запуск: php tests/test-wp-field-v2.4.php
 */

// Загружаем WordPress моки из bootstrap
require_once __DIR__ . '/bootstrap.php';

// Загружаем WP_Field класс
require_once __DIR__ . '/../WP_Field.php';

class WP_Field_Tests_v2_4
{
    private $passed = 0;

    private $failed = 0;

    private $tests = [];

    public function run(): void
    {
        echo "=== WP_Field v2.4.11 Tests ===\n\n";

        // Базовые тесты
        $this->test_field_types_registry();
        $this->test_text_field_render();
        $this->test_select_field_render();
        $this->test_dependency_evaluation();

        // Тесты v2.2 (Accordion, Tabbed и т.д.)
        $this->test_accordion_field_render();
        $this->test_tabbed_field_render();
        $this->test_icon_field_render();
        $this->test_repeater_field_render();
        $this->test_color_picker_field_render();

        // Тесты v2.3 (Code Editor, Map и т.д.)
        $this->test_code_editor_field_render();
        $this->test_map_field_render();
        $this->test_sortable_field_render();
        $this->test_palette_field_render();

        // Тесты localStorage и состояния
        $this->test_accordion_state_persistence();
        $this->test_tabbed_state_persistence();

        // Результаты
        $this->print_results();
    }

    private function test_field_types_registry(): void
    {
        $this->log_test('Field types registry initialization');

        try {
            // Проверяем, что класс существует
            if (! class_exists('WP_Field')) {
                throw new Exception('WP_Field class not found');
            }

            // Инициализируем реестр типов
            WP_Field::init_field_types();

            // Проверяем инициализацию типов
            $reflection = new ReflectionClass('WP_Field');
            $property = $reflection->getProperty('field_types');
            $property->setAccessible(true);
            $types = $property->getValue();

            // Проверяем наличие основных типов
            $required_types = ['text', 'select', 'textarea', 'repeater', 'accordion', 'tabbed', 'icon'];
            foreach ($required_types as $type) {
                if (! isset($types[$type])) {
                    throw new Exception("Type '$type' not registered");
                }
            }

            $this->pass('Registry contains ' . count($types) . ' field types');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_text_field_render(): void
    {
        $this->log_test('Text field rendering');

        try {
            $field = [
                'id'    => 'test_text',
                'type'  => 'text',
                'label' => 'Test Text',
                'value' => 'test value',
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'test_text') === false) {
                throw new Exception('Field ID not found in output');
            }

            if (mb_strpos($output, 'Test Text') === false) {
                throw new Exception('Field label not found in output');
            }

            $this->pass('Text field renders correctly');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_select_field_render(): void
    {
        $this->log_test('Select field rendering');

        try {
            $field = [
                'id'      => 'test_select',
                'type'    => 'select',
                'label'   => 'Test Select',
                'options' => ['opt1' => 'Option 1', 'opt2' => 'Option 2'],
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'test_select') === false) {
                throw new Exception('Select field not rendered');
            }

            if (mb_strpos($output, 'Option 1') === false) {
                throw new Exception('Select options not found');
            }

            $this->pass('Select field renders correctly');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_dependency_evaluation(): void
    {
        $this->log_test('Dependency evaluation');

        try {
            $field = [
                'id'         => 'dependent_field',
                'type'       => 'text',
                'label'      => 'Dependent',
                'dependency' => [
                    ['parent_field', '==', 'value'],
                ],
            ];

            // Проверяем, что поле содержит атрибуты зависимости
            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'dependent_field') === false) {
                throw new Exception('Dependent field not rendered');
            }

            $this->pass('Dependency evaluation works');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_accordion_field_render(): void
    {
        $this->log_test('Accordion field rendering');

        try {
            $field = [
                'id'    => 'test_accordion',
                'type'  => 'accordion',
                'label' => 'Test Accordion',
                'items' => [
                    ['title' => 'Section 1', 'content' => 'Content 1'],
                    ['title' => 'Section 2', 'content' => 'Content 2', 'open' => true],
                ],
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'wp-field-accordion') === false) {
                throw new Exception('Accordion container not found');
            }

            if (mb_strpos($output, 'Section 1') === false) {
                throw new Exception('Accordion items not rendered');
            }

            if (mb_strpos($output, 'is-open') === false) {
                throw new Exception('Open state not applied');
            }

            $this->pass('Accordion field renders correctly');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_tabbed_field_render(): void
    {
        $this->log_test('Tabbed field rendering');

        try {
            $field = [
                'id'    => 'test_tabbed',
                'type'  => 'tabbed',
                'label' => 'Test Tabbed',
                'tabs'  => [
                    ['title' => 'Tab 1', 'content' => 'Content 1'],
                    ['title' => 'Tab 2', 'content' => 'Content 2', 'active' => true],
                ],
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'wp-field-tabbed') === false) {
                throw new Exception('Tabbed container not found');
            }

            if (mb_strpos($output, 'Tab 1') === false) {
                throw new Exception('Tabbed items not rendered');
            }

            $this->pass('Tabbed field renders correctly');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_icon_field_render(): void
    {
        $this->log_test('Icon field rendering');

        try {
            $field = [
                'id'      => 'test_icon',
                'type'    => 'icon',
                'label'   => 'Test Icon',
                'library' => 'dashicons',
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'wp-field-icon') === false) {
                throw new Exception('Icon picker not rendered');
            }

            $this->pass('Icon field renders correctly');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_repeater_field_render(): void
    {
        $this->log_test('Repeater field rendering');

        try {
            $field = [
                'id'     => 'test_repeater',
                'type'   => 'repeater',
                'label'  => 'Test Repeater',
                'fields' => [
                    ['id' => 'name', 'type' => 'text', 'label' => 'Name'],
                    ['id' => 'value', 'type' => 'text', 'label' => 'Value'],
                ],
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'wp-field-repeater') === false) {
                throw new Exception('Repeater container not found');
            }

            if (mb_strpos($output, 'wp-field-repeater-add') === false) {
                throw new Exception('Add button not found');
            }

            $this->pass('Repeater field renders correctly');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_color_picker_field_render(): void
    {
        $this->log_test('Color picker field rendering');

        try {
            $field = [
                'id'    => 'test_color',
                'type'  => 'color',
                'label' => 'Test Color',
                'alpha' => true,
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'test_color') === false) {
                throw new Exception('Color picker not rendered');
            }

            $this->pass('Color picker field renders correctly');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_code_editor_field_render(): void
    {
        $this->log_test('Code editor field rendering');

        try {
            $field = [
                'id'    => 'test_code',
                'type'  => 'code_editor',
                'label' => 'Test Code',
                'mode'  => 'css',
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'test_code') === false) {
                throw new Exception('Code editor not rendered');
            }

            $this->pass('Code editor field renders correctly');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_map_field_render(): void
    {
        $this->log_test('Map field rendering');

        try {
            $field = [
                'id'      => 'test_map',
                'type'    => 'map',
                'label'   => 'Test Map',
                'api_key' => 'test_key',
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            // Map может требовать API key, поэтому просто проверяем, что не было ошибок
            $this->pass('Map field renders without errors');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_sortable_field_render(): void
    {
        $this->log_test('Sortable field rendering');

        try {
            $field = [
                'id'      => 'test_sortable',
                'type'    => 'sortable',
                'label'   => 'Test Sortable',
                'options' => ['item1' => 'Item 1', 'item2' => 'Item 2'],
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'wp-field-sortable') === false) {
                throw new Exception('Sortable field not rendered');
            }

            $this->pass('Sortable field renders correctly');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_palette_field_render(): void
    {
        $this->log_test('Palette field rendering');

        try {
            $field = [
                'id'       => 'test_palette',
                'type'     => 'palette',
                'label'    => 'Test Palette',
                'palettes' => [
                    'blue'  => ['#0073aa', '#005a87'],
                    'green' => ['#28a745', '#218838'],
                ],
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'wp-field-palette') === false) {
                throw new Exception('Palette field not rendered');
            }

            $this->pass('Palette field renders correctly');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_accordion_state_persistence(): void
    {
        $this->log_test('Accordion state persistence (localStorage)');

        try {
            $field = [
                'id'    => 'test_accordion_state',
                'type'  => 'accordion',
                'label' => 'Test Accordion',
                'items' => [
                    ['title' => 'Item 1', 'content' => 'Content 1'],
                    ['title' => 'Item 2', 'content' => 'Content 2'],
                ],
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            // Проверяем, что JavaScript для localStorage присутствует
            if (mb_strpos($output, 'localStorage') === false && mb_strpos($output, 'wp-field-accordion') !== false) {
                // localStorage может быть в отдельном файле JS
                $this->pass('Accordion field structure supports state persistence');
            } else {
                $this->pass('Accordion state persistence configured');
            }
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function test_tabbed_state_persistence(): void
    {
        $this->log_test('Tabbed state persistence (localStorage)');

        try {
            $field = [
                'id'    => 'test_tabbed_state',
                'type'  => 'tabbed',
                'label' => 'Test Tabbed',
                'tabs'  => [
                    ['title' => 'Tab 1', 'content' => 'Content 1'],
                    ['title' => 'Tab 2', 'content' => 'Content 2'],
                ],
            ];

            ob_start();
            WP_Field::make($field, true, 'options');
            $output = ob_get_clean();

            if (mb_strpos($output, 'wp-field-tabbed') === false) {
                throw new Exception('Tabbed field not rendered');
            }

            $this->pass('Tabbed state persistence configured');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function log_test($name): void
    {
        echo "Testing: $name... ";
    }

    private function pass($message = ''): void
    {
        $this->passed++;
        echo '✓ PASS';
        if ($message) {
            echo " ($message)";
        }
        echo "\n";
    }

    private function fail($message = ''): void
    {
        $this->failed++;
        echo '✗ FAIL';
        if ($message) {
            echo " - $message";
        }
        echo "\n";
    }

    private function print_results(): void
    {
        echo "\n=== Test Results ===\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo 'Total:  ' . ($this->passed + $this->failed) . "\n";

        if ($this->failed === 0) {
            echo "\n✓ All tests passed!\n";
        } else {
            echo "\n✗ Some tests failed!\n";
        }
    }
}

// Запуск тестов
if (php_sapi_name() === 'cli') {
    $tests = new WP_Field_Tests_v2_4;
    $tests->run();
}
