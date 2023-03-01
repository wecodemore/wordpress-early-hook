<?php

/*
 * This file is part of the "wordpress-early-hook" package.
 *
 * Copyright (C)  Giuseppe Mazzapica and contributors.
 * See LICENSE file.
 */

declare(strict_types=1);

namespace WeCodeMore\WpEarlyHook
{
    if (defined(__NAMESPACE__ . '\\EARLY_HOOK_VERSION')) {
        return;
    }

    const EARLY_HOOK_VERSION = '1.0.0';

    /**
     * @param string $type
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $acceptedArgs
     * @return void
     *
     * @internal
     */
    function earlyAddHook(
        string $type,
        string $hook,
        callable $callback,
        int $priority,
        int $acceptedArgs
    ): void {

        /** @var array<string, bool> $exists */
        static $exists = [];
        $isFilter = ($type === 'filter');
        $isFilter or $type = 'action';

        $wpFuncExists = $exists[$type] ?? null;
        if ($wpFuncExists === null) {
            $wpFuncExists = function_exists($isFilter ? 'add_filter' : 'add_action');
            $wpFuncExists and $exists[$type] = true;
        }

        if ($wpFuncExists || defined('ABSPATH')) {
            if (!$wpFuncExists) {
                require_once ABSPATH . 'wp-includes/plugin.php';
            }

            $isFilter
                ? add_filter($hook, $callback, $priority)
                : add_action($hook, $callback, $priority);

            return;
        }
        /**
         * If here, this function is called very early, probably _too_ early,
         * before ABSPATH is defined.
         * Only option we have is to "manually" write in global `$wp_filter` array.
         */
        global $wp_filter;
        is_array($wp_filter) or $wp_filter = [];
        is_array($wp_filter[$hook] ?? null) or $wp_filter[$hook] = [];
        /** @psalm-suppress MixedArrayAssignment */
        is_array($wp_filter[$hook][$priority] ?? null) or $wp_filter[$hook][$priority] = [];
        /** @psalm-suppress MixedArrayAssignment */
        $wp_filter[$hook][$priority][] = [
            'function' => $callback,
            'accepted_args' => $acceptedArgs
        ];
    }
}

namespace WeCodeMore {

    /**
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $acceptedArgs
     * @return void
     */
    function earlyAddFilter(
        string $hook,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1
    ): void {

        WpEarlyHook\earlyAddHook('filter', $hook, $callback, $priority, $acceptedArgs);
    }

    /**
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $acceptedArgs
     * @return void
     */
    function earlyAddAction(
        string $hook,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1
    ): void {

        WpEarlyHook\earlyAddHook('action', $hook, $callback, $priority, $acceptedArgs);
    }
}
