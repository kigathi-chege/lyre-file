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
    }
}" x-init="function() {
    this.state = this.selectedFiles.map(file => file.id);
    this.tempState = this.state;
}">
    <div class="flex flex-col space-y-4">
        <div class="grid lg:grid-cols-4 grid-cols-1 md:grid-cols-2 gap-4 justify-center items-center">
            @foreach ($files as $file)
                <div class="rounded-md h-64 relative cursor-pointer border-2"
                    :class="tempState?.includes({{ $file['id'] }}) ? 'border-blue-500' : 'border-transparent'"
                    @click="toggleSelection(@js($file['id']), @js($file['name']), @js($file['mimetype'] ?? null), @js($file['link']))">
                    <template x-if="tempState?.includes({{ $file['id'] }})">
                        <div
                            class="absolute top-0 right-0 w-0 h-0 border-t-[40px] border-t-green-500 border-l-[40px] border-l-transparent">
                        </div>
                    </template>
                    <div class="absolute inset-0 hover:bg-black hover:opacity-25 rounded-md"
                        :class="tempState?.includes({{ $file['id'] }}) ? 'bg-green-600 opacity-25' : 'bg-transparent'">
                    </div>
                    @if (isset($file['mimetype']) && $file['mimetype'] == 'application/pdf')
                        <div class="flex flex-col items-center justify-center gap-4 h-full w-full">
                            <img src="{{ asset('pdf.png') }}" alt="PDF" class="h-48 w-48">
                            <p class="">{{ $file['name'] }}</p>
                        </div>
                    @else
                        <img src="{{ $file['link'] }}" alt="{{ $file['name'] }}"
                            class="w-full h-full object-cover rounded-md">
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex justify-between" id="sth">
            <div class="flex gap-4">
                <x-filament::button wire:click="prevPage" color="gray"
                    x-bind:disabled="!previousPage">Previous</x-filament::button>
                <x-filament::button wire:click="nextPage" color="gray"
                    x-bind:disabled="!nxtPage">Next</x-filament::button>
            </div>
            <div class="flex gap-4">
                <x-filament::button @click="selectFiles; $dispatch('close-modal', { id: 'select-file' })">
                    Select Files
                </x-filament::button>
                <x-filament::button @click="tempstate = []; state = []; $dispatch('close-modal', { id: 'select-file' })"
                    color="gray" x-on:click="tempState=null">
                    Cancel
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
