<?php

namespace Redking\Bundle\CoreRestBundle\Controller;

use FOS\RestBundle\Routing\ClassResourceInterface;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\Routing\Exception\ResourceNotFoundException,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;

use FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\FOSRestController,
    FOS\RestBundle\Controller\Annotations,
    FOS\RestBundle\Request\ParamFetcherInterface,
    FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use JMS\Serializer\SerializationContext;

use Redking\Bundle\CoreRestBundle\Exception\InvalidFormException,
    Redking\Bundle\CoreRestBundle\Handler\BaseHandler;

use Redking\Bundle\CoreRestBundle\Event\Event,
    Redking\Bundle\CoreRestBundle\Event\Events;

class BaseRestController extends FOSRestController
{

    /**
     * [$service_handler description]
     * @var [type]
     */
    protected $service_handler;

    /**
     * Constructor
     * 
     * @param BaseHandler $service_handler [description]
     */
    public function __construct(BaseHandler $service_handler)
    {
        $this->service_handler = $service_handler;
    }

    /**
     * Returns handler
     * 
     * @return BaseHandler
     */
    public function getServiceHandler()
    {
        return $this->service_handler;
    }

    /**
     * Récupère les objets
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing objects.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="10", description="How many objects to return.")
     * @Annotations\QueryParam(name="sort_field", requirements="\w+", description="Field to use for sorting.", nullable=true)
     * @Annotations\QueryParam(name="sort_order", requirements="^asc|desc$", description="Order of the sort.", nullable=true)
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * 
     * @ApiDoc()
     */
    protected function getObjectsAction(Request $request, ParamFetcherInterface $paramFetcher, $serialize_groups = array())
    {
        $params = $paramFetcher->all();

        list($limit, $offset, $filters, $sort) = $this->getParamsAndFilters($params);

        $count = $this->getServiceHandler()->count($filters);
        $result = $this->getServiceHandler()->all($limit, $offset, $filters, $sort);

        $view = $this->view(array('count' => $count, 'results' => $result));

        $default_serialize_groups = array('Default');
        $serialize_groups = array_merge($default_serialize_groups, $serialize_groups);

        $view->setSerializationContext(SerializationContext::create()->enableMaxDepthChecks()->setGroups($serialize_groups));
        return $this->handleView($view);
    }

    public function getParamsAndFilters($params)
    {
        $offset = (isset($params['offset']) && !is_null($params['offset'])) ? $params['offset'] : 0;
        $limit = (isset($params['limit']) && !is_null($params['limit'])) ? $params['limit'] : 10;
        $sort_field = (isset($params['sort_field']) && !is_null($params['sort_field'])) ? $params['sort_field'] : null;
        $sort_order = (isset($params['sort_order']) && !is_null($params['sort_order'])) ? $params['sort_order'] : null;

        if (!is_null($sort_field) && !is_null($sort_order)) {
            $sort = array($sort_field => $sort_order);
        } else {
            $sort = null;
        }

        // Suppression des clés commencant par un "_" des params : ils ne doivent pas être considérés comme des filtres
        $filtered_params = [];
        foreach ($params as $key => $value) {
            if (substr($key, 0, 1) != '_') {
                $filtered_params[$key] = $value;
            }
        }

        // Récupération des autres paramètres qui seront traités comme des filtres
        $filters = array_filter(array_diff_key($filtered_params, array_flip(array('offset', 'limit', 'sort_field', 'sort_order'))), function($item){
            if (is_array($item)) {
                return (count($item) > 0);
            } else {
                return (strlen($item) > 0);
            }
        });

        return [$limit, $offset, $filters, $sort];
    }

    /**
     * Récupère un objet
     * 
     * @param  string $id Identifiant
     * @return [type]     [description]
     *
     * @ApiDoc()
     */
    protected function getObjectAction($id, $serialize_groups = array())
    {
        $object = $this->getOr404($id);

        $view = $this->view($object);

        $this->get('event_dispatcher')->dispatch(
            Events::GET_OBJECT,
            new Event($object, $this)
        );

        $default_serialize_groups = array('Default');
        $serialize_groups = array_merge($default_serialize_groups, $serialize_groups);
        
        $view->setSerializationContext(SerializationContext::create()->enableMaxDepthChecks()->setGroups($serialize_groups));
        return $this->handleView($view);
    }

