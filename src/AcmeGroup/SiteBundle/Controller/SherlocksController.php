<?php

namespace AcmeGroup\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use labo\Bundle\TestmanuBundle\services\aetools\aeReponse;

class SherlocksController extends Controller {
	private $parm = array();

	//////////////////////////
	// PAGES
	//////////////////////////

	// Testq CB
	// numéro : 4974934125497800 (paiement accepté) / 4972187615205 (paiement refusé)
	// date : attention, mettre une date future
	// Crypto : 600 / 640 / 650 / 653 / 655
	// Verif carte : 00000000

	public function livraisonChoixAction() {
		$data['user'] = $this->get('security.context')->getToken()->getUser();
		// $data['magasin'] = 
		return $this->render("AcmeGroupSiteBundle:Sherlocks:livraisonChoix.html.twig", $data);
	}

	public function paiementChoixAction($livraison = "defaut", $test = false) {
		$this->container->get('acmeGroup.aelog')->createNewLogAuto();
		$em = $this->getDoctrine()->getManager(); // EntityManager
		$version = $this->get('session')->get('version');
		$data = array();
		$infos = array(1 => 'code', 2 => 'error', 3 => 'message');

		$panier = $this->get('acmeGroup.panier');
		$user = $this->get('security.context')->getToken()->getUser();
		if($livraison !== 'defaut') {
			$user->setLivraison($livraison);
			$em->flush();
		}
		$data["infoPanier"] = $panier->getInfosPanier($user);
		$data["test"] = $test;
		$prix = number_format($data["infoPanier"]["prixtotal"], 2, "", "");
		$idCommercant = "045169008500019";

		switch($test) {
			case 1:
				// $prix = "100";
				$idCommercant = "014295303911112";
				break;
			case 2: // test pour 1 euro
				$prix = "100";
				$idCommercant = "014295303911112";
				break;
			default:
				break;
		}
		$data["infoPanier"]["prixtotal"] = number_format($data["infoPanier"]["prixtotal"], 2, ",", "");
		$this->initParm();
		$date = new \Datetime();
		// $this->addParm("transaction_id", $version["id"]."-".$user->getId()."-".$date->getTimestamp());
		$this->addParm("customer_email", $user->getEmail());
		$this->addParm("customer_id", $version["id"]);
		// $this->addParm("customer_email", $user->getEmail());
		$this->addParm("merchant_id", $idCommercant);
		$this->addParm("merchant_country", "fr");
		$this->addParm("amount", $prix);
		$this->addParm("currency_code", "978");
		$this->addParm("pathfile", "/sherlocks/param/pathfile");

		$data["stringParm"] = $this->getStringParm();
		$data["path_bin"] = "/sherlocks/bin/glibc-2.5-42/request";
		// customer_email=emmanuel@aequation-webdesign.fr customer_id=1 merchant_id=045169008500019 merchant_country=fr amount=15900 currency_code=978 pathfile=/sherlocks/param/pathfile
		$result = exec($data["path_bin"]." ".$data["stringParm"]);

		$tableau = explode ("!", "$result");
		foreach($infos as $key => $info) {
			if(isset($tableau[$key])) $data[$info] = $tableau[$key]; else $data[$info] = "";
		}
		if(($data['code'] == "" && $data['error'] == "") || ($data['code'] != 0)) {
			$data["result"] = false;
		} else $data["result"] = true;

		return $this->render("AcmeGroupSiteBundle:Sherlocks:paiementChoix.html.twig", $data);
	}

	public function veilleAction() {
		$this->container->get('acmeGroup.aelog')->createNewLogAuto();
		$data = array();
		return $this->render("AcmeGroupSiteBundle:Sherlocks:veille.html.twig", $data);
	}

