<?php

namespace AcmeGroup\LaboBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use labo\Bundle\TestmanuBundle\Entity\ficheCreative as ficheCreativeBase;

/**
 * ficheCreative
 *
 * @ORM\Entity
 * @ORM\Table(name="ficheCreative")
 * @ORM\Entity(repositoryClass="AcmeGroup\LaboBundle\Entity\ficheCreativeRepository")
 */
class ficheCreative extends ficheCreativeBase {

	/**
	 * @var integer
	 *
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;


}
