<?php

namespace Redking\Bundle\CoreRestBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TimestampToDateTimeTransformer implements DataTransformerInterface
{
    
    /**
     * Transform un objet DateTime en timestamp
     * @param  Datetime|null $datetime
     * @return array
     */
    public function transform($datetime)
    {
        if (!$datetime instanceof \DateTime) {
            return null;
        }
        return $datetime->format("U");
    }

    /**
     * Transforme un timestamp en un objet DateTime
     * @param  integer|null $timestamp
     * @return null|DateTime
     */
    public function reverseTransform($timestamp)
    {
        if (!is_null($timestamp)) {
            return new \DateTime('@'.$timestamp);
        } 
        return null;
    }
}
