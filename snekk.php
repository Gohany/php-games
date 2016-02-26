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

class stage
{

    const X = 80;
    const Y = 25;
    const ESC = "\033";
    
    public $x = 80;
    public $y = 25;
    public $fill;

    public function __construct($x = 80, $y = 25)
    {
        $this->x = $x;
        $this->y = $y;
        print stage::ESC . "[2J";
        //print stage::ESC . "[=3h";
        print stage::ESC . "[44m";
    }

    public function fillScreen($char = 'X')
    {

        if ($this->fill != $char)
        {
            print stage::ESC . "[2J";
            print stage::ESC . "[0;0H";
            for ($i = 0; $i < $this->y; $i++)
            {
                for ($e = 0; $e < $this->x; $e++)
                {
                    print $char;
                }
                print PHP_EOL;
            }
            print stage::ESC . "[0;0H";
        }
    }
    
    public function border($top = 'X', $bottom = 'X', $left = 'X', $right = 'X')
    {
        print stage::ESC . "[2J";
        print stage::ESC . "[1;1H";
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
                print " ";
            }
            print PHP_EOL;
        }
        print stage::ESC . "[2;2H";
    }

    public function __destruct()
    {
        print stage::ESC . "[0m";
    }

}

class input
{

    public $input = '';
    public $command;
    public static $alphabet = [
            0x41 => 'A',
            0x42 => 'B',
            0x43 => 'C',
            0x44 => 'D',
            0x45 => 'E',
            0x46 => 'F',
            0x47 => 'G',
            0x48 => 'H',
            0x49 => 'I',
            0x4a => 'J',
            0x4b => 'K',
            0x4c => 'L',
            0x4d => 'M',
            0x4e => 'N',
            0x4f => 'O',
            0x50 => 'P',
            0x51 => 'Q',
            0x52 => 'R',
            0x53 => 'S',
            0x54 => 'T',
            0x55 => 'U',
            0x56 => 'V',
            0x57 => 'W',
            0x58 => 'X',
            0x59 => 'Y',
            0x5a => 'Z',
            0x61 => 'a',
            0x62 => 'b',
            0x63 => 'c',
            0x64 => 'd',
            0x65 => 'e',
            0x66 => 'f',
            0x67 => 'g',
            0x68 => 'h',
            0x69 => 'i',
            0x6a => 'j',
            0x6b => 'k',
            0x6c => 'l',
            0x6d => 'm',
            0x6e => 'n',
            0x6f => 'o',
            0x70 => 'p',
            0x71 => 'q',
            0x72 => 'r',
            0x73 => 's',
            0x74 => 't',
            0x75 => 'u',
            0x76 => 'v',
            0x77 => 'w',
            0x78 => 'x',
            0x79 => 'y',
            0x7a => 'z',
            0x20 => ' ',
            0x7f => 'BACKSPACE',
            0x0a => 'ENTER',
    ];
    
    const LEFT = 'LEFT';
    const RIGHT = 'RIGHT';
    const DOWN = 'DOWN';
    const UP = 'UP';
    const REFRESH = 'REFRESH';
    
    public static $escapes = [
            0x1b5b44 => self::LEFT,
            0x1b5b43 => self::RIGHT,
            0x1b5b42 => self::DOWN,
            0x1b5b41 => self::UP,
            0x1b5b31357e => self::REFRESH,
    ];
    
    public static $movement = [
            self::LEFT => "\033[1D",
            self::RIGHT => "\033[1C",
            self::DOWN => "\033[1B",
            self::UP => "\033[1A",
    ];

    public function __construct()
    {
        readline_callback_handler_install('', function()
        {
            
        });
    }
    
    public function hex($input)
    {
        return intval(bin2hex($input), 16);
    }
    
    public function last($input, $number)
    {
        return intval(substr(bin2hex($input), $number * -1), 16);
    }

    public function listen($commands, $max)
    {
        $r = array(STDIN);
        $w = NULL;
        $e = NULL;
        $n = stream_select($r, $w, $e, 0);
        stream_set_timeout(STDIN, 0, 1000);
        if ($n && in_array(STDIN, $r))
        {
            
            if (strlen(bin2hex($this->input)) > $max)
            {
                $this->input = '';
            }
            $this->input .= stream_get_contents(STDIN, 1);
            
            if (!empty($commands[$this->hex($this->input)]))
            {
                $this->command = $commands[$this->hex($this->input)];
                $this->input = '';
            }
            
        }
    }

    public function input()
    {
        if (!empty($this->input))
        {
            return $this->input;
        }
        return false;
    }

    public function command()
    {
        if (!empty($this->command))
        {
            return $this->command;
        }
        return false;
    }

}

class snake
{
    
    public $stage;
    public $input;
    public $direction = input::RIGHT;
    public $length = 0;
    public $grow = 3;
    public $x_max;
    public $x_min;
    public $y_max;
    public $y_min;
    public $moveCounter = 0;
    public $score = 0;
    public $moveTime;
    
    public $x = 2;
    public $y = 2;
    
    const COMMAND_MAX = 6;
    const GROW_LOOPS = 6;
    const SNAKE_CHAR = 'X';
    const FOOD_CHAR = 'O';
    const BACKGROUND_CHAR = ' ';
    const WAIT_FRAMES = 25000;
    const MOVE_EVERY = 100; //ms
    
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
            input::LEFT => stage::ESC . '[1D',
            input::RIGHT => stage::ESC . '[1C',
            input::DOWN => stage::ESC . '[1B',
            input::UP => stage::ESC . '[1A',
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

            $this->tailCoords['_' . $this->x . $this->y] = ['x' => $this->x, 'y' => $this->y];
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
                !empty($this->tailCoords['_' . $this->x . $this->y])
        )
        {
            return true;
        }
        return false;
    }
    
    public function focusHome()
    {
        $this->focus(1, 1);
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
                if (empty($this->tailCoords['_' . $x . $y]))
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
    
    public function focus($x, $y)
    {
        print stage::ESC . "[" . $y . ";" . $x . "H";
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