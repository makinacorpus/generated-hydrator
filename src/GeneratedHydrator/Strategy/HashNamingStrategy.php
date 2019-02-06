<?php

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
    public function generateClassName($userClassName, $generatedClassNamespace)
    {
        return \ltrim($generatedClassNamespace."\\G".\sha1($userClassName), '\\');
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilename($realClassName, $generatedClassesTargetDir, $namespacePrefix = null)
    {
        return \rtrim($generatedClassesTargetDir, '//').'/'.\str_replace("\\", "_", $realClassName).'.php';
    }
}
