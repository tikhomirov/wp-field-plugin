<?php

declare(strict_types=1);

namespace WpField\Container;

use WpField\Storage\PostMetaStorage;

class MetaboxContainer extends AbstractContainer
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(string $id, array $config = [])
    {
        parent::__construct($id, new PostMetaStorage, $config);
    }

    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'addMetaBox']);
        add_action('save_post', [$this, 'save']);
    }

    public function addMetaBox(): void
    {
        $postTypes = $this->getConfig('post_types', ['post']);

        $rawTitle = $this->getConfig('title', 'Custom Fields');
        $title = is_string($rawTitle) ? $rawTitle : 'Custom Fields';

        $rawContext = $this->getConfig('context', 'normal');
        $context = is_string($rawContext) ? $rawContext : 'normal';

        $rawPriority = $this->getConfig('priority', 'default');
        $priorityStr = is_string($rawPriority) ? $rawPriority : 'default';

        // Ensure priority is one of the allowed values
        $allowedPriorities = ['core', 'default', 'high', 'low'];
        /** @var 'core'|'default'|'high'|'low' $priority */
        $priority = in_array($priorityStr, $allowedPriorities, true) ? $priorityStr : 'default';

        foreach ((array) $postTypes as $postType) {
            $postTypeStr = is_string($postType) ? $postType : 'post';
            add_meta_box(
                $this->id,
                $title,
                [$this, 'render'],
                $postTypeStr,
                $context,
                $priority,
            );
        }
    }

    public function render(): void
    {
        global $post;

        if (! $post) {
            return;
        }

        $this->loadFieldValues($post->ID);

        wp_nonce_field($this->id.'_nonce_action', $this->id.'_nonce');

        echo '<div class="wp-field-metabox">';

        foreach ($this->fields as $field) {
            echo '<div class="wp-field-row">';
            echo $field->render();
            echo '</div>';
        }

        echo '</div>';
    }

    public function save(int|string $id): void
    {
        $postId = (int) $id;

        if (! isset($_POST[$this->id.'_nonce'])) {
            return;
        }

        if (! wp_verify_nonce($_POST[$this->id.'_nonce'], $this->id.'_nonce_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $postId)) {
            return;
        }

        $this->saveFieldValues($postId);
    }
}
