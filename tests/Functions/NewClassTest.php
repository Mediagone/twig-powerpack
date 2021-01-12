<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack\Functions;

use ArgumentCountError;
use Mediagone\Twig\PowerPack\Functions\NewClass;
use PHPUnit\Framework\TestCase;
use Tests\Mediagone\Twig\PowerPack\Foo;
use Tests\Mediagone\Twig\PowerPack\FooWithArgument;
use Tests\Mediagone\Twig\PowerPack\FooWithTwoArguments;


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
    
    
    
}
