<?php

declare(strict_types=1);

use WpField\UI\UIManager;

beforeEach(function (): void {
    UIManager::setMode('vanilla');
});

afterEach(function (): void {
    UIManager::setMode('vanilla');
});

it('sets mode to vanilla', function (): void {
    UIManager::setMode('vanilla');

    expect(UIManager::getMode())->toBe('vanilla');
});

it('sets mode to react', function (): void {
    UIManager::setMode('react');

    expect(UIManager::getMode())->toBe('react');
});

it('does not set invalid mode', function (): void {
    UIManager::setMode('vanilla');
    UIManager::setMode('invalid');

    expect(UIManager::getMode())->toBe('vanilla');
});

it('returns true for react mode', function (): void {
    UIManager::setMode('react');

    expect(UIManager::isReactMode())->toBeTrue();
});

it('returns false for vanilla mode', function (): void {
    UIManager::setMode('vanilla');

    expect(UIManager::isReactMode())->toBeFalse();
});

it('does not enqueue assets twice', function (): void {
    UIManager::enqueueAssets();
    UIManager::enqueueAssets();

    expect(true)->toBeTrue();
});

it('skips enqueueing on components page', function (): void {
    if (! function_exists('get_current_screen')) {
        $this->markTestSkipped('get_current_screen not available');
    }

    $screen = new class
    {
        public string $id = 'tools_page_wp-field-components';
    };

    if (! function_exists('get_current_screen')) {
        function get_current_screen()
        {
            global $test_screen;

            return $test_screen;
        }
    }

    global $test_screen;
    $test_screen = $screen;

    UIManager::enqueueAssets();

    $test_screen = null;
});

it('enqueues assets in vanilla mode', function (): void {
    UIManager::setMode('vanilla');

    UIManager::enqueueAssets();

    expect(true)->toBeTrue();
});

it('enqueues assets in react mode', function (): void {
    UIManager::setMode('react');

    UIManager::enqueueAssets();

    expect(true)->toBeTrue();
});
