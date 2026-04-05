<?php

declare(strict_types=1);

namespace WpField\UI;

/**
 * Generic Admin Shell — neutral settings page wrapper for WordPress plugins and themes.
 *
 * Renders:
 *  - React mount for sidebar + tabs (admin-shell.jsx reads data-nav JSON).
 *  - PHP-rendered panel wrappers for every (leaf, panel) pair.
 *  - Sticky header breadcrumb (React updates the section label without reload).
 *  - A single form wrapping all panels + action bar.
 *
 * The host plugin provides:
 *  - A NavItem[] tree (use NavItem::flatSections() for backward-compatible flat nav).
 *  - A panel_renderer callable(string $segment_id, string $panel_id): void.
 *  - Page title, action URL, nonce HTML.
 *  - Optionally AdminShellConfig to override query keys and labels.
 *
 * @see NavItem
 * @see AdminShellConfig
 */
final class AdminShell
{
    /**
     * @param NavItem[]        $nav            Navigation tree (depth ≤ 2).
     * @param string           $active_segment Active leaf id (resolved from GET by host or passed directly).
     * @param string           $active_panel   Active panel/tab id within the leaf ('' = first panel).
     * @param string           $page_title     Page title (already translated).
     * @param string           $action_url     Form action URL.
     * @param string           $nonce_field    HTML output of wp_nonce_field().
     * @param callable         $panel_renderer callable(string $segment_id, string $panel_id): void
     * @param AdminShellConfig $config         Optional configuration.
     */
    public static function render(
        array $nav,
        string $active_segment,
        string $active_panel,
        string $page_title,
        string $action_url,
        string $nonce_field,
        callable $panel_renderer,
        AdminShellConfig $config = new AdminShellConfig(),
    ): void {
        $leaves = NavItem::collectLeaves($nav);

        // Fallback: first leaf
        if ($active_segment === '' || NavItem::findLeaf($nav, $active_segment) === null) {
            $active_segment = NavItem::firstLeafId($nav);
        }

        $active_leaf = NavItem::findLeaf($nav, $active_segment);

        // Resolve active panel: fallback to first panel id or empty string
        if ($active_panel === '' && $active_leaf?->panels) {
            $active_panel = $active_leaf->panels[0]['id'] ?? '';
        }

        $nav_json = (string) wp_json_encode(NavItem::toJsonArray($nav), JSON_UNESCAPED_UNICODE);

        $wrapper_class = trim('wp-field-shell ' . $config->wrapper_extra_class);
        ?>
        <div class="<?php echo esc_attr($wrapper_class); ?>">

            <?php /* React mount: SidebarNav renders inside this div */ ?>
            <div id="wp-field-shell-root"
                 data-nav="<?php echo esc_attr($nav_json); ?>"
                 data-active="<?php echo esc_attr($active_segment); ?>"
                 data-active-panel="<?php echo esc_attr($active_panel); ?>"
                 data-title="<?php echo esc_attr($page_title); ?>"
                 data-section-key="<?php echo esc_attr($config->section_query_key); ?>"
                 data-tab-key="<?php echo esc_attr($config->tab_query_key); ?>">
            </div>

            <div class="wp-field-shell__main">

                <div class="wp-field-shell__header">
                    <div class="wp-field-shell__breadcrumb">
<!--                        <span class="wp-field-shell__breadcrumb-page">--><?php //echo esc_html($page_title); ?><!--</span>-->
<!--                        <span class="wp-field-shell__breadcrumb-sep" aria-hidden="true"> › </span>-->
                        <span class="wp-field-shell__breadcrumb-section" id="wp-field-shell-header-section">
                            <?php echo esc_html($active_leaf?->label ?? $page_title); ?>
                        </span>
                    </div>
                </div>

                <?php /* Tab bar placeholder — React renders tabs here when leaf has panels */ ?>
                <div id="wp-field-shell-tabs-bar"></div>

                <form id="wp-field-shell-form" method="post" action="<?php echo esc_url($action_url); ?>">
                    <?php echo $nonce_field; ?>
                    <input type="hidden" name="action" value="<?php echo esc_attr($config->post_action); ?>">
                    <input type="hidden" name="<?php echo esc_attr($config->section_query_key); ?>"
                           id="wp-field-shell-active-segment"
                           value="<?php echo esc_attr($active_segment); ?>">
                    <input type="hidden" name="<?php echo esc_attr($config->tab_query_key); ?>"
                           id="wp-field-shell-active-panel"
                           value="<?php echo esc_attr($active_panel); ?>">

                    <?php foreach ($leaves as $leaf) {
                        $panels = $leaf->panels ?: [['id' => '', 'label' => '']];
                        foreach ($panels as $panel) {
                            $panel_id = (string) ($panel['id'] ?? '');
                            $is_active = $leaf->id === $active_segment
                                && ($panel_id === $active_panel || ($active_panel === '' && $panel_id === ($panels[0]['id'] ?? '')));
                            ?>
                            <div class="wp-field-shell-section-panel"
                                 id="wp-field-shell-panel-<?php echo esc_attr($leaf->id); ?>-<?php echo esc_attr($panel_id); ?>"
                                 data-segment="<?php echo esc_attr($leaf->id); ?>"
                                 data-panel="<?php echo esc_attr($panel_id); ?>"
                                <?php if (! $is_active) { ?>style="display:none"<?php } ?>>

                                <div class="wp-field-shell__card">
                                    <?php if (count($panels) > 1 || $panel_id !== '') { ?>
                                    <div class="wp-field-shell__card-header">
                                        <h2 class="wp-field-shell__card-title">
                                            <?php echo esc_html($panel['label'] ?? $leaf->label); ?>
                                        </h2>
                                    </div>
                                    <?php } else { ?>
                                    <div class="wp-field-shell__card-header">
                                        <h2 class="wp-field-shell__card-title">
                                            <?php echo esc_html($leaf->label); ?>
                                        </h2>
                                    </div>
                                    <?php } ?>

                                    <div class="wp-field-shell__card-body">
                                        <?php call_user_func($panel_renderer, $leaf->id, $panel_id); ?>
                                    </div>
                                </div>

                            </div>
                            <?php
                        }
                    } ?>

                    <div class="wp-field-shell__action-bar">
                        <button type="submit" name="save" class="button button-primary wp-field-shell__action-bar-save">
                            <?php echo esc_html($config->save_label); ?>
                        </button>
                    </div>
                </form>

            </div>

        </div>
        <?php
    }

    /**
     * Resolve active segment and panel from GET parameters using the given config.
     *
     * @param  NavItem[]                             $nav
     * @return array{segment: string, panel: string}
     */
    public static function resolveFromRequest(array $nav, AdminShellConfig $config = new AdminShellConfig()): array
    {
        $segment = isset($_GET[$config->section_query_key])
            ? sanitize_key((string) $_GET[$config->section_query_key])
            : NavItem::firstLeafId($nav);

        $panel = isset($_GET[$config->tab_query_key])
            ? sanitize_key((string) $_GET[$config->tab_query_key])
            : '';

        return ['segment' => $segment, 'panel' => $panel];
    }
}
