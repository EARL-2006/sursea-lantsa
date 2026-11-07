<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;

/**
 * ALL the example data of the prototype lives in this one file.
 * Session state overlays on top so reservations, check-ins, waitlist promotions,
 * trip schedule additions, and cargo updates persist during testing.
 */
class SampleData
{
    // ===============================================================
    // ACCOUNTS (demo logins)
    // ===============================================================

    public static function accounts(): array
    {
        return [
            [
                'username' => 'Robert', 'email' => 'RobertZabala@gmail.com', 'password' => 'password',
                'role' => 'passenger', 'name' => 'Robert Zabala', 'contact' => '09123123123',
            ],
            [
                'username' => 'islanorte', 'email' => 'islanorte@sursea.example', 'password' => 'password',
                'role' => 'operator', 'name' => 'Isla Norte Operator', 'contact' => '09123123123',
                'business' => 'Isla Norte Transport', 'lantsa' => 'MV Isla Norte', 'approved' => true,
            ],
            [
                'username' => 'admin', 'email' => 'admin@sursea.example', 'password' => 'password',
                'role' => 'admin', 'name' => 'Administrator', 'contact' => '+63 917 555 0100',
            ],
        ];
    }

    // Finds an account by username/email + password. Returns null if wrong.
    public static function findAccount(string $login, string $password): ?array
    {
        $login = strtolower($login);

        // 1. demo accounts (plain password, because they are only for testing)
        foreach (self::accounts() as $account) {
            $matches = strtolower($account['username']) === $login || strtolower($account['email']) === $login;
            if ($matches && hash_equals($account['password'], $password)) {
                unset($account['password']);
                return $account;
            }
        }

        // 2. accounts created on the register page (password is hashed)
        foreach (session('registered_accounts', []) as $account) {
            $matches = strtolower($account['username']) === $login || strtolower($account['email']) === $login;
            if ($matches && Hash::check($password, $account['password'])) {
                // Check if admin approved this operator in session
                if (($account['role'] ?? '') === 'operator' && !empty($account['app_id'])) {
                    $decisions = session('decisions', []);
                    if (($decisions[$account['app_id']] ?? '') === 'Approved') {
                        $account['approved'] = true;
                    }
                }
                unset($account['password']);
                return $account;
            }
        }

        return null;
    }

    public static function accountExists(string $username, string $email): bool
    {
        $all = array_merge(self::accounts(), session('registered_accounts', []));

        foreach ($all as $account) {
            if (strtolower($account['username']) === strtolower($username) || strtolower($account['email']) === strtolower($email)) {
                return true;
            }
        }

        return false;
    }

    // Where each role lands after login
    public static function homeFor(string $role): string
    {
        return match ($role) {
            'admin' => '/admin/dashboard',
            'operator' => '/operator/dashboard',
            default => '/',
        };
    }

    // ===============================================================
    // PASSENGER SIDE
    // ===============================================================

    public static function places(): array
    {
        return ['Surigao City', 'Dinagat', 'Del Carmen', 'Libjo', 'San Jose', 'Cabungaan', 'Cagdianao'];
    }

    // The 6 trip cards of the Schedules page
    public static function trips(): array
    {
        $trips = [
            1 => self::makeTrip(1, 'Dinagat', 'Surigao City', 'Dinagat-Surigao', 'MV Isla Norte', '12:00pm', 20, 12, 'Available'),
            2 => self::makeTrip(2, 'Surigao City', 'Cabungaan', 'Surigao-Cabungaan', 'Lantsa Marajaw', '5:00am', 36, 4, 'Almost Full'),
            3 => self::makeTrip(3, 'Surigao City', 'Cagdianao', 'Surigao-Cagdianao', 'MV Isla Norte', '5:00am', 40, 0, 'Full'),
            4 => self::makeTrip(4, 'Del Carmen', 'Surigao City', 'Del Carmen-Surigao', 'Lantsa Marajaw', '4:30pm', 36, 3, 'Almost Full'),
            5 => self::makeTrip(5, 'Libjo', 'Surigao City', 'Libjo-Surigao', 'MV Isla Norte', '12:00pm', 20, 15, 'Available'),
            6 => self::makeTrip(6, 'San Jose', 'Surigao City', 'San Jose-Surigao', 'Lantsa Marajaw', '4:30pm', 40, 0, 'Full'),
        ];

        // Trip 3 already left the port (the Figma shows "Departed" + "Full")
        $trips[3]['departed'] = true;

        // Apply dynamic seat reductions from confirmed passenger reservations
        $bookedSeats = session('trip_booked_seats', []);
        foreach ($bookedSeats as $tripId => $bookedCount) {
            if (isset($trips[$tripId])) {
                $newLeft = max(0, $trips[$tripId]['left'] - (int) $bookedCount);
                $trips[$tripId]['left'] = $newLeft;
                if ($newLeft === 0) {
                    $trips[$tripId]['status'] = 'Full';
                } elseif ($newLeft <= 5) {
                    $trips[$tripId]['status'] = 'Almost Full';
                }
            }
        }

        return $trips;
    }

