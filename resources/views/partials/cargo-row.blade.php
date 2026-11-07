<div class="cargo-row">
    <div class="field">
        <label>Cargo Name</label>
        <input type="text" name="cargo_name[]" value="{{ $name ?? '' }}" placeholder="e.g. Bag, Box, Sack">
    </div>
    <div class="field qty">
        <label>Quantity</label>
        <input type="number" name="cargo_qty[]" min="1" max="99" value="{{ $qty ?? 1 }}">
    </div>
    <button type="button" class="trash" data-remove-row aria-label="Remove cargo row">
        @include('partials.icon', ['name' => 'trash'])
    </button>
</div>

