<?php

namespace spec\AppBundle\Game;

use AppBundle\Entity\Game;
use AppBundle\Exception\GameManagerException;
use Doctrine\ORM\EntityManager;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Session\Session;

class GameManagerSpec extends ObjectBehavior
{
    const UUID_LENGTH = 36;

    /** @var Prophet */
    private $prophet;

    private $game;

    private $gameRepo;

    function let(Session $session, EntityManager $entityManager)
    {
        $this->beConstructedWith($session, $entityManager);

        $this->prophet = new Prophet;
        $this->game = $this->prophet->prophesize('AppBundle\Entity\Game');
        $this->gameRepo = $this->prophet->prophesize('AppBundle\Repository\GameRepository');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('AppBundle\Game\GameManager');
    }

    function it_creates_a_game(Session $session, EntityManager $entityManager)
    {
        $gameId = $this->newGame();
        $gameId->shouldBeAUuid();

        $session->set(sprintf("game_%s", $gameId->getWrappedObject()), Argument::Any())->shouldHaveBeenCalled();
        $entityManager->persist(Argument::Type(Game::class))->shouldHaveBeenCalled();
        $entityManager->flush()->shouldHaveBeenCalled();
    }

    function it_ends_a_game(EntityManager $entityManager)
    {
        $gameId = Uuid::uuid4()->toString();

        $this->makeGetGameExpectations($gameId, $entityManager);

        $status = Game::STATUS_CLEARED;
        $this->game->end($status)->willReturn($this->getDateInterval());
        $this->game->end($status)->shouldBeCalled();

        $entityManager->flush($this->game)->shouldBeCalled();

        $this->endGame($gameId, $status)->shouldReturnAnInstanceOf('DateInterval');
    }

    function it_throws_an_exception_if_game_is_ended_with_uncorrect_status(EntityManager $entityManager)
    {
        $gameId = Uuid::uuid4()->toString();
        $this->makeGetGameExpectations($gameId, $entityManager);

        $this->shouldThrow(GameManagerException::class)->duringEndGame($gameId, -1);
    }

    function it_throws_an_exception_if_try_to_end_a_game_that_cannot_be_ended(EntityManager $entityManager)
    {
        $gameId = Uuid::uuid4()->toString();

        $this->makeGetGameExpectations($gameId, $entityManager);

        $status = Game::STATUS_FAILED;
        $this->game->end($status)->shouldBeCalled();
        $this->game->canBeEnded()->willReturn(false);

        $this->shouldThrow(GameManagerException::class)->duringEndGame($gameId, $status);
    }

    public function getMatchers()
    {
        return [
            'beAUuid' => function ($value) {
                if (!is_string($value)) {
                    throw new FailureException(sprintf(
                        'Expected a string but got ... ?'
                    ));
                }

                if (strlen($value) != self::UUID_LENGTH) {
                    throw new FailureException(sprintf(
                        'Expected a Uuid but got ... ?'
                    ));
                }

                return true;
            },
        ];
    }

    /**
     * @return bool|\DateInterval
     */
    private function getDateInterval()
    {
        $start = new \DateTime();
        $end = new \DateTime();

        return $start->diff($end);
    }

    /**
     * @param $gameId
     * @param EntityManager $entityManager
     */
    private function makeGetGameExpectations($gameId, EntityManager $entityManager)
    {
        $entityManager->getRepository('AppBundle:Game')->willReturn($this->gameRepo);
        $this->gameRepo->findBy(['id' => $gameId])->willReturn($this->game);
    }
}
