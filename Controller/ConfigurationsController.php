<?php

namespace Redking\Bundle\CoreRestBundle\Controller;


use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Routing\Exception\ResourceNotFoundException,
    Symfony\Component\Validator\Constraints as Assert
    ;

use FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\FOSRestController,
    FOS\RestBundle\Controller\Annotations,
    FOS\RestBundle\Request\ParamFetcherInterface,
    FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Redking\Bundle\CoreRestBundle\Exception\InvalidFormException,
    Redking\Bundle\CoreRestBundle\Handler\BaseHandler;

use Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform,
    Redking\Bundle\CoreRestBundle\Form\ConfigurationPlatformType;

class ConfigurationsController extends BaseRestController
{

    /**
     * Récupère la configuration
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * 
     * @ApiDoc()
     */
    public function getConfigurationAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $object = $this->getObject();
        return $this->getObjectAction($object->getId(), []);
    }

    /**
     * Retourne l'unique objet configuration en base
     * @return [type] [description]
     */
    public function getObject()
    {
        $objects = $this->getServiceHandler()->all();
        if (count($objects) != 1) {
            throw new \InvalidArgumentException("Aucun objet configuration en base");
        }
        return $objects[0];
    }
}
