<?php
declare(strict_types=1);
/**
 * This file is part of bovigo\callmap.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  bovigo_callmap
 */
namespace bovigo\callmap;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\each;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isOfType;
/**
 * Helper trait for the test.
 */
trait SomeTrait
{
    public function action($something)
    {
        return $something;
    }

    abstract public function other(array $optional = [], int $roland = 303);
}
/**
 * Applies tests to a self defined class.
 *
 * @group  issue_1
 */
class TraitTest extends TestCase
{
    /**
     * @type  bovigo\callmap\Proxy
     */
    private $proxy;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->proxy = NewInstance::of(SomeTrait::class);
    }

    /**
     * @test
     */
    public function callsOriginalMethodIfNoMappingProvided()
    {
        assert($this->proxy->action(313), equals(313));
    }

    /**
     * @test
     */
    public function mapToSimpleValueReturnsValueOnMethodCall()
    {
        $this->proxy->mapCalls(['action' => 'foo']);
        assert($this->proxy->action(313), equals('foo'));
    }

    /**
     * @test
     */
    public function mapToClosureReturnsClosureReturnValueOnMethodCall()
    {
        $this->proxy->mapCalls(['action' => function() { return 'foo'; }]);
        assert($this->proxy->action(313), equals('foo'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function amountOfCallsToMethodIsZeroIfNotCalled()
    {
        verify($this->proxy, 'action')->wasNeverCalled();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function recordsAmountOfCallsToMethod()
    {
        $this->proxy->action(303);
        $this->proxy->action(313);
        verify($this->proxy, 'action')->wasCalled(2);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function returnsListOfReceivedArgumentsIfMethodCalled()
    {
        $this->proxy->action(313);
        verify($this->proxy, 'action')->received(313);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function optionalArgumentsCanNotBeVerifiedWhenNotExplicitlyPassed()
    {
        $this->proxy->other();
        verify($this->proxy, 'other')->receivedNothing();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function listOfReceivedArgumentsContainsGivenArguments()
    {
        $this->proxy->other(['play' => 808]);
        verify($this->proxy, 'other')->received(each(isOfType('int')));
    }
}
