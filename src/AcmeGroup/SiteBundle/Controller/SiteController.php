<?php

namespace AcmeGroup\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use labo\Bundle\TestmanuBundle\services\aetools\aeReponse;
// use app\Resources\classes\html2pdf\HTML2PDF;
use AcmeGroup\UserBundle\Entity\User;
use AcmeGroup\LaboBundle\Entity\tempuser;
use AcmeGroup\LaboBundle\Form\tempuserType;

class SiteController extends Controller {
	private $em;


	//////////////////////////
	// PAGES
	//////////////////////////

	public function indexAction() {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$params = $this->get('acmeGroup.parametre');
		$data["pageweb"] = $this->container->get('acmeGroup.pageweb')->getDynPages("homepage");
		$data["diapo"] = $this->container->get('acmeGroup.collection')->getDiaporama(
			$params->getDiapoIntroSlug()
		);
		// $this->get('session')->getFlashBag()->add("homepageRegister", $this->getRequest()->get('_route'));
		// echo ("<div>ROUTE : ".$this->getRequest()->get('_route')."</div>");
		// $data["homepageRegister"] = $params->getHomePageRegister();
		// echo("homepage Register : ".$data["homepageRegister"]."<br />");
		return $this->render($this->verifVersionPage($data['pageweb'][0]->getFichierhtml()), $data);
	}

	public function pagewebAction($pagewebSlug, $pagedata = null) {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$pagedata = json_decode($pagedata, true);
		// page web
		$pageweb = $this->container->get('acmeGroup.pageweb');
		$data["pageweb"] = $pageweb->getDynPages($pagewebSlug);
		// données relatives à la page
		$data = array_merge($data, $this->getDatasForPageweb($pagewebSlug, $pagedata));

		return $this->render($this->verifVersionPage($data['pageweb'][0]->getFichierhtml()), $data);
	}

	public function pdfimportAction($mode = "view", $id, $nom = null) {
		$fichier = $this->get('acmeGroup.entities')->defineEntity('fichierPdf');
		switch ($mode) {
			case 'load':
				$ctype = 'application/force-download';
				$attach = 'attachment; ';
				break;
			default:
				$ctype = 'application/pdf';
				$attach = '';
				break;
		}
		$data['fichierPdf'] = $fichier->getRepo()->find($id);
		if(is_object($data['fichierPdf'])) {
			$file = __DIR__.'/../../../../web/images/pdf/'.$data['fichierPdf']->getFichierNom();
			if(file_exists($file)) {
				return new Response(
					file_get_contents($file),
					200,
					array(
						'Content-Type' => $ctype,
						// 'Content-Disposition' => 'filename='.$data['fichierPdf']->getFichierOrigine()
						'Content-Disposition' => 'attachment; '.'filename='.$data['fichierPdf']->getFichierOrigine()
					)
				);
			} else {
				return new Response('Fichier PDF absent !!<br />'.$file);
			}
		} else {
			return new Response('Fichier PDF inexistant en base de données (id = '.$id.')');
		}

	// EXEMPLE :
	//    $fichier = "nomFichier.pdf";
	//    $chemin = "bundles/nomBundle/.../"; // emplacement de votre fichier .pdf
	//          
	//    $response = new Response();
	//    $response->setContent(file_get_contents($chemin.$fichier));
	//    $response->headers->set('Content-Type', 'application/force-download'); // modification du content-type pour forcer le téléchargement (sinon le navigateur internet essaie d'afficher le document)
	//    $response->headers->set('Content-disposition', 'filename='. $fichier);
	//          
	//    return $response;
	}

	public function categoriesAction($categorieSlug) {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$data["categorieSlug"] = $categorieSlug;
		return $this->render($this->verifVersionPage("pageListproduits"), $data);
	}

	public function autresUnivAction($categorieSlug) {
		$data["categorieSlug"] = $categorieSlug;
		return $this->render($this->verifVersionPage("pageListautrUnivers"), $data);
	}

	public function pageAtelierCreatifAction($categorieSlug = null) {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$atelier = $this->get('acmeGroup.atelierCreatif');
		$atel = $atelier->getThemesEtFiches();
		if($categorieSlug !== null && $categorieSlug !== "atelier-creatif") {
			// page des fiches du thème $categorieSlug
			$data["theme"] = $atel[$categorieSlug];
		} else {
			// page générique des thèmes
			$data["themes"] = $atel;
		}
		return $this->render($this->verifVersionPage("pageAtelierCreatif"), $data);
	}

	public function pageAtelierFicheAction($fiche) {
		$atelier = $this->get('acmeGroup.atelierCreatif');
		$data["fiche"] = $atelier->getFicheBySlug($fiche);
		return $this->render($this->verifVersionPage("pageThemesAtelier"), $data);
	}

