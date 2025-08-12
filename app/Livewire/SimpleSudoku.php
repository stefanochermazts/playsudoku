<?php

namespace App\Livewire;

use Livewire\Component;

class SimpleSudoku extends Component
{
    public array $grid = [];
    
    public function mount()
    {
        $this->grid = array_fill(0, 9, array_fill(0, 9, null));
    }
    
    public function render()
    {
        return view('livewire.simple-sudoku');
    }
}