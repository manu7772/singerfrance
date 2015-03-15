<?php
// src/AcmeGroup/SiteBundle/Classes/menuOptions.php

namespace AcmeGroup\SiteBundle\Classes;

class menuOptions {

	protected $routesForCategories = array('acme_site_categories', 'acme_site_univers', 'acme_site_atelier_theme');

	public function __construct($container) {
		$this->container = $container;
	}

	public function getOptions() {
		$router = $this->container->get('router');
		$categorie = $this->container->get('AcmeGroup.categorie');
		return array(
			'decorate' => true,
			'rootOpen' => function($tree) {
				$lvl = $tree[0]['lvl'];
				if($lvl == 0) return null;
				$ulName = array("", "menu_haut", "secondLevel", "thirdLevel");
				if($lvl == 1) $type = "id"; else $type = "class";
				return '<ul '.$type.'="'.$ulName[$lvl].'" title="'.$tree[0]['descriptif'].'">';
			},
			'rootClose' => function($tree) {
				return '</ul>';
			},
			'childOpen' => function($child) {
				// echo("<pre><span style='color:red;'>".$child['lvl']."********************************************</span>");
				// var_dump($child);
				// echo("<br /><br /></pre>");
				if($child['lvl'] == 0) return null;
				$liName = array("", "menu_p", "titre_secondLevel", "sous_menu3");
				$li = $liName[$child['lvl']];
				if($child['lft'] == 2) $li = "first";
				return '<li class="'.$li.'">';
			},
			'childClose' => function($child) {
				if($child['lvl'] == 0) return null;
				return '</li>';
			},
			'nodeDecorator' => function($node) use ($router, $categorie) {
				// N'affiche pas le premier niveau "catégories", ça ne nous intéresse pas !
				if($node['lvl'] == 0) return null;
				// Récupération des paramètres de l'url
				$cat = $categorie->getRepo()->findBySlug($node["slug"]);
				if(count($cat) > 0) {
					$pagewebSlug = $cat[0]->getPage()->getSlug();
					$routeParam = $cat[0]->getPage()->getRoute();
					$Url = $router->generate($routeParam, array("pagewebSlug" => $pagewebSlug, "categorieSlug" => $node['slug']));
				} else $Url = $router->generate("acme_site_home");
				// Génération de l'url
				return '<a href="'.$Url.'" title="'.$node['descriptif'].'">'.mb_strtoupper($node["nom"], 'UTF-8').'</a>';
			}
		);
	}
}

?>