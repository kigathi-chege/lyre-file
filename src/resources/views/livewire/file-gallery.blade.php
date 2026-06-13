<div x-data="{
    tempState: [],
    state: @js($state),
    tempSelectedFiles: @js($selectedFiles),
    selectedFiles: @js($selectedFiles),
    multiple: @js($multiple),
    previousPage: @entangle('previousPage'),
    nxtPage: @entangle('nxtPage'),
    toggleSelection(fileId, fileName, fileMime, fileLink) {
        if (this.tempState == null) {
            this.tempState = []
        }
        if (this.tempState?.includes(fileId)) {
            this.tempState = this.tempState.filter(id => id !== fileId);
            this.tempSelectedFiles = this.tempSelectedFiles.filter(file => file.id !== fileId);
        } else {
            if (!this.multiple) {
                this.tempState = []
                this.tempSelectedFiles = []
            }
            this.tempState.push(fileId);
            this.tempSelectedFiles.push({ id: fileId, name: fileName, mimetype: fileMime, link: fileLink });
        }
    },
    selectFiles() {
        this.state = this.tempState;
        this.selectedFiles = this.tempSelectedFiles;
        $dispatch('filesSelected', { files: this.selectedFiles });
    },
    refreshState(uploadedFile = null) {
        if (uploadedFile) {
            this.selectedFiles.push(uploadedFile);
            this.tempSelectedFiles.push(uploadedFile);
        }
        this.state = this.selectedFiles.map(file => file.id);
        this.tempState = this.state;
    }
}" x-init="refreshState()"
    @refresh-gallery-state.window="refreshState($event.detail.uploadedFile)">
    <div class="flex flex-col gap-6">
        <div>
            <x-filament::tabs class="ring-0" label="Gallery tabs">
                <x-filament::tabs.item :active="!$addNew" icon="heroicon-m-photo" wire:click="$set('addNew', false)">
                    View All
                </x-filament::tabs.item>

                <x-filament::tabs.item :active="$addNew" icon="heroicon-m-plus" wire:click="$set('addNew', true)">
                    Add New
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>

        @if (!$addNew)
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @foreach ($files as $file)
                    <div class="group relative flex min-h-[18rem] cursor-pointer flex-col overflow-hidden rounded-2xl border bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900"
                        :class="tempState?.includes({{ $file['id'] }})
                            ? 'border-primary-500 ring-2 ring-primary-200 dark:ring-primary-500/30'
                            : 'border-gray-200 dark:border-white/10'"
                        @click="toggleSelection(@js($file['id']), @js($file['name']), @js($file['mimetype'] ?? null), @js($file['link']))">
                        <template x-if="tempState?.includes({{ $file['id'] }})">
                            <div class="absolute right-3 top-3 z-20 rounded-full bg-primary-600 px-2.5 py-1 text-xs font-semibold text-white shadow-lg">
                                Selected
                            </div>
                        </template>

                        <div class="relative aspect-[4/3] overflow-hidden bg-gray-100 dark:bg-gray-800">
                            <div class="absolute inset-0 z-10 bg-gradient-to-t from-black/10 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"
                                :class="tempState?.includes({{ $file['id'] }}) ? 'opacity-100' : ''">
                            </div>

                            @php
                                $ext = strtolower($file['extension'] ?? '');
                            @endphp

                            @if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                                <img src="{{ $file['link'] }}" alt="{{ $file['name'] }}"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                            @elseif ($ext === 'pdf')
                                <iframe src="{{ $file['link'] }}" class="h-full w-full" frameborder="0"></iframe>
                            @elseif (in_array($ext, ['mp4', 'webm']))
                                <video controls class="h-full w-full object-cover">
                                    <source src="{{ $file['link'] }}" type="video/{{ $ext }}">
                                    Your browser does not support the video tag.
                                </video>
                            @elseif ($ext === 'xlsx')
                                <div class="flex h-full flex-col items-center justify-center p-4 text-center">
                                    <img src="{{ asset('xlsx.png') }}" alt="Excel" class="mb-3 h-16 w-16">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $file['name'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Excel file preview unavailable</p>
                                </div>
                            @elseif ($ext === 'md')
                                <div class="flex h-full flex-col items-center justify-center overflow-auto p-4 text-center">
                                    <img src="{{ asset('md.png') }}" alt="Markdown" class="mb-3 h-16 w-16">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $file['name'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Markdown preview coming soon</p>
                                </div>
                            @else
                                <div class="flex h-full w-full flex-col items-center justify-center gap-4 p-4">
                                    <img src="{{ asset("{$ext}.png") }}" alt="{{ $ext }}" class="h-20 w-20">
                                    <p class="line-clamp-2 text-center text-sm font-medium text-gray-900 dark:text-white">{{ $file['name'] }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col justify-between gap-3 p-4">
                            <div>
                                <p class="line-clamp-2 text-sm font-semibold text-gray-950 dark:text-white">
                                    {{ $file['name'] }}
                                </p>
                                <p class="mt-1 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {{ $ext ?: 'file' }}
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ strtoupper($ext ?: 'FILE') }}</span>
                                <span x-show="tempState?.includes({{ $file['id'] }})" class="font-medium text-primary-600 dark:text-primary-300">
                                    Ready
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <form wire:submit.prevent="create">
                {{ $this->form }}
            </form>
        @endif

        <div class="mt-2 flex flex-col gap-4 border-t border-gray-200 pt-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex gap-3">
                {{-- TODO: Kigathi - July 19 2025 - Implement actual pagination with page numbers --}}
                <x-filament::button wire:click="prevPage" color="gray"
                    x-bind:disabled="!previousPage">Previous</x-filament::button>
                <x-filament::button wire:click="nextPage" color="gray"
                    x-bind:disabled="!nxtPage">Next</x-filament::button>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                @if (!$addNew)
                    <x-filament::button @click="selectFiles(); $dispatch('close-modal', { id: @js($modalId) })">
                        Select Files
                    </x-filament::button>
                @else
                    <x-filament::button color="info" wire:click="create">
                        Submit
                    </x-filament::button>
                @endif

                <x-filament::button
                    @click="tempState = []; state = []; tempSelectedFiles = [...selectedFiles]; refreshState(); $dispatch('close-modal', { id: @js($modalId) })"
                    color="gray">
                    Cancel
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
