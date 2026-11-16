<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StorageTypesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 2).'/WP_Field.php';
    }

    #[Test]
    public function it_supports_post_storage_type(): void
    {
        $field = new \WP_Field([
            'id' => 'test_meta',
            'type' => 'text',
            'label' => 'Test',
        ], 'post', 123);

        expect($field->storage_type)->toBe('post');
        expect($field->storage_id)->toBe(123);
    }

    #[Test]
    public function it_supports_options_storage_type(): void
    {
        $field = new \WP_Field([
            'id' => 'test_option',
            'type' => 'text',
            'label' => 'Test',
        ], 'options');

        expect($field->storage_type)->toBe('options');
    }

    #[Test]
    public function it_supports_term_storage_type(): void
    {
        $field = new \WP_Field([
            'id' => 'test_term_meta',
            'type' => 'text',
            'label' => 'Test',
        ], 'term', 456);

        expect($field->storage_type)->toBe('term');
        expect($field->storage_id)->toBe(456);
    }

    #[Test]
    public function it_supports_user_storage_type(): void
    {
        $field = new \WP_Field([
            'id' => 'test_user_meta',
            'type' => 'text',
            'label' => 'Test',
        ], 'user', 789);

        expect($field->storage_type)->toBe('user');
        expect($field->storage_id)->toBe(789);
    }

    #[Test]
    public function it_supports_comment_storage_type(): void
    {
        $field = new \WP_Field([
            'id' => 'test_comment_meta',
            'type' => 'text',
            'label' => 'Test',
        ], 'comment', 321);

        expect($field->storage_type)->toBe('comment');
        expect($field->storage_id)->toBe(321);
    }

    #[Test]
    public function it_renders_field_with_post_storage(): void
    {
        $html = \WP_Field::make([
            'id' => 'post_field',
            'type' => 'text',
            'label' => 'Post Field',
        ], false, 'post', 123);

        $this->assertStringContainsString('post_field', $html);
    }

    #[Test]
    public function it_renders_field_with_options_storage(): void
    {
        $html = \WP_Field::make([
            'id' => 'option_field',
            'type' => 'text',
            'label' => 'Option Field',
        ], false, 'options');

        $this->assertStringContainsString('option_field', $html);
    }

    #[Test]
    public function it_renders_field_with_term_storage(): void
    {
        $html = \WP_Field::make([
            'id' => 'term_field',
            'type' => 'text',
            'label' => 'Term Field',
        ], false, 'term', 456);

        $this->assertStringContainsString('term_field', $html);
    }

    #[Test]
    public function it_renders_field_with_user_storage(): void
    {
        $html = \WP_Field::make([
            'id' => 'user_field',
            'type' => 'text',
            'label' => 'User Field',
        ], false, 'user', 789);

        $this->assertStringContainsString('user_field', $html);
    }

    #[Test]
    public function it_renders_field_with_comment_storage(): void
    {
        $html = \WP_Field::make([
            'id' => 'comment_field',
            'type' => 'text',
            'label' => 'Comment Field',
        ], false, 'comment', 321);

        $this->assertStringContainsString('comment_field', $html);
    }
}
