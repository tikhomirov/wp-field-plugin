<?php

declare(strict_types=1);

namespace WpField\Field\Types;

use WpField\Field\AbstractField;
use WpField\Field\FieldInterface;

class GroupField extends AbstractField
{
    /**
     * @var array<int, FieldInterface>
     */
    protected array $fields = [];

    public function __construct(string $name)
    {
        parent::__construct($name, 'group');
    }

    /**
     * @param  array<int, FieldInterface>  $fields
     */
    public function fields(array $fields): static
    {
        $this->fields = [];

        foreach ($fields as $field) {
            if ($field instanceof FieldInterface) {
                $this->fields[] = $field;
            }
        }

        return $this;
    }

    public function addField(FieldInterface $field): static
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return array<int, FieldInterface>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['fields'] = array_map(static fn (FieldInterface $field): array => $field->toArray(), $this->fields);

        return $array;
    }

    public function sanitize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return [];
        }

        $sanitized = [];

        foreach ($this->fields as $field) {
            $fieldName = $field->getName();

            if (array_key_exists($fieldName, $value)) {
                $sanitized[$fieldName] = $field->sanitize($value[$fieldName]);
            }
        }

        return $sanitized;
    }

    public function validate(mixed $value): bool
    {
        if (! is_array($value)) {
            return ! $this->isRequired();
        }

        foreach ($this->fields as $field) {
            $fieldName = $field->getName();
            $fieldValue = $value[$fieldName] ?? null;

            if (! $field->validate($fieldValue)) {
                return false;
            }
        }

        return true;
    }

    public function render(): string
    {
        $wrapperClass = 'wp-field-group';
        $customClass = $this->getAttribute('class', '');
        if (is_string($customClass) && $customClass !== '') {
            $wrapperClass .= ' '.trim($customClass);
        }

        $name = esc_attr($this->name);
        $value = $this->getValue();
        $values = is_array($value) ? $value : [];

        $html = sprintf('<div class="%s" data-name="%s">', esc_attr($wrapperClass), $name);

        $rawLabel = $this->getAttribute('label');
        if ($rawLabel !== null && is_string($rawLabel) && $rawLabel !== '') {
            $html .= sprintf('<h3 class="wp-field-group-label">%s</h3>', esc_html($rawLabel));
        }

        $html .= '<div class="wp-field-group-fields">';

        foreach ($this->fields as $field) {
            $fieldName = $field->getName();
            $fullName = sprintf('%s[%s]', $this->name, $fieldName);
            $clonedField = $this->cloneSubFieldWithName($field, $fullName);
            $clonedField->value($values[$fieldName] ?? null);

            $html .= '<div class="wp-field-group-field">';
            $html .= $clonedField->render();
            $html .= '</div>';
        }

        $html .= '</div></div>';

        $rawDescription = $this->getAttribute('description');
        if ($rawDescription !== null && is_string($rawDescription)) {
            $html .= sprintf('<p class="description">%s</p>', esc_html($rawDescription));
        }

        return $html;
    }

    private function cloneSubFieldWithName(FieldInterface $field, string $newName): FieldInterface
    {
        if (method_exists($field, 'cloneWithName')) {
            /** @var FieldInterface $renamed */
            $renamed = $field->cloneWithName($newName);

            return $renamed;
        }

        return clone $field;
    }
}
