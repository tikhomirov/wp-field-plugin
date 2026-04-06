<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use WpField\Field\Field;

class LegacyWrapperConditionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 2).'/WP_Field.php';
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_maps_flat_when_conditions_to_legacy_dependency(): void
    {
        $html = Field::legacy('select', 'legacy_select')
            ->label('Legacy Select')
            ->attribute('options', [
                'yes' => 'Yes',
                'no' => 'No',
            ])
            ->when('delivery_type', '==', 'courier')
            ->render();

        $this->assertStringContainsString('data-dependency', $html);
        $this->assertStringContainsString('delivery_type', $html);
        $this->assertStringContainsString('courier', $html);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_maps_or_conditions_to_legacy_dependency_relation(): void
    {
        $html = Field::legacy('select', 'legacy_select')
            ->label('Legacy Select')
            ->attribute('options', [
                'yes' => 'Yes',
                'no' => 'No',
            ])
            ->when('field_a', '==', '1')
            ->orWhen('field_b', '==', '2')
            ->render();

        $this->assertStringContainsString('data-dependency', $html);
        $this->assertStringContainsString('field_a', $html);
        $this->assertStringContainsString('field_b', $html);
        $this->assertStringContainsString('OR', $html);
    }
}
