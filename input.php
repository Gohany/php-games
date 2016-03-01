<?php

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