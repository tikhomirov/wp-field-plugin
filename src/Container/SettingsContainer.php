<?php

declare(strict_types=1);

namespace WpField\Container;

use WpField\Storage\OptionStorage;

class SettingsContainer extends AbstractContainer
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(string $id, array $config = [])
    {
        parent::__construct($id, new OptionStorage, $config);
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function addSettingsPage(): void
    {
        $rawPageTitle = $this->getConfig('page_title', 'Settings');
        $pageTitle = is_string($rawPageTitle) ? $rawPageTitle : 'Settings';

        $rawMenuTitle = $this->getConfig('menu_title', 'Settings');
        $menuTitle = is_string($rawMenuTitle) ? $rawMenuTitle : 'Settings';

        $rawCapability = $this->getConfig('capability', 'manage_options');
        $capability = is_string($rawCapability) ? $rawCapability : 'manage_options';

        $rawMenuSlug = $this->getConfig('menu_slug', $this->id);
        $menuSlug = is_string($rawMenuSlug) ? $rawMenuSlug : $this->id;

        $rawIcon = $this->getConfig('icon', 'dashicons-admin-generic');
        $icon = is_string($rawIcon) ? $rawIcon : 'dashicons-admin-generic';

        $rawPosition = $this->getConfig('position', null);
        $position = is_int($rawPosition) || is_float($rawPosition) ? $rawPosition : null;

        $rawParentSlug = $this->getConfig('parent_slug', null);
        $parentSlug = is_string($rawParentSlug) ? $rawParentSlug : null;

        if ($parentSlug !== null) {
            add_submenu_page(
                $parentSlug,
                $pageTitle,
                $menuTitle,
                $capability,
                $menuSlug,
                [$this, 'render'],
            );
        } else {
            add_menu_page(
                $pageTitle,
                $menuTitle,
                $capability,
                $menuSlug,
                [$this, 'render'],
                $icon,
                $position,
            );
        }
    }

    public function registerSettings(): void
    {
        foreach ($this->fields as $field) {
            register_setting(
                $this->id,
                $field->getName(),
                [
                    'sanitize_callback' => [$field, 'sanitize'],
                ],
            );
        }
    }

    public function render(): void
    {
        $this->loadFieldValues(0);

        echo '<div class="wrap">';
        $rawPageTitle = $this->getConfig('page_title', 'Settings');
        $pageTitle = is_string($rawPageTitle) ? $rawPageTitle : 'Settings';
        echo '<h1>'.esc_html($pageTitle).'</h1>';
        echo '<form method="post" action="options.php">';

        settings_fields($this->id);

        echo '<table class="form-table">';

        foreach ($this->fields as $field) {
            echo '<tr>';
            echo '<th scope="row">';
            $rawLabel = $field->getAttribute('label');
            if ($rawLabel !== null && $rawLabel !== '') {
                $label = is_string($rawLabel) ? $rawLabel : '';
                if ($label !== '') {
                    echo esc_html($label);
                }
            }
            echo '</th>';
            echo '<td>';
            echo $field->render();
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';

        submit_button();

        echo '</form>';
        echo '</div>';
    }

    public function save(int|string $id): void
    {
        // Settings are saved automatically via options.php
    }
}
