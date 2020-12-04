<?php

declare(ticks = 1);

require_once 'game.php';
require_once 'stage.php';
require_once 'input.php';

// signal handler function
function sig_handler($signor)
{
    switch ($signor) {
        case SIGTERM:
            // handle shutdown tasks
            print "\033[0m";
            print "\033[2J";
            print PHP_EOL;
            exit;
            break;
        case SIGHUP:
            print "\033[0m";
            print "\033[2J";
            print PHP_EOL;
            exit;
            break;
        default:
            // handle all other signals
            print "\033[0m";
            print "\033[2J";
            print PHP_EOL;
            exit;
    }
}

#pcntl_signal(SIGTERM, "sig_handler");
#pcntl_signal(SIGHUP,  "sig_handler");
#pcntl_signal(SIGUSR1, "sig_handler");

interface ISnake {
    /**
     * Run the game
     * @return void
     */
    public function run();
}

class snake implements ISnake
{
    /**
     * Maximum simultaneous commands
     */
    const COMMAND_MAX = 6;
    /**
     * Number of tiles to grow when eating food
     */
    const GROW_LOOPS = 6;
    /**
     * Frame length in miliseconds
     */
    const MOVE_EVERY = 150;

    const SNAKE_CHAR = 'X';
    const FOOD_CHAR = 'O';
    const BACKGROUND_CHAR = ' ';

    /**
     * Direction of the snake
     * @var string
     */
    private $direction = input::RIGHT;
    /**
     * Number of tiles remaining to grow
     * 3 is the starting length of the snake
     * @var int
     */
    private $grow = 3;
    /**
     * Last moved time
     * @var
     */
    private $moveTime;

    private $game;
    private $stage;
    private $input;

    private $x_max;
    private $x_min;
    private $y_max;
    private $y_min;
    private $x = 2;
    private $y = 2;

    public function __construct(){
        $this->game = new game;
        $this->stage = new stage($this->game);
        $this->input = new input($this->game);
        $this->x_max = $this->stage->x() - 1;
        $this->y_max = $this->stage->y() - 1;
        $this->x_min = 2;
        $this->y_min = 2;
    }

    private static $commands = [
            0x1b5b44 => input::LEFT,
            0x1b5b43 => input::RIGHT,
            0x1b5b42 => input::DOWN,
            0x1b5b41 => input::UP,
    ];

    private static $movement = [
            input::LEFT => "\033[1D",
            input::RIGHT => "\033[1C",
            input::DOWN => "\033[1B",
            input::UP => "\033[1A",
    ];

    private $tailCoords = [];
    private $foodCoords = [];

    private function move()
    {
        if ((microtime(true) - $this->moveTime) * 1000 >= self::MOVE_EVERY) {
            $this->game->focus(1, 25);
            print str_pad($this->direction, 6, ' ');
            switch ($this->direction) {
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

            if ($this->death()) {
                $this->gameOverScreen();
            }

            $this->tailCoords['_' . $this->x . '-' . $this->y] = ['x' => $this->x, 'y' => $this->y];
            $this->game->focus($this->x, $this->y);
            print self::SNAKE_CHAR;

            if (!$this->grow) {
                $disappear = array_shift($this->tailCoords);
                $this->game->focus(10, 25);
                print ' x: '. $disappear['x'] . ' ' . 'y: ' . $disappear['y'] . ' ' . $this->grow;
                $this->game->focus($disappear['x'], $disappear['y']);
                print self::BACKGROUND_CHAR;
            }

            $this->grow = $this->grow == 0 ? 0 : --$this->grow;
            $this->game->focusHome();
            $this->moveTime = microtime(true);
        }

    }

    private function gameOverScreen()
    {
        $this->stage->fillScreen();
        $x_title = round($this->stage->x() / 3);
        $y_title = round($this->stage->y() / 2);
        $this->game->focus($x_title, $y_title);
        for ($i = 0, $c = 25; $i < $c; $i++) {
            print self::BACKGROUND_CHAR;
        }
        $this->game->focus($x_title, $y_title - 1);
        for ($i = 0, $c = 25; $i < $c; $i++) {
            print self::BACKGROUND_CHAR;
        }
        $this->game->focus($x_title, $y_title - 2);
        for ($i = 0, $c = 25; $i < $c; $i++) {
            print self::BACKGROUND_CHAR;
        }
        $this->game->focus($x_title, $y_title - 1);
        print "  You died. Score: " . $this->game->getScore();
        $this->game->focus(1, $this->stage->y() + 1);
        exit;
    }

    private function death()
    {
        if (
                $this->x < $this->x_min ||
                $this->x > $this->x_max ||
                $this->y < $this->y_min ||
                $this->y > $this->y_max ||
                !empty($this->tailCoords['_' . $this->x . '-' . $this->y])
        ) {
            return true;
        }
        return false;
    }

    private function hitFood()
    {
        if (!empty($this->foodCoords) && $this->x == $this->foodCoords['x'] && $this->y == $this->foodCoords['y']) {
            $this->game->incrementScore();
            $this->game->focus(30, 25);
            print " Score: " . $this->game->getScore() . ' ';
            return true;
        }
        return false;
    }

    private function generateFood()
    {
        if ($this->x_max * $this->y_max == count($this->tailCoords)) {
            die('You win!');
        } else {
            $validPosition = false;
            do {
                $x = rand($this->x_min, $this->x_max);
                $y = rand($this->y_min, $this->y_max);
                if (empty($this->tailCoords['_' . $x . '-' . $y])) {
                    $validPosition = true;
                }
            }
            while ($validPosition == false);
            $this->game->focus($x, $y);
            print self::FOOD_CHAR;
            $this->game->focusHome();
            $this->foodCoords['x'] = $x;
            $this->foodCoords['y'] = $y;
        }
    }

    public function run()
    {
        $this->stage->border();
        $this->generateFood();
        $command = input::RIGHT;
        while (true) {
            $this->input->listen(self::$commands, self::COMMAND_MAX);
            if ($this->input->command() && $this->input->command() != $command) {
                $this->direction = $this->input->command();
                $command = $this->input->command();
            }
            $this->move();
            if ($this->hitFood()) {
                $this->grow = self::GROW_LOOPS;
                $this->generateFood();
            }
        }
    }
    
}

$snake = new snake;
$snake->run();
