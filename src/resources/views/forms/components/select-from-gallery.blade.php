<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">

    @php
        $multiple = $getMultiple();
        $modalId = 'select-file-' . \Illuminate\Support\Str::slug($getStatePath(), '-');
    @endphp

    <div x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        selectedFiles: @js($getSelectedFiles()),
    }" x-init="function() {
        this.state = this.selectedFiles.map(file => file.id);
        $watch('selectedFiles', () => {
            this.state = this.selectedFiles.map(file => file.id);
        });
    }"
        x-on:lyre-file-gallery-selected.window="
            if ($event.detail.statePath !== '{{ $getStatePath() }}') {
                return;
            }

            selectedFiles = $event.detail.files ?? [];
            state = selectedFiles.map(file => file.id);
            $dispatch('close-modal', { id: '{{ $modalId }}' });
        ">
        <div
            class="fi-fo-field-wrp rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-primary-400 hover:shadow-md dark:border-white/10 dark:bg-gray-900 dark:hover:border-primary-500"
        >
            <button
                type="button"
                class="flex w-full items-start gap-4 rounded-2xl p-4 text-left outline-none"
                x-on:click="$dispatch('open-modal', { id: '{{ $modalId }}' })"
            >
                <div class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-100 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
                    <template x-if="selectedFiles.length > 0">
                        <img :src="selectedFiles[0]?.link" alt="Selected file preview" class="h-full w-full object-cover">
                    </template>

                    <template x-if="selectedFiles.length === 0">
                        <img src="{{ asset('lyre/file/placeholder.webp') }}" alt="No file selected"
                            class="h-full w-full object-cover">
                    </template>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $multiple ? 'Selected files' : 'Selected file' }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <span x-show="selectedFiles.length === 0">Choose from the gallery or upload a new file.</span>
                                <span x-show="selectedFiles.length > 0" x-text="selectedFiles.length === 1
                                    ? (selectedFiles[0]?.name ?? '1 file selected')
                                    : `${selectedFiles.length} files selected`"></span>
                            </p>
                        </div>

                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            Browse
                        </span>
                    </div>

                    <template x-if="selectedFiles.length > 1">
                        <div class="mt-3 flex flex-wrap gap-2">
                            <template x-for="file in selectedFiles.slice(0, 6)" :key="file.id">
                                <span class="inline-flex max-w-full items-center rounded-full bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                    <span class="truncate" x-text="file.name"></span>
                                </span>
                            </template>
                        </div>
                    </template>
                </div>
            </button>
        </div>

        <x-filament::modal id="{{ $modalId }}" width="7xl">
            <x-slot name="heading">
                Select {{ $multiple ? 'Files' : 'File' }}
            </x-slot>

            <x-slot name="description">
                Browse your gallery, preview media, or upload something new without leaving the form.
            </x-slot>

            @livewire('file-gallery', ['selectedFiles' => $getSelectedFiles(), 'multiple' => $multiple, 'modalId' => $modalId, 'statePath' => $getStatePath()], key($modalId))
        </x-filament::modal>
    </div>
</x-dynamic-component>
