<?php

namespace AcmeGroup\LaboBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use labo\Bundle\TestmanuBundle\Entity\fichierPdf as fichierPdfBase;

/**
 * fichierPdf
 *
 * @ORM\Entity
 * @ORM\Table(name="fichierPdf")
 * @ORM\Entity(repositoryClass="AcmeGroup\LaboBundle\Entity\fichierPdfRepository")
 */
class fichierPdf extends fichierPdfBase {

	/**
	 * @var integer
	 *
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;


}
