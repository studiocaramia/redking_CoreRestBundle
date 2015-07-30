<?php
/**
 * Subscriber pour le cycle de vie des documents
 */
namespace Redking\Bundle\CoreRestBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Events as MongoDBEvents;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreFlushEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;

use Symfony\Component\PropertyAccess\PropertyAccess;

use FOS\UserBundle\Model\UserManager;

use Redking\Bundle\CoreRestBundle\Document\Translation;

class DocumentEventSubscriber implements EventSubscriber
{
    
    /**
     * @var Configuration : objet configuration devant etre mis à jour apres tous les autres traitement, au postFlush
     */
    protected $configuration_to_be_updated = null;

    /**
     * Champs de la configuration updatés
     * @var array
     */
    protected $configuration_fields_updated = [];

    protected $preflush_done = false;

    public function getSubscribedEvents()
    {
        return array(
            MongoDBEvents::prePersist,
            MongoDBEvents::postUpdate,
            MongoDBEvents::preRemove,
            MongoDBEvents::preFlush,
            );
    }

    
    /**
     * Met a jour un champ de type timestamp de l'objet configuration
     *
     * @param DocumentManager $dm
     * @param string $field  Nom du champ
     */
    public function updateConfigurationTimestampField(DocumentManager $dm, $field)
    {
        if (is_null($this->configuration_to_be_updated)) {
            $this->configuration_to_be_updated = $dm->getRepository('RedkingCoreRestBundle:Configuration')->getSingleton();
        }
        
        if (!is_null($this->configuration_to_be_updated) && !in_array($field, $this->configuration_fields_updated)) {

            $this->configuration_fields_updated[] = $field;

            $accessor = PropertyAccess::createPropertyAccessor();
            $accessor->setValue($this->configuration_to_be_updated, $field, new \DateTime());

            if ($this->preflush_done === true) {
                $dm->persist($this->configuration_to_be_updated);
                $this->configuration_to_be_updated = null;
                $this->preflush_done = false;
                $dm->flush();
            }
        }
    }


    /**
     * 
     * @param  LifecycleEventArgs $eventArgs [description]
     * @return [type]                        [description]
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $dm = $eventArgs->getDocumentManager();

        if ($document instanceof Translation) {
            $this->updateConfigurationTimestampField($dm, 'translation_updated_at');
        }
    }

    /**
     * 
     * @param  LifecycleEventArgs $eventArgs [description]
     * @return [type]                        [description]
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $dm = $eventArgs->getDocumentManager();

        if ($document instanceof Translation) {
            $this->updateConfigurationTimestampField($dm, 'translation_updated_at');
        }
    }

    /**
     * [preRemove description]
     * @param  LifecycleEventArgs $eventArgs [description]
     * @return [type]                        [description]
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $dm = $eventArgs->getDocumentManager();

        if ($document instanceof Translation) {
            $this->updateConfigurationTimestampField($dm, 'translation_updated_at');
        }
    }

    /**
     * [postFlush description]
     * @param  LifecycleEventArgs $eventArgs [description]
     * @return [type]                        [description]
     */
    public function preFlush(PreFlushEventArgs $eventArgs)
    {
        $dm = $eventArgs->getDocumentManager();
        
        if (!is_null($this->configuration_to_be_updated)) {
            $class = $dm->getClassMetadata(get_class($this->configuration_to_be_updated));
            $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($class, $this->configuration_to_be_updated);
        }

        $this->preflush_done = true;
    }
}
