<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DoctrineController
 * @package App\Controller
 *
 * @Route("/doctrine")
 */
class DoctrineController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->render('doctrine/index.html.twig', [
            'controller_name' => 'DoctrineController',
        ]);
    }

    /**
     * @Route("/dbal")
     */
    public function dbal()
    {
        // contient le DBAL de Doctrine = surcouche à PDO
        /** @var Connection $cnx */
        $cnx = $this->getDoctrine()->getConnection();

        // utilisable comme PDO :
        $stmt = $cnx->query('SELECT * FROM abonne WHERE id = 1');
        dump($stmt->fetch());

        // exécute la requête et fetch en FETCH_ASSOC dans la même méthode
        $abonne1 = $cnx->fetchAssoc(
            'SELECT * FROM abonne WHERE id = :id',
            [
                ':id' => 1
            ]
        );

        dump($abonne1);

        // exécute la requête et fetchAll en FETCH_ASSOC dans la même méthode
        $abonnes = $cnx->fetchAll('SELECT * FROM abonne');

        dump($abonnes);

        // exécute la requête et fetchColumn
        $nb = $cnx->fetchColumn('SELECT count(*) FROM abonne');

        dump($nb);

        // insert into abonne (prenom) values ('Ben')
        //$cnx->insert('abonne', ['prenom' => 'Ben']);

        // update abonne set prenom = 'Jules' where id = 1
        $cnx->update('abonne', ['prenom' => 'Jules'], ['id' => 1]);

        // delete from abonne where id = 3
        $cnx->delete('abonne', ['id' => 3]);

        return $this->render('doctrine/dbal.html.twig');
    }

    /**
     * @Route("/user/{id}", requirements={"id": "\d+"})
     */
    public function getOneUser($id)
    {
        // gestionnaire d'entités de Doctrine
        $em = $this->getDoctrine()->getManager();

        /*
         * User::class = 'App\Entity\User'
         * Retourne un objet User dont les attributs sont settés
         * à partir de la bdd dans la table user à l'id passé en 2e paramètre
         * ou null si l'id n'existe pas dans la table user
         */
        $user = $em->find(User::class, $id);

        /* en version longue :
        $repository contient une instance de App\Repository\UserRepository

        $repository = $em->getRepository(User::class);
        $repository->find($id);
        */

        dump($user);

        if (is_null($user)) {
            // renvoie une 404
            throw new NotFoundHttpException();
        }

        return $this->render(
            'doctrine/get_one_user.html.twig',
            [
                'user' => $user
            ]
        );
    }

    /**
     * @Route("/list-users")
     */
    public function listUsers()
    {
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository(User::class);
        // retourne tous les utilisateurs de la table user
        // sous forme d'un tableau d'objets User
        $users = $repository->findAll();

        dump($users);

        return $this->render(
            'doctrine/list_users.html.twig',
            [
                'users' => $users
            ]
        );
    }

    /**
     * @Route("/search-email")
     */
    public function searchEmail(Request $request)
    {
        $twigVariables = [];

        if ($request->query->has('email')) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(User::class);

            // findOneBy() quand on est sûr qu'il n'y aura pas plus d'un résultat
            // Retourne un objet User ou null
            $user = $repository->findOneBy([
                'email' => $request->query->get('email')
            ]);

            $twigVariables['user'] = $user;
        }

        return $this->render('doctrine/search_email.html.twig', $twigVariables);
    }

    /**
     * @Route("/search-firstname/{firstname}")
     */
    public function searchFirstname($firstname)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(User::class);

        // retourne un tableau d'objets User filtrés sur le prénom ;
        // un tableau vide si aucun résultat
