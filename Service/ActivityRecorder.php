<?php
/**
 * Enregistreur des activities
 */
namespace Redking\Bundle\CoreRestBundle\Service;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class ActivityRecorder
{
    /**
     * ODM Document Manager
     * @var [type]
     */
    protected $dm;

    /**
     * Classe Activity
     * @var [type]
     */
    protected $activity_class;

    public function __construct($doctrine, $activity_class)
    {
        $this->dm             = $doctrine->getManager();
        $this->activity_class = $activity_class;
    }

    /**
     * Enregistrement d'une action
     * @param  object $object    [description]
     * @param  string $action      [description]
     * @param  mixed  $from_user   [description]
     * @param  mixed  $to_user     [description]
     * @param  mixed  $child_object Peut être un objet enfant ou juste le type de cet objet
     * @return [type]              [description]
     */
    public function record($object, $action, $from_user = null, $child_object = null, $to_user = null)
    {
        $instantiator = new \Doctrine\Instantiator\Instantiator();
        
        $activity = $instantiator->instantiate($this->activity_class);

        $activity->setDate(new \DateTime());
        $activity->setObject($object);
        $activity->setObjectType($this->getClassBasename($object));
        $activity->setAction($action);

        if (!is_null($from_user) && $from_user instanceof AdvancedUserInterface) {
            $activity->setFromUser($from_user);
        }

        if (!is_null($to_user) && $to_user instanceof AdvancedUserInterface) {
            $activity->setToUser($to_user);
        }

        if (!is_null($child_object)) {
            if (is_object($child_object)) {
                $activity->setChildObject($child_object);
                $activity->setChildObjectType($this->getClassBasename($child_object));
            } else {
                $activity->setChildObjectType($child_object);
            }
        }

        $this->dm->persist($activity);
        $this->dm->flush();
    }

    /**
     * Retourne le nom de classe de l'objet sans le namespace
     * @param  [type] $object [description]
     * @return [type]         [description]
     */
    protected function getClassBasename($object)
    {
        $class_parts = explode('\\', get_class($object));
        return array_pop($class_parts);
    }
}
