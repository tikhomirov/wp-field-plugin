<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ChoiceFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 2).'/WP_Field.php';
    }

    #[Test]
    public function it_renders_select_with_options(): void
    {
        $html = \WP_Field::make([
            'id' => 'country',
            'type' => 'select',
            'label' => 'Country',
            'options' => [
                'ru' => 'Russia',
                'us' => 'USA',
                'uk' => 'UK',
            ],
        ], false);

        $this->assertStringContainsString('Russia', $html);
        $this->assertStringContainsString('USA', $html);
        $this->assertStringContainsString('UK', $html);
    }

    #[Test]
    public function it_renders_multiselect(): void
    {
        $html = \WP_Field::make([
            'id' => 'features',
            'type' => 'multiselect',
            'label' => 'Features',
            'options' => ['a' => 'Feature A', 'b' => 'Feature B'],
        ], false);

        $this->assertStringContainsString('multiple="multiple"', $html);
        $this->assertStringContainsString('Feature A', $html);
        $this->assertStringContainsString('Feature B', $html);
    }

    #[Test]
    public function it_renders_radio_with_options(): void
    {
        $html = \WP_Field::make([
            'id' => 'delivery',
            'type' => 'radio',
            'label' => 'Delivery Type',
            'options' => [
                'courier' => 'Courier',
                'pickup' => 'Pickup',
            ],
        ], false);

        $this->assertStringContainsString('type="radio"', $html);
        $this->assertStringContainsString('Courier', $html);
        $this->assertStringContainsString('Pickup', $html);
    }

    #[Test]
    public function it_renders_checkbox_group(): void
    {
        $html = \WP_Field::make([
            'id' => 'notifications',
            'type' => 'checkbox_group',
            'label' => 'Notifications',
            'options' => [
                'sms' => 'SMS',
                'email' => 'Email',
                'push' => 'Push',
            ],
        ], false);

        $this->assertStringContainsString('wp-field-checkbox-group', $html);
        $this->assertStringContainsString('SMS', $html);
        $this->assertStringContainsString('Email', $html);
        $this->assertStringContainsString('Push', $html);
    }

    #[Test]
    public function it_renders_select_with_selected_value(): void
    {
        $html = \WP_Field::make([
            'id' => 'status',
            'type' => 'select',
            'label' => 'Status',
            'value' => 'active',
            'options' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
        ], false);

        $this->assertStringContainsString('selected', $html);
    }

    #[Test]
    public function it_renders_radio_with_checked_value(): void
    {
        $html = \WP_Field::make([
            'id' => 'choice',
            'type' => 'radio',
            'label' => 'Choice',
            'value' => 'yes',
            'options' => [
                'yes' => 'Yes',
                'no' => 'No',
            ],
        ], false);

        $this->assertStringContainsString('checked', $html);
    }

    #[Test]
    public function it_renders_checkbox_group_with_multiple_values(): void
    {
        $html = \WP_Field::make([
            'id' => 'tags',
            'type' => 'checkbox_group',
            'label' => 'Tags',
            'value' => ['tag1', 'tag2'],
            'options' => [
                'tag1' => 'Tag 1',
                'tag2' => 'Tag 2',
                'tag3' => 'Tag 3',
            ],
        ], false);

        $this->assertStringContainsString('checkbox_group', $html);
        $this->assertStringContainsString('Tag 1', $html);
        $this->assertStringContainsString('Tag 2', $html);
        $this->assertStringContainsString('Tag 3', $html);
    }

    #[Test]
    public function it_supports_parse_options(): void
    {
        $html = \WP_Field::make([
            'id' => 'parsed',
            'type' => 'select',
            'label' => 'Parsed Options',
            'parse_options' => true,
            'options' => [
                'Option 1:opt1',
                'Option 2:opt2',
            ],
        ], false);

        $this->assertStringContainsString('Option 1', $html);
        $this->assertStringContainsString('Option 2', $html);
    }

    #[Test]
    public function it_renders_radio_group_with_labels(): void
    {
        $html = \WP_Field::make([
            'id' => 'type',
            'type' => 'radio',
            'label' => 'Type',
            'options' => [
                'type_a' => 'Type A',
                'type_b' => 'Type B',
                'type_c' => 'Type C',
            ],
        ], false);

        $this->assertStringContainsString('wp-field-radio-group', $html);
        $this->assertStringContainsString('Type A', $html);
        $this->assertStringContainsString('Type B', $html);
        $this->assertStringContainsString('Type C', $html);
    }
}
