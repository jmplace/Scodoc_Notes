<?php
/***************************************/
/* Service de création de token JWT   /*
/* https://github.com/firebase/php-jwt */
/***************************************/

	$path = realpath($_SERVER['DOCUMENT_ROOT'] . '/..');

	include_once "$path/includes/default_config.php";
	include_once "$path/includes/user.class.php";
	$user = new User();

	if(
		$user->getId() != '5770' &&
		$user->getId() != '8416'
	){ 
		die("Ce service n'est autorisé que pour jean-marie.place@univ-lille.fr, vous pouvez le contacter.");
	}

	use \Firebase\JWT\JWT;

	include $path . '/lib/JWT/JWT.php';

	$exp = time() + 15 * 3600 * 24 ; // today + 15 days
	//$exp = strtotime('2025-03-01');
    	$root_url = (isset($_SERVER["https"]) ? "https://" : "http://" ). $_SERVER["HTTP_HOST"];
	$payload = [
		'id' => '42415617' , // nip, ou idCAS, si la personne n'a pas de nip
		'idCAS' => '',
		'name' => 'JARRY',
		'statut' => 'etudiant', // 'etudiant' | 'personnel' | 'administrateur' | 'superadministrateur' | INCONNU
		'exp' => $exp // (optionnel) timestamp d'expiration du token
	];
	echo $root_url."?token=".JWT::encode($payload, $Config->JWT_key);
?>
