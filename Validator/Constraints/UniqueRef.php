<?php
namespace Redking\Bundle\CoreRestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueRef extends Constraint
{
    public $message = 'L\'objet "%id%" est déja présent dans la réference';
}
