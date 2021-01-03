<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack;

use Mediagone\Twig\PowerPack\Tags\RegisterRegistry;
use Mediagone\Twig\PowerPack\Tags\RegisterTokenParser;
use Mediagone\Twig\PowerPack\Tags\RequireTokenParser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


final class TwigPowerPackExtension extends AbstractExtension
{
    //========================================================================================================
    //
    //========================================================================================================
    
    public function getName() : string
    {
        return 'TwigPowerPackExtension';
    }
    
    
    public function getFunctions() : array
    {
        return [
            new TwigFunction('registry', [new RegisterRegistry(), 'read']),
        ];
    }
    
    
    public function getTokenParsers() : array
    {
        return [
            new RegisterTokenParser(),
            new RequireTokenParser(),
        ];
    }
    
    
    
}
