<?php

declare(strict_types=1);

namespace WpField\Container;

use WpField\Field\FieldInterface;
use WpField\Storage\StorageInterface;

abstract class AbstractContainer implements ContainerInterface
{
    /** @var array<string, FieldInterface> */
    protected array $fields = [];

    protected StorageInterface $storage;

    protected string $id;

    /** @var array<string, mixed> */
    protected array $config = [];

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(string $id, StorageInterface $storage, array $config = [])
    {
        $this->id = $id;
        $this->storage = $storage;
        $this->config = $config;
    }

    public function addField(FieldInterface $field): static
    {
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * @return array<string, FieldInterface>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField(string $name): ?FieldInterface
    {
        return $this->fields[$name] ?? null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? $default;
    }

    protected function loadFieldValues(int|string $objectId): void
    {
        foreach ($this->fields as $field) {
            $value = $this->storage->get($field->getName(), $objectId);
            if ($value !== false) {
                $field->value($value);
            }
        }
    }

    protected function saveFieldValues(int|string $objectId): void
    {
        foreach ($this->fields as $field) {
            $value = $_POST[$field->getName()] ?? null;

            if ($value !== null) {
                $sanitized = $field->sanitize($value);

                if ($field->validate($sanitized)) {
                    $this->storage->set($field->getName(), $sanitized, $objectId);
                }
            }
        }
    }

    abstract public function register(): void;

    abstract public function render(): void;

    abstract public function save(int|string $id): void;
}
