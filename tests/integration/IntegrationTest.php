<?php

/*
 * This file is part of the "wordpress-early-hook" package.
 *
 * Copyright (C)  Giuseppe Mazzapica and contributors.
 * See LICENSE file.
 */

declare(strict_types=1);

namespace WeCodeMore\Tests;

use function WeCodeMore\earlyAddAction;
use function WeCodeMore\earlyAddFilter;

/**
 * @runTestsInSeparateProcesses
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class IntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function addHooksBeforeAbspath(): void
    {
        [$filter, $action, $hookObj] = $this->createCallbacks();

        earlyAddFilter('wecodemore.test-filter', $filter);
        earlyAddFilter('wecodemore.test-filter', [$hookObj, 'filter']);
        earlyAddAction('wecodemore.test-action', $action);
        earlyAddAction('wecodemore.test-action', [$hookObj, 'action']);

        $this->loadWpApi();

        $this->doAssertions($filter, $action, $hookObj);
    }

    /**
     * @test
     */
    public function addHooksAfterAbspathBeforeApi(): void
    {
        [$filter, $action, $hookObj] = $this->createCallbacks();

        $this->defineAbspath();

        earlyAddFilter('wecodemore.test-filter', $filter);
        earlyAddFilter('wecodemore.test-filter', [$hookObj, 'filter']);
        earlyAddAction('wecodemore.test-action', $action);
        earlyAddAction('wecodemore.test-action', [$hookObj, 'action']);

        $this->loadWpApi();

        $this->doAssertions($filter, $action, $hookObj);
    }

    /**
     * @test
     */
    public function addHooksAfterApi(): void
    {
        [$filter, $action, $hookObj] = $this->createCallbacks();

        $this->loadWpApi();

        earlyAddFilter('wecodemore.test-filter', $filter);
        earlyAddFilter('wecodemore.test-filter', [$hookObj, 'filter']);
        earlyAddAction('wecodemore.test-action', $action);
        earlyAddAction('wecodemore.test-action', [$hookObj, 'action']);

        $this->doAssertions($filter, $action, $hookObj);
    }

    /**
     * @return array{callable(int):int, callable(string):void, object}
     */
    private function createCallbacks(): array
    {
        static::assertFalse(defined('ABSPATH'));
        static::assertFalse(function_exists('add_action'));
        static::assertFalse(function_exists('add_filter'));

        $filter = static function (int $count): int {
            return $count + 1;
        };

        $action = static function (string $str): void {
            print $str;
        };

        $hookObj = new class ($filter, $action)
        {
            /** @var callable */
            private $filter;
            /** @var callable */
            private $action;

            /**
             * @param callable $filter
             * @param callable $action
             */
            public function __construct(callable $filter, callable $action)
            {
                $this->filter = $filter;
                $this->action = $action;
            }

            /**
             * @param mixed $args
             * @return mixed
             */
            public function filter(...$args)
            {
                return ($this->filter)(...$args);
            }

            /**
             * @param mixed $args
             * @return void
             */
            public function action(...$args): void
            {
                ($this->action)(...$args);
            }
        };

        return [$filter, $action, $hookObj];
    }

    /**
     * @param callable $filter
     * @param callable $action
     * @param object $hookObj
     * @return void
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    private function doAssertions(callable $filter, callable $action, object $hookObj): void
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        static::assertSame(
            5,
            apply_filters(
                'wecodemore.test-filter',
                apply_filters('wecodemore.test-filter', 1)
            )
        );

        ob_start();
        do_action('wecodemore.test-action', 'TEST_');
        static::assertSame('TEST_TEST_', trim((string) ob_get_clean()));

        static::assertSame(10, has_filter('wecodemore.test-filter', $filter));
        static::assertSame(10, has_filter('wecodemore.test-action', $action));
        static::assertSame(10, has_filter('wecodemore.test-filter', [$hookObj, 'filter']));
        static::assertSame(10, has_action('wecodemore.test-action', [$hookObj, 'action']));

        remove_filter('wecodemore.test-filter', $filter);
        remove_action('wecodemore.test-action', $action);

        static::assertSame(
            3,
            apply_filters(
                'wecodemore.test-filter',
                apply_filters('wecodemore.test-filter', 1)
            )
        );

        ob_start();
        do_action('wecodemore.test-action', 'TEST_');
        static::assertSame('TEST_', trim((string) ob_get_clean()));

        static::assertTrue(has_filter('wecodemore.test-filter'));
        static::assertTrue(has_filter('wecodemore.test-action'));
        static::assertSame(10, has_filter('wecodemore.test-filter', [$hookObj, 'filter']));
        static::assertSame(10, has_action('wecodemore.test-action', [$hookObj, 'action']));

        remove_filter('wecodemore.test-filter', [$hookObj, 'filter']);
        remove_action('wecodemore.test-action', [$hookObj, 'action']);

        static::assertSame(1, apply_filters('wecodemore.test-filter', 1));
        ob_start();
        do_action('wecodemore.test-action', 'TEST');
        static::assertSame('', trim((string) ob_get_clean()));

        static::assertFalse(has_filter('wecodemore.test-filter'));
        static::assertFalse(has_filter('wecodemore.test-action'));
    }
}
