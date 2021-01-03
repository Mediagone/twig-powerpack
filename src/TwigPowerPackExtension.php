<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack;

use Mediagone\Twig\PowerPack\Tags\RegisterTokenParser;
use Mediagone\Twig\PowerPack\Tags\RequireTokenParser;
use Twig\Extension\AbstractExtension;


final class TwigPowerPackExtension extends AbstractExtension
{
    //========================================================================================================
    //
    //========================================================================================================
    
    public function getName() : string
    {
        return 'TwigPowerPackExtension';
    }
    
    
    public function getTokenParsers() : array
    {
        return [
            new RegisterTokenParser(),
            new RequireTokenParser(),
        ];
    }
    
    
    
}
