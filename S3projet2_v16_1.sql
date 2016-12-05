DROP TABLE  IF EXISTS paniers,commandes, produits, users, typeProduits, etats;

-- --------------------------------------------------------
-- Structure de la table typeproduits
--
CREATE TABLE IF NOT EXISTS typeProduits (
  id int(10) NOT NULL,
  libelle varchar(50) DEFAULT NULL,
  PRIMARY KEY (id)
)  DEFAULT CHARSET=utf8;
-- Contenu de la table typeproduits
INSERT INTO typeProduits (id, libelle) VALUES
  (1, 'plate'),
  (2, 'gazeuse'),
  (3, 'soda');

-- --------------------------------------------------------
-- Structure de la table etats

CREATE TABLE IF NOT EXISTS etats (
  id int(11) NOT NULL AUTO_INCREMENT,
  libelle varchar(20) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 ;
-- Contenu de la table etats
INSERT INTO etats (id, libelle) VALUES
  (1, 'A pr�parer'),
  (2, 'Exp�di�');

-- --------------------------------------------------------
-- Structure de la table produits

CREATE TABLE IF NOT EXISTS produits (
  id int(10) NOT NULL AUTO_INCREMENT,
  typeProduit_id int(10) DEFAULT NULL,
  nom varchar(50) DEFAULT NULL,
  prix float(6,2) DEFAULT NULL,
  photo varchar(50) DEFAULT NULL,
  dispo tinyint(4) NOT NULL,
  stock int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_produits_typeProduits FOREIGN KEY (typeProduit_id) REFERENCES typeProduits (id)
) DEFAULT CHARSET=utf8 ;

INSERT INTO produits (id,typeProduit_id,nom,prix,photo,dispo,stock) VALUES
  (1,2, 'badoit','100','badoit.jpeg',1,5),
  (2,3, 'coca','5.5','coca.jpeg',1,4),
  (3,1, 'contrex','8.5','contrex.jpeg',1,10),
  (4,1, 'cristaline','8','cristaline.jpeg',1,5),
  (5,1, 'evian','55','evian.jpeg',1,4),
  (6,3, 'fanta','5','fanta.jpeg',1,10),
  (7,1, 'mont blanc','55','mont_blanc.jpeg',1,4),
  (8,3, 'orangina','5','orangina.jpeg',1,10),
  (9,3, 'pepsi','5','pepsi.jpeg',1,10),
  (10,2, 'rozana','5','rozana.jpg',1,10),
  (11,2, 'san pellegrino','100','s_pellegrino.jpeg',1,5),
  (12,3, 'schweppes','5.5','schweppes.jpeg',1,4),
  (13,1, 'vitel','55','vitel.jpeg',1,4),
  (14,1, 'volvic','55','volvic.jpeg',1,4),
  (15,1, 'wattwiller','55','wattwiller.jpeg',1,4);


-- --------------------------------------------------------
-- Structure de la table user
-- valide permet de rendre actif le compte (exemple controle par email )

CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  login varchar(255) NOT NULL,
  nom varchar(255) NOT NULL,
  code_postal varchar(255),
  ville varchar(255),
  adresse varchar(255),
  valide tinyint NOT NULL,
  droit varchar(255) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

-- Contenu de la table users
INSERT INTO users (id,login,password,email,valide,droit,nom) VALUES
  (1, 'admin', 'admin', 'admin@gmail.com',1,'DROITadmin','a'),
  (2, 'vendeur', 'vendeur', 'vendeur@gmail.com',1,'DROITadmin','a'),
  (3, 'client', 'client', 'client@gmail.com',1,'DROITclient','a'),
  (4, 'client2', 'client2', 'client2@gmail.com',1,'DROITclient','a'),
  (5, 'client3', 'client3', 'client3@gmail.com',1,'DROITclient','a');



-- --------------------------------------------------------
-- Structure de la table commandes
CREATE TABLE IF NOT EXISTS commandes (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  prix float(6,2) NOT NULL,
  date_achat  timestamp default CURRENT_TIMESTAMP,
  etat_id int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_commandes_users FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT fk_commandes_etats FOREIGN KEY (etat_id) REFERENCES etats (id)
) DEFAULT CHARSET=utf8 ;



-- --------------------------------------------------------
-- Structure de la table paniers
CREATE TABLE IF NOT EXISTS paniers (
  id int(11) NOT NULL AUTO_INCREMENT,
  quantite int(11) NOT NULL,
  prix float(6,2) NOT NULL,
  dateAjoutPanier timestamp default CURRENT_TIMESTAMP,
  user_id int(11) NOT NULL,
  produit_id int(11) NOT NULL,
  commande_id int(11) DEFAULT null,
  PRIMARY KEY (id),
  CONSTRAINT fk_paniers_users FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT fk_paniers_produits FOREIGN KEY (produit_id) REFERENCES produits (id),
  CONSTRAINT fk_paniers_commandes FOREIGN KEY (commande_id) REFERENCES commandes (id)
) DEFAULT CHARSET=utf8 ;

