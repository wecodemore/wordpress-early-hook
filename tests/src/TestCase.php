<?php

/*
 * This file is part of the "wordpress-early-hook" package.
 *
 * Copyright (C)  Giuseppe Mazzapica and contributors.
 * See LICENSE file.
 */

declare(strict_types=1);

namespace WeCodeMore\Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    protected function defineAbspath(): void
    {
        if (!defined('ABSPATH')) {
            define('ABSPATH', (getenv('WP_DIR') ?: '') . '/');
        }
    }

    /**
     * @return void
     */
    protected function loadWpApi(): void
    {
        $this->defineAbspath();
        /** @psalm-suppress UndefinedConstant */
        require_once ABSPATH . 'wp-includes/plugin.php';
    }
}
