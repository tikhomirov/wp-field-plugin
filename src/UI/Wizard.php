<?php

declare(strict_types=1);

namespace WpField\UI;

/**
 * Generic linear Wizard — step-by-step setup flow for WordPress plugins.
 *
 * Renders:
 *  - React mount for progress indicator + nav buttons (wizard.jsx).
 *  - PHP-rendered step panels (.wp-field-wizard-step[data-wizard-step]).
 *  - A single form wrapping all steps (host handles POST per step or at finish).
 *
 * v1: linear order only; branching is reserved for v2.
 *
 * @see WizardConfig
 */
final class Wizard
{
    /**
     * @param  array<int, array{id: string, title: string, description?: string}>  $steps
     *                                                                                     Step metadata — id, title, optional description (all translated by host).
     * @param  string  $active_step  Currently active step id.
     * @param  string  $action_url  Form action URL.
     * @param  string  $nonce_field  HTML output of wp_nonce_field().
     * @param  callable  $step_renderer  callable(string $step_id): void
     * @param  WizardConfig  $config  Optional configuration.
     */
    public static function render(
        array $steps,
        string $active_step,
        string $action_url,
        string $nonce_field,
        callable $step_renderer,
        WizardConfig $config = new WizardConfig,
    ): void {
        if ($steps === []) {
            return;
        }

        // Fallback to first step
        $step_ids = array_column($steps, 'id');
        if ($active_step === '' || ! in_array($active_step, $step_ids, true)) {
            $active_step = $step_ids[0];
        }

        $steps_json = (string) wp_json_encode($steps, JSON_UNESCAPED_UNICODE);

        $wrapper_class = trim('wp-field-wizard '.$config->wrapper_extra_class);
        ?>
        <div class="<?php echo esc_attr($wrapper_class); ?>">

            <?php /* React mount: progress indicator + nav buttons */ ?>
            <div id="wp-field-wizard-root"
                 data-steps="<?php echo esc_attr($steps_json); ?>"
                 data-initial-step="<?php echo esc_attr($active_step); ?>"
                 data-step-key="<?php echo esc_attr($config->step_query_key); ?>"
                 data-next="<?php echo esc_attr($config->next_label); ?>"
                 data-back="<?php echo esc_attr($config->back_label); ?>"
                 data-skip="<?php echo esc_attr($config->skip_label); ?>"
                 data-finish="<?php echo esc_attr($config->finish_label); ?>">
            </div>

            <form id="wp-field-wizard-form" method="post" action="<?php echo esc_url($action_url); ?>">
                <?php echo $nonce_field; ?>
                <input type="hidden" name="action" value="<?php echo esc_attr($config->post_action); ?>">
                <input type="hidden" name="<?php echo esc_attr($config->step_query_key); ?>"
                       id="wp-field-wizard-active-step"
                       value="<?php echo esc_attr($active_step); ?>">

                <?php foreach ($steps as $step) {
                    $step_id = (string) $step['id'];
                    $is_active = $step_id === $active_step;
                    ?>
                    <div class="wp-field-wizard-step"
                         id="wp-field-wizard-step-<?php echo esc_attr($step_id); ?>"
                         data-wizard-step="<?php echo esc_attr($step_id); ?>"
                        <?php if (! $is_active) { ?>style="display:none"<?php } ?>>

                        <div class="wp-field-wizard-step__header">
                            <h2 class="wp-field-wizard-step__title">
                                <?php echo esc_html($step['title']); ?>
                            </h2>
                            <?php if (! empty($step['description'])) { ?>
                                <p class="wp-field-wizard-step__description">
                                    <?php echo esc_html($step['description']); ?>
                                </p>
                            <?php } ?>
                        </div>

                        <div class="wp-field-wizard-step__body">
                            <?php call_user_func($step_renderer, $step_id); ?>
                        </div>

                    </div>
                    <?php
                } ?>

            </form>

        </div>
<?php
    }

    /**
     * Resolve active step from GET parameters.
     *
     * @param  array<int, array{id: string, title: string}>  $steps
     */
    public static function resolveFromRequest(array $steps, WizardConfig $config = new WizardConfig): string
    {
        if (isset($_GET[$config->step_query_key])) {
            $requested = sanitize_key((string) $_GET[$config->step_query_key]);
            $ids = array_column($steps, 'id');
            if (in_array($requested, $ids, true)) {
                return $requested;
            }
        }

        return (string) ($steps[0]['id'] ?? '');
    }
}
