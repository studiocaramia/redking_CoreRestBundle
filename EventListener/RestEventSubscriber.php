<?php
/**
 * Subscriber pour les events REST
 */
namespace Redking\Bundle\CoreRestBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Redking\Bundle\CoreRestBundle\Event\Events;
use Redking\Bundle\CoreRestBundle\Event\Event;
use Redking\Bundle\CoreRestBundle\Service\ActivityRecorder;
use Redking\Bundle\CoreRestBundle\Document\Activity;

class RestEventSubscriber implements EventSubscriberInterface 
{
    
    /**
     * @var ActivityRecorder
     */
    protected $activity_persister;

    /**
     * Liste des documents qui enregistrent des activities
     * @var array
     */
    protected $documents_subscribed = array();


    /**
     * [__construct description]
     * @param ActivityRecorder $activity_persister [description]
     */
    public function __construct(ActivityRecorder $activity_persister)
    {
        $this->activity_persister = $activity_persister;
    }

    /**
     * [setDocumentsSubscribed description]
     * @param array $documents_subscribed [description]
     */
    public function setDocumentsSubscribed($documents_subscribed)
    {
        foreach ($this->documents_subscribed as $class_name => $config) {
            if (!class_exists($class_name)) {
                throw new \Exception(sprintf(__METHOD__.' : Unknown class "%s"', $class_name));
            }
            if (!isset($config['actions'])) {
                throw new \Exception(sprintf(__METHOD__.' : Missing actions for class "%s"', $class_name));
            }
        }
        $this->documents_subscribed = $documents_subscribed;
    }

    /**
     * Indique si le document doit engendré une activity pour une action donnée
     * @param  object  $document
     * @param  string  $action
     * @return boolean           [description]
     */
    protected function isDocumentSubscribed($document, $action)
    {
        foreach ($this->documents_subscribed as $class_name => $config) {
            if ($document instanceof $class_name && in_array($action, $config['actions'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retourne le champ user_field de la configuration pour un document
     * @param  object $document [description]
     * @return [type]           [description]
     */
    protected function getUserFieldForDocument($document)
    {
        foreach ($this->documents_subscribed as $class_name => $config) {
            if ($document instanceof $class_name && !is_null($config['user_field'])) {
                return $config['user_field'];
            }
        }
        return null;
    }

    /**
     * Evenements soucrits
     * @return [type] [description]
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::POST_PERSIST      => 'onPostPersist',
            Events::UPDATE_OBJECT     => 'onUpdate',
            Events::PRE_DELETE_OBJECT => 'onPreDelete',
            );
    }

    /**
     * Gestion des nouveaux documents enregistrés
     * @param  Event  $ev [description]
     * @return [type]     [description]
     */
    public function onPostPersist(Event $ev)
    {
        $this->sendEventToPersister($ev, Activity::ACTION_INSERT);
    }

    /**
     * Gestion des nouveaux documents enregistrés
     * @param  Event  $ev [description]
     * @return [type]     [description]
     */
    public function onUpdate(Event $ev)
    {
        $this->sendEventToPersister($ev, Activity::ACTION_UPDATE);
    }

    /**
     * Gestion des nouveaux documents enregistrés
     * @param  Event  $ev [description]
     * @return [type]     [description]
     */
    public function onPreDelete(Event $ev)
    {
        $this->sendEventToPersister($ev, Activity::ACTION_DELETE);
    }

    /**
     * Envoi des documents présents dans l'event au persister si besoin
     * @param  Event  $ev     [description]
     * @param  [type] $action [description]
     * @return [type]         [description]
     */
    protected function sendEventToPersister(Event $ev, $action)
    {
        $document        = $ev->getObject();
        $linked_document = $ev->getLinkedObject();
        $user            = $ev->getController()->get('security.context')->getToken()->getUser();

        if ($this->isDocumentSubscribed($linked_document, $action)) {
            if (!is_null($this->getUserFieldForDocument($linked_document))) {
                $user = $this->getUserFromDocument($linked_document);
            }
            $this->activity_persister->record($document, $action, $user, $linked_document);
        }

        else if ($this->isDocumentSubscribed($document, $action)) {
            // Dans le cas ou un event insert est envoyé avec un linked_document mais que ce linked_document n'est pas pris en charge, l'event doit etre considéré comme update du document parent
            if ($action == Activity::ACTION_INSERT && !is_null($linked_document)) {
                $action = Activity::ACTION_UPDATE;
            }

            if (!is_null($this->getUserFieldForDocument($document))) {
                $user = $this->getUserFromDocument($document);
            }
            $this->activity_persister->record($document, $action, $user);
        }
    }

    /**
     * Retourne un user lié au document
     * @param  [type] $document [description]
     * @return [type]           [description]
     */
    protected function getUserFromDocument($document)
    {
        $accessor = \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor();
        return $accessor->getValue($document, $this->getUserFieldForDocument($document));
    }
}
