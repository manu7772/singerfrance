<?php
// src/AcmeGroup/UserBundle/Entity/User.php
 
namespace AcmeGroup\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="User")
 * @ORM\Entity(repositoryClass="AcmeGroup\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser {

	private $modeslivraison;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="AcmeGroup\LaboBundle\Entity\image", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $avatar;

	/**
	* @ORM\Column(name="preferences", type="array", nullable=true)
	*/
	private $preferences;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nom", type="string", length=100, nullable=true, unique=false)
	 */
	private $nom;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="livraison", type="string", length=100, nullable=false, unique=false)
	 */
	private $livraison;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="prenom", type="string", length=100, nullable=true, unique=false)
	 */
	private $prenom;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="adresse", type="text", nullable=true, unique=false)
	 */
	private $adresse;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="cp", type="string", length=10, nullable=true, unique=false)
	 */
	private $cp;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="ville", type="string", length=255, nullable=true, unique=false)
	 */
	private $ville;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="commentaire", type="text", nullable=true, unique=false)
	 */
	private $commentaire;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="annotation", type="text", nullable=true, unique=false)
	 */
	private $annotation;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\voteArticle", mappedBy="user")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $voteArticles;

	/**
	* @var string
	*
	* @ORM\Column(name="adressIp", type="array", nullable=true)
	*/
	private $adressIp;

	/**
	* @var string
	*
	* @ORM\Column(name="autresMails", type="array", nullable=true)
	*/
	private $autresMails;

	/**
	* @var string
	*
	* @ORM\Column(name="tel", type="string", length=15, nullable=true)
	*/
	private $tel;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\panier", mappedBy="propUser", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $paniers;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\image", mappedBy="propUser")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $images;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\video", mappedBy="propUser")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $videos;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\ficheCreative", mappedBy="propUser")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $ficheCreatives;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\article", mappedBy="propUser")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $articles;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\evenement", mappedBy="propUser")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $evenements;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\bonLivraison", mappedBy="client")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $bonLivraisons;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\commande", mappedBy="propUser", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $commandes;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\fichierPdf", mappedBy="propUser")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $fichierPdfs;

	/**
	 * @ORM\OneToMany(targetEntity="AcmeGroup\LaboBundle\Entity\richtext", mappedBy="propUser")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $richtexts;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="idxFactures", type="integer", nullable=false, unique=false)
	 */
	private $idxFactures;

	/**
	 * @var array
	 *
	 * @ORM\ManyToMany(targetEntity="AcmeGroup\LaboBundle\Entity\version")
	 */
	private $versions;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="mactype", type="string", length=255, nullable=true, unique=false)
	 */
	private $mactype;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="macmarque", type="string", length=255, nullable=true, unique=false)
	 */
	private $macmarque;

	/**
	 * @ORM\ManyToOne(targetEntity="AcmeGroup\LaboBundle\Entity\marque")
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	private $marque;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="macnoserie", type="string", length=255, nullable=true, unique=false)
	 */
	private $macnoserie;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="macdateachat", type="datetime", nullable=true, unique=false)
	 */
	private $macdateachat;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AcmeGroup\LaboBundle\Entity\magasin")
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $magasin;

	private $mac;

	public function __construct() {
		parent::__construct();

		$this->macdateachat = null;
		$this->voteArticles = new ArrayCollection();

		$this->adresses = new ArrayCollection();
		$this->images = new ArrayCollection();
		$this->paniers = new ArrayCollection();
		$this->videos = new ArrayCollection();
		$this->ficheCreatives = new ArrayCollection();
		$this->articles = new ArrayCollection();
		$this->evenements = new ArrayCollection();
		$this->fichierPdfs = new ArrayCollection();
		$this->richtexts = new ArrayCollection();
		$this->bonLivraisons = new ArrayCollection();
		$this->commandes = new ArrayCollection();
		$this->factures = new ArrayCollection();
		$this->versions = new ArrayCollection();
		$this->idxFactures = 0;
		$this->macmarque = null;

		$this->modeslivraison = array(
		"poste"		=> "Par voie postale",
		"depot"		=> "Enlèvement en boutique",
		);

		$this->livraison = $this->modeslivraison["poste"];
	}

	public function getModeslivraison() {
		return $this->modeslivraison;
	}

	public function getMac() {
		return $this->mac;
	}
	public function setMac($mac = null) {
		$this->mac = $mac;
		return $this;
	}

	// ADRESSE
	public function isAdressePartielle() {
		// TRUE si au moins un des champs d'adresse est renseigné
		if(($this->livraison === "poste") && ($this->nom !== null || $this->prenom !== null || $this->adresse !== null || $this->cp !== null || $this->ville !== null || $this->commentaire !== null || $this->tel !== null)) {
			return true;
		} else return false;
	}

	public function isAdresseNulle() {
		// TRUE si aucun des champs d'adresse n'est renseigné
		if($this->nom === null && $this->prenom === null && $this->adresse === null && $this->cp === null && $this->ville === null && $this->commentaire === null && $this->tel === null) {
			return true;
		} else return false;
	}

	public function isAdresseComplete() {
		// TRUE si les champs obligatoires mini sont renseignés (adresse valide)
		if($this->nom !== null && $this->adresse !== null && $this->cp !== null && $this->ville !== null) {
			return true;
		} else return false;
	}

	public function isAdresseNotComplete() {
		// TRUE si au moins un des champs obligatoires n'est pas renseigné
		if(($this->livraison === "poste") && ($this->nom === null || $this->adresse === null || $this->cp === null || $this->ville === null)) {
			return true;
		} else return false;
	}

	// MACHINE
	public function isMachinePartielle() {
		// TRUE si aucun des champs machine n'est renseigné
		if($this->mactype !== null || $this->marque !== null || $this->macnoserie !== null || $this->macdateachat !== null) {
			return true;
		} else return false;
	}

	public function isMachineComplete() {
		// TRUE si aucun des champs machine n'est renseigné
		if($this->mactype !== null && $this->marque !== null && $this->macnoserie !== null && $this->macdateachat !== null) {
			return true;
		} else return false;
	}

	public function isMachineNulle() {
		// TRUE si aucun des champs machine n'est renseigné
		if($this->mactype === null && $this->marque === null && $this->macnoserie === null && $this->macdateachat === null) {
			return true;
		} else return false;
	}

	/**
	 * Get id
	 *
	 * @return integer 
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Add preference
	 *
	 * @param $preferences
	 * @return User
	 */
	public function addPreference($preferences) {
		$this->preferences[] = $preferences;
		return $this;
	}

	/**
	 * Remove preference
	 *
	 * @param $key
	 */
	public function removePreference($preferences) {
		$this->preferences->removeElement($preferences);
	}

	/**
	 * Get preferences
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getPreferences() {
		return $this->preferences;
	}

	/**
	 * Set adresse
	 *
	 * @param string $adresse
	 * @return User
	 */
	public function setAdresse($adresse) {
		$this->adresse = $adresse;
	
		return $this;
	}

	/**
	 * Get adresse
	 *
	 * @return string 
	 */
	public function getAdresse() {
		return $this->adresse;
	}

	/**
	 * Set nom
	 *
	 * @param string $nom
	 * @return User
	 */
	public function setNom($nom) {
		$this->nom = $nom;
	
		return $this;
	}

	/**
	 * Get nom
	 *
	 * @return string 
	 */
	public function getNom() {
		return $this->nom;
	}

	/**
	 * Set livraison
	 *
	 * @param string $livraison
	 * @return User
	 */
	public function setLivraison($livraison) {
		$this->modeslivraison = array(
		"poste"		=> "Par voie postale",
		"depot"		=> "Enlèvement en boutique",
		);
		if((!array_key_exists($this->livraison, $this->modeslivraison)) || ($this->magasin === null)) {
			$this->livraison = "poste";
		} else {
			$this->livraison = $livraison;
		}
		return $this;
	}

	/**
	 * Get livraison
	 *
	 * @return string 
	 */
	public function getLivraison() {
		$this->setLivraison($this->livraison);
		return $this->livraison;
	}

	public function getLivraisonTxt() {
		$this->modeslivraison = array(
		"poste"		=> "Par voie postale",
		"depot"		=> "Enlèvement en boutique",
		);
		return $this->modeslivraison[$this->getLivraison()];
	}

	/**
	 * Set prenom
	 *
	 * @param string $prenom
	 * @return User
	 */
	public function setPrenom($prenom) {
		$this->prenom = $prenom;
	
		return $this;
	}

	/**
	 * Get prenom
	 *
	 * @return string 
	 */
	public function getPrenom() {
		return $this->prenom;
	}

	/**
	 * Set cp
	 *
	 * @param string $cp
	 * @return User
	 */
	public function setCp($cp) {
		$this->cp = $cp;
	
		return $this;
	}

	/**
	 * Get cp
	 *
	 * @return string 
	 */
	public function getCp() {
		return $this->cp;
	}

	/**
	 * Set ville
	 *
	 * @param string $ville
	 * @return User
	 */
	public function setVille($ville) {
		$this->ville = $ville;
	
		return $this;
	}

	/**
	 * Get ville
	 *
	 * @return string 
	 */
	public function getVille() {
		return $this->ville;
	}

	/**
	 * Set commentaire
	 *
	 * @param string $commentaire
	 * @return User
	 */
	public function setCommentaire($commentaire) {
		$this->commentaire = $commentaire;
	
		return $this;
	}

	/**
	 * Get commentaire
	 *
	 * @return string 
	 */
	public function getCommentaire() {
		return $this->commentaire;
	}

	/**
	 * Get annotation
	 *
	 * @return string
	 */
	public function getAnnotation() {
		return $this->annotation;
	}

	/**
	 * Set annotation
	 *
	 * @param string $annotation
	 * @return User
	 */
	public function setAnnotation($annotation) {
		$this->annotation = $annotation;
	
		return $this;
	}

	/**
	 * Set adressIp
	 *
	 * @param array $adressIp
	 * @return User
	 */
	public function setAdressIp($adressIp) {
		$this->adressIp = $adressIp;
	
		return $this;
	}

	/**
	 * Get adressIp
	 *
	 * @return array 
	 */
	public function getAdressIp() {
		return $this->adressIp;
	}

	/**
	 * Set tel
	 *
	 * @param string $tel
	 * @return User
	 */
	public function setTel($tel) {
		$this->tel = $tel;
	
		return $this;
	}

	/**
	 * Get tel
	 *
	 * @return string 
	 */
	public function getTel() {
		return $this->tel;
	}

	/**
	 * Set autresTels
	 *
	 * @param array $autresTels
	 * @return User
	 */
	public function setAutresTels($autresTels) {
		$this->autresTels = $autresTels;
	
		return $this;
	}

	/**
	 * Get autresTels
	 *
	 * @return array 
	 */
	public function getAutresTels() {
		return $this->autresTels;
	}

	/**
	 * Add panier
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\panier $panier
	 * @return User
	 */
	public function addPanier(\AcmeGroup\LaboBundle\Entity\panier $panier) {
		$this->paniers[] = $panier;
		return $this;
	}

	/**
	 * Remove panier
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\panier $panier
	 */
	public function removePanier(\AcmeGroup\LaboBundle\Entity\panier $panier) {
		$this->paniers->removeElement($panier);
	}

	/**
	 * Get paniers
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getPaniers() {
		return $this->paniers;
	}

	/**
	 * Add images
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\image $images
	 * @return User
	 */
	public function addImage(\AcmeGroup\LaboBundle\Entity\image $images) {
		$this->images[] = $images;
		return $this;
	}

	/**
	 * Remove images
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\image $images
	 */
	public function removeImage(\AcmeGroup\LaboBundle\Entity\image $images) {
		$this->images->removeElement($images);
		$images->setPropUser(null); // relation facultative !!! (nullable = true)
	}

	/**
	 * Get images
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getImages() {
		return $this->images;
	}

	/**
	 * Add videos
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\video $videos
	 * @return User
	 */
	public function addVideo(\AcmeGroup\LaboBundle\Entity\video $videos) {
		$this->videos[] = $videos;
		$videos->setPropUser($this); // ajout pour relation bidirectionnelle
		return $this;
	}

	/**
	 * Remove videos
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\video $videos
	 */
	public function removeVideo(\AcmeGroup\LaboBundle\Entity\video $videos) {
		$this->videos->removeElement($videos);
		$videos->setPropUser(null); // relation facultative !!! (nullable = true)
	}

	/**
	 * Get videos
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getVideos() {
		return $this->videos;
	}

	/**
	 * Add articles
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\article $articles
	 * @return User
	 */
	public function addArticle(\AcmeGroup\LaboBundle\Entity\article $articles) {
		$this->articles[] = $articles;
		$articles->setPropUser($this); // ajout pour relation bidirectionnelle
		return $this;
	}

	/**
	 * Remove articles
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\article $articles
	 */
	public function removeArticle(\AcmeGroup\LaboBundle\Entity\article $articles) {
		$this->articles->removeElement($articles);
		$articles->setPropUser(null); // relation facultative !!! (nullable = true)
	}

	/**
	 * Get articles
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getArticles() {
		return $this->articles;
	}

	/**
	 * Add evenements
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\evenement $evenements
	 * @return User
	 */
	public function addEvenement(\AcmeGroup\LaboBundle\Entity\evenement $evenements) {
		$this->evenements[] = $evenements;
		$evenements->setPropUser($this); // ajout pour relation bidirectionnelle
		return $this;
	}

	/**
	 * Remove evenements
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\evenement $evenements
	 */
	public function removeEvenement(\AcmeGroup\LaboBundle\Entity\evenement $evenements) {
		$this->evenements->removeElement($evenements);
		$evenements->setPropUser(null); // relation facultative !!! (nullable = true)
	}

	/**
	 * Get evenements
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getEvenements() {
		return $this->evenements;
	}

	/**
	 * Add ficheCreative
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\ficheCreative $ficheCreative
	 * @return User
	 */
	public function addFicheCreative(\AcmeGroup\LaboBundle\Entity\ficheCreative $ficheCreative) {
		$this->ficheCreatives[] = $ficheCreative;
		$ficheCreative->setPropUser($this); // ajout pour relation bidirectionnelle
		return $this;
	}

	/**
	 * Remove ficheCreative
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\ficheCreative $ficheCreative
	 */
	public function removeFicheCreative(\AcmeGroup\LaboBundle\Entity\ficheCreative $ficheCreative) {
		$this->ficheCreatives->removeElement($ficheCreative);
		$ficheCreative->setPropUser(null); // relation facultative !!! (nullable = true)
	}

	/**
	 * Get ficheCreatives
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getFicheCreatives() {
		return $this->ficheCreatives;
	}

	/**
	 * Add bonLivraisons
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\bonLivraison $bonLivraisons
	 * @return User
	 */
	public function addBonLivraison(\AcmeGroup\LaboBundle\Entity\bonLivraison $bonLivraisons) {
		$this->bonLivraisons[] = $bonLivraisons;
		$bonLivraisons->setPropUser($this); // ajout pour relation bidirectionnelle
		return $this;
	}

	/**
	 * Remove bonLivraisons
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\bonLivraison $bonLivraisons
	 */
	public function removeBonLivraison(\AcmeGroup\LaboBundle\Entity\bonLivraison $bonLivraisons) {
		$this->bonLivraisons->removeElement($bonLivraisons);
		$bonLivraisons->setPropUser(null); // relation facultative !!! (nullable = true)
	}

	/**
	 * Get bonLivraisons
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getBonLivraisons() {
		return $this->bonLivraisons;
	}

	/**
	 * Add commandes
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\commande $commandes
	 * @return User
	 */
	public function addCommande(\AcmeGroup\LaboBundle\Entity\commande $commandes) {
		$this->commandes[] = $commandes;
		$commandes->setPropUser($this); // ajout pour relation bidirectionnelle
		return $this;
	}

	/**
	 * Remove commandes
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\commande $commandes
	 */
	public function removeCommande(\AcmeGroup\LaboBundle\Entity\commande $commandes) {
		$this->commandes->removeElement($commandes);
		$commandes->setPropUser(null); // relation facultative !!! (nullable = true)
	}

	/**
	 * Get commandes
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getCommandes() {
		return $this->commandes;
	}

	/**
	 * Add fichierPdfs
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\fichierPdf $fichierPdfs
	 * @return User
	 */
	public function addFichierPdf(\AcmeGroup\LaboBundle\Entity\fichierPdf $fichierPdfs) {
		$this->fichierPdfs[] = $fichierPdfs;
		$fichierPdfs->setPropUser($this); // ajout pour relation bidirectionnelle
		return $this;
	}

	/**
	 * Remove fichierPdfs
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\fichierPdf $fichierPdfs
	 */
	public function removeFichierPdf(\AcmeGroup\LaboBundle\Entity\fichierPdf $fichierPdfs) {
		$this->fichierPdfs->removeElement($fichierPdfs);
		$fichierPdfs->setPropUser(null); // relation facultative !!! (nullable = true)
	}

	/**
	 * Get fichierPdfs
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getFichierPdfs() {
		return $this->fichierPdfs;
	}

	/**
	 * Add richtexts
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\richtext $richtexts
	 * @return User
	 */
	public function addRichtext(\AcmeGroup\LaboBundle\Entity\richtext $richtexts) {
		$this->richtexts[] = $richtexts;
		$richtexts->setPropUser($this); // ajout pour relation bidirectionnelle
		return $this;
	}

	/**
	 * Remove richtexts
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\richtext $richtexts
	 */
	public function removeRichtext(\AcmeGroup\LaboBundle\Entity\richtext $richtexts) {
		$this->richtexts->removeElement($richtexts);
		$richtexts->setPropUser(null); // relation facultative !!! (nullable = true)
	}

	/**
	 * Get richtexts
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getRichtexts() {
		return $this->richtexts;
	}

	/**
	 * Set idxFactures
	 *
	 * @param integer $idxFactures
	 * @return User
	 */
	public function setIdxFactures($idxFactures) {
		$this->idxFactures = $idxFactures;
	
		return $this;
	}

	/**
	 * incrementeidxFactures
	 *
	 * @return User
	 */
	public function incrementeIdxFactures() {
		$this->idxFactures++;
	
		return $this;
	}

	/**
	 * Get idxFactures
	 *
	 * @return integer 
	 */
	public function getIdxFactures() {
		return $this->idxFactures;
	}

	/**
	 * Add versions
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\version $versions
	 * @return User
	 */
	public function addVersion(\AcmeGroup\LaboBundle\Entity\version $versions)
	{
		$this->versions[] = $versions;
	
		return $this;
	}

	/**
	 * Remove versions
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\version $versions
	 */
	public function removeVersion(\AcmeGroup\LaboBundle\Entity\version $versions)
	{
		$this->versions->removeElement($versions);
	}

	/**
	 * Get versions
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getVersions()
	{
		return $this->versions;
	}

    /**
     * Set avatar
     *
     * @param \AcmeGroup\LaboBundle\Entity\image $avatar
     * @return User
     */
    public function setAvatar(\AcmeGroup\LaboBundle\Entity\image $avatar)
    {
        $this->avatar = $avatar;
    
        return $this;
    }

    /**
     * Get avatar
     *
     * @return \AcmeGroup\LaboBundle\Entity\image 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

	/**
	 * Add voteArticle
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\voteArticle $voteArticle
	 * @return User
	 */
	public function addVoteArticle(\AcmeGroup\LaboBundle\Entity\voteArticle $voteArticle) {
		$this->voteArticles[] = $voteArticle;
	
		return $this;
	}

	/**
	 * Remove voteArticle
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\voteArticle $voteArticle
	 */
	public function removeVoteArticle(\AcmeGroup\LaboBundle\Entity\voteArticle $voteArticle) {
		$this->voteArticles->removeElement($voteArticle);
	}

	/**
	 * Get voteArticles
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getVoteArticles() {
		return $this->voteArticles;
	}

	/**
	 * Set mactype
	 *
	 * @param string $mactype
	 * @return User
	 */
	public function setMactype($mactype) {
		$this->mactype = $mactype;
	
		return $this;
	}

	/**
	 * Get mactype
	 *
	 * @return string 
	 */
	public function getMactype() {
		return $this->mactype;
	}

	/**
	 * Set macmarque
	 *
	 * @param string $macmarque
	 * @return User
	 */
	public function setMacmarque($macmarque) {
		$this->macmarque = $macmarque;
	
		return $this;
	}

	/**
	 * Get macmarque
	 *
	 * @return string 
	 */
	public function getMacmarque() {
		return $this->macmarque;
	}

	/**
	 * Set marque
	 *
	 * @param AcmeGroup\LaboBundle\Entity\marque $marque
	 * @return User
	 */
	public function setMarque($marque = null) {
		$this->marque = $marque;
	
		return $this;
	}

	/**
	 * Get marque
	 *
	 * @return string 
	 */
	public function getMarque() {
		return $this->marque;
	}

	/**
	 * Set macnoserie
	 *
	 * @param string $macnoserie
	 * @return User
	 */
	public function setMacnoserie($macnoserie) {
		$this->macnoserie = $macnoserie;
	
		return $this;
	}

	/**
	 * Get macnoserie
	 *
	 * @return string 
	 */
	public function getMacnoserie() {
		return $this->macnoserie;
	}

	/**
	 * Set macdateachat
	 *
	 * @param \DateTime $macdateachat
	 * @return User
	 */
	public function setMacdateachat($macdateachat) {
		$this->macdateachat = $macdateachat;
	
		return $this;
	}

	/**
	 * Get macdateachat
	 *
	 * @return \DateTime 
	 */
	public function getMacdateachat() {
		return $this->macdateachat;
	}

	/**
	 * Set magasin
	 *
	 * @param \AcmeGroup\LaboBundle\Entity\magasin $magasin
	 * @return User
	 */
	public function setMagasin($magasin) {
		$this->magasin = $magasin;
	
		return $this;
	}

	/**
	 * Get magasin
	 *
	 * @return \AcmeGroup\LaboBundle\Entity\magasin 
	 */
	public function getMagasin() {
		return $this->magasin;
	}


}