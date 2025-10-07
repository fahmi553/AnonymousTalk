<?php

namespace App\Helpers;

class UsernameGenerator
{
    public static function generate()
    {
        $adjectives = [
            'Agile',
            'Bold',
            'Calm',
            'Daring',
            'Eager',
            'Fearless',
            'Gentle',
            'Honest',
            'Intrepid',
            'Joyful',
            'Kind',
            'Loyal',
            'Mighty',
            'Nimble',
            'Optimistic',
            'Patient',
            'Quick',
            'Resilient',
            'Strong',
            'Trusty',
            'Unique',
            'Valiant',
            'Wise',
            'Xenial',
            'Young',
            'Zesty'
        ];

        $animals = [
            'Antelope',
            'Bear',
            'Cat',
            'Dog',
            'Eagle',
            'Falcon',
            'Giraffe',
            'Horse',
            'Iguana',
            'Jaguar',
            'Koala',
            'Lion',
            'Monkey',
            'Newt',
            'Owl',
            'Penguin',
            'Quail',
            'Rabbit',
            'Snake',
            'Tiger',
            'Urial',
            'Vulture',
            'Wolf',
            'Xerus',
            'Yak',
            'Zebra'
        ];

        $adjective = $adjectives[array_rand($adjectives)];
        $animal = $animals[array_rand($animals)];
        $number = rand(10, 999);

        return $adjective . $animal . $number;
    }
}
