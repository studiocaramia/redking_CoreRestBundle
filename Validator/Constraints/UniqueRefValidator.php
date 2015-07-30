<?php
namespace Redking\Bundle\CoreRestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class UniqueRefValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \Doctrine\Common\Collections\Collection)
        {
            throw new \Exception("UniqueRefValidator : Invalid type, should be a Doctrine collection");
            
        }
        $ids = array();
        foreach($value as $object) {
            if (in_array($object->getId(), $ids)) {
                $this->context->addViolation($constraint->message, array('%id%' => $object->getId()));
            }
            $ids[] = $object->getId();
        }
    }
}
