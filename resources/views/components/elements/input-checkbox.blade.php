@props([
    'label' => null,
    'id' => null,
    'name' => null,
    'autofocus' => false,
])

@php
    $wireModel = $attributes->get('wire:model');
@endphp

<div x-data="{ checked: false }" x-init="
    @if($autofocus ?? false) setTimeout(() => $refs.checkbox.focus(), 1); @endif
    checked = $refs.checkbox.checked;
">
    <div class="flex items-center space-x-2">
        <input
            {{ $attributes }}
            {{ $attributes->whereStartsWith('wire:model') }}
            id="{{ $id }}"
            name="{{ $name }}"
            type="checkbox"
            x-ref="checkbox"
            x-model="checked"
            @focus="$el.classList.add('ring-1', 'ring-zinc-800')"
            @blur="$el.classList.remove('ring-1', 'ring-zinc-800')"
            class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-2 focus:ring-primary-600 disabled:opacity-50 disabled:cursor-not-allowed @error($wireModel) border-red-300 text-red-900 focus:ring-red-500 @enderror"
        />

        @if ($label)
            <label for="{{ $id }}" class="cursor-pointer text-sm text-gray-600>
                {!! $label !!}
            </label>
        @endif
    </div>

    @error($wireModel)
        <p class="my-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
