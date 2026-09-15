<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CategoryThree extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $categories,
        public $selected = [],
        public $level = 0
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.category-three', [
            'categories' => $this->categories,
            'selected' => $this->selected,
            'level' => $this->level,
        ]);
    }
}
