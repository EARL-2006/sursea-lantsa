<div class="table-wrap">
    <table class="data">
        <thead>
            <tr>
                <th>Business</th>
                <th>Lantsa</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applications as $app)
                <tr>
                    <td>
                        <strong>{{ $app['business'] }}</strong>
                        <br>
                        <small class="muted">Operator registration</small>
                    </td>
                    <td><strong>{{ $app['lantsa'] }}</strong></td>
                    <td class="muted">{{ $app['submitted'] }}</td>
                    <td>
                        <span class="badge {{ \App\Support\SampleData::badge($app['status']) }}">{{ $app['status'] }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.approvals.show', $app['id']) }}" class="btn btn-blue btn-sm">
                            Review @include('partials.icon', ['name' => 'arrow-right'])
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

