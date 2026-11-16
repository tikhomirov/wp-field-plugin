<?php

declare(strict_types=1);

/**
 * WP_Field v4.0 — Modern API Examples
 *
 * This file demonstrates the fluent interface and advanced field types
 * including Repeater and Flexible Content fields with infinite nesting.
 */

use WpField\Container\MetaboxContainer;
use WpField\Container\SettingsContainer;
use WpField\Field\Field;
use WpField\Legacy\LegacyAdapter;
use WpField\UI\UIManager;

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__.'/../vendor/autoload.php';

UIManager::init();
UIManager::setMode('react');

add_action('init', function (): void {
    $metabox = new MetaboxContainer('product_details', [
        'title' => 'Product Details',
        'post_types' => ['product'],
        'context' => 'normal',
        'priority' => 'high',
    ]);

    $metabox->addField(
        Field::text('sku')
            ->label('Product SKU')
            ->placeholder('Enter SKU')
            ->required()
            ->class('regular-text'),
    );

    $metabox->addField(
        Field::text('price')
            ->label('Price')
            ->placeholder('0.00')
            ->required()
            ->pattern('/^\d+(\.\d{2})?$/'),
    );

    $metabox->addField(
        Field::repeater('product_features')
            ->label('Product Features')
            ->description('Add multiple product features')
            ->fields([
                Field::text('title')
                    ->label('Feature Title')
                    ->required(),
                Field::text('description')
                    ->label('Feature Description'),
            ])
            ->min(1)
            ->max(10)
            ->buttonLabel('Add Feature')
            ->layout('table'),
    );

    $metabox->addField(
        Field::flexibleContent('product_sections')
            ->label('Product Sections')
            ->description('Build custom product page sections')
            ->addLayout('text_block', 'Text Block', [
                Field::text('heading')
                    ->label('Heading')
                    ->required(),
                Field::text('content')
                    ->label('Content'),
            ])
            ->addLayout('image_gallery', 'Image Gallery', [
                Field::text('gallery_title')
                    ->label('Gallery Title'),
                Field::repeater('images')
                    ->label('Images')
                    ->fields([
                        Field::text('image_url')
                            ->label('Image URL')
                            ->required(),
                        Field::text('caption')
                            ->label('Caption'),
                    ])
                    ->min(1)
                    ->buttonLabel('Add Image'),
            ])
            ->addLayout('video', 'Video', [
                Field::text('video_url')
                    ->label('Video URL')
                    ->required()
                    ->url(),
                Field::text('video_title')
                    ->label('Video Title'),
            ])
            ->min(1)
            ->buttonLabel('Add Section'),
    );

    $metabox->register();
});

add_action('init', function (): void {
    $settings = new SettingsContainer('plugin_settings', [
        'page_title' => 'Plugin Settings',
        'menu_title' => 'My Plugin',
        'capability' => 'manage_options',
        'icon' => 'dashicons-admin-generic',
    ]);

    $settings->addField(
        Field::text('api_key')
            ->label('API Key')
            ->description('Enter your API key')
            ->required()
            ->class('regular-text'),
    );

    $settings->addField(
        Field::text('api_secret')
            ->label('API Secret')
            ->description('Enter your API secret')
            ->required()
            ->class('regular-text')
            ->when('api_key', '!=', ''),
    );

    $settings->register();
});

add_action('init', function (): void {
    $legacyField = LegacyAdapter::make([
        'id' => 'legacy_field',
        'type' => 'text',
        'label' => 'Legacy Field',
        'placeholder' => 'Using old API',
        'required' => true,
        'validation' => 'required|email',
        'conditional_logic' => [
            ['field' => 'some_field', 'operator' => '==', 'value' => 'yes'],
        ],
    ]);

    echo $legacyField->render();
});
