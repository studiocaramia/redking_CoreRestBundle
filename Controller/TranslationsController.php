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


class TranslationsController extends BaseRestController
{

    /**
     * Récupère les traductions
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing objects.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="10", description="How many objects to return.")
     * @Annotations\QueryParam(name="sort_field", requirements="\w+", description="Field to use for sorting.", nullable=true)
     * @Annotations\QueryParam(name="sort_order", requirements="^asc|desc$", description="Order of the sort.", nullable=true)
     * @Annotations\QueryParam(name="support", requirements="^(iphone|ipad|android|web)$", description="Support", strict=true, nullable=true)
     * @Annotations\QueryParam(name="screen", description="Nom de l’écran sur lequel se trouve le label")
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * 
     * @ApiDoc()
     */
    public function getTranslationsAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        return parent::getObjectsAction($request, $paramFetcher);
    }

    /**
     * Récupère une traduction
     * 
     * @param  string $id Identifiant
     * @return [type]     [description]
     *
     * @ApiDoc()
     */
    public function getTranslationAction($id)
    {
        return parent::getObjectAction($id);
    }

    /**
     * Crée une traduction
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Crée une traduction",
     *   input = "Redking\Bundle\CoreRestBundle\Form\TranslationType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     */
    /*public function postTranslationAction(Request $request)
    {
        $view = parent::postObjectAction($request);
        return $view;
    }*/

    /**
     * Met à jour une traduction
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Mets à jour une traduction",
     *   input = "Redking\Bundle\CoreRestBundle\Form\TranslationEditedType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     */
    /*public function putTranslationAction(Request $request, $id)
    {
        $view = parent::putObjectAction($request, $id);
        return $view;
    }*/

    /**
     * Supprime une traduction
     *
     * @ApiDoc()
     */
    /*public function deleteTranslationAction($id)
    {
        return parent::deleteObjectAction($id);
    }*/

    
 }
