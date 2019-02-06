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

namespace GeneratedHydratorTest;

use GeneratedHydrator\Strategy\ClassNamingStrategy;

/**
 * Tests for {@see \GeneratedHydrator\Configuration}
 *
 * @author Pierre Rineau <pierre.rineau@processus.org>
 * @license MIT
 */
class ClassNamingStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testEverything()
    {
        $strategy = new ClassNamingStrategy();

        self::assertSame(
            "Foo\\Bar\\GeneratedHydrator",
            $strategy->generateClassName(
                '\\App\\Domain\\Generated',
                '\\Foo\\Bar'
            )
        );

        self::assertSame(
            "./src/Foo/Bar/GeneratedHydrator.php",
            $strategy->generateFilename(
                'Foo\\Bar\\GeneratedHydrator',
                './src/'
            )
        );

        self::assertSame(
            "./src/Bar/GeneratedHydrator.php",
            $strategy->generateFilename('Foo\\Bar\\GeneratedHydrator', './src/', '\\Foo')
        );

        self::assertSame(
            "./src/Bar/GeneratedHydrator.php",
            $strategy->generateFilename('Foo\\Bar\\GeneratedHydrator', './src/', 'Foo')
        );

        self::assertSame(
            "./src/Bar/GeneratedHydrator.php",
            $strategy->generateFilename('\\Foo\\Bar\\GeneratedHydrator', './src/', '\\Foo\\')
        );
    }
}
