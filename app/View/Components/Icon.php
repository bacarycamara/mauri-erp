<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Icon extends Component
{
    public $name;
    public $style;

    public function __construct($name, $style = 'o')
    {
        $this->name = $name;
        $this->style = $style;
    }

    public function render()
    {
        return view('components.icon');
    }
}