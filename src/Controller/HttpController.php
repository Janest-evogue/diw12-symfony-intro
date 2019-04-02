<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HttpController
 * @package App\Controller
 *
 * @Route("/http")
 */
class HttpController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->render('http/index.html.twig', [
            'controller_name' => 'HttpController',
        ]);
    }

    /**
     * @Route("/request")
     */
    public function request(Request $request)
    {
        dump($_GET);

        /*
         * $request->query est l'attribut de l'objet Request qui
         * fait référence à $_GET, sa méthode all() retourne la totalité
         * de $_GET
         */
        dump($request->query->all());

        /*
         * $_GET['nom']
         * null si 'nom' n'est pas dans $_GET
         */
        dump($request->query->get('nom'));

        // 2e paramètre optionnel : valeur par défaut si 'nom' n'est pas dans $_GET
        dump($request->query->get('nom', 'anonyme'));

        // GET ou POST
        dump($request->getMethod());

        // éq: if (!empty($_POST)) {
        if ($request->isMethod('POST')) {
            /*
             * $request->request est l'attribut de l'objet Request qui
             * fait référence à $_POST, sa méthode all() retourne la totalité
             * de $_POST
             */
            dump($request->request->all());

            // $_POST['nom']
            dump($request->request->get('nom'));
        }

        return $this->render('http/request.html.twig');
    }

    /**
     * @param Request $request
     *
     * @Route("/session")
     */
    public function session(Request $request)
    {
        // pour accéder à la session
        $session = $request->getSession();

        // ajouter des élément à la session
        $session->set('nom', 'Anest');
        $session->set('prenom', 'Julien');

        // les éléments stockés par l'objet Session le sont
        // dans $_SESSION['_sf2_attributes']
        dump($_SESSION);

        // tous les éléments de la session
        dump($session->all());

        // accéder à un élément de la session
        dump($session->get('nom'));

        // supprime un élément de la session
        $session->remove('prenom');

        dump($session->all());

        // vide la session
        $session->clear();

        dump($session->all());

        return $this->render('http/session.html.twig');
    }

    /**
     * @return Response
     *
     * @Route("/response")
     */
    public function response(Request $request)
    {
        // une réponse qui contient du texte brut
        $response = new Response('Contenu de la page en texte brut');

        // http://127.0.0.1:8000/http/response?type=twig
        if ($request->query->get('type') == 'twig') {
            // $this->render() retourne un objet Response dont le contenu
            // est le HTML construit par le template
            $response = $this->render('http/response.html.twig');
        // http://127.0.0.1:8000/http/response?type=json
        } elseif ($request->query->get('type') == 'json') {
            $exemple = [
                'nom' => 'Anest',
                'prenom' => 'Julien'
            ];

            // encode $exemple en json et retourne une réponse
            // avec le Content-Type application/json dans les entêtes HTTP
            $response = new JsonResponse($exemple);

            //$response = new Response(json_encode($exemple));

        // http://127.0.0.1:8000/http/response?found=no
        } elseif ($request->query->get('found') == 'no') {
            // on jette cette exception pour retourner une 404
            throw new NotFoundHttpException();
        // http://127.0.0.1:8000/http/response?redirect=index
        } elseif ($request->query->get('redirect') == 'index') {
            // redirige vers la page dont la route a pour nom app_index_index
            // soit celle contenu dans la méthode index() de IndexController
            $response = $this->redirectToRoute('app_index_index');
        // http://127.0.0.1:8000/http/response?redirect=bonjour
        } elseif ($request->query->get('redirect') == 'bonjour') {
            // redirection vers une route avec une partie variable
            $response = $this->redirectToRoute(
                'app_index_bonjour',
                [
                    'qui' => 'le monde'
                ]
            );
        }

        // une méthode de contrôleur doit toujours retourner
        // un objet instance de Response
        return $response;
    }

    /**
     * @Route("/flash")
     */
    public function flash()
    {
        // enregistre un message dans la session qui en sera supprimé
        // dès qu'on l'aura affiché une fois
        $this->addFlash('success', 'Message de confirmation');
        $this->addFlash('success', 'Autre message de confirmation');
        $this->addFlash('error', "Message d'erreur");

        return $this->redirectToRoute('app_http_flashed');
    }

    /**
     * @Route("/flashed")
     */
    public function flashed()
    {
        return $this->render('http/flashed.html.twig');
    }

    /*
     * Faire une page avec un formulaire en post avec :
     * - email (text)
     * - message (textarea)
     *
     * Si le formulaire est envoyé, vérifier que les deux champs sont remplis
     * Si non, afficher un message d'erreur
     * Si oui, enregistrer les valeurs en session et rediriger vers
     * une nouvelle page qui les affiche et vide la session
     * Dans cette page, si la session est vide, on redirige vers le formulaire
     */

    /**
     * @Route("/formulaire")
     */
    public function formulaire(Request $request)
    {
        $erreur = '';

        // si le formulaire a été envoyé
        if ($request->isMethod('POST')) {
            // $_POST['email'] & $_POST['message']
            $email = $request->request->get('email');
            $message = $request->request->get('message');

            if (!empty($email) && !empty($message)) {
                $session = $request->getSession();
                // enregistrement en session
                $session->set('email', $email);
                $session->set('message', $message);

                // redirection vers la page resultat
                return $this->redirectToRoute('app_http_resultat');
            } else {
                $erreur = 'Tous les champs sont obligatoires';
            }

        }

        return $this->render(
            'http/formulaire.html.twig',
            [
                'erreur' => $erreur
            ]
        );
    }

    /**
     * @Route("/resultat")
     */
    public function resultat(Request $request)
    {
        $session = $request->getSession();

        // redirection vers le formulaire si la session est vide
        if (empty($session->all())) {
        // if ($session->isEmpty()) {
            return $this->redirectToRoute('app_http_formulaire');
        }

        // l'email & le message stockés en session
        $email = $session->get('email');
        $message = $session->get('message');

        // vider la session
        $session->clear();

        return $this->render(
            'http/resultat.html.twig',
            [
                'email' => $email,
                'message' => $message
            ]
        );
    }
}









