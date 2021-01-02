<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack\Tags;

use DateTime;
use Exception;
use Mediagone\Twig\PowerPack\Tags\RequireTokenParser;
use PHPUnit\Framework\TestCase;
use Tests\Mediagone\Twig\PowerPack\Foo;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use function substr;


/**
 * @covers \Mediagone\Twig\PowerPack\Tags\RequireTokenParser
 */
final class RequireTokenParserTest extends TestCase
{
    //========================================================================================================
    // INIT
    //========================================================================================================
    
    private Environment $env;
    
    protected function setUp() : void
    {
        $this->env = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), [
            'debug' => true,
            'cache' => false,
            'autoescape' => false,
            'strict_variables' => true,
            'optimizations' => 0,
        ]);
    }
    
    
    
    //========================================================================================================
    // PRIMITIVE TYPES
    //========================================================================================================
    
    public function validPrimitiveProvider()
    {
        yield ['string', ''];
        yield ['string', 'Lorem ipsum'];
        yield ['?string', null];
        yield ['bool', true];
        yield ['bool', false];
        yield ['?bool', null];
        yield ['int', 1];
        yield ['?int', null];
        yield ['float', 1.234];
        yield ['?float', null];
    }
    
    /**
     * @dataProvider validPrimitiveProvider
     */
    public function test_primitive_variable_is_expected(string $type, $value) : void
    {
        $this->env->addTokenParser(new RequireTokenParser());
    
        $nullable = '';
        if ($type[0] === '?') {
            $nullable = 'nullable ';
            $type = substr($type, 1);
        }
        
        $result = $this->env->createTemplate("{% require $nullable'$type' as VAR %}")->render(['VAR' => $value]);
        
        self::assertSame('', $result);
    }
    
    
    public function invalidPrimitiveProvider()
    {
        yield ['string', null];
        yield ['string', true];
        yield ['string', 1];
        yield ['string', 1.234];
        yield ['string', new Foo()];
        yield ['bool', null];
        yield ['bool', 1];
        yield ['bool', 1.234];
        yield ['bool', 'Lorem ipsum'];
        yield ['bool', new Foo()];
        yield ['float', null];
        yield ['float', 1];
        yield ['float', true];
        yield ['float', 'Lorem ipsum'];
        yield ['float', new Foo()];
        yield ['int', null];
        yield ['int', true];
        yield ['int', 1.234];
        yield ['int', 'Lorem ipsum'];
        yield ['int', new Foo()];
    }
    
    /**
     * @dataProvider invalidPrimitiveProvider
     */
    public function test_primitive_variable_is_invalid(string $type, $value) : void
    {
        $this->env->addTokenParser(new RequireTokenParser());
        
        $this->expectException(Exception::class);
        $this->env->createTemplate("{% require '$type' as VAR %}")->render(['VAR' => $value]);
    }
    
    
    //========================================================================================================
    // PHP CLASSES
    //========================================================================================================
    
    public function test_php_class_is_defined() : void
    {
        $this->env->addTokenParser(new RequireTokenParser());
        
        $result = $this->env->createTemplate('{% require "DateTime" as DATETIME %}')->render(['DATETIME' => new DateTime()]);
        
        self::assertSame('', $result);
    }
    
    public function test_php_class_is_missing() : void
    {
        $this->env->addTokenParser(new RequireTokenParser());
        
        $this->expectException(Exception::class);
        $this->env->createTemplate('{% require "DateTime" as DATETIME %}')->render([]);
    }
    
    
    //========================================================================================================
    // CUSTOM CLASSES
    //========================================================================================================
    
    public function test_custom_class_is_defined() : void
    {
        $this->env->addTokenParser(new RequireTokenParser());
        
        $result = $this->env->createTemplate('{% require "Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\Foo" as FOO %}')->render(['FOO' => new Foo()]);
        
        self::assertSame('', $result);
    }
    
    public function test_custom_class_is_missing() : void
    {
        $this->env->addTokenParser(new RequireTokenParser());
        
        $this->expectException(Exception::class);
        $this->env->createTemplate('{% require "Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\Foo" as FOO %}')->render([]);
    }
    
    
    //========================================================================================================
    // MULTIPLE CLASSES
    //========================================================================================================
    
    public function test_multiple_classes_are_defined() : void
    {
        $this->env->addTokenParser(new RequireTokenParser());
        
        $result = $this->env->createTemplate(
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
        $this->env->addTokenParser(new RequireTokenParser());
        
        $this->expectException(Exception::class);
        $this->env->createTemplate(
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
        $this->env->addTokenParser(new RequireTokenParser());
        
        $result = $this->env->createTemplate(
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
        $this->env->addTokenParser(new RequireTokenParser());
        
        $this->expectException(Exception::class);
        $this->env->createTemplate(
            '{% require nullable "DateTime" as DATETIME %}'
                   .'{% require nullable "Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\Foo" as FOO %}'
        )->render([
            'DATETIME' => null,
        ]);
    }
    
    
    //========================================================================================================
    // ARRAY
    //========================================================================================================
    
    public function validArrayProvider()
    {
        yield ['string', []];
        yield ['string', ['Lorem ipsum']];
        yield ['string', ['Lorem ipsum', 'Lorem ipsum']];
        yield ['?string', [null]];
        yield ['?string', ['Lorem ipsum', null]];
        yield ['?string', [null, 'Lorem ipsum']];
        
        yield ['bool', []];
        yield ['bool', [true]];
        yield ['bool', [false]];
        yield ['bool', [true, false]];
        yield ['?bool', [null]];
        yield ['?bool', [true, null]];
        yield ['?bool', [null, true]];
        
        yield ['int', []];
        yield ['int', [1]];
        yield ['int', [1, 2]];
        yield ['?int', [null]];
        yield ['?int', [1, null]];
        yield ['?int', [null, 1]];
        
        yield ['float', []];
        yield ['float', [1.234]];
        yield ['float', [1.234, 2.345]];
        yield ['?float', [null]];
        yield ['?float', [1.234, null]];
        yield ['?float', [null, 1.234]];
    }
    
    /**
     * @dataProvider validArrayProvider
     */
    public function test_array_of_type_is_expected(string $type, $value) : void
    {
        $this->env->addTokenParser(new RequireTokenParser());
        
        $nullable = '';
        if ($type[0] === '?') {
            $nullable = 'nullable ';
            $type = substr($type, 1);
        }
        
        $result = $this->env->createTemplate("{% require array of $nullable'$type' as VAR %}")->render(['VAR' => $value]);
        
        self::assertSame('', $result);
    }
    
    
    public function invalidArrayProvider()
    {
        yield ['string', ['Lorem ipsum', 1]];
        yield ['string', ['Lorem ipsum', 1.234]];
        yield ['string', ['Lorem ipsum', true]];
        yield ['string', ['Lorem ipsum', null]];
        yield ['string', ['Lorem ipsum', new Foo()]];
        yield ['?string', [null, 1]];
        yield ['?string', [null, 1.234]];
        yield ['?string', [null, true]];
        yield ['?string', [null, new Foo()]];
    
        yield ['bool', ['Lorem ipsum', 1]];
        yield ['bool', ['Lorem ipsum', 1.234]];
        yield ['bool', ['Lorem ipsum', 'true']];
        yield ['bool', ['Lorem ipsum', null]];
        yield ['bool', ['Lorem ipsum', new Foo()]];
        yield ['?bool', [null, 1]];
        yield ['?bool', [null, 1.234]];
        yield ['?bool', [null, 'true']];
        yield ['?bool', [null, new Foo()]];
    
        yield ['int', ['Lorem ipsum', '1']];
        yield ['int', ['Lorem ipsum', 1.234]];
        yield ['int', ['Lorem ipsum', true]];
        yield ['int', ['Lorem ipsum', null]];
        yield ['int', ['Lorem ipsum', new Foo()]];
        yield ['?int', [null, '1']];
        yield ['?int', [null, 1.234]];
        yield ['?int', [null, true]];
        yield ['?int', [null, new Foo()]];
    
        yield ['float', ['Lorem ipsum', 1]];
        yield ['float', ['Lorem ipsum', '1.234']];
        yield ['float', ['Lorem ipsum', true]];
        yield ['float', ['Lorem ipsum', null]];
        yield ['float', ['Lorem ipsum', new Foo()]];
        yield ['?float', [null, 1]];
        yield ['?float', [null, '1.234']];
        yield ['?float', [null, true]];
        yield ['?float', [null, new Foo()]];
    }
    
    /**
     * @dataProvider invalidArrayProvider
     */
    public function test_array_contains_invalid_elements(string $type, $value) : void
    {
        $this->env->addTokenParser(new RequireTokenParser());
        
        $nullable = '';
        if ($type[0] === '?') {
            $nullable = 'nullable ';
            $type = substr($type, 1);
        }
        
        $this->expectException(Exception::class);
        $this->env->createTemplate("{% require array of $nullable'$type' as VAR %}")->render(['VAR' => $value]);
    }
    
    
}