    // Trips displayed on the Waiting List page (matches the 6 ongoing cards in the PDF design)
    public static function waitingListTrips(): array
    {
        $all = self::trips();
        $list = [];
        foreach ($all as $trip) {
            $trip['capacity_text'] = '25/45';
            $list[] = $trip;
        }
        return $list;
    }

    private static function makeTrip(int $id, string $from, string $to, string $short, string $lantsa, string $time, int $seats, int $left, string $status): array
    {
        return [
            'id' => $id, 'from' => $from, 'to' => $to, 'short' => $short, 'lantsa' => $lantsa,
            'departure' => $time, 'arrival' => '12:00pm', 'seats' => $seats, 'left' => $left,
            'status' => $status, 'departed' => false,
            'date' => '26-09-2026', 'date_long' => 'Sept 26, 2026',
        ];
    }

    // Rows of the "Today's Lantsa Routes & Status" table
    // [time, destination, vessel, seats text, status]
    public static function routeBoard(): array
    {
        return [
            ['07:30 AM', 'Nonoc Island', 'M/B Nonoc Express', '12 Left', 'On Time'],
            ['09:00 AM', 'Hanigad Island', 'M/B Alona Waves', 'Full', 'Waitlist Only'],
            ['10:30 AM', 'Dinagat Port', 'M/B Sea Charger', '5 Left', 'Boarding'],
            ['01:15 PM', 'Hikdop Island', 'M/B Island Voyager', '18 Left', 'Scheduled'],
            ['01:15 PM', 'Hikdop Island', 'M/B Island Voyager', '18 Left', 'Boarding'],
            ['01:15 PM', 'Hikdop Island', 'M/B Island Voyager', '18 Left', 'Boarding'],
            ['01:15 PM', 'Hikdop Island', 'M/B Island Voyager', '18 Left', 'Boarding'],
        ];
    }

    // ===============================================================
    // BADGE COLORS (one place to control every status color)
    // ===============================================================

    public static function badge(string $label): string
    {
        $colors = [
            'Available' => 'green', 'Active' => 'green', 'Completed' => 'green', 'Checked in' => 'green',
            'Approved' => 'green', 'Delivered' => 'green', 'Verified' => 'green', 'Moved to seat' => 'green',
            'Almost Full' => 'amber', 'Pending' => 'amber', 'Pending approval' => 'amber', 'Reserved' => 'amber', 'Unread' => 'amber',
            'On Going' => 'blue', 'Needs info' => 'blue', 'Boarding' => 'blue', 'Departed' => 'blue',
            'Waiting' => 'blue', 'In transit' => 'blue',
            'Full' => 'red', 'Suspended' => 'red', 'Rejected' => 'red', 'Cancelled' => 'red',
            'On Time' => 'teal', 'Waitlist Only' => 'orange',
            'Scheduled' => 'grey', 'Read' => 'grey',
        ];

        return $colors[$label] ?? 'grey';
    }

    // ===============================================================
    // SIDEBAR MENUS (operator + admin)
    // ===============================================================

    public static function menu(string $role): array
    {
        if ($role === 'admin') {
            return [[
                'label' => 'MANAGEMENT',
                'items' => [
                    self::item('Dashboard', 'admin/dashboard', 'dashboard'),
                    self::item('Operator Approvals', 'admin/approvals', 'shield', self::pendingCount()),
                    self::item('Operators', 'admin/operators', 'users'),
                    self::item('Lantsa & Routes', 'admin/lantsa-routes', 'ship'),
                    self::item('Trip Schedule', 'admin/trip-schedule', 'calendar'),
                    self::item('Passenger Manifest', 'admin/passenger-manifest', 'clipboard'),
                    self::item('Cargo', 'admin/cargo', 'box'),
                    self::item('Reports', 'admin/reports', 'chart'),
                    self::item('Notifications', 'admin/notifications', 'bell'),
                    self::item('Settings', 'admin/settings', 'gear'),
                ],
            ]];
        }

        return [
            [
                'label' => 'OPERATIONS',
                'items' => [
                    self::item('Dashboard', 'operator/dashboard', 'dashboard'),
                    self::item('My Vessel & Routes', 'operator/vessel-routes', 'ship'),
                    self::item('Trip Schedule', 'operator/trip-schedule', 'calendar'),
                    self::item('Waiting List', 'operator/waiting-list', 'waiting'),
                    self::item('Check-in', 'operator/check-in', 'check-square'),
                    self::item('Passenger Manifest', 'operator/manifest', 'clipboard'),
                    self::item('Cargo', 'operator/cargo', 'box'),
                    self::item('Reports', 'operator/reports', 'chart'),
                ],
            ],
            [
                'label' => 'ACCOUNT',
                'items' => [
                    self::item('Notifications', 'operator/notifications', 'bell', 3),
                    self::item('Profile', 'operator/profile', 'user'),
                ],
            ],
        ];
    }

