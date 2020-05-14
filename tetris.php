<?php

require_once 'game.php';
require_once 'stage.php';
require_once 'input.php';

class sprite
{

    public $width;
    public $height;
    public $numberOfFrames;
    public $frames = [];
    public $currentFrame;
    public $blocks = [];

    public function __construct($location, $width, $height, $frames)
    {
        $this->width = $width;
        $this->height = $height;
        $this->numberOfFrames = $frames;
        $this->currentFrame = 0;
        if (!$this->loadFrames($location))
        {
            throw new Exception('Failed to load sprite');
        }
    }

    public function advanceFrame()
    {
        $this->currentFrame = $this->currentFrame >= $this->numberOfFrames - 1 ? 0 : $this->currentFrame + 1;
    }
    
    public function drawnHeight()
    {
        $i = 0;
        foreach (array_reverse($this->frames[$this->currentFrame]) as $lineNumber => $line)
        {
            if (strspn($line, ' ') != $this->width)
            {
                return $this->height - $i;
            }
            $i++;
        }
        return $this->height;
    }

    public function clear(stage $stage)
    {
        if (!empty($this->blocks))
        {
            foreach ($this->blocks as $line)
            {
                $stage->focus($line['x'], $line['y']);
                for ($i = 0; $i < $line['width']; $i++)
                {
                    print " ";
                }
            }
            $this->blocks = [];
        }
    }

    public function draw(stage $stage, $x, $y)
    {

        $this->clear($stage);
        foreach ($this->frames[$this->currentFrame] as $line)
        {
            $this->blocks[] = ['x' => $x, 'y' => $y, 'width' => $this->width];
            $stage->focus($x, $y);
            for ($i=0,$c=strlen($line);$i<$c;$i++)
            {
				var_dump($line);
                if ($line[$i] == ' ')
                {
                    $stage->focus($x + $i, $y);
                }
                else
                {
					foreach (str_split($line[$i]) as $letter) {
						if ($letter == ' ') {
							continue;
						}
						// we need to redraw Xs we get rid by focusing
						print $letter;
					}
                }
            }
            ///print $line;
            $y++;
        }
        $stage->focus($this->width + $x, $this->height + $y);
    }

    public function loadFrames($location)
    {
        $handle = fopen($location, 'r');
        if ($handle)
        {
            $line = 0;
            while (($buffer = fgets($handle)) !== false) {
                for ($i = 0, $c = $this->numberOfFrames; $i < $c; $i++)
                {
                    $this->frames[$i][$line] = substr($buffer, $i * $this->width, $this->width);
                }
                $line++;
            }
            return true;
        }
        return false;
    }

}

class tetris extends game
{

    public $blockStage;
    public $comingNextStage;
    public $scoreStage;
    public $command;
    public $activeShape;
    public $activeSprite;
    public $nextSprite;
    public $moveTime;
    public $shapeOrientation;
    public $actionTime;

    const MOVE_EVERY = 250;
    const BLOCK = "\178";
    const ROTATE = 'ROTATE';
    const COMMAND_MAX = 6;
    const ACTION_EVERY = 50;

    public static $commands = [
            0x1b5b44 => input::LEFT,
            0x1b5b43 => input::RIGHT,
            0x1b5b42 => input::DOWN,
            0x1b5b41 => input::UP,
            0x20 => self::ROTATE,
    ];
    public static $shapes = [
            0 => [
                    'file' => 'i',
                    'width' => 4,
                    'height' => 4,
                    'frames' => 4
            ],
            1 => [
                    'file' => 'j',
                    'width' => 3,
                    'height' => 3,
                    'frames' => 4
            ],
            2 => [
                    'file' => 'l',
                    'width' => 3,
                    'height' => 3,
                    'frames' => 4
            ],
            3 => [
                    'file' => 'o',
                    'width' => 3,
                    'height' => 3,
                    'frames' => 4
            ],
            4 => [
                    'file' => 's',
                    'width' => 3,
                    'height' => 3,
                    'frames' => 4
            ],
            5 => [
                    'file' => 't',
                    'width' => 3,
                    'height' => 3,
                    'frames' => 4
            ],
            6 => [
                    'file' => 'z',
                    'width' => 3,
                    'height' => 3,
                    'frames' => 4
            ],
    ];

