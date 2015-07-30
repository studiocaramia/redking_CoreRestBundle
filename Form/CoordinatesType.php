<?php
/**
 * Form Type sur les Coordinates pour avoir le datatransformer
 */

namespace Redking\Bundle\CoreRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CoordinatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('longitude', null, array(
                'required'    => false,
                'description' => "longitude"
                ))
            ->add('latitude', null, array(
                'required'    => false,
                'description' => "latitude"
                ))
            ->add('country', 'country', array(
                'required'    => false,
                'description' => "Pays"
                ))
            ->add('city', null, array(
                'required'    => false,
                'description' => "Ville"
                ))
            ->add('postal_code', null, array(
                'required'    => false,
                'description' => "Code postal"
                ))
            ->add('adress', 'textarea', array(
                'required'    => false,
                'description' => "Adresse",
                'attr'        => ['rows' => 5]
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'Redking\Bundle\CoreRestBundle\Document\Coordinates',
            'csrf_protection' => false
        ));
    }


    public function getName()
    {
        return 'coordinates';
    }
}
