<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;

class ImportExportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('ImportExport/Index');
    }
}
