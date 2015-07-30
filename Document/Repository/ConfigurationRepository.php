<?php

namespace Redking\Bundle\CoreRestBundle\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ConfigurationRepository extends DocumentRepository
{
    /**
     * Retourne le seul enregistrement qui devrait exister en base
     */
    public function getSingleton()
    {
        $results = $this->findAll();
        
        if (count($results) != 1) {
            return null;
        }

        return $results[0];
    }

}
