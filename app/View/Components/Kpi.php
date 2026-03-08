<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Kpi extends Component
{
    public $color;
    public $icon;
    public $label;
    public $value;

    public function __construct($color, $icon, $label, $value)
    {
        $this->color = $color;
        $this->icon = $icon;
        $this->label = $label;
        $this->value = $value;
    }

    public function render()
    {
        return view('components.kpi');
    }
}