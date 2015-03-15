<?php

namespace AcmeGroup\LaboBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use labo\Bundle\TestmanuBundle\Entity\bonLivraison as bonLivraisonBase;

/**
 * bonLivraison
 *
 * @ORM\Entity
 * @ORM\Table(name="bonLivraison")
 * @ORM\Entity(repositoryClass="AcmeGroup\LaboBundle\Entity\bonLivraisonRepository")
 */
class bonLivraison extends bonLivraisonBase {

	/**
	 * @var integer
	 *
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;


}
