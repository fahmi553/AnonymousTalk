<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $defaults = [
            'General'       => '#0d6efd',
            'Help'          => '#20c997',
            'News'          => '#dc3545',
            'Advice'        => '#198754',
            'Experiences'   => '#fd7e14',
            'Off-Topic'     => '#6f42c1',
            'Entertainment' => '#d63384',
            'Technology'    => '#0dcaf0',
            'Gaming'        => '#ffc107',
            'Sports'        => '#6610f2',
        ];

        foreach ($defaults as $name => $color) {
            Category::updateOrCreate(
                ['name' => $name],
                ['color_code' => $color]
            );
        }
    }
}
