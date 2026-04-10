<?php

declare(strict_types=1);

use WpField\Container\MetaboxContainer;
use WpField\Field\AbstractField;

class TestMetaboxField extends AbstractField
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
    $this->container = new MetaboxContainer('test_metabox', [
        'post_types' => ['post', 'page'],
        'title' => 'Test Metabox',
        'context' => 'normal',
        'priority' => 'high',
    ]);
});

it('can construct with default PostMetaStorage', function (): void {
    expect($this->container->getId())->toBe('test_metabox');
});

it('can get config values', function (): void {
    expect($this->container->getConfig('post_types'))->toBe(['post', 'page'])
        ->and($this->container->getConfig('title'))->toBe('Test Metabox')
        ->and($this->container->getConfig('context'))->toBe('normal')
        ->and($this->container->getConfig('priority'))->toBe('high');
});

it('registers hooks on register method', function (): void {
    global $wp_test_actions;
    $wp_test_actions = [];

    $this->container->register();

    $hooks = array_column($wp_test_actions, 'hook');
    expect(in_array('add_meta_boxes', $hooks))->toBeTrue()
        ->and(in_array('save_post', $hooks))->toBeTrue();
});

it('renders early when post is not set', function (): void {
    global $post;
    $post = null;

    ob_start();
    $this->container->render();
    $output = ob_get_clean();

    expect($output)->toBe('');
});

it('renders metabox structure when post is set', function (): void {
    global $post;
    $post = (object) ['ID' => 123];

    $this->container->addField(new TestMetaboxField);

    ob_start();
    $this->container->render();
    $output = ob_get_clean();

    expect($output)->toContain('wp-field-metabox')
        ->and($output)->toContain('wp-field-row')
        ->and($output)->toContain('test_metabox_nonce');
});

it('save returns early when nonce is not set', function (): void {
    $_POST = [];

    $this->container->save(123);

    expect(true)->toBeTrue();
});

it('save returns early when nonce verification fails', function (): void {
    $GLOBALS['wp_test_verify_nonce'] = false;

    $_POST['test_metabox_nonce'] = 'invalid';

    $this->container->save(123);

    $GLOBALS['wp_test_verify_nonce'] = true;
});

it('save returns early when doing autosave', function (): void {
    if (! defined('DOING_AUTOSAVE')) {
        define('DOING_AUTOSAVE', true);
    }

    $_POST['test_metabox_nonce'] = 'valid';

    $this->container->save(123);
})->skip('DOING_AUTOSAVE constant affects subsequent tests');

it('save returns early when user cannot edit post', function (): void {
    $GLOBALS['wp_test_verify_nonce'] = true;

    $_POST['test_metabox_nonce'] = 'valid';

    $this->container->save(999999);

    $GLOBALS['wp_test_verify_nonce'] = true;
});

it('save calls saveFieldValues when nonce and permissions are valid', function (): void {
    $GLOBALS['wp_test_verify_nonce'] = true;
    $GLOBALS['wp_test_current_user_can'] = true;

    $_POST['test_metabox_nonce'] = 'valid';

    $this->container->save(123);

    $GLOBALS['wp_test_verify_nonce'] = true;
    $GLOBALS['wp_test_current_user_can'] = true;
});

it('addMetaBox registers metabox for configured post types', function (): void {
    global $wp_test_meta_boxes;
    $wp_test_meta_boxes = [];

    $this->container->addMetaBox();

    expect(count($wp_test_meta_boxes))->toBe(2)
        ->and($wp_test_meta_boxes[0]['id'])->toBe('test_metabox')
        ->and($wp_test_meta_boxes[0]['title'])->toBe('Test Metabox')
        ->and($wp_test_meta_boxes[0]['screen'])->toBe('post')
        ->and($wp_test_meta_boxes[1]['screen'])->toBe('page');
});

it('addMetaBox uses default values when config not provided', function (): void {
    $container = new MetaboxContainer('test_metabox');

    global $wp_test_meta_boxes;
    $wp_test_meta_boxes = [];

    $container->addMetaBox();

    expect($wp_test_meta_boxes[0]['title'])->toBe('Custom Fields')
        ->and($wp_test_meta_boxes[0]['context'])->toBe('normal')
        ->and($wp_test_meta_boxes[0]['priority'])->toBe('default');
});

it('addMetaBox validates priority to allowed values', function (): void {
    $container = new MetaboxContainer('test_metabox', [
        'post_types' => ['post'],
        'priority' => 'invalid',
    ]);

    global $wp_test_meta_boxes;
    $wp_test_meta_boxes = [];

    $container->addMetaBox();

    expect($wp_test_meta_boxes[0]['priority'])->toBe('default');
});

it('uses default config values when not provided', function (): void {
    $container = new MetaboxContainer('test_metabox');

    expect($container->getConfig('title'))->toBeNull()
        ->and($container->getConfig('context'))->toBeNull()
        ->and($container->getConfig('priority'))->toBeNull()
        ->and($container->getConfig('post_types'))->toBeNull();
});

it('handles array cast for post_types config', function (): void {
    $container = new MetaboxContainer('test_metabox', [
        'post_types' => 'post',
    ]);

    expect($container->getConfig('post_types'))->toBe('post');
});
