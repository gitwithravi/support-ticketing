<x-filament-panels::page.simple>
    <x-slot name="subheading">
        Complete your Google registration by providing your unique ID.
    </x-slot>

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.register.form.before') }}

    <x-filament-panels::form wire:submit="register">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.register.form.after') }}
</x-filament-panels::page.simple>