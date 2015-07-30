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

use Redking\Bundle\CoreRestBundle\Controller\BaseRestController,
    Redking\Bundle\CoreRestBundle\Exception\InvalidFormException,
    Redking\Bundle\CoreRestBundle\Handler\BaseHandler;


class ActivitysController extends BaseRestController
{

    /**
     * Récupère les Activities
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing objects.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="10", description="How many objects to return.")
     * @Annotations\QueryParam(name="sort_field", requirements="\w+", default="id", description="Field to use for sorting.", nullable=true)
     * @Annotations\QueryParam(name="sort_order", requirements="^asc|desc$", default="desc", description="Order of the sort.", nullable=true)
     * @Annotations\QueryParam(name="action", requirements="\w+", description="Filtre sur une action", nullable=true)
     * @Annotations\QueryParam(name="from_user_id", requirements="\w+", description="Id user émetteur", nullable=true)
     * @Annotations\QueryParam(name="to_user_id", requirements="\w+", description="Id user destinataire", nullable=true)
     * @Annotations\QueryParam(name="object_type", requirements="\w+", description="Type objet", nullable=true)
     * @Annotations\QueryParam(name="child_object_type", requirements="\w+", description="Type objet enfant", nullable=true)
     * @Annotations\QueryParam(name="_return_objects", requirements="^1|0$", default="0", description="Retourne ou pas les objets liés")
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * 
     * @ApiDoc()
     * @Annotations\Get("/activities")
     */
    public function getActivitysAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $serialize_groups = ['Default'];
        if ($paramFetcher->get('_return_objects') == 1) {
            $serialize_groups[] = 'link';
        } else {
            $serialize_groups[] = 'id';
        }
        return parent::getObjectsAction($request, $paramFetcher, $serialize_groups);
    }

 }
