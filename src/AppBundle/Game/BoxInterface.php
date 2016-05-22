<?php

namespace AppBundle\Game;


interface BoxInterface
{
    public function isMine();

    public function isOpen();

    public function getValue();

    public function open();
}