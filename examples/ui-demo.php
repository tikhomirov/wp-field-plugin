<?php

/**
 * WP Field Admin Shell — UI Elements Demo Page
 *
 * All styled elements in admin-shell context.
 * URL: tools.php?page=wp-field-ui-demo  (WP_DEBUG only)
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

class WP_Field_UI_Demo
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'tools.php',
            'Admin Shell UI Demo',
            'Admin Shell UI Demo',
            'manage_options',
            'wp-field-ui-demo',
            [$this, 'renderPage'],
        );
    }

    public function enqueueAssets(string $hook): void
    {
        if ($hook !== 'tools_page_wp-field-ui-demo') {
            return;
        }

        $base_path = trailingslashit(dirname(__DIR__));
        $base_url = trailingslashit(plugin_dir_url(__DIR__));

        wp_enqueue_style('wp-color-picker');

        $wpfield_css = $base_path.'legacy/assets/css/wp-field.css';
        if (file_exists($wpfield_css)) {
            wp_enqueue_style('wp-field-main', $base_url.'legacy/assets/css/wp-field.css', [], (string) filemtime($wpfield_css));
        }

        $shell_css = $base_path.'assets/css/admin-shell.css';
        if (file_exists($shell_css)) {
            wp_enqueue_style('iiko-admin-shell', $base_url.'assets/css/admin-shell.css', ['wp-field-main'], (string) filemtime($shell_css));
        }

        wp_add_inline_style('iiko-admin-shell', '
            /* ── Demo page layout ── */
            .ui-demo-wrap { max-width: 960px; }
            .ui-demo-wrap h1 { font-size: 22px; font-weight: 700; color: #111827; margin: 24px 0 4px; }
            .ui-demo-wrap .ui-demo-subtitle { font-size: 13.5px; color: #6b7280; margin: 0 0 32px; }
            .ui-demo-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 8px; }
            .ui-demo-col { display: flex; flex-direction: column; gap: 8px; }
            .ui-demo-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; color: #9ca3af; }
            .ui-demo-section-divider { height: 1px; background: #f3f4f6; margin: 16px 0; }
            /* Badge / pill component */
            .ui-badge {
                display: inline-flex; align-items: center; gap: 4px;
                padding: 2px 8px; border-radius: 100px; font-size: 11.5px; font-weight: 500;
                line-height: 1.6;
            }
            .ui-badge-gray   { background: #f3f4f6; color: #374151; }
            .ui-badge-blue   { background: #dbeafe; color: #1e40af; }
            .ui-badge-green  { background: #dcfce7; color: #166534; }
            .ui-badge-yellow { background: #fef9c3; color: #854d0e; }
            .ui-badge-red    { background: #fee2e2; color: #991b1b; }
            .ui-badge-purple { background: #ede9fe; color: #5b21b6; }
            /* Kbd / shortcut */
            .ui-kbd {
                display: inline-flex; align-items: center; justify-content: center;
                min-width: 22px; height: 20px; padding: 0 5px;
                background: #f3f4f6; border: 1px solid #e5e7eb;
                border-bottom-width: 2px; border-radius: 4px;
                font-size: 11px; font-weight: 600; color: #374151;
                font-family: -apple-system, monospace; letter-spacing: 0;
            }
            /* Separator */
            .ui-separator { height: 1px; background: #e5e7eb; margin: 4px 0; }
            /* Avatar */
            .ui-avatar {
                width: 36px; height: 36px; border-radius: 50%;
                background: #111827; color: #fff;
                display: flex; align-items: center; justify-content: center;
                font-size: 13px; font-weight: 600; flex-shrink: 0;
            }
            .ui-avatar-sm { width: 28px; height: 28px; font-size: 11px; }
            .ui-avatar-lg { width: 48px; height: 48px; font-size: 16px; }
            /* Input with prefix/suffix */
            .ui-input-group {
                display: flex; align-items: center;
                border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden;
                background: #fff;
            }
            .ui-input-group:focus-within {
                border-color: #111827; box-shadow: 0 0 0 3px rgba(17,24,39,0.1);
            }
            .ui-input-affix {
                padding: 0 10px; font-size: 13px; color: #9ca3af;
                background: #f9fafb; border-right: 1px solid #d1d5db;
                height: 36px; display: flex; align-items: center; white-space: nowrap;
                flex-shrink: 0;
            }
            .ui-input-suffix { border-right: none; border-left: 1px solid #d1d5db; }
            .ui-input-group input {
                border: none !important; box-shadow: none !important;
                outline: none !important; flex: 1; min-width: 0;
                padding: 0 10px !important; height: 36px !important;
                font-size: 13.5px; background: transparent;
            }
            /* Progress bar */
            .ui-progress { height: 6px; background: #f3f4f6; border-radius: 100px; overflow: hidden; }
            .ui-progress-bar { height: 100%; background: #111827; border-radius: 100px; transition: width 0.3s ease; }
            /* Skeleton */
            .ui-skeleton {
                background: linear-gradient(90deg, #f3f4f6 25%, #e9eaec 50%, #f3f4f6 75%);
                background-size: 200% 100%;
                animation: ui-shimmer 1.5s infinite;
                border-radius: 4px;
            }
            @keyframes ui-shimmer {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }
            /* Checkbox pill variant — higher specificity beats admin-shell.css !important */
            .wp-field-shell__card-body input[type="checkbox"].ui-checkbox-pill {
                position: absolute !important;
                width: 1px !important;
                height: 1px !important;
                overflow: hidden !important;
                clip: rect(0,0,0,0) !important;
                opacity: 0 !important;
                pointer-events: none !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            .ui-checkbox-pill + label {
                display: inline-flex; align-items: center; gap: 6px;
                padding: 5px 12px; border: 1px solid #e5e7eb; border-radius: 100px;
                font-size: 13px; color: #374151; cursor: pointer;
                transition: background 0.12s, border-color 0.12s, color 0.12s;
                user-select: none;
            }
            .ui-checkbox-pill:checked + label {
                background: #111827; border-color: #111827; color: #fff;
            }
            .ui-checkbox-pill + label:hover { border-color: #9ca3af; }
            /* Table */
            .ui-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
            .ui-table th { font-weight: 600; color: #374151; text-align: left;
                padding: 10px 16px; border-bottom: 1px solid #e5e7eb; font-size: 12px;
                text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; }
            .ui-table td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; color: #374151; }
            .ui-table tr:last-child td { border-bottom: none; }
            .ui-table tr:hover td { background: #f9fafb; }
        ');
    }

    private function card(string $title, string $body, string $extra_style = ''): void
    {
        ?>
        <div class="wp-field-shell__card" style="margin-bottom:20px;<?php echo esc_attr($extra_style); ?>">
            <div class="wp-field-shell__card-header">
                <h3 class="wp-field-shell__card-title"><?php echo esc_html($title); ?></h3>
            </div>
            <div class="wp-field-shell__card-body" style="padding:20px 24px;">
                <?php echo $body; // phpcs:ignore WordPress.Security.EscapeOutput?>
            </div>
        </div>
        <?php
    }

    public function renderPage(): void
    {
        ob_start();
        $this->renderButtons();
        $buttons = ob_get_clean();

        ob_start();
        $this->renderCheckbox();
        $checkbox = ob_get_clean();

        ob_start();
        $this->renderRadio();
        $radio = ob_get_clean();

        ob_start();
        $this->renderSwitch();
        $switch = ob_get_clean();

        ob_start();
        $this->renderInputs();
        $inputs = ob_get_clean();

        ob_start();
        $this->renderSelect();
        $select = ob_get_clean();

        ob_start();
        $this->renderBadges();
        $badges = ob_get_clean();

        ob_start();
        $this->renderNotices();
        $notices = ob_get_clean();

        ob_start();
        $this->renderTypography();
        $typography = ob_get_clean();

        ob_start();
        $this->renderActionBar();
        $actionbar = ob_get_clean();

        ob_start();
        $this->renderMisc();
        $misc = ob_get_clean();
        ?>
        <div class="wrap ui-demo-wrap">
            <h1>Admin Shell UI Demo</h1>
            <p class="ui-demo-subtitle">
                All styled elements in <code>.wp-field-shell</code> context.
                Reference: <a href="https://fluxui.dev/components/" target="_blank">Flux UI</a>
            </p>

            <div class="wp-field-shell" style="display:block;min-height:auto;margin:0;background:transparent;">
                <?php
                $this->card('Buttons', $buttons);
        $this->card('Checkbox Group', $checkbox);
        $this->card('Radio Group', $radio);
        $this->card('Switch / Toggle', $switch);
        $this->card('Input / Textarea', $inputs);
        $this->card('Select / Dropdown', $select);
        $this->card('Badge / Tag / Kbd', $badges);
        $this->card('Notices / Callouts', $notices);
        $this->card('Typography', $typography);
        $this->card('Misc: Avatar · Progress · Skeleton · Table · Separator', $misc);
        $this->card('Action Bar', $actionbar);
        ?>
            </div>
        </div>
        <?php
    }

    private function renderButtons(): void
    {
        ?>
        <table class="form-table" style="margin:0;">
            <tr>
                <th>Primary</th>
                <td class="ui-demo-row">
                    <button class="button button-primary wp-field-shell__action-bar-save" type="button">Save Changes</button>
                    <button class="button button-primary wp-field-shell__action-bar-save" type="button" style="opacity:.45;cursor:not-allowed;" disabled>Disabled</button>
                </td>
            </tr>
            <tr>
                <th>Default / Outline</th>
                <td class="ui-demo-row">
                    <button class="button" type="button">Default</button>
                    <button class="button button-secondary" type="button">Secondary</button>
                    <button class="button" type="button" disabled style="opacity:.45;cursor:not-allowed;">Disabled</button>
                </td>
            </tr>
            <tr>
                <th>Danger</th>
                <td class="ui-demo-row">
                    <button class="button button-link-delete" type="button">Delete item</button>
                </td>
            </tr>
            <tr>
                <th>Sizes</th>
                <td class="ui-demo-row" style="align-items:center;">
                    <button class="button button-primary wp-field-shell__action-bar-save" type="button" style="height:40px;line-height:38px;font-size:14px;">Large</button>
                    <button class="button button-primary wp-field-shell__action-bar-save" type="button">Default</button>
                    <button class="button button-primary wp-field-shell__action-bar-save" type="button" style="height:28px;line-height:26px;font-size:12px;min-width:auto;padding:0 10px;">Small</button>
                </td>
            </tr>
            <tr>
                <th>Button group</th>
                <td>
                    <div style="display:inline-flex;">
                        <button class="button" type="button" style="border-radius:7px 0 0 7px;border-right-width:.5px;">Oldest</button>
                        <button class="button" type="button" style="border-radius:0;border-left-width:.5px;border-right-width:.5px;">Newest</button>
                        <button class="button" type="button" style="border-radius:0 7px 7px 0;border-left-width:.5px;">Top</button>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    private function renderCheckbox(): void
    {
        ?>
        <table class="form-table" style="margin:0;">
            <tr>
                <th>Basic group</th>
                <td>
                    <fieldset>
                        <label><input type="checkbox" checked> Push notifications</label>
                        <label><input type="checkbox" checked> Email</label>
                        <label><input type="checkbox"> In-app alerts</label>
                        <label><input type="checkbox"> SMS</label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th>With description</th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" checked>
                            <span>Newsletter<span class="description">Receive monthly newsletter with latest updates and offers.</span></span>
                        </label>
                        <label>
                            <input type="checkbox">
                            <span>Product updates<span class="description">Stay informed about new features and product updates.</span></span>
                        </label>
                        <label>
                            <input type="checkbox">
                            <span>Event invitations<span class="description">Get invitations to our exclusive events and webinars.</span></span>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th>Disabled</th>
                <td>
                    <fieldset>
                        <label><input type="checkbox" checked disabled> Checked &amp; disabled</label>
                        <label><input type="checkbox" disabled> Unchecked &amp; disabled</label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th>Horizontal</th>
                <td>
                    <div style="display:flex;gap:20px;flex-wrap:wrap;">
                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13.5px;color:#374151;">
                            <input type="checkbox" checked> English
                        </label>
                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13.5px;color:#374151;">
                            <input type="checkbox" checked> Spanish
                        </label>
                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13.5px;color:#374151;">
                            <input type="checkbox"> French
                        </label>
                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13.5px;color:#374151;">
                            <input type="checkbox"> German
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Pill variant</th>
                <td>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <?php foreach (['Fantasy', 'Sci-Fi', 'Horror', 'Mystery', 'Romance', 'Thriller'] as $i => $g) { ?>
                        <input type="checkbox" id="pill-<?php echo $i; ?>" class="ui-checkbox-pill" <?php echo $i < 2 ? 'checked' : ''; ?>>
                        <label for="pill-<?php echo $i; ?>"><?php echo esc_html($g); ?></label>
                        <?php } ?>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    private function renderRadio(): void
    {
        ?>
        <table class="form-table" style="margin:0;">
            <tr>
                <th>Basic group</th>
                <td>
                    <fieldset>
                        <label><input type="radio" name="payment" value="card" checked> Credit Card</label>
                        <label><input type="radio" name="payment" value="paypal"> PayPal</label>
                        <label><input type="radio" name="payment" value="bank"> Bank transfer</label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th>With description</th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="role" value="admin" checked>
                            <span>Administrator<span class="description">Administrator users can perform any action.</span></span>
                        </label>
                        <label>
                            <input type="radio" name="role" value="editor">
                            <span>Editor<span class="description">Editor users have the ability to read, create, and update.</span></span>
                        </label>
                        <label>
                            <input type="radio" name="role" value="viewer">
                            <span>Viewer<span class="description">Viewer users only have the ability to read.</span></span>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th>Horizontal</th>
                <td>
                    <div style="display:flex;gap:20px;flex-wrap:wrap;">
                        <?php foreach (['Small', 'Medium', 'Large', 'XL'] as $s) { ?>
                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13.5px;color:#374151;">
                            <input type="radio" name="size" value="<?php echo strtolower($s); ?>" <?php echo $s === 'Medium' ? 'checked' : ''; ?>>
                            <?php echo esc_html($s); ?>
                        </label>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Disabled</th>
                <td>
                    <fieldset>
                        <label><input type="radio" name="disabled-r" checked disabled> Selected &amp; disabled</label>
                        <label><input type="radio" name="disabled-r" disabled> Unselected &amp; disabled</label>
                    </fieldset>
                </td>
            </tr>
        </table>
        <?php
    }

    private function renderSwitch(): void
    {
        $rows = [
            ['Communication emails', 'Receive emails about your account activity.', true],
            ['Marketing emails', 'Receive emails about new products, features, and more.', true],
            ['Social emails', 'Receive emails for friend requests, follows, and more.', false],
            ['Security emails', 'Receive emails about your account activity and security.', false],
        ];
        ?>
        <table class="form-table" style="margin:0;">
            <?php foreach ($rows as [$label, $desc, $checked]) { ?>
            <tr>
                <th><?php echo esc_html($label); ?></th>
                <td>
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                        <span class="wp-field-switcher" style="margin-top:1px;">
                            <input type="checkbox" <?php echo $checked ? 'checked' : ''; ?>>
                            <span class="wp-field-switcher-slider"></span>
                        </span>
                        <span style="font-size:13.5px;color:#374151;line-height:1.5;">
                            <?php echo esc_html($desc); ?>
                        </span>
                    </label>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <th>Disabled</th>
                <td>
                    <label style="display:flex;align-items:center;gap:10px;cursor:not-allowed;opacity:.45;">
                        <span class="wp-field-switcher">
                            <input type="checkbox" checked disabled>
                            <span class="wp-field-switcher-slider"></span>
                        </span>
                        <span style="font-size:13.5px;color:#374151;">Feature disabled</span>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    private function renderInputs(): void
    {
        ?>
        <table class="form-table" style="margin:0;">
            <tr>
                <th>Text</th>
                <td>
                    <input type="text" class="regular-text" placeholder="Your name...">
                    <p class="description">Standard text input.</p>
                </td>
            </tr>
            <tr>
                <th>Email</th>
                <td><input type="email" class="regular-text" placeholder="you@example.com" value="admin@site.com"></td>
            </tr>
            <tr>
                <th>Password</th>
                <td><input type="password" class="regular-text" value="secret123"></td>
            </tr>
            <tr>
                <th>Number</th>
                <td><input type="number" value="42" style="width:100px;" min="0" max="999"></td>
            </tr>
            <tr>
                <th>URL</th>
                <td><input type="url" class="regular-text" value="https://example.com"></td>
            </tr>
            <tr>
                <th>With prefix</th>
                <td>
                    <div class="ui-input-group" style="max-width:360px;">
                        <span class="ui-input-affix">https://</span>
                        <input type="text" placeholder="yourdomain.com">
                    </div>
                </td>
            </tr>
            <tr>
                <th>With suffix</th>
                <td>
                    <div class="ui-input-group" style="max-width:240px;">
                        <input type="number" value="30" style="width:auto;">
                        <span class="ui-input-affix ui-input-suffix">minutes</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Textarea</th>
                <td>
                    <textarea rows="3" class="regular-text" placeholder="Enter your message here...">WooCommerce integration with iiko RMS for order management.</textarea>
                    <p class="description">Resizable multiline input.</p>
                </td>
            </tr>
            <tr>
                <th>Disabled</th>
                <td><input type="text" class="regular-text" value="Read-only value" disabled></td>
            </tr>
            <tr>
                <th>Readonly</th>
                <td>
                    <input type="text" class="regular-text" value="sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" readonly>
                    <p class="description">API key — click to copy.</p>
                </td>
            </tr>
            <tr>
                <th>Range / Slider</th>
                <td>
                    <div class="wp-field-slider-wrapper">
                        <input type="range" class="wp-field-slider" min="0" max="100" value="65">
                        <div class="wp-field-slider-value">65%</div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Color</th>
                <td>
                    <input type="color" value="#111827" style="width:48px;height:36px;padding:2px;border:1px solid #d1d5db;border-radius:6px;cursor:pointer;">
                    <input type="color" value="#3b82f6" style="width:48px;height:36px;padding:2px;border:1px solid #d1d5db;border-radius:6px;cursor:pointer;">
                    <input type="color" value="#ef4444" style="width:48px;height:36px;padding:2px;border:1px solid #d1d5db;border-radius:6px;cursor:pointer;">
                </td>
            </tr>
        </table>
        <?php
    }

    private function renderSelect(): void
    {
        ?>
        <table class="form-table" style="margin:0;">
            <tr>
                <th>Single select</th>
                <td>
                    <select style="min-width:220px;">
                        <option value="">Choose industry...</option>
                        <option value="photo" selected>Photography</option>
                        <option value="design">Design services</option>
                        <option value="web">Web development</option>
                        <option value="legal">Legal services</option>
                        <option value="other">Other</option>
                    </select>
                    <p class="description">Dropdown with chevron.</p>
                </td>
            </tr>
            <tr>
                <th>Multi-select</th>
                <td>
                    <select multiple style="min-width:220px;height:100px;">
                        <option value="en" selected>English</option>
                        <option value="ru" selected>Russian</option>
                        <option value="de">German</option>
                        <option value="fr">French</option>
                        <option value="es">Spanish</option>
                    </select>
                    <p class="description">Hold Ctrl / ⌘ to select multiple.</p>
                </td>
            </tr>
            <tr>
                <th>Disabled</th>
                <td>
                    <select disabled style="min-width:220px;">
                        <option>Not available</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Button set</th>
                <td>
                    <div class="wp-field-button-set">
                        <?php foreach (['Day', 'Week', 'Month', 'Year'] as $i => $opt) { ?>
                        <label class="wp-field-button-set-item <?php echo $i === 1 ? 'active' : ''; ?>">
                            <input type="radio" name="period" value="<?php echo strtolower($opt); ?>" <?php echo $i === 1 ? 'checked' : ''; ?>>
                            <span><?php echo esc_html($opt); ?></span>
                        </label>
                        <?php } ?>
                    </div>
                    <p class="description">Segmented control / button-set selector.</p>
                </td>
            </tr>
        </table>
        <?php
    }

    private function renderBadges(): void
    {
        ?>
        <table class="form-table" style="margin:0;">
            <tr>
                <th>Badge / Tag</th>
                <td>
                    <div class="ui-demo-row">
                        <span class="ui-badge ui-badge-gray">Default</span>
                        <span class="ui-badge ui-badge-blue">Info</span>
                        <span class="ui-badge ui-badge-green">Active</span>
                        <span class="ui-badge ui-badge-yellow">Warning</span>
                        <span class="ui-badge ui-badge-red">Error</span>
                        <span class="ui-badge ui-badge-purple">Premium</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Keyboard shortcut</th>
                <td>
                    <div class="ui-demo-row">
                        <span>Save</span>
                        <span class="ui-kbd">⌘</span><span class="ui-kbd">S</span>
                    </div>
                    <div class="ui-demo-row" style="margin-top:6px;">
                        <span>Search</span>
                        <span class="ui-kbd">⌘</span><span class="ui-kbd">K</span>
                    </div>
                    <div class="ui-demo-row" style="margin-top:6px;">
                        <span>Close</span>
                        <span class="ui-kbd">Esc</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Inline code</th>
                <td>
                    Path: <code>wp-content/uploads/iiko/</code><br>
                    Hook: <code style="font-size:13px;">woocommerce_get_sections_iiko</code>
                </td>
            </tr>
        </table>
        <?php
    }

    private function renderNotices(): void
    {
        ?>
        <div class="wp-field-notice wp-field-notice-info" style="margin-bottom:10px;">
            <strong>Info:</strong> Your settings have been saved successfully.
        </div>
        <div class="wp-field-notice wp-field-notice-success" style="margin-bottom:10px;">
            <strong>Success:</strong> Connection to iiko API established. Latency: 142ms.
        </div>
        <div class="wp-field-notice wp-field-notice-warning" style="margin-bottom:10px;">
            <strong>Warning:</strong> API rate limit approaching (90%). Consider reducing sync frequency.
        </div>
        <div class="wp-field-notice wp-field-notice-error">
            <strong>Error:</strong> Failed to connect to iiko API. Check your credentials and network access.
        </div>
        <?php
    }

    private function renderTypography(): void
    {
        ?>
        <table class="form-table" style="margin:0;">
            <tr>
                <th>Label (th)</th>
                <td style="padding-top:20px;">
                    <span style="font-size:13.5px;font-weight:600;color:#374151;">Section label</span>
                    — bold, text-secondary
                </td>
            </tr>
            <tr>
                <th>Primary text</th>
                <td style="font-size:13.5px;color:#111827;">Primary text — #111827, 13.5px</td>
            </tr>
            <tr>
                <th>Secondary text</th>
                <td style="font-size:13.5px;color:#374151;">Secondary text — #374151, 13.5px</td>
            </tr>
            <tr>
                <th>Muted text</th>
                <td style="font-size:13.5px;color:#6b7280;">Muted text — #6b7280, 13.5px</td>
            </tr>
            <tr>
                <th>Help / Description</th>
                <td><p class="description">Help text below the field — #9ca3af, 12.5px, normal style.</p></td>
            </tr>
            <tr>
                <th>Card section headings</th>
                <td>
                    <h2 style="margin:0 0 8px !important;">Card h2 heading (13.5px, 600)</h2>
                    <h5 style="margin:0 !important;">Card h5 sub-heading (13px, 600, muted)</h5>
                </td>
            </tr>
        </table>
        <?php
    }

    private function renderMisc(): void
    {
        ?>
        <table class="form-table" style="margin:0;">
            <tr>
                <th>Avatar</th>
                <td>
                    <div class="ui-demo-row" style="align-items:center;">
                        <div class="ui-avatar ui-avatar-sm">A</div>
                        <div class="ui-avatar">JD</div>
                        <div class="ui-avatar ui-avatar-lg" style="background:#3b82f6;">KD</div>
                        <div class="ui-avatar" style="background:#10b981;">HS</div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Progress</th>
                <td class="ui-demo-col" style="max-width:320px;">
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-bottom:4px;">
                            <span>Storage used</span><span>68%</span>
                        </div>
                        <div class="ui-progress">
                            <div class="ui-progress-bar" style="width:68%;"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-bottom:4px;">
                            <span>API quota</span><span>92%</span>
                        </div>
                        <div class="ui-progress">
                            <div class="ui-progress-bar" style="width:92%;background:#ef4444;"></div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Separator</th>
                <td>
                    <div style="font-size:13.5px;color:#374151;margin-bottom:8px;">Above separator</div>
                    <div class="ui-separator"></div>
                    <div style="font-size:13.5px;color:#374151;margin-top:8px;">Below separator</div>
                </td>
            </tr>
            <tr>
                <th>Skeleton loader</th>
                <td class="ui-demo-col" style="max-width:300px;">
                    <div class="ui-skeleton" style="height:14px;width:60%;"></div>
                    <div class="ui-skeleton" style="height:14px;width:85%;"></div>
                    <div class="ui-skeleton" style="height:14px;width:45%;"></div>
                </td>
            </tr>
            <tr>
                <th>Table</th>
                <td>
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Last sync</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>iiko Cloud API</td>
                                <td><span class="ui-badge ui-badge-green">Active</span></td>
                                <td style="color:#6b7280;">2 min ago</td>
                            </tr>
                            <tr>
                                <td>Menu sync</td>
                                <td><span class="ui-badge ui-badge-yellow">Pending</span></td>
                                <td style="color:#6b7280;">15 min ago</td>
                            </tr>
                            <tr>
                                <td>Order webhook</td>
                                <td><span class="ui-badge ui-badge-red">Error</span></td>
                                <td style="color:#6b7280;">1 hr ago</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
        <?php
    }

    private function renderActionBar(): void
    {
        ?>
        <p style="font-size:13px;color:#6b7280;margin:0 0 16px;">The sticky action bar appears at the bottom of the content area:</p>
        <div class="wp-field-shell__action-bar" style="position:relative;bottom:auto;margin:0;">
            <button class="button button-primary wp-field-shell__action-bar-save" type="button">Save Changes</button>
            <button class="button" type="button">Reset to Defaults</button>
            <span style="margin-left:auto;font-size:12.5px;color:#9ca3af;">Last saved: just now</span>
        </div>
        <?php
    }
}

new WP_Field_UI_Demo;
