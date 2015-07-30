<?php

namespace Redking\Bundle\CoreRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TranslationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, array(
                'required'    => true,
                'description' => "Identifiant"
                ))
            ->add('support', 'choice', array(
                'choices' => array('iphone' => 'iphone', 'ipad' => 'ipad', 'android' => 'android', 'web' => 'web'),
                'required'    => false,
                'description' => "iphone, ipad, android ou web"
                ))
            ->add('screen', null, array(
                'required'    => false,
                'description' => "Nom de l’écran sur lequel se trouve le label"
                ))
            ->add('fr', null, array(
                'required'    => true,
                'description' => "Valeur en français"
                ))
            ->add('en', null, array(
                'required'    => false,
                'description' => "Valeur en anglais"
                ))
        ;

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'Redking\Bundle\CoreRestBundle\Document\Translation',
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
