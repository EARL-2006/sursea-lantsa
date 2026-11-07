@php
    $oldNames = old('cargo_name');
    $oldQtys = old('cargo_qty');
    $defaults = $defaults ?? [['name' => 'Bag', 'qty' => 1], ['name' => 'Box', 'qty' => 1]];
@endphp

<div class="cargo-rows" id="cargoRows">
    @if(is_array($oldNames) && count($oldNames) > 0)
        @foreach($oldNames as $idx => $oldName)
            @include('partials.cargo-row', ['name' => $oldName, 'qty' => $oldQtys[$idx] ?? 1])
        @endforeach
    @else
        @foreach($defaults as $row)
            @include('partials.cargo-row', ['name' => $row['name'], 'qty' => $row['qty']])
        @endforeach
    @endif
</div>

<button type="button" class="add-cargo" id="addCargo">
    @include('partials.icon', ['name' => 'plus-circle'])
    <span>Add Another Cargo</span>
</button>

<template id="cargoTemplate">
    @include('partials.cargo-row', ['name' => '', 'qty' => 1])
</template>