	public function pageEBoutiqueAction() {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$cats = array("acme_site_univers", "acme_site_categories");
		$macc = array("Mécaniques", "Électroniques", "Professionnelles");
		$articles = $this->get("AcmeGroup.article")->getArticlesByReseau('e-commerce');
		$arr = array();
		foreach($articles as $article) {
			foreach($article->getCategories() as $categorie) {
				if(in_array($categorie->getPage()->getRoute(), $cats)) {
					$arr[$categorie->getId()]["slug"] = $categorie->getSlug();
					$catnom = $categorie->getNom();
					if(in_array($catnom, $macc)) $catnom = "Machines à coudre ".mb_strtolower($catnom,'UTF-8');
					$arr[$categorie->getId()]["nom"] = $catnom;
					$arr[$categorie->getId()]["articles"][$article->getNom()] = $article;
				}
				// ["categories"]
			}
		}
		ksort($arr);
		foreach($arr as $c => $cat) $data["categories"][$cat["nom"]] = $cat;
		// $this->emailPrev($this->get('security.context')->getToken()->getUser(), "e-Boutique");
		$data["pageweb"] = $this->container->get('acmeGroup.pageweb')->getRepo()->findBySlug("eboutique");
		return $this->render($this->verifVersionPage("pageEBoutique"), $data);
	}

	public function PageMonPanierAction() {
		// echo("<pre>");
		// var_dump($this->get('security.context')->getToken());
		// echo("</pre>");
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$user = $this->get('security.context')->getToken()->getUser();
		$panier = $this->get('acmeGroup.panier');
		$facture = $this->get('acmeGroup.facture');
		$data = array(
			// "articles"		=> $panier->getArticlesOfUser($user),
			"infopanier"	=> $panier->getInfosPanier($user),
			"user"			=> $user,
			"factures"		=> $facture->getFacturesOfUser($user),
		);
		// $this->emailPrev($user, "PANIER");
		// $f = $facture->getRepo()->findAll();
		// $this->emailFacture("manu7772@gmail.com", $f[0]);
		// if(intval($f[0]->getBankresponsecode()) == 0) {
		// 	$this->emailConfirmClient($f[0]);
		// 	$this->emailMagasin("manu7772@gmail.com", $f[0]);
		// }
		return $this->render("AcmeGroupSiteBundle:Site:pagePanier.html.twig", $data);
	}

	public function pageMesFacturesAction($user = null) {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		if($user === null) {
			$user = $this->get('security.context')->getToken()->getUser();
		}
		$data = array(
			"user"			=> $user,
			"factures"		=> $this->get('acmeGroup.facture')->getFacturesOfUser($user, "stade", "ASC"),
		);
		return $this->render("AcmeGroupSiteBundle:Site:pageFacturesUser.html.twig", $data);
	}

	public function detailFactureAction($facture) {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$data = array(
			"facture"		=> $this->get('acmeGroup.facture')->getRepo()->find($facture)
		);
		return $this->render("AcmeGroupSiteBundle:Site:detailFacture.html.twig", $data);
	}

	/**
	 * pdfFactureAction
	 * Génération/export de la facture $facture en pdf
	 */
	public function pdfFactureAction($facture) {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$facture = $this->get('acmeGroup.facture')->getRepo()->find($facture);
		// génération du PDF
		// $html2pdf = $this->container->get('acmeGroup.html2pdf');
		require_once('../app/Resources/classes/html2pdf/html2pdf.class.php');
		$html2pdf = new \HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', array(10, 8, 10, 10));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html = $this->renderView("AcmeGroupSiteBundle:Site:detailFacturePDF.html.twig", array("facture" => $facture));
		$html2pdf->writeHTML($html, false);
		return new Response($html2pdf->Output($facture->getReference().'.pdf'), 200, array(
			'Content-Type' => 'application/force-download',
			// 'Content-Disposition' => 'attachment; filename='.$facture->getReference().'.pdf'
			)
		);
		// return $this->render("AcmeGroupSiteBundle:Site:detailFacture.html.twig", $data);
	}

	public function pageRechercheMagasinAction($local = null) {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$data = array();
		$magasin = $this->get('acmeGroup.magasin');
		$rechmag = $this->getRequest()->request->get('rechmag'); // POST
		// if($rechmag !== null) $rechmag = $this->getRequest()->query->get('zone'); // GET
		// si Post ou Get existe -> $local n'est pas pris en compte !!
		if($rechmag !== null) $local = $rechmag;
		// détection ville ou département/code postal
		if(preg_match("#^(\d+)$#", $local)) {
			// code postal ou département
			if((strlen($local) < 2) || (strlen($local) == 4)) $local = "0".$local;
			$mags = $magasin->findMagsByCP($local);
			$data["magasins"] = $this->optimizeMagasins($mags);
		} else if(($local !== null) || ($local != "")) {
			// ville
			$mags = $magasin->findMagsByVille($local);
			if(count($mags) < 1) $mags = $magasin->findMagsByCP($local);
			$data["magasins"] = $this->optimizeMagasins($mags);
		}
		$data["listeVilles"] = $magasin->listeVilleAvecMagasin();
		return $this->render("AcmeGroupSiteBundle:Site:pageRechercheMagasin.html.twig", $data);
	}

