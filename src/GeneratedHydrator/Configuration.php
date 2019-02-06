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

namespace GeneratedHydrator;

use GeneratedHydrator\ClassGenerator\HydratorGeneratorInterface;
use GeneratedHydrator\ClassGenerator\PHP5HydratorGenerator;
use GeneratedHydrator\Factory\HydratorFactory;
use GeneratedHydrator\Strategy\HashNamingStrategy;
use GeneratedHydrator\Strategy\NamingStrategy;

/**
 * Base configuration class for the generated hydrator - serves as micro disposable DIC/facade
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
final class Configuration
{
    const DEFAULT_GENERATED_CLASS_NAMESPACE = 'GeneratedHydratorGeneratedClass';
    const DEFAULT_GENERATED_NAME_STRATEGY = 'hash';

    private $autoGenerateProxies = true;
    private $generatedClassesAutoloader;
    private $generatedClassesNamespace = self::DEFAULT_GENERATED_CLASS_NAMESPACE;
    private $generatedClassesTargetDir;
    private $hydratedClassName;
    private $hydratorGenerator;
    private $namespacePrefix;
    private $namingStrategy;

    /**
     * @param string $hydratedClassName
     */
    public function __construct($hydratedClassName = null)
    {
        if ($hydratedClassName) {
            $this->setHydratedClassName($hydratedClassName);
        }
    }

    /**
     * @return \GeneratedHydrator\Factory\HydratorFactory
     */
    public function createFactory()
    {
        return new HydratorFactory($this);
    }

    /**
     * @param string $namespacePrefix
     */
    public function setNamespacePrefix($namespacePrefix)
    {
        $this->namespacePrefix = $namespacePrefix;
    }

    /**
     * @return ?string
     */
    public function getNamespacePrefix()
    {
        return $this->namespacePrefix;
    }

    /**
     * @param \GeneratedHydrator\Strategy\NamingStrategy $namingStrategy
     */
    public function setNamingStrategy(NamingStrategy $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * @return \GeneratedHydrator\Strategy\NamingStrategy
     */
    public function getNamingStrategy()
    {
        if (null === $this->namingStrategy) {
            $this->namingStrategy = new HashNamingStrategy();
        }

        return $this->namingStrategy;
    }

    /**
     * @param string $hydratedClassName
     */
    public function setHydratedClassName($hydratedClassName)
    {
        $this->hydratedClassName = $hydratedClassName;
    }

    /**
     * @return string
     */
    public function getHydratedClassName()
    {
        return $this->hydratedClassName;
    }

    /**
     * @param bool $autoGenerateProxies
     */
    public function setAutoGenerateProxies($autoGenerateProxies)
    {
        $this->autoGenerateProxies = $autoGenerateProxies;
    }

    /**
     * @return bool
     */
    public function doesAutoGenerateProxies()
    {
        return $this->autoGenerateProxies;
    }

    /**
     * @param string $generatedClassesNamespace
     */
    public function setGeneratedClassesNamespace($generatedClassesNamespace)
    {
        $this->generatedClassesNamespace = $generatedClassesNamespace;
    }

    /**
     * @return string
     */
    public function getGeneratedClassesNamespace()
    {
        return $this->generatedClassesNamespace;
    }

    /**
     * @param string $generatedClassesTargetDir
     */
    public function setGeneratedClassesTargetDir($generatedClassesTargetDir)
    {
        $this->generatedClassesTargetDir = $generatedClassesTargetDir;
    }

    /**
     * @return null|string
     */
    public function getGeneratedClassesTargetDir()
    {
        if (null === $this->generatedClassesTargetDir) {
            $this->generatedClassesTargetDir = \sys_get_temp_dir();
        }

        return $this->generatedClassesTargetDir;
    }

    /**
     * @param HydratorGeneratorInterface $hydratorGenerator
     */
    public function setHydratorGenerator(HydratorGeneratorInterface $hydratorGenerator)
    {
        $this->hydratorGenerator = $hydratorGenerator;
    }

    /**
     * @return HydratorGeneratorInterface
     */
    public function getHydratorGenerator()
    {
        if (null === $this->hydratorGenerator) {
            $this->hydratorGenerator = new PHP5HydratorGenerator();
        }

        return $this->hydratorGenerator;
    }
}