    private static function item(string $label, string $path, string $icon, $badge = null): array
    {
        return ['label' => $label, 'path' => $path, 'icon' => $icon, 'badge' => $badge];
    }

    // ===============================================================
    // OPERATOR DASHBOARD
    // ===============================================================

    // [label, number, small text, icon]
    public static function operatorStats(): array
    {
        $manifest = self::manifestRecords('MV Isla Norte');
        $cargo = self::cargoRecords('MV Isla Norte');
        $waitlist = self::waitlistRecords();

        $reservedSeats = array_sum(array_column($manifest, 'passengers'));
        $pendingCargo = count(array_filter($cargo, fn ($c) => $c['status'] === 'Pending'));
        $waitingCount = count(array_filter($waitlist, fn ($w) => $w['status'] === 'Waiting'));

        return [
            ['Trips Today', '6', 'Scheduled crossings', 'calendar'],
            ['Seats Reserved', (string) max(140, $reservedSeats), 'Across today’s trips', 'armchair'],
            ['Waiting List', (string) max(9, $waitingCount), 'Passengers in queue', 'waiting'],
            ['Cargo Pending', (string) max(14, $pendingCargo), 'Items awaiting check-in', 'box'],
        ];
    }

    // [route, via, departure, seats left, status]
    public static function operatorLiveTrips(): array
    {
        $all = self::tripScheduleRecords();
        $mine = array_filter($all, fn ($t) => $t['lantsa'] === 'MV Isla Norte');

        $rows = [];
        foreach ($mine as $t) {
            $rows[] = [
                $t['route'],
                'Via '.$t['port'],
                $t['departure'],
                $t['seats_left'],
                $t['status'],
                $t['id'],
            ];
        }

        return $rows;
    }

    // ===============================================================
    // ADMIN: OPERATOR APPLICATIONS
    // ===============================================================

    // How many applications still wait for a decision (12 at the start)
    public static function pendingCount(): int
    {
        $done = 0;
        foreach (session('decisions', []) as $status) {
            if ($status === 'Approved' || $status === 'Rejected') {
                $done++;
            }
        }

        $extra = count(array_filter(
            session('registered_accounts', []),
            fn ($acc) => ($acc['role'] ?? '') === 'operator'
        ));

        return max(0, 12 + $extra - $done);
    }

    public static function applications(): array
    {
        $apps = [
            'OP-2026-041' => self::makeApp('OP-2026-041', 'Sea Breeze Transport', 'Maria Santos', 'maria@seabreeze.example', '+63 917 555 0142', 'MV Sea Breeze', 48, 'Surigao City → Dinagat', 'Today, 08:20', 'today at 08:20', 'Pending'),
            'OP-2026-040' => self::makeApp('OP-2026-040', 'Dinagat Island Ferries', 'Jose Ramirez', 'jose@dinagatferries.example', '+63 918 555 0177', 'MV Dinagat', 40, 'Surigao City → Dinagat', 'Today, 07:45', 'today at 07:45', 'Pending'),
            'OP-2026-039' => self::makeApp('OP-2026-039', 'Blue Horizon Lines', 'Ana Villanueva', 'ana@bluehorizon.example', '+63 919 555 0123', 'MV Blue Horizon', 36, 'Route under review', 'Yesterday, 16:10', 'yesterday at 16:10', 'Needs info'),
            'OP-2026-038' => self::makeApp('OP-2026-038', 'Hikdop Marine', 'Pedro Lim', 'pedro@hikdopmarine.example', '+63 920 555 0188', 'MV Hikdop Star', 32, 'Surigao City → Hikdop Island', 'Yesterday, 11:30', 'yesterday at 11:30', 'Pending'),
            'OP-2026-037' => self::makeApp('OP-2026-037', 'Nonoc Express Lines', 'Liza Gomez', 'liza@nonocexpress.example', '+63 921 555 0199', 'MV Nonoc Runner', 44, 'Surigao City → Nonoc Island', 'Yesterday, 09:05', 'yesterday at 09:05', 'Pending'),
            'OP-2026-036' => self::makeApp('OP-2026-036', 'Cabungaan Sea Transport', 'Carlo Mendoza', 'carlo@cabungaansea.example', '+63 922 555 0166', 'MV Cabungaan', 38, 'Surigao City → Cabungaan', '2 days ago, 15:40', '2 days ago at 15:40', 'Needs info'),
        ];

        // Include newly registered operators from session
        foreach (session('registered_accounts', []) as $idx => $acc) {
            if (($acc['role'] ?? '') === 'operator') {
                $id = $acc['app_id'] ?? ('OP-2026-0'.(42 + $idx));
                $apps[$id] = self::makeApp(
                    $id,
                    $acc['business'] ?? 'New Operator',
                    $acc['name'] ?? $acc['username'],
                    $acc['email'],
                    $acc['contact'] ?? '',
                    $acc['lantsa'] ?? 'MV Lantsa',
                    40,
                    'Surigao City → Dinagat',
                    'Just now',
                    'just now',
                    'Pending'
                );
            }
        }

        // Apply the decisions the admin made in this session
        foreach (session('decisions', []) as $id => $status) {
            if (isset($apps[$id])) {
                $apps[$id]['status'] = $status;
            }
        }

        return $apps;
    }

