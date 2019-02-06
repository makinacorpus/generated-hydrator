<?php

namespace GeneratedHydrator\Strategy;

/**
 * Uses the user class name suffixed using "Hydrator".
 *
 * @author Pierre Rineau <pierre.rineau@processus.org>
 * @license MIT
 */
final class ClassNamingStrategy implements NamingStrategy
{
    /**
     * {@inheritdoc}
     */
    public function generateClassName($userClassName, $generatedClassNamespace)
    {
        if (false !== ($pos = \strrpos($userClassName, '\\'))) {
            $userClassName = \substr($userClassName, $pos + 1);
        }

        return \ltrim($generatedClassNamespace."\\".$userClassName."Hydrator", '\\');
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilename($realClassName, $generatedClassesTargetDir, $namespacePrefix = null)
    {
        $realClassName = \trim($realClassName, '\\');

        if ($namespacePrefix) {
            $namespacePrefix = \trim($namespacePrefix, '\\').'\\';
            $prefixLength = \strlen($namespacePrefix);
            if (\substr($realClassName, 0, $prefixLength) === $namespacePrefix) {
                $realClassName = \substr($realClassName, $prefixLength);
            }
        }

        return \rtrim($generatedClassesTargetDir, '//').'/'.\str_replace("\\", "/", $realClassName).'.php';
    }
}
