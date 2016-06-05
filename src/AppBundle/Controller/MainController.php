<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    /**
     * @Route("/play", name="app.main.start_game")
     * @Template()
     */
    public function startGameAction()
    {
        $gameManager = $this->get('app.game.game_manager');
        $gameManager->newGame();

        return [
            'scheme' => $gameManager->getScheme(),
        ];
    }

    /**
     * @Route("/open", name="app.main.open_box", condition="request.isXmlHttpRequest()")
     */
    public function openBox(Request $request)
    {
        $rowIndex = $request->query->get('r');
        $columnIndex = $request->query->get('c');

        $gameManager = $this->get('app.game.game_manager');
        $result = $gameManager->open($rowIndex, $columnIndex);

        return new JsonResponse(json_encode($result));
    }
}