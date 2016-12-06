<?php
namespace App\Model;

use Silex\Application;
use Doctrine\DBAL\Query\QueryBuilder;;

class UserModel {

	private $db;

	public function __construct(Application $app) {
		$this->db = $app['db'];
	}

	public function verif_login_mdp_Utilisateur($login,$mdp){
		$sql = "SELECT id,login,password,droit FROM users WHERE login = ? AND password = ?";
		$res=$this->db->executeQuery($sql,[$login,$mdp]);   //md5($mdp);
		if($res->rowCount()==1)
			return $res->fetch();
		else
			return false;
	}
	public function updateUser($donnees,$id) {
		$queryBuilder = new QueryBuilder($this->db);
		$queryBuilder
				->update('users')
				->set('nom', '?')
				->set('email','?')
				->set('login','?')
				->set('code_postal','?')
				->set('ville','?')
				->set('adresse','?')
				->where('id= ?')
				->setParameter(0, $donnees['nom'])
				->setParameter(1, $donnees['email'])
				->setParameter(2, $donnees['login'])
				->setParameter(3, $donnees['code_postal'])
				->setParameter(3, $donnees['ville'])
				->setParameter(3, $donnees['adresse'])
				->setParameter(6, $id);
		return $queryBuilder->execute();
	}
	public function getUser($user_id) {
		$queryBuilder = new QueryBuilder($this->db);
		$queryBuilder
			->select('*')
			->from('users')
			->where('id = :idUser')
			->setParameter('idUser', $user_id);
		return $queryBuilder->execute()->fetch();

	}
}