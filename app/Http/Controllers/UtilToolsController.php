<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
class UtilToolsController extends Controller
{
    public function tasbihDigital(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/utility/TasbihDigital');
    }

    public function papanSkor(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/utility/PapanSkor');
    }
    public function kocokDadu(Request $request): \Inertia\Response
    {
            return Inertia::render('tools/utility/KocokDadu');
    }
}
