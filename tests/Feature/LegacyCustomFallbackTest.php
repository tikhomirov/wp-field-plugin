<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use WpField\Field\Field;

class LegacyCustomFallbackTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_renders_generic_fallback_for_unknown_custom_type(): void
    {
        $html = Field::make('my_custom_type', 'custom_payload')
            ->label('Custom Payload')
            ->placeholder('Enter value')
            ->description('Custom field fallback')
            ->value('abc')
            ->render();

        $this->assertStringContainsString('wp-field-legacy-fallback', $html);
        $this->assertStringContainsString('data-legacy-type="my_custom_type"', $html);
        $this->assertStringContainsString('name="custom_payload"', $html);
        $this->assertStringContainsString('value="abc"', $html);
        $this->assertStringContainsString('Enter value', $html);
        $this->assertStringContainsString('Custom field fallback', $html);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_supports_custom_options_shape_in_fallback_mode(): void
    {
        $html = Field::make('my_custom_select', 'custom_select')
            ->label('Custom Select')
            ->attribute('options', [
                'a' => 'Option A',
                'b' => 'Option B',
            ])
            ->value('b')
            ->render();

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('name="custom_select"', $html);
        $this->assertStringContainsString('value="b" selected', $html);
    }
}
