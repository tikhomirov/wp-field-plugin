<?php

declare(strict_types=1);

namespace WpField\Storage;

class CustomTableStorage implements StorageInterface
{
    protected string $tableName;

    public function __construct(string $tableName)
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix.$tableName;
    }

    public function get(string $key, int|string $id): mixed
    {
        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM {$this->tableName} WHERE meta_key = %s AND object_id = %d LIMIT 1",
                $key,
                $id,
            ),
        );

        return $result !== null ? maybe_unserialize($result) : false;
    }

    public function set(string $key, mixed $value, int|string $id): bool
    {
        global $wpdb;

        $serializedValue = maybe_serialize($value);

        if ($this->exists($key, $id)) {
            return $wpdb->update(
                $this->tableName,
                ['meta_value' => $serializedValue],
                ['meta_key' => $key, 'object_id' => (int) $id],
                ['%s'],
                ['%s', '%d'],
            ) !== false;
        }

        return $wpdb->insert(
            $this->tableName,
            [
                'object_id' => (int) $id,
                'meta_key' => $key,
                'meta_value' => $serializedValue,
            ],
            ['%d', '%s', '%s'],
        ) !== false;
    }

    public function delete(string $key, int|string $id): bool
    {
        global $wpdb;

        return $wpdb->delete(
            $this->tableName,
            ['meta_key' => $key, 'object_id' => (int) $id],
            ['%s', '%d'],
        ) !== false;
    }

    public function exists(string $key, int|string $id): bool
    {
        global $wpdb;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->tableName} WHERE meta_key = %s AND object_id = %d",
                $key,
                $id,
            ),
        );

        return (int) $count > 0;
    }
}
