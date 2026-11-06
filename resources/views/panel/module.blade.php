@extends('layouts.panel')

@section('title', $m['title'].' - SURSEA')
@section('crumb', $m['crumb'])

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">{{ $m['eyebrow'] }}</p>
        <h1>{{ $m['title'] }}</h1>
        <p class="subtitle">{{ $m['subtitle'] }}</p>
    </div>
</div>

{{-- Small number cards --}}
@if(!empty($m['stats']))
    <div class="stat-grid cols-3">
        @foreach($m['stats'] as $stat)
            <div class="stat-card">
                <p class="stat-label">{{ $stat[0] }}</p>
                <p class="stat-number {{ strlen($stat[1]) > 6 ? 'small' : '' }}">{{ $stat[1] }}</p>
                <p class="stat-note">{{ $stat[2] }}</p>
            </div>
        @endforeach
    </div>
@endif

{{-- Table box --}}
<section class="box">
    <div class="box-head">
        <h2>{{ $m['table'] }}</h2>
        @if(!empty($m['mark_read']))
            <button type="button" class="btn btn-blue btn-sm" id="markAllRead">Mark all as read</button>
        @endif
    </div>

    @if($m['toolbar'] ?? true)
        <div class="toolbar">
            <input type="text" id="tableSearch" class="input-search" placeholder="{{ $m['search'] }}">
            @if(!empty($m['statuses']))
                <select id="tableStatus" class="input-select">
                    <option value="">All statuses</option>
                    @foreach($m['statuses'] as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    @endif

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    @foreach($m['columns'] as $column)
                        <th>{{ $column[0] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($m['rows'] as $row)
                    <tr>
                        @foreach($m['columns'] as $i => $column)
                            <td>
                                @if($column[1] === 'status')
                                    <span class="badge {{ \App\Support\SampleData::badge($row[$i]) }}">{{ $row[$i] }}</span>
                                @elseif($column[1] === 'bold')
                                    <strong>{{ $row[$i] }}</strong>
                                @else
                                    {{ $row[$i] }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="table-note">{{ $m['note'] }}</p>
</section>
@endsection