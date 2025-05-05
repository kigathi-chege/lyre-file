<?php

namespace Lyre\File\Livewire;

use Livewire\Component;
use Lyre\File\Repositories\Contracts\FileRepositoryInterface;

class FileGallery extends Component
{
    public int $page = 1, $perPage = 8;
    public array $files = [], $selectedFiles = [];
    public bool $multiple = false, $previousPage = false, $nxtPage = false;
    public $state;

    public function mount()
    {
        $this->loadFiles();
    }

    public function loadFiles()
    {
        $repo = app(FileRepositoryInterface::class);
        $data = $repo->paginate($this->perPage, $this->page)->all();
        $this->page = $data['meta']['current_page'];
        $this->perPage = $data['meta']['per_page'];
        $lastPage = $data['meta']['last_page'];
        $this->previousPage = $this->page > 1;
        $this->nxtPage = $this->page < $lastPage;
        $this->files = $data['data']->resolve();
    }

    public function nextPage()
    {
        $this->page++;
        $this->loadFiles();
    }

    public function prevPage()
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadFiles();
        }
    }

    public function render()
    {
        return view('lyre.file::livewire.file-gallery');
    }
}