    private static function makeApp(string $id, string $business, string $owner, string $email, string $contact, string $lantsa, int $seats, string $route, string $submitted, string $submittedLong, string $status): array
    {
        return [
            'id' => $id, 'business' => $business, 'owner' => $owner, 'email' => $email, 'contact' => $contact,
            'address' => 'Surigao City, Surigao del Norte', 'lantsa' => $lantsa, 'seats' => $seats,
            'service' => 'Passenger & cargo', 'route' => $route,
            'submitted' => $submitted, 'submitted_long' => $submittedLong, 'status' => $status,
        ];
    }

    // [file name, size]
    public static function documents(): array
    {
        return [
            ['Business permit', 'PDF · 1.2 MB'],
            ['Government-issued ID', 'PDF · 0.8 MB'],
            ['Vessel registration', 'PDF · 1.6 MB'],
        ];
    }

    // ===============================================================
    // ADMIN: OPERATORS, DASHBOARD LISTS, REPORTS, SETTINGS
    // ===============================================================

    public static function operators(): array
    {
        $apps = self::applications();

        $list = [[
            'business' => 'Isla Norte Transport', 'vessel' => 'MV Isla Norte',
            'route' => 'Surigao → Dinagat', 'status' => 'Active', 'link' => url('admin/lantsa-routes'),
        ]];

        foreach (['OP-2026-041', 'OP-2026-040', 'OP-2026-039'] as $id) {
            if (isset($apps[$id])) {
                $app = $apps[$id];
                $list[] = [
                    'business' => $app['business'],
                    'vessel' => $app['lantsa'],
                    'route' => str_replace('Surigao City', 'Surigao', $app['route']),
                    'status' => $app['status'] === 'Approved' ? 'Active' : $app['status'],
                    'link' => url('admin/approvals/'.$id),
                ];
            }
        }

        return $list;
    }

    public static function lantsaPreview(): array
    {
        $vessels = self::lantsaRecords();
        $preview = [];
        foreach (array_slice($vessels, 0, 3) as $v) {
            $preview[] = [$v['lantsa'], $v['route'], $v['status']];
        }
        return $preview;
    }

    public static function dailyReport(): array
    {
        return [
            'Trips completed' => '18 / 48',
            'Passengers checked in' => '624',
            'Cargo registered' => '86',
        ];
    }

    // [route, completed trips, passengers, cargo items]
    public static function routeActivity(): array
    {
        return [
            ['Surigao City → Dinagat', 6, 248, 36],
            ['Surigao City → Cabungaan', 5, 168, 22],
            ['Surigao City → Cagdianao', 4, 128, 18],
            ['Surigao City → Del Carmen', 3, 80, 10],
        ];
    }

    public static function defaultSettings(): array
    {
        return [
            'workspace_name' => 'SURSEA Administration',
            'service_area' => 'Surigao City',
            'timezone' => 'Philippine Standard Time (PHT)',
            'support_contact' => 'support@sursea.example',
        ];
    }

    // ===============================================================
    // RICH MODULE DATA (ADMIN + OPERATOR) WITH SESSION OVERLAYS
    // ===============================================================

