<?php

namespace App\Http\Controllers;

use App\Support\SampleData;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function dashboard()
    {
        return view('operator.dashboard', [
            'stats' => SampleData::operatorStats(),
            'trips' => SampleData::operatorLiveTrips(),
        ]);
    }

    public function module(string $module)
    {
        return match ($module) {
            'vessel-routes' => view('operator.vessel-routes'),
            'trip-schedule' => view('operator.trip-schedule', [
                'trips' => SampleData::tripScheduleRecords('MV Isla Norte'),
            ]),
            'waiting-list' => view('operator.waiting-list', [
                'waitlist' => SampleData::waitlistRecords(),
            ]),
            'check-in' => view('operator.check-in', [
                'manifests' => SampleData::manifestRecords('MV Isla Norte'),
            ]),
            'manifest' => view('operator.manifest', [
                'manifests' => SampleData::manifestRecords('MV Isla Norte'),
            ]),
            'cargo' => view('operator.cargo', [
                'cargos' => SampleData::cargoRecords('MV Isla Norte'),
            ]),
            'reports' => view('operator.reports'),
            'profile' => view('operator.profile'),
            default => $this->genericModule($module),
        };
    }

    private function genericModule(string $module)
    {
        $config = SampleData::module('operator', $module);
        abort_if($config === null, 404);

        return view('panel.module', ['m' => $config]);
    }

    // ---------- OPERATOR ACTIONS ----------

    public function tripStore(Request $request)
    {
        $data = $request->validate([
            'route' => 'required|string',
            'departure' => 'required|string|max:30',
        ]);

        [$route, $port] = array_pad(explode('|', $data['route']), 2, 'Surigao City Port');

        $custom = session('custom_trips', []);
        array_unshift($custom, [
            'id' => 'TS-'.(300 + count($custom)),
            'departure' => $data['departure'],
            'arrival' => '12:00 PM',
            'date' => 'Today, Sept 26',
            'route' => $route,
            'port' => $port,
            'lantsa' => 'MV Isla Norte',
            'operator' => 'Isla Norte Transport',
            'capacity' => 40,
            'reserved' => 0,
            'seats_left' => '40 left',
            'cargo_count' => 0,
            'status' => 'Scheduled',
            'op_status' => 'Scheduled',
        ]);
        session(['custom_trips' => $custom]);

        return redirect()->route('operator.module', 'trip-schedule')
            ->with('status', 'Added crossing '.$route.' at '.$data['departure'].' for MV Isla Norte.');
    }

    public function tripStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|in:Available,Almost Full,Boarding,On Going,Scheduled,Completed',
        ]);

        $statuses = session('trip_statuses', []);
        $statuses[$id] = $data['status'];
        session(['trip_statuses' => $statuses]);

        return redirect()->route('operator.module', 'trip-schedule')
            ->with('status', 'Trip status updated to '.$data['status'].'.');
    }

    public function waitlistPromote(string $ref)
    {
        $wlStatuses = session('waitlist_statuses', []);
        $wlStatuses[$ref] = 'Moved to seat';

        $manifestStatuses = session('manifest_statuses', []);
        $manifestStatuses[$ref] = 'Moved to seat';

        session([
            'waitlist_statuses' => $wlStatuses,
            'manifest_statuses' => $manifestStatuses,
        ]);

        return redirect()->route('operator.module', 'waiting-list')
            ->with('status', 'Passenger '.$ref.' moved from waiting list to a confirmed seat and notified.');
    }

    public function checkinVerify(Request $request)
    {
        $data = $request->validate(['ref' => 'required|string|max:30']);
        $ref = trim($data['ref']);

        $found = null;
        foreach (SampleData::manifestRecords() as $m) {
            if (strcasecmp($m['ref'], $ref) === 0) {
                $found = $m;
                break;
            }
        }

        if (! $found) {
            return redirect()->route('operator.module', 'check-in')
                ->with('status', 'Reservation reference "'.$ref.'" not found.');
        }

        $statuses = session('manifest_statuses', []);
        $statuses[$found['ref']] = 'Checked in';
        session(['manifest_statuses' => $statuses]);

        return redirect()->route('operator.module', 'check-in')
            ->with('status', 'Checked in '.$found['name'].' (Ref: '.$found['ref'].', '.$found['passengers'].' passenger/s).');
    }

    public function manifestExport()
    {
        $rows = [['Reference', 'Passenger', 'Contact', 'Route', 'Passengers', 'Status']];
        foreach (SampleData::manifestRecords('MV Isla Norte') as $m) {
            $rows[] = [$m['ref'], $m['name'], $m['contact'], $m['route'], $m['passengers'], $m['status']];
        }

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'mv-isla-norte-manifest.csv', ['Content-Type' => 'text/csv']);
    }

    public function cargoStore(Request $request)
    {
        $data = $request->validate([
            'sender' => 'required|string|max:100',
            'receiver' => 'required|string|max:100',
            'items' => 'required|string|max:150',
            'qty' => 'required|integer|min:1|max:99',
        ]);

        $custom = session('custom_cargos', []);
        $ref = sprintf('0000-%05d', 150 + count($custom));
        array_unshift($custom, [
            'ref' => $ref,
            'sender' => $data['sender'],
            'sender_contact' => '09123123123',
            'receiver' => $data['receiver'],
            'receiver_contact' => '09123123123',
            'items' => $data['items'],
            'qty' => (int) $data['qty'],
            'route' => 'Surigao City → Dinagat',
            'departure' => '12:00 PM',
            'lantsa' => 'MV Isla Norte',
            'fee' => ((int) $data['qty']) * 25,
            'status' => 'Pending',
        ]);
        session(['custom_cargos' => $custom]);

        return redirect()->route('operator.module', 'cargo')
            ->with('status', 'Cargo '.$ref.' recorded for MV Isla Norte.');
    }

    public function cargoStatus(Request $request, string $ref)
    {
        $data = $request->validate(['status' => 'required|in:Pending,In transit,Delivered']);

        $statuses = session('cargo_statuses', []);
        $statuses[$ref] = $data['status'];
        session(['cargo_statuses' => $statuses]);

        return redirect()->route('operator.module', 'cargo')
            ->with('status', 'Cargo '.$ref.' updated to "'.$data['status'].'".');
    }

    public function reportsExport()
    {
        $rows = [
            ['Route', 'Completed Trips', 'Passengers', 'Cargo Items'],
            ['Surigao City -> Dinagat', 1, 40, 12],
            ['Surigao City -> Cabungaan', 1, 56, 17],
            ['Surigao City -> Cagdianao', 0, 0, 0],
            ['Surigao City -> Del Carmen', 0, 0, 0],
            ['TOTAL', 2, 96, 29],
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'mv-isla-norte-daily-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function profileSave(Request $request)
    {
        $data = $request->validate([
            'business' => 'required|string|max:100',
            'lantsa' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'contact' => 'required|string|max:20',
            'address' => 'required|string|max:150',
        ]);

        $data['service'] = 'Passenger & cargo';
        $data['seats'] = '40';

        session(['operator_profile' => $data]);

        return redirect()->route('operator.module', 'profile')
            ->with('status', 'Business profile updated.');
    }
}
