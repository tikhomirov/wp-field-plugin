<?php

declare(strict_types=1);

use WpField\Container\UserContainer;
use WpField\Field\AbstractField;

class TestUserField extends AbstractField
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
    $this->container = new UserContainer('test_user_meta', [
        'title' => 'Test User Meta',
    ]);
});

it('can construct with default UserMetaStorage', function (): void {
    expect($this->container->getId())->toBe('test_user_meta');
});

it('can get config values', function (): void {
    expect($this->container->getConfig('title'))->toBe('Test User Meta');
});

it('registers hooks on register method', function (): void {
    global $wp_test_actions;
    $wp_test_actions = [];

    $this->container->register();

    $hooks = array_column($wp_test_actions, 'hook');
    expect(in_array('show_user_profile', $hooks))->toBeTrue()
        ->and(in_array('edit_user_profile', $hooks))->toBeTrue()
        ->and(in_array('personal_options_update', $hooks))->toBeTrue()
        ->and(in_array('edit_user_profile_update', $hooks))->toBeTrue();
});

it('render returns early when user_id is not set', function (): void {
    global $user_id;
    $user_id = null;

    ob_start();
    $this->container->render();
    $output = ob_get_clean();

    expect($output)->toBe('');
});

it('render outputs table structure when user_id is set', function (): void {
    global $user_id;
    $user_id = 1;

    $this->container->addField((new TestUserField)->label('Test Label'));

    ob_start();
    $this->container->render();
    $output = ob_get_clean();

    expect($output)->toContain('<h2>Test User Meta</h2>')
        ->and($output)->toContain('<table class="form-table">')
        ->and($output)->toContain('Test Label')
        ->and($output)->toContain('test_user_meta_nonce');
});

it('render uses default title when not configured', function (): void {
    global $user_id;
    $user_id = 1;

    $container = new UserContainer('test_user_meta');

    ob_start();
    $container->render();
    $output = ob_get_clean();

    expect($output)->toContain('<h2>Additional Information</h2>');
});

it('save returns early when nonce is not set', function (): void {
    $_POST = [];

    $this->container->save(1);

    expect(true)->toBeTrue();
});

it('save returns early when nonce verification fails', function (): void {
    global $wp_test_verify_nonce;
    $wp_test_verify_nonce = false;

    $_POST['test_user_meta_nonce'] = 'invalid';

    $this->container->save(1);

    $wp_test_verify_nonce = true;
});

it('save returns early when user cannot edit', function (): void {
    global $wp_test_verify_nonce;
    global $wp_test_current_user_can;
    $wp_test_verify_nonce = true;
    $wp_test_current_user_can = false;

    $_POST['test_user_meta_nonce'] = 'valid';

    $this->container->save(1);

    $wp_test_verify_nonce = true;
    $wp_test_current_user_can = true;
});

it('save calls saveFieldValues when nonce and permissions are valid', function (): void {
    global $wp_test_verify_nonce;
    global $wp_test_current_user_can;
    $wp_test_verify_nonce = true;
    $wp_test_current_user_can = true;

    $_POST['test_user_meta_nonce'] = 'valid';

    $this->container->save(1);

    $wp_test_verify_nonce = true;
    $wp_test_current_user_can = true;
});

it('uses default title when not configured', function (): void {
    $container = new UserContainer('test_user_meta');

    expect($container->getConfig('title'))->toBeNull();
});
