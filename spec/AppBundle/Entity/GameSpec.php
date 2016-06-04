<?php

namespace spec\AppBundle\Entity;

use AppBundle\Entity\Game;
use AppBundle\Exception\GameException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GameSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('AppBundle\Entity\Game');
    }

    function it_starts_when_created()
    {
        $this->getStartStamp()->shouldBeAnInstanceOf('DateTime');
        $this->getEndStamp()->shouldBeNull();
        $this->getStatus()->shouldBeEqualTo(Game::STATUS_STARTED);
        $this->getScheme()->shouldBeArray();
    }

    function it_ends_correctly()
    {
        $this->end(Game::STATUS_CLEARED)->shouldBeAnInstanceOf('DateInterval');
    }

    function it_throws_an_exception_if_endend_with_incorrect_status()
    {
        $this->shouldThrow(GameException::class)->duringEnd(-1);
    }

    function it_throws_an_exception_if_game_cannot_be_ended()
    {
        $this->end(Game::STATUS_CLEARED);
        $this->shouldThrow(GameException::class)->duringEnd(Game::STATUS_CLEARED);
    }
}
