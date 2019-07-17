<?php

declare(strict_types=1);

namespace GeneratedHydrator\Strategy;

/**
 * Default naming strategy, uses a SHA1 hash of the user class.
 *
 * @author Pierre Rineau <pierre.rineau@processus.org>
 * @license MIT
 */
final class HashNamingStrategy implements NamingStrategy
{
    /**
     * {@inheritdoc}
     */
    public function generateClassName(string $userClassName, string $generatedClassNamespace): string
    {
        return \ltrim($generatedClassNamespace."\\G".\sha1($userClassName), '\\');
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilename(string $realClassName, string $generatedClassesTargetDir, ?string $namespacePrefix = null): string
    {
        return \rtrim($generatedClassesTargetDir, '//').'/'.\str_replace("\\", "_", $realClassName).'.php';
    }
}
