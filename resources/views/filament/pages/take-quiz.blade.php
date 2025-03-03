<x-filament-panels::page>
    {{ $this->form }}

    <!-- Add Submit Button Below the Form -->
    <div class="flex justify-start">
        @foreach ($this->getActions() as $action)
            {{ $action }}
        @endforeach
    </div>
</x-filament-panels::page>

