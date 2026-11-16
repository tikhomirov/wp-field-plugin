<?php

declare(strict_types=1);

use WpField\Container\AbstractContainer;
use WpField\Field\AbstractField;
use WpField\Storage\PostMetaStorage;
use WpField\Storage\StorageInterface;

class TestContainer extends AbstractContainer
{
    public function register(): void
    {
        // Test implementation
    }

    public function render(): void
    {
        // Test implementation
    }

    public function save(int|string $id): void
    {
        // Test implementation
    }

    public function publicLoadFieldValues(int|string $objectId): void
    {
        $this->loadFieldValues($objectId);
    }

    public function publicSaveFieldValues(int|string $objectId): void
    {
        $this->saveFieldValues($objectId);
    }
}

class TestField extends AbstractField
{
    public function __construct(string $name = 'test_field')
    {
        parent::__construct($name, 'text');
    }

    public function render(): string
    {
        return '<input type="text" name="'.$this->name.'">';
    }

    public function sanitize(mixed $value): mixed
    {
        return is_string($value) ? sanitize_text_field($value) : $value;
    }

    public function validate(mixed $value): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}

beforeEach(function (): void {
    $this->storage = new PostMetaStorage;
    $this->container = new TestContainer('test_container', $this->storage, ['key' => 'value']);
});

it('can construct with id, storage and config', function (): void {
    expect($this->container->getId())->toBe('test_container')
        ->and($this->container->getConfig())->toBe(['key' => 'value'])
        ->and($this->container->getConfig('key'))->toBe('value')
        ->and($this->container->getConfig('nonexistent', 'default'))->toBe('default');
});

it('can add field', function (): void {
    $field = new TestField;
    $result = $this->container->addField($field);

    expect($result)->toBe($this->container)
        ->and($this->container->getFields())->toHaveCount(1)
        ->and($this->container->getField('test_field'))->toBe($field);
});

it('can get all fields', function (): void {
    $field1 = new TestField('field1');
    $field2 = new TestField('field2');

    $this->container->addField($field1)->addField($field2);

    $fields = $this->container->getFields();

    expect($fields)->toHaveCount(2)
        ->and($fields['field1'])->toBe($field1)
        ->and($fields['field2'])->toBe($field2);
});

it('can get field by name', function (): void {
    $field = new TestField;
    $this->container->addField($field);

    expect($this->container->getField('test_field'))->toBe($field)
        ->and($this->container->getField('nonexistent'))->toBeNull();
});

it('loads field values from storage', function (): void {
    $storage = new class implements StorageInterface
    {
        public function get(string $key, int|string $objectId): mixed
        {
            return $key === 'test_field' ? 'stored_value' : false;
        }

        public function set(string $key, mixed $value, int|string $objectId): bool
        {
            return true;
        }

        public function exists(string $key, int|string $objectId): bool
        {
            return $key === 'test_field';
        }

        public function delete(string $key, int|string $objectId): bool
        {
            return true;
        }
    };

    $container = new TestContainer('test_container', $storage);
    $field = new TestField('test_field');
    $container->addField($field);

    $container->publicLoadFieldValues(123);

    expect($field->getValue())->toBe('stored_value');
});

it('does not load field value when storage returns false', function (): void {
    $storage = new class implements StorageInterface
    {
        public function get(string $key, int|string $objectId): mixed
        {
            return false;
        }

        public function set(string $key, mixed $value, int|string $objectId): bool
        {
            return true;
        }

        public function exists(string $key, int|string $objectId): bool
        {
            return false;
        }

        public function delete(string $key, int|string $objectId): bool
        {
            return true;
        }
    };

    $container = new TestContainer('test_container', $storage);
    $field = new TestField('test_field');
    $container->addField($field);

    $container->publicLoadFieldValues(123);

    expect($field->getValue())->toBeNull();
});

it('saves field values to storage', function (): void {
    $saved = false;
    $savedKey = null;
    $savedValue = null;
    $savedObjectId = null;

    $storage = new class($saved, $savedKey, $savedValue, $savedObjectId) implements StorageInterface
    {
        public function __construct(
            public mixed &$saved,
            public mixed &$savedKey,
            public mixed &$savedValue,
            public mixed &$savedObjectId,
        ) {}

        public function get(string $key, int|string $objectId): mixed
        {
            return false;
        }

        public function set(string $key, mixed $value, int|string $objectId): bool
        {
            $this->saved = true;
            $this->savedKey = $key;
            $this->savedValue = $value;
            $this->savedObjectId = $objectId;

            return true;
        }

        public function exists(string $key, int|string $objectId): bool
        {
            return false;
        }

        public function delete(string $key, int|string $objectId): bool
        {
            return true;
        }
    };

    $container = new TestContainer('test_container', $storage);
    $field = new TestField('test_field');
    $container->addField($field);

    $_POST['test_field'] = 'test_value';

    $container->publicSaveFieldValues(123);

    expect($saved)->toBeTrue()
        ->and($savedKey)->toBe('test_field')
        ->and($savedValue)->toBe('test_value')
        ->and($savedObjectId)->toBe(123);
});

it('does not save field when value is null in POST', function (): void {
    $saved = false;

    $storage = new class($saved) implements StorageInterface
    {
        public function __construct(public mixed &$saved) {}

        public function get(string $key, int|string $objectId): mixed
        {
            return false;
        }

        public function set(string $key, mixed $value, int|string $objectId): bool
        {
            $this->saved = true;

            return true;
        }

        public function exists(string $key, int|string $objectId): bool
        {
            return false;
        }

        public function delete(string $key, int|string $objectId): bool
        {
            return true;
        }
    };

    $container = new TestContainer('test_container', $storage);
    $field = new TestField('test_field');
    $container->addField($field);

    $_POST = [];

    $container->publicSaveFieldValues(123);

    expect($saved)->toBeFalse();
});

it('does not save field when validation fails', function (): void {
    $saved = false;

    $storage = new class($saved) implements StorageInterface
    {
        public function __construct(public mixed &$saved) {}

        public function get(string $key, int|string $objectId): mixed
        {
            return false;
        }

        public function set(string $key, mixed $value, int|string $objectId): bool
        {
            $this->saved = true;

            return true;
        }

        public function exists(string $key, int|string $objectId): bool
        {
            return false;
        }

        public function delete(string $key, int|string $objectId): bool
        {
            return true;
        }
    };

    $container = new TestContainer('test_container', $storage);
    $field = new class extends TestField
    {
        public function __construct()
        {
            parent::__construct('test_field');
        }

        public function validate(mixed $value): bool
        {
            return false;
        }
    };
    $container->addField($field);

    $_POST['test_field'] = 'test_value';

    $container->publicSaveFieldValues(123);

    expect($saved)->toBeFalse();
});

it('sanitizes field value before saving', function (): void {
    $savedValue = null;

    $storage = new class($savedValue) implements StorageInterface
    {
        public function __construct(public mixed &$savedValue) {}

        public function get(string $key, int|string $objectId): mixed
        {
            return false;
        }

        public function set(string $key, mixed $value, int|string $objectId): bool
        {
            $this->savedValue = $value;

            return true;
        }

        public function exists(string $key, int|string $objectId): bool
        {
            return false;
        }

        public function delete(string $key, int|string $objectId): bool
        {
            return true;
        }
    };

    $container = new TestContainer('test_container', $storage);
    $field = new TestField('test_field');
    $container->addField($field);

    $_POST['test_field'] = '  <script>alert("xss")</script>  ';

    $container->publicSaveFieldValues(123);

    expect($savedValue)->toBe('  alert("xss")  ');
});
