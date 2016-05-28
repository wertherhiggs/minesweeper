<?php

namespace spec\AppBundle\Game;

use AppBundle\Entity\Game;
use AppBundle\Exception\OpeningMineBoxException;
use AppBundle\Game\Box;
use AppBundle\Game\BoxInterface;
use AppBundle\Game\GameManager;
use AppBundle\Game\MinedBox;
use AppBundle\Game\SchemaManager;
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

    const FAKE_SCHEMA_ROWS = 8;

    const FAKE_SCHEMA_COLUMNS = 8;

    const FAKE_SCHEMA_MINES_NUMBER = 10;

    /** @var Prophet */
    private $prophet;

    private $game;

    private $gameRepo;

    function let(Session $session, EntityManager $entityManager, SchemaManager $schemaManager)
    {
        $this->beConstructedWith($session, $entityManager, $schemaManager);

        $this->prophet = new Prophet;
        $this->game = $this->prophet->prophesize('AppBundle\Entity\Game');
        $this->gameRepo = $this->prophet->prophesize('AppBundle\Repository\GameRepository');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('AppBundle\Game\GameManager');
    }

    function it_creates_a_game(Session $session, EntityManager $entityManager, SchemaManager $schemaManager)
    {
        $schemaDouble = $this->createASchemaDouble();
        $schemaManager->createSchema(Argument::any(), Argument::any(), Argument::any())->willReturn($schemaDouble);

        $gameId = $this->newGame();
        $gameId->shouldBeAUuid();

        $session->set(self::GAME_ID_SESSION_KEY, $gameId->getWrappedObject())->shouldHaveBeenCalled();
        $entityManager->persist(Argument::Type(Game::class))->shouldHaveBeenCalled();
        $entityManager->flush()->shouldHaveBeenCalled();
    }

    function it_creates_a_game_and_save_previous_one_with_failure_status(
        Session $session,
        EntityManager $entityManager,
        SchemaManager $schemaManager
    ) {
        $previousGameId = Uuid::uuid4()->toString();
        $previousGame = $this->prophet->prophesize('AppBundle\Entity\Game');

        $session->get(self::GAME_ID_SESSION_KEY)->shouldBeCalled();
        $session->get(self::GAME_ID_SESSION_KEY)->willReturn($previousGameId);

        $entityManager->getRepository('AppBundle:Game')->willReturn($this->gameRepo);
        $this->gameRepo->findOneBy(['id' => $previousGameId])->willReturn($previousGame);

        $previousGame->getStatus()->willReturn(Game::STATUS_STARTED);
        $previousGame->end(Game::STATUS_FAILED)->shouldBeCalled();

        $entityManager->flush($previousGame)->shouldBeCalled();

        $schemaDouble = $this->createASchemaDouble();
        $schemaManager->createSchema(Argument::any(), Argument::any(), Argument::any())->willReturn($schemaDouble);

        $session->set(self::GAME_ID_SESSION_KEY, Argument::any())->shouldBeCalled();
        $session->clear(GameManager::GAME_ID_SESSION_KEY)->shouldBeCalled();
        $entityManager->persist(Argument::Type(Game::class))->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->newGame();
    }

    function it_opens_a_box_correctly(Session $session, EntityManager $entityManager, SchemaManager $schemaManager)
    {
        $schemaDouble =  $this->createASchemaDouble();
        $openedSchemaDouble = $this->openSchemaDouble();

        $this->retrieveGameExpectationsAndPromises($session, $entityManager, $schemaManager);
        $schemaManager->openBox(0, 0, $schemaDouble)->shouldBeCalled();
        $schemaManager->openBox(0, 0, $schemaDouble)->willReturn($openedSchemaDouble);

        $this->game->setSchema($openedSchemaDouble)->shouldBeCalled();
        $entityManager->flush($this->game)->shouldBeCalled();

        $this->open(0, 0)->shouldBeArray();
    }

    function it_ends_a_game_after_open_a_mine(Session $session, EntityManager $entityManager, SchemaManager $schemaManager)
    {
        $schemaDouble =  $this->createASchemaDouble();

        $this->retrieveGameExpectationsAndPromises($session, $entityManager, $schemaManager);
        $schemaManager->openBox(3, 1, $schemaDouble)->shouldBeCalled();
        $schemaManager->openBox(3, 1, $schemaDouble)->willThrow(OpeningMineBoxException::class);

        $this->game->end(Game::STATUS_FAILED)->shouldBeCalled();
        $entityManager->flush($this->game)->shouldBeCalled();
        $session->clear(GameManager::GAME_ID_SESSION_KEY)->shouldBeCalled();

        $this->open(3, 1)->shouldReturn(false);
    }

    function it_does_nothing_if_try_to_open_a_non_existent_box()
    {
        $this->open(-1, 0)->shouldReturn(null);
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
     * @param Session $session
     * @param EntityManager $entityManager
     * @param SchemaManager $schemaManager
     */
    private function retrieveGameExpectationsAndPromises(
        Session $session,
        EntityManager $entityManager,
        SchemaManager $schemaManager
    ) {
        $schemaDouble = $this->createASchemaDouble();
        $schemaManager->createSchema(Argument::any(), Argument::any(), Argument::any())->willReturn($schemaDouble);

        $gameId = $this->newGame()->getWrappedObject();

        $session->set(self::GAME_ID_SESSION_KEY, $gameId)->shouldBeCalled();
        $session->get(self::GAME_ID_SESSION_KEY)->willReturn($gameId);

        $entityManager->getRepository('AppBundle:Game')->willReturn($this->gameRepo);
        $this->gameRepo->findOneBy(['id' => $gameId])->willReturn($this->game);

        $this->game->getSchema()->willReturn($schemaDouble);
    }

    /**
     * @return array
     */
    private function createASchemaDouble()
    {
        return $this->getSchemaDouble();
    }

    /**
     * @return array
     */
    private function getSchemaDouble()
    {
        return [
            [new Box(0), new Box(0), new Box(1), new Box(1), new Box(1), new Box(0), new Box(0), new Box(0)],
            [new Box(0), new Box(0), new Box(1), new MinedBox(), new Box(2), new Box(1), new Box(1), new Box(0)],
            [new Box(1), new Box(1), new Box(3), new Box(3), new Box(4), new MinedBox(), new Box(1), new Box(0)],
            [new Box(1), new MinedBox(), new Box(2), new MinedBox(), new MinedBox(), new Box(3), new Box(2), new Box(0)],
            [new Box(1), new Box(1), new Box(3), new Box(3), new Box(4), new MinedBox(), new Box(1), new Box(0)],
            [new Box(1), new Box(1), new Box(1), new MinedBox(), new Box(2), new Box(1), new Box(2), new Box(1)],
            [new MinedBox(), new Box(1), new Box(1), new Box(1), new Box(1), new Box(0), new Box(1), new MinedBox()],
            [new MinedBox(), new Box(2), new Box(0), new Box(0), new Box(0), new Box(0), new Box(1), new Box(1)],
        ];
    }

    /**
     * @return array
     */
    private function openSchemaDouble()
    {
        $schemaDouble = $this->getSchemaDouble();
        /** @var $box BoxInterface */
        $box = $schemaDouble[0][2];

        $box->open();

        return $schemaDouble;
    }
}
