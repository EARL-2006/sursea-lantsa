<div class="modal {{ !empty($open) ? 'open' : '' }}" id="{{ $modalId ?? 'ticketModal' }}">
    <div class="ticket">
        <div class="ticket-head">
            <h2>{{ ($ticket['type'] ?? 'passenger') === 'cargo' ? 'Cargo Delivery Ticket' : 'Passenger Reservation Ticket' }}</h2>
            <button type="button" class="modal-close" data-close-modal aria-label="Close ticket">
                @include('partials.icon', ['name' => 'close'])
            </button>
        </div>

        @if(($ticket['type'] ?? 'passenger') === 'cargo')
            <dl class="ticket-body">
                <dt>Cargo Tracking ID:</dt>
                <dd>{{ $ticket['ref'] }}</dd>

                <dt>Sender name:</dt>
                <dd>{{ $ticket['sender'] }}</dd>

                <dt>Sender contact number:</dt>
                <dd>{{ $ticket['sender_contact'] }}</dd>

                <dt>Receiver name:</dt>
                <dd>{{ $ticket['receiver'] }}</dd>

                <dt>Receiver contact number:</dt>
                <dd>{{ $ticket['receiver_contact'] }}</dd>

                <dt>Cargo name:</dt>
                <dd class="small">{{ $ticket['items'] }}</dd>

                <dt>Cargo quantity:</dt>
                <dd>{{ $ticket['qty'] }}</dd>

                <dt>Lantsa name:</dt>
                <dd>{{ $ticket['lantsa'] }}</dd>
            </dl>

            <div class="ticket-foot">
                <div>
                    <p>Please take a screenshot on this ticket for verification before delivery.</p>
                </div>
                <div class="qr" data-code="{{ $ticket['ref'] }}"></div>
            </div>
        @else
            <dl class="ticket-body">
                <dt>Reservation reference code:</dt>
                <dd>{{ $ticket['ref'] }}</dd>

                <dt>Passenger full name:</dt>
                <dd>{{ $ticket['name'] }}</dd>

                <dt>Number of passengers:</dt>
                <dd>{{ $ticket['passengers'] }}</dd>

                <dt>Cargo quantity:</dt>
                <dd>{{ $ticket['cargo_qty'] }}</dd>

                <dt>Route and departure date/time:</dt>
                <dd class="small">{{ $ticket['route'] }}</dd>

                <dt>Lantsa name:</dt>
                <dd>{{ $ticket['lantsa'] }}</dd>

                <dt>Contact Number:</dt>
                <dd>{{ $ticket['contact'] }}</dd>
            </dl>

            <div class="ticket-foot">
                <div>
                    <div class="total">
                        <strong>Total Payment:</strong>
                        <span>{{ $ticket['total'] }}</span>
                    </div>
                    <p>Please take a screenshot on this ticket for verification before boarding</p>
                </div>
                <div class="qr" data-code="{{ $ticket['ref'] }}"></div>
            </div>
        @endif
    </div>
</div>

