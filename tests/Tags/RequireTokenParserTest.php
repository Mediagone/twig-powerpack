<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack\Tags;

use DateTime;
use Exception;
use Mediagone\Twig\PowerPack\Tags\RequireTokenParser;
use PHPUnit\Framework\TestCase;
use Tests\Mediagone\Twig\PowerPack\Foo;
use Twig\Environment;
use Twig\Loader\LoaderInterface;


/**
 * @covers \Mediagone\Twig\PowerPack\Tags\RequireTokenParser
 */
final class RequireTokenParserTest extends TestCase
{
    //========================================================================================================
    // PHP CLASSES
    //========================================================================================================
    
    public function test_php_class_is_defined() : void
    {
        $env = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $env->addTokenParser(new RequireTokenParser());
        
        $result = $env->createTemplate('{% require "DateTime" as DATETIME %}')->render(['DATETIME' => new DateTime()]);
        
        self::assertSame('', $result);
    }
    
    public function test_php_class_is_missing() : void
    {
        $env = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $env->addTokenParser(new RequireTokenParser());
    
        $this->expectException(Exception::class);
        $env->createTemplate('{% require "DateTime" as DATETIME %}')->render([]);
    }
    
    
    //========================================================================================================
    // CUSTOM CLASSES
    //========================================================================================================
    
    public function test_custom_class_is_defined() : void
    {
        $env = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $env->addTokenParser(new RequireTokenParser());
        
        $result = $env->createTemplate('{% require "Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\Foo" as FOO %}')->render(['FOO' => new Foo()]);
        
        self::assertSame('', $result);
    }
    
    public function test_custom_class_is_missing() : void
    {
        $env = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $env->addTokenParser(new RequireTokenParser());
        
        $this->expectException(Exception::class);
        $result = $env->createTemplate('{% require "Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\Foo" as FOO %}')->render([]);
    }
    
    
    //========================================================================================================
    // MULTIPLE CLASSES
    //========================================================================================================
    
    public function test_multiple_classes_are_defined() : void
    {
        $env = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $env->addTokenParser(new RequireTokenParser());
        
        $result = $env->createTemplate(
            '{% require "DateTime" as DATETIME %}'
                   .'{% require "Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\Foo" as FOO %}'
        )->render([
            'DATETIME' => new DateTime(),
            'FOO' => new Foo(),
        ]);
        
        self::assertSame('', $result);
    }
    
    public function test_multiple_classes_one_is_missing() : void
    {
        $env = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $env->addTokenParser(new RequireTokenParser());
    
        $this->expectException(Exception::class);
        $env->createTemplate(
            '{% require "DateTime" as DATETIME %}'
                   .'{% require "Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\Foo" as FOO %}'
        )->render([
            'DATETIME' => new DateTime(),
        ]);
    }
    
    
    //========================================================================================================
    // NULLABLE
    //========================================================================================================
    
    public function test_can_be_nullable() : void
    {
        $env = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $env->addTokenParser(new RequireTokenParser());
        
        $result = $env->createTemplate(
            '{% require nullable "DateTime" as DATETIME %}'
                   .'{% require nullable "Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\Foo" as FOO %}'
        )->render([
            'DATETIME' => null,
            'FOO' => null,
        ]);
        
        self::assertSame('', $result);
    }
    
    public function test_can_be_nullable_but_one_is_missing() : void
    {
        $env = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $env->addTokenParser(new RequireTokenParser());
        
        $this->expectException(Exception::class);
        $env->createTemplate(
            '{% require nullable "DateTime" as DATETIME %}'
                   .'{% require nullable "Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\Foo" as FOO %}'
        )->render([
            'DATETIME' => null,
        ]);
    }
    
    
    
}