    /**
     * Crée un objet
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Crée une object",
     *   input = "Redking\Bundle\CoreRestBundle\Form\ConfigurationType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     */
    protected function postObjectAction(Request $request, $serialize_groups = array(), $reload_from_db = true)
    {
        
        try {
            $object = $this->getServiceHandler()->post(
                $request
            );

            $this->get('event_dispatcher')->dispatch(
                Events::POST_PERSIST,
                new Event($object, $this)
            );

            $dm = $this->getServiceHandler()->getDm();
            $dm->flush();
            $dm->clear();
            if (is_object($object) && $reload_from_db === true) {
                return $this->getObjectAction($object->getId());
            }
            // Si plusieurs array(plusieurs objets) on les renvoit direct ou si reload from db est false
            elseif (is_array($object) || (is_object($object) && $reload_from_db === false)) {
                $default_serialize_groups = array('Default');
                $serialize_groups = array_merge($default_serialize_groups, $serialize_groups);
                $view = $this->view($object);
                $view->setSerializationContext(SerializationContext::create()->enableMaxDepthChecks()->setGroups($serialize_groups));
                return $this->handleView($view);
            }

        } catch (InvalidFormException $exception) {

            $view = $this->view($exception->getForm());
            return $this->handleView($view);
        }
    }

    /**
     * Update object
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Redking\Bundle\CoreRestBundle\Form\ConfigurationType",
     *   statusCodes = {
     *     201 = "Returned when the Page is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     *
     * @param Request $request the request object
     * @param int     $id      the page id
     *
     * @return View
     *
     * @throws NotFoundHttpException when page not exist
     */
    protected function putObjectAction(Request $request, $id, $serialize_groups = array(), $reload_from_db = true)
    {
        $object = $this->getOr404($id);

        $this->get('event_dispatcher')->dispatch(
                Events::PRE_UPDATE_OBJECT,
                new Event($object, $this)
            );

        try {
            $object = $this->getServiceHandler()->put(
                    $object,
                    $request
                );

            $this->get('event_dispatcher')->dispatch(
                Events::UPDATE_OBJECT,
                new Event($object, $this)
            );
             
        } catch (InvalidFormException $e) {
            $view = $this->view($e->getForm());
            return $this->handleView($view);
        }
        $dm = $this->getServiceHandler()->getDm();
        $dm->flush();
        $dm->clear(); 
        if ($reload_from_db === true) {
            return $this->getObjectAction($object->getId());
        } else {
            $view = $this->view($object);
            $default_serialize_groups = array('Default');
            $serialize_groups = array_merge($default_serialize_groups, $serialize_groups);
            
            $view->setSerializationContext(SerializationContext::create()->enableMaxDepthChecks()->setGroups($serialize_groups));
            return $this->handleView($view);
        }
    }

    
    /**
     * Supprime un objet
     *
     * @ApiDoc()
     */
    protected function deleteObjectAction($id)
    {
        $object = $this->getOr404($id);
        
        $this->get('event_dispatcher')->dispatch(
            Events::PRE_DELETE_OBJECT,
            new Event($object, $this)
        );
        
        $this->getServiceHandler()->delete($object);

        $this->get('event_dispatcher')->dispatch(
            Events::DELETE_OBJECT,
            new Event($object, $this)
        );

        $view = $this->view(array('success' => true));
        return $this->handleView($view);
    }


    /**
     * Fetch a Object or throw an 404 Exception.
     *
     * @param mixed $id
     *
     * @return PageInterface
     *
     * @throws NotFoundHttpException
     */
    public function getOr404($id)
    {
        if (!($page = $this->getServiceHandler()->get($id))) {
            throw new ResourceNotFoundException(sprintf('The resource \'%s\' with id \'%s\' was not found.', $this->getServiceHandler()->getEntityClassShortened(), $id));
        }

        return $page;
    }

