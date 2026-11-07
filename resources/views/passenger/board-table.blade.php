<div class="board">
    <table>
        <thead>
            <tr>
                <th>DEPARTURE TIME</th>
                <th>ROUTE (FROM – TO)</th>
                <th>VESSEL NAME</th>
                <th>SEATS AVAILABLE</th>
                <th class="right">TRIP STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($board as $row)
                <tr>
                    <td><strong>{{ $row[0] }}</strong></td>
                    <td>
                        <strong>Surigao Port</strong>
                        <span class="arrow">→</span>
                        <strong class="dest">{{ $row[1] }}</strong>
                    </td>
                    <td>
                        <span class="vessel">@include('partials.icon', ['name' => 'ship']) {{ $row[2] }}</span>
                    </td>
                    <td>
                        <strong class="{{ $row[3] === 'Full' ? 'full-text' : '' }}">{{ $row[3] }}</strong>
                    </td>
                    <td class="right">
                        <span class="badge plain {{ \App\Support\SampleData::badge($row[4]) }}">{{ $row[4] }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

