<?php

namespace AppBundle\Game;

use AppBundle\Exception\OpeningMineBoxException;

class Box
{
    const MINED_BOX_VALUE = -1;

    /** @var bool */
    private $gotMine;

    /** @var bool */
    private $isOpen;

    /** @var int */
    private $value;

    /**
     * @param $gotMine
     * @param $value
     */
    public function __construct($gotMine, $value)
    {
        $this->gotMine = $gotMine;
        $this->isOpen = false;

        if ($gotMine) {
            $value = self::MINED_BOX_VALUE;
        }

        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function isMine()
    {
        return $this->gotMine;
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        return $this->isOpen;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function open()
    {
        if ($this->isMine()) {
            throw new OpeningMineBoxException("You're opening a mine box. Your game should be over");
        }

        $this->isOpen = true;

        return true;
    }
}
