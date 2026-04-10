<?php

declare(strict_types=1);

use WpField\Storage\CustomTableStorage;

beforeEach(function (): void {
    global $wpdb;
    $wpdb = new class
    {
        public string $prefix = 'wp_';

        public ?string $last_query = null;

        public ?array $last_prepare_args = null;

        public bool $update_return = true;

        public bool $insert_return = true;

        public bool $delete_return = true;

        public mixed $get_var_result = '0';

        public function prepare(string $query, ...$args): string
        {
            $this->last_prepare_args = $args;

            return $query;
        }

        public function get_var(string $query): mixed
        {
            return $this->get_var_result;
        }

        public function update(string $table, array $data, array $where, ?array $format = null, ?array $where_format = null): int|false
        {
            $this->last_query = $table;

            return $this->update_return ? 1 : false;
        }

        public function insert(string $table, array $data, ?array $format = null): int|false
        {
            $this->last_query = $table;

            return $this->insert_return ? 1 : false;
        }

        public function delete(string $table, array $where, ?array $where_format = null): int|false
        {
            $this->last_query = $table;

            return $this->delete_return ? 1 : false;
        }
    };

    if (! function_exists('maybe_serialize')) {
        function maybe_serialize($data)
        {
            return is_array($data) || is_object($data) ? serialize($data) : $data;
        }
    }

    if (! function_exists('maybe_unserialize')) {
        function maybe_unserialize($data)
        {
            if (is_serialized($data)) {
                return @unserialize($data);
            }

            return $data;
        }
    }

    if (! function_exists('is_serialized')) {
        function is_serialized($data, $strict = true)
        {
            if (! is_string($data)) {
                return false;
            }
            $data = trim($data);
            if ($data === 'N;') {
                return true;
            }
            if (strlen($data) < 4) {
                return false;
            }
            if ($data[1] !== ':') {
                return false;
            }
            if ($strict) {
                $lastc = substr($data, -1);
                if ($lastc !== ';' && $lastc !== '}') {
                    return false;
                }
            } else {
                $semicolon = strpos($data, ';');
                $brace = strpos($data, '}');
                if ($semicolon === false && $brace === false) {
                    return false;
                }
                if ($semicolon !== false && $semicolon < 3) {
                    return false;
                }
                if ($brace !== false && $brace < 4) {
                    return false;
                }
            }
            $token = $data[0];
            switch ($token) {
                case 's':
                    if ($strict) {
                        if (substr($data, -2, 1) !== '"') {
                            return false;
                        }
                    } elseif (strpos($data, '"') === false) {
                        return false;
                    }
                case 'a':
                case 'O':
                    return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
                case 'b':
                case 'i':
                case 'd':
                    $end = $strict ? '$' : '';

                    return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
            }

            return false;
        }
    }
});

it('constructs with table name', function (): void {
    $storage = new CustomTableStorage('custom_meta');

    expect($storage)->toBeInstanceOf(CustomTableStorage::class);
});

it('gets value from database', function (): void {
    global $wpdb;
    $wpdb->get_var_result = 'serialized_value';

    $storage = new CustomTableStorage('custom_meta');
    $result = $storage->get('test_key', 123);

    expect($result)->toBe('serialized_value');
});

it('returns false when value not found', function (): void {
    global $wpdb;
    $wpdb->get_var_result = null;

    $storage = new CustomTableStorage('custom_meta');
    $result = $storage->get('test_key', 123);

    expect($result)->toBeFalse();
});

it('checks if key exists', function (): void {
    global $wpdb;
    $wpdb->get_var_result = '1';

    $storage = new CustomTableStorage('custom_meta');
    $exists = $storage->exists('test_key', 123);

    expect($exists)->toBeTrue();
});

it('returns false when key does not exist', function (): void {
    global $wpdb;
    $wpdb->get_var_result = '0';

    $storage = new CustomTableStorage('custom_meta');
    $exists = $storage->exists('test_key', 123);

    expect($exists)->toBeFalse();
});

it('updates existing record', function (): void {
    global $wpdb;
    $wpdb->get_var_result = '1';

    $storage = new CustomTableStorage('custom_meta');
    $result = $storage->set('test_key', 'test_value', 123);

    expect($result)->toBeTrue();
});

it('inserts new record when not exists', function (): void {
    global $wpdb;
    $wpdb->get_var_result = '0';

    $storage = new CustomTableStorage('custom_meta');
    $result = $storage->set('test_key', 'test_value', 123);

    expect($result)->toBeTrue();
});

it('deletes record', function (): void {
    global $wpdb;

    $storage = new CustomTableStorage('custom_meta');
    $result = $storage->delete('test_key', 123);

    expect($result)->toBeTrue();
});

it('returns false on update failure', function (): void {
    global $wpdb;
    $wpdb->get_var_result = '1';
    $wpdb->update_return = false;

    $storage = new CustomTableStorage('custom_meta');
    $result = $storage->set('test_key', 'test_value', 123);

    expect($result)->toBeFalse();
});

it('returns false on insert failure', function (): void {
    global $wpdb;
    $wpdb->get_var_result = '0';
    $wpdb->insert_return = false;

    $storage = new CustomTableStorage('custom_meta');
    $result = $storage->set('test_key', 'test_value', 123);

    expect($result)->toBeFalse();
});

it('returns false on delete failure', function (): void {
    global $wpdb;
    $wpdb->delete_return = false;

    $storage = new CustomTableStorage('custom_meta');
    $result = $storage->delete('test_key', 123);

    expect($result)->toBeFalse();
});
