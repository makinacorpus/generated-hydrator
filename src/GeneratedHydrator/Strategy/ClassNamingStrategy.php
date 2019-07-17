<?php

declare(strict_types=1);

namespace GeneratedHydrator\Strategy;

/**
 * Uses the user class name suffixed using "Hydrator".
 *
 * Warning: this implementation is subject to hydrator class name conflicts.
 * @todo Find a way.
 *
 * @author Pierre Rineau <pierre.rineau@processus.org>
 * @license MIT
 */
final class ClassNamingStrategy implements NamingStrategy
{
    /**
     * Get class name without namespace
     *
     * @param string $realClassName
     *
     * @return string
     */
    private function getRelativeClassName(string $qualifiedClassName): string
    {
        if (false !== ($pos = \strrpos($qualifiedClassName, '\\'))) {
            return \substr($qualifiedClassName, $pos + 1);
        }

        return $qualifiedClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function generateClassName(string $userClassName, string $generatedClassNamespace): string
    {
        $userClassName = $this->getRelativeClassName($userClassName);

        return \ltrim($generatedClassNamespace."\\".$userClassName."Hydrator", '\\');
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilename(string $realClassName, string $generatedClassesTargetDir, ?string $namespacePrefix = null): string
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
