<?php

class stage extends game
{

    const X = 80;
    const Y = 25;
    const ESC = "\033";
    
    public $x = 80;
    public $y = 25;
    public $xOffset = 0;
    public $yOffset = 0;
    public $fill;

    public function __construct($x = 80, $y = 25, $xOffset = 1, $yOffset = 1)
    {
        $this->x = $x;
        $this->y = $y;
        $this->xOffset = $xOffset;
        $this->yOffset = $yOffset;
        print stage::ESC . "[2J";
        //print stage::ESC . "[=3h";
        print stage::ESC . "[44m";
    }
    
    public function focus($x, $y)
    {
        return parent::focus($x + $this->xOffset, $y + $this->yOffset);
    }

    public function fillScreen($char = 'X')
    {

        if ($this->fill != $char)
        {
            print stage::ESC . "[2J";
            print stage::ESC . "[1;1H";
            for ($i = 0; $i < $this->y(); $i++)
            {
                for ($e = 0; $e < $this->x(); $e++)
                {
                    print $char;
                }
                print PHP_EOL;
            }
            print stage::ESC . "[1;1H";
        }
    }
    
    public function x()
    {
        return $this->x + $this->xOffset;
    }
    
    public function y()
    {
        return $this->y + $this->yOffset;
    }
    
    public function border($fill = ' ', $top = 'X', $bottom = 'X', $left = 'X', $right = 'X')
    {
        //print stage::ESC . "[2J";
        print stage::ESC . "[" . $this->yOffset . ";" . $this->xOffset . "H";
        for ($i = 0; $i < $this->y; $i++)
        {
            for ($e = 0; $e < $this->x; $e++)
            {
                if ($i == 0)
                {
                    print $top;
                    continue;
                }
                if ($i == $this->y - 1)
                {
                    print $bottom;
                    continue;
                }
                if ($e == 0)
                {
                    print $left;
                    continue;
                }
                if ($e == $this->x - 1)
                {
                    print $right;
                    continue;
                }
                print $fill;
            }
            parent::focus($this->xOffset, $this->yOffset + $i + 1);
            //print stage::ESC . "[" . $this->yOffset + $i + 1 . ";" . $this->xOffset . "H";
            //print PHP_EOL;
        }
        print stage::ESC . "[1;1H";
    }

    public function __destruct()
    {
        print stage::ESC . "[0m";
    }

}