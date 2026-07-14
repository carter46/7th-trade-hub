<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class DevUiController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(app()->environment('local'), 404);

        $icons = collect(File::files(resource_path('icons')))
            ->map(fn ($f) => pathinfo($f->getFilename(), PATHINFO_FILENAME))
            ->sort()
            ->values()
            ->all();

        return view('dev.ui', [
            'icons' => $icons,
        ]);
    }
}
