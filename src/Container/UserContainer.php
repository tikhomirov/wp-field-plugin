<?php

declare(strict_types=1);

namespace WpField\Container;

use WpField\Storage\UserMetaStorage;

class UserContainer extends AbstractContainer
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(string $id, array $config = [])
    {
        parent::__construct($id, new UserMetaStorage, $config);
    }

    public function register(): void
    {
        add_action('show_user_profile', [$this, 'render']);
        add_action('edit_user_profile', [$this, 'render']);
        add_action('personal_options_update', [$this, 'save']);
        add_action('edit_user_profile_update', [$this, 'save']);
    }

    public function render(): void
    {
        global $user_id;

        if (! $user_id) {
            return;
        }

        $this->loadFieldValues($user_id);

        wp_nonce_field($this->id.'_nonce_action', $this->id.'_nonce');

        $rawTitle = $this->getConfig('title', 'Additional Information');
        $title = is_string($rawTitle) ? $rawTitle : 'Additional Information';
        echo '<h2>'.esc_html($title).'</h2>';
        echo '<table class="form-table">';

        foreach ($this->fields as $field) {
            echo '<tr>';
            echo '<th scope="row">';
            $rawLabel = $field->getAttribute('label');
            if ($rawLabel !== null && is_string($rawLabel)) {
                echo esc_html($rawLabel);
            }
            echo '</th>';
            echo '<td>';
            echo $field->render();
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    public function save(int|string $id): void
    {
        $userId = (int) $id;

        if (! isset($_POST[$this->id.'_nonce'])) {
            return;
        }

        if (! wp_verify_nonce($_POST[$this->id.'_nonce'], $this->id.'_nonce_action')) {
            return;
        }

        if (! current_user_can('edit_user', $userId)) {
            return;
        }

        $this->saveFieldValues($userId);
    }
}
