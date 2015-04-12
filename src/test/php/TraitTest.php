<?php
/**
 * This file is part of bovigo\callmap.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  bovigo_callmap
 */
namespace bovigo\callmap;
/**
 * Helper trait for the test.
 */
trait SomeTrait
{
    public function action($something)
    {
        return $something;
    }

    abstract public function other(array $optional = [], $roland = 303);
}
/**
 * Applies tests to a self defined class.
 *
 * @group  issue_1
 */
class TraitTest extends \PHPUnit_Framework_TestCase
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
        $this->proxy = NewInstance::of('bovigo\callmap\SomeTrait');
    }

    /**
     * @test
     */
    public function callsOriginalMethodIfNoMappingProvided()
    {
        assertEquals(
                313,
                $this->proxy->action(313)
        );
    }

    /**
     * @test
     */
    public function mapToSimpleValueReturnsValueOnMethodCall()
    {
        $this->proxy->mapCalls(['action' => 'foo']);
        assertEquals(
                'foo',
                $this->proxy->action(313)
        );
    }

    /**
     * @test
     */
    public function mapToClosureReturnsClosureReturnValueOnMethodCall()
    {
        $this->proxy->mapCalls(['action' => function() { return 'foo'; }]);
        assertEquals(
                'foo',
                $this->proxy->action(313)
        );
    }

    /**
     * @test
     */
    public function amountOfCallsToMethodIsZeroIfNotCalled()
    {
        assertTrue(verify($this->proxy, 'action')->wasNeverCalled());
    }

    /**
     * @test
     */
    public function recordsAmountOfCallsToMethod()
    {
        $this->proxy->action(303);
        $this->proxy->action(313);
        assertTrue(verify($this->proxy, 'action')->wasCalled(2));
    }

    /**
     * @test
     */
    public function listOfReceivedArgumentsIsNullIfMethodNotCalled()
    {
        assertNull($this->proxy->argumentsReceivedFor('action'));
    }

    /**
     * @test
     */
    public function returnsListOfReceivedArgumentsIfMethodCalled()
    {
        $this->proxy->action(313);
        assertEquals(
                [313],
                $this->proxy->argumentsReceivedFor('action')
        );
    }

    /**
     * @test
     */
    public function listOfReceivedArgumentsDoesNotContainOptionalArguments()
    {
        $this->proxy->other();
        assertEquals(
                [],
                $this->proxy->argumentsReceivedFor('other')
        );
    }

    /**
     * @test
     */
    public function listOfReceivedArgumentsContainsGivenArguments()
    {
        $this->proxy->other(['play' => 808]);
        assertEquals(
                [['play' => 808]],
                $this->proxy->argumentsReceivedFor('other')
        );
    }

    /**
     * @test
     */
    public function listOfReceivedArgumentsIsNullWhenNotCalledForRequestedInvocationCount()
    {
        $this->proxy->action(313);
        assertNull($this->proxy->argumentsReceivedFor('action', 2));
    }
}
