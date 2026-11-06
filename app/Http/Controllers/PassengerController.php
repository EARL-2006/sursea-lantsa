<?php

namespace App\Http\Controllers;

use App\Support\SampleData;
use Illuminate\Http\Request;

class PassengerController extends Controller
{
    // Prices used for the "Total Payment" on the ticket.
    // 1 passenger (150) + 2 cargo items (2 x 25) = 200, same as the Figma ticket.
    const PASSENGER_FARE = 150;
    const CARGO_FEE = 25;

    // ---------------------------------------------------------------
    // PUBLIC PAGES
    // ---------------------------------------------------------------

    public function home()
    {
        return view('passenger.home', [
            'places' => SampleData::places(),
            'board' => array_slice(SampleData::routeBoard(), 0, 4), // first 4 rows only
        ]);
    }

    public function schedules(Request $request)
    {
        $from = $request->query('from', 'all');
        $to = $request->query('to', 'all');

        // Keep only the trips that match the search
        $trips = array_filter(SampleData::trips(), function ($trip) use ($from, $to) {
            $fromOk = ($from === 'all' || $trip['from'] === $from);
            $toOk = ($to === 'all' || $trip['to'] === $to);
            return $fromOk && $toOk;
        });

        return view('passenger.schedules', [
            'trips' => $trips,
            'places' => SampleData::places(),
            'from' => $from,
            'to' => $to,
            'reservation' => session('reservation'),
        ]);
    }

    public function routeList()
    {
        return view('passenger.routes', ['board' => SampleData::routeBoard()]);
    }

    // ---------------------------------------------------------------
    // WAITING LIST PAGE
    // ---------------------------------------------------------------

    public function waitingList()
    {
        // Only full trips can have a waiting list
        $trips = array_filter(SampleData::trips(), fn ($trip) => $trip['status'] === 'Full');

        return view('passenger.waiting-list', [
            'trips' => $trips,
            'waitlist' => session('waitlist'),
        ]);
    }

    // ---------------------------------------------------------------
    // SEAT RESERVATION
    // ---------------------------------------------------------------

    public function seatForm($id)
    {
        $trip = $this->findTrip($id);

        if ($trip['status'] === 'Full') {
            return redirect()->route('waitlist.form', $trip['id'])
                ->with('notice', 'That trip is full, so you can join the waiting list instead.');
        }

        return view('passenger.seat-form', ['trip' => $trip]);
    }

    public function seatStore(Request $request, $id)
    {
        $trip = $this->findTrip($id);

        // A full trip cannot be reserved, only waitlisted
        if ($trip['status'] === 'Full') {
            return redirect()->route('waitlist.form', $trip['id']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'required|string|max:20',
            'passengers' => 'required|integer|min:1|max:'.$trip['left'],
            'cargo_name' => 'nullable|array',
            'cargo_name.*' => 'nullable|string|max:60',
            'cargo_qty' => 'nullable|array',
            'cargo_qty.*' => 'nullable|integer|min:1|max:99',
        ]);

        $number = $this->nextNumber('ref_counter', 0);
        $ticket = $this->passengerTicket($trip, $data, sprintf('0000-%05d', $number), false);

        session(['reservation' => $ticket]);

        return redirect()->route('seat.form', $trip['id'])->with('show_ticket', true);
    }

    // ---------------------------------------------------------------
    // WAITING LIST RESERVATION
    // ---------------------------------------------------------------

    public function waitlistForm($id)
    {
        $trip = $this->findTrip($id);

        if ($trip['status'] !== 'Full') {
            return redirect()->route('seat.form', $trip['id'])
                ->with('notice', 'Seats are still available on this trip, you can reserve a seat directly.');
        }

        return view('passenger.waitlist-form', ['trip' => $trip]);
    }

