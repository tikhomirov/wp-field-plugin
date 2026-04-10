<?php

declare(strict_types=1);

use WpField\Storage\TermMetaStorage;

beforeEach(function (): void {
    global $wp_test_meta_storage;
    $this->termId = random_int(10000, 99999);
    $wp_test_meta_storage['term'][$this->termId] = [];
    $this->storage = new TermMetaStorage;
});

afterEach(function (): void {
    global $wp_test_meta_storage;
    unset($wp_test_meta_storage['term'][$this->termId]);
});

it('TermMetaStorage can set and get values', function (): void {
    $key = 'test_meta_key';
    $value = 'test_value';

    $this->storage->set($key, $value, $this->termId);
    $retrieved = $this->storage->get($key, $this->termId);

    expect($retrieved)->toBe($value);
});

it('TermMetaStorage can check if key exists', function (): void {
    $key = 'existing_key';
    $value = 'some_value';

    $this->storage->set($key, $value, $this->termId);

    expect($this->storage->exists($key, $this->termId))->toBeTrue()
        ->and($this->storage->exists('non_existing_key', $this->termId))->toBeFalse();
});

it('TermMetaStorage can delete values', function (): void {
    $key = 'delete_test_key';
    $value = 'delete_test_value';

    $this->storage->set($key, $value, $this->termId);
    expect($this->storage->exists($key, $this->termId))->toBeTrue();

    $this->storage->delete($key, $this->termId);
    expect($this->storage->exists($key, $this->termId))->toBeFalse();
});

it('TermMetaStorage handles arrays correctly', function (): void {
    $key = 'array_key';
    $value = ['item1', 'item2', 'item3'];

    $this->storage->set($key, $value, $this->termId);
    $retrieved = $this->storage->get($key, $this->termId);

    expect($retrieved)->toBe($value);
});
