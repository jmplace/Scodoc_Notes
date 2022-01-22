#Introduction
Cette application web est l adaptation locale du projet de Sébastien Lehmann (https://github.com/SebL68/Scodoc_Notes)

#Adaptations

Le projet original fonctionne en plusieurs étapes:
1. demande d'authentification (retourne un identifiant_CAS)
2. si il s'git d'un étudiant: 
3. -> recherche du code NIP de l'étudiant
4. si les accès enseignant ou personnel ont été activé
5. -> vérification du statut de l utilisateur 

il en est résulté les adapatations suivantes:
## Fichier(s) de configuration
###config/cas_config.php
RAS

	diff --git a/config/cas_config.php b/config/cas_config.php
	index f9f8341..8a91ebe 100644
	--- a/config/cas_config.php
	+++ b/config/cas_config.php
	@@ -3,9 +3,9 @@
		// Basic Config of the phpCAS client //
		///////////////////////////////////////
		// Full Hostname of your CAS Server
	-	$cas_host = 'cas.uha.fr';
	+	$cas_host = 'cas.univ-lille.fr';
		// Context of the CAS Server
	-	$cas_context = '/cas/';
	+	$cas_context = '';
		// Port of your CAS server. Normally for a https server it's 443
		$cas_port = 443;
		// Path to the ca chain that issued the cas server certificate
	
###config/config.php

	diff --git a/config/config.php b/config/config.php
	index 5c88a44..3b26625 100644
	--- a/config/config.php
	+++ b/config/config.php


* mise à jour de la liste des départements


	  @@ -8,10 +8,11 @@
	  public static $departements = [
	  'GEA',
	  'GEII',
		-				'GLT',
						'GMP',
		-				'MMI',
		-				'SGM'
		+				'BIO',
		+				'MP',
		+				'CHI',
		+				'INFO'
					];
	  /**********************************/
	  /* Activation des modules du site */

		activation acces enseignant
		Activation des tickets JWT
		
* activation des accès enseignants

	-		public static $acces_enseignants = false;
	+		public static $acces_enseignants = true;

* données retournées par le cas. réglage à 'mail' pour permettre la transposition vers le NIP (même si le CAS retourne l'uid - ldap de l'utilisateur)

	 
	 /*********************************/
	 /* Données retournées par le CAS */
	 /*********************************/
	-		public static $CAS_return_type = 'nip';	// Valeurs possibles : 
	+		public static $CAS_return_type = 'mail';	// Valeurs possibles :
														//  - 'nip' : numéro d'étudiant
														//  - 'idCAS' : un identificant autre (mail, identifiant LDAP ou autres)
	 
* Réglage des accès au serveur scodoc

	@@ -56,27 +57,47 @@
	 /********************************/
		/*	Il faut créer compte avec un accès "secrétariat" qui a accès à tous les départements */
	 
	-		public static $scodoc_url = 'https://iutmscodoc9.uha.fr/ScoDoc';	// Attention, il doit y avoir /Scodoc à la fin	
	-		public static $scodoc_login = 'LOGIN_SCODOC';
	-		public static $scodoc_psw = 'MDP_SCODOC';
	+		public static $scodoc_url = 'https://iut-scodoc.univ-lille.fr/ScoDoc';	// Attention, il doit y avoir /Scodoc à la fin
	+		public static $scodoc_login = '*obfuscated*';
	+		public static $scodoc_psw = '*obuscated*';
			
* Réglages DNS (pas forcément utile)
	-/*******************************************/
	-/* Déclaration du domaine DNS de l'UFR pour
	-	les mails utilisateurs dans la zone admin
	-/*******************************************/
	-		public static $DNS = 'uha.fr';
	+	/*******************************************/
	+	/* Déclaration du domaine DNS de l'UFR pour
	+		les mails utilisateurs dans la zone admin
	+	/*******************************************/
	+		public static $DNS = 'univ-lille.fr';

* activation du système de jeton
	+		/********************************/
	+		/* Clé pour les jetons JWT      */
	+		/********************************/
	+		public static $JWT_key = '*obfuscated*'; // Laisser vide si on n'utilise pas les jetons JWT
	 
* Pour l instant pas de LDAP (cf TODO)
  
### html/services/createJWT
insertion en dur du contrôle de l'utilisateur pour la création du jeton. cf TODO liste des administrateurs

	diff --git a/html/services/createJWT.php b/html/services/createJWT.php
	index 6bcc8d3..6091751 100644
	--- a/html/services/createJWT.php
	+++ b/html/services/createJWT.php
	@@ -11,7 +11,7 @@
		$user = new User();
	 
		if(
	-		$user->getSessionName() != 'sebastien.lehmann@uha.fr' &&
	+		$user->getSessionName() != '5770' &&
			$user->getSessionName() != 'denis.graef@uha.fr'
		){ 
			die("Ce service n'est autorisé que pour Sébastien Lehmann, vous pouvez le contacter.");

### html/services/data.php
adaptation du contrôle administrateur quand le site est en cours de maintenance. cf. TODO administrateurs

	@@ -36,4 +36,4 @@
		
		eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzZXNzaW9uIjoiQ29tcHRlX0RlbW8udGVzdEB1aGEuZnIiLCJzdGF0dXQiOiJldHVkaWFudCJ9.kHuiNx8X2mWUjv1LAHVOdcLGCu2yQS_i6fxqZZICuEA
	 */
	-?>
	\ No newline at end of file
	+?>
	diff --git a/html/services/data.php b/html/services/data.php
	index f700e35..b769881 100644
	--- a/html/services/data.php
	+++ b/html/services/data.php
	@@ -29,7 +29,7 @@
	 /*******************************/
	 /* Mise en maintenance du site */
	 /*******************************/
	-	//if($user->getSessionName() != 'sebastien.lehmann@uha.fr') returnError('Site en cours de maintenance ...');
	+	if($user->getSessionName() != "5770") returnError('Site en cours de maintenance ...');
	 
	 /* Utilisateur qui n'est pas dans la composante : n'est pas autorisé. */
		if($user->getStatut() == INCONNU){ returnError('Ce site est réservé aux étudiants et personnels de l\'IUT.'); }

### include/annuaire.clas.php
mécanisme de transposition uid => nip
Les modifications à opérer (la plupart historiques visant à conserver l infra existante):

* les CAS retourne un uid numérique: le transformer en string

* le format du fichier de transcription (initialement "NIP:mail") devient "uid:NIP". il faut donc inverser les rôle des premiers et second champs (et appliquer rtrim sur le bon champs)

* la localistaion du fichier devient `/srv/nip.list` au lieu de `data/annuaires/liste_etu.txt`'


	diff --git a/includes/annuaire.class.php b/includes/annuaire.class.php
	index 31bfd88..784b5d3 100644
	--- a/includes/annuaire.class.php
	+++ b/includes/annuaire.class.php

Localisation de l annuaire des étudiants

	@@ -17,7 +17,7 @@
	 $path = realpath($_SERVER['DOCUMENT_ROOT'] . '/..');
	 include_once "$path/includes/default_config.php";
	 
	-Annuaire::$STUDENTS_PATH = "$path/data/annuaires/liste_etu.txt";
	+Annuaire::$STUDENTS_PATH = "/srv/nip.list"; // "$path/data/annuaires/liste_etu.txt";
	 Annuaire::$USERS_PATH = "$path/data/annuaires/utilisateurs.json";
	 Annuaire::$STAF_PATH = [
		$path.'/data/annuaires/liste_ens.txt',

Inversion de l'ordre des champs

	@@ -51,8 +51,8 @@ class Annuaire{
				$handle = fopen(self::$STUDENTS_PATH, 'r');
				while(($line = fgets($handle, 1000)) !== FALSE){
					$data = explode(':', $line);
	-				if(rtrim($data[1]) == $mail)
	-					return Config::nipModifier($data[0]);
	+				if($data[0] == $mail)
	+					return Config::nipModifier(rtrim($data[1]));
				}
			} else {
				return Config::nipModifier($mail);

Affichage de l'uid en cas d'utilisateur pas dans l annuaire

	@@ -61,7 +61,7 @@ class Annuaire{
			exit(
				json_encode(
					array(
	-					'erreur' => "Votre compte n'est pas encore dans l'annuaire. Si le problème persiste, contactez votre responsable."
	+					'erreur' => "Votre compte (".$mail.") n'est pas encore dans l'annuaire. Si le problème persiste, contactez votre responsable."
					)
				)
			);

Inversion de l'ordre des champs

	@@ -87,8 +87,8 @@ class Annuaire{
			$handle = fopen(self::$STUDENTS_PATH, 'r');
			while(($line = fgets($handle, 1000)) !== FALSE){
				$data = explode(':', $line);
	-			if(substr($data[0], 1) == substr($num, 1))
	-				return rtrim($data[1]);
	+			if(substr(rtrim($data[1]), 1) == substr($num, 1))
	+				return rtrim($data[0]);
			}
		}
	 
Identification superadmin

	@@ -143,6 +143,12 @@ class Annuaire{
	 
			On peut donc forcer un statut en plaçant la personne comme admin ou vacataire, sinon par défaut c'est étudiant et enfin personnel.
			*/
	+			/* Test Superadministrateur */
	+			if ($user == '5770') {
	+				$_SESSION['statut'] = SUPERADMINISTRATEUR;
	+				return $_SESSION['statut'];
	+			}
	+
				/* Test administrateur */
				foreach(json_decode(file_get_contents(self::$USERS_PATH)) as $departement => $dep){
					if(preg_grep($pattern, $dep->administrateurs)){
	@@ -196,4 +202,4 @@ class Annuaire{
				returnError("Fichier inexistant : <b>$file</b><br>Veuillez mettre les listes des utilisateurs à jour.");
		}
	 }


### includes/serverIO.php

Affichae du mail/département d'un étudiant non trouvé dans l'annuaire

diff --git a/includes/serverIO.php b/includes/serverIO.php
index ae16940..7608a9d 100644
--- a/includes/serverIO.php
+++ b/includes/serverIO.php

@@ -149,7 +148,7 @@ function getStudentSemesters($data){
 		return $output;
 	}else{
 		returnError(
-			"Problème de compte, vous n'êtes pas dans Scodoc ou votre numéro d'étudiant est erroné, si le problème persiste, contactez votre responsable en lui précisant : il y a peut être un .0 à la fin du numéro d'étudiant dans Scodoc."
+			"Problème de compte, vous n'êtes pas dans Scodoc ou votre numéro d'étudiant (".$nip."/".$dep.") est erroné, si le problème persiste, contactez votre responsable en lui précisant : il y a peut être un .0 à la fin du numéro d'étudiant dans Scodoc."
 		);
 	}
 }
	 
Conversion en String de l uid numérique retourné par le CAS

diff --git a/includes/user.class.php b/includes/user.class.php
index 2a586b9..d0d0812 100644
--- a/includes/user.class.php
+++ b/includes/user.class.php
@@ -47,7 +47,7 @@
 
 			} else {
 				/* Procédure d'authentification */
-				$this->session = Auth::defaultAuth();
+				$this->session = (string) Auth::defaultAuth();
 				$this->defineStatut();
 			}
 		}

# aide au diagnostic 

TODO

- comment tester le cas (cf ../services/diagnostic.php) ?
- comment tester un accès étudiant ?
- comment tester un accès enseignant ?


#TODO (certains points peuvent être fait aussi sur le projet initial)

* Mettre en cache les liste d'étudiants

* adaptation administrateurs