	public function pageUnMagasinAction($id) {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$data["magasin"] = $this->get('acmeGroup.magasin')->findMag($id);
		return $this->render("AcmeGroupSiteBundle:Site:pageUnMagasin.html.twig", $data);
	}


	// pages types
	public function pagesTypesAction($type = 1) {
		$articles = $this->get("AcmeGroup.magasin")->getArticlesByReseau('e-commerce');
		return $this->render("AcmeGroupSiteBundle:Site:pagesTypes.html.twig");
	}

	// pages du monde Singer
	public function pagesMondeAction($pagewebSlug = null) {
		$data = array();
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$pageweb = $this->get("acmeGroup.pageweb");
		// $data['menu'] = $pageweb->getListByTag("monde"); // chargement du menu
		$data["pageweb"] = $pageweb->getDynPages($pagewebSlug);
		$data['menu'] = $pageweb->getListByCategorie("le-monde-singer"); // chargement du menu
		if(count($data['pageweb']) > 0) {
			// foreach($data["pageweb"] as $m) echo("page : ".$m->getNom()."<br />");
			// foreach($data["menu"] as $m) echo("menu : ".$m->getNom()."<br />");
			return $this->render($this->verifVersionPage($data['pageweb'][0]->getFichierhtml()), $data);
		} else {
			return new Response('Erreur : aucun texte trouvé');
		}
	}

	// pages web
	public function pageswebWithRichtextAction($pagewebSlug) {
		$data = array();
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$pageweb = $this->get("acmeGroup.pageweb");
		$data['pageweb'] = $pageweb->getDynPages($pagewebSlug);
		return $this->render($this->verifVersionPage($data['pageweb'][0]->getFichierhtml()), $data);
	}

	// pages vidéos
	public function pageVideosAction($recherche = null) {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		// $data["pageweb"] = $this->container->get('acmeGroup.pageweb')->getRepo()->findBySlug("video");
		$data["recherche"] = $recherche;

		$Tidx = $this->get("session")->get('version');
		$em = $this->getDoctrine()->getManager()->getRepository('AcmeGroupLaboBundle:video')->setVersion($Tidx["nom"]);
		$data['videos'] = $em->getVideos($recherche);
		// $data["titre"] = "Page des vidéos";
		$data["pageweb"] = $this->container->get('acmeGroup.pageweb')->getRepo()->findBySlug("video");
		return $this->render($this->verifVersionPage($data['pageweb'][0]->getFichierhtml()), $data);
	}

	public function pageMacAction() {
		return $this->render("AcmeGroupSiteBundle:Site:pageMac.html.twig");
	}

	public function pageUniversAction() {
		return $this->render("AcmeGroupSiteBundle:Site:pageUnivers.html.twig");
	}

	// Page avec les deux univers
	public function pageProduitsAction() {
		// $this->get('acmeGroup.aelog')->createNewLogAuto();
		$data["pageweb"] = $this->container->get('acmeGroup.pageweb')->getDynPages("produits");
		return $this->render($this->verifVersionPage($data['pageweb'][0]->getFichierhtml()), $data);
	}

	public function pageEnCoursAction() {
		return $this->render("AcmeGroupSiteBundle:Site:pageEnCours.html.twig");
	}

	// Page fiche produits
	public function ficheArticleAction($articleSlug, $tab = "") {
		$this->get('acmeGroup.aelog')->createNewLogAuto();
		$data["articleSlug"] = $articleSlug;
		$data["tab"] = $tab;
		return $this->render("AcmeGroupSiteBundle:Site:pageFicheArticle.html.twig", $data);
	}


	//////////////////////////
	// BLOCS
	//////////////////////////

	public function menuAction($template = "menuPpal") {
		return $this->render("AcmeGroupSiteBundle:menu:".$template.".html.twig");
	}

	public function footerAction($template = "footer") {
		$data = array();
		$data["foothaut"] = "footerHaut3";
		$data["footbas"] = "footerBas2";
		$part = $this->get('AcmeGroup.entities')->defineEntity('partenaire');
		$data['partenaires'] = $part->getRepo()->findAll();
		// pop up MIMC
		// echo ("<div>ROUTE : ".$this->getRequest()->get('_route')."</div>");
		// if($this->getRequest()->get('_route') === "acme_site_home")
		// 	$data["homepageRegister"] = $params->getHomePageRegister();
		// echo("homepage Register : ".$data["homepageRegister"]."<br />");
		return $this->render("AcmeGroupSiteBundle:Site:".$template.".html.twig", $data);
	}

	public function monPanierIconeAction() {
		$user = $this->get('security.context')->getToken()->getUser();
		$panier = $this->get('acmeGroup.panier');
		$data["infopanier"] = $panier->getInfosPanier($user);
		return $this->render("AcmeGroupSiteBundle:bloc:monPanier.html.twig", $data);
	}

