<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;
use WpField\Field\Field;
use WpField\Field\FieldInterface;

class FieldsetField extends AbstractField
{
    public function __construct(string $name)
    {
        parent::__construct($name, 'fieldset');
    }

    /**
     * @param  array<int|string, mixed>  $fields
     */
    public function fields(array $fields): static
    {
        return $this->attribute('fields', $fields);
    }

    public function render(): string
    {
        $legend = $this->attributeString('legend', $this->attributeString('label'));
        $class = trim('wp-field-fieldset '.$this->attributeString('class'));

        $html = sprintf('<fieldset class="%s">', esc_attr($class));

        if ($legend !== '') {
            $html .= sprintf('<legend>%s</legend>', esc_html($legend));
        }

        foreach ($this->resolveFields() as $field) {
            $html .= $field->render();
        }

        $html .= '</fieldset>';

        $description = $this->attributeString('description');
        if ($description !== '') {
            $html .= sprintf('<p class="description">%s</p>', esc_html($description));
        }

        return $html;
    }

    /**
     * @return array<int, FieldInterface>
     */
    private function resolveFields(): array
    {
        $configuredFields = $this->getAttribute('fields', []);
        if (! is_array($configuredFields)) {
            return [];
        }

        $result = [];

        foreach ($configuredFields as $configuredField) {
            if ($configuredField instanceof FieldInterface) {
                $result[] = $configuredField;

                continue;
            }

            if (! is_array($configuredField)) {
                continue;
            }

            $type = $configuredField['type'] ?? null;
            $id = $configuredField['id'] ?? null;

            if (! is_string($type) || ! is_string($id) || $type === '' || $id === '') {
                continue;
            }

            $field = Field::make($type, $id);

            if (isset($configuredField['value'])) {
                $field->value($configuredField['value']);
            }

            if (isset($configuredField['required']) && (bool) $configuredField['required']) {
                $field->required();
            }

            if (isset($configuredField['label']) && is_scalar($configuredField['label'])) {
                $field->label((string) $configuredField['label']);
            }

            if (method_exists($field, 'withName') && isset($configuredField['name']) && is_string($configuredField['name'])) {
                $field->withName($configuredField['name']);
            }

            foreach ($configuredField as $key => $value) {
                if (in_array((string) $key, ['id', 'type', 'label', 'value', 'required', 'name'], true)) {
                    continue;
                }

                $field->attribute((string) $key, $value);
            }

            $result[] = $field;
        }

        return $result;
    }
}