    public function waitlistStore(Request $request, $id)
    {
        $trip = $this->findTrip($id);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'required|string|max:20',
            'passengers' => 'required|integer|min:1|max:10',
            'cargo_name' => 'nullable|array',
            'cargo_name.*' => 'nullable|string|max:60',
            'cargo_qty' => 'nullable|array',
            'cargo_qty.*' => 'nullable|integer|min:1|max:99',
        ]);

        $number = $this->nextNumber('ref_counter', 0);
        $ticket = $this->passengerTicket($trip, $data, sprintf('0000-%05d', $number), true);

        session(['waitlist' => $ticket]);

        return redirect()->route('waitlist.form', $trip['id'])->with('show_ticket', true);
    }

    // ---------------------------------------------------------------
    // CARGO RESERVATION
    // ---------------------------------------------------------------

    public function cargoForm($id)
    {
        return view('passenger.cargo-form', ['trip' => $this->findTrip($id)]);
    }

    public function cargoStore(Request $request, $id)
    {
        $trip = $this->findTrip($id);

        $data = $request->validate([
            'sender_name' => 'required|string|max:100',
            'sender_contact' => 'required|string|max:20',
            'receiver_name' => 'required|string|max:100',
            'receiver_contact' => 'required|string|max:20',
            'cargo_name' => 'required|array',
            'cargo_name.*' => 'nullable|string|max:60',
            'cargo_qty' => 'nullable|array',
            'cargo_qty.*' => 'nullable|integer|min:1|max:99',
        ]);

        $items = $this->cargoItems($data);

        if (count($items) === 0) {
            return back()->withErrors(['cargo_name' => 'Please add at least one cargo item.'])->withInput();
        }

        $names = array_column($items, 'name');
        $number = $this->nextNumber('cargo_counter', 110); // first tracking ID = 0000-00111

        session(['cargo' => [
            'type' => 'cargo',
            'ref' => sprintf('0000-%05d', $number),
            'sender' => $data['sender_name'],
            'sender_contact' => $data['sender_contact'],
            'receiver' => $data['receiver_name'],
            'receiver_contact' => $data['receiver_contact'],
            'items' => implode(', ', $names),
            'qty' => array_sum(array_column($items, 'qty')),
            'lantsa' => $trip['lantsa'],
        ]]);

        return redirect()->route('cargo.form', $trip['id'])->with('show_ticket', true);
    }

    // ---------------------------------------------------------------
    // SMALL HELPERS
    // ---------------------------------------------------------------

    private function findTrip($id): array
    {
        $trip = SampleData::trips()[(int) $id] ?? null;
        abort_if($trip === null, 404);

        return $trip;
    }

    // Counts up: 1, 2, 3 ... and remembers the number in the session
    private function nextNumber(string $key, int $start): int
    {
        $number = session($key, $start) + 1;
        session([$key => $number]);

        return $number;
    }

    // Turns the cargo_name[] and cargo_qty[] form fields into a clean list
    private function cargoItems(array $data): array
    {
        $items = [];

        foreach (($data['cargo_name'] ?? []) as $index => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue; // skip empty rows
            }
            $qty = (int) ($data['cargo_qty'][$index] ?? 1);
            $items[] = ['name' => $name, 'qty' => $qty > 0 ? $qty : 1];
        }

        return $items;
    }

    // Builds the data shown on the "Passenger Reservation Ticket"
    private function passengerTicket(array $trip, array $data, string $ref, bool $isWaitlist): array
    {
        $items = $this->cargoItems($data);
        $cargoQty = array_sum(array_column($items, 'qty'));
        $passengers = (int) $data['passengers'];

        return [
            'type' => 'passenger',
            'ref' => $ref,
            'name' => $data['name'],
            'passengers' => $passengers,
            'cargo_qty' => $cargoQty,
            'route' => $trip['short'].', '.$trip['date'].', '.$trip['departure'],
            'lantsa' => $trip['lantsa'],
            'contact' => $data['contact'],
            'total' => ($passengers * self::PASSENGER_FARE) + ($cargoQty * self::CARGO_FEE),
            'waitlist' => $isWaitlist,
            'trip' => $trip,
        ];
    }
}
