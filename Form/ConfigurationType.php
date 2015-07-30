<?php

namespace Redking\Bundle\CoreRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ConfigurationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('offline', null, array(
                'required'    => false,
                'description' => "Offline ?"
                ))
            ->add('offline_message', null, array(
                'required'    => false,
                'description' => "Message a afficher a l'ecran si applis / sites offline"
                ))
            ->add('email_support', null, array(
                'required'    => true,
                'description' => "Email du support"
                ))
            ->add('email_marketing', null, array(
                'required'    => true,
                'description' => "Email du marketing"
                ))
        ;

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'Redking\Bundle\CoreRestBundle\Document\Configuration',
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
