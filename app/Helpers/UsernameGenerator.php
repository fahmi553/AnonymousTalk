<?php

namespace App\Helpers;

class UsernameGenerator
{
    public static function generate()
    {
        $adjectives = ['Silent', 'Loud', 'Brave', 'Mysterious', 'Quick', 'Clever', 'Hidden', 'Dark', 'Bright'];
        $animals = ['Fox', 'Otter', 'Hawk', 'Tiger', 'Panda', 'Wolf', 'Owl', 'Bear', 'Eagle'];

        $adjective = $adjectives[array_rand($adjectives)];
        $animal = $animals[array_rand($animals)];
        $number = rand(10, 999);

        return $adjective . $animal . $number;
    }
}
