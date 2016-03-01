<?php

class game
{
    
    public $stage;
    public $input;
    public $x_max;
    public $x_min;
    public $y_max;
    public $y_min;
    public $score = 0;
    
    public $x = 2;
    public $y = 2;
    
    public function focus($x, $y)
    {
        print stage::ESC . "[" . $y . ";" . $x . "H";
    }
    
    public function focusHome()
    {
        $this->focus(1, 1);
    }
    
}