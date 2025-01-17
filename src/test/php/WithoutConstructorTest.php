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
use bovigo\callmap\helper\ClassWithConstructor;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isNull;
/**
 * Applies tests to a stub of a class.
 */
class WithoutConstructorTest extends TestCase
{
    /**
     * @type  bovigo\callmap\Proxy
     */
    private $proxy;

    /**
     * set up test environment
     */
    public function setUp(): void
    {
        $this->proxy = NewInstance::stub(ClassWithConstructor::class);
    }
    /**
     * @test
     */
    public function returnsNullIfMethodCallNotMapped()
    {
        assertThat($this->proxy->action(), isNull());
    }

    /**
     * @test
     */
    public function mapToSimpleValueReturnsValueOnMethodCall()
    {
        $this->proxy->returns(['action' => 3]);
        assertThat($this->proxy->action(), equals(3));
    }

    /**
     * @test
     */
    public function mapToClosureReturnsClosureReturnValueOnMethodCall()
    {
        $this->proxy->returns(['action' => function() { return 42; }]);
        assertThat($this->proxy->action(), equals(42));
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
        $this->proxy->action();
        $this->proxy->action();
        verify($this->proxy, 'action')->wasCalled(2);
    }

    /**
     * @test
     */
    public function returnsListOfReceivedArgumentsIfMethodCalled()
    {
        $this->proxy->action(303);
        verify($this->proxy, 'action')->received(303);
    }
}
