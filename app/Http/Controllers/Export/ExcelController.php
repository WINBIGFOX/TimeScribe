<?php

declare(strict_types=1);

namespace App\Http\Controllers\Export;

use App\Http\Controllers\Controller;
use App\Services\Export\ExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Native\Desktop\Dialog;
use Native\Desktop\Facades\Alert;
use Native\Desktop\Support\Environment;

class ExcelController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $savePath = Dialog::new()->asSheet()
            ->defaultPath('TimeScribe-Export.xlsx')
            ->button(__('app.save'))
            ->save();

        if ($savePath === null) {
            return back();
        }

        try {
            (new ExportService)->exportAsExcel($savePath);
        } catch (\Throwable) {
            Alert::error(
                __('app.export failed'),
                __('app.an error occurred while exporting the data. please try again.')
            );
        }

        Alert::type('info')
            ->title(__('app.export successful'))
            ->show(__('app.the data was successfully exported from timescribe.'));

        if (Environment::isWindows()) {
            shell_exec('explorer "'.pathinfo($savePath, PATHINFO_DIRNAME).'"');
        } else {
            shell_exec('open "'.pathinfo($savePath, PATHINFO_DIRNAME).'"');
        }

        return back();
    }
}
