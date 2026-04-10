<?php

declare(strict_types=1);

namespace {
    use WpField\Field\Field;
    use WpField\Legacy\LegacyAdapter;
    use WpField\Storage\CustomTableStorage;
    use WpField\Storage\OptionStorage;
    use WpField\Storage\TermMetaStorage;
    use WpField\Storage\UserMetaStorage;

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

    it('covers repeater hot paths', function (): void {
        $field = Field::repeater('items')->fields([Field::text('title')->value('Hello')]);
        expect($field->sanitize([['title' => '<b>One</b>']]))->toBe([['title' => 'One']]);
    });

    it('covers flexible content hot paths', function (): void {
        $field = Field::flexibleContent('content_blocks')->addLayout('hero', 'Hero', [Field::text('heading')->value('Welcome')]);
        expect($field->sanitize([['acf_fc_layout' => 'hero', 'heading' => '<b>Welcome</b>']]))->toBe([['acf_fc_layout' => 'hero', 'heading' => 'Welcome']]);
    });

    it('covers group hot paths', function (): void {
        $field = Field::group('settings')->fields([Field::text('title')->value('Hello'), Field::text('subtitle')->value('World')]);
        expect($field->sanitize(['title' => '<b>Hello</b>', 'subtitle' => 'World']))->toBe(['title' => 'Hello', 'subtitle' => 'World']);
    });

    it('covers legacy adapter mapping', function (): void {
        $field = LegacyAdapter::make(['id' => 'legacy_name', 'type' => 'text', 'label' => 'Label', 'placeholder' => 'Enter value', 'desc' => 'Description', 'class' => 'custom', 'required' => true, 'attributes' => ['data-flag' => 'yes']]);
        expect($field->getName())->toBe('legacy_name');
        expect($field->getAttribute('placeholder'))->toBe('Enter value');
        expect($field->getAttribute('description'))->toBe('Description');
        expect($field->getAttribute('class'))->toBe('custom');
        expect($field->getAttribute('data-flag'))->toBe('yes');
        expect($field->isRequired())->toBeTrue();
        expect(fn () => LegacyAdapter::make(['id' => '0']))->toThrow(InvalidArgumentException::class);
    });

    it('covers storage hot paths', function (): void {
        global $wpdb;
        $wpdb = new class
        {
            public string $prefix = 'wp_';

            public array $rows = [];

            public function prepare(string $query, mixed ...$args): string
            {
                return vsprintf($query, array_map(static fn ($arg) => is_int($arg) ? $arg : "'$arg'", $args));
            }

            public function get_var(string $query): mixed
            {
                return str_contains($query, 'COUNT(*)') ? (string) count($this->rows) : ($this->rows[0]['meta_value'] ?? null);
            }

            public function update(string $table, array $data, array $where): int|false
            {
                $this->rows[0]['meta_value'] = $data['meta_value'];

                return 1;
            }

            public function insert(string $table, array $data): int|false
            {
                $this->rows[] = $data;

                return 1;
            }

            public function delete(string $table, array $where): int|false
            {
                $this->rows = [];

                return 1;
            }
        };

        $optionStorage = new OptionStorage;
        expect($optionStorage->exists('site_name', 0))->toBeFalse();
        expect($optionStorage->set('site_name', 'WP', 0))->toBeTrue();
        expect($optionStorage->get('site_name', 0))->toBe('WP');
        expect($optionStorage->delete('site_name', 0))->toBeTrue();

        global $wp_test_meta_storage;
        $wp_test_meta_storage['term'] = [];
        $termStorage = new TermMetaStorage;
        expect($termStorage->set('term_key', 'term', 12))->toBeTrue();
        expect($termStorage->get('term_key', 12))->toBe('term');
        expect($termStorage->exists('term_key', 12))->toBeTrue();

        $wp_test_meta_storage['user'] = [];
        $userStorage = new UserMetaStorage;
        expect($userStorage->set('user_key', 'user', 34))->toBeTrue();
        expect($userStorage->get('user_key', 34))->toBe('user');
        expect($userStorage->exists('user_key', 34))->toBeTrue();

        $storage = new CustomTableStorage('custom_meta');
        expect($storage->set('table_key', ['a' => 1], 7))->toBeTrue();
        expect($storage->get('table_key', 7))->toBe(['a' => 1]);
    });
}
