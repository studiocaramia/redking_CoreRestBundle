<?php

namespace Redking\Bundle\CoreRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ConfigurationPlatformType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url_store', null, array(
                'required'    => true,
                'description' => "Version dans le store"
                ))
            ->add('store_version', null, array(
                'required'    => false,
                'description' => "Numero de la derniere version dispo sur le store"
                ))
            ->add('store_message', null, array(
                'required'    => true,
                'description' => "Texte a afficher si nouvelle version dispo sur le store"
                ))
            ->add('block_version', null, array(
                'required'    => true,
                'description' => "Numero de blockage sous lequel l'appli est pas utilisable => obligation d'update"
                ))
            ->add('block_message', null, array(
                'required'    => true,
                'description' => "texte a afficher si blocage"
                ))
        ;

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform',
            'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }
}
