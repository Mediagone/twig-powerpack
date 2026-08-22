<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack\Functions;

use ArgumentCountError;
use Error;
use Mediagone\Twig\PowerPack\Functions\NewClass;
use PHPUnit\Framework\TestCase;
use Tests\Mediagone\Twig\PowerPack\Foo;
use Tests\Mediagone\Twig\PowerPack\FooWithArgument;
use Tests\Mediagone\Twig\PowerPack\FooWithArrayArgument;
use Tests\Mediagone\Twig\PowerPack\FooWithOptionalArgument;
use Tests\Mediagone\Twig\PowerPack\FooWithTwoArguments;
use TypeError;


/**
 * @covers \Mediagone\Twig\PowerPack\Functions\NewClass
 */
final class NewClassTest extends TestCase
{
    //========================================================================================================
    // PRIMITIVE TYPES
    //========================================================================================================
    
    public function test_can_create_instance() : void
    {
        $instance = NewClass::createInstance(Foo::class, 'some string');
        self::assertInstanceOf(Foo::class, $instance);
    }
    
    
    public function test_can_create_instance_with_argument() : void
    {
        $instance = NewClass::createInstance(FooWithArgument::class, 'some string');
        self::assertInstanceOf(FooWithArgument::class, $instance);
        self::assertSame('some string', $instance->getArg());
    }
    
    
    public function test_can_create_instance_with_two_arguments() : void
    {
        $instance = NewClass::createInstance(FooWithTwoArguments::class, 'some string', ['other string', 'another string']);
        self::assertInstanceOf(FooWithTwoArguments::class, $instance);
        self::assertSame('some string', $instance->getStringArg());
        self::assertIsArray($instance->getArrayArg());
        self::assertCount(2, $instance->getArrayArg());
    }
    
    
    public function test_can_create_instance_with_missing_argument() : void
    {
        $this->expectException(ArgumentCountError::class);
        NewClass::createInstance(FooWithArgument::class);
    }
    
    
    
    //========================================================================================================
    // NAMED ARGUMENTS (single associative array, spread as named arguments)
    //========================================================================================================
    
    public function test_can_create_instance_with_named_arguments() : void
    {
        $instance = NewClass::createInstance(FooWithTwoArguments::class, ['stringArg' => 'some string', 'arrayArg' => ['a', 'b']]);
        self::assertInstanceOf(FooWithTwoArguments::class, $instance);
        self::assertSame('some string', $instance->getStringArg());
        self::assertSame(['a', 'b'], $instance->getArrayArg());
    }
    
    
    public function test_can_create_instance_with_named_arguments_in_any_order() : void
    {
        $instance = NewClass::createInstance(FooWithTwoArguments::class, ['arrayArg' => ['a', 'b'], 'stringArg' => 'some string']);
        self::assertInstanceOf(FooWithTwoArguments::class, $instance);
        self::assertSame('some string', $instance->getStringArg());
        self::assertSame(['a', 'b'], $instance->getArrayArg());
    }
    
    
    public function test_can_create_instance_with_named_arguments_omitting_optional_one() : void
    {
        $instance = NewClass::createInstance(FooWithOptionalArgument::class, ['arg' => 'some string']);
        self::assertInstanceOf(FooWithOptionalArgument::class, $instance);
        self::assertSame('some string', $instance->getArg());
        self::assertSame('default value', $instance->getOptionalArg());
    }
    
    
    public function test_cannot_create_instance_with_unknown_named_argument() : void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessageMatches('/Unknown named parameter/');
        NewClass::createInstance(FooWithArgument::class, ['unknownKey' => 'some string']);
    }
    
    
    public function test_cannot_create_instance_with_named_argument_of_wrong_type() : void
    {
        $this->expectException(TypeError::class);
        NewClass::createInstance(FooWithArgument::class, ['arg' => 123]);
    }
    
    
    public function test_empty_array_stays_positional() : void
    {
        $instance = NewClass::createInstance(FooWithArrayArgument::class, []);
        self::assertInstanceOf(FooWithArrayArgument::class, $instance);
        self::assertSame([], $instance->getArg());
    }
    
    
    public function test_numeric_keyed_array_stays_positional() : void
    {
        $instance = NewClass::createInstance(FooWithArrayArgument::class, [0 => 'a', 1 => 'b']);
        self::assertInstanceOf(FooWithArrayArgument::class, $instance);
        self::assertSame(['a', 'b'], $instance->getArg());
    }
    
    
    
    //========================================================================================================
    // POSITIONAL ESCAPE HATCH
    //========================================================================================================
    
    public function test_positional_creation_receives_associative_array_as_single_argument() : void
    {
        $instance = NewClass::createInstancePositional(FooWithArrayArgument::class, ['Label' => 'some string']);
        self::assertInstanceOf(FooWithArrayArgument::class, $instance);
        self::assertSame(['Label' => 'some string'], $instance->getArg());
    }
    
    
    
}
