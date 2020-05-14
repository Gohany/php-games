<?php

interface IGame
{
    /**
     * Increase score by one
     * @return game
     */
    public function incrementScore() : game;

    /**
     * Set score
     * @param int $score
     * @return game
     */
    public function setScore(int $score) : game;

    /**
     * Get score
     * @return int
     */
    public function getScore() : int;

    /**
     * Move pointer to selected location of entire output
     * @param $x
     * @param $y
     * @return void
     */
    public function focus($x, $y);

    /**
     * Move pointer to 1,1 of entire output
     * @return void
     */
    public function focusHome();
}

class game implements IGame
{

    private $score = 0;

    public function incrementScore() : game
    {
        $this->score++;
        return $this;
    }

    public function setScore(int $score) : game
    {
        $this->score = $score;
        return $this;
    }

    public function getScore() : int
    {
        return $this->score;
    }

    public function focus($x, $y)
    {
        print stage::ESC . "[" . $y . ";" . $x . "H";
    }

    public function focusHome()
    {
        $this->focus(1, 1);
    }
    
}