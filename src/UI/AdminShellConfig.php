<?php

declare(strict_types=1);

namespace WpField\UI;

/**
 * Configuration for AdminShell::render().
 *
 * All labels should already be translated by the host plugin.
 */
final class AdminShellConfig
{
    public function __construct(
        /** URL query key for active segment (leaf id). */
        public readonly string $section_query_key = 'wpfs_section',
        /** URL query key for active tab (panel id). */
        public readonly string $tab_query_key = 'wpfs_tab',
        /** POST action name value. */
        public readonly string $post_action = 'wp_field_shell_save',
        /** Submit button label. */
        public readonly string $save_label = 'Save Changes',
        /** Optional extra class for the root .wp-field-shell wrapper. */
        public readonly string $wrapper_extra_class = '',
    ) {}
}
