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
 * <http://www.doctrine-project.org>.
 */

namespace BaleenTest\Migrations\Service\Runner\Factory;

use Baleen\Migrations\Service\Runner\Factory\CollectionRunnerFactory;
use Baleen\Migrations\Service\Runner\RunnerInterface;
use Baleen\Migrations\Common\Collection\CollectionInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class CollectionRunnerFactoryTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CollectionRunnerFactoryTest extends BaseTestCase
{
    /**
     * testCreate
     * @return void
     */
    public function testCreate()
    {
        $factory = new CollectionRunnerFactory();
        /** @var CollectionInterface|m\Mock $collection */
        $collection = m::mock(CollectionInterface::class);
        $result = $factory->create($collection);
        $this->assertInstanceOf(RunnerInterface::class, $result);
    }
}
