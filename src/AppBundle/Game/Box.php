<?php

namespace AppBundle\Game;

use AppBundle\Exception\OpeningMineBoxException;

class Box implements BoxInterface
{
    const MINED_BOX_VALUE = -1;

    /** @var bool */
    private $isOpen;

    /** @var int */
    private $value;

    /**
     * @param integer $value
     */
    public function __construct($value)
    {
        $this->isOpen = false;
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function isMine()
    {
        return false;
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
     */
    public function open()
    {
        $this->isOpen = true;

        return true;
    }
}
