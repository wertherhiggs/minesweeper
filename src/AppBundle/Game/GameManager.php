<?php

namespace AppBundle\Game;

use AppBundle\Entity\Game;
use AppBundle\Exception\GameException;
use AppBundle\Exception\GameManagerException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

class GameManager
{
    /** @var Session */
    private $session;

    /** @var EntityManager  */
    private $entityManager;

    /** @var Game */
    private $game;

    /**
     * @param Session $session
     * @param EntityManager $entityManager
     */
    public function __construct(Session $session, EntityManager $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->game = null;
    }

    /**
     * @return string
     */
    public function newGame()
    {
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
        $this->session->set(sprintf("game_%s", $gameId), true);
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
     * @param $gameId
     * @param $status
     *
     * @return \DateInterval
     *
     * @throws \Exception
     */
    public function endGame($gameId, $status)
    {
        try {
            $this->session->get(sprintf("game_%s", $gameId));
            $game = $this->getGameFromId($gameId);

            $totalGamingTime = $game->end($status);
            $this->entityManager->flush($game);

            return $totalGamingTime;
        } catch (GameException $e) {
            throw new GameManagerException("Internal error! something went wrong!");
        }
    }

    /**
     * @param $gameId
     *
     * @return Game
     */
    protected function getGameFromId($gameId)
    {
        return $this->getGameRepository()->findBy([
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

}
