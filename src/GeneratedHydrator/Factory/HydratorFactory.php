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

namespace GeneratedHydrator\Factory;

use GeneratedHydrator\Configuration;

/**
 * Factory responsible of producing hydrators
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Pierre Rineau <pierre.rineau@processus.org>
 * @license MIT
 */
class HydratorFactory
{
    private $configuration;

    /**
     * @param \GeneratedHydrator\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = clone $configuration;
    }

    /**
     * Ensure target directory exists
     *
     * @param ?string $directory
     *
     * @return string
     */
    private function ensureDirectory($directory = null)
    {
        if (!$directory) {
            $directory = \sys_get_temp_dir().'/goat-hydrator';
        }
        if (!\is_dir($directory) && !@\mkdir($directory)) { // Attempt directory creation
            throw new \InvalidArgumentException(\sprintf("'%s': could not create directory", $directory));
        }

        return $directory;
    }

    /**
     * Write PHP file
     *
     * @param string $filename
     * @param string $content
     */
    private function writeFile($filename, $content)
    {
        $directory = \dirname($filename);
        if (!\is_writable($directory)) {
            throw new \RuntimeException(\sprintf('Target directory "%s" is not writable.', $directory));
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
    public function getHydratorClass()
    {
        $originalClassName = $this->configuration->getHydratedClassName();
        $namingStrategy = $this->configuration->getNamingStrategy();

        $realClassName = $namingStrategy->generateClassName(
            $originalClassName,
            $this->configuration->getGeneratedClassesNamespace()
        );

        if (!class_exists($realClassName)) {

            $targetFile = $namingStrategy->generateFilename(
                $realClassName,
                $this->configuration->getGeneratedClassesTargetDir()
            );

            $this->ensureDirectory(\dirname($targetFile));

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
