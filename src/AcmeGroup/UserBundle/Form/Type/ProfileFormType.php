<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmeGroup\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraint\UserPassword as OldUserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ProfileFormType extends AbstractType
{
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        // if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
        //     $constraint = new UserPassword();
        // } else {
        //     // Symfony 2.1 support with the old constraint class
        //     $constraint = new OldUserPassword();
        // }

        $this->buildUserForm($builder, $options);

        $entity = new \AcmeGroup\UserBundle\Entity\User();

        $builder
            // ->add('current_password', 'password', array(
            //     'label' => 'form.current_password',
            //     'translation_domain' => 'FOSUserBundle',
            //     'mapped' => false,
            //     'constraints' => $constraint,
            //     ))
            ->add('nom', 'text', array(
                'label'     => 'Nom : ',
                'required'  => false,
                ))
            ->add('prenom', 'text', array(  
                'label'     => 'Prénom : ',
                'required'  => false,
                ))
            ->add('tel', 'text', array(
                'label'     => 'Téléphone : ',
                'required'  => false,
                ))
            ->add('adresse', 'textarea', array(
                "required"  => false,
                "label"     => 'Adresse complète : '
                ))
            ->add('cp', 'text', array(
                "required"  => false,
                "label"     => 'Code Postal : '
                ))
            ->add('ville', 'text', array(
                "required"  => false,
                "label"     => 'Ville : '
                ))
            ->add('commentaire', 'textarea', array(
                "required"  => false,
                "label"     => 'Complément d\'adresse : '
                ))
            ->add('mactype', 'text', array(
                "required"  => false,
                "label"     => 'Type/Modèle de machine : '
                ))
            ->add('marque', 'entity', array(
                "required"  => false,
                'multiple'  => false,
                "label"     => 'Marque : ',
                'class'     => 'AcmeGroupLaboBundle:marque',
                'property'  => 'nom',
                ))
            ->add('livraison', 'choice', array(
                "required"  => true,
                'multiple'  => false,
                "label"     => 'Mode de livraison : ',
                "choices"   => $entity->getModeslivraison()
                ))
            ->add('macnoserie', 'text', array(
                "required"  => false,
                "label"     => 'Numéro de série : '
                ))
            ->add('macdateachat', 'datepicker2alldates', array( // --> datepicker : élément de formulaire personnalisé passé en service !!!
                "required"  => false,
                "label"     => 'Date d\'achat : '
                ))
            ->add('magasin', 'entity', array(
                "required"  => true,
                'class'     => 'AcmeGroupLaboBundle:magasin',
                'property'  => 'nomformenu',
                'multiple'  => false,
                "label"     => 'Boutique référente : ',
                "query_builder" => function(\AcmeGroup\LaboBundle\Entity\magasinRepository $magasin) {
                    return $magasin->findNomformenu();
                    }
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AcmeGroup\UserBundle\Entity\User',
            'intention'  => 'profile',
        ));
    }

    public function getName()
    {
        return 'acmegroup_user_profile';
    }

    /**
     * Builds the embedded form representing the user.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    protected function buildUserForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('username', null, array(
                'label' => 'form.username',
                'translation_domain' => 'FOSUserBundle'
                ))
            ->add('email', 'email', array(
                'label' => 'form.email',
                'translation_domain' => 'FOSUserBundle'
                ))
        ;
    }
}
?>