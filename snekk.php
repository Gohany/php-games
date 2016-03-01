<?php

declare(ticks = 1);

// signal handler function
function sig_handler($signo)
{

    switch ($signo)
    {
        case SIGTERM:
            // handle shutdown tasks
            print "\033[0m";
            exit;
            break;
        case SIGHUP:
            print "\033[0m";
            exit;
            break;
        case SIGUSR1:
            print "\033[0m";
            exit;
            break;
        default:
            // handle all other signals
            print "\033[0m";
            exit;
    }
}

require_once 'game.php';
require_once 'stage.php';
require_once 'input.php';

class snake extends game
{
    
    public $direction = input::RIGHT;
    public $length = 0;
    public $grow = 3;
    public $moveCounter = 0;    
    public $moveTime;
    
    const COMMAND_MAX = 6;
    const GROW_LOOPS = 6;
    const SNAKE_CHAR = 'X';
    const FOOD_CHAR = 'O';
    const BACKGROUND_CHAR = ' ';
    const WAIT_FRAMES = 25000;
    const MOVE_EVERY = 150; //ms
    
    public function __construct(){
        $this->stage = new stage;
        $this->input = new input;
        $this->x_max = $this->stage->x - 1;
        $this->y_max = $this->stage->y - 1;
        $this->x_min = 2;
        $this->y_min = 2;
    }
    
    public static $commands = [
            0x1b5b44 => input::LEFT,
            0x1b5b43 => input::RIGHT,
            0x1b5b42 => input::DOWN,
            0x1b5b41 => input::UP,
    ];
    
    public static $movement = [
            input::LEFT => "\033[1D",
            input::RIGHT => "\033[1C",
            input::DOWN => "\033[1B",
            input::UP => "\033[1A",
    ];
    
    public $tailCoords = [];
    public $foodCoords = [];
    
    public function move()
    {
        if ((microtime(true) - $this->moveTime) * 1000 >= self::MOVE_EVERY)
        {
            $this->moveCounter = 0;
            $this->focus(1, 25);
            print $this->direction;
            switch ($this->direction)
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
                case input::UP:
                    $this->y--;
                    break;
            }
            
            $this->checkForDeath();

            $this->tailCoords['_' . $this->x . '-' . $this->y] = ['x' => $this->x, 'y' => $this->y];
            $this->focus($this->x, $this->y);
            print self::SNAKE_CHAR;

            if (!$this->grow)
            {
                $disappear = array_shift($this->tailCoords);
                $this->focus(10, 25);
                print 'x: '. $disappear['x'] . ' ' . 'y: ' . $disappear['y'] . ' ' . $this->grow;
                $this->focus($disappear['x'], $disappear['y']);
                print self::BACKGROUND_CHAR;
            }
            
            $this->grow = $this->grow == 0 ? 0 : --$this->grow;
            $this->focusHome();
            $this->moveTime = microtime(true);
        }
        
    }
    
    public function checkForDeath()
    {
        if ($this->death())
        {
            $this->stage->fillScreen();
            $x_title = round($this->stage->x / 3);
            $y_title = round($this->stage->y / 2);
            $this->focus($x_title, $y_title);
            for ($i = 0, $c = 25; $i < $c; $i++)
            {
                print self::BACKGROUND_CHAR;
            }
            $this->focus($x_title, $y_title - 1);
            for ($i = 0, $c = 25; $i < $c; $i++)
            {
                print self::BACKGROUND_CHAR;
            }
            $this->focus($x_title, $y_title - 2);
            for ($i = 0, $c = 25; $i < $c; $i++)
            {
                print self::BACKGROUND_CHAR;
            }
            $this->focus($x_title, $y_title - 1);
            print "  You died. Score: " . $this->score;
            $this->focus(1, $this->stage->y + 1);
            exit;
        }
    }
    
    public function death()
    {
        if (
                $this->x < $this->x_min || 
                $this->x > $this->x_max || 
                $this->y < $this->y_min || 
                $this->y > $this->y_max ||
                !empty($this->tailCoords['_' . $this->x . '-' . $this->y])
        )
        {
            return true;
        }
        return false;
    }
    
    public function hitFood()
    {
        if (!empty($this->foodCoords) && $this->x == $this->foodCoords['x'] && $this->y == $this->foodCoords['y'])
        {
            $this->score++;
            $this->focus(30, 25);
            print "Score: " . $this->score;
            return true;
        }
        return false;
    }
    
    public function generateFood()
    {
        if ($this->x_max * $this->y_max == count($this->tailCoords))
        {
            die('You win!');
        }
        else
        {
            $validPosition = false;
            do
            {
                $x = rand($this->x_min, $this->x_max);
                $y = rand($this->y_min, $this->y_max);
                if (empty($this->tailCoords['_' . $x . '-' . $y]))
                {
                    $validPosition = true;
                }
            }
            while ($validPosition == false);
            $this->focus($x, $y);
            print self::FOOD_CHAR;
            $this->focusHome();
            $this->foodCoords['x'] = $x;
            $this->foodCoords['y'] = $y;
        }
    }
                
    public function run()
    {
        $this->stage->border();
        $this->generateFood();
        $command = input::RIGHT;
        while (true)
        {
            $this->input->listen(self::$commands, self::COMMAND_MAX);
            if ($this->input->command() && $this->input->command() != $command)
            {
                $this->direction = $this->input->command();
                $command = $this->input->command();
            }
            $this->move();
            if ($this->hitFood())
            {
                $this->grow = self::GROW_LOOPS;
                $this->generateFood();
            }
        }
    }
    
}

$snake = new snake;
$snake->run();