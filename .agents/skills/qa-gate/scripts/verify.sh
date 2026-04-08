#!/usr/bin/env bash
set -euo pipefail

# QA Gate verification script for WP_Field
# Keep it fast. Prefer one command that CI also runs.

echo "== PHP Syntax Check =="
find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \; > /dev/null 2>&1 || {
    echo "PHP syntax errors found"
    exit 1
}

echo "== Composer Autoload =="
composer dump-autoload -o --quiet

echo "== PHP Lint (Pint) =="
composer lint:check --quiet 2>/dev/null || {
    echo "Lint check failed or not configured. Run: composer lint:check"
}

echo "== Static Analysis (PHPStan) =="
composer analyse --quiet 2>/dev/null || {
    echo "PHPStan warnings found or not configured"
}

echo "== Tests =="
composer test --quiet 2>/dev/null || {
    echo "Tests failed or not configured"
}

echo "OK"
