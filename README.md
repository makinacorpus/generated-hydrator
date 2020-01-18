# Generated Hydrator

GeneratedHydrator is a library about high performance transition of data from
arrays to objects and from objects to arrays.

This project was forked from
[ocramius/generated-hydrator](https://github.com/Ocramius/GeneratedHydrator)
all credits goes to Marco Pivetta, original author of this library.
This project will remain API compatible with it.

# Status

**Time has passed, and PHP 5.6 is not supported anywhere anymore, as a consequence**
**this package is not supported anymore**.

Please use [ocramius/generated-hydrator](https://github.com/Ocramius/GeneratedHydrator)
instead, and if you wish to integrate it with Symfony, please consider using
[makinacorpus/generated-hydrator-bundle](https://github.com/makinacorpus/generated-hydrator-bundle).

This package may if necessary receive some updates, for bugfixes and
security fixes.

# Description

Differences are:

 * it does not use nikic/php-parser dependency, which causes us serious
   dependency hell on many projects,

 * it does not use ocramius/code-generator-utils for the very same reason,

 * code is much more shorted and faster for hydrator class generation, and
   simpler to maintain in time,

 * we needed at some point PHP 5.6 compatibility, this package temporarily
   restores it, and will dropped again in late 2019.

Apart from those few differences, generated hydrator code remain the same
and perform equivalently.

## Version

 * **For PHP >= 7.4 please use `ocramius/generated-hydrator` instead**.
 * 3.0 (master branch) is PHP 7.2 and 7.3 compatible.
 * 2.x (2.0 branch is for PHP 5.6 and compatible and is now deprecated and unmaintained.
 * 1.x is deprecated and unmaintained.