    public static function lantsaRecords(): array
    {
        $defaults = [
            ['id' => 'LR-1', 'lantsa' => 'MV Isla Norte', 'operator' => 'Isla Norte Transport', 'route' => 'Surigao City → Dinagat', 'port' => 'Surigao City Port', 'seats' => 40, 'cargo_limit' => '50 items', 'fare' => 150, 'status' => 'Active'],
            ['id' => 'LR-2', 'lantsa' => 'MV Marajaw', 'operator' => 'Marajaw Transport', 'route' => 'Surigao City → San Jose', 'port' => 'San Jose Wharf', 'seats' => 36, 'cargo_limit' => '45 items', 'fare' => 150, 'status' => 'Active'],
            ['id' => 'LR-3', 'lantsa' => 'MV Island Star', 'operator' => 'Island Star Lines', 'route' => 'Surigao City → Libjo', 'port' => 'Libjo Port', 'seats' => 20, 'cargo_limit' => '25 items', 'fare' => 180, 'status' => 'Suspended'],
            ['id' => 'LR-4', 'lantsa' => 'M/B Nonoc Express', 'operator' => 'Nonoc Express Lines', 'route' => 'Surigao City → Nonoc Island', 'port' => 'Surigao City Port', 'seats' => 44, 'cargo_limit' => '50 items', 'fare' => 120, 'status' => 'Active'],
            ['id' => 'LR-5', 'lantsa' => 'M/B Alona Waves', 'operator' => 'Alona Marine', 'route' => 'Surigao City → Hanigad Island', 'port' => 'Hanigad Pier', 'seats' => 30, 'cargo_limit' => '35 items', 'fare' => 130, 'status' => 'Active'],
            ['id' => 'LR-6', 'lantsa' => 'M/B Island Voyager', 'operator' => 'Voyager Sea Lines', 'route' => 'Surigao City → Hikdop Island', 'port' => 'Hikdop Wharf', 'seats' => 36, 'cargo_limit' => '40 items', 'fare' => 140, 'status' => 'Active'],
        ];

        $custom = session('custom_lantsas', []);
        $all = array_merge($custom, $defaults);

        $statusOverrides = session('lantsa_statuses', []);
        foreach ($all as &$item) {
            if (isset($statusOverrides[$item['id']])) {
                $item['status'] = $statusOverrides[$item['id']];
            }
        }

        return $all;
    }

