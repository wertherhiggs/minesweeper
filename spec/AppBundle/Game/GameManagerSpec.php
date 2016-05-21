<?php

namespace spec\AppBundle\Game;

use AppBundle\Entity\Game;
use AppBundle\Exception\GameException;
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

    const UNCORRECT_GAME_STATUS = -1;

    const GAME_ID_SESSION_KEY = 'gameId';

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

        $session->set(self::GAME_ID_SESSION_KEY, $gameId->getWrappedObject())->shouldHaveBeenCalled();
        $entityManager->persist(Argument::Type(Game::class))->shouldHaveBeenCalled();
        $entityManager->flush()->shouldHaveBeenCalled();
    }

    function it_creates_a_game_and_save_previous_one_with_failure_status(Session $session, EntityManager $entityManager)
    {
        $previousGameId = Uuid::uuid4()->toString();
        $previousGame = $this->prophet->prophesize('AppBundle\Entity\Game');

        $session->get(self::GAME_ID_SESSION_KEY)->shouldBeCalled();
        $session->get(self::GAME_ID_SESSION_KEY)->willReturn($previousGameId);

        $entityManager->getRepository('AppBundle:Game')->willReturn($this->gameRepo);
        $this->gameRepo->findOneBy(['id' => $previousGameId])->willReturn($previousGame);

        $previousGame->getStatus()->willReturn(Game::STATUS_STARTED);
        $previousGame->end(Game::STATUS_FAILED)->shouldBeCalled();

        $entityManager->flush($previousGame)->shouldBeCalled();

        $session->set(self::GAME_ID_SESSION_KEY, Argument::any())->shouldBeCalled();
        $entityManager->persist(Argument::Type(Game::class))->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->newGame();
    }

    function it_ends_a_game(Session $session, EntityManager $entityManager)
    {
        $gameId = $this->newGame()->getWrappedObject();

        $session->set(self::GAME_ID_SESSION_KEY, $gameId)->shouldBeCalled();
        $session->get(self::GAME_ID_SESSION_KEY)->willReturn($gameId);

        $this->makeGetGameExpectations($gameId, $entityManager);

        $status = Game::STATUS_CLEARED;
        $this->game->end($status)->shouldBeCalled();
        $this->game->end($status)->willReturn($this->getDateInterval());

        $entityManager->flush($this->game)->shouldBeCalled();
        $session->clear(self::GAME_ID_SESSION_KEY)->shouldBeCalled();

        $this->endGame($status)->shouldReturnAnInstanceOf('DateInterval');
    }

    function it_throws_an_exception_if_game_cannot_be_ended(Session $session, EntityManager $entityManager)
    {
        $gameId = $this->newGame()->getWrappedObject();

        $session->set(self::GAME_ID_SESSION_KEY, $gameId)->shouldBeCalled();
        $session->get(self::GAME_ID_SESSION_KEY)->willReturn($gameId);

        $this->makeGetGameExpectations($gameId, $entityManager);

        $this->game->end(self::UNCORRECT_GAME_STATUS)->willThrow(new GameException());

        $this->shouldThrow(GameManagerException::class)->duringEndGame(self::UNCORRECT_GAME_STATUS);
    }

    function it_throws_an_exception_if_game_cannot_be_found_onto_db_during_end(Session $session, EntityManager $entityManager)
    {
        $gameId = $this->newGame()->getWrappedObject();

        $session->set(self::GAME_ID_SESSION_KEY, $gameId)->shouldBeCalled();
        $session->get(self::GAME_ID_SESSION_KEY)->willReturn($gameId);

        $entityManager->getRepository('AppBundle:Game')->willReturn($this->gameRepo);
        $this->gameRepo->findOneBy(['id' => $gameId])->willReturn(null);

        $this->shouldThrow(GameManagerException::class)->duringEndGame(self::UNCORRECT_GAME_STATUS);
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
        $this->gameRepo->findOneBy(['id' => $gameId])->willReturn($this->game);
    }
}