    /**
     * Crée un objet lié à l'objet géré
     * @param  Request $request
     * @param  string  $id        Identifiant de l'objet géré
     * @param  string  $link_name Nom de la classe de l'objet lié
     * @param  string  $namespace Nom du namespace de l'objet lié
     * @param  string|null  $attribute_name Nom de l'attribut
     * @param  string|null  $form_name Nom du formulaire
     * @return mixed
     */
    protected function postLinkedObject(Request $request, $id, $classname, $namespace, $attribute_name = null, $form_name = null)
    {
        $object = $this->getOr404($id);

        $document_class = $namespace.'\\Document\\'.$classname;
        
        if (is_null($attribute_name)) {
            $method = 'add'.$classname;
        } else {
            $method = 'add'.$attribute_name;
            $method = lcfirst(\Symfony\Component\DependencyInjection\Container::camelize($method));
        }
        if (!method_exists($object, $method)) {
            throw new \Exception(sprintf('No method %s for object %s', $method, get_class($object)));
        }
        
        $object_link = new $document_class();

        if ($form_name instanceof \Symfony\Component\Form\AbstractType) {
            $form = $this->createForm($form_name, $object_link);
        } else {
            if (is_null($form_name)) {
                $form_class = $namespace.'\\Form\\'.$classname.'Type';
            } else {
                $form_class = $namespace.'\\Form\\'.$form_name;
            }
            $form = $this->createForm(new $form_class(), $object_link);
        }

        // Nettoyage et redéfinition de la Request
        $request = $this->getServiceHandler()->checkAndCleanRequest($request);

        $this->get('event_dispatcher')->dispatch(
            Events::PRE_BIND,
            new Event($object, $this, $object_link)
        );

        $form->handleRequest($request);

        $this->get('event_dispatcher')->dispatch(
            Events::POST_BIND,
            new Event($object, $this, $object_link)
        );

        if ($form->isValid()) {
            $object->$method($object_link);

            if (method_exists($object, 'setUpdatedAt')) {
                $object->setUpdatedAt(new \DateTime());
            }

            $error_list = $this->get('validator')->validate($object);
            if (count($error_list) === 0) {
                $dm = $this->getServiceHandler()->getDm();
                
                $this->get('event_dispatcher')->dispatch(
                    Events::PRE_PERSIST,
                    new Event($object, $this, $object_link)
                );

                $dm->persist($object);

                $this->get('event_dispatcher')->dispatch(
                    Events::POST_PERSIST,
                    new Event($object, $this, $object_link)
                );

                $dm->flush();
                $dm->clear();
            } else {
                throw new \InvalidArgumentException($error_list);
            }
        } else {
            $view = $this->view($form);
            return $this->handleView($view);
        }

        return $this->getObjectAction($id);
    }