	public function tableauPanierAction() {
		$user = $this->get('security.context')->getToken()->getUser();
		$panier = $this->get('acmeGroup.panier');
		$data = array(
			"articles"		=> $panier->getArticlesOfUser($user),
			"infopanier"	=> $panier->getInfosPanier($user),
			"user"			=> $user
		);
		return $this->render("AcmeGroupSiteBundle:bloc:tableauPanier.html.twig", $data);
	}

	public function versionDefineAction() {
		return $this->render("AcmeGroupSiteBundle:Site:versionDefine.html.twig");
	}

	public function homepageAction() {
		return $this->render("AcmeGroupSiteBundle:Site:homepage.html.twig");
	}
	public function ListproduitsAction($categorieSlug) {
		$data = $this->get("AcmeGroup.article")->getArticlesByCategorie($categorieSlug);
		$data["categorieSlug"] = $categorieSlug;
		return $this->render("AcmeGroupSiteBundle:Site:Listproduits.html.twig", $data);
	}
	public function ListAutrUniversAction($categorieSlug) {
		$data = $this->get("AcmeGroup.article")->getArticlesByCategorie($categorieSlug);
		$data["categorieSlug"] = $categorieSlug;
		return $this->render("AcmeGroupSiteBundle:Site:ListAutrUnivers.html.twig", $data);
	}
	public function MacAction() {
		return $this->render("AcmeGroupSiteBundle:Site:Mac.html.twig");
	}
	public function UniversAction() {
		return $this->render("AcmeGroupSiteBundle:Site:Univers.html.twig");
	}
	// Fiches produits
	public function blocFicheArticleAction($articleSlug, $tab = "") {
		// fiche à afficher en priorité
		$data["tab"] = $tab;
		// chargement fiches
		// $data["pageweb"] = $this->container->get('acmeGroup.pageweb')->getRepo()->findBySlug("produits");
		$data["pageweb"] = $this->container->get('acmeGroup.pageweb')->getDynPages("produits", array("article::".$articleSlug));
		$atelier = $this->get('acmeGroup.atelierCreatif');
		$data["themes"] = $atelier->getThemesEtFiches();
		//chargement articles
		$data["articleSlug"] = $articleSlug;
		$r = $this->getDoctrine()->getManager()->getRepository('AcmeGroupLaboBundle:article')->findBySlug($articleSlug);
		$data["article"] = $r[0];
		$cat = $data["article"]->getCategories();
		$data["categorieSlug"] = $cat[1]->getSlug();
		return $this->render("AcmeGroupSiteBundle:Site:ficheArticle.html.twig", $data);
	}

	// avisUtilisateurs
	public function avisUtilisateursAction($id) {
		$artools = $this->get('acmeGroup.article');
		$data["article"] = $artools->getInfoFiche($id);
		$vote = $this->get('acmeGroup.votes');
		$data["dejavote"] = $vote->dejaVote($data["article"], $this->get('security.context')->getToken()->getUser());
		return $this->render("AcmeGroupSiteBundle:bloc:avisUtilisateurs.html.twig", $data);
	}

	// BLOCS/ACTIONS Ajax

	// Enregistre un vote pour un article (note + avis)
	// Données envoyées en POST
	public function voteArticleAction() {
		$em = $this->getDoctrine()->getManager();
		$vote = $this->get('acmeGroup.votes');
		$user = $this->getRequest()->request->get("userID");
		$note = $this->getRequest()->request->get("noteArticle");
		$avis = $this->getRequest()->request->get("Votre_avis");
		$article = $this->getRequest()->request->get("articleID");
		// if($user === null) $user = $this->getRequest()->request->get("anonIP");
		$userO = $em->getRepository('AcmeGroupUserBundle:User')->find($user);
		$ArticleO = $em->getRepository('AcmeGroupLaboBundle:article')->find($article);
		$data = $vote->voteForArticle($ArticleO, $note, $avis, $userO); // enregistre le vote
		return new JsonResponse(json_encode($data));
	}

	/**
	 * changePanierAction
	 * action sur panier
	 * Données envoyées en POST
	 */
	public function changePanierAction() {
		$action 	= $this->getRequest()->request->get("action");
		$idarticle 	= $this->getRequest()->request->get("idarticle");
		$quantite 	= $this->getRequest()->request->get("quantite");
		$user 		= $this->getRequest()->request->get("user");
		if($user != null) $user = $this->getDoctrine()->getManager()->getRepository('AcmeGroupUserBundle:User')->find($user);
			else $user = $this->get('security.context')->getToken()->getUser();
		if(intval($quantite) < 1) $quantite = 1;
		if($idarticle !== null) $article = $this->get('acmeGroup.article')->getArticleById($idarticle);
			else $article = false;
		if($article !== false || $action === 'viderPanier') {
			$panier = $this->get('acmeGroup.panier');
			switch($action) {
				case 'supprimer': // Supprimer un article du panier
					$ajout = $panier->SupprimeArticle($article, $user);
					$data = new aeReponse($ajout->getResult(), null, $ajout->getMessage());
					break;
				case 'enlever': // Enlever x quantités d'un article
					$ajout = $panier->reduitArticle($article, $user, $quantite);
					$data = new aeReponse($ajout->getResult(), null, $ajout->getMessage());
					break;
				case 'ajouter': // Ajouter x quantités à un article
					$ajout = $panier->ajouteArticle($article, $user, $quantite);
					$data = new aeReponse($ajout->getResult(), null, $ajout->getMessage());
					break;
				case 'viderPanier': // Supprimer tous les articles (vider le panier)
					$ajout = $panier->videPanier($user);
					$data = new aeReponse($ajout->getResult(), null, $ajout->getMessage());
					break;
				default:
					$data = new aeReponse(false, null, "Requête inconnue (".$action.").");
					break;
			}
		} else {
			$data = new aeReponse(false, null, "L'article ".$idarticle." n'existe pas.");
		}
		return new JsonResponse($data->getJSONreponse());
	}

