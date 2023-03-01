# WP Early Hook

Small library to safely add WordPress hooks before WordPress is loaded.

---

## What

This package provides two functions:

- `WeCodeMore\earlyAddAction()`
- `WeCodeMore\earlyAddFilter()`

Having the same signature of WP's `add_action` and `add_filter`, but they are loaded with
Composer autoload, and can add hooks _before_ WordPress is actually loaded.

## Why

When we write code for WordPress with Composer support, we might be tempted to use Composer autoload
file functionality to add WordPress action and filters.

When the code we're writing is part of a [WP Starter](https://github.com/wecodemore/wpstarter) 
project that works well, because WP Starter loads the WP plugin API before loading the 
Composer autoload.

But if we aim to release the code as "standalone" we don't know where it'll be used and so it might
be possible that when the Composer autoload file is required the WP plugin API is not loaded yet,
and so calling `add_action`/`add_filter` will result in a fatal error.

## How

The two functions of this package first check if the actual WP functions are available, if so, it
used them.

If WP functions are not available, it checks if `ABSPATH` constant is defined. If so, it uses it 
to load the `wp-includes/plugin.php` file that defines the hooks API.
Since WP 4.7, that file is independent of the rest of WordPress and can be loaded before WP is 
loaded. After the file is loaded, this package's functions can call the WP functions.

In the case not even `ABSPATH` constant is defined, this package's functions resort to fill the
`$wp_filter` global variable, which WordPress will "reconciliate" when loaded using
[`WP_Hook::build_preinitialized_hooks`](https://github.com/WordPress/WordPress/blob/6.1/wp-includes/class-wp-hook.php#L408).

## System Requirements

 - PHP 7.1+
 - WordPress 4.7+ (Only tested on 5.9+ for PHP 8.1+)

## Installation

Via Composer, package name is `wecodemore/wordpress-early-hook`.

## License

MIT. See [LICENSE](LICENSE) file.

## Who's Behind

I'm Giuseppe, I deal with PHP since 2005. For questions, rants or chat ping me on Mastodon ([@gmazzap@phpc.social](https://phpc.social/@gmazzap)). Well, it's possible I'll ignore rants.
