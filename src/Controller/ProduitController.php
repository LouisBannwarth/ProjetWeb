<?php
namespace App\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Symfony\Component\HttpFoundation\Request;   // pour utiliser request

use App\Model\ProduitModel;
use App\Model\TypeProduitModel;

use Symfony\Component\Validator\Constraints as Assert;   // pour utiliser la validation
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security;

class ProduitController implements ControllerProviderInterface
{
    private $produitModel;
    private $typeProduitModel;


    public function initModel(Application $app){  //  ne fonctionne pas dans le const
        $this->produitModel = new ProduitModel($app);
        $this->typeProduitModel = new TypeProduitModel($app);
    }

    public function index(Application $app) {
        return $this->show($app);
    }

    public function show(Application $app) {
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllProduits();
        return $app["twig"]->render('backOff/Produit/show.html.twig',['data'=>$produits]);
    }
    public function showCommande(Application $app) {
        $this->produitModel = new ProduitModel($app);
        $id=$app["session"]->get('user_id');
        $produits = $this->produitModel->getCommandeId($id);
        return $app["twig"]->render('frontOff/showCommande.html.twig',['commande'=>$produits]);
    }
    public function showCommandeAdm(Application $app) {
        $this->produitModel = new ProduitModel($app);

        $produits = $this->produitModel->getAllCommande();
        return $app["twig"]->render('backOff/showCommande.html.twig',['commande'=>$produits]);
    }
    public function showPanier(Application $app) {
        $this->produitModel = new ProduitModel($app);
        $id=$app["session"]->get('user_id');
        $panier = $this->produitModel->getNonCommandePanier($id);
        return $app["twig"]->render('frontOff/Produit/showPanier.html.twig',['panier'=>$panier]);
    }
    public function showFront(Application $app) {
        $id=$app["session"]->get('user_id');
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllProduits();
        $panier = $this->produitModel ->getNonCommandePanier($id);
        return $app["twig"]->render('frontOff/Produit/show.html.twig',['data'=>$produits ,'panier'=>$panier]);
    }
    public function add(Application $app) {
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        return $app["twig"]->render('backOff/Produit/add.html.twig',['typeProduits'=>$typeProduits]);  //,'path'=>BASE_URL
    }

    public function validFormAdd(Application $app, Request $req) {
       // var_dump($app['request']->attributes);
        if (isset($_POST['nom']) && isset($_POST['typeProduit_id']) and isset($_POST['nom']) and isset($_POST['photo'])) {
            $donnees = [
                'nom' => htmlspecialchars($_POST['nom']),                    // echapper les entrées
                'typeProduit_id' => htmlspecialchars($req->get('typeProduit_id')),  //$app['request']-> ne focntionne plus
                'prix' => htmlspecialchars($req->get('prix')),
                'photo' => $app->escape($req->get('photo'))  //$req->query->get('photo')-> ne focntionne plus
            ];
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom']='nom composé de 2 lettres minimum';
            if(! is_numeric($donnees['typeProduit_id']))$erreurs['typeProduit_id']='veuillez saisir une valeur';
            if(! is_numeric($donnees['prix']))$erreurs['prix']='saisir une valeur numérique';
            if (! preg_match("/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/",$donnees['photo'])) $erreurs['photo']='nom de fichier incorrect (extension jpeg , jpg ou png)';

            if(! empty($erreurs))
            {
                $this->typeProduitModel = new TypeProduitModel($app);
                $typeProduits = $this->typeProduitModel->getAllTypeProduits();
                return $app["twig"]->render('backOff/Produit/add.html.twig',['donnees'=>$donnees,'erreurs'=>$erreurs,'typeProduits'=>$typeProduits]);
            }
            else
            {
                $this->ProduitModel = new ProduitModel($app);
                $this->ProduitModel->insertProduit($donnees);
                return $app->redirect($app["url_generator"]->generate("produit.index"));
            }

        }
        else
            return $app->abort(404, 'error Pb data form Add');
    }




    public function delete(Application $app, $id) {
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        $this->produitModel = new ProduitModel($app);
        $donnees = $this->produitModel->getProduit($id);
        return $app["twig"]->render('backOff/Produit/delete.html.twig',['typeProduits'=>$typeProduits,'donnees'=>$donnees]);
    }

