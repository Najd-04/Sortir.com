	npm -v 10.8.1 
	node -v 22.4.1
	php -v 8.2.12
	symfony -v 5.10.4
	
	Consigne : 
 	1) Installer WampServer64
	2) 'symfony composer i' pour installer les dépendances
	3) Créer une table sortir dans la base de donnée PhpMyAdmin avec le nom d'utilisateur "root" et sans mot de passe
 	4) 'symfony console do:sc:up -f' pour la création des tables de la BDD
 	5) Injecter les fichier .sql du dossier SQL dans l'ordre indiqué
  	6) Créer une fichier .env.local à la racine du projet
   	7) Y ajouter : 'GOOGLE_MAPS_API_KEY=AIzaSyD3J_53kKhTVQ6rkCJuyMV5Zvxa2dFYfjI' pour l'API GoogleMap
    8) Déplacer les photos du dossier SQL dans le dossier assets/uploads/photos pour voir les photos de profil 
