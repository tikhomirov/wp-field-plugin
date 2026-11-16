<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;
use WpField\Field\FieldInterface;

class RepeaterField extends AbstractField
{
    /**
     * @var array<FieldInterface>
     */
    protected array $subFields = [];

    protected int $min = 0;

    protected int $max = 0;

    protected string $buttonLabel = 'Add Row';

    protected string $layout = 'table';

    public function __construct(string $name)
    {
        parent::__construct($name, 'repeater');
    }

    /**
     * @param  array<FieldInterface>  $fields
     */
    public function fields(array $fields): static
    {
        $this->subFields = $fields;

        return $this;
    }

    public function addField(FieldInterface $field): static
    {
        $this->subFields[] = $field;

        return $this;
    }

    /**
     * @return array<FieldInterface>
     */
    public function getFields(): array
    {
        return $this->subFields;
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

    public function layout(string $layout): static
    {
        if (in_array($layout, ['table', 'block', 'row'], true)) {
            $this->layout = $layout;
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['fields'] = array_map(fn (FieldInterface $field) => $field->toArray(), $this->subFields);
        $array['min'] = $this->min;
        $array['max'] = $this->max;
        $array['button_label'] = $this->buttonLabel;
        $array['layout'] = $this->layout;

        return $array;
    }

    public function sanitize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return [];
        }

        $sanitized = [];
        foreach ($value as $rowIndex => $row) {
            if (! is_array($row)) {
                continue;
            }

            $sanitizedRow = [];
            foreach ($this->subFields as $field) {
                $fieldName = $field->getName();
                if (isset($row[$fieldName])) {
                    $sanitizedRow[$fieldName] = $field->sanitize($row[$fieldName]);
                }
            }
            $sanitized[$rowIndex] = $sanitizedRow;
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

        foreach ($value as $row) {
            if (! is_array($row)) {
                return false;
            }

            foreach ($this->subFields as $field) {
                $fieldName = $field->getName();
                $fieldValue = $row[$fieldName] ?? null;

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
        $rows = is_array($rawValue) ? $rawValue : [];

        $html = sprintf('<div class="wp-field-repeater" data-name="%s" data-layout="%s">', $name, esc_attr($this->layout));

        $rawLabel = $this->getAttribute('label');
        if ($rawLabel !== null && is_string($rawLabel)) {
            $html .= sprintf('<h3>%s</h3>', esc_html($rawLabel));
        }

        $rawDescription = $this->getAttribute('description');
        if ($rawDescription !== null && is_string($rawDescription)) {
            $html .= sprintf('<p class="description">%s</p>', esc_html($rawDescription));
        }

        $html .= '<div class="wp-field-repeater-rows">';

        if ($rows === [] && $this->min > 0) {
            for ($i = 0; $i < $this->min; $i++) {
                $html .= $this->renderRow($i, []);
            }
        } else {
            foreach ($rows as $index => $row) {
                $html .= $this->renderRow($index, is_array($row) ? $row : []);
            }
        }

        $html .= '</div>';

        $html .= sprintf(
            '<button type="button" class="button wp-field-repeater-add" data-max="%d">%s</button>',
            $this->max,
            esc_html($this->buttonLabel),
        );

        $html .= '<script type="text/template" class="wp-field-repeater-template">';
        $html .= $this->renderRow('{{INDEX}}', []);
        $html .= '</script>';

        $html .= '</div>';

        return $html;
    }

    /**
     * @param  array<string, mixed>  $rowData
     */
    protected function renderRow(int|string $index, array $rowData): string
    {
        $name = esc_attr($this->name);
        $html = sprintf('<div class="wp-field-repeater-row" data-index="%s">', esc_attr((string) $index));

        if ($this->layout === 'table') {
            $html .= '<table class="widefat"><tbody><tr>';
        }

        foreach ($this->subFields as $field) {
            $fieldName = $field->getName();
            $fieldValue = $rowData[$fieldName] ?? null;

            $fullName = sprintf('%s[%s][%s]', $name, $index, $fieldName);
            $clonedField = $this->cloneSubFieldWithName($field, $fullName);
            $clonedField->value($fieldValue);

            if ($this->layout === 'table') {
                $html .= '<td>';
            }

            $html .= $clonedField->render();

            if ($this->layout === 'table') {
                $html .= '</td>';
            }
        }

        if ($this->layout === 'table') {
            $html .= '<td class="wp-field-repeater-actions">';
        } else {
            $html .= '<div class="wp-field-repeater-actions">';
        }

        $removeLabel = function_exists('esc_html__') ? esc_html__('Remove', 'wp-field') : 'Remove';

        $html .= sprintf(
            '<button type="button" class="button wp-field-repeater-remove" data-min="%d">%s</button>',
            $this->min,
            esc_html($removeLabel),
        );

        if ($this->layout === 'table') {
            $html .= '</td></tr></tbody></table>';
        } else {
            $html .= '</div>';
        }

        $html .= '</div>';

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
