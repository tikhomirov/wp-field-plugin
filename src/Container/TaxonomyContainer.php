<?php

declare(strict_types=1);

namespace WpField\Container;

use WpField\Storage\TermMetaStorage;

class TaxonomyContainer extends AbstractContainer
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(string $id, array $config = [])
    {
        parent::__construct($id, new TermMetaStorage, $config);
    }

    public function register(): void
    {
        $taxonomies = $this->getConfig('taxonomies', ['category']);

        foreach ((array) $taxonomies as $taxonomy) {
            if (! is_string($taxonomy)) {
                continue;
            }
            add_action("{$taxonomy}_add_form_fields", [$this, 'renderAddForm']);
            add_action("{$taxonomy}_edit_form_fields", [$this, 'renderEditForm']);
            add_action("created_{$taxonomy}", [$this, 'save']);
            add_action("edited_{$taxonomy}", [$this, 'save']);
        }
    }

    public function renderAddForm(): void
    {
        wp_nonce_field($this->id.'_nonce_action', $this->id.'_nonce');

        foreach ($this->fields as $field) {
            echo '<div class="form-field">';
            echo $field->render();
            echo '</div>';
        }
    }

    public function renderEditForm(\WP_Term $term): void
    {
        $this->loadFieldValues($term->term_id);

        wp_nonce_field($this->id.'_nonce_action', $this->id.'_nonce');

        foreach ($this->fields as $field) {
            echo '<tr class="form-field">';
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
    }

    public function render(): void
    {
        // Handled by renderAddForm and renderEditForm
    }

    public function save(int|string $id): void
    {
        $termId = (int) $id;

        if (! isset($_POST[$this->id.'_nonce'])) {
            return;
        }

        if (! wp_verify_nonce($_POST[$this->id.'_nonce'], $this->id.'_nonce_action')) {
            return;
        }

        $this->saveFieldValues($termId);
    }
}
