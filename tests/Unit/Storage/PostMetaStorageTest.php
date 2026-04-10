<?php

declare(strict_types=1);

use WpField\Storage\PostMetaStorage;

beforeEach(function (): void {
    global $wp_test_meta_storage;
    $this->postId = random_int(10000, 99999);
    $wp_test_meta_storage['post'][$this->postId] = [];
    $this->storage = new PostMetaStorage;
});

afterEach(function (): void {
    global $wp_test_meta_storage;
    unset($wp_test_meta_storage['post'][$this->postId]);
});

it('PostMetaStorage can set and get values', function (): void {
    $key = 'test_meta_key';
    $value = 'test_value';

    $this->storage->set($key, $value, $this->postId);
    $retrieved = $this->storage->get($key, $this->postId);

    expect($retrieved)->toBe($value);
});

it('PostMetaStorage can check if key exists', function (): void {
    $key = 'existing_key';
    $value = 'some_value';

    $this->storage->set($key, $value, $this->postId);

    expect($this->storage->exists($key, $this->postId))->toBeTrue()
        ->and($this->storage->exists('non_existing_key', $this->postId))->toBeFalse();
});

it('PostMetaStorage can delete values', function (): void {
    $key = 'delete_test_key';
    $value = 'delete_test_value';

    $this->storage->set($key, $value, $this->postId);
    expect($this->storage->exists($key, $this->postId))->toBeTrue();

    $this->storage->delete($key, $this->postId);
    expect($this->storage->exists($key, $this->postId))->toBeFalse();
});

it('PostMetaStorage handles arrays correctly', function (): void {
    $key = 'array_key';
    $value = ['item1', 'item2', 'item3'];

    $this->storage->set($key, $value, $this->postId);
    $retrieved = $this->storage->get($key, $this->postId);

    expect($retrieved)->toBe($value);
});
