<?php

namespace AppBundle\Game;

use AppBundle\Entity\Game;
use AppBundle\Exception\GameException;
use AppBundle\Exception\GameManagerException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

class GameManager
{
    const GAME_ID_SESSION_KEY = 'gameId';

    /** @var Session */
    private $session;

    /** @var EntityManager  */
    private $entityManager;

    /**
     * @param Session $session
     * @param EntityManager $entityManager
     */
    public function __construct(Session $session, EntityManager $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     */
    public function newGame()
    {
        $this->handlePreviousGame();

        $game = new Game();
        $gameId = $game->getId();

        $this->setGameUuidInSession($gameId);
        $this->saveGame($game);

        return $gameId;
    }

    /**
     * @param $gameId
     */
    protected function setGameUuidInSession($gameId)
    {
        $this->session->set(self::GAME_ID_SESSION_KEY, $gameId);
    }

    /**
     * @return mixed
     */
    protected function getGameUuidFromSession()
    {
        return $this->session->get(self::GAME_ID_SESSION_KEY);
    }

    protected function clearGameFromSession()
    {
        $this->session->clear(self::GAME_ID_SESSION_KEY);
    }

    /**
     * @param Game $game
     */
    protected function saveGame(Game $game)
    {
        $this->entityManager->persist($game);
        $this->entityManager->flush();
    }

    /**
     * @param $status
     *
     * @return \DateInterval
     *
     * @throws \Exception
     */
    public function endGame($status)
    {
        $gameId = $this->session->get(self::GAME_ID_SESSION_KEY);
        $game = $this->getGameFromId($gameId);

        if (!$game instanceof Game) {
            throw new GameManagerException(sprintf("Game %s was not saved into db", $gameId));
        }

        try {
            $totalGamingTime = $game->end($status);
            $this->entityManager->flush($game);
            $this->clearGameFromSession();

            return $totalGamingTime;
        } catch (GameException $e) {
            throw new GameManagerException($e->getMessage());
        }
    }

    /**
     * @param $gameId
     *
     * @return Game
     */
    protected function getGameFromId($gameId)
    {
        return $this->getGameRepository()->findOneBy([
            'id' => $gameId
        ]);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getGameRepository()
    {
        return $this->entityManager->getRepository('AppBundle:Game');
    }

    protected function handlePreviousGame()
    {
        $gameId = $this->getGameUuidFromSession();

        if (!$gameId) {
            return;
        }

        $previousGame = $this->getGameFromId($gameId);
        if ($previousGame->getStatus() == Game::STATUS_STARTED) {
            $previousGame->end(Game::STATUS_FAILED);
        }

        $this->entityManager->flush($previousGame);
    }

}