//        $users = $repository->findBy([
//            'firstname' => $firstname
//        ]);

        $users = $repository->findBy(
            [
                'firstname' => $firstname
            ],
            // avec un tri ascendant sur le lastname
            [
                'lastname' => 'ASC'
            ]
        );

        return $this->render(
            'doctrine/search_firstname.html.twig',
            [
                'users' => $users
            ]
        );
    }

    /**
     * @Route("/create-user")
     */
    public function createUser(Request $request)
    {
        // si le formulaire a été soumis
        if ($request->isMethod('POST')) {
            // $data contient tout $_POST
            $data = $request->request->all();

            // on instancie un objet User
            $user = new User();
            // et on sette ses attributs avec les données du formulaire
            $user
                ->setLastname($data['lastname'])
                ->setFirstname($data['firstname'])
                ->setEmail($data['email'])
                // le setter de birthdate attend un objet DateTime
                ->setBirthdate(new \DateTime($data['birthdate']))
            ;

            $em = $this->getDoctrine()->getManager();

            // indique au gestionnaire d'entités qu'il faudra enregistrer le User
            // en bdd au prochain appel de la méthode flush()
            $em->persist($user);
            // enregistrement effectif
            $em->flush();
        }

        return $this->render('doctrine/create_user.html.twig');
    }

    /**
     * @Route("/update-user/{id}")
     */
    public function updateUser(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->find(User::class, $id);

        if (is_null($user)) {
            // renvoie une 404
            throw new NotFoundHttpException();
        }

        // si le formulaire a été soumis
        if ($request->isMethod('POST')) {
            // $data contient tout $_POST
            $data = $request->request->all();

            // on sette ses attributs du User avec les données du formulaire
            $user
                ->setLastname($data['lastname'])
                ->setFirstname($data['firstname'])
                ->setEmail($data['email'])
                // le setter de birthdate attend un objet DateTime
                ->setBirthdate(new \DateTime($data['birthdate']))
            ;

            // indique au gestionnaire d'entités qu'il faudra enregistrer le User
            // en bdd au prochain appel de la méthode flush()
            $em->persist($user);
            // enregistrement effectif
            $em->flush();
        }

        return $this->render(
            'doctrine/update_user.html.twig',
            [
                'user' => $user
            ]
        );
    }

    /**
     * @Route("/delete-user/{id}")
     */
    public function deleteUser($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->find(User::class, $id);

        // si l'id existe en bdd
        if (!is_null($user)) {
            // suppression du User en bdd
            $em->remove($user);
            $em->flush();

            return new Response('Utilisateur supprimé');
        } else {
            return new Response('Utilisateur inexistant');
        }
    }

    /**
     * ParamConverter :
     * la paramètre dans l'url s'appelle id comme la clé primaire de la table user.
     * En typant User le paramètre passé à la méthode, on récupère dasn $user
     * un objet User défini à partir d'un SELECT sur la table user avec une clause WHERE
     * sur cet id
     * Si l'id n'existe pas, le ParamConverter retourne une 404
     *
     * @Route("/another-user/{id}")
     */
    public function getAnotherUser(User $user)
    {
        return $this->render(
            'doctrine/get_one_user.html.twig',
            [
                'user' => $user
            ]
        );
    }

    /**
     * @Route("/publication/author/{id}")
     */
    public function publicationsByAuthor(User $user)
    {
        dump($user);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Publication::class);

        $publications = $repository->findBy(
            [
                'author' => $user
            ]
        );

        return $this->render(
            'doctrine/publications.html.twig',
            [
                'publications' => $publications
            ]
        );
    }

    /**
     * @Route("/user/{id}/publications")
     */
    public function userPublications(User $user)
    {
        dump($user);

        /*
         * En appelant le getter de l'attribut $publications d'un objet User,
         * Doctrine va automatiquement faire une requête en bdd pour y mettre
         * les publications liées (lazy loading)
         */

        return $this->render(
            'doctrine/publications.html.twig',
            [
                'publications' => $user->getPublications()
            ]
        );
    }

    /**
     * @Route("/user/{id}/teams")
     */
    public function teamsByUser(User $user)
    {
        return $this->render(
            'doctrine/teams.html.twig',
            [
                'teams' => $user->getTeams()
            ]
        );
    }

    /**
     * @Route("/team/{id}/users")
     */
    public function usersByTeam(Team $team)
    {
        return $this->render(
            'doctrine/list_users.html.twig',
            [
                'users' => $team->getUsers()
            ]
        );
    }
}







