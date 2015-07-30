<?php

namespace Redking\Bundle\CoreRestBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ObjectToJsonTransformer implements DataTransformerInterface
{
    /**
     * Transform un objet en représensation json
     * @param  Datetime|null $datetime
     * @return array
     */
    public function transform($object)
    {
        if (!is_null($object)) {
            return json_encode($object, JSON_PRETTY_PRINT);
        }
        return '';
    }

    /**
     * Transforme une chaine json en objet
     * @param  integer|null $timestamp
     * @return null|DateTime
     */
    public function reverseTransform($json)
    {
        return json_decode($json);
    }
}
