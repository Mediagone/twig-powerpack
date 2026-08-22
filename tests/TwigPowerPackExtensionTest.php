<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack;

use Mediagone\Twig\PowerPack\Tags\RegisterRegistry;
use Mediagone\Twig\PowerPack\TwigPowerPackExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use function json_encode;


/**
 * @covers \Mediagone\Twig\PowerPack\TwigPowerPackExtension
 */
final class TwigPowerPackExtensionTest extends TestCase
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
        $this->env->addExtension(new TwigPowerPackExtension());
        
        RegisterRegistry::clear();
    }


    /**
     * The tags declared by the extension, read from the extension itself: Environment::getTags() was
     * removed in Twig 3, and the tag list is not exposed anywhere else.
     *
     * @return string[]
     */
    private function getExtensionTags() : array
    {
        $parsers = $this->env->getExtension(TwigPowerPackExtension::class)->getTokenParsers();

        return array_map(static fn($parser) => $parser->getTag(), $parsers);
    }
    
    
    
    //========================================================================================================
    // REQUIRE
    //========================================================================================================
    
    public function test_require_tag_is_enabled() : void
    {
        self::assertContains('expect', $this->getExtensionTags());
    }
    
    
    
    //========================================================================================================
    // REGISTER
    //========================================================================================================
    
    public function test_register_tag_is_enabled() : void
    {
        self::assertContains('register', $this->getExtensionTags());
    }
    
    
    
    //========================================================================================================
    // REGISTRY
    //========================================================================================================
    
    public function test_registry_function_is_enabled() : void
    {
        self::assertNotNull($this->env->getFunction('registry'));
    }
    
    
    public function test_registry_function_is_working_fine() : void
    {
        $result = $this->env->createTemplate("{{ registry('css')|length }}")->render();
        self::assertSame('0', $result);
        
        RegisterRegistry::register('css', 'styles.css', null, 0);
        
        $result = $this->env->createTemplate("{{ registry('css')|length }}")->render();
        self::assertSame('1', $result);
    }
    
    
    
    //========================================================================================================
    // NEW
    //========================================================================================================
    
    public function test_new_function_is_enabled() : void
    {
        self::assertNotNull($this->env->getFunction('new'));
    }
    
    
    public function test_new_function_is_working_fine() : void
    {
        $result = $this->env->createTemplate("{{ new('Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\FooWithArgument', 'some string').arg }}")->render();
        
        self::assertSame('some string', $result);
    }
    
    
    public function test_new_function_is_working_fine_with_multiple_args() : void
    {
        $resultString = $this->env->createTemplate("{{ new('Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\FooWithTwoArguments', 'some string', ['a', 'b']).stringArg }}")->render();
        $resultArray = $this->env->createTemplate("{{ new('Tests\\\\Mediagone\\\\Twig\\\\PowerPack\\\\FooWithTwoArguments', 'some string', ['a', 'b']).arrayArg|length }}")->render();
        
        self::assertSame('some string', $resultString);
        self::assertSame('2', $resultArray);
    }
    
    
    //========================================================================================================
    // FILTERS
    //========================================================================================================
    
    public function test_json_decode_filter_is_working_fine() : void
    {
        $result = $this->env->createTemplate("{{ (json|json_decode).msg }}")->render(['json' => json_encode(['msg' => 'Hello world'])]);
        
        self::assertSame('Hello world', $result);
    }
    
    
    
    
}
