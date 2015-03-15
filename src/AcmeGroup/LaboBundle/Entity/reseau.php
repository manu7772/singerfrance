<?php

namespace AcmeGroup\LaboBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use labo\Bundle\TestmanuBundle\Entity\reseau as reseauBase;

/**
 * reseau
 *
 * @ORM\Entity
 * @ORM\Table(name="reseau")
 * @ORM\Entity(repositoryClass="AcmeGroup\LaboBundle\Entity\reseauRepository")
 */
class reseau extends reseauBase {

	/**
	 * @var integer
	 *
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;


}