	// mise à jour d'une zone selfmaj
	// voir majzone() javascript
	// !!! données passées en POST !!!
	public function majZoneAction() {
		$posts = $this->getRequest()->request->all();
		$call = $posts["methode"]."Action";
		$data = $this->$call($posts);
		return new JsonResponse(json_encode($data));
	}
	///////////////////////////////
	// ---> méthodes pour majZone :
	///////////////////////////////
	// raffraîchissement des notations articles (étoiles)
	public function majZoneBloc5etoilesAction($dataFromPost) {
		// données nécessaires :
		// - articleId = id de l'article <input type="hidden" class="Notation_articleId" name="Notation_articleId" value="{{ article.id }}" />
		$data['article'] = $this->getDoctrine()->getManager()->getRepository('AcmeGroupLaboBundle:article')->find($dataFromPost['articleId']);
		if(is_object($data["article"])) {
			$data['note'] = $data['article']->getNotation();
			$result['html'] = $this->renderView("AcmeGroupSiteBundle:bloc:bloc5etoiles_majZone.html.twig", $data);
			$result['result'] = true;
		} else $result['result'] = false;
		return $result;
	}
	// raffraîchissement des avis utilisateurs
	public function majZoneBlocAvisArticleAction($dataFromPost) {
		// données nécessaires :
		// - articleId = id de l'article <input type="hidden" class="Notation_articleId" name="Notation_articleId" value="{{ article.id }}" />
		$artools = $this->get('acmeGroup.article');
		$data["article"] = $artools->getInfoFiche($dataFromPost['articleId']);
		if(is_object($data["article"])) {
			$result['html'] = $this->renderView("AcmeGroupSiteBundle:bloc:blocAvisArticle.html.twig", $data);
			$result['result'] = true;
		} else $result['result'] = false;
		return $result;
	}
	// raffraîchissement de l'icone panier en haut de toutes les pages
	public function majZoneBlocIconePanierAction($dataFromPost) {
		// données nécessaires :
		// - 
		$user = $this->get('security.context')->getToken()->getUser();
		$panier = $this->get('acmeGroup.panier');
		$data = array(
			"infopanier" => $panier->getInfosPanier($user),
			true
		);
		if(is_array($data)) {
			$result['html'] = $this->renderView("AcmeGroupSiteBundle:bloc:monPanier.html.twig", $data);
			$result['result'] = true;
		} else $result['result'] = false;
		return $result;
	}
	// raffraîchissement tableau panier
	public function majZoneBloctableauPanierAction($dataFromPost) {
		$user = $this->get('security.context')->getToken()->getUser();
		$panier = $this->get('acmeGroup.panier');
		$data = array(
			"articles"		=> $panier->getArticlesOfUser($user),
			"infopanier"	=> $panier->getInfosPanier($user),
			"user"			=> $user
		);
		if(($data["infopanier"] !== null) && ($data["articles"] !== null)) {
			$result['html'] = $this->renderView("AcmeGroupSiteBundle:bloc:tableauPanier.html.twig", $data);
			$result['result'] = true;
		} else $result['result'] = false;
		return $result;
	}

	// AUTRES BLOCS POUR SingerDemo

	public function tableauVersionAction() {
		$Tidx = $this->get("session")->get('version');
		$data["versionsTXT"] = $this->sessionVars();
		// $a = $this->getDoctrine()->getManager()->getRepository("AcmeGroupLaboBundle:article")->find(1);
		// echo("Article alias namespace : ".$a->getAliasNameSpace()."<br />");
		return $this->render("AcmeGroupSiteBundle:".$Tidx["templateIndex"].":tableau_version.html.twig", $data);
	}

	public function rapportEventAction() {
		$Tidx = $this->get("session")->get('version');
		return $this->render("AcmeGroupSiteBundle:".$Tidx["templateIndex"].":rapport_event.html.twig");
	}

	public function listRoutesAction() {
		$Tidx = $this->get("session")->get('version');
		$data["listRoutes"] = $this->get("acmeGroup.aetools")->getAllRoutes("acme_site_");
		return $this->render("AcmeGroupSiteBundle:".$Tidx["templateIndex"].":listRoutes.html.twig", $data);
	}

