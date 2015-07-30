<?php

namespace Redking\Bundle\CoreRestBundle\Handler;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\FormFactoryInterface,
    Symfony\Component\HttpFoundation\Request;
use Redking\Bundle\CoreRestBundle\Exception\InvalidFormException;
use Symfony\Bridge\Monolog\Logger;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Redking\Bundle\CoreRestBundle\Event\Event,
    Redking\Bundle\CoreRestBundle\Event\Events;

class BaseHandler
{
    
    protected $dm;
    protected $entityClass;
    protected $entityFormClass;
    protected $repository;
    protected $formFactory;
    protected $logger;
    protected $event_dispatcher;
    
    /**
     * 
     * @param DocumentManager      $dm          [description]
     * @param [type]               $entityClass [description]
     * @param [type]               $entityFormClass [description]
     * @param FormFactoryInterface $formFactory [description]
     */
    public function __construct(DocumentManager $dm, $entityClass, $entityFormClass, FormFactoryInterface $formFactory, Logger $logger, EventDispatcherInterface $event_dispatcher)
    {
        $this->dm               = $dm;
        $this->entityClass      = $entityClass;
        $this->entityFormClass  = $entityFormClass;
        $this->repository       = $this->dm->getRepository($this->entityClass);
        $this->formFactory      = $formFactory;
        $this->logger           = $logger;
        $this->event_dispatcher = $event_dispatcher;
    }

    /**
     * Return DocumentManager
     * @return DocumentManager
     */
    public function getDm()
    {
        return $this->dm;
    }

    /**
     * Return object
     * 
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * 
     * @param string $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * 
     * @param string $entityFormClass
     */
    public function setEntityFormClass($entityFormClass)
    {
        $this->entityFormClass = $entityFormClass;
        return $this;
    }

    public function getEntityClassShortened()
    {
        $class_path = explode('\\', ClassUtils::getRealClass($this->getEntityClass()));
        return array_pop($class_path);
    }

    /**
     * Get a list of objects.
     *
     * @param int $limit  the limit of the result
     * @param int $offset starting from the offset
     * @param array $filters filters
     *
     * @return array
     */
    public function all($limit = 10, $offset = 0, $filters = array(), $sort = null)
    {
        $qb = $this->getQueryBuilderForAll($filters)
            ->limit($limit)
            ->skip($offset);
        if (is_array($sort)) {
            $qb->sort($sort);
        }
        return $qb
            ->getQuery()
            ->execute()
            ->toArray(false)
        ;
    }

    /**
     * Get number of objects
     * @param  [type] $filters [description]
     * @return [type]          [description]
     */
    public function count($filters)
    {
        $nb = $this->getQueryBuilderForAll($filters)
            ->getQuery()
            ->execute()
            ->count()
        ;
        return $nb;
    }

    /**
     * Get QueryBuilder for the listing
     * @param  array $filters
     * @return QueryBuilder
     */
    public function getQueryBuilderForAll($filters)
    {
        $qb = $this->repository->createQueryBuilder();

        // Recherche d'une méthode existante dans le repository pour un filtre
        foreach($filters as $filter_key => $filter_value) {
            $method = 'find_by_'.$filter_key;
            $method = lcfirst(\Symfony\Component\DependencyInjection\Container::camelize($method));
            
            if (method_exists($this->repository, $method)) {
                $qb = $this->repository->$method($filter_value);
                unset($filters[$filter_key]);
            }
        }
        
        foreach($filters as $filter_key => $filter_value) {
            $param_value = (is_numeric($filter_value)) ? (int) $filter_value : $filter_value;
            // Si le nom du paramètre fini par _id
            if (substr($filter_key, -3) == '_id') {
                $id_prefix = (strpos('__', $filter_key) !== false) ? '_' : '$';
                $key = str_replace('__','.', $filter_key);
                $key = substr($key, 0, -3) . '.' . $id_prefix . 'id';
                if (!is_array($filter_value)) {
                    $qb->field($key)->equals(new \MongoId($param_value));
                } else {
                    if (strpos('$', $key) !== false) {
                        $qb->field($key)->in($param_value);
                    } else {
                        $params_mongos = [];
                        foreach ($param_value as $param) {
                            $params_mongos[] = new \MongoId($param);
                        }
                        $qb->field($key)->in($params_mongos);
                    }
                }
            } 
            // Si le champ correspond à un boolean
            elseif (substr($filter_key, 0, 3) == 'is_') {
                $param_value = ($param_value === 'true' || $param_value === '1' || $param_value === 1 || $param_value === true) ? true : false;
                $qb->field($filter_key)->equals((bool)$param_value);
            }
            // Si le paramètre commence par une majuscule, c'est un champ d'une relation, je décompose
            elseif (preg_match("/^[A-Z]$/", substr($filter_key, 0, 1)) == 1) {
                $key = implode('.',explode('_',strtolower($filter_key)));
                $qb->field($key)->equals($param_value);
            } elseif (is_array($param_value)) {
                $qb->field($filter_key)->in($param_value);
            } else {
                $qb->field($filter_key)->equals($param_value);
            }
        }
        return $qb;
    }

    /**
     * Create a new object
     *
     * @param Request $request
     *
     * @return PageInterface
     */
    public function post(Request $request)
    {
        $object = $this->createObject();
        
        $this->logger->debug('POST Multiple request : '.var_export($this->isMultipleObjectsInRequest($request), true));
        
        if ($this->isMultipleObjectsInRequest($request)) {
            $objects = array();
            foreach ($request->request->all() as $sub_request_param) {
                $sub_request = $request->duplicate(null, $sub_request_param);
                $this->logger->debug('subrequest : '.var_export($sub_request->request->all(), true));
                $objects[] = $this->processForm($this->createObject(), $sub_request, 'POST');
            }
            return $objects;
        } else {
            return $this->processForm($object, $request, 'POST');
        }
    }