    public function validFormDelete(Application $app, Request $req) {
        $id=$app->escape($req->get('id'));
        if (is_numeric($id)) {
            $this->produitModel = new ProduitModel($app);
            $this->produitModel->deleteProduit($id);
            return $app->redirect($app["url_generator"]->generate("produit.index"));
        }
        else
            return $app->abort(404, 'error Pb id form Delete');
    }











    public function deletePanier(Application $app, $id) {
        $this->produitModel = new ProduitModel($app);
        $user_id=$app["session"]->get('user_id');
        $donnees = $this->produitModel->getPanier($id,$user_id);
        return $app["twig"]->render('frontOff/Produit/deletePanier.html.twig',['donnees'=>$donnees]);
    }

    public function validFormDeletePanier(Application $app, Request $req) {
        $id=$app->escape($req->get('id'));
        if (is_numeric($id)) {
            $user_id=$app["session"]->get('user_id');
            $this->produitModel = new ProduitModel($app);
            $this->produitModel->deletePanier($id,$user_id);
            return $app->redirect($app["url_generator"]->generate("produit.showc"));
        }
        else
            return $app->abort(404, 'error Pb id form Delete');
    }

    public function commandePanier(Application $app) {
        $this->produitModel = new ProduitModel($app);
        $user_id=$app["session"]->get('user_id');

        $donnees['prixTotal']=0;
        $panier=$this->produitModel->getNonCommandePanier($user_id);
        foreach ($panier as $prix){
            $donnees['prixTotal']=$donnees['prixTotal']+(($prix['prix'])*($prix['quantite']));
        }
        $donnees['user_id']=$user_id;
        $commande=$this->produitModel->getAllCommande();
        $cpt=1;
        foreach ($commande as $cmd){
            if (isset($cmd['id'])) {
                $cpt++;
            }
        }
        $donnees['id']=$cpt;
        $this->produitModel->insertCommande($donnees);
        $donnees2 = $this->produitModel->getNonCommandePanier($user_id);
        foreach ($donnees2 as $produit){
            $commande=$this->produitModel->updatePanier($produit['id'],$cpt);
        }
        return $app->redirect($app["url_generator"]->generate("produit.showc"));
    }

















    public function edit(Application $app, $id) {
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        $this->produitModel = new ProduitModel($app);
        $donnees = $this->produitModel->getProduit($id);
        return $app["twig"]->render('backOff/Produit/edit.html.twig',['typeProduits'=>$typeProduits,'donnees'=>$donnees]);
    }
    public function editCommande(Application $app, $id) {
        $this->produitModel = new ProduitModel($app);
        $user_id=$app["session"]->get('user_id');

        $this->produitModel->updateCommande($id);
        $produits = $this->produitModel->getAllCommande();
        return $app["twig"]->render('backOff/Produit/showCommande.html.twig',['commande'=>$produits]);
    }

