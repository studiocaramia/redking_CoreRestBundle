<?php

namespace Redking\Bundle\CoreRestBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Redking\Bundle\CoreRestBundle\Form\DataTransformer\TimestampToDateTimeTransformer;

class TimestampType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new TimestampToDateTimeTransformer();
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'La valeur ne pas être convertie en DateTime',
        ));
    }

    public function getParent()
    {
        return 'integer';
    }

    public function getName()
    {
        return 'timestamp';
    }
}
