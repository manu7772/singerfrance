<?php

namespace AcmeGroup\LaboBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use labo\Bundle\TestmanuBundle\Entity\panier as panierBase;

/**
 * panier
 *
 * @ORM\Entity
 * @ORM\Table(name="panier")
 * @ORM\Entity(repositoryClass="AcmeGroup\LaboBundle\Entity\panierRepository")
 */
class panier extends panierBase {

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="AcmeGroup\UserBundle\Entity\User", inversedBy="paniers")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 */
	protected $propUser;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="AcmeGroup\LaboBundle\Entity\article")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 */
	protected $article;

}