    public function __construct()
    {
        $this->stage = new stage(70, 40);
        $this->blockStage = new stage(50, 40);
        $this->comingNextStage = new stage(21, 15, 50, 26);
        $this->scoreStage = new stage(21, 10, 50, 1);
        $this->input = new input;
        $this->x_max = $this->stage->x - 1;
        $this->y_max = $this->stage->y - 1;
        $this->x_min = 2;
        $this->y_min = 2;
    }

    public function spawnShape()
    {

        try
        {

            $directory = getcwd();
            
            if (empty($this->activeShape) && empty($this->nextShape))
            {
                $this->activeShape = self::$shapes[rand(0, 6)];
            }
            elseif (!empty($this->nextShape))
            {
                $this->activeShape = $this->nextShape;
            }

            $this->activeSprite = new sprite($directory . '/tetris/sprites/' . $this->activeShape['file'] . '.php', $this->activeShape['width'], $this->activeShape['height'], $this->activeShape['frames']);

            $this->x = round($this->blockStage->x / 2) - $this->activeSprite->width;
            $this->y = 2;
            $this->blockStage->focus($this->x, $this->y);

            $this->nextShape = self::$shapes[rand(0, 6)];
            $this->nextSprite = new sprite($directory . '/tetris/sprites/' . $this->nextShape['file'] . '.php', $this->nextShape['width'], $this->nextShape['height'], $this->nextShape['frames']);
        }
        catch (Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function shapeMove()
    {

        if (!empty($this->command) && (microtime(true) - $this->actionTime) * 1000 >= self::ACTION_EVERY)
        {
            switch ($this->command)
            {
                case input::LEFT:
                    $this->x--;
                    break;
                case input::RIGHT:
                    $this->x++;
                    break;
                case input::DOWN:
                    $this->y++;
                    break;
                case self::ROTATE:
                    $this->activeSprite->advanceFrame();
                    break;
            }

            $this->command = null;
            $this->input->clear();

            $this->activeSprite->draw($this->blockStage, $this->x, $this->y);
            $this->focusHome();
        }

        if ((microtime(true) - $this->moveTime) * 1000 >= self::MOVE_EVERY)
        {
            $this->activeSprite->draw($this->blockStage, $this->x, $this->y);
            $this->focusHome();
            $this->y++;
            $this->moveTime = microtime(true);
        }
    }

    public function shapeCollision()
    {
        if ($this->y == ($this->blockStage->y - Stage::STAGE_BORDER_THICKNESS) - $this->activeSprite->drawnHeight())
        {
            $this->activeSprite->drawnHeight();
            return true;
        }
    }

    public function stickShape()
    {
        $this->activeSprite = $this->nextSprite;
        $this->x = round($this->blockStage->x / 2) - $this->activeSprite->width;
        $this->y = 2;
        $this->nextShape = self::$shapes[rand(0, 6)];
        $this->nextSprite = new sprite(getcwd() . '/tetris/sprites/' . $this->nextShape['file'] . '.php', $this->nextShape['width'], $this->nextShape['height'], $this->nextShape['frames']);
    }

    public function run()
    {
        $this->comingNextStage->border(' ', ' ');
        $this->stage->border(' ');
        $this->blockStage->border(' ');
        $this->scoreStage->border(' ');

        $this->spawnShape();
        while (true) {

            $this->input->listen(self::$commands, self::COMMAND_MAX);
            if ($this->input->command())
            {
                $this->command = $this->input->command();
                print $this->command;
            }

//            if (!$this->canMoveShape())
//            {
//                die ("It's over!");
//            }
			
			
            if ($this->shapeCollision())
            {
                $this->stickShape();
//                $this->clearLines();
            } else {
				$this->shapeMove();
			}
			
			

//            if (!$this->activeShape)
//            {
//                $this->spawnShape();
//            }
//            
//            $this->userMove();
//            $this->shapeMove();
//            
//            if ($this->shapeStuck())
//            {
//                $this->score++;
//                foreach ($this->completedRows() as $row)
//                {
//                    $this->removeRow($row);
//                }
//            }
        }
//        $this->generateFood();
//        $command = input::RIGHT;
//        while (true)
//        {
//            $this->input->listen(self::$commands, self::COMMAND_MAX);
//            if ($this->input->command() && $this->input->command() != $command)
//            {
//                $this->direction = $this->input->command();
//                $command = $this->input->command();
//            }
//            $this->move();
//            if ($this->hitFood())
//            {
//                $this->grow = self::GROW_LOOPS;
//                $this->generateFood();
//            }
//        }
    }

}

$tetris = new tetris;
$tetris->run();
