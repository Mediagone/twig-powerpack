<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Tags;

use function usort;
use function uasort;


final class RegisterRegistry
{
    //========================================================================================================
    // Properties
    //========================================================================================================
    
    private static array $registry = [];
    
    
    
    //========================================================================================================
    // Methods
    //========================================================================================================
    
    public static function register(string $registry, string $data, ?string $key, ?int $priority) : void
    {
        if (! isset(self::$registry[$registry])) {
            self::$registry[$registry] = [];
        }
        
        if ($key === null) {
            self::$registry[$registry][] = (object)['value' => $data, 'priority' => $priority];
            usort(self::$registry[$registry], [self::class, 'sortByPriority']);
        }
        else {
            self::$registry[$registry][$data] = (object)['value' => $data, 'priority' => $priority];
            uasort(self::$registry[$registry], [self::class, 'sortByPriority']);
        }
    }
    
    public static function sortByPriority(object $a, object $b) : int
    {
        $a = $a->priority;
        $b = $b->priority;
        
        if ($a === null && $b === null) {
            return 0;
        }
        if ($a === null) {
            return 1;
        }
        if ($b === null) {
            return -1;
        }
        
        return $a <=> $b;
    }
    
    
    public static function read(string $registry) : array
    {
        return array_map(static fn($i) => $i->value, self::$registry[$registry] ?? []);
    }
    
    
    public static function clear() : void
    {
        self::$registry = [];
    }
    
    
    
}
