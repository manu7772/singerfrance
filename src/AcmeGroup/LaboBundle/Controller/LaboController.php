<?php

namespace AcmeGroup\LaboBundle\Controller;

use labo\Bundle\TestmanuBundle\Controller\LaboController as LaboCtrl;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class LaboController extends LaboCtrl {

	// Page d'accueil de l'admin (labo)
	public function homeAction($option = "home") {
		$data = array();
		$data['option'] = $option;
		switch ($option) {
			case 'home-public-exports':
				// récupération des marques
				$entity = $this->get("acmeGroup.entities")->defineEntity('marque');
				$mark = $entity->getRepo()->findAll();
				$data["marques"] = array();
				foreach ($mark as $marque) {
					$data["marques"][] = $marque->getNom();
				}
				break;
			
			default:
				# code...
				break;
		}
		// test ajout service directement dans twig
		$data['magasins'] = $this->get("AcmeGroup.magasin");
		return $this->render('AcmeGroupLaboBundle:pages:index.html.twig', $data);
	}

	public function leftSideMenuAction($option = "home") {
		$data = array();
		$data['option'] = $option;
		return $this->render('AcmeGroupLaboBundle:bloc:leftSideMenu.html.twig', $data);
	}

	public function navbarAction($pageweb = null) {
		// return parent navbarAction()
		return parent::navbarAction($pageweb);
	}



	/**
	 * checkUsers
	 *
	 * @return aeReponse
	 */
	private function checkUsers() {
		$r = array();
		$em = $this->getDoctrine()->getManager();
		$repoU = $em->getRepository('AcmeGroupUserBundle:User');
		$repoM = $em->getRepository('AcmeGroup\\LaboBundle\\Entity\\marque');
		$users = $repoU->findAll();
		$marqs = $repoM->findAll();
		// liste des noms de marques
		$ms = $objm = array();
		foreach($marqs as $marque) {
			$ms[] = strtoupper($marque->getNom());			// $ms 		= noms de marques
			$objm[strtoupper($marque->getNom())] = $marque;	// $objm	= objets marques
		}
		// Check
		foreach ($users as $user) {
			$MM = strtoupper($user->getMacmarque());
			if((in_array($MM, $ms)) && ($user->getMarque() === null)) {
				$r[$user->getId()]["marque"]["format"] = "string";
				$r[$user->getId()]["MACmarque"]["format"] = "string";
				$r[$user->getId()]["MACmarque"]["before"] = $user->getMacmarque();
				if($user->getMarque() !== null) $r[$user->getId()]["marque"]["before"] = $user->getMarque()->getNom();
					else $r[$user->getId()]["marque"]["before"] = "aucun";
				// change user marque
				$user->setMarque($objm[$MM]);
				$r[$user->getId()]["marque"]["after"] = $user->getMarque()->getNom();
				$r[$user->getId()]["MACmarque"]["after"] = $user->getMarque()->getNom();
			}
		}
		$em->flush();
		return new aeReponse(1, $r, "Check utilisateurs terminé");
		// return new aeReponse(2, array(), "Check User pas encore programmé…");
	}

	/**
	 * imagesVersionAction
	 * Ajoute les images d'entête de la version (pour l'instant impossible par fixtures)
	 */
	public function imagesVersionAction() {
		$em = $this->getDoctrine()->getManager();
		$repoImg = $em->getRepository("AcmeGroup\\LaboBundle\\Entity\\image");
		$repoVer = $em->getRepository("AcmeGroup\\LaboBundle\\Entity\\version");
		$imnoms = array("Singer" => "Logo Singer entête", "Singer-V2" => "Logo Singer.v2 entête", "DemoSinger" => "Logo Singer Démo");
		$cpt = 0;$echec = 0;
		foreach($imnoms as $ver => $img) {
			$i = $repoImg->findByNom($img);
			$v = $repoVer->findByNom($ver);
			if(is_object($v[0]) && is_object($i[0])) {
				$v[0]->setImageEntete($i[0]);
				$em->persist($v[0]);
				$cpt++;
			} else $echec++;
		}
		$em->flush();
		$this->get('session')->getFlashBag()->add("info", $cpt." images ajoutées / ".$echec." échec");
		$Tidx = $this->get("session")->get('version');
		return $this->redirect($this->generateUrl("acme_site_home", array("versionDefine" => $Tidx["slug"])));
	}

}
