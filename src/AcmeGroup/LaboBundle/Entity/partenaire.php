<?php

namespace AcmeGroup\LaboBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use labo\Bundle\TestmanuBundle\Entity\partenaire as partenaireBase;

/**
 * partenaire
 *
 * @ORM\Entity
 * @ORM\Table(name="partenaire")
 * @ORM\Entity(repositoryClass="AcmeGroup\LaboBundle\Entity\partenaireRepository")
 */
class partenaire extends partenaireBase {

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


}
