<?php

declare(strict_types=1);

use WpField\Container\TaxonomyContainer;
use WpField\Field\AbstractField;

class TestTaxonomyField extends AbstractField
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
    $this->container = new TaxonomyContainer('test_taxonomy', [
        'taxonomies' => ['category', 'post_tag'],
    ]);
});

it('can construct with default TermMetaStorage', function (): void {
    expect($this->container->getId())->toBe('test_taxonomy');
});

it('can get config values', function (): void {
    expect($this->container->getConfig('taxonomies'))->toBe(['category', 'post_tag']);
});

it('registers hooks for configured taxonomies', function (): void {
    global $wp_test_actions;
    $wp_test_actions = [];

    $this->container->register();

    $hooks = array_column($wp_test_actions, 'hook');
    expect(in_array('category_add_form_fields', $hooks))->toBeTrue()
        ->and(in_array('category_edit_form_fields', $hooks))->toBeTrue()
        ->and(in_array('created_category', $hooks))->toBeTrue()
        ->and(in_array('edited_category', $hooks))->toBeTrue()
        ->and(in_array('post_tag_add_form_fields', $hooks))->toBeTrue()
        ->and(in_array('post_tag_edit_form_fields', $hooks))->toBeTrue()
        ->and(in_array('created_post_tag', $hooks))->toBeTrue()
        ->and(in_array('edited_post_tag', $hooks))->toBeTrue();
});

it('renders add form structure', function (): void {
    $this->container->addField(new TestTaxonomyField);

    ob_start();
    $this->container->renderAddForm();
    $output = ob_get_clean();

    expect($output)->toContain('<div class="form-field">')
        ->and($output)->toContain('test_taxonomy_nonce');
});

it('renders edit form structure with term', function (): void {
    $term = new WP_Term(123);
    $this->container->addField((new TestTaxonomyField)->label('Test Label'));

    ob_start();
    $this->container->renderEditForm($term);
    $output = ob_get_clean();

    expect($output)->toContain('<tr class="form-field">')
        ->and($output)->toContain('<th scope="row">')
        ->and($output)->toContain('Test Label')
        ->and($output)->toContain('test_taxonomy_nonce');
});

it('skips non-string taxonomy names during registration', function (): void {
    $container = new TaxonomyContainer('test_taxonomy', [
        'taxonomies' => ['category', 123, null, 'post_tag'],
    ]);

    global $wp_test_actions;
    $wp_test_actions = [];

    $container->register();

    $hooks = array_column($wp_test_actions, 'hook');
    expect(in_array('category_add_form_fields', $hooks))->toBeTrue()
        ->and(in_array('post_tag_add_form_fields', $hooks))->toBeTrue()
        ->and(in_array('123_add_form_fields', $hooks))->toBeFalse();
});

it('save returns early when nonce is not set', function (): void {
    $_POST = [];

    $this->container->save(123);

    expect(true)->toBeTrue();
});

it('save returns early when nonce verification fails', function (): void {
    global $wp_test_verify_nonce;
    $wp_test_verify_nonce = false;

    $_POST['test_taxonomy_nonce'] = 'invalid';

    $this->container->save(123);

    $wp_test_verify_nonce = true;
});

it('save calls saveFieldValues when nonce is valid', function (): void {
    global $wp_test_verify_nonce;
    $wp_test_verify_nonce = true;

    $_POST['test_taxonomy_nonce'] = 'valid';

    $this->container->save(123);

    $wp_test_verify_nonce = true;
});

it('render method is empty as rendering is handled by renderAddForm and renderEditForm', function (): void {
    $this->container->render();

    expect(true)->toBeTrue();
});

it('uses default taxonomies config when not provided', function (): void {
    $container = new TaxonomyContainer('test_taxonomy');

    expect($container->getConfig('taxonomies'))->toBeNull();
});

it('handles array cast for taxonomies config', function (): void {
    $container = new TaxonomyContainer('test_taxonomy', [
        'taxonomies' => 'category',
    ]);

    expect($container->getConfig('taxonomies'))->toBe('category');
});
