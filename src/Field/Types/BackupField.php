<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;

class BackupField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'backup');
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $exportData = $this->getAttribute('export_data', []);
        $exportData = is_array($exportData) ? $exportData : [];

        $html = '';
        $label = $this->attributeString('label');
        if ($label !== '') {
            $id = esc_attr($this->attributeString('id', $this->name));
            $html .= sprintf('<label for="%s">%s</label>', $id, esc_html($label));
        }

        $html .= '<div class="wp-field-backup">';

        $html .= '<div class="wp-field-backup-section">';
        $html .= '<h4>'.esc_html__('Export Settings', 'wp-field').'</h4>';

        if ($exportData !== []) {
            $json = function_exists('wp_json_encode')
                ? wp_json_encode($exportData, JSON_PRETTY_PRINT)
                : json_encode($exportData, JSON_PRETTY_PRINT);
            $jsonString = $json === false ? '' : (string) $json;

            $html .= sprintf(
                '<textarea readonly class="wp-field-backup-export" rows="10" style="width:100%%;">%s</textarea>',
                htmlspecialchars($jsonString, ENT_QUOTES, 'UTF-8'),
            );
            $html .= '<button type="button" class="button wp-field-backup-copy">'.esc_html__('Copy to Clipboard', 'wp-field').'</button>';
            $html .= '<button type="button" class="button wp-field-backup-download">'.esc_html__('Download JSON', 'wp-field').'</button>';
        } else {
            $html .= '<p class="description">'.esc_html__('No data to export', 'wp-field').'</p>';
        }

        $html .= '</div>';

        $html .= '<div class="wp-field-backup-section">';
        $html .= '<h4>'.esc_html__('Import Settings', 'wp-field').'</h4>';
        $html .= sprintf(
            '<textarea name="%s" class="wp-field-backup-import" rows="10" placeholder="%s" style="width:100%%;"></textarea>',
            $name,
            esc_attr(__('Paste JSON data here...', 'wp-field')),
        );
        $html .= '<button type="button" class="button button-primary wp-field-backup-validate">'.esc_html__('Validate JSON', 'wp-field').'</button>';
        $html .= '<div class="wp-field-backup-status"></div>';
        $html .= '</div>';

        $html .= '</div>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }

    public function validate(mixed $value): bool
    {
        if (! is_scalar($value)) {
            return false;
        }

        $json = trim((string) $value);
        if ($json === '') {
            return true;
        }

        json_decode($json, true);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
