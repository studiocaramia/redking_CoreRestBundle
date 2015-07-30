<?php

namespace Redking\Bundle\CoreRestBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConfigurationAdminController extends Controller
{

    /**
     * Surcharge pour ne pas autoriser de nouvelles configurations si il y en a déja en base
     */
    public function createAction()
    {
        if ($this->admin->getDatagrid()->getResults()->count() > 0) {
            $this->addFlash('sonata_flash_error', $this->admin->trans('flash_configuration_already_exists_error', array(), 'RedkingCoreRestBundle'));
            return new RedirectResponse($this->admin->generateUrl('list'));
        }
        
        return parent::createAction();
    }
}
