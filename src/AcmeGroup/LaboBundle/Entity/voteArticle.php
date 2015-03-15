<?php

namespace AcmeGroup\LaboBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use labo\Bundle\TestmanuBundle\Entity\voteArticle as voteArticleBase;

/**
 * voteArticle
 *
 * @ORM\Entity
 * @ORM\Table(name="voteArticle")
 * @ORM\Entity(repositoryClass="AcmeGroup\LaboBundle\Entity\voteArticleRepository")
 */
class voteArticle extends voteArticleBase {

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="AcmeGroup\UserBundle\Entity\User", inversedBy="voteArticles", cascade={"persist", "remove"})
	 */
	protected $user;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="AcmeGroup\LaboBundle\Entity\article", inversedBy="voteUsers", cascade={"persist", "remove"})
	 */
	protected $article;

}