<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack;

use Mediagone\Twig\PowerPack\Tags\RegisterRegistry;
use Mediagone\Twig\PowerPack\Tags\RegisterTokenParser;
use Mediagone\Twig\PowerPack\Tags\ExpectTokenParser;
use Mediagone\Twig\PowerPack\Functions\NewClass;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function json_decode;


final class TwigPowerPackExtension extends AbstractExtension
{
    //========================================================================================================
    //
    //========================================================================================================
    
    public function getName() : string
    {
        return 'TwigPowerPackExtension';
    }
    
    
    public function getFilters() : array
    {
        return [
            new TwigFilter('json_decode', fn($json) => json_decode($json, false, 512, JSON_THROW_ON_ERROR)),
        ];
    }
    
    
    public function getFunctions() : array
    {
        return [
            new TwigFunction('registry', [new RegisterRegistry(), 'read']),
            new TwigFunction('new', [new NewClass(), 'createInstance']),
        ];
    }
    
    
    public function getTokenParsers() : array
    {
        return [
            new RegisterTokenParser(),
            new ExpectTokenParser(),
        ];
    }
    
    
    
}