    /**
     * Edit an object
     *
     * @param Request $request
     *
     * @return PageInterface
     */
    public function put($object, Request $request)
    {
        return $this->processForm($object, $request, 'PUT');
    }

    /**
     * Delete object
     * 
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete($object)
    {
        $this->dm->remove($object);
        $this->dm->flush();
    }


    /**
     * Processes the form.
     *
     * @param $object
     * @param Request         $request
     * @param String        $method
     *
     * @return PageInterface
     *
     * @throws \Redking\Bundle\CoreRestBundle\Exception\InvalidFormException
     */
    protected function processForm($object, Request $request, $method = "PUT")
    {
        $request = $this->checkAndCleanRequest($request);

        $form = $this->formFactory->create($this->createFormTypeObject(), $object, array('method' => $method));

        if ($method == "PUT") {
            $request = $this->resetRequestForPut($request, $form);
        }
        $this->logger->debug('REQUEST : '.var_export($request->request->all(), true));
        
        $this->event_dispatcher->dispatch(
            Events::PRE_BIND,
            new Event($object, null, null, $request)
        );

        $form->handleRequest($request);

        $this->event_dispatcher->dispatch(
            Events::POST_BIND,
            new Event($object, null, null, $request)
        );

        if ($form->isValid()) {

            $object = $form->getData();
            $this->updateObjectBeforePersist($object);

            $this->dm->persist($object);
            $this->dm->flush($object);

            return $object;
        }
        // return $form;
        throw new InvalidFormException('Invalid submitted data', $form);
    }

    
    /**
     * Vérifie et nettoie l'objet Request
     * @param  Request $request
     * @return Request
     */
    public function checkAndCleanRequest(Request $request)
    {
        if ($request->request->has('_format')) {
            $request->request->remove('_format');
        }
        if (count($request->request->all()) === 0) {
            throw new \InvalidArgumentException("Parameters missing");
        }
        return $request;
    }

    /**
     * Indique si il y a au moins un objet sous forme de tableau dans la request
     * @return boolean
     */
    public function isMultipleObjectsInRequest(Request $request)
    {
        $keys = array_keys($request->request->all());
        if (is_array($keys) && count($keys) >= 1 && $keys[0] === 0) {
            return true;
        }
        return false;
    }

    /**
     * Dans le cas d'un update, je forge la request avec les champs du formulaire qui n'ont pas été passés
     * @param  Request $request
     * @param  FormInterface  $form
     * @return Request
     */
    public function resetRequestForPut(Request $request, $form)
    {
        foreach ($form as $field_key => $field) {
            if (!$request->request->has($field->getName()) && !is_null($field->getData())) {
                if (is_object($field->getData()))
                {
                    $this->logger->debug($field->getName().' -> '.get_class($field->getData()));
                    // Dans le cas d'une référence vers plusieurs objets, on reforge le tableau d'id
                    if ($field->getData() instanceof \Doctrine\ODM\MongoDB\PersistentCollection) {
                        $data = array();
                        foreach ($field->getData() as $sub_object) {
                            $this->logger->debug('sub_object -> '.get_class($sub_object));
                            if (method_exists($sub_object, 'toRequest')) {
                                $data[] = $sub_object->toRequest();
                            } else {
                                $data[] = $sub_object->getId();
                            }
                        }
                        $request->request->set($field->getName(), $data);
                    // On reformate un timestamp a partir d'une DateTime
                    } elseif ($field->getData() instanceof \DateTime) {
                        $request->request->set($field->getName(), $field->getData()->format('U'));
                    // On repasse l'id d'un Objet dans le cas d'une liaison
                    } elseif (method_exists($field->getData(), 'getId')) {
                        $request->request->set($field->getName(), $field->getData()->getId());
                    // Pour un objet sans id, on tente la serialization
                    } elseif (method_exists($field->getData(), 'toRequest')) {
                        $request->request->set($field->getName(), $field->getData()->toRequest());
                    } 
                }
                else {
                    $request->request->set($field->getName(), $field->getData());
                }
            }
        }
        return $request;
    }



    /**
     * Instanciate new object 
     * 
     * @return [type] [description]
     */
    public function createObject()
    {
        return new $this->entityClass();
    }

    /**
     * Instanciate form type
     * @return [type] [description]
     */
    public function createFormTypeObject()
    {
        return new $this->entityFormClass();
    }

    /**
     * Json decode request parameter if needed
     * @param  string  $request_key
     * @param  Request $request
     * @return Request
     */
    public function ensureJsonDecodeRequest($request_key, Request $request)
    {
        if ($request->request->has($request_key) && $request->request->get($request_key) != '' 
            && !is_array($request->request->get($request_key)) && !is_object($request->request->get($request_key))
            ) {
            $data = json_decode($request->request->get($request_key), true);
            if (!is_array($data)) {
                throw new \InvalidArgumentException("Unable to decode '".$request_key."' parameter");
            }
            $request->request->set($request_key, $data);
        }
        return $request;
    }

    /**
     * Update fields before object persistance
     * @param  Document $object
     * @return [type]         [description]
     */
    protected function updateObjectBeforePersist(&$object)
    {

    }

    /**
     * Suppression d'un critère de recherche dans le querybuilder
     * @param  [type] $qb    [description]
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    protected function removeFieldFromQueryBuilder($qb, $field) 
    {
        $query_array = $qb->getQueryArray();
        if (isset($query_array[$field])) {
            unset($query_array[$field]);
        }
        $qb->setQueryArray($query_array);

        return $qb;
    }

}
