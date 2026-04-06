<?php

declare(strict_types=1);

namespace WpField\Field;

interface FieldInterface
{
    public function getName(): string;

    public function getType(): string;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    public function render(): string;

    public function sanitize(mixed $value): mixed;

    public function validate(mixed $value): bool;

    public function getAttribute(string $key, mixed $default = null): mixed;

    public function value(mixed $value): static;

    public function getValue(): mixed;

    // HasAttributes trait methods
    public function label(string $label): static;

    public function placeholder(string $placeholder): static;

    public function description(string $description): static;

    public function default(mixed $default): static;

    public function class(string $class): static;

    public function id(string $id): static;

    public function disabled(bool $disabled = true): static;

    public function readonly(bool $readonly = true): static;

    public function setAttribute(string $key, mixed $value): static;

    public function attribute(string $key, mixed $value): static;

    // HasValidation trait methods
    public function required(bool $required = true): static;

    public function min(int|float $min): static;

    public function max(int|float $max): static;

    public function pattern(string $pattern): static;

    public function email(): static;

    public function url(): static;

    // HasConditionals trait methods
    public function when(string $field, string $operator, mixed $value): static;

    public function orWhen(string $field, string $operator, mixed $value): static;
}
