<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AiToolsController extends Controller
{
    public function captionGenerator()
    {
        return Inertia::render('Tools/Ai/CaptionGenerator');
    }

    public function productDescription()
    {
        return Inertia::render('Tools/Ai/ProductDescription');
    }
}