    public function validFormEdit(Application $app, Request $req) {
        // var_dump($app['request']->attributes);
        if (isset($_POST['nom']) && isset($_POST['typeProduit_id']) and isset($_POST['nom']) and isset($_POST['photo']) and isset($_POST['id'])) {
            $donnees = [
                'nom' => htmlspecialchars($_POST['nom']),                    // echapper les entrées
                'typeProduit_id' => htmlspecialchars($req->get('typeProduit_id')),  //$app['request']-> ne focntionne plus
                'prix' => htmlspecialchars($req->get('prix')),
                'photo' => $app->escape($req->get('photo')),  //$req->query->get('photo')-> ne focntionne plus
                'id' => $app->escape($req->get('id'))//$req->query->get('photo')
            ];
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom']='nom composé de 2 lettres minimum';
            if(! is_numeric($donnees['typeProduit_id']))$erreurs['typeProduit_id']='veuillez saisir une valeur';
            if(! is_numeric($donnees['prix']))$erreurs['prix']='saisir une valeur numérique';
            if (! preg_match("/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/",$donnees['photo'])) $erreurs['photo']='nom de fichier incorrect (extension jpeg , jpg ou png)';
            if(! is_numeric($donnees['id']))$erreurs['id']='saisir une valeur numérique';
            $contraintes = new Assert\Collection(
                [
                    'id' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'typeProduit_id' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'nom' => [
                        new Assert\NotBlank(['message'=>'saisir une valeur']),
                        new Assert\Length(['min'=>2, 'minMessage'=>"Le nom doit faire au moins {{ limit }} caractères."])
                    ],
                    //http://symfony.com/doc/master/reference/constraints/Regex.html
                    'photo' => [
                        new Assert\Length(array('min' => 5)),
                        new Assert\Regex([ 'pattern' => '/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/',
                        'match'   => true,
                        'message' => 'nom de fichier incorrect (extension jpeg , jpg ou png)' ]),
                    ],
                    'prix' => new Assert\Type(array(
                        'type'    => 'numeric',
                        'message' => 'La valeur {{ value }} n\'est pas valide, le type est {{ type }}.',
                    ))
                ]);
            $errors = $app['validator']->validate($donnees,$contraintes);  // ce n'est pas validateValue

        //    $violationList = $this->get('validator')->validateValue($req->request->all(), $contraintes);
//var_dump($violationList);

          //   die();
            if (count($errors) > 0) {
                // foreach ($errors as $error) {
                //     echo $error->getPropertyPath().' '.$error->getMessage()."\n";
                // }
                // //die();
                //var_dump($erreurs);

            // if(! empty($erreurs))
            // {
                $this->typeProduitModel = new TypeProduitModel($app);
                $typeProduits = $this->typeProduitModel->getAllTypeProduits();
                return $app["twig"]->render('backOff/Produit/edit.html.twig',['donnees'=>$donnees,'errors'=>$errors,'erreurs'=>$erreurs,'typeProduits'=>$typeProduits]);
            }
            else
            {
                $this->ProduitModel = new ProduitModel($app);
                $this->ProduitModel->updateProduit($donnees);
                return $app->redirect($app["url_generator"]->generate("produit.index"));
            }

        }
        else
            return $app->abort(404, 'error Pb id form edit');

    }



    public function editPanier(Application $app, $id) {
        $this->produitModel = new ProduitModel($app);
        $user_id=$app["session"]->get('user_id');
        $donnees = $this->produitModel->getProduit($id);
        $this->produitModel->insertPanier($donnees,$user_id);
        return $app->redirect($app["url_generator"]->generate("produit.showc"));
    }



    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\produitController::index')->bind('produit.index');
        $controllers->get('/show', 'App\Controller\produitController::show')->bind('produit.show');
        $controllers->get('/showc', 'App\Controller\produitController::showFront')->bind('produit.showc');
        $controllers->get('/showp', 'App\Controller\produitController::showPanier')->bind('produit.showp');
        $controllers->get('/showcom', 'App\Controller\produitController::showCommande')->bind('produit.showcom');
        $controllers->get('/showcomadm', 'App\Controller\produitController::showCommandeAdm')->bind('produit.showcomadm');

        $controllers->get('/add', 'App\Controller\produitController::add')->bind('produit.add');
        $controllers->post('/add', 'App\Controller\produitController::validFormAdd')->bind('produit.validFormAdd');

        $controllers->get('/commandePanier', 'App\Controller\produitController::commandePanier')->bind('produit.commandePanier');

        $controllers->get('/delete/{id}', 'App\Controller\produitController::delete')->bind('produit.delete')->assert('id', '\d+');
        $controllers->delete('/delete', 'App\Controller\produitController::validFormDelete')->bind('produit.validFormDelete');

        $controllers->get('/deletepanier/{id}', 'App\Controller\produitController::deletePanier')->bind('produit.deletePanier')->assert('id', '\d+');
        $controllers->delete('/deletepanier', 'App\Controller\produitController::validFormDeletePanier')->bind('produit.validFormDeletePanier');

        $controllers->get('/edit/{id}', 'App\Controller\produitController::edit')->bind('produit.edit')->assert('id', '\d+');
        $controllers->put('/edit', 'App\Controller\produitController::validFormEdit')->bind('produit.validFormEdit');

        $controllers->get('/editPanier/{id}', 'App\Controller\produitController::editPanier')->bind('produit.editPanier')->assert('id', '\d+');

        $controllers->get('/editCommande/{id}', 'App\Controller\produitController::editCommande')->bind('produit.editCommande')->assert('id', '\d+');

        return $controllers;
    }
}
