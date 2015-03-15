<?php

namespace AcmeGroup\LaboBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use labo\Bundle\TestmanuBundle\Entity\voteArticleBlack as voteArticleBlackBase;

/**
 * voteArticleBlack
 *
 * @ORM\Entity
 * @ORM\Table(name="voteArticleBlack")
 * @ORM\Entity(repositoryClass="AcmeGroup\LaboBundle\Entity\voteArticleBlackRepository")
 */
class voteArticleBlack extends voteArticleBlackBase {

	/**
	 * @var integer
	 *
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;


}
