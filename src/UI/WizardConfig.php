<?php

declare(strict_types=1);

namespace WpField\UI;

/**
 * Configuration for Wizard::render().
 *
 * All labels should already be translated by the host plugin.
 */
final class WizardConfig
{
    public function __construct(
        /** POST action name. */
        public readonly string $post_action = 'wp_field_wizard_save',
        /** URL query key for tracking the active step. */
        public readonly string $step_query_key = 'wizard_step',
        /** Button labels (host should translate). */
        public readonly string $next_label = 'Next',
        public readonly string $back_label = 'Back',
        public readonly string $skip_label = 'Skip',
        public readonly string $finish_label = 'Finish',
        /** Optional extra class for the root .wp-field-wizard wrapper. */
        public readonly string $wrapper_extra_class = '',
    ) {}
}