	public function responseAction($auto = null) {
		$data = array();
		$infos = array(1 => 'code', 2 => 'error', 3 => 'merchant_id', 4 => 'merchant_country', 5 => 'amount',
			6 => 'transaction_id', 7 => 'payment_means', 8 => 'transmission_date', 9 => 'payment_time', 10 => 'payment_date',
			11 => 'response_code', 12 => 'payment_certificate', 13 => 'authorisation_id', 14 => 'currency_code', 15 => 'card_number',
			16 => 'cvv_flag', 17 => 'cvv_response_code', 18 => 'bank_response_code', 19 => 'complementary_code', 20 => 'complementary_info',
			21 => 'return_context', 22 => 'caddie', 23 => 'receipt_complement', 24 => 'merchant_language', 25 => 'language',
			26 => 'customer_id', 27 => 'order_id', 28 => 'customer_email', 29 => 'customer_ip_address', 30 => 'capture_day',
			31 => 'capture_mode', 32 => 'data', 33 => 'order_validity', 34 => 'transaction_condition', 35 => 'statement_reference',
			36 => 'card_validity', 37 => 'score_value', 38 => 'score_color', 39 => 'score_info', 40 => 'score_threshold',
			41 => 'score_profile', 43 => 'threed_ls_code', 44 => 'threed_relegation_code'
			); // pas de 42 ! normal !
		//
		$message = "message=$_POST[DATA]";
		$pathfile = "pathfile=/sherlocks/param/pathfile";
		$path_bin = "/sherlocks/bin/glibc-2.5-42/response";
		$message = escapeshellcmd($message);
		$result=exec("$path_bin $pathfile $message");

		$tableau = explode ("!", $result);
		foreach($infos as $key => $info) {
			if(isset($tableau[$key])) $data[$info] = $tableau[$key]; else $data[$info] = "";
		}
		if(($data['code'] == "" && $data['error'] == "") || ($data['code'] != 0)) {
			// ECHEC !!!!!!!!!!!!
			$data["result"] = false;
			return $this->render("AcmeGroupSiteBundle:Sherlocks:echec.html.twig", $data);
		} else {
			// SUCCESS !!!!!!!!!!
			$data["result"] = true;
			$panier = $this->get('acmeGroup.panier');
			if($auto === "auto") {
				// auto-response Sherlocks
				$versObj = $this->getDoctrine()->getManager()->getRepository('AcmeGroupLaboBundle:version')->find($data["customer_id"]);
				$userTP = $this->getDoctrine()->getManager()->getRepository('AcmeGroupUserBundle:User')->findByEmail($data["customer_email"]);
				$user = $userTP[0];
				$data["reference"] = $versObj->getSlug()."-".$data["transaction_id"]."-".$data["payment_date"];
				$facture = $this->get('acmeGroup.facture');
				$statut = $this->getDoctrine()->getManager()->getRepository('AcmeGroupLaboBundle:statut')->defaultVal();
				$data["facture"] = $facture->createNewByPanier($user, $versObj, $panier, $data, $statut[0]);
				$data["videPanier"] = $panier->videPanier($user);
				// mail au client
				// $mailer = $this->get('mailer');
				// $message = \Swift_Message::newInstance()
				// 	->setSubject($versObj->getNom()." - Votre commande")
				// 	->setFrom('noreply@singerfrance.com')
				// 	// ->setFrom($version['email'])
				// 	->setTo('emmanuel@aequation-webdesign.fr')
				// 	->setBody('manu7772@gmail.com'." : \nVotre commande a bien été enregistrée.\nMerci de votre confiance.");
				// $rmail = $mailer->send($message);
				$this->emailPrev($user, "Retour (auto) paiement LCL-Sherlocks : codes ".$data["facture"]->getBankresponsecode()."/".$data["facture"]->getResponsecode());
				$this->emailFacture("manu7772@gmail.com", $data["facture"]);
				$this->emailFacture("charlottetetau@gmail.com", $data["facture"]);
				$this->emailFacture("marianna.maglara@singer-distrib.com", $data["facture"]);
				// $this->emailFacture("gregory.ponsardin@singer-distrib.com", $data["facture"]);
				$this->emailFacture("logistique@singer-distrib.com", $data["facture"]);
				// EN CAS DE SUCCÈS UNIQUEMENT
				if((intval($data["facture"]->getBankresponsecode()) == 0) && (intval($data["facture"]->getResponsecode()) == 0)) {
					// email au client
					$this->emailConfirmClient($data["customer_email"], $data["facture"]);
					// email au magasin (si le mail du magasin existe !!!)
					if($user->getMagasin()->getEmail() !== NULL) {
						$this->emailMagasin($user->getMagasin()->getEmail(), $data["facture"]);
					}
				}
				// enregistre facture
				return new JsonResponse(json_encode("ok"));
				// return new JsonResponse(json_encode(array($versObj->getNom() => $data["transaction_id"])));
			} else {
				$this->container->get('acmeGroup.aelog')->createNewLogAuto();
				// response Sherlocks
				// ---> vérifie la facture (au cas où l'auto-response aurait échoué) et la crée si besoin
				// ---> mais normalement, il ne doit pas y avoir besoin !!!
				$versObj = $this->getDoctrine()->getManager()->getRepository('AcmeGroupLaboBundle:version')->find($data["customer_id"]);
				$userTP = $this->getDoctrine()->getManager()->getRepository('AcmeGroupUserBundle:User')->findByEmail($data["customer_email"]);
				$user = $userTP[0];
				$data["reference"] = $versObj->getSlug()."-".$data["transaction_id"]."-".$data["payment_date"];
				$facture = $this->get('acmeGroup.facture');
				$statut = $this->getDoctrine()->getManager()->getRepository('AcmeGroupLaboBundle:statut')->defaultVal();
				$data["facture"] = $facture->createNewByPanier($user, $versObj, $panier, $data, $statut[0]);
				$data["videPanier"] = $panier->videPanier($user);
				//
				// $version = $this->get('session')->get('version');
				// $data["reference"] = $version["slug"]."-".$data["transaction_id"]."-".$data["payment_date"];
				// $user = $this->get('security.context')->getToken()->getUser();
				// $data["videPanier"] = $panier->videPanier($user);
				// $data['mail_confirm'] = "Un mail vous a été adressé pour confirmation de votre commande.";
				return $this->render("AcmeGroupSiteBundle:Sherlocks:confirmation.html.twig", $data);
			}
		}
	}

