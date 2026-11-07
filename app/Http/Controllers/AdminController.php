<?php

namespace App\Http\Controllers;

use App\Support\SampleData;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'pending' => SampleData::pendingCount(),
            'applications' => array_slice(SampleData::applications(), 0, 3),
            'lantsas' => SampleData::lantsaPreview(),
            'report' => SampleData::dailyReport(),
        ]);
    }

    // ---------------------------------------------------------------
    // OPERATOR APPROVALS
    // ---------------------------------------------------------------

    public function approvals()
    {
        return view('admin.approvals', [
            'applications' => SampleData::applications(),
            'pending' => SampleData::pendingCount(),
        ]);
    }

    public function approvalShow(string $id)
    {
        $apps = SampleData::applications();
        $app = $apps[$id] ?? null;
        abort_if($app === null, 404);

        return view('admin.approval-review', [
            'app' => $app,
            'documents' => SampleData::documents(),
        ]);
    }

    public function approvalDecide(Request $request, string $id)
    {
        $apps = SampleData::applications();
        abort_if(! isset($apps[$id]), 404);

        $data = $request->validate([
            'decision' => 'required|in:Approved,Needs info,Rejected',
            'note' => 'nullable|string|max:500',
        ]);

        $decisions = $request->session()->get('decisions', []);
        $decisions[$id] = $data['decision'];
        $request->session()->put('decisions', $decisions);

        if (! empty($data['note'])) {
            $notes = $request->session()->get('decision_notes', []);
            $notes[$id] = $data['note'];
            $request->session()->put('decision_notes', $notes);
        }

        return redirect()
            ->route('admin.approvals.show', $id)
            ->with('status', 'Application '.$id.' updated to "'.$data['decision'].'".');
    }

    // ---------------------------------------------------------------
    // OPERATORS DIRECTORY
    // ---------------------------------------------------------------

    public function operators()
    {
        return view('admin.operators', [
            'operators' => SampleData::operators(),
            'pending' => SampleData::pendingCount(),
        ]);
    }

    public function operatorsExport()
    {
        $rows = [['Business', 'Assigned Vessel', 'Primary Route', 'Status']];
        foreach (SampleData::operators() as $op) {
            $rows[] = [$op['business'], $op['vessel'], $op['route'], $op['status']];
        }

        return $this->csvDownload('sursea-operators.csv', $rows);
    }

    // ---------------------------------------------------------------
    // NETWORK REPORTS
    // ---------------------------------------------------------------

    public function reports()
    {
        return view('admin.reports', [
            'routes' => SampleData::routeActivity(),
            'report' => SampleData::dailyReport(),
        ]);
    }

    public function reportsExport()
    {
        $rows = [['Route', 'Completed Trips', 'Passengers Checked In', 'Cargo Items']];
        foreach (SampleData::routeActivity() as $r) {
            $rows[] = $r;
        }
        $rows[] = ['TOTAL', 18, 624, 86];

        return $this->csvDownload('sursea-daily-report.csv', $rows);
    }

    // ---------------------------------------------------------------
    // WORKSPACE SETTINGS
    // ---------------------------------------------------------------

    public function settings()
    {
        return view('admin.settings', [
            'settings' => session('workspace_settings', SampleData::defaultSettings()),
        ]);
    }

    public function settingsSave(Request $request)
    {
        $data = $request->validate([
            'workspace_name' => 'required|string|max:100',
            'service_area' => 'required|string|max:100',
            'timezone' => 'required|string|max:100',
            'support_contact' => 'required|email|max:100',
        ]);

        session(['workspace_settings' => $data]);

        return redirect()->route('admin.settings')->with('status', 'Workspace settings saved.');
    }

    // ---------------------------------------------------------------
    // MODULES: LANTSA & ROUTES, TRIP SCHEDULE, MANIFEST, CARGO, NOTIFICATIONS
    // ---------------------------------------------------------------

    public function module(string $module)
    {
        return match ($module) {
            'lantsa-routes' => view('admin.lantsa-routes', [
                'lantsas' => SampleData::lantsaRecords(),
            ]),
            'trip-schedule' => view('admin.trip-schedule', [
                'trips' => SampleData::tripScheduleRecords(),
                'lantsas' => SampleData::lantsaRecords(),
            ]),
            'passenger-manifest' => view('admin.passenger-manifest', [
                'manifests' => SampleData::manifestRecords(),
            ]),
            'cargo' => view('admin.cargo', [
                'cargos' => SampleData::cargoRecords(),
            ]),
            default => $this->genericModule($module),
        };
    }

    private function genericModule(string $module)
    {
        $config = SampleData::module('admin', $module);
        abort_if($config === null, 404);

        return view('panel.module', ['m' => $config]);
    }

    // ---------- LANTSA & ROUTES ACTIONS ----------

    public function lantsaStore(Request $request)
    {
        $data = $request->validate([
            'lantsa' => 'required|string|max:100',
            'operator' => 'required|string|max:100',
            'route' => 'required|string|max:100',
            'seats' => 'required|integer|min:10|max:200',
        ]);

        $custom = session('custom_lantsas', []);
        array_unshift($custom, [
            'id' => 'LR-'.(10 + count($custom)),
            'lantsa' => $data['lantsa'],
            'operator' => $data['operator'],
            'route' => $data['route'],
            'port' => 'Surigao City Port',
            'seats' => (int) $data['seats'],
            'cargo_limit' => '40 items',
            'fare' => 150,
            'status' => 'Active',
        ]);
        session(['custom_lantsas' => $custom]);

        return redirect()->route('admin.module', 'lantsa-routes')
            ->with('status', $data['lantsa'].' was registered and assigned to '.$data['route'].'.');
    }

    public function lantsaStatus(Request $request, string $id)
    {
        $data = $request->validate(['status' => 'required|in:Active,Suspended']);
        $statuses = session('lantsa_statuses', []);
        $statuses[$id] = $data['status'];
        session(['lantsa_statuses' => $statuses]);

        return redirect()->route('admin.module', 'lantsa-routes')
            ->with('status', 'Vessel operating status updated to '.$data['status'].'.');
    }

    // ---------- TRIP SCHEDULE ACTIONS ----------

    public function tripStore(Request $request)
    {
        $data = $request->validate([
            'lantsa' => 'required|string',
            'route' => 'required|string',
            'departure' => 'required|string|max:30',
            'status' => 'required|in:Scheduled,Boarding,On Going',
        ]);

        [$lantsa, $operator, $seats] = array_pad(explode('|', $data['lantsa']), 3, '40');
        [$route, $port] = array_pad(explode('|', $data['route']), 2, 'Surigao City Port');

        $custom = session('custom_trips', []);
        array_unshift($custom, [
            'id' => 'TS-'.(200 + count($custom)),
            'departure' => $data['departure'],
            'arrival' => '12:00 PM',
            'date' => 'Today, Sept 26',
            'route' => $route,
            'port' => $port,
            'lantsa' => $lantsa,
            'operator' => $operator,
            'capacity' => (int) $seats,
            'reserved' => 0,
            'seats_left' => ((int) $seats).' left',
            'cargo_count' => 0,
            'status' => $data['status'],
            'op_status' => $data['status'],
        ]);
        session(['custom_trips' => $custom]);

        return redirect()->route('admin.module', 'trip-schedule')
            ->with('status', 'New crossing scheduled for '.$lantsa.' on '.$route.' at '.$data['departure'].'.');
    }

    public function tripStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|in:Scheduled,Boarding,On Going,Completed,Cancelled',
        ]);

        $statuses = session('trip_statuses', []);
        $statuses[$id] = $data['status'];
        session(['trip_statuses' => $statuses]);

        return redirect()->route('admin.module', 'trip-schedule')
            ->with('status', 'Trip '.$id.' status updated to '.$data['status'].'.');
    }

    public function tripsExport()
    {
        $rows = [['Trip ID', 'Departure', 'Route', 'Port', 'Lantsa', 'Operator', 'Reserved', 'Capacity', 'Seats Left', 'Status']];
        foreach (SampleData::tripScheduleRecords() as $t) {
            $rows[] = [$t['id'], $t['departure'], $t['route'], $t['port'], $t['lantsa'], $t['operator'], $t['reserved'], $t['capacity'], $t['seats_left'], $t['status']];
        }

        return $this->csvDownload('sursea-trip-schedules.csv', $rows);
    }

    // ---------- PASSENGER MANIFEST ACTIONS ----------

    public function manifestStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'required|string|max:20',
            'route' => 'required|string|max:100',
            'lantsa' => 'required|string|max:100',
            'passengers' => 'required|integer|min:1|max:20',
            'status' => 'required|in:Checked in,Reserved,Waiting',
        ]);

        $custom = session('custom_manifests', []);
        $ref = sprintf('0000-%05d', 20 + count($custom));
        array_unshift($custom, [
            'ref' => $ref,
            'name' => $data['name'],
            'contact' => $data['contact'],
            'route' => $data['route'],
            'departure' => '12:00 PM',
            'date' => '26-09-2026',
            'lantsa' => $data['lantsa'],
            'passengers' => (int) $data['passengers'],
            'cargo_qty' => 0,
            'total' => ((int) $data['passengers']) * 150,
            'status' => $data['status'],
        ]);
        session(['custom_manifests' => $custom]);

        return redirect()->route('admin.module', 'passenger-manifest')
            ->with('status', 'Passenger '.$data['name'].' added to manifest (Reference: '.$ref.').');
    }

    public function manifestVerify(Request $request)
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
            return redirect()->route('admin.module', 'passenger-manifest')
                ->with('status', 'Reference code "'.$ref.'" was not found in today’s manifest.');
        }

        $statuses = session('manifest_statuses', []);
        $statuses[$found['ref']] = 'Checked in';
        session(['manifest_statuses' => $statuses]);

        return redirect()->route('admin.module', 'passenger-manifest')
            ->with('status', 'Verified '.$found['ref'].': '.$found['name'].' ('.$found['passengers'].' pax) is now Checked in for '.$found['lantsa'].'.');
    }

    public function manifestStatus(Request $request, string $ref)
    {
        $data = $request->validate(['status' => 'required|in:Checked in,Reserved,Waiting,Moved to seat']);

        $statuses = session('manifest_statuses', []);
        $statuses[$ref] = $data['status'];
        session(['manifest_statuses' => $statuses]);

        return redirect()->route('admin.module', 'passenger-manifest')
            ->with('status', 'Reservation '.$ref.' updated to "'.$data['status'].'".');
    }

    public function manifestExport()
    {
        $rows = [['Reference Code', 'Passenger Name', 'Contact', 'Route', 'Departure', 'Lantsa', 'Passengers', 'Cargo Qty', 'Total Paid', 'Status']];
        foreach (SampleData::manifestRecords() as $m) {
            $rows[] = [$m['ref'], $m['name'], $m['contact'], $m['route'], $m['departure'], $m['lantsa'], $m['passengers'], $m['cargo_qty'], $m['total'], $m['status']];
        }

        return $this->csvDownload('sursea-passenger-manifest.csv', $rows);
    }

    // ---------- CARGO ACTIONS ----------

    public function cargoStore(Request $request)
    {
        $data = $request->validate([
            'sender' => 'required|string|max:100',
            'sender_contact' => 'required|string|max:20',
            'receiver' => 'required|string|max:100',
            'receiver_contact' => 'required|string|max:20',
            'items' => 'required|string|max:150',
            'qty' => 'required|integer|min:1|max:99',
            'lantsa' => 'required|string|max:100',
            'route' => 'required|string|max:100',
        ]);

        $custom = session('custom_cargos', []);
        $ref = sprintf('0000-%05d', 120 + count($custom));
        array_unshift($custom, [
            'ref' => $ref,
            'sender' => $data['sender'],
            'sender_contact' => $data['sender_contact'],
            'receiver' => $data['receiver'],
            'receiver_contact' => $data['receiver_contact'],
            'items' => $data['items'],
            'qty' => (int) $data['qty'],
            'route' => $data['route'],
            'departure' => '12:00 PM',
            'lantsa' => $data['lantsa'],
            'fee' => ((int) $data['qty']) * 25,
            'status' => 'Pending',
        ]);
        session(['custom_cargos' => $custom]);

        return redirect()->route('admin.module', 'cargo')
            ->with('status', 'Cargo delivery '.$ref.' registered for '.$data['lantsa'].'.');
    }

    public function cargoStatus(Request $request, string $ref)
    {
        $data = $request->validate(['status' => 'required|in:Pending,In transit,Delivered']);

        $statuses = session('cargo_statuses', []);
        $statuses[$ref] = $data['status'];
        session(['cargo_statuses' => $statuses]);

        return redirect()->route('admin.module', 'cargo')
            ->with('status', 'Cargo '.$ref.' status updated to "'.$data['status'].'".');
    }

    public function cargoExport()
    {
        $rows = [['Tracking ID', 'Sender', 'Sender Contact', 'Receiver', 'Receiver Contact', 'Items', 'Qty', 'Route', 'Lantsa', 'Status']];
        foreach (SampleData::cargoRecords() as $c) {
            $rows[] = [$c['ref'], $c['sender'], $c['sender_contact'], $c['receiver'], $c['receiver_contact'], $c['items'], $c['qty'], $c['route'], $c['lantsa'], $c['status']];
        }

        return $this->csvDownload('sursea-cargo-deliveries.csv', $rows);
    }

    // ---------------------------------------------------------------
    // CSV HELPER
    // ---------------------------------------------------------------

    private function csvDownload(string $filename, array $rows)
    {
        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
