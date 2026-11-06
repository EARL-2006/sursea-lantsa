<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;

/**
 * ALL the example data of the prototype lives in this one file.
 * Later, when you add a database, replace each method with a real query.
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
                'role' => 'admin', 'name' => 'Administrator', 'contact' => '',
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

        return $trips;
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
            ['02:00 PM', 'Nonoc Island', 'M/B Nonoc Express', '9 Left', 'Scheduled'],
            ['03:30 PM', 'Hanigad Island', 'M/B Alona Waves', '14 Left', 'Scheduled'],
            ['04:45 PM', 'Dinagat Port', 'M/B Sea Charger', '20 Left', 'Scheduled'],
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
            'Almost Full' => 'amber', 'Pending' => 'amber', 'Reserved' => 'amber', 'Unread' => 'amber',
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
        return [
            ['Trips Today', '6', 'Scheduled crossings', 'calendar'],
            ['Seats Reserved', '140', 'Across today’s trips', 'armchair'],
            ['Waiting List', '9', 'Passengers in queue', 'waiting'],
            ['Cargo Pending', '14', 'Items awaiting check-in', 'box'],
        ];
    }

    // [route, via, departure, seats left, status]
    public static function operatorLiveTrips(): array
    {
        return [
            ['Surigao City → Dinagat', 'Via Surigao City Port', '05:00 AM', '12 left', 'Available'],
            ['Surigao City → Cabungaan', 'Via San Jose Wharf', '07:30 AM', '2 left', 'Almost Full'],
            ['Surigao City → Cagdianao', 'Via Cagdianao Pier', '12:00 PM', '5 left', 'On Going'],
            ['Surigao City → Del Carmen', 'Via Del Carmen Port', '03:00 PM', '9 left', 'Scheduled'],
        ];
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

        return 12 - $done;
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
            'route' => 'Surigao City → Dinagat', 'status' => 'Active', 'link' => url('admin/lantsa-routes'),
        ]];

        foreach (['OP-2026-041', 'OP-2026-040', 'OP-2026-039'] as $id) {
            $app = $apps[$id];
            $list[] = [
                'business' => $app['business'], 'vessel' => $app['lantsa'], 'route' => $app['route'],
                'status' => $app['status'], 'link' => url('admin/approvals/'.$id),
            ];
        }

        return $list;
    }

    public static function lantsaPreview(): array
    {
        return [
            ['MV Isla Norte', 'Surigao City → Dinagat', 'Active'],
            ['MV Marajaw', 'Surigao City → San Jose', 'Active'],
            ['MV Island Star', 'Surigao City → Libjo', 'Suspended'],
        ];
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
    // TABLE PAGES (one layout, different data)
    // ---------------------------------------------------------------
    // 'columns' = [column title, type]   type: bold | text | status
    // ===============================================================

    public static function module(string $area, string $key): ?array
    {
        $all = $area === 'admin' ? self::adminModules() : self::operatorModules();

        return $all[$key] ?? null;
    }

    // ---------- ADMIN modules that were missing in the Figma ----------
    private static function adminModules(): array
    {
        $eyebrow = 'ADMIN MANAGEMENT';

        return [
            'lantsa-routes' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Lantsa & Routes',
                'title' => 'Lantsa & Routes',
                'subtitle' => 'Manage registered vessels, assigned routes, and operating status.',
                'stats' => [
                    ['Registered lantsas', '32', 'Across all operators'],
                    ['Active routes', '18', 'Open for reservations'],
                    ['Suspended vessels', '2', 'Not accepting trips'],
                ],
                'table' => 'Lantsa directory',
                'search' => 'Search lantsa, operator, or route',
                'statuses' => ['Active', 'Suspended'],
                'columns' => [['Lantsa', 'bold'], ['Operator', 'text'], ['Route', 'text'], ['Passenger seats', 'text'], ['Status', 'status']],
                'rows' => [
                    ['MV Isla Norte', 'Isla Norte Transport', 'Surigao City → Dinagat', '40', 'Active'],
                    ['MV Marajaw', 'Marajaw Transport', 'Surigao City → San Jose', '36', 'Active'],
                    ['MV Island Star', 'Island Star Lines', 'Surigao City → Libjo', '20', 'Suspended'],
                    ['M/B Nonoc Express', 'Nonoc Express Lines', 'Surigao City → Nonoc Island', '44', 'Active'],
                    ['M/B Alona Waves', 'Alona Marine', 'Surigao City → Hanigad Island', '30', 'Active'],
                ],
                'note' => 'Showing 5 lantsas · Example data',
            ],
            'trip-schedule' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Trip Schedule',
                'title' => 'Trip schedule',
                'subtitle' => 'See every scheduled crossing across all operators.',
                'stats' => [
                    ['Trips today', '48', 'Scheduled crossings'],
                    ['Completed', '18', 'Already arrived'],
                    ['Remaining', '30', 'Still to depart'],
                ],
                'table' => 'Today’s crossings',
                'search' => 'Search route or lantsa',
                'statuses' => ['Completed', 'On Going', 'Boarding', 'Scheduled', 'Cancelled'],
                'columns' => [['Departure', 'bold'], ['Route', 'text'], ['Lantsa', 'text'], ['Seats left', 'text'], ['Status', 'status']],
                'rows' => [
                    ['05:00 AM', 'Surigao City → Dinagat', 'MV Isla Norte', '12 left', 'Completed'],
                    ['05:00 AM', 'Surigao City → Cabungaan', 'MV Marajaw', '4 left', 'Completed'],
                    ['07:30 AM', 'Surigao City → Cagdianao', 'MV Isla Norte', 'Full', 'On Going'],
                    ['10:30 AM', 'Surigao City → Del Carmen', 'MV Marajaw', '9 left', 'Boarding'],
                    ['01:15 PM', 'Surigao City → Hikdop Island', 'M/B Island Voyager', '18 left', 'Scheduled'],
                    ['04:30 PM', 'Surigao City → Libjo', 'MV Island Star', '20 left', 'Cancelled'],
                ],
                'note' => 'Showing 6 of 48 trips · Example data',
            ],
            'passenger-manifest' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Passenger Manifest',
                'title' => 'Passenger manifest',
                'subtitle' => 'Check the passenger list of every crossing before it departs.',
                'stats' => [
                    ['Checked in', '624', 'Across completed trips'],
                    ['Reserved', '88', 'Waiting to check in'],
                    ['On waiting list', '9', 'Passengers in queue'],
                ],
                'table' => 'Passenger list',
                'search' => 'Search passenger or reference code',
                'statuses' => ['Checked in', 'Reserved', 'Waiting'],
                'columns' => [['Reference', 'bold'], ['Passenger', 'text'], ['Route', 'text'], ['Lantsa', 'text'], ['Passengers', 'text'], ['Status', 'status']],
                'rows' => [
                    ['0000-00001', 'Robert Zabala', 'Dinagat → Surigao City', 'MV Isla Norte', '1', 'Checked in'],
                    ['0000-00002', 'Maria Dela Cruz', 'Surigao City → Cabungaan', 'MV Marajaw', '2', 'Checked in'],
                    ['0000-00003', 'Juan Reyes', 'Surigao City → Dinagat', 'MV Isla Norte', '3', 'Reserved'],
                    ['0000-00004', 'Ana Santos', 'Libjo → Surigao City', 'MV Isla Norte', '1', 'Reserved'],
                    ['0000-00005', 'Pedro Lim', 'San Jose → Surigao City', 'MV Marajaw', '2', 'Waiting'],
                ],
                'note' => 'Showing 5 passengers · Example data',
            ],
            'cargo' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Cargo',
                'title' => 'Cargo',
                'subtitle' => 'Monitor cargo registered by passengers across all operators.',
                'stats' => [
                    ['Cargo registered', '86', 'Items across the network'],
                    ['Pending check-in', '14', 'Items awaiting check-in'],
                    ['Delivered', '52', 'Already received'],
                ],
                'table' => 'Cargo deliveries',
                'search' => 'Search tracking ID, sender, or receiver',
                'statuses' => ['Pending', 'In transit', 'Delivered'],
                'columns' => [['Tracking ID', 'bold'], ['Sender', 'text'], ['Receiver', 'text'], ['Items', 'text'], ['Qty', 'text'], ['Lantsa', 'text'], ['Status', 'status']],
                'rows' => [
                    ['0000-00111', 'Robert Zabala', 'Earl Fermillan', 'Box of Coca Cola, Box of Sprite', '9', 'MV Isla Norte', 'Pending'],
                    ['0000-00112', 'Maria Dela Cruz', 'Rosa Dela Cruz', 'Sack of rice', '4', 'MV Marajaw', 'In transit'],
                    ['0000-00113', 'Juan Reyes', 'Mark Reyes', 'Box of fish', '6', 'MV Isla Norte', 'Delivered'],
                    ['0000-00114', 'Ana Santos', 'Ben Santos', 'Bag of vegetables', '3', 'MV Isla Norte', 'Delivered'],
                ],
                'note' => 'Showing 4 deliveries · Example data',
            ],
            'notifications' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Notifications',
                'title' => 'Notifications',
                'subtitle' => 'Important updates about applications, trips, and cargo.',
                'stats' => [
                    ['Unread', '3', 'Need your attention'],
                    ['Today', '5', 'Received today'],
                    ['This week', '21', 'All notifications'],
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

    // ---------- OPERATOR pages (for MV Isla Norte only) ----------
    private static function operatorModules(): array
    {
        $eyebrow = 'OPERATOR OVERVIEW';

        return [
            'vessel-routes' => [
                'eyebrow' => $eyebrow, 'crumb' => 'My Vessel & Routes',
                'title' => 'My Vessel & Routes',
                'subtitle' => 'Your lantsa and the routes it is allowed to run.',
                'stats' => [
                    ['Vessel', 'MV Isla Norte', 'Passenger & cargo'],
                    ['Passenger seats', '40', 'Total capacity'],
                    ['Routes', '4', 'Approved by admin'],
                ],
                'table' => 'My routes',
                'search' => 'Search route',
                'statuses' => ['Active'],
                'columns' => [['Route', 'bold'], ['Via', 'text'], ['Passenger fare', 'text'], ['Status', 'status']],
                'rows' => [
                    ['Surigao City → Dinagat', 'Surigao City Port', '150', 'Active'],
                    ['Surigao City → Cabungaan', 'San Jose Wharf', '150', 'Active'],
                    ['Surigao City → Cagdianao', 'Cagdianao Pier', '150', 'Active'],
                    ['Surigao City → Del Carmen', 'Del Carmen Port', '150', 'Active'],
                ],
                'note' => 'Showing 4 routes · Example data',
            ],
            'trip-schedule' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Trip Schedule',
                'title' => 'Trip schedule',
                'subtitle' => 'All crossings of MV Isla Norte for today.',
                'stats' => [
                    ['Trips today', '6', 'Scheduled crossings'],
                    ['Departed', '2', 'Already left the port'],
                    ['Remaining', '4', 'Still to depart'],
                ],
                'table' => 'Today’s crossings',
                'search' => 'Search route',
                'statuses' => ['Available', 'Almost Full', 'On Going', 'Scheduled'],
                'columns' => [['Departure', 'bold'], ['Route', 'text'], ['Seats reserved', 'text'], ['Seats left', 'text'], ['Status', 'status']],
                'rows' => [
                    ['05:00 AM', 'Surigao City → Dinagat', '28', '12 left', 'Available'],
                    ['07:30 AM', 'Surigao City → Cabungaan', '38', '2 left', 'Almost Full'],
                    ['12:00 PM', 'Surigao City → Cagdianao', '35', '5 left', 'On Going'],
                    ['03:00 PM', 'Surigao City → Del Carmen', '31', '9 left', 'Scheduled'],
                ],
                'note' => 'Showing 4 trips · Example data',
            ],
            'waiting-list' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Waiting List',
                'title' => 'Waiting list',
                'subtitle' => 'Passengers waiting for a seat on a full trip.',
                'stats' => [
                    ['In queue', '9', 'Passengers waiting'],
                    ['Moved to seat', '3', 'Seat became available'],
                    ['Full trips', '2', 'With a waiting list'],
                ],
                'table' => 'Waiting passengers',
                'search' => 'Search passenger or route',
                'statuses' => ['Waiting', 'Moved to seat'],
                'columns' => [['Position', 'bold'], ['Passenger', 'text'], ['Route', 'text'], ['Passengers', 'text'], ['Status', 'status']],
                'rows' => [
                    ['1', 'Pedro Lim', 'San Jose → Surigao City', '2', 'Waiting'],
                    ['2', 'Liza Gomez', 'San Jose → Surigao City', '1', 'Waiting'],
                    ['3', 'Carlo Mendoza', 'Surigao City → Cagdianao', '3', 'Waiting'],
                    ['4', 'Rosa Dela Cruz', 'Surigao City → Cagdianao', '1', 'Moved to seat'],
                ],
                'note' => 'Showing 4 passengers · Example data',
            ],
            'check-in' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Check-in',
                'title' => 'Passenger check-in',
                'subtitle' => 'Verify the reservation code of each passenger before boarding.',
                'stats' => [
                    ['Checked in', '96', 'Passengers today'],
                    ['Waiting to check in', '44', 'Reserved but not yet at the port'],
                    ['Next departure', '12:00 PM', 'Surigao City → Cagdianao'],
                ],
                'table' => 'Reservations to verify',
                'search' => 'Search reference code or passenger',
                'statuses' => ['Checked in', 'Reserved'],
                'columns' => [['Reference', 'bold'], ['Passenger', 'text'], ['Route', 'text'], ['Passengers', 'text'], ['Status', 'status']],
                'rows' => [
                    ['0000-00001', 'Robert Zabala', 'Dinagat → Surigao City', '1', 'Checked in'],
                    ['0000-00003', 'Juan Reyes', 'Surigao City → Dinagat', '3', 'Reserved'],
                    ['0000-00004', 'Ana Santos', 'Libjo → Surigao City', '1', 'Reserved'],
                ],
                'note' => 'Showing 3 reservations · Example data',
            ],
            'manifest' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Passenger Manifest',
                'title' => 'Passenger manifest',
                'subtitle' => 'The official passenger list of MV Isla Norte.',
                'stats' => [
                    ['Passengers', '140', 'Across today’s trips'],
                    ['Checked in', '96', 'Ready to board'],
                    ['Reserved', '44', 'Not yet checked in'],
                ],
                'table' => 'Passenger list',
                'search' => 'Search passenger or reference code',
                'statuses' => ['Checked in', 'Reserved'],
                'columns' => [['Reference', 'bold'], ['Passenger', 'text'], ['Contact', 'text'], ['Route', 'text'], ['Passengers', 'text'], ['Status', 'status']],
                'rows' => [
                    ['0000-00001', 'Robert Zabala', '09123123123', 'Dinagat → Surigao City', '1', 'Checked in'],
                    ['0000-00003', 'Juan Reyes', '09171234567', 'Surigao City → Dinagat', '3', 'Reserved'],
                    ['0000-00004', 'Ana Santos', '09181234567', 'Libjo → Surigao City', '1', 'Reserved'],
                ],
                'note' => 'Showing 3 passengers · Example data',
            ],
            'cargo' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Cargo',
                'title' => 'Cargo',
                'subtitle' => 'Cargo registered for MV Isla Norte.',
                'stats' => [
                    ['Cargo pending', '14', 'Items awaiting check-in'],
                    ['In transit', '20', 'Already on board'],
                    ['Delivered', '52', 'Already received'],
                ],
                'table' => 'Cargo list',
                'search' => 'Search tracking ID, sender, or receiver',
                'statuses' => ['Pending', 'In transit', 'Delivered'],
                'columns' => [['Tracking ID', 'bold'], ['Sender', 'text'], ['Receiver', 'text'], ['Items', 'text'], ['Qty', 'text'], ['Status', 'status']],
                'rows' => [
                    ['0000-00111', 'Robert Zabala', 'Earl Fermillan', 'Box of Coca Cola, Box of Sprite', '9', 'Pending'],
                    ['0000-00113', 'Juan Reyes', 'Mark Reyes', 'Box of fish', '6', 'Delivered'],
                    ['0000-00114', 'Ana Santos', 'Ben Santos', 'Bag of vegetables', '3', 'In transit'],
                ],
                'note' => 'Showing 3 deliveries · Example data',
            ],
            'reports' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Reports',
                'title' => 'Reports',
                'subtitle' => 'Daily summary for MV Isla Norte.',
                'stats' => [
                    ['Completed trips', '2 / 6', 'Scheduled crossings today'],
                    ['Passengers checked in', '96', 'Across completed trips'],
                    ['Cargo registered', '29', 'Items today'],
                ],
                'table' => 'Route activity',
                'search' => 'Search route',
                'statuses' => [],
                'columns' => [['Route', 'bold'], ['Completed trips', 'text'], ['Passengers', 'text'], ['Cargo items', 'text']],
                'rows' => [
                    ['Surigao City → Dinagat', '1', '40', '12'],
                    ['Surigao City → Cabungaan', '1', '56', '17'],
                    ['Surigao City → Cagdianao', '0', '0', '0'],
                    ['Surigao City → Del Carmen', '0', '0', '0'],
                ],
                'note' => 'Showing 4 routes · Example data',
            ],
            'notifications' => [
                'eyebrow' => $eyebrow, 'crumb' => 'Notifications',
                'title' => 'Notifications',
                'subtitle' => 'Updates about reservations, the waiting list, and cargo.',
                'stats' => [
                    ['Unread', '3', 'Need your attention'],
                    ['Today', '6', 'Received today'],
                    ['This week', '17', 'All notifications'],
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
            'profile' => [
                'eyebrow' => 'ACCOUNT', 'crumb' => 'Profile',
                'title' => 'Business profile',
                'subtitle' => 'Your business details as approved by the administrator.',
                'toolbar' => false,
                'stats' => [],
                'table' => 'Details',
                'columns' => [['Detail', 'bold'], ['Information', 'text']],
                'rows' => [
                    ['Business name', 'Isla Norte Transport'],
                    ['Lantsa', 'MV Isla Norte'],
                    ['Service', 'Passenger & cargo'],
                    ['Email address', 'islanorte@sursea.example'],
                    ['Business address', 'Surigao City, Surigao del Norte'],
                    ['Approval status', 'Approved by administrator'],
                ],
                'note' => 'Example data',
            ],
        ];
    }
}
