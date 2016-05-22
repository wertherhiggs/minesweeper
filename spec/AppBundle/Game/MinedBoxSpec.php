<?php

namespace spec\AppBundle\Game;

use AppBundle\Exception\OpeningMineBoxException;
use AppBundle\Game\MinedBox;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MinedBoxSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('AppBundle\Game\MinedBox');
    }

    function it_checks_if_it_is_a_mine()
    {
        $this->isMine()->shouldBeEqualTo(true);
    }

    function it_returns_unopen_state_after_construct()
    {
        $this->isOpen()->shouldBeEqualTo(false);
    }

    function it_returns_a_non_valid_value_if_is_a_mine()
    {
        $this->getValue()->shouldBeEqualTo(MinedBox::VALUE);
    }

    function it_throws_an_exception_if_try_to_open_a_mine()
    {
        $this->shouldThrow(OpeningMineBoxException::class)->duringOpen();
    }
}
