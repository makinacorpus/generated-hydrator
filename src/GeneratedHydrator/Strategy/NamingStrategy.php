<?php

namespace GeneratedHydrator\Strategy;

/**
 * Class naming strategy.
 *
 * @author Pierre Rineau <pierre.rineau@processus.org>
 * @license MIT
 */
interface NamingStrategy
{
    /**
     * Generate class name
     *
     * @param string $userClassName
     * @param string $generatedClassNamespace
     *
     * @return string
     *   Fully qualified namespace
     */
    public function generateClassName($userClassName, $generatedClassNamespace);

    /**
     * Generate full filename in which the file will be saved
     *
     * @param string $realClassName
     * @param string $generatedClassesTargetDir
     * @param ?string $namespacePrefix
     *   When working in a PSR-4 namespace, this is the namespace prefix
     *
     * @return string
     */
    public function generateFilename($realClassName, $generatedClassesTargetDir, $namespacePrefix = null);
}
