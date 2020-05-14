<?php

interface IStage
{
    public function setX(int $x) : stage;
    public function setY(int $y) : stage;
    public function setXOffset(int $xOffset) : stage;
    public function setYOffset(int $yOffset) : stage;
    public function focus($x, $y);
    public function x();
    public function y();
    public function fillScreen($char = 'X');
    public function finalX($offset = 0);
    public function finalY($offset = 0);
    public function border($fill = ' ', $top = 'X', $bottom = 'X', $left = 'X', $right = 'X');
}

class stage implements IStage
{

    const X = 80;
    const Y = 25;
    const ESC = "\033";
	const STAGE_BORDER_THICKNESS = 1;
	const CLEAR = "[2J";

    protected $x = 80;
    protected $y = 25;

    private $xOffset = 0;
    private $yOffset = 0;
    private $fill;
    private $game;

    public function __construct(game $game, $x = 80, $y = 25, $xOffset = 1, $yOffset = 1)
    {
        $this->game = $game;
        $this->x = $x;
        $this->y = $y;
        $this->xOffset = $xOffset;
        $this->yOffset = $yOffset;
        self::clear();
        print stage::ESC . "[44m";
    }

    public static function clear()
    {
        print stage::ESC . stage::CLEAR;
    }

    public function setX(int $x) : stage
    {
        $this->x = $x;
        return $this;
    }

    public function setY(int $y) : stage
    {
        $this->y = $y;
        return $this;
    }

    public function setXOffset(int $xOffset) : stage
    {
        $this->xOffset = $xOffset;
        return $this;
    }

    public function setYOffset(int $yOffset) : stage
    {
        $this->yOffset = $yOffset;
        return $this;
    }
    
    public function focus($x, $y)
    {
        return $this->game->focus($x + $this->xOffset, $y + $this->yOffset);
    }

    public function x()
    {
        return $this->x;
    }

    public function y()
    {
        return $this->y;
    }

    public function fillScreen($char = 'X')
    {
        if ($this->fill != $char) {
            self::clear();
            $this->game->focusHome();
            for ($i = 0; $i < $this->finalY(); $i++) {
                for ($e = 0; $e < $this->finalX(); $e++) {
                    print $char;
                }
                print PHP_EOL;
            }
            $this->game->focusHome();
        }
    }
    
    public function finalX($offset = 0)
    {
        return $this->x + $this->xOffset + $offset;
    }
    
    public function finalY($offset = 0)
    {
        return $this->y + $this->yOffset + $offset;
    }
    
    public function border($fill = ' ', $top = 'X', $bottom = 'X', $left = 'X', $right = 'X')
    {
        print stage::ESC . "[" . $this->yOffset . ";" . $this->xOffset . "H";
        for ($i = 0; $i < $this->y; $i++) {
            for ($e = 0; $e < $this->x; $e++) {
                if ($i == 0) {
                    print $top;
                    continue;
                }
                if ($i == $this->y - 1) {
                    print $bottom;
                    continue;
                }
                if ($e == 0) {
                    print $left;
                    continue;
                }
                if ($e == $this->x - 1) {
                    print $right;
                    continue;
                }
                print $fill;
            }
            $this->game->focus($this->xOffset, $this->yOffset + $i + 1);
        }
        $this->game->focusHome();
    }

    public function __destruct()
    {
        print stage::ESC . "[0m";
    }

}