	public function infoVotesUsersAction() {
		$Tidx = $this->get("session")->get('version');
		$this->em = $this->getDoctrine()->getManager();
		$data['users'] = $this->em->getRepository("AcmeGroupUserBundle:User")->findAll();
		return $this->render("AcmeGroupSiteBundle:".$Tidx["templateIndex"].":infoVotesUsers.html.twig", $data);
	}


	//////////////////////////
	// Register popup
	//////////////////////////

	public function registerPopupAction() {
		$data = array();
		$data["exist"] = false;
		$em = $this->getDoctrine()->getManager();

		$obj = new tempuser();
		// ajout de la version courante
		$ver = $this->get("session")->get('version');
		$version = $em->getRepository("AcmeGroupLaboBundle:version")->findBySlug($ver["slug"]);
		if(isset($version[0])) $obj->addVersion($version[0]);
		// Création du formulaire
		$form = $this->createForm(new tempuserType($this), $obj);

		$request = $this->get('request');
		if($request->getMethod() == "POST") {
			$form->bind($request);
			// ---> verifie si l'inscrit n'existe pas déjà dans User
			$data["exist"] = $this->verifUserExists($form->getData());
			if($form->isValid() && ($data["exist"] === false)) {
				$em->persist($obj);
				// ---> enregistrement réel de User
				$this->converToUser($obj);
				// ---> envoi du mail avec le code PROMO (SINGER15)
				$this->emailCodePromo($obj);
				$em->flush();
				$reponse = $this->renderView("AcmeGroupSiteBundle:Site:pageRegisterPopup_ok.html.twig", $data);
			} else {
				// formulaire mal rempli
				$data["form"] = $form->createView();
				$reponse = $this->renderView("AcmeGroupSiteBundle:Site:pageRegisterPopup.html.twig", $data);
			}
		} else {
			// formulaire vierge
			$data["form"] = $form->createView();
			$reponse = $this->renderView("AcmeGroupSiteBundle:Site:pageRegisterPopup.html.twig", $data);
		}
		return new Response($reponse);
	}

	/**
	 * Vérifie si un tempuser existe déjà selon un tempuser
	 * @param tempuser
	 * @return boolean (false si User n'existe pas)
	 */
	private function verifTempUserExists($tempuser) {
		$message = array();
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('AcmeGroupLaboBundle:tempuser');
		$byUsername = $repo->findByUsername($tempuser->getUsername());
		$byEmail = $repo->findByEmail($tempuser->getEmail());
		if(count($byUsername) > 0) $message[] = "Ce nom d'utilisateur existe déjà.";
		if(count($byEmail) > 0) $message[] = "Cet email existe déjà";
		// Renvoi
		if(count($message) > 0) return implode("<br />", $message);
			else return false;
	}

	/**
	 * Vérifie si un User existe déjà selon un tempuser
	 * @param tempuser
	 * @return boolean (false si User n'existe pas)
	 */
	private function verifUserExists($tempuser) {
		$message = array();
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('AcmeGroupUserBundle:User');
		$byUsername = $repo->findByUsername($tempuser->getUsername());
		$byEmail = $repo->findByEmail($tempuser->getEmail());
		if(count($byUsername) > 0) $message[] = "Ce nom d'utilisateur existe déjà.";
		if(count($byEmail) > 0) $message[] = "Cet email existe déjà";
		// Renvoi
		if(count($message) > 0) return implode("<br />", $message);
			else return false;
	}

	/**
	 * Enregistre un tempuser dans User
	 * @param tempuser
	 */
	private function converToUser($tempuser) {
		// $newUser = new User();
		$UM = $this->get('fos_user.user_manager');
		$newUser = $UM->createUser();
		// connexion
		$newUser->setUsername($tempuser->getUsername());
		$newUser->setEmail($tempuser->getEmail());
		// magasin
		$newUser->setMagasin($tempuser->getMagasin());
		$newUser->setLivraison($tempuser->getModelivraison());
		// machine
		$newUser->setMactype($tempuser->getTypemachine());
		if(is_object($tempuser->getMarque()))
			$newUser->setMacmarque($tempuser->getMarque()->getNom());
		$newUser->setMarque($tempuser->getMarque());
		$newUser->setMacnoserie($tempuser->getNumserie());
		$newUser->setMacdateachat($tempuser->getDateachat());
		// version(s)
		foreach($tempuser->getVersions() as $version) {
			$newUser->addVersion($version);
		}
		// activation
		$newUser->setEnabled(true);
		// password
		$newUser->setPlainPassword($tempuser->getMdp1());

		// enregistre le User
		$UM->updateUser($newUser);
	}

	//////////////////////////
	// Autres fonctions
	//////////////////////////