    public static function tripScheduleRecords(?string $lantsaFilter = null): array
    {
        $defaults = [
            [
                'id' => 'TS-101', 'departure' => '05:00 AM', 'arrival' => '06:30 AM', 'date' => 'Today, Sept 26',
                'route' => 'Surigao City → Dinagat', 'port' => 'Surigao City Port',
                'lantsa' => 'MV Isla Norte', 'operator' => 'Isla Norte Transport',
                'capacity' => 40, 'reserved' => 28, 'seats_left' => '12 left', 'cargo_count' => 12,
                'status' => 'Completed', 'op_status' => 'Available',
            ],
            [
                'id' => 'TS-102', 'departure' => '05:00 AM', 'arrival' => '06:15 AM', 'date' => 'Today, Sept 26',
                'route' => 'Surigao City → Cabungaan', 'port' => 'San Jose Wharf',
                'lantsa' => 'MV Marajaw', 'operator' => 'Marajaw Transport',
                'capacity' => 36, 'reserved' => 32, 'seats_left' => '4 left', 'cargo_count' => 9,
                'status' => 'Completed', 'op_status' => 'Completed',
            ],
            [
                'id' => 'TS-103', 'departure' => '07:30 AM', 'arrival' => '09:00 AM', 'date' => 'Today, Sept 26',
                'route' => 'Surigao City → Cabungaan', 'port' => 'San Jose Wharf',
                'lantsa' => 'MV Isla Norte', 'operator' => 'Isla Norte Transport',
                'capacity' => 40, 'reserved' => 38, 'seats_left' => '2 left', 'cargo_count' => 17,
                'status' => 'Boarding', 'op_status' => 'Almost Full',
            ],
            [
                'id' => 'TS-104', 'departure' => '07:30 AM', 'arrival' => '09:10 AM', 'date' => 'Today, Sept 26',
                'route' => 'Surigao City → Cagdianao', 'port' => 'Cagdianao Pier',
                'lantsa' => 'MV Isla Norte', 'operator' => 'Isla Norte Transport',
                'capacity' => 40, 'reserved' => 40, 'seats_left' => 'Full', 'cargo_count' => 15,
                'status' => 'On Going', 'op_status' => 'On Going',
            ],
            [
                'id' => 'TS-105', 'departure' => '10:30 AM', 'arrival' => '12:00 PM', 'date' => 'Today, Sept 26',
                'route' => 'Surigao City → Del Carmen', 'port' => 'Del Carmen Port',
                'lantsa' => 'MV Marajaw', 'operator' => 'Marajaw Transport',
                'capacity' => 36, 'reserved' => 27, 'seats_left' => '9 left', 'cargo_count' => 11,
                'status' => 'Boarding', 'op_status' => 'Boarding',
            ],
            [
                'id' => 'TS-106', 'departure' => '12:00 PM', 'arrival' => '01:30 PM', 'date' => 'Today, Sept 26',
                'route' => 'Dinagat → Surigao City', 'port' => 'Surigao City Port',
                'lantsa' => 'MV Isla Norte', 'operator' => 'Isla Norte Transport',
                'capacity' => 40, 'reserved' => 35, 'seats_left' => '5 left', 'cargo_count' => 9,
                'status' => 'On Going', 'op_status' => 'On Going',
            ],
            [
                'id' => 'TS-107', 'departure' => '01:15 PM', 'arrival' => '02:30 PM', 'date' => 'Today, Sept 26',
                'route' => 'Surigao City → Hikdop Island', 'port' => 'Hikdop Wharf',
                'lantsa' => 'M/B Island Voyager', 'operator' => 'Voyager Sea Lines',
                'capacity' => 36, 'reserved' => 18, 'seats_left' => '18 left', 'cargo_count' => 6,
                'status' => 'Scheduled', 'op_status' => 'Scheduled',
            ],
            [
                'id' => 'TS-108', 'departure' => '03:00 PM', 'arrival' => '04:30 PM', 'date' => 'Today, Sept 26',
                'route' => 'Surigao City → Del Carmen', 'port' => 'Del Carmen Port',
                'lantsa' => 'MV Isla Norte', 'operator' => 'Isla Norte Transport',
                'capacity' => 40, 'reserved' => 31, 'seats_left' => '9 left', 'cargo_count' => 7,
                'status' => 'Scheduled', 'op_status' => 'Scheduled',
            ],
            [
                'id' => 'TS-109', 'departure' => '04:30 PM', 'arrival' => '06:00 PM', 'date' => 'Today, Sept 26',
                'route' => 'Surigao City → Libjo', 'port' => 'Libjo Port',
                'lantsa' => 'MV Island Star', 'operator' => 'Island Star Lines',
                'capacity' => 20, 'reserved' => 0, 'seats_left' => '20 left', 'cargo_count' => 0,
                'status' => 'Cancelled', 'op_status' => 'Cancelled',
            ],
        ];

        $custom = session('custom_trips', []);
        $all = array_merge($custom, $defaults);

        $statusOverrides = session('trip_statuses', []);
        foreach ($all as &$trip) {
            if (isset($statusOverrides[$trip['id']])) {
                $trip['status'] = $statusOverrides[$trip['id']];
                $trip['op_status'] = $statusOverrides[$trip['id']];
            }
        }

        if ($lantsaFilter) {
            $all = array_values(array_filter($all, fn ($t) => $t['lantsa'] === $lantsaFilter));
        }

        return $all;
    }

