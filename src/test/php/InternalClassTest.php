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

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
/**
 * Applies tests to a PHP internal class.
 */
class InternalClassTest extends TestCase
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
        $this->proxy = NewInstance::of(\ReflectionObject::class, [$this]);
    }

    /**
     * @test
     */
    public function callsOriginalMethodIfNoMappingProvided()
    {
        assertThat($this->proxy->getName(), equals(__CLASS__));
    }

    /**
     * @test
     */
    public function mapToSimpleValueReturnsValueOnMethodCall()
    {
        $this->proxy->returns(['getName' => 'foo']);
        assertThat($this->proxy->getName(), equals('foo'));
    }

    /**
     * @test
     */
    public function mapToClosureReturnsClosureReturnValueOnMethodCall()
    {
        $this->proxy->returns(['getName' => function() { return 'foo'; }]);
        assertThat($this->proxy->getName(), equals('foo'));
    }

    /**
     * @test
     * @since  0.4.0
     */
    public function mapToCallableReturnsCallableReturnValueOnMethodCall()
    {
        $this->proxy->returns(['getName' => 'strtoupper']);
        assertThat($this->proxy->getName('foo'), equals('FOO'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function amountOfCallsToMethodIsZeroIfNotCalled()
    {
        verify($this->proxy, 'getNamespaceName')->wasNeverCalled();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function recordsAmountOfCallsToMethod()
    {
        $this->proxy->getName();
        $this->proxy->getName();
        $this->proxy->getShortName();
        verify($this->proxy,'getName')->wasCalled(2);
        verify($this->proxy, 'getShortName')->wasCalledOnce();
    }

    /**
     * @test
     */
    public function canVerifyReceivedArguments()
    {
        $this->proxy->implementsInterface(Proxy::class);
        verify($this->proxy, 'implementsInterface')->received(Proxy::class);
    }

    /**
     * @test
     */
    public function canVerifyReceivedArgumentsOfSpecificInvocation()
    {
        $this->proxy->hasProperty('foo');
        $this->proxy->hasProperty('bar');
        verify($this->proxy, 'hasProperty')->receivedOn(2, 'bar');
    }
}
