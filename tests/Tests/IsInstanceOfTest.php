<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack\Tests;

use Mediagone\Twig\PowerPack\Tests\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use Tests\Mediagone\Twig\PowerPack\Bar;
use Tests\Mediagone\Twig\PowerPack\Foo;


/**
 * @covers \Mediagone\Twig\PowerPack\Tests\IsInstanceOf
 */
final class IsInstanceOfTest extends TestCase
{
    //========================================================================================================
    // PRIMITIVE TYPES
    //========================================================================================================
    
    public function test_can_tell_is_an_instance_of() : void
    {
        $foo = new Foo();
        self::assertTrue(IsInstanceOf::instanceOf($foo, Foo::class));
    }
    
    
    public function test_can_tell_is_not_an_instance_of() : void
    {
        $bar = new Bar();
        self::assertFalse(IsInstanceOf::instanceOf($bar, Foo::class));
    }
    
    
    public function test_can_tell_null_is_not_an_instance_of() : void
    {
        self::assertFalse(IsInstanceOf::instanceOf(null, Foo::class));
    }
    
    
    
}
