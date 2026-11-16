<?php

declare(strict_types=1);

namespace WpField\Traits;

trait HasConditionals
{
    /** @var array<int, array<string, mixed>> */
    protected array $conditions = [];

    public function when(string $field, string $operator, mixed $value): static
    {
        $this->conditions[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'logic' => 'AND',
        ];

        return $this;
    }

    public function orWhen(string $field, string $operator, mixed $value): static
    {
        $this->conditions[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'logic' => 'OR',
        ];

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function hasConditions(): bool
    {
        return ! empty($this->conditions);
    }
}
