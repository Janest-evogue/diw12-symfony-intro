<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TwigController
 * @package App\Controller
 *
 * Préfixe de route : toutes les url définies dans les routes
 * de ce contrôleur seront préfixées par /twig
 * @Route("/twig")
 */
class TwigController extends AbstractController
{
    /**
     * L'url de cette route est /twig ou /twig/ parce qu'il y a le préfixe
     * de route défini pour la classe
     *
     * @Route("/")
     */
    public function index()
    {
        return $this->render(
            'twig/index.html.twig',
            [
                'auj' => new \DateTime()
            ]
        );
    }
}
