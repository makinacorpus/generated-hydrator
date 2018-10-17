<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace GeneratedHydrator\ClassGenerator;

/**
 * Generator for highly performing {@see \Zend\Hydrator\HydratorInterface}
 * for objects
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Pierre Rineau <pierre.rineau@makina-corpus.com>
 * @license MIT
 */
class PHP5HydratorGenerator implements HydratorGeneratorInterface
{
    /**
     * Generates an hydrator class
     *
     *
     * @return string
     */
    public function generate(\ReflectionClass $originalClass, string $realClassName, string $originalClassName) : string
    {
        $position = \strrpos($realClassName, '\\');
        $namespace = \substr($realClassName, 0, $position);
        $className = \substr($realClassName, $position + 1);

        $visiblePropertyMap = [];
        $hiddenPropertyMap = [];

        foreach ($this->findAllInstanceProperties($originalClass) as $property) {
            $declaringClassName = $property->getDeclaringClass()->getName();

            if ($property->isPrivate() || $property->isProtected()) {
                $hiddenPropertyMap[$declaringClassName][] = $property->getName();
            } else {
                $visiblePropertyMap[] = $property->getName();
            }
        }

        return <<<EOT
<?php

namespace {$namespace};

use Zend\Hydrator\HydratorInterface;

/**
 * This is a generated hydrator for the {$originalClassName} class
 */
class {$className} implements HydratorInterface
{
    private \$hydrateCallbacks = [];
    private \$extractCallbacks = [];

    public function __construct()
    {
        {$this->createContructor($visiblePropertyMap, $hiddenPropertyMap)}
    }

    public function hydrate(array \$data, \$object)
    {
        {$this->createHydrateMethod($visiblePropertyMap, $hiddenPropertyMap)}
    }

    public function extract(\$object)
    {
        {$this->createExtractMethod($visiblePropertyMap, $hiddenPropertyMap)}
    }
}
EOT;
    }

    /**
     * Find all class properties recursively using class hierarchy without
     * removing name redefinitions
     *
     * @param \ReflectionClass $class
     *
     * @return \ReflectionProperty[]
     */
    private function findAllInstanceProperties(\ReflectionClass $class = null)
    {
        if (! $class) {
            return [];
        }

        return \array_values(\array_merge(
            $this->findAllInstanceProperties($class->getParentClass() ?: null), // of course PHP is shit.
            \array_values(\array_filter(
                $class->getProperties(),
                function (\ReflectionProperty $property) : bool {
                    return ! $property->isStatic();
                }
            ))
        ));
    }

    /**
     * Generate code for the hydrator constructor
     *
     * @param string[][] $visiblePropertyMap
     * @param string[] $hiddenPropertyMap
     *
     * @return string
     */
    private function createContructor(array $visiblePropertyMap, array $hiddenPropertyMap) : string
    {
        $content = [];

        // Create a set of closures that will be called to hydrate the object.
        // Array of closures in a naturally indexed array, ordered, which will
        // then be called in order in the hydrate() and extract() methods.
        foreach ($hiddenPropertyMap as $className => $propertyNames) {
            // Hydrate closures
            $content[] = "\$this->hydrateCallbacks[] = \\Closure::bind(function (\$object, \$values) {";
            foreach ($propertyNames as $propertyName) {
                $content[] = "    if (isset(\$values['" . $propertyName . "'])) {";
                $content[] = "        \$object->" . $propertyName . " = \$values['" . $propertyName . "'];";
                $content[] = "    }";
            }
            $content[] = '}, null, ' . \var_export($className, true) . ');' . "\n";

            // Extract closures
            $content[] = "\$this->extractCallbacks[] = \\Closure::bind(function (\$object, &\$values) {";
            foreach ($propertyNames as $propertyName) {
                $content[] = "    \$values['" . $propertyName . "'] = \$object->" . $propertyName . ";";
            }
            $content[] = '}, null, ' . \var_export($className, true) . ');' . "\n";
        }

        return \implode("\n", \array_map(
            function ($line) {
                return "        " . $line;
            }, $content
        ));
    }

    /**
     * Generate code for the hydrate method
     *
     * @param string[][] $visiblePropertyMap
     * @param string[] $hiddenPropertyMap
     *
     * @return string
     */
    private function createHydrateMethod(array $visiblePropertyMap, array $hiddenPropertyMap) : string
    {
        $content = [];

        foreach ($visiblePropertyMap as $propertyName) {
            $content[] = "if (isset(\$data['" . $propertyName . "'])) {";
            $content[] = "    \$object->" . $propertyName . " = \$data['" . $propertyName . "'];";
            $content[] = "}";
        }
        $index = 0;
        foreach ($hiddenPropertyMap as $className => $propertyNames) {
            $content[] = "\$this->hydrateCallbacks[" . ($index++) . "]->__invoke(\$object, \$data);";
        }

        $content[] = "return \$object;";

        return \implode("\n", \array_map(
            function ($line) {
                return "        " . $line;
            }, $content
        ));
    }

    /**
     * Generate code for the extract method
     *
     * @param string[][] $visiblePropertyMap
     * @param string[] $hiddenPropertyMap
     *
     * @return string
     */
    private function createExtractMethod(array $visiblePropertyMap, array $hiddenPropertyMap) : string
    {
        $content = [];

        $content[] = "\$ret = array();";
        foreach ($visiblePropertyMap as $propertyName) {
            $content[] = "\$ret['" . $propertyName . "'] = \$object->" . $propertyName . ";";
        }
        $index = 0;
        foreach ($hiddenPropertyMap as $className => $propertyNames) {
            $content[] = "\$this->extractCallbacks[" . ($index++) . "]->__invoke(\$object, \$ret);";
        }

        $content[] = "return \$ret;";

        return \implode("\n", \array_map(
            function ($line) {
                return "        " . $line;
            }, $content
        ));
    }
}
