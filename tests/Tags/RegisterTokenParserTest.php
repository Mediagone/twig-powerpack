<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack\Tags;

use Mediagone\Twig\PowerPack\Tags\RegisterRegistry;
use Mediagone\Twig\PowerPack\Tags\RegisterTokenParser;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Loader\LoaderInterface;
use function array_keys;


/**
 * @covers \Mediagone\Twig\PowerPack\Tags\RegisterTokenParser
 */
final class RegisterTokenParserTest extends TestCase
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
        
        $this->env->addTokenParser(new RegisterTokenParser());
        
        RegisterRegistry::clear();
    }
    
    
    
    //========================================================================================================
    // SIMPLE DATA
    //========================================================================================================
    
    public function test_can_register_data_in_different_registries() : void
    {
        self::assertCount(0, RegisterRegistry::read('styles'));
        
        $this->env->createTemplate("{% register '/styles.css' in 'styles' %}")->render();
        $this->env->createTemplate("{% register '/scripts.js' in 'scripts' %}")->render();
        $this->env->createTemplate("{% register '/scripts_2.js' in 'scripts' %}")->render();
        
        $styles = RegisterRegistry::read('styles');
        self::assertCount(1, $styles);
        self::assertTrue(isset($styles[0]));
        self::assertSame('/styles.css', $styles[0]);
        
        $scripts = RegisterRegistry::read('scripts');
        self::assertCount(2, $scripts);
        self::assertTrue(isset($scripts[0]));
        self::assertTrue(isset($scripts[1]));
        self::assertSame('/scripts.js', $scripts[0]);
        self::assertSame('/scripts_2.js', $scripts[1]);
    }
    
    
    public function test_can_infer_registry_name_from_path() : void
    {
        self::assertCount(0, RegisterRegistry::read('css'));
        
        $this->env->createTemplate("{% register '/styles.css' %}")->render();
        
        $css = RegisterRegistry::read('css');
        self::assertCount(1, $css);
        self::assertTrue(isset($css[0]));
        self::assertSame('/styles.css', $css[0]);
    }
    
    
    public function test_registry_name_is_mandatory_if_not_inferable_from_path() : void
    {
        $this->expectException(SyntaxError::class);
        $this->env->createTemplate("{% register 'this_is_not_a_path' %}")->render();
    }
    
    
    public function test_same_data_can_be_registered_multiple_times() : void
    {
        self::assertCount(0, RegisterRegistry::read('css'));
        
        $this->env->createTemplate("{% register '/styles.css' %}")->render();
        $this->env->createTemplate("{% register '/styles.css' %}")->render();
        
        $css = RegisterRegistry::read('css');
        self::assertCount(2, $css);
        self::assertTrue(isset($css[0]));
        self::assertTrue(isset($css[1]));
        self::assertSame('/styles.css', $css[0]);
        self::assertSame('/styles.css', $css[1]);
    }
    
    
    public function test_same_data_can_be_registered_only_once() : void
    {
        self::assertCount(0, RegisterRegistry::read('css'));
        
        $this->env->createTemplate("{% register once '/styles.css' %}")->render();
        $this->env->createTemplate("{% register once '/styles.css' %}")->render();
        
        $css = RegisterRegistry::read('css');
        self::assertCount(1, $css);
        self::assertTrue(isset($css['/styles.css']));
        self::assertSame('/styles.css', $css['/styles.css']);
    }
    
    
    
    //========================================================================================================
    // BODY DATA
    //========================================================================================================
    
    public function test_can_parse_body_data() : void
    {
        $this->env->createTemplate("{% register in 'messages' %}{{ msg }}{% endregister %}")->render(['msg' => 'Hello world']);
        $this->env->createTemplate("{% register in 'messages' %}{{ msg }}{% endregister %}")->render(['msg' => 'Buzz!']);
        
        $messages = RegisterRegistry::read('messages');
        self::assertCount(2, $messages);
        self::assertTrue(isset($messages[0]));
        self::assertTrue(isset($messages[1]));
        self::assertSame('Hello world', $messages[0]);
        self::assertSame('Buzz!', $messages[1]);
    }
    
    
    public function test_can_register_body_multiple_times() : void
    {
        $this->env->createTemplate("{% register in 'messages' %}{{ msg }}{% endregister %}")->render(['msg' => 'Hello world']);
        $this->env->createTemplate("{% register in 'messages' %}{{ msg }}{% endregister %}")->render(['msg' => 'Hello world']);
        
        $messages = RegisterRegistry::read('messages');
        self::assertCount(2, $messages);
        self::assertTrue(isset($messages[0]));
        self::assertTrue(isset($messages[1]));
        self::assertSame('Hello world', $messages[0]);
        self::assertSame('Hello world', $messages[1]);
    }
    
    
    public function test_can_register_body_only_once() : void
    {
        $this->env->createTemplate("{% register once in 'messages' %}{{ msg }}{% endregister %}")->render(['msg' => 'Hello world']);
        $this->env->createTemplate("{% register once in 'messages' %}{{ msg }}{% endregister %}")->render(['msg' => 'Hello world']);
        
        $messages = RegisterRegistry::read('messages');
        self::assertCount(1, $messages);
        self::assertTrue(isset($messages['Hello world']));
        self::assertSame('Hello world', $messages['Hello world']);
    }
    
    
    
    //========================================================================================================
    // PRIORITY
    //========================================================================================================
    
    public function test_can_register_data_with_priority() : void
    {
        self::assertCount(0, RegisterRegistry::read('js'));
        
        $this->env->createTemplate("{% register 'third.js' priority 3 %}")->render();
        $this->env->createTemplate("{% register 'second.js' priority 2 %}")->render();
        $this->env->createTemplate("{% register 'first.js' priority 1 %}")->render();
        
        $js = RegisterRegistry::read('js');
        self::assertCount(3, $js);
        self::assertSame('first.js', $js[0]);
        self::assertSame('second.js', $js[1]);
        self::assertSame('third.js', $js[2]);
        
        $this->env->createTemplate("{% register 'before.js' priority 0 %}")->render();
        
        $js = RegisterRegistry::read('js');
        self::assertCount(4, $js);
        self::assertSame('before.js', $js[0]);
        self::assertSame('first.js', $js[1]);
        self::assertSame('second.js', $js[2]);
        self::assertSame('third.js', $js[3]);
    }
    
    
    public function test_can_register_unique_data_with_priority() : void
    {
        self::assertCount(0, RegisterRegistry::read('js'));
        
        $this->env->createTemplate("{% register once 'second.js' priority 1 %}")->render();
        $this->env->createTemplate("{% register once 'second.js' priority 2 %}")->render();
        $this->env->createTemplate("{% register once 'first.js' priority 1 %}")->render();
        
        $keys = array_keys(RegisterRegistry::read('js'));
        self::assertCount(2, $keys);
        self::assertSame('first.js', $keys[0]);
        self::assertSame('second.js', $keys[1]);
        
        $this->env->createTemplate("{% register once 'before.js' priority 0 %}")->render();
        
        $keys = array_keys(RegisterRegistry::read('js'));
        self::assertCount(3, $keys);
        self::assertSame('before.js', $keys[0]);
        self::assertSame('first.js', $keys[1]);
        self::assertSame('second.js', $keys[2]);
    }
    
    public function test_null_priority_come_last() : void
    {
        self::assertCount(0, RegisterRegistry::read('js'));
        
        $this->env->createTemplate("{% register 'second.js' %}")->render();
        $this->env->createTemplate("{% register 'first.js' priority 1 %}")->render();
        
        $js = RegisterRegistry::read('js');
        self::assertCount(2, $js);
        self::assertSame('first.js', $js[0]);
        self::assertSame('second.js', $js[1]);
    
        $this->env->createTemplate("{% register 'before.js' priority 0 %}")->render();
    
        $js = RegisterRegistry::read('js');
        self::assertCount(3, $js);
        self::assertSame('before.js', $js[0]);
        self::assertSame('first.js', $js[1]);
        self::assertSame('second.js', $js[2]);
    }
    
    public function test_unique_null_priority_come_last() : void
    {
        self::assertCount(0, RegisterRegistry::read('js'));
        
        $this->env->createTemplate("{% register once 'second.js' %}")->render();
        $this->env->createTemplate("{% register once 'first.js' priority 1 %}")->render();
        
        $js = array_keys(RegisterRegistry::read('js'));
        self::assertCount(2, $js);
        self::assertSame('first.js', $js[0]);
        self::assertSame('second.js', $js[1]);
        
        $this->env->createTemplate("{% register once 'before.js' priority 0 %}")->render();
        
        $js = array_keys(RegisterRegistry::read('js'));
        self::assertCount(3, $js);
        self::assertSame('before.js', $js[0]);
        self::assertSame('first.js', $js[1]);
        self::assertSame('second.js', $js[2]);
    }
    
    
    public function test_can_register_body_with_priority() : void
    {
        $this->env->createTemplate("{% register in 'messages' priority 2 %}{{ msg }}{% endregister %}")->render(['msg' => 'second']);
        $this->env->createTemplate("{% register in 'messages' priority 1 %}{{ msg }}{% endregister %}")->render(['msg' => 'first']);
        
        $messages = RegisterRegistry::read('messages');
        self::assertCount(2, $messages);
        self::assertSame('first', $messages[0]);
        self::assertSame('second', $messages[1]);
    }
    
    
    public function test_can_register_unique_body_with_priority() : void
    {
        $this->env->createTemplate("{% register once in 'messages' priority 2 %}{{ msg }}{% endregister %}")->render(['msg' => 'second']);
        $this->env->createTemplate("{% register once in 'messages' priority 1 %}{{ msg }}{% endregister %}")->render(['msg' => 'first']);
        
        $keys = array_keys(RegisterRegistry::read('messages'));
        self::assertCount(2, $keys);
        self::assertSame('first', $keys[0]);
        self::assertSame('second', $keys[1]);
    }
    
    
    public function test_body_with_null_priority_come_last() : void
    {
        self::assertCount(0, RegisterRegistry::read('messages'));
        
        $this->env->createTemplate("{% register in 'messages' %}{{ msg }}{% endregister %}")->render(['msg' => 'second']);
        $this->env->createTemplate("{% register in 'messages' priority 1 %}{{ msg }}{% endregister %}")->render(['msg' => 'first']);
        
        $js = RegisterRegistry::read('messages');
        self::assertCount(2, $js);
        self::assertSame('first', $js[0]);
        self::assertSame('second', $js[1]);
        
        $this->env->createTemplate("{% register in 'messages' priority 0 %}{{ msg }}{% endregister %}")->render(['msg' => 'before']);
        
        $js = RegisterRegistry::read('messages');
        self::assertCount(3, $js);
        self::assertSame('before', $js[0]);
        self::assertSame('first', $js[1]);
        self::assertSame('second', $js[2]);
    }
    
    
    
}
