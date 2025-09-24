<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::select('category_id', 'name', 'color_code')
            ->orderBy('name')
            ->get();
    }
}
