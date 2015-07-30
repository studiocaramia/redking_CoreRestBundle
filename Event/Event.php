<?php

namespace Redking\Bundle\CoreRestBundle\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Base class for Redking Core Rest Events
 */
class Event extends BaseEvent
{
    protected $object;
    
    protected $linked_object;

    protected $controller;

    protected $request;

    /**
     * [__construct description]
     * @param mixed     $object        [description]
     * @param Controller $controller    [description]
     * @param mixed     $linked_object [description]
     */
    public function __construct($object, $controller = null, $linked_object = null, $request = null)
    {
        $this->object        = $object;
        $this->controller    = $controller;
        $this->linked_object = $linked_object;
        $this->request       = $request;
        if (is_null($request) && !is_null($controller)) {
            $this->request = $this->controller->getRequest();
        }
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getLinkedObject()
    {
        return $this->linked_object;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
