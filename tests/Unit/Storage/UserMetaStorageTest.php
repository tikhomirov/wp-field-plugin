<?php

declare(strict_types=1);

use WpField\Storage\UserMetaStorage;

beforeEach(function (): void {
    global $wp_test_meta_storage;
    $this->userId = random_int(10000, 99999);
    $wp_test_meta_storage['user'][$this->userId] = [];
    $this->storage = new UserMetaStorage;
});

afterEach(function (): void {
    global $wp_test_meta_storage;
    unset($wp_test_meta_storage['user'][$this->userId]);
});

it('UserMetaStorage can set and get values', function (): void {
    $key = 'test_meta_key';
    $value = 'test_value';

    $this->storage->set($key, $value, $this->userId);
    $retrieved = $this->storage->get($key, $this->userId);

    expect($retrieved)->toBe($value);
});

it('UserMetaStorage can check if key exists', function (): void {
    $key = 'existing_key';
    $value = 'some_value';

    $this->storage->set($key, $value, $this->userId);

    expect($this->storage->exists($key, $this->userId))->toBeTrue()
        ->and($this->storage->exists('non_existing_key', $this->userId))->toBeFalse();
});

it('UserMetaStorage can delete values', function (): void {
    $key = 'delete_test_key';
    $value = 'delete_test_value';

    $this->storage->set($key, $value, $this->userId);
    expect($this->storage->exists($key, $this->userId))->toBeTrue();

    $this->storage->delete($key, $this->userId);
    expect($this->storage->exists($key, $this->userId))->toBeFalse();
});

it('UserMetaStorage handles arrays correctly', function (): void {
    $key = 'array_key';
    $value = ['item1', 'item2', 'item3'];

    $this->storage->set($key, $value, $this->userId);
    $retrieved = $this->storage->get($key, $this->userId);

    expect($retrieved)->toBe($value);
});
