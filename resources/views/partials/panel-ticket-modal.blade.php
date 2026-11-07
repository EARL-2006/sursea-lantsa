<div class="modal" id="panelTicketModal">
    <div class="ticket">
        <div class="ticket-head">
            <h2 id="ptTitle">Passenger Reservation Ticket</h2>
            <button type="button" class="modal-close" data-close-modal aria-label="Close ticket">
                @include('partials.icon', ['name' => 'close'])
            </button>
        </div>

        <dl class="ticket-body" id="ptBody"></dl>

        <div class="ticket-foot">
            <div>
                <div class="total" id="ptTotalWrap">
                    <strong>Total Payment:</strong>
                    <span id="ptTotal">200</span>
                </div>
                <p id="ptNote">Please take a screenshot on this ticket for verification before boarding</p>
            </div>
            <div class="qr" id="ptQr" data-code="0000-00001"></div>
        </div>
    </div>
</div>

