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

namespace GeneratedHydratorTest\Functional;

use GeneratedHydrator\Configuration;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithMixedProperties;
use GeneratedHydratorTestAsset\ClassWithPrivateProperties;
use GeneratedHydratorTestAsset\ClassWithPrivatePropertiesAndParent;
use GeneratedHydratorTestAsset\ClassWithPrivatePropertiesAndParents;
use GeneratedHydratorTestAsset\ClassWithProtectedProperties;
use GeneratedHydratorTestAsset\ClassWithPublicProperties;
use GeneratedHydratorTestAsset\ClassWithStaticProperties;
use GeneratedHydratorTestAsset\EmptyClass;
use GeneratedHydratorTestAsset\HydratedObject;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class HydratorFunctionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getHydratorClasses
     *
     * @param object $instance
     */
    public function testHydrator($instance)
    {
        $reflection = new \ReflectionClass($instance);
        $initialData = [];
        $newData = [];

        $this->recursiveFindInitialData($reflection, $instance, $initialData, $newData);

        $generatedClass = $this->generateHydrator($instance);

        // Hydration and extraction don't guarantee ordering.
        ksort($initialData);
        ksort($newData);
        $extracted = $generatedClass::extract($instance);
        ksort($extracted);

        self::assertSame($initialData, $extracted);
        self::assertSame($instance, $generatedClass::hydrate($newData, $instance));

        // Same as upper applies
        $inspectionData = [];
        $this->recursiveFindInspectionData($reflection, $instance, $inspectionData);
        ksort($inspectionData);
        $extracted = $generatedClass::extract($instance);
        ksort($extracted);

        self::assertSame($inspectionData, $newData);
        self::assertSame($inspectionData, $extracted);

        // Same as upper still applies
        $newInstance = $generatedClass::create($newData);
        $extracted = $generatedClass::extract($newInstance);
        ksort($extracted);

        self::assertSame($newData, $extracted);
    }

    public function testHydratingNull()
    {
        $instance = new ClassWithPrivateProperties();

        self::assertSame('property0', $instance->getProperty0());

        $this->generateHydrator($instance)::hydrate(['property0' => null], $instance);

        self::assertSame(null, $instance->getProperty0());
    }

    /**
     * @return array
     */
    public function getHydratorClasses() : array
    {
        return [
            [new \stdClass()],
            [new EmptyClass()],
            [new HydratedObject()],
            [new BaseClass()],
            [new ClassWithPublicProperties()],
            [new ClassWithProtectedProperties()],
            [new ClassWithPrivateProperties()],
            [new ClassWithPrivatePropertiesAndParent()],
            [new ClassWithPrivatePropertiesAndParents()],
            [new ClassWithMixedProperties()],
            [new ClassWithStaticProperties()],
        ];
    }

    /**
     * Recursively populate the $initialData and $newData array browsing the
     * full class hierarchy tree
     *
     * Private properties from parent class that are hidden by children will be
     * dropped from the populated arrays
     *
     * @param \ReflectionClass $class
     * @param object $instance
     * @param array $initialData
     * @param array $newData
     */
    private function recursiveFindInitialData(\ReflectionClass $class, $instance, array &$initialData, array &$newData)
    {
        if ($parentClass = $class->getParentClass()) {
            $this->recursiveFindInitialData($parentClass, $instance, $initialData, $newData);
        }

        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();

            $property->setAccessible(true);
            $initialData[$propertyName] = $property->getValue($instance);
            $newData[$propertyName]     = $property->getName() . '__new__value';
        }
    }

    /**
     * Recursively populate the $inspectedData array browsing the full class
     * hierarchy tree
     *
     * Private properties from parent class that are hidden by children will be
     * dropped from the populated arrays
     *
     * @param \ReflectionClass $class
     * @param object $instance
     * @param array $initialData
     * @param array $newData
     */
    private function recursiveFindInspectionData(\ReflectionClass $class, $instance, array &$inspectionData)
    {
        if ($parentClass = $class->getParentClass()) {
            $this->recursiveFindInspectionData($parentClass, $instance, $inspectionData);
        }

        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();

            $property->setAccessible(true);
            $inspectionData[$propertyName] = $property->getValue($instance);
        }
    }

    /**
     * Generates a hydrator for the given class name, and retrieves its class name
     *
     * @param object $instance
     */
    private function generateHydrator($instance): string
    {
        $parentClassName = \get_class($instance);
        $config = new Configuration($parentClassName);

        return $config->createFactory()->getHydratorClass($parentClassName);
    }
}
