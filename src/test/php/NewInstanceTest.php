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
use bovigo\callmap\helper\AnotherTestHelperClass;
use bovigo\callmap\helper\ReturnTypeHints;
use bovigo\callmap\helper\ThisIsNotPossible;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\{
    assertThat,
    expect,
    predicate\equals,
    predicate\isInstanceOf,
    predicate\isNotSameAs,
    predicate\isNull
};
/**
 * All remaining tests for bovigo\callmap\NewInstance.
 */
class NewInstanceTest extends TestCase
{
    /**
     * @test
     */
    public function callWithNonObjectOrClassNameThrowsInvalidArgumentException()
    {
        expect(function() {
                NewInstance::of(313);
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function callWithNonExistingClassNameThrowsInvalidArgumentException()
    {
        expect(function() {
                NewInstance::of('DoesNotExist');
        })
        ->throws(\InvalidArgumentException::class);

    }

    /**
     * @test
     * @since  0.4.0
     */
    public function canNotCreateInstanceOfFinalClass()
    {
        expect(function() {
                NewInstance::of(ThisIsNotPossible::class);
        })
        ->throws(\InvalidArgumentException::class)
        ->withMessage('Can not create mapping proxy for final class ' . ThisIsNotPossible::class);
    }

    /**
     * @test
     * @since  0.4.0
     */
    public function canNotCreateStubInstanceOfFinalClass()
    {
        expect(function() {
                NewInstance::stub(ThisIsNotPossible::class);
        })
        ->throws(\InvalidArgumentException::class)
        ->withMessage('Can not create mapping proxy for final class ' . ThisIsNotPossible::class);
    }

    /**
     * @test
     * @since  0.4.0
     */
    public function canNotRetrieveMappedClassnameForFinalClass()
    {
        expect(function() {
                NewInstance::classname(ThisIsNotPossible::class);
        })
        ->throws(\InvalidArgumentException::class)
        ->withMessage('Can not create mapping proxy for final class ' . ThisIsNotPossible::class);
    }

    /**
     * @test
     * @since  0.2.0
     */
    public function doesNotGenerateClassTwice()
    {
        assertThat(
                NewInstance::classname(\ReflectionObject::class),
                equals(NewInstance::classname(\ReflectionObject::class))
        );
    }

    /**
     * @test
     * @since  0.2.0
     */
    public function doesCreateIndependentInstances()
    {
        assertThat(
                NewInstance::of(\ReflectionObject::class, [$this]),
                isNotSameAs(NewInstance::of(\ReflectionObject::class, [$this]))
        );
    }

    /**
     * @test
     * @since  0.2.0
     */
    public function doesCreateIndependentStubs()
    {
        assertThat(
                NewInstance::stub(AnotherTestHelperClass::class),
                isNotSameAs(NewInstance::stub(AnotherTestHelperClass::class))
        );
    }

    /**
     * @test
     * @since  0.4.0
     */
    public function mapNonExistingMethodThrowsInvalidArgumentException()
    {
        expect(function() {
                NewInstance::of(AnotherTestHelperClass::class)
                        ->returns(['doesNotExist' => true]);
        })
        ->throws(\InvalidArgumentException::class)
        ->withMessage('Trying to map method ' . AnotherTestHelperClass::class.'::doesNotExist(), but it does not exist. Probably a typo?');
    }

    /**
     * @test
     * @since  0.4.0
     */
    public function mapExistingMethodWithTypoThrowsInvalidArgumentException()
    {
        expect(function() {
                NewInstance::of(AnotherTestHelperClass::class)
                        ->returns(['doSomethingy' => true]);
        })
        ->throws(\InvalidArgumentException::class)
        ->withMessage('Trying to map method ' . AnotherTestHelperClass::class.'::doSomethingy(), but it does not exist. Probably a typo?');
    }

    /**
     * @test
     * @since  0.4.0
     */
    public function mapNonApplicableMethodThrowsInvalidArgumentException()
    {
        $proxy = NewInstance::of(AnotherTestHelperClass::class);
        expect(function() use ($proxy) {
                $proxy->returns(['doNotTouchThis' => true]);
        })
                ->throws(\InvalidArgumentException::class)
                ->withMessage(
                        'Trying to map method '
                        . AnotherTestHelperClass::class.'::doNotTouchThis(),'
                        . ' but it is not applicable for mapping.'
                );
    }

    /**
     * @test
     * @since  0.5.0
     */
    public function retrieveInvocationsForNonExistingMethodThrowsInvalidArgumentException()
    {
        $proxy = NewInstance::of(AnotherTestHelperClass::class);
        expect(function() use ($proxy) { $proxy->invocations('doesNotExist'); })
                ->throws(\InvalidArgumentException::class)
                ->withMessage(
                        'Trying to retrieve invocations for method '
                        . AnotherTestHelperClass::class.'::doesNotExist(),'
                        . ' but it does not exist. Probably a typo?'
                );
    }

    /**
     * @test
     * @since  0.5.0
     */
    public function retrieveInvocationsForExistingMethodWithTypoThrowsInvalidArgumentException()
    {
        $proxy = NewInstance::of(AnotherTestHelperClass::class);
        expect(function() use ($proxy) { $proxy->invocations('doSomethingy'); })
                ->throws(\InvalidArgumentException::class)
                ->withMessage(
                        'Trying to retrieve invocations for method '
                        . AnotherTestHelperClass::class.'::doSomethingy(),'
                        . ' but it does not exist. Probably a typo?'
                );
    }

    /**
     * @test
     * @since  0.5.0
     */
    public function retrieveInvocationsForNonApplicableMethodThrowsInvalidArgumentException()
    {
        $proxy = NewInstance::of(AnotherTestHelperClass::class);
        expect(function() use ($proxy) { $proxy->invocations('doNotTouchThis'); })
                ->throws(\InvalidArgumentException::class)
                ->withMessage(
                        'Trying to retrieve invocations for method '
                        . AnotherTestHelperClass::class.'::doNotTouchThis(),'
                        . ' but it is not applicable for mapping.'
                );
    }

    /**
     * @test
     * @since  2.0.0
     */
    public function canCreateInstanceFromClassWithPhp7ReturnTypeHintOnMethod()
    {
        assertThat(
                NewInstance::of(ReturnTypeHints::class),
                isInstanceOf(ReturnTypeHints::class)
        );
    }

    /**
     * @test
     * @since  2.0.1
     */
    public function mapReturnValueToNullShouldNotCallOriginalMethod()
    {
        $instance = NewInstance::of(AnotherTestHelperClass::class)
                ->returns(['gimmeFive' => null]);
        assertThat($instance->gimmeFive(), isNull());
    }
}
