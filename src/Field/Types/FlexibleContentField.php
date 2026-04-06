<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;
use WpField\Field\FieldInterface;

class FlexibleContentField extends AbstractField
{
    /**
     * @var array<string, array{label: string, fields: array<FieldInterface>}>
     */
    protected array $layouts = [];

    protected int $min = 0;

    protected int $max = 0;

    protected string $buttonLabel = 'Add Layout';

    public function __construct(string $name)
    {
        parent::__construct($name, 'flexible_content');
    }

    /**
     * @param  array<FieldInterface>  $fields
     */
    public function addLayout(string $name, string $label, array $fields): static
    {
        $this->layouts[$name] = [
            'label' => $label,
            'fields' => $fields,
        ];

        return $this;
    }

    /**
     * @return array<string, array{label: string, fields: array<FieldInterface>}>
     */
    public function getLayouts(): array
    {
        return $this->layouts;
    }

    public function min(int|float $min): static
    {
        $this->min = (int) $min;

        return $this;
    }

    public function max(int|float $max): static
    {
        $this->max = (int) $max;

        return $this;
    }

    public function buttonLabel(string $label): static
    {
        $this->buttonLabel = $label;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['layouts'] = [];

        foreach ($this->layouts as $layoutName => $layout) {
            $array['layouts'][$layoutName] = [
                'label' => $layout['label'],
                'fields' => array_map(fn (FieldInterface $field) => $field->toArray(), $layout['fields']),
            ];
        }

        $array['min'] = $this->min;
        $array['max'] = $this->max;
        $array['button_label'] = $this->buttonLabel;

        return $array;
    }

    public function sanitize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return [];
        }

        $sanitized = [];
        foreach ($value as $blockIndex => $block) {
            if (! is_array($block) || ! isset($block['acf_fc_layout'])) {
                continue;
            }

            $layoutName = $block['acf_fc_layout'];
            if (! isset($this->layouts[$layoutName])) {
                continue;
            }

            $sanitizedBlock = ['acf_fc_layout' => $layoutName];
            $layout = $this->layouts[$layoutName];

            foreach ($layout['fields'] as $field) {
                $fieldName = $field->getName();
                if (isset($block[$fieldName])) {
                    $sanitizedBlock[$fieldName] = $field->sanitize($block[$fieldName]);
                }
            }

            $sanitized[$blockIndex] = $sanitizedBlock;
        }

        return $sanitized;
    }

    public function validate(mixed $value): bool
    {
        if (! is_array($value)) {
            return ! $this->isRequired();
        }

        $count = count($value);

        if ($this->min > 0 && $count < $this->min) {
            return false;
        }

        if ($this->max > 0 && $count > $this->max) {
            return false;
        }

        foreach ($value as $block) {
            if (! is_array($block) || ! isset($block['acf_fc_layout'])) {
                return false;
            }

            $layoutName = $block['acf_fc_layout'];
            if (! isset($this->layouts[$layoutName])) {
                return false;
            }

            $layout = $this->layouts[$layoutName];
            foreach ($layout['fields'] as $field) {
                $fieldName = $field->getName();
                $fieldValue = $block[$fieldName] ?? null;

                if (! $field->validate($fieldValue)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function render(): string
    {
        $name = esc_attr($this->name);
        $rawId = $this->getAttribute('id', $this->name);
        $id = is_string($rawId) ? esc_attr($rawId) : esc_attr($this->name);

        $rawValue = $this->getValue();
        $blocks = is_array($rawValue) ? $rawValue : [];

        $html = sprintf('<div class="wp-field-flexible" data-name="%s">', $name);

        $rawLabel = $this->getAttribute('label');
        if ($rawLabel !== null && is_string($rawLabel)) {
            $html .= sprintf('<h3>%s</h3>', esc_html($rawLabel));
        }

        $rawDescription = $this->getAttribute('description');
        if ($rawDescription !== null && is_string($rawDescription)) {
            $html .= sprintf('<p class="description">%s</p>', esc_html($rawDescription));
        }

        $html .= '<div class="wp-field-flexible-blocks">';

        foreach ($blocks as $index => $block) {
            if (is_array($block) && isset($block['acf_fc_layout'])) {
                $layoutName = $block['acf_fc_layout'];
                if (isset($this->layouts[$layoutName])) {
                    $html .= $this->renderBlock($index, $layoutName, $block);
                }
            }
        }

        $html .= '</div>';

        $html .= '<div class="wp-field-flexible-add-block">';
        $html .= sprintf('<button type="button" class="button">%s</button>', esc_html($this->buttonLabel));
        $html .= '<div class="wp-field-flexible-layouts" style="display:none;">';

        foreach ($this->layouts as $layoutName => $layout) {
            $html .= sprintf(
                '<button type="button" class="button" data-layout="%s">%s</button>',
                esc_attr($layoutName),
                esc_html($layout['label']),
            );
        }

        $html .= '</div></div>';

        foreach (array_keys($this->layouts) as $layoutName) {
            $html .= sprintf('<script type="text/template" class="wp-field-flexible-template" data-layout="%s">', esc_attr($layoutName));
            $html .= $this->renderBlock('{{INDEX}}', $layoutName, []);
            $html .= '</script>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected function renderBlock(int|string $index, string $layoutName, array $blockData): string
    {
        $name = esc_attr($this->name);
        $layout = $this->layouts[$layoutName];

        $html = sprintf(
            '<div class="wp-field-flexible-block" data-index="%s" data-layout="%s">',
            esc_attr((string) $index),
            esc_attr($layoutName),
        );

        $html .= '<div class="wp-field-flexible-block-header">';
        $html .= sprintf('<h4>%s</h4>', esc_html($layout['label']));
        $html .= '<div class="wp-field-flexible-block-controls">';
        $html .= '<button type="button" class="button wp-field-flexible-collapse">−</button>';
        $removeLabel = function_exists('esc_html__') ? esc_html__('Remove', 'wp-field') : 'Remove';

        $html .= sprintf(
            '<button type="button" class="button wp-field-flexible-remove" data-min="%d">%s</button>',
            $this->min,
            esc_html($removeLabel),
        );
        $html .= '</div></div>';

        $html .= '<div class="wp-field-flexible-block-content">';

        $html .= sprintf(
            '<input type="hidden" name="%s[%s][acf_fc_layout]" value="%s" />',
            $name,
            $index,
            esc_attr($layoutName),
        );

        foreach ($layout['fields'] as $field) {
            $fieldName = $field->getName();
            $fieldValue = $blockData[$fieldName] ?? null;

            $fullName = sprintf('%s[%s][%s]', $name, $index, $fieldName);
            $clonedField = $this->cloneSubFieldWithName($field, $fullName);
            $clonedField->value($fieldValue);

            $html .= '<div class="wp-field-flexible-field">';
            $html .= $clonedField->render();
            $html .= '</div>';
        }

        $html .= '</div></div>';

        return $html;
    }

    protected function cloneSubFieldWithName(FieldInterface $field, string $newName): FieldInterface
    {
        if (method_exists($field, 'cloneWithName')) {
            /** @var FieldInterface $renamed */
            $renamed = $field->cloneWithName($newName);

            return $renamed;
        }

        return clone $field;
    }
}
