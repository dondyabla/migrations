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
 * and is licensed under the MIT license. For more information, see
 * <https://github.com/baleen/migrations>.
 */

namespace BaleenTest\Migrations\Service\MigrationBus;

use Baleen\Migrations\Exception\MigrationBusException;
use Baleen\Migrations\Service\MigrationBus\Middleware\AbstractMiddleware;
use Baleen\Migrations\Service\MigrationBus\MigrateHandler;
use Baleen\Migrations\Service\MigrationBus\MigrationBus;
use BaleenTest\Migrations\BaseTestCase;
use League\Tactician\Middleware;
use Mockery as m;

/**
 * Class MigrationBusTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrationBusTest extends BaseTestCase
{
    /**
     * testThrowsExceptionIfNoMigrateHandler
     * @return void
     */
    public function testThrowsExceptionIfNoMigrateHandler()
    {
        $this->setExpectedException(MigrationBusException::class, MigrateHandler::class);
        new MigrationBus([]);
    }

    /**
     * testCreateDefaultBus
     * @return void
     */
    public function testCreateDefaultBus()
    {
        $bus = MigrationBus::createDefaultBus();
        $this->assertInstanceOf(MigrationBus::class, $bus);
    }

    /**
     * testGetDefaultMiddleware
     * @return void
     */
    public function testGetDefaultMiddleware()
    {
        $defaults = MigrationBus::getDefaultMiddleWare();
        $this->assertTrue(is_array($defaults));
        $this->assertNotEmpty($defaults);
        $this->assertContainsOnlyInstancesOf(Middleware::class, $defaults);
    }
}
