<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class ProduitModel {

    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }
    // http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/query-builder.html#join-clauses
    public function getAllProduits() {
//        $sql = "SELECT p.id, t.libelle, p.nom, p.prix, p.photo
//            FROM produits as p,typeProduits as t
//            WHERE p.typeProduit_id=t.id ORDER BY p.nom;";
//        $req = $this->db->query($sql);
//        return $req->fetchAll();
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 't.libelle', 'p.nom', 'p.prix', 'p.photo')
            ->from('produits', 'p')
            ->innerJoin('p', 'typeProduits', 't', 'p.typeProduit_id=t.id')
            ->addOrderBy('p.nom', 'ASC');
        return $queryBuilder->execute()->fetchAll();

    }

    public function insertProduit($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('produits')
            ->values([
                'nom' => '?',
                'typeProduit_id' => '?',
                'prix' => '?',
                'photo' => '?'
            ])
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['typeProduit_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['photo'])
        ;
        return $queryBuilder->execute();
    }
    public function insertCommande($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('commandes')
            ->values([

                'user_id' => '?',
                'prix' => '?',
                'date_achat' => '?',
                'id' => '?',
                'etat_id' => '1'
            ])
            ->setParameter(0, $donnees['user_id'])
            ->setParameter(1, $donnees['prixTotal'])
            ->setParameter(2, date('Y-m-d H:i:s'))
            ->setParameter(3, $donnees['id'])
        ;
        return $queryBuilder->execute();
    }
    public function insertPanier($donnees,$user_id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.quantite,p.id')
            ->from('paniers','p')
            ->innerJoin('p', 'produits', 'pr', 'p.produit_id=pr.id')
            ->where('p.user_id= ?')
            ->andWhere('pr.id=?')
            ->andWhere('p.commande_id is null')
            ->setParameter(0, $user_id)
            ->setParameter(1, $donnees['id']);
        $donnees2=$queryBuilder->execute()->fetch();
        if(empty($donnees2['id'])) {
            $queryBuilder2 = new QueryBuilder($this->db);

            $queryBuilder2->insert('paniers')
                ->values([
                    'quantite' => '?',
                    'prix' => '?',
                    'dateAjoutPanier' => '?',
                    'user_id' => '?',
                    'produit_id' => '?',
                    'commande_id' => '?'
                ])
                ->setParameter(0, 1)
                ->setParameter(1, $donnees['prix'])
                ->setParameter(2, date('Y-m-d H:i:s'))
                ->setParameter(3, $user_id)
                ->setParameter(4, $donnees['id'])
                ->setParameter(5, null);
            return $queryBuilder2->execute();
        }else{
            $queryBuilder3 = new QueryBuilder($this->db);

            $queryBuilder3->update('paniers')

                ->set('quantite', '?')
                ->where('id= ?')
                ->setParameter(0, $donnees2['quantite']+1)
                ->setParameter(1, $donnees2['id']);

            return $queryBuilder3->execute();
        }

    }

    function getProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('id', 'typeProduit_id', 'nom', 'prix', 'photo')
            ->from('produits')
            ->where('id= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

    public function updateProduit($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('produits')
            ->set('nom', '?')
            ->set('typeProduit_id','?')
            ->set('prix','?')
            ->set('photo','?')
            ->where('id= ?')
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['typeProduit_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['photo'])
            ->setParameter(4, $donnees['id']);
        return $queryBuilder->execute();
    }
    public function updateCommande($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('commandes')
            ->set('etat_id', '2')
            ->where('id= ?')
            ->setParameter(0, $id);
        return $queryBuilder->execute();
    }
    public function updatePanier($id,$commande_id) {
        $queryBuilder3 = new QueryBuilder($this->db);

        $queryBuilder3->update('paniers')

            ->set('commande_id', '?')
            ->where('id= ?')
            ->setParameter(0,$commande_id )
            ->setParameter(1, $id);

        return $queryBuilder3->execute();

    }
    public function deleteProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('produits')
            ->where('id = :id')
            ->setParameter('id',(int)$id)
        ;
        return $queryBuilder->execute();
    }
    public function deletePanier($id,$user_id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('paniers')
            ->where('user_id = ?')
            ->andWhere('id=?')
            ->setParameter(0,(int)$user_id)
            ->setParameter(1,(int)$id)
        ;
        return $queryBuilder->execute();
    }

    public function getPanier($id,$user_id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('pa.id','quantite', 'pa.prix', 'dateAjoutPanier', 'p.nom', 'p.photo' ,'p.dispo ')
            ->from('produits','p')
            ->innerJoin('p', 'paniers', 'pa', 'pa.produit_id=p.id')
            ->where('pa.user_id= ?')
            ->andWhere('pa.id=?')
            ->setParameter(0, $user_id)
            ->setParameter(1, $id);
        return $queryBuilder->execute()->fetch();
    }
    public function getAllPanier($id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('pa.id','quantite', 'pa.prix', 'dateAjoutPanier', 'p.nom', 'p.photo' ,'p.dispo ')
            ->from('produits','p')
            ->innerJoin('p', 'paniers', 'pa', 'pa.produit_id=p.id')
            ->where('pa.user_id= ?')
            ->setParameter(0, $id);
        return $queryBuilder->execute()->fetchAll();
    }
    public function getAllCommande(){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('nom','c.id','c.user_id','prix','date_achat','libelle','etat_id')
            ->from('commandes','c')
            ->innerJoin('c', 'users', 'u', 'u.id=c.user_id')
            ->innerJoin('c', 'etats', 'e', 'c.etat_id=e.id');
        return $queryBuilder->execute()->fetchAll();
    }
    public function getCommandeId($id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('commandes')
            ->where('user_id=?')
            ->setParameter(0, $id);
        ;
        return $queryBuilder->execute()->fetchAll();
    }
    public function getCommande($id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id','p.typeProduit_id','nom','p.prix','photo','pa.quantite','pa.dateAjoutPanier')
            ->from('produits','p')
            ->innerJoin('p','paniers','pa','pa.produit_id=p.id')
            //->innerJoin('p','commande','c','c.id=pa.id_commande')
            ->where('pa.commande_id=?')
            ->setParameter(0, $id);
        ;
        return $queryBuilder->execute()->fetchAll();
    }
    public function getNonCommandePanier($user_id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('pa.id','quantite', 'pa.prix', 'dateAjoutPanier', 'p.nom', 'p.photo' ,'p.dispo ')
            ->from('produits','p')
            ->innerJoin('p', 'paniers', 'pa', 'pa.produit_id=p.id')
            ->where('pa.user_id= ?')
            ->andWhere('commande_id is NULL')
            ->setParameter(0, $user_id);

        return $queryBuilder->execute()->fetchAll();
    }




}