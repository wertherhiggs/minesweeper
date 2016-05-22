<?php

namespace AppBundle\Game;

use AppBundle\Exception\OpeningMineBoxException;

class MinedBox implements BoxInterface
{
    const VALUE = -1;

    public function isMine()
    {
        return true;
    }

    public function isOpen()
    {
        return false;
    }

    public function getValue()
    {
        return self::VALUE;
    }

    public function open()
    {
        throw new OpeningMineBoxException("You're opening a mine box. Your game should be over");
    }
}
