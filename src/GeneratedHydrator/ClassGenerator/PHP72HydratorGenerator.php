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
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Pierre Rineau <pierre.rineau@makina-corpus.com>
 * @license MIT
 */
class PHP72HydratorGenerator implements HydratorGeneratorInterface
{
    /**
     * Generates an hydrator class
     *
     *
     * @return string
     */
    public function generate(\ReflectionClass $originalClass, string $realClassName): string
    {
        if ($position = \strrpos($realClassName, '\\')) {
            $namespace = \substr($realClassName, 0, $position);
            $className = \substr($realClassName, $position + 1);
        } else {
            $namespace = null;
            $className = $realClassName;
        }

        $originalClassName = $originalClass->name;
        if ($position = \strrpos($originalClassName, '\\')) {
            $hydratedNamespace = \substr($originalClassName, 0, $position);
            $hydratedClassName = \substr($originalClassName, $position + 1);
        } else {
            $hydratedNamespace = null;
            $hydratedClassName = $originalClassName;
        }

        if ($namespace !== $hydratedNamespace) {
            $use = "\nuse {$originalClassName};\n";
        } else {
            $use = '';
        }

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

        $doUseConstructor = true;
        if ($method = $originalClass->getConstructor()) {
            if (!$method->isPublic()) {
                /** @var \ReflectionParameter $parameter */
                foreach ($method->getParameters() as $parameter) {
                    if (!$parameter->isOptional()) {
                        $doUseConstructor = false;
                    }
                }
            } else {
                $doUseConstructor = false;
            }
        } else {
            $doUseConstructor = false;
        }

        $doUseConstructorValue = $doUseConstructor ? "true" : "false";

        return <<<EOT
<?php

declare(strict_types=1);

namespace {$namespace};
{$use}
/**
 * This is a generated hydrator for the {$originalClassName} class
 */
final class {$className}
{
    private static \$useConstructor = {$doUseConstructorValue};
    private static \$reflection;

{$this->createStaticProperties($visiblePropertyMap, $hiddenPropertyMap, $className)}

    /**
     * Hydrate given object instance with given data
     */
    public static function hydrate(array \$data, {$hydratedClassName} \$object): {$hydratedClassName}
    {
{$this->createHydrateMethod($visiblePropertyMap, $hiddenPropertyMap, $className)}
    }

    /**
     * Extract object data as a key-value array whose keys are properties names
     */
    public static function extract({$hydratedClassName} \$object): array
    {
{$this->createExtractMethod($visiblePropertyMap, $hiddenPropertyMap, $className)}
    }

    /**
     * Create a new instance and hydate it with given data
     */
    public static function create(array \$data): {$hydratedClassName}
    {
        if (self::\$useConstructor) {
            \$object = new {$hydratedClassName}();
        } else {
            \$object = (self::\$reflection ?? (
                self::\$reflection = new \ReflectionClass({$hydratedClassName}::class))
            )->newInstanceWithoutConstructor();
        }

        return self::hydrate(\$data, \$object);
    }
}

{$this->createCallbackInitialization($visiblePropertyMap, $hiddenPropertyMap, $className)}
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
    private function findAllInstanceProperties(\ReflectionClass $class = null): array
    {
        if (!$class) {
            return [];
        }

        return \array_values(\array_merge(
            $this->findAllInstanceProperties($class->getParentClass() ?: null), // of course PHP is shit.
            \array_values(\array_filter(
                $class->getProperties(),
                function (\ReflectionProperty $property): bool {
                    return ! $property->isStatic();
                }
            ))
        ));
    }

    /**
     * Dump lines with indentation
     */
    private function dumpLines(array $lines, int $indent = 4): string
    {
        return \trim(\implode("\n", \array_map(
            static function ($line) use ($indent) {
                return \str_repeat(" ", $indent).$line;
            }, $lines
        )), "\n");
    }

    /**
     * Generate code for the hydrator constructor
     */
    private function createStaticProperties(
        array $visiblePropertyMap,
        array $hiddenPropertyMap,
        string $hydratorClassName
    ): string {
        $content = [];

        for ($i = 0; $i < count($hiddenPropertyMap); $i++) {
            $content[] = "public static \$hydrate".$i.";";
            $content[] = "public static \$extract".$i.";";
        }

        return $this->dumpLines($content, 4);
    }

    /**
     * Generate code for the hydrator constructor
     */
    private function createCallbackInitialization(
        array $visiblePropertyMap,
        array $hiddenPropertyMap,
        string $hydratorClassName
    ): string {
        $content = [];

        // Create a set of closures that will be called to hydrate the object.
        // Array of closures in a naturally indexed array, ordered, which will
        // then be called in order in the hydrate() and extract() methods.
        $i = 0;
        foreach ($hiddenPropertyMap as $className => $propertyNames) {
            // Hydrate closures
            $content[] = $hydratorClassName."::\$hydrate".$i." = \\Closure::bind(static function (\$object, \$values) {";
            foreach ($propertyNames as $propertyName) {
                $content[] = "    if (isset(\$values['" . $propertyName . "']) || \$object->" . $propertyName . " !== null && \\array_key_exists('" . $propertyName . "', \$values)) {";
                $content[] = "        \$object->" . $propertyName . " = \$values['" . $propertyName . "'];";
                $content[] = "    }";
            }
            $content[] = '}, null, ' . \var_export($className, true) . ');' . "\n";

            // Extract closures
            $content[] = $hydratorClassName."::\$extract".$i." = \\Closure::bind(static function (\$object, &\$values) {";
            foreach ($propertyNames as $propertyName) {
                $content[] = "    \$values['" . $propertyName . "'] = \$object->" . $propertyName . ";";
            }
            $content[] = '}, null, ' . \var_export($className, true) . ');' . "\n";
            $i++;
        }

        return $this->dumpLines($content, 0);
    }

    /**
     * Generate code for the hydrate method
     */
    private function createHydrateMethod(
        array $visiblePropertyMap,
        array $hiddenPropertyMap,
        string $hydratorClassName
    ): string {
        $content = [];

        foreach ($visiblePropertyMap as $propertyName) {
            $content[] = "if (isset(\$data['" . $propertyName . "']) || \$object->" . $propertyName . " !== null && \\array_key_exists('" . $propertyName . "', \$data)) {";
            $content[] = "    \$object->" . $propertyName . " = \$data['" . $propertyName . "'];";
            $content[] = "}";
        }

        for ($i = 0; $i < count($hiddenPropertyMap); $i++) {
            $content[] = "self::\$hydrate".$i."->__invoke(\$object, \$data);";
        }

        $content[] = "return \$object;";

        return $this->dumpLines($content, 8);
    }

    /**
     * Generate code for the extract method
     */
    private function createExtractMethod(
        array $visiblePropertyMap,
        array $hiddenPropertyMap,
        string $hydratorClassName
    ): string {
        $content = [];

        $content[] = "\$ret = [];";
        foreach ($visiblePropertyMap as $propertyName) {
            $content[] = "\$ret['" . $propertyName . "'] = \$object->" . $propertyName . ";";
        }

        for ($i = 0; $i < count($hiddenPropertyMap); $i++) {
            $content[] = "self::\$extract".$i."->__invoke(\$object, \$ret);";
        }

        $content[] = "return \$ret;";

        return $this->dumpLines($content, 8);
    }
}
