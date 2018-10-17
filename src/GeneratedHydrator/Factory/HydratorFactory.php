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

namespace GeneratedHydrator\Factory;

use GeneratedHydrator\Configuration;

/**
 * Factory responsible of producing hydrators
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorFactory
{
    /**
     * @var \GeneratedHydrator\Configuration
     */
    private $configuration;

    /**
     * @param \GeneratedHydrator\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = clone $configuration;
    }

    /**
     * Generate class name from parent class name
     *
     * @param string $userClassName
     *
     * @return string
     */
    private function generateClassName(string $userClassName) : string
    {
        return $this->configuration->getGeneratedClassesNamespace() . "\\G" . \sha1($userClassName);
    }

    /**
     * Write PHP file
     *
     * @param string $filename
     * @param string $content
     */
    private function writeFile(string $filename, string $content)
    {
        $directory = \dirname($filename);
        if (!\is_writable($directory)) {
            throw new \RuntimeException(\sprintf('Cache directory "%s" is not writable.', $directory));
        }

        $tmpFile = \tempnam($directory, \basename($filename));

        if (false === @\file_put_contents($tmpFile, $content)) {
            throw new \RuntimeException(\sprintf('Could not write file "%s".', $tmpFile));
        }
        if (!@\rename($tmpFile, $filename)) {
            throw new \RuntimeException(\sprintf('Could not write file "%s".', $filename));
        }
        @\chmod($filename, 0666 & ~\umask());
    }

    /**
     * Retrieves the generated hydrator FQCN
     *
     * @return string
     */
    public function getHydratorClass() : string
    {
        $originalClassName = $this->configuration->getHydratedClassName();
        $realClassName = $this->generateClassName($originalClassName);

        if (!class_exists($realClassName)) {

            $directory = $directory = $this->configuration->getGeneratedClassesTargetDir();
            $targetFile = $directory . '/' . \str_replace("\\", "_", $realClassName) . '.php';

            if (@include_once $targetFile) {
                return $realClassName;
            }

            $generator = $this->configuration->getHydratorGenerator();
            $phpClassCode = $generator->generate(new \ReflectionClass($originalClassName), $realClassName, $originalClassName);
            $this->writeFile($targetFile, $phpClassCode);

            require_once $targetFile;
        }

        return $realClassName;
    }
}