	/**
	 * Récupère les données nécessaires relatives aux pages web
	 * @param string $pagewebSlug
	 * @param array $pagedata = null
	 * @return array
	 */
	private function getDatasForPageweb($pagewebSlug, $pagedata = null) {
		$data = array();
		// var_dump($pagedata);
		switch ($pagewebSlug) {
			case 'liste-d-evenements':
				// $data["pageweb"] = $pageweb->getRepo()->findBySlug("liste-d-evenements");
				$events = $this->get("acmeGroup.events");
				$dates = $events->getAllEventsByType("salons-foires");
				$actu = $events->classByYear($dates);
				// liste des années
				$data["yearslist"] = array();
				foreach ($actu as $key => $value) $data["yearslist"][] = $key;
				// année en cours par défaut
				$date = new \Datetime();
				$YenCours = intval($date->format("Y"));
				if(!isset($pagedata["dateY"])) $pagedata["dateY"] = $tmpDate = $YenCours;
					else $tmpDate = $pagedata["dateY"];
				if($pagedata["dateY"] === "all") unset($tmpDate);
				// if(!isset($data["yearslist"][$tmpDate])) $data["yearslist"][$tmpDate] = array();
				// ne trie que l'année si $pagedata = dateY => année (ex. 2014)
				if(isset($tmpDate)) {
					do {
						if(array_key_exists($tmpDate, $actu)) $data["actualite"][$tmpDate] = $actu[$tmpDate];
						$tmpDate--;
						// if(!isset($data["actualite"][$tmpDate+1])) $data["actualite"][$tmpDate+1] = array();
					} while((!isset($data["actualite"][$tmpDate+1])) || (count($data["actualite"][$tmpDate+1]) < 1));
				} else $data["actualite"] = $actu;
				break;
			case 'les-articles-de-presse':
				// $data["pageweb"] = $pageweb->getRepo()->findBySlug("les-articles-de-presse");
				$events = $this->get("acmeGroup.events");
				if(isset($pagedata["id"])) {
					$data["presse1art"] = $events->getRepo()->find($pagedata["id"]);
				} else {
					$dates = $events->getAllEventsByType("presse");
					$actu = $events->classByYear($dates);
					// ne trie que l'année si $pagedata = année
					if(array_key_exists($pagedata["dateY"], $actu)) $data["presse"][$pagedata["dateY"]] = $actu[$pagedata["dateY"]];
						else $data["presse"] = $actu;
				}
				break;
			
			default:
				//
				break;
		}
		return $data;
	}

	private function optimizeMagasins($mags) {
		$this->em = $this->getDoctrine()->getManager();
		$mailvalidate = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
		$champstest = array(
			"ville" => 2,
			"adresse" => 1,
			"responsable" => 1,
			"cp" => 1
			);
		$magasins = array();
		foreach($mags as $magasin) {
			$defauts = 0;
			foreach($champstest as $test => $coef) {
				$Gmethod = "get".ucfirst($test);
				$Smethod = "set".ucfirst($test);
				if((trim($magasin->$Gmethod()."")) == "") {
					$defauts = $defauts + $coef;
					$magasin->$Smethod("");
				}
			}
			// supprime les emails défectueux
			if(!preg_match($mailvalidate, $magasin->getEmail())) {
				$magasin->setEmail(null);
			}
			$this->em->persist($magasin);
			// ajoute du magasin optimisé dans la liste
			if($defauts < 3) $magasins[] = $magasin;
		}
		// enregistrement en BDD des magasins optimisés
		$this->em->flush();
		return $magasins;
	}

	// Fonction d'affichate TABLE pour SingerDemo
	private function sessionVars() {
		$test = "<table style='border:1px solid blue;'>";
		foreach($this->container->get('request')->getSession()->all() as $nom => $val) {
			$test .= "<tr style='background-color:#ddf;'><td colspan='2'><b style='color:blue;'>".$nom."</b></td></tr>";
			if(is_array($val)) foreach($val as $nom2 => $val2) {
				$val3 = $val2;
				if(is_string($val2)) $val3 = $val2;
				if(is_array($val2)) $val3 = "<i title='".var_dump($val2)."'>(array ".count($val2).")</i>";
				if(is_object($val2)) $val3 = "<i>(object ".count($val2).")</i>";
				if(strlen($val3) > 120) $val3 = "<i>string (".strlen($val3).")</i>"; // réduit la chaîne si trop longue…
				$test .= "<tr><td>".$nom2."</td><td>".$val3."</td></tr>";
			} else if(is_string($val)) {
				$test .= "<tr><td colspan='2'>".$val."</td></tr>";
			}
		}
		$test .= "</table>";
		return $test;
	}

