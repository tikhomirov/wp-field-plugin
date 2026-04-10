<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$distDir = $root.'/dist';
$pluginSlug = 'wp-field-vanilla';
$pluginDir = $distDir.'/'.$pluginSlug;
$zipPath = $distDir.'/'.$pluginSlug.'.zip';
$version = detectVersion($root.'/wp-field.php') ?? '4.0.0';

$filesToCopy = [
    'vanilla/bootstrap.php',
    'vanilla/WP_Field.php',
    'vanilla/assets/css/wp-field.css',
    'vanilla/assets/js/wp-field.js',
    'lang/wp-field.pot',
    'lang/wp-field-ru_RU.po',
    'lang/wp-field-ru_RU.mo',
    'lang/wp-field-ru_RU.l10n.php',
];

if (! is_dir($distDir) && ! mkdir($distDir, 0777, true) && ! is_dir($distDir)) {
    throw new RuntimeException("Failed to create dist directory: {$distDir}");
}

recreateDirectory($pluginDir);

foreach ($filesToCopy as $relativePath) {
    copyFile($root, $pluginDir, $relativePath);
}

writeStandaloneBootstrap($pluginDir.'/'.$pluginSlug.'.php', $version);
writeReadme($pluginDir.'/README.txt', $version);
createZipArchive($pluginDir, $zipPath, $pluginSlug);

fwrite(STDOUT, "Standalone vanilla plugin built successfully.\n");
fwrite(STDOUT, "Directory: {$pluginDir}\n");
fwrite(STDOUT, "Archive:   {$zipPath}\n");
fwrite(STDOUT, "Included files:\n");

foreach (["{$pluginSlug}.php", 'README.txt', ...$filesToCopy] as $relativePath) {
    fwrite(STDOUT, " - {$relativePath}\n");
}

function detectVersion(string $pluginFile): ?string
{
    $contents = @file_get_contents($pluginFile);
    if ($contents === false) {
        return null;
    }

    if (! preg_match('/^ \* Version:\s+(.+)$/m', $contents, $matches)) {
        return null;
    }

    return trim($matches[1]);
}

function recreateDirectory(string $directory): void
{
    if (is_dir($directory)) {
        deleteDirectory($directory);
    }

    if (! mkdir($directory, 0777, true) && ! is_dir($directory)) {
        throw new RuntimeException("Failed to create directory: {$directory}");
    }
}

function deleteDirectory(string $directory): void
{
    $items = scandir($directory);
    if ($items === false) {
        throw new RuntimeException("Failed to read directory: {$directory}");
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory.'/'.$item;

        if (is_dir($path) && ! is_link($path)) {
            deleteDirectory($path);

            continue;
        }

        if (! unlink($path)) {
            throw new RuntimeException("Failed to delete file: {$path}");
        }
    }

    if (! rmdir($directory)) {
        throw new RuntimeException("Failed to remove directory: {$directory}");
    }
}

function copyFile(string $root, string $pluginDir, string $relativePath): void
{
    $source = $root.'/'.$relativePath;
    $target = $pluginDir.'/'.$relativePath;
    $targetDirectory = dirname($target);

    if (! is_file($source)) {
        throw new RuntimeException("Required file not found: {$source}");
    }

    if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0777, true) && ! is_dir($targetDirectory)) {
        throw new RuntimeException("Failed to create directory: {$targetDirectory}");
    }

    if (! copy($source, $target)) {
        throw new RuntimeException("Failed to copy file: {$relativePath}");
    }
}

function writeStandaloneBootstrap(string $targetFile, string $version): void
{
    $bootstrap = <<<PHP
<?php

/**
 * Plugin Name: WP_Field Vanilla
 * Plugin URI:  https://github.com/rwsite/wp-field-plugin
 * Description: Standalone vanilla runtime build of WP_Field for WordPress admin fields.
 * Version:     {$version}
 * Requires at least: 6.0
 * Tested up to: 7.0
 * Requires PHP: 8.3
 * Author:      Aleksei Tikhomirov
 * Author URI:  https://rwsite.ru
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-field
 * Domain Path: /lang/
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

if (! defined('WP_FIELD_PLUGIN_FILE')) {
    define('WP_FIELD_PLUGIN_FILE', __FILE__);
}
if (! defined('WP_FIELD_PLUGIN_DIR')) {
    define('WP_FIELD_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (! defined('WP_FIELD_PLUGIN_URL')) {
    define('WP_FIELD_PLUGIN_URL', plugin_dir_url(__FILE__));
}

add_action('plugins_loaded', static function (): void {
    load_plugin_textdomain('wp-field', false, dirname(plugin_basename(__FILE__)).'/lang/');
});

require_once __DIR__.'/vanilla/bootstrap.php';
PHP;

    if (file_put_contents($targetFile, $bootstrap.PHP_EOL) === false) {
        throw new RuntimeException("Failed to write bootstrap file: {$targetFile}");
    }
}

function writeReadme(string $targetFile, string $version): void
{
    $readme = <<<TXT
=== WP_Field Vanilla ===
Contributors: rwsite
Tags: forms, fields, admin, legacy
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.3
Stable tag: {$version}
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Standalone vanilla runtime build of WP_Field for WordPress admin fields.

== Description ==
This package is generated from the vanilla runtime and includes only the minimal runtime required for the standalone legacy plugin. It is intended for publishing as a separate WordPress plugin archive or directory.

Included files:
- wp-field-vanilla.php
- README.txt
- vanilla/bootstrap.php
- vanilla/WP_Field.php
- vanilla/assets/css/wp-field.css
- vanilla/assets/js/wp-field.js
- lang/wp-field.pot
- lang/wp-field-ru_RU.po
- lang/wp-field-ru_RU.mo
- lang/wp-field-ru_RU.l10n.php

The generated plugin loads the wp-field text domain from the lang/ directory and is ready to upload through the WordPress Plugins screen.

== Installation ==
1. Upload the zip archive through Plugins → Add New → Upload Plugin.
2. Or copy the extracted folder into wp-content/plugins/.
3. Activate the plugin in WordPress.

== Changelog ==
= {$version} =
* Initial standalone vanilla export.
TXT;

    if (file_put_contents($targetFile, $readme.PHP_EOL) === false) {
        throw new RuntimeException("Failed to write README.txt file: {$targetFile}");
    }
}

function createZipArchive(string $pluginDir, string $zipPath, string $pluginSlug): void
{
    if (file_exists($zipPath) && ! unlink($zipPath)) {
        throw new RuntimeException("Failed to remove existing archive: {$zipPath}");
    }

    $zip = new ZipArchive;
    $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    if ($result !== true) {
        throw new RuntimeException("Failed to create zip archive: {$zipPath}");
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($pluginDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
    );

    foreach ($iterator as $item) {
        $filePath = $item->getPathname();
        $relativePath = substr($filePath, strlen($pluginDir) + 1);
        $archivePath = $pluginSlug.'/'.$relativePath;

        if ($item->isDir()) {
            $zip->addEmptyDir($archivePath);

            continue;
        }

        if (! $zip->addFile($filePath, $archivePath)) {
            $zip->close();
            throw new RuntimeException("Failed to add file to archive: {$archivePath}");
        }
    }

    $zip->close();
}