    public static function manifestRecords(?string $lantsaFilter = null): array
    {
        $defaults = [
            [
                'ref' => '0000-00001', 'name' => 'Robert Zabala', 'contact' => '09123123123',
                'route' => 'Dinagat → Surigao City', 'departure' => '12:00 PM', 'date' => '26-09-2026',
                'lantsa' => 'MV Isla Norte', 'passengers' => 1, 'cargo_qty' => 2,
                'total' => 200, 'status' => 'Checked in',
            ],
            [
                'ref' => '0000-00002', 'name' => 'Maria Dela Cruz', 'contact' => '09175550112',
                'route' => 'Surigao City → Cabungaan', 'departure' => '05:00 AM', 'date' => '26-09-2026',
                'lantsa' => 'MV Marajaw', 'passengers' => 2, 'cargo_qty' => 1,
                'total' => 325, 'status' => 'Checked in',
            ],
            [
                'ref' => '0000-00003', 'name' => 'Juan Reyes', 'contact' => '09171234567',
                'route' => 'Surigao City → Dinagat', 'departure' => '05:00 AM', 'date' => '26-09-2026',
                'lantsa' => 'MV Isla Norte', 'passengers' => 3, 'cargo_qty' => 0,
                'total' => 450, 'status' => 'Reserved',
            ],
            [
                'ref' => '0000-00004', 'name' => 'Ana Santos', 'contact' => '09181234567',
                'route' => 'Libjo → Surigao City', 'departure' => '12:00 PM', 'date' => '26-09-2026',
                'lantsa' => 'MV Isla Norte', 'passengers' => 1, 'cargo_qty' => 1,
                'total' => 175, 'status' => 'Reserved',
            ],
            [
                'ref' => '0000-00005', 'name' => 'Pedro Lim', 'contact' => '09205550188',
                'route' => 'San Jose → Surigao City', 'departure' => '04:30 PM', 'date' => '26-09-2026',
                'lantsa' => 'MV Marajaw', 'passengers' => 2, 'cargo_qty' => 0,
                'total' => 300, 'status' => 'Waiting',
            ],
            [
                'ref' => '0000-00006', 'name' => 'Carlo Mendoza', 'contact' => '09225550166',
                'route' => 'Surigao City → Cagdianao', 'departure' => '07:30 AM', 'date' => '26-09-2026',
                'lantsa' => 'MV Isla Norte', 'passengers' => 3, 'cargo_qty' => 2,
                'total' => 500, 'status' => 'Waiting',
            ],
        ];

        $custom = session('custom_manifests', []);
        $all = array_merge($custom, $defaults);

        $statusOverrides = session('manifest_statuses', []);
        foreach ($all as &$item) {
            if (isset($statusOverrides[$item['ref']])) {
                $item['status'] = $statusOverrides[$item['ref']];
            }
        }

        if ($lantsaFilter) {
            $all = array_values(array_filter($all, fn ($m) => $m['lantsa'] === $lantsaFilter));
        }

        return $all;
    }

    public static function waitlistRecords(): array
    {
        $defaults = [
            ['id' => 'WL-1', 'position' => 1, 'ref' => '0000-00005', 'name' => 'Pedro Lim', 'contact' => '09205550188', 'route' => 'San Jose → Surigao City', 'lantsa' => 'MV Isla Norte', 'passengers' => 2, 'status' => 'Waiting'],
            ['id' => 'WL-2', 'position' => 2, 'ref' => '0000-00007', 'name' => 'Liza Gomez', 'contact' => '09215550199', 'route' => 'San Jose → Surigao City', 'lantsa' => 'MV Isla Norte', 'passengers' => 1, 'status' => 'Waiting'],
            ['id' => 'WL-3', 'position' => 3, 'ref' => '0000-00006', 'name' => 'Carlo Mendoza', 'contact' => '09225550166', 'route' => 'Surigao City → Cagdianao', 'lantsa' => 'MV Isla Norte', 'passengers' => 3, 'status' => 'Waiting'],
            ['id' => 'WL-4', 'position' => 4, 'ref' => '0000-00008', 'name' => 'Rosa Dela Cruz', 'contact' => '09195550144', 'route' => 'Surigao City → Cagdianao', 'lantsa' => 'MV Isla Norte', 'passengers' => 1, 'status' => 'Moved to seat'],
        ];

        $custom = session('custom_waitlist', []);
        $all = array_merge($defaults, $custom);

        $statusOverrides = session('waitlist_statuses', []);
        foreach ($all as $idx => &$item) {
            $item['position'] = $idx + 1;
            if (isset($statusOverrides[$item['ref']])) {
                $item['status'] = $statusOverrides[$item['ref']];
            }
        }

        return $all;
    }

