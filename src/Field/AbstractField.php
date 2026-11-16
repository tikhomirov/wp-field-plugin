<?php

declare(strict_types=1);

namespace WpField\Field;

use WpField\Traits\HasAttributes;
use WpField\Traits\HasConditionals;
use WpField\Traits\HasValidation;

abstract class AbstractField implements FieldInterface
{
    use HasAttributes;
    use HasConditionals;
    use HasValidation;

    protected string $name;

    protected string $type;

    protected mixed $value = null;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function value(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value ?? $this->getAttribute('default');
    }

    protected function stringify(mixed $value, string $default = ''): string
    {
        return is_scalar($value) ? (string) $value : $default;
    }

    protected function attributeString(string $key, string $default = ''): string
    {
        return $this->stringify($this->getAttribute($key, $default), $default);
    }

    public function withName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function cloneWithName(string $name): static
    {
        $clone = clone $this;
        $clone->withName($name);

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge([
            'id' => $this->name,
            'name' => $this->name,
            'type' => $this->type,
            'required' => $this->isRequired(),
            'value' => $this->getValue(),
        ], $this->attributes, [
            'validation' => $this->validationRules,
            'conditions' => $this->conditions,
        ]);
    }

    public function validate(mixed $value): bool
    {
        if ($this->isRequired && empty($value)) {
            return false;
        }

        foreach ($this->validationRules as $rule => $ruleValue) {
            if (! $this->validateRule($rule, $value, $ruleValue)) {
                return false;
            }
        }

        return true;
    }

    protected function validateRule(string $rule, mixed $value, mixed $ruleValue): bool
    {
        return match ($rule) {
            'required' => ! empty($value),
            'min' => is_numeric($value) && is_numeric($ruleValue) && $value >= $ruleValue,
            'max' => is_numeric($value) && is_numeric($ruleValue) && $value <= $ruleValue,
            'email' => is_string($value) && is_email($value),
            'url' => is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false,
            'pattern' => is_string($ruleValue) && is_string($value) && preg_match($ruleValue, $value) === 1,
            default => true,
        };
    }

    public function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => $this->sanitize($v), $value);
        }
        if (! is_string($value)) {
            if (is_scalar($value)) {
                $value = (string) $value;
            } else {
                return '';
            }
        }

        return sanitize_text_field($value);
    }

    abstract public function render(): string;
}