	public function confirmationAction($data) {
		$this->container->get('acmeGroup.aelog')->createNewLogAuto();
		// if(!is_array($data)) $data = array();
		return $this->render("AcmeGroupSiteBundle:Sherlocks:confirmation.html.twig", $data);
	}

	public function echecAction($data) {
		$this->container->get('acmeGroup.aelog')->createNewLogAuto();
		// if(!is_array($data)) $data = array();
		return $this->render("AcmeGroupSiteBundle:Sherlocks:echec.html.twig", $data);
	}

	public function annulationAction() {
		$this->container->get('acmeGroup.aelog')->createNewLogAuto();
		$data = array();
		return $this->render("AcmeGroupSiteBundle:Sherlocks:annulation.html.twig", $data);
	}





	private function initParm() {
		$this->parm = array();
	}

	private function addParm($nom, $parm) {
		$this->parm[$nom] = $parm;
	}

	private function getStringParm() {
		$r = "";
		foreach($this->parm as $parm => $val) {
			$r .= $parm."=".$val." ";
		}
		return escapeshellcmd(trim($r));
	}


	private function emailPrev($user, $page = "(inconnue)") {
		// mail
		if(is_object($user)) {
			$mailer = $this->get('mailer');
			// $transport = \Swift_SmtpTransport::newInstance('auth.smtp.1and1.fr', 25)
			// 	->setUsername('emmanuel@aequation-webdesign.fr')
			// 	->setPassword('azetyu123');
			$message = \Swift_Message::newInstance()
				->setSubject($user->getNom()." - page : ".$page)
				->setContentType('text/html')
				->setFrom('noreply@singerfrance.com')
				// ->setFrom($version['email'])
				// ->setTo('emmanuel@aequation-webdesign.fr')
				->setTo('manu7772@gmail.com')
				// ->setBody($user->getNom()." A visité la page ".$page."\nBonne nouvelle, ça bouge !!!\n\nÀ bientôt.");
				->setBody($user->getNom()." a réalisé un paiement sur le site SingerFrance.com<br />Retour : ".$page."<br /><br />Ceci est une information automatique du site Singerfrance.com");
			$rmail = $mailer->send($message);
		}
	}

	private function emailFacture($dest, $facture) {
		$templating = $this->get('templating');
		$contenu = $templating->render("AcmeGroupSiteBundle:Sherlocks:mail-retourpaiement.html.twig", array("facture" => $facture));
		// mail
		if(is_object($facture)) {
			$mailer = $this->get('mailer');
			$message = \Swift_Message::newInstance()
				->setSubject($facture->getNom()." : paiement en ligne")
				->setContentType('text/html')
				->setFrom('noreply@singerfrance.com')
				->setTo($dest)
				->setBody($contenu);
			$rmail = $mailer->send($message);
		}
	}

	private function emailConfirmClient($dest, $facture) {
		$templating = $this->get('templating');
		$contenu = $templating->render("AcmeGroupSiteBundle:Sherlocks:mail-modele001.html.twig", array("facture" => $facture));
		// mail
		if(is_object($facture)) {
			$mailer = $this->get('mailer');
			$message = \Swift_Message::newInstance()
				->setSubject($facture->getNom()." : paiement en ligne")
				->setContentType('text/html')
				->setFrom('noreply@singerfrance.com')
				->setTo($dest)
				->setBody($contenu);
			$rmail = $mailer->send($message);
		}
	}

	private function emailMagasin($dest, $facture) {
		$templating = $this->get('templating');
		$contenu = $templating->render("AcmeGroupSiteBundle:Sherlocks:mail-retourpaiementMag.html.twig", array("facture" => $facture));
		// mail
		if(is_object($facture)) {
			$mailer = $this->get('mailer');
			$message = \Swift_Message::newInstance()
				->setSubject($facture->getNom()." : commande en ligne")
				->setContentType('text/html')
				->setFrom('noreply@singerfrance.com')
				->setTo($dest)
				->setBody($contenu);
			$rmail = $mailer->send($message);
		}
	}


}



