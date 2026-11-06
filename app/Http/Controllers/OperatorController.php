<?php

namespace App\Http\Controllers;

use App\Support\SampleData;

class OperatorController extends Controller
{
    public function dashboard()
    {
        return view('operator.dashboard', [
            'stats' => SampleData::operatorStats(),
            'trips' => SampleData::operatorLiveTrips(),
        ]);
    }

    // Shows any of the simple table pages (trip-schedule, cargo, etc.)
    public function module(string $module)
    {
        $config = SampleData::module('operator', $module);
        abort_if($config === null, 404);

        return view('panel.module', ['m' => $config]);
    }
}
