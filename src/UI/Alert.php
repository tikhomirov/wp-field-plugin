<?php

declare(strict_types=1);

namespace WpField\UI;

final class Alert
{
    /**
     * @param  array<string, string> $attributes
     */
    public static function render(
        string $tone = 'neutral',
        string $title = '',
        string $content_html = '',
        array $attributes = [],
    ): string {
        $allowed_tones = ['neutral', 'success', 'warning', 'error'];
        $resolved_tone = in_array($tone, $allowed_tones, true) ? $tone : 'neutral';
        $role = $attributes['role'] ?? ($resolved_tone === 'error' ? 'alert' : 'status');
        $class = trim('wp-field-alert wp-field-alert--' . $resolved_tone . ' ' . ($attributes['class'] ?? ''));

        $attribute_string = self::buildAttributes(array_merge($attributes, [
            'class' => $class,
            'role' => $role,
        ]));

        ob_start();
        ?>
        <div <?php echo $attribute_string; ?>>
            <?php if ($title !== '') { ?>
                <div class="wp-field-alert__title"><?php echo esc_html($title); ?></div>
            <?php } ?>
            <?php if ($content_html !== '') { ?>
                <div class="wp-field-alert__body"><?php echo wp_kses_post($content_html); ?></div>
            <?php } ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * @param  array<string, string> $attributes
     */
    private static function buildAttributes(array $attributes): string
    {
        $compiled = [];

        foreach ($attributes as $name => $value) {
            if ($value === '') {
                continue;
            }

            $compiled[] = sprintf('%s="%s"', esc_attr($name), esc_attr($value));
        }

        return implode(' ', $compiled);
    }
}
