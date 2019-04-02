<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * Page à la racine du nom de domaine
     *
     * @Route("/")
     */
    public function index()
    {
        return $this->render('index/index.html.twig');
    }

    /**
     * localhost:8000/hello
     *
     * Url de la page
     * @Route("/hello")
     */
    public function hello()
    {
        // rendu du fichier qui construit le HTML contenu dans la page
        return $this->render('index/hello.html.twig');
    }

    /**
     * Une route avec une partie variable (entre accolades)
     * Le $qui en paramètre de la méthode (même nom que dans la route)
     * contient la valeur de cette partie variable
     *
     * @Route("/bonjour/{qui}")
     */
    public function bonjour($qui)
    {
        return $this->render(
            'index/bonjour.html.twig',
            // le tableau en 2e paramètre de la méthode render() permet
            // de passer des variables au template :
            // le nom de la variable est la clé dans le tableau
            [
                'nom' => $qui
            ]
        );
    }

    /**
     * Valeur par défaut pour la partie variable :
     * la route matche /salut/unNom : $qui vaut "unNom"
     * et matche aussi /salut ou /salut/ : $qui vaut "à toi"
     *
     * @Route("/salut/{qui}", defaults={"qui": "à toi"})
     */
    public function salut($qui)
    {
        return $this->render(
            'index/salut.html.twig',
            [
                'qui' => $qui
            ]
        );
    }

    /**
     * Cette route matche /coucou/Julien et /coucou/Julien-Anest
     *
     * @Route("/coucou/{prenom}-{nom}", defaults={"nom": ""})
     *
     */
    public function coucou($prenom, $nom)
    {
        $qui = rtrim($prenom . ' ' . $nom);

        // un même template peut être utilisé dans plusieurs page
        return $this->render(
            'index/salut.html.twig',
            [
                'qui' => $qui
            ]
        );
    }

    /**
     * id doit être un nombre (\d+ en expression régulière)
     * sinon la route ne matche pas => 404
     *
     * @Route("/categorie/{id}", requirements={"id": "\d+"})
     */
    public function categorie($id)
    {
        return $this->render(
            'index/categorie.html.twig',
            [
                'id' => $id
            ]
        );
    }
}










