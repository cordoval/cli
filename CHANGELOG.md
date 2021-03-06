Changelog
=========

* 1.0.0-beta3 (2015-03-19)

 * switched to webmozart/console package
 * moved command code to command handler classes
 * added tests for command handler classes
 * injected Puli's event dispatcher into the console application to let plugins
   extend the console configuration
 * renamed `puli bind` option `--duplicate` to `--overridden`
 * added "factory" target to `puli build` command
 * added `puli plugin` command

* 1.0.0-beta2 (2015-01-27)

 * updated to work with Puli components in version 1.0.0-beta2

* 1.0.0-beta (2015-01-12)

 * moved code from `Puli\Cli\Console` to `Puli\Cli`
 * added `puli map` command
 * added `puli type` command
 * added `puli type define` command
 * added `puli type remove` command
 * added `puli bind` command
 * added `puli package` command
 * added `puli package install` command
 * added `puli package remove` command
 * added `puli package clean` command
 * added `puli config` command
 * added `puli tree` command
 * added `puli find` command
 * improved `puli ls` command
 * renamed `puli dump` to `puli build`

* 1.0.0-alpha1 (2014-12-03)

 * first alpha release
