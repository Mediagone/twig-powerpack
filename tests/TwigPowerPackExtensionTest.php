<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack;

use Mediagone\Twig\PowerPack\Tags\RegisterRegistry;
use Mediagone\Twig\PowerPack\TwigPowerPackExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\LoaderInterface;


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
        
        RegisterRegistry::register('css', 'styles.css');
        
        $result = $this->env->createTemplate("{{ registry('css')|length }}")->render();
        self::assertSame('1', $result);
    }
    
    
    
}
