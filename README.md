# Generated Hydrator

GeneratedHydrator is a library about high performance transition of data from
arrays to objects and from objects to arrays.

This project is forked from https://github.com/Ocramius/GeneratedHydrator all
credits goes to Marco Pivetta, original author of this library. This project
will remain API compatible with it.

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

 * 2.0 branch is for php 5.6 and compatible with with ocramius/generated-hydrator until end of 2018
 * master as no releases yet, is php 7.2 only.