	private function TESTS() {
		if($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ) {

			$this->em = $this->getDoctrine()->getManager();
	
			// // modif sur facture
			// $facture = $this->em->getRepository("AcmeGroupLaboBundle:facture")->find(4);
			// if(is_object($facture)) {
			// 	$facture->inverseAnnule();
			// 	// $this->em->persist($facture); // pas besoin de persiser !!!
			// 	$this->em->flush();
			// }
			// // modif de prix sur article		
			// $article = $this->em->getRepository("AcmeGroupLaboBundle:article")->find(1);
			// if(is_object($article)) {
			// 	$article->setPrix(5000);
			// 	// $this->em->persist($article); // pas besoin de persiser !!!
			// 	$this->em->flush();
			// }
			// $article = $this->em->getRepository("AcmeGroupLaboBundle:article")->find(2);
			// if(is_object($article)) {
			// 	$article->setPrixHT(1000);
			// 	// $this->em->persist($article); // pas besoin de persiser !!!
			// 	$this->em->flush();
			// }
		}


		
		// $user = $this->get('security.context')->getToken();

		// $user = $this->em->getRepository("AcmeGroupUserBundle:User")->findByUsername("sadmin");
		// $prefs = array("trou" => "ducul", "R1" => 1000, "R6" => 600, "etc" => "etc");
		// foreach($prefs as $nom => $val) $user[0]->addPreference($nom, $val);
		// $this->em->persist($user[0]);
		// $this->em->flush();

		// var_dump($user[0]->getPreferences());

		// echo("val : ".$user[0]->getPreference("trou")."<br />");
		// echo("val : ".$user[0]->getPreference("R1")."<br />");
		// echo("val : ".$user[0]->getPreference("R6")."<br />");
		// echo("val : ".$user[0]->getPreference("etc")."<br />");
		// echo("val : ".$user[0]->getPreference("test")."<br />");
		// echo("val : ".$user[0]->getPreference("oups")."<br />");

		// if($this->get('security.context')->isGranted('ROLE_USER')) {
		// 	$user = $this->em->getRepository("AcmeGroupUserBundle:User")->findByUsername("charlotte");
		// 	$newFact = new facture();
		// 	// version
		// 	$ver = $this->get("session")->get('version');
		// 	$version = $this->em->getRepository("AcmeGroupLaboBundle:version")->findBySlug($ver["slug"]);
		// 	$newFact->addVersion($version[0]);
		// 	// client
		// 	$newFact->setClient($user[0]);
		// 	$this->em->persist($newFact);
		// 	$this->em->flush();
		// }
	}

	//////////////////////////
	// Autres fonctions
	//////////////////////////

	private function recupVersion() {
		$Tidx = $this->get("session")->get('version');
		if($Tidx["templateIndex"] === null) $Tidx["templateIndex"] = "Site";
		return $Tidx;
	}

	private function verifVersionPage($page) {
		$p = explode(":", $page, 2);
		if(count($p) > 1) {
			$Tidx["templateIndex"] = $p[0];
			$page = $p[1];
		} else {
			$Tidx = $this->recupVersion();
		}
		if(!$this->get('templating')->exists("AcmeGroupSiteBundle:".$Tidx["templateIndex"].":".$page.".html.twig")) {
			// si la page n'existe pas, on prend le template de la version par défaut
			$Tidx["templateIndex"] = $this->get("acmeGroup.version")->getDefaultVersionDossTemplates();
		}
		return "AcmeGroupSiteBundle:".$Tidx["templateIndex"].":".$page.".html.twig";
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
				->setBody($user->getUsername()." a visité la page ".$page."<br /><br />Ceci est une information automatique du site Singerfrance.com");
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

	private function emailConfirmClient($facture) {
		$templating = $this->get('templating');
		$contenu = $templating->render("AcmeGroupSiteBundle:Sherlocks:mail-modele001.html.twig", array("facture" => $facture));
		// mail
		if(is_object($facture)) {
			$mailer = $this->get('mailer');
			$message = \Swift_Message::newInstance()
				->setSubject($facture->getNom()." : paiement en ligne")
				->setContentType('text/html')
				->setFrom('noreply@singerfrance.com')
				// ->setTo($facture->getEmail())
				->setTo('manu7772@gmail.com')
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

	private function emailCodePromo($User) {
		// mail
		if(is_object($User)) {
			$templating = $this->get('templating');
			$contenu = $templating->render("AcmeGroupSiteBundle:mail:codepromo.html.twig", array("user" => $User));
			$mailer = $this->get('mailer');
			$message = \Swift_Message::newInstance()
				->setSubject($User->getUsername().", votre code PROMO SINGER sur Made In Me Couture")
				->setContentType('text/html')
				->setFrom('noreply@singerfrance.com')
				// ->setFrom($version['email'])
				// ->setTo('emmanuel@aequation-webdesign.fr')
				->setTo($User->getEmail())
				->setBody($contenu);
			$Umail = $mailer->send($message);
			// pour Admin
			// $message = \Swift_Message::newInstance()
			// 	->setSubject($User->getUsername().", votre code PROMO SINGER sur Made In Me Couture")
			// 	->setContentType('text/html')
			// 	->setFrom('noreply@singerfrance.com')
			// 	// ->setFrom($version['email'])
			// 	// ->setTo('emmanuel@aequation-webdesign.fr')
			// 	->setTo('manu7772@gmail.com')
			// 	->setBody($contenu);
			// $Amail = $mailer->send($message);
		}
	}

}



