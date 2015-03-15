<?php

namespace AcmeGroup\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use labo\Bundle\TestmanuBundle\services\aetools\aeReponse;
// use app\Resources\classes\html2pdf\HTML2PDF;

class operationsController extends Controller {



	public function getCSVcontactsWibilongAction($option = null) {
		$date = new \Datetime();
		$AAAA = $this->fillNumber($date->format("Y"), 4);
		$MM = $this->fillNumber($date->format("m"), 2);
		$JJ = $this->fillNumber($date->format("d"), 2);
		$nomfichier = "CCV_".$AAAA.'_'.$MM.'_'.$JJ.'.csv';
		$dateDuJour = $date->format("d/m/Y");
		$champs = array(
			"ProductId" => "getTypemachine|getNumserie",
			"OrderId" => "getId",
			"OrderDate" => $dateDuJour,
			"CustomerId" => "getId",
			"CustomerName" => "getUsername",
			"CustomerFirstName" => ".",
			"CustomerEmail" => "getEmail",
			"CustomerTitle" => "madam",
			"CustomerBirthDate" => "01/01/1970",
			"CustomerZipCode" => "getCp",
			"MerchantName" => "Singer France",
			);
		$glue = " - ";
		$titles = array();
		foreach ($champs as $key => $value) {
			$titles[] = $key;
		}
		// récupération des marques
		$entity = $this->get("acmeGroup.entities")->defineEntity('marque');
		$mark = $entity->getRepo()->findAll();
		$marques = array();
		foreach ($mark as $marque) {
			$marques[] = strtolower($marque->getNom());
		}
		// récupération des Users
		$entity = $this->get("acmeGroup.entities")->defineEntity('tempuser');
		$contacts = $entity->getRepo()->findAll();
		$data = array();
		// tri des données
		$i = 0;
		foreach($contacts as $contact) {
			$ligneOK = true;
			// exception option = "Singer"
			if($option !== null && in_array($option, $marques)) {
				if($contact->getMarque() !== null) {
					$marque = strtolower($contact->getMarque()->getNom());
					if($option === $marque) {
						$champs["MerchantName"] = $contact->getMarque()->getNom();
						$ligneOK = true;
					} else $ligneOK = false;
				} else $ligneOK = false;
			}
			if($ligneOK === true) foreach ($champs as $titre => $champ) {
				if($champ !== false) {
					switch (substr($champ, 0, 3)) {
						case "get":
							$chx = explode('|', $champ);
							if(count($chx) < 2) $champ = array($champ);
							$ctx = array();
							foreach($chx as $chxxx) {
								$val = $contact->$chxxx()."";
								if(strlen($val) > 0) $ctx[] = $val;
							}
							$data[$i][$titre] = implode($glue, $ctx);
							break;
						default:
							$data[$i][$titre] = $champ."";
							break;
					}
				} else $data[$i][$titre] = ""; // $champ = false
			}
			$i++;
		}
		// enregistrement de la ligne de tête
		$handle = fopen('php://memory', 'r+');
		fputcsv($handle, $titles, "|");
		// enregistrement des contacts
		foreach($data as $d) fputcsv($handle, $d, "|");
		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);
		// envoi du fichier en téléchargement
		return new Response($content, 200, array(
			'Content-Type' => 'application/force-download',
			'Content-Disposition' => 'attachment; filename="'.$nomfichier.'"'
		));
	}

	private function formatTxtWibilong($txt) {
		$txt = strip_tags(str_replace("|", "&#16;", $txt));
		return $txt;
	}

	/**
	 * Ajoute des "0" avant un chiffre pour totaliser un nombre de caractères égale à $q
	 * @param integer/string $num - nombre
	 * @param integer $q - nombre de caractères total voulu
	 * @param string $fill - caractère(s) de remplissage
	 * @return string
	 */
	private function fillNumber($num, $q, $fill = "0") {
		$num = $num."";
		if(strlen($num) < $q) {
			while (strlen($num) <= $q) {
				$num = $fill.$num;
			}
		}
		return $num;
	}

	public function getCSVcontactsMimcAction() {
		$champs = array(
			"Id",
			"Date inscription",
			"Nom utilisateur",
			"Email",
			"Type Machine",
			"Marque",
			"Num série",
			"Date d'achat",
			);
		$date = new \Datetime();
		$nomfichier = "export-contacts-mimc-".$date->format("Y_m_d").'.csv';
		$entity = $this->get("acmeGroup.entities")->defineEntity('tempuser');
		$contacts = $entity->getRepo()->findAll();
		$data = array();
		// tri des données
		$i = 1;
		foreach($contacts as $contact) {
			$data[$i]["Id"]						= $contact->getId();
			$data[$i]["Date inscription"]		= $contact->getDateCreation()->format('d/m/Y');
			$data[$i]["Nom utilisateur"]		= $contact->getUsername();
			$data[$i]["Email"]					= $contact->getEmail();
			// machine
			$data[$i]["Type Machine"]			= $contact->getTypemachine();
			if(is_object($contact->getMarque()))
				$data[$i]["Marque"]				= $contact->getMarque()->getNom();
			else
				$data[$i]["Marque"]				= "";
			$data[$i]["Num série"]				= $contact->getNumserie();
			if(is_object($contact->getDateachat()))
				$data[$i]["Date d'achat"]			= $contact->getDateachat()->format('d/m/Y');
			else
				$data[$i]["Date d'achat"]			= "";
			$i++;
		}
		// enregistrement des lignes
		$handle = fopen('php://memory', 'r+');
		fputcsv($handle, $champs, "|");
		foreach($data as $d) fputcsv($handle, $d, "|");
		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);
		// envoi du fichier en téléchargement
		return new Response($content, 200, array(
			'Content-Type' => 'application/force-download',
			'Content-Disposition' => 'attachment; filename="'.$nomfichier.'"'
		));
	}

	public function getCSVarticlesAction($type = 'articles') {
		$listypes = array(
			"articles"		=> "PRODUCTS_",
			"accessoires"	=> "ACCESSORIES_",
			);
		if(!array_key_exists($type, $listypes)) $type = "articles";
		$date = new \Datetime();
		$nomfichier = $listypes[$type].$date->format("Y_m_d").'.csv';
		$articles = $this->get("acmeGroup.article")->getRepo()->findAll();
		$data = array();
		$i = 1;
		foreach($articles as $article) {
			$do = false;
			$cat = $article->getCategories();
			$levels = $this->getLevels($cat);
			if ($type == 'accessoires') {
				$do = false;
				// accessoires
				foreach($cat as $c) {
					if($c->getSlug() == 'accessoires') {
						$do = true;
					}
				}
				if(strtolower($article->getStatut()->getNom()) != "actif") $do = false;
				if($do === true) {
					$data[$i]["ProductAcesoryId"] 			= $article->getId();
					$data[$i]["ProductAcesoryName"] 		= $article->getNom();
					$data[$i]["ProductAcesoryBrandName"] 	= $article->getMarque()->getNom();
					$data[$i]["ProductAcesoryLink"] 		= $this->generateUrl("acme_site_fichearticle", array("articleSlug"=>$article->getSlug()), true);
					$data[$i]["ProductAcesoryPictureUrl"] 	= $this->getRequest()->getUriForPath("/images/tn200/".$article->getImagePpale()->getFichierNom());
					$data[$i]["ProductAcesoryPrice"] 		= number_format($article->getPrix(), 2, ",", "")."";
					$data[$i]["ProductParentId"] 			= "";
					$i++;
				}
			} else if ($type == 'articles') {
				$do = true;
				// articles
				foreach($cat as $c) {
					if($c->getSlug() == 'accessoires') {
						$do = false;
					}
				}
				if(strtolower($article->getStatut()->getNom()) != "actif") $do = false;
				if($do === true) {
					$data[$i]["FUPID"]						= $article->getId();
					$data[$i]["ProductName"] 				= $article->getNom();
					$data[$i]["BrandName"] 					= $article->getMarque()->getNom();
					$data[$i]["MerchantProductUnivers"] 	= $levels["haut"];
					$data[$i]["MerchantProductCategory"] 	= $levels["fin"];
					$data[$i]["MerchantProductMarket"] 		= $article->getMarque()->getNom();
					$data[$i]["ProductLink"] 				= $this->generateUrl("acme_site_fichearticle", array("articleSlug"=>$article->getSlug()), true);
					$data[$i]["ProductPrice"] 				= number_format($article->getPrix(), 2, ",", "")."";
					$data[$i]["ProductEanCode"] 			= "";
					$data[$i]["ProductLongDescription"] 	= $article->getDescriptif();
					$data[$i]["ProductShortDescription"] 	= $article->getAccroche();
					$data[$i]["PictureMediumUrl"] 			= $this->getRequest()->getUriForPath("/images/tn200/".$article->getImagePpale()->getFichierNom());
					$i++;
				}
			}
		}
		// ecriture de la ligne des libellés
		$data2 = array();
		foreach($data[1] as $lib => $dat) $data2[0][$lib] = $lib;
		// suppression des caractères "|" (pipe) éventuels, car ils servent de balise de séparation dans le fichier CSV
		foreach($data as $nom1 => $d1) foreach($d1 as $nom2 => $d2) $data2[$nom1][$nom2] = str_replace("|", "&#16;", $data[$nom1][$nom2]);
		// enregistrement des lignes
		$handle = fopen('php://memory', 'r+');
		foreach($data2 as $d) fputcsv($handle, $d, "|");
		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);
		// envoi du fichier en téléchargement
		return new Response($content, 200, array(
			'Content-Type' => 'application/force-download',
			'Content-Disposition' => 'attachment; filename="'.$nomfichier.'"'
		));
	}

	/**
	 * confirmLivraisonAction
	 * Lien pour confirmation de livraison par le client (le lien se trouve dans son mail)
	 * @param string $factureRef (référence de la facture)
	 */
	public function confirmLivraisonAction($factureRef = null) {
		$facture = $this->get("AcmeGroup.facture");
		$f = $facture->getRepo()->findByReference(urldecode($factureRef));
		if(count($f) == 1) {
			$data["facture"] = $f[0];
			$data["facture"]->setStade(2);
			$facture->getEm()->flush();
		} else {
			$data["facture"] = null;
		}
		return $this->render($this->verifVersionPage("pageConfirmLivraison"), $data);
	}

	/**
	 * loadRichtextAction
	 * Lien pour téléchargement des textes (richtext) du site
	 */
	public function loadRichtextAction() {
		$el = "\n";
		$date = new \Datetime();
		$nomfichier = "sitetextes_".$date->format("Y_m_d").'.txt';
		// récupération des textes
		$textes = $this->get("AcmeGroup.entities")->defineEntity('richtext')->getRepo()->findAll();
		// écriture en mémoire
		$handle = fopen('php://memory', 'r+');
		foreach($textes as $texte) {
			fwrite($handle, "ID : ".$texte->getId().$el);
			fwrite($handle, "Nom : ".$texte->getNom().$el);
			// fwrite($handle, "Résumé :".$el.preg_replace_callback('/&#([0-9a-fx]+);/mi', 'this->replace_num_entity', strip_tags(html_entity_decode($texte->getResume()))).$el);
			fwrite($handle, "Résumé :".$el.$this->decodeTxt($texte->getResume()).$el);
			fwrite($handle, "----------------------------------------------------".$el);
			// fwrite($handle, "Contenu du texte :".$el.preg_replace_callback('/&#([0-9a-fx]+);/mi', 'this->replace_num_entity', strip_tags(html_entity_decode($texte->getTexte()))).$el);
			fwrite($handle, "Contenu du texte :".$el.$this->decodeTxt($texte->getTexte()).$el);
			fwrite($handle, $el."*****************************************************************************************".$el.$el);
		}
		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);
		// envoi du fichier en téléchargement
		return new Response($content, 200, array(
			'Content-Type' => 'application/force-download',
			'Content-Disposition' => 'attachment; filename="'.$nomfichier.'"'
		));
	}
	private function decodeTxt($t) {
		$t = html_entity_decode(strip_tags($t));
		return str_replace("&#39;", "'", $t);
	}

	public function loadConcessionnairesAction($option = null) {
		$date = new \Datetime();
		$nomfichier = "concessionnaires_".$date->format("Y_m_d").'.csv';
		$data = array();
		switch ($option) {
			case 'withmail':
				// concessionnaires sans email
				// $concessionnaires = $this->get("acmeGroup.magasin")->findMagSansMail();
				$concessionnaires = $this->get("acmeGroup.magasin")->getRepo()->findAll();
				$cc = array();
				foreach($concessionnaires as $key => $concess) {
					if(($concess->getEmail() !== null) && (trim($concess->getEmail()) !== "")) $cc[$key] = $concess;
				}
				$concessionnaires = array();
				$concessionnaires = $cc;
				$cc = array();unset($cc);
				break;
			case 'nomail':
				// concessionnaires sans email
				// $concessionnaires = $this->get("acmeGroup.magasin")->findMagSansMail();
				$concessionnaires = $this->get("acmeGroup.magasin")->getRepo()->findAll();
				$cc = array();
				foreach($concessionnaires as $key => $concess) {
					if(($concess->getEmail() === null) || (trim($concess->getEmail()) === "")) $cc[$key] = $concess;
				}
				$concessionnaires = array();
				$concessionnaires = $cc;
				$cc = array();unset($cc);
				break;
			default:
				// tous
				$concessionnaires = $this->get("acmeGroup.magasin")->getRepo()->findAll();
				break;
		}
		foreach($concessionnaires as $key => $magasin) {
			$data[$key]['id'] = $magasin->getId();
			$data[$key]['nommagasin'] = $magasin->getNommagasin();
			$data[$key]['raisonsociale'] = $magasin->getRaisonsociale();
			$data[$key]['responsable'] = $magasin->getResponsable();
			$data[$key]['email'] = $magasin->getEmail();
			$data[$key]['telephone'] = $magasin->getTelephone();
			$data[$key]['mobile'] = $magasin->getTelmobile();
			$data[$key]['adresse'] = $magasin->getAdresse()." ".$magasin->getCp()." ".$magasin->getVille();
			$data[$key]['departement'] = $magasin->getDepartement();
			$data[$key]['siteurl'] = $magasin->getSiteurl();
			$data[$key]['code'] = $magasin->getCode();
			$data[$key]['secteur'] = $magasin->getSecteur();
			$data[$key]['magvoisin1'] = $magasin->getMagvoisin1();
			$data[$key]['magvoisin2'] = $magasin->getMagvoisin2();
			$data[$key]['titreSeo'] = $magasin->getTitreSeo();
			$data[$key]['descSeo'] = $magasin->getDescSeo();
			$data[$key]['metakey'] = $magasin->getMetakey();
			$data[$key]['typemagasin'] = $magasin->getTypemagasin();
			$data[$key]['posithoraire'] = $magasin->getPosithoraire();
			$data[$key]['item'] = $magasin->getItem();
			if($magasin->getPlusVisible() === true)
				$data[$key]['plus visible'] = "oui";
				else $data[$key]['plus visible'] = "non";
			if($magasin->getImage() !== null)
				$data[$key]['image vitrine'] = "oui";
				else $data[$key]['image vitrine'] = "non";
			$data[$key]['statut'] = $magasin->getStatut()->getNom();
		}
		if(count($data) > 0) {
			// ecriture de la ligne des libellés
			$data2 = array();
			reset($data);
			foreach(current($data) as $lib => $dat) $data2[0][$lib] = $lib;
			// suppression des caractères "|" (pipe) éventuels, car ils servent de balise de séparation dans le fichier CSV
			$i = 1;
			foreach($data as $nom1 => $d1) {
				foreach($d1 as $nom2 => $d2) {
					$data2[$i][$nom2] = str_replace("|", "&#16;", $d2);
				}
				$i++;
			}
			// enregistrement des lignes
			$handle = fopen('php://memory', 'r+');
			foreach($data2 as $d) fputcsv($handle, $d, "|");
			rewind($handle);
			$content = stream_get_contents($handle);
			fclose($handle);
		} else {
			// aucune donnée en retour
			$handle = fopen('php://memory', 'r+');
			fputcsv($handle, array("Aucune donnée trouvée"), "|");
			rewind($handle);
			$content = stream_get_contents($handle);
			fclose($handle);
		}
		// envoi du fichier en téléchargement
		return new Response($content, 200, array(
			'Content-Type' => 'application/force-download',
			'Content-Disposition' => 'attachment; filename="'.$nomfichier.'"'
		));
	}

	//////////////////////////
	// Fonctions diverses
	//////////////////////////

	private function getLevels($cat) {
		$max = 0;
		$min = 100;
		foreach($cat as $c) {
			if($c->getId() > $max) {
				$max = $c->getId();
				$fin = $c->getNom();
			}
			if($c->getId() < $min) {
				$min = $c->getId();
				$haut = $c->getNom();
			}
		}
		return array("fin" => $fin, "haut" => $haut);
	}

	public function getAssetUrl($path, $packageName = null) {
		// $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
		// return $baseurl.$this->container->get('templating.helper.assets')->getUrl($path, $packageName);
		return $this->getRequest()->getUriForPath($path);
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

	private function replace_num_entity($ord) {
		$ord = $ord[1];
		if (preg_match('/^x([0-9a-f]+)$/i', $ord, $match)) {
			$ord = hexdec($match[1]);
		} else {
			$ord = intval($ord);
		}
		$no_bytes = 0;
		$byte = array();
		if ($ord < 128) {
			return chr($ord);
		} elseif ($ord < 2048) {
			$no_bytes = 2;
		} elseif ($ord < 65536) {
			$no_bytes = 3;
		} elseif ($ord < 1114112) {
			$no_bytes = 4;
		} else {
			return;
		}

		switch($no_bytes) {
			case 2: {
				$prefix = array(31, 192);
				break;
			}
			case 3: {
				$prefix = array(15, 224);
				break;
			}
			case 4: {
				$prefix = array(7, 240);
			}
		}

		for ($i = 0; $i < $no_bytes; $i++) {
			$byte[$no_bytes - $i - 1] = (($ord & (63 * pow(2, 6 * $i))) / pow(2, 6 * $i)) & 63 | 128;
		}
		$byte[0] = ($byte[0] & $prefix[0]) | $prefix[1];
		$ret = '';
		for ($i = 0; $i < $no_bytes; $i++) {
			$ret .= chr($byte[$i]);
		}
		return $ret;
	}

}



