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

namespace Baleen\Migrations\Version\Comparator;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Takes the version's namespace into account when sorting
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class NamespacesAwareComparator extends AbstractReversibleComparator
{
    /** @var array */
    private $namespaces;

    /** @var ComparatorInterface */
    private $fallback;

    /**
     * NamespacesAwareComparator constructor.
     *
     * @param int $order
     * @param ComparatorInterface $fallbackComparator
     * @param array $namespaces Namespaces with keys ordered by priority (highest priority first)
     */
    public function __construct($order, ComparatorInterface $fallbackComparator, array $namespaces)
    {
        $this->fallback = $fallbackComparator;
        // normalize namespaces
        foreach ($namespaces as &$namespace) {
            $namespace = trim($namespace, '\\') . '\\';
        }
        krsort($namespaces);
        $this->namespaces = $namespaces;
        parent::__construct($order);
    }

    /**
     * {@inheritdoc}
     *
     * Given the following $namespaces passed in the constructor:
     *   - Taz (lowest priority)
     *   - Bar
     *   - Foo (highest priority)
     *
     * Will produce the following results based on the migration's FQCN:
     *   - (Foo\v200012, Bar\v201612) => -1
     *   - (Taz\v201612, Foo\v200012) => 1
     *   - (FooBar\v201612, Taz\v200012) => 1
     *   - (Taz\v201612, Taz\v201601) => delegate to fallback
     *   - (FooBar\v201612, FooBar\v200012) => delegate to fallback
     *
     * @param VersionInterface $version1
     * @param VersionInterface $version2
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function compare(VersionInterface $version1, VersionInterface $version2)
    {
        if ($version1->getMigration() === null || $version2->getMigration() === null) {
            throw new InvalidArgumentException(
                "Expected both versions to be linked to a migration, but at least one of them isn't."
            );
        }
        $class1 = get_class($version1->getMigration());
        $class2 = get_class($version2->getMigration());

        if ($class1 === $class2) {
            // exit early in this case
            return 0;
        }

        $res = null;
        // loop from highest namespace priority to lowest
        foreach ($this->namespaces as $priority => $namespace) {
            if (strpos($class1, $namespace) === 0) {
                $res = 1;
            }
            if (strpos($class2, $namespace) === 0) {
                // subtract 1 from $res, setting it to either -1 or 0
                $res = (int) $res - 1;
            }
            if (null !== $res) {
                break; // exit as soon as we found a sort order
            }
        }
        // null = could not determine order / zero = both orders are equal
        if (empty($res)) {
            // delegate sorting to the fallback comparator
            $res = call_user_func($this->fallback, $version1, $version2);
        }
        return $res;
    }

    /**
     * @inheritDoc
     */
    public function withOrder($order)
    {
        return new static($order, $this->fallback, $this->namespaces);
    }
}