    public static function cargoRecords(?string $lantsaFilter = null): array
    {
        $defaults = [
            [
                'ref' => '0000-00111', 'sender' => 'Robert Zabala', 'sender_contact' => '09123123123',
                'receiver' => 'Earl Fermillan', 'receiver_contact' => '09123123123',
                'items' => 'Box of Coca Cola, Box of Sprite', 'qty' => 9,
                'route' => 'Dinagat → Surigao City', 'departure' => '12:00 PM',
                'lantsa' => 'MV Isla Norte', 'fee' => 225, 'status' => 'Pending',
            ],
            [
                'ref' => '0000-00112', 'sender' => 'Maria Dela Cruz', 'sender_contact' => '09175550112',
                'receiver' => 'Rosa Dela Cruz', 'receiver_contact' => '09195550144',
                'items' => 'Sack of rice', 'qty' => 4,
                'route' => 'Surigao City → Cabungaan', 'departure' => '05:00 AM',
                'lantsa' => 'MV Marajaw', 'fee' => 100, 'status' => 'In transit',
            ],
            [
                'ref' => '0000-00113', 'sender' => 'Juan Reyes', 'sender_contact' => '09171234567',
                'receiver' => 'Mark Reyes', 'receiver_contact' => '09179998877',
                'items' => 'Box of fish', 'qty' => 6,
                'route' => 'Surigao City → Dinagat', 'departure' => '05:00 AM',
                'lantsa' => 'MV Isla Norte', 'fee' => 150, 'status' => 'Delivered',
            ],
            [
                'ref' => '0000-00114', 'sender' => 'Ana Santos', 'sender_contact' => '09181234567',
                'receiver' => 'Ben Santos', 'receiver_contact' => '09187654321',
                'items' => 'Bag of vegetables', 'qty' => 3,
                'route' => 'Libjo → Surigao City', 'departure' => '12:00 PM',
                'lantsa' => 'MV Isla Norte', 'fee' => 75, 'status' => 'Delivered',
            ],
            [
                'ref' => '0000-00115', 'sender' => 'Pedro Lim', 'sender_contact' => '09205550188',
                'receiver' => 'Elena Lim', 'receiver_contact' => '09201112233',
                'items' => 'Crate of hardware supplies', 'qty' => 5,
                'route' => 'Surigao City → Cagdianao', 'departure' => '07:30 AM',
                'lantsa' => 'MV Isla Norte', 'fee' => 125, 'status' => 'In transit',
            ],
        ];

        $custom = session('custom_cargos', []);
        $all = array_merge($custom, $defaults);

        $statusOverrides = session('cargo_statuses', []);
        foreach ($all as &$item) {
            if (isset($statusOverrides[$item['ref']])) {
                $item['status'] = $statusOverrides[$item['ref']];
            }
        }

        if ($lantsaFilter) {
            $all = array_values(array_filter($all, fn ($c) => $c['lantsa'] === $lantsaFilter));
        }

        return $all;
    }

    // ===============================================================
    // TABLE PAGES CONFIG (Notifications & Fallback)
    // ===============================================================

    public static function module(string $area, string $key): ?array
    {
        $all = $area === 'admin' ? self::adminModules() : self::operatorModules();

        return $all[$key] ?? null;
    }

    private static function adminModules(): array
    {
        $eyebrow = 'ADMIN MANAGEMENT';

        return [
            'notifications' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Notifications',
                'title' => 'Notifications',
                'subtitle' => 'Important updates about applications, trips, and cargo.',
                'stats' => [
                    ['Unread', '3', 'Need your attention', 'bell'],
                    ['Today', '5', 'Received today', 'calendar'],
                    ['This week', '21', 'All notifications', 'clipboard'],
                ],
                'table' => 'Latest notifications',
                'search' => 'Search notifications',
                'statuses' => ['Unread', 'Read'],
                'mark_read' => true,
                'columns' => [['Received', 'text'], ['Notification', 'bold'], ['Type', 'text'], ['Status', 'status']],
                'rows' => [
                    ['Today, 08:20', 'Sea Breeze Transport sent an operator application.', 'Approval', 'Unread'],
                    ['Today, 07:45', 'Dinagat Island Ferries sent an operator application.', 'Approval', 'Unread'],
                    ['Today, 06:10', 'MV Island Star was suspended by an administrator.', 'Vessel', 'Unread'],
                    ['Yesterday, 16:10', 'Blue Horizon Lines was asked for more information.', 'Approval', 'Read'],
                    ['Yesterday, 12:00', 'Daily report was exported.', 'Report', 'Read'],
                ],
                'note' => 'Showing 5 notifications · Example data',
            ],
        ];
    }

    private static function operatorModules(): array
    {
        $eyebrow = 'OPERATOR OVERVIEW';

        return [
            'notifications' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Notifications',
                'title' => 'Notifications',
                'subtitle' => 'Updates about reservations, the waiting list, and cargo.',
                'stats' => [
                    ['Unread', '3', 'Need your attention', 'bell'],
                    ['Today', '6', 'Received today', 'calendar'],
                    ['This week', '17', 'All notifications', 'clipboard'],
                ],
                'table' => 'Latest notifications',
                'search' => 'Search notifications',
                'statuses' => ['Unread', 'Read'],
                'mark_read' => true,
                'columns' => [['Received', 'text'], ['Notification', 'bold'], ['Type', 'text'], ['Status', 'status']],
                'rows' => [
                    ['Today, 08:05', 'A seat became free on Surigao City → Cagdianao.', 'Waiting list', 'Unread'],
                    ['Today, 07:40', 'New cargo registered: 0000-00111.', 'Cargo', 'Unread'],
                    ['Today, 06:30', '2 seats left on Surigao City → Cabungaan.', 'Trip', 'Unread'],
                    ['Yesterday, 17:00', 'Daily report is ready.', 'Report', 'Read'],
                ],
                'note' => 'Showing 4 notifications · Example data',
            ],
        ];
    }
}
