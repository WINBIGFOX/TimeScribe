<?php

declare(strict_types=1);

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Jobs\CalculateWeekBalance;
use App\Services\Import\ClockifyImportService;
use Inertia\Inertia;
use Native\Laravel\Dialog;
use Native\Laravel\Facades\Alert;

class ClockifyController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::modal('ImportExport/Clockify/Create', [
            'submit_route' => route('import.clockify.store'),
        ])->baseRoute('import-export.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $clockifyCsvPath = Dialog::new()->asSheet()
            ->filter('Clockify CSV', ['csv'])
            ->files()
            ->button(__('app.restoring'))
            ->open();

        if ($clockifyCsvPath === null) {
            return back();
        }

        try {
            new ClockifyImportService($clockifyCsvPath)->import();
        } catch (\Throwable) {
            Alert::error(
                __('app.import failed'),
                __('app.an error occurred while importing the file. please check the file format and try again.')
            );

            return redirect()->route('import-export.index');
        }

        Alert::type('info')
            ->title(__('app.import successful'))
            ->show(__('app.the data was successfully imported into timescribe.'));

        CalculateWeekBalance::dispatch();

        return redirect()->route('import-export.index');
    }
}
