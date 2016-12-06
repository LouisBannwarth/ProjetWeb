<?php
namespace App\Controller;

use App\Model\UserModel;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Symfony\Component\HttpFoundation\Request;   // pour utiliser request

class UserController implements ControllerProviderInterface {

	private $userModel;

	public function index(Application $app) {
		return $this->connexionUser($app);
	}

	public function connexionUser(Application $app)
	{
		return $app["twig"]->render('v_session_connexion.html.twig');
	}

	public function validFormConnexionUser(Application $app, Request $req)
	{
		$app['session']->clear();
		$donnees['login']=$req->get('login');
		$donnees['password']=$req->get('password');

		$this->userModel = new UserModel($app);
		$data=$this->userModel->verif_login_mdp_Utilisateur($donnees['login'],$donnees['password']);

		if($data != NULL)
		{
			$app['session']->set('droit', $data['droit']);  //dans twig {{ app.session.get('droit') }}
			$app['session']->set('login', $data['login']);
			$app['session']->set('logged', 1);
			$app['session']->set('user_id', $data['id']);
			return $app->redirect($app["url_generator"]->generate("accueil"));
		}
		else
		{
			$app['session']->set('erreur','mot de passe ou login incorrect');
			return $app["twig"]->render('v_session_connexion.html.twig');
		}
	}
    public function showUser(Application $app) {
        $this->userModel = new UserModel($app);
        $id=$app["session"]->get('user_id');
        $user = $this->userModel->getUser($id);
        return $app["twig"]->render('frontOff/showUser.html.twig',['data'=>$user]);
    }
	public function deconnexionSession(Application $app)
	{
		$app['session']->clear();
		$app['session']->getFlashBag()->add('msg', 'vous êtes déconnecté');
		return $app->redirect($app["url_generator"]->generate("accueil"));
	}
	public function edit(Application $app) {

		$this->userModel = new userModel($app);
		$id=$app["session"]->get('user_id');
		$donnees = $this->userModel->getUser($id);
		return $app["twig"]->render('frontOff/editUser.html.twig',['donnees'=>$donnees]);
	}

	public function validFormEdit(Application $app, Request $req) {
		// var_dump($app['request']->attributes);
		if (isset($_POST['nom']) && isset($_POST['email']) and isset($_POST['login']) and isset($_POST['code_postal']) and isset($_POST['ville'])and isset($_POST['adresse'])) {
			$donnees = [
					'nom' => htmlspecialchars($_POST['nom']),                    // echapper les entrées
					'email' => htmlspecialchars($_POST['email']),  //$app['request']-> ne focntionne plus
					'login' => htmlspecialchars($_POST['login']),
					'code_postal' => htmlspecialchars($_POST['code_postal']),
					'ville' => htmlspecialchars($_POST['ville']),
					'adresse' => htmlspecialchars($_POST['adresse'])

			];
			if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom']='nom composé de 2 lettres minimum';
			if ((! preg_match("/^[A-Za-z0-9]{2,}/",$donnees['login']))) $erreurs['login']='login composé de 2 caractere minimum';
			if(! is_numeric($donnees['code_postal']))$erreurs['code_postal']='veuillez saisir une valeur';
			if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['ville']))) $erreurs['ville']='ville composé de 2 lettres minimum';
			if (! preg_match("/[A-Za-z0-9]{2,}@[A-Za-z]{1,}.[A-Za-z]{1,}/",$donnees['email'])) $erreurs['email']='email n\'est pas correct';
			if ((! preg_match("/^[A-Za-z0-9]{5,}/",$donnees['adresse']))) $erreurs['adresse']='adresse composé de 2 caractere minimum';



			$errors = $app['validator']->validate($donnees);  // ce n'est pas validateValue

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
				return $app["twig"]->render('frontOff/editUser.html.twig',['donnees'=>$donnees,'errors'=>$errors,'erreurs'=>$erreurs]);
			}
			else
			{
				$this->Usermodel = new UserModel($app);
				$id=$app["session"]->get('user_id');
				$this->Usermodel->updateUser($donnees,$id);
				return $app->redirect($app["url_generator"]->generate("produit.index"));
			}

		}
		else
			return $app->abort(404, 'error Pb id form edit');

	}

	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];
		$controllers->match('/', 'App\Controller\UserController::index')->bind('user.index');
		$controllers->get('/login', 'App\Controller\UserController::connexionUser')->bind('user.login');
		$controllers->post('/login', 'App\Controller\UserController::validFormConnexionUser')->bind('user.validFormlogin');
		$controllers->get('/logout', 'App\Controller\UserController::deconnexionSession')->bind('user.logout');

		$controllers->get('/edit', 'App\Controller\UserController::edit')->bind('user.edit');

        $controllers->get('/showuser', 'App\Controller\UserController::showUser')->bind('user.showuser');
		return $controllers;
	}

}