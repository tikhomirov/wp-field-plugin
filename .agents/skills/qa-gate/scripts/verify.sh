#!/usr/bin/env bash
set -euo pipefail

# QA Gate verification script for WP_Field
# Keep it fast. Prefer one command that CI also runs.

echo "== PHP Syntax Check =="
find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \; > /dev/null 2>&1 || {
    echo "PHP syntax errors found"
    exit 1
}

echo "== PHP Lint (Pint) =="
composer lint:check --quiet

echo "== Static Analysis (PHPStan) =="
composer analyse --quiet

echo "== Tests =="
composer test --quiet

echo "== Frontend Lint =="
npm run lint --silent

echo "OK"