    /**
     * Edition d'un object lié à l'objet géré
     * @param  Request $request
     * @param  string  $id        Identifiant de l'objet géré
     * @param  string  $link_id   Identifiant de l'objet lié
     * @param  string  $link_name Nom de la classe de l'objet lié
     * @param  string  $namespace Nom du namespace de l'objet lié
     * @param  string|null  $attribute_name Nom de l'attribut
     * @param  string|null  $form_name Nom du formulaire
     * @return mixed
     */
    protected function putLinkedObject(Request $request, $id, $link_id, $classname, $namespace, $attribute_name = null, $form_name = null)
    {
        $object = $this->getOr404($id);

        if (is_null($attribute_name)) {
            $method = 'get'.$classname.'ById';
        } else {
            $method = 'get_'.$attribute_name.'_by_id';
            $method = lcfirst(\Symfony\Component\DependencyInjection\Container::camelize($method));
        }

        if (!method_exists($object, $method)) {
            throw new \Exception(sprintf('No method %s for object %s', $method, get_class($object)));
        }
        $object_link = $object->$method($link_id);
        if (is_null($object_link)) {
            throw new ResourceNotFoundException(sprintf('The %s \'%s\' was not part of object \'%s\'.',$classname, $link_id, $id));
        }

        if ($form_name instanceof \Symfony\Component\Form\AbstractType) {
            $form = $this->createForm($form_name, $object_link, array('method'=>'PUT'));
        } else {
            if (is_null($form_name)) {
                $form_class = $namespace.'\\Form\\'.$classname.'Type';
            } else {
                $form_class = $namespace.'\\Form\\'.$form_name;
            }

            $form = $this->createForm(new $form_class(), $object_link, array('method'=>'PUT'));
        }

        // Nettoyage et redéfinition de la Request
        $request = $this->getServiceHandler()->checkAndCleanRequest($request);
        $request = $this->getServiceHandler()->resetRequestForPut($request, $form);

        $this->get('event_dispatcher')->dispatch(
            Events::PRE_BIND,
            new Event($object, $this, $object_link)
        );

        $form->handleRequest($request);

        $this->get('event_dispatcher')->dispatch(
            Events::POST_BIND,
            new Event($object, $this, $object_link)
        );

        if ($form->isValid()) {
            $error_list = $this->get('validator')->validate($object);
            if (count($error_list) === 0) {
                $dm = $this->getServiceHandler()->getDm();

                $this->get('event_dispatcher')->dispatch(
                    Events::PRE_UPDATE_OBJECT,
                    new Event($object, $this, $object_link)
                );

                if (method_exists($object, 'setUpdatedAt')) {
                    $object->setUpdatedAt(new \DateTime());
                    $dm->persist($object);
                }

                $dm->persist($object_link);

                $this->get('event_dispatcher')->dispatch(
                    Events::UPDATE_OBJECT,
                    new Event($object, $this, $object_link)
                );
                
                $dm->flush();
                $dm->clear();
            } else {
                throw new \InvalidArgumentException($error_list);
            }
        } else {
            $view = $this->view($form);
            return $this->handleView($view);
        }

        return $this->getObjectAction($id);
    }

    /**
     * Suppression d'un object lié à l'objet géré
     * @param  string $id      Identifiant de l'objet géré
     * @param  string $link_id Identifiant de l'objet lié
     * @return mixed
     */
    protected function deleteLinkedObject($id, $link_id, $classname, $attribute_name = null)
    {
        $object = $this->getOr404($id);

        if (is_null($attribute_name)) {
            $method = 'get'.$classname.'ById';
        } else {
            $method = 'get_'.$attribute_name.'_by_id';
            $method = lcfirst(\Symfony\Component\DependencyInjection\Container::camelize($method));
        }

        if (!method_exists($object, $method)) {
            throw new \Exception(sprintf('No method %s for object %s', $method, get_class($object)));
        }
        $object_link = $object->$method($link_id);
        if (is_null($object_link)) {
            throw new ResourceNotFoundException(sprintf('The %s \'%s\' was not part of object \'%s\'.',$classname, $link_id, $id));
        }

        if (is_null($attribute_name)) {
            $method = 'remove'.$classname;
        } else {
            $method = 'remove_'.$attribute_name;
            $method = lcfirst(\Symfony\Component\DependencyInjection\Container::camelize($method));
        }
        if (!method_exists($object, $method)) {
            throw new \Exception(sprintf('No method %s for object %s', $method, get_class($object)));
        }
        
        $this->get('event_dispatcher')->dispatch(
            Events::PRE_DELETE_OBJECT,
            new Event($object, $this, $object_link)
        );

        $object->$method($object_link);

        $dm = $this->getServiceHandler()->getDm();

        if (method_exists($object, 'setUpdatedAt')) {
            $object->setUpdatedAt(new \DateTime());
            $dm->persist($object);
        }

        $dm->persist($object_link);

        $this->get('event_dispatcher')->dispatch(
            Events::DELETE_OBJECT,
            new Event($object, $this, $object_link)
        );

        $dm->flush();
        $dm->clear();

        $view = $this->view(array('success' => true));
        return $this->handleView($view);

    }

}
