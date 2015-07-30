<?php

namespace Redking\Bundle\CoreRestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Activiy logs
 *
 * @MongoDB\Document(collection="activity")
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\AccessorOrder("custom", custom = {"id", "date", "from_user", "to_user", "object", "object_type", "child_object", "child_object_type", "action"})
 */
class Activity
{
    const ACTION_INSERT = 'insert';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_LINK   = 'link';
    const ACTION_UNLINK = 'unlink';

    /**
     * @MongoDB\Id(strategy="INCREMENT")
     * @Serializer\Expose
     */
    protected $id;

    /**
     * @MongoDB\Date
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Serializer\Expose
     */
    protected $date;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Symfony\Component\Security\Core\User\AdvancedUserInterface")
     * @Serializer\Expose
     * @Serializer\Accessor(getter="getFromUserDrawback")
     * @Serializer\Groups({"link"})
     */
    protected $from_user;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Symfony\Component\Security\Core\User\AdvancedUserInterface")
     * @Serializer\Expose
     * @Serializer\Accessor(getter="getToUserDrawback")
     * @Serializer\Groups({"link"})
     */
    protected $to_user;

    /**
     * @MongoDB\ReferenceOne
     * @Serializer\Expose
     * @Serializer\Accessor(getter="getObjectDrawback")
     * @Serializer\Groups({"link"})
     */
    protected $object;

    /**
     * @MongoDB\String
     * @Serializer\Expose
     */
    protected $object_type;

    /**
     * @MongoDB\ReferenceOne
     * @Serializer\Expose
     * @Serializer\Accessor(getter="getChildObjectDrawback")
     * @Serializer\Groups({"link"})
     */
    protected $child_object;

    /**
     * @MongoDB\String
     * @Serializer\Expose
     */
    protected $child_object_type;

    /**
     * @MongoDB\String
     * @Serializer\Expose
     */
    protected $action;

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setDate(new \DateTime());
    }

    /**
     * Get id
     *
     * @return int_id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param date $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set fromUser
     *
     * @param Symfony\Component\Security\Core\User\AdvancedUserInterface $fromUser
     * @return self
     */
    public function setFromUser(\Symfony\Component\Security\Core\User\AdvancedUserInterface $fromUser)
    {
        $this->from_user = $fromUser;
        return $this;
    }

    /**
     * Get fromUser
     *
     * @return Symfony\Component\Security\Core\User\AdvancedUserInterface $fromUser
     */
    public function getFromUser()
    {
        return $this->from_user;
    }

    /**
     * Set toUser
     *
     * @param Symfony\Component\Security\Core\User\AdvancedUserInterface $toUser
     * @return self
     */
    public function setToUser(\Symfony\Component\Security\Core\User\AdvancedUserInterface $toUser)
    {
        $this->to_user = $toUser;
        return $this;
    }

    /**
     * Get toUser
     *
     * @return Symfony\Component\Security\Core\User\AdvancedUserInterface $toUser
     */
    public function getToUser()
    {
        return $this->to_user;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return self
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get action
     *
     * @return string $action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Returns getObject if present, only the id otherwise
     * @return [type] [description]
     */
    public function getObjectDrawback()
    {
        if (!is_null($this->getObject())) {
            try {
                $this->getObject()->__load();
                return $this->getObject();
            } catch (\Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                return $this->getObject()->getId();
            }
        }
    }

    /**
     * Returns getChildObject if present, only the id otherwise
     * @return [type] [description]
     */
    public function getChildObjectDrawback()
    {
        if (!is_null($this->getChildObject())) {
            try {
                $this->getChildObject()->__load();
                return $this->getChildObject();
            } catch (\Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                return $this->getChildObject()->getId();
            }
        }
    }

    /**
     * Returns getFromUser if present, only the id otherwise
     * @return [type] [description]
     */
    public function getFromUserDrawback()
    {
        if (!is_null($this->getFromUser())) {
            try {
                $this->getFromUser()->__load();
                return $this->getFromUser();
            } catch (\Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                return $this->getFromUser()->getId();
            }
        }
    }

    /**
     * Returns getToUser if present, only the id otherwise
     * @return [type] [description]
     */
    public function getToUserDrawback()
    {
        if (!is_null($this->getToUser())) {
            try {
                $this->getToUser()->__load();
                return $this->getToUser();
            } catch (\Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
                return $this->getToUser()->getId();
            }
        }
    }

    /**
     * Set object
     *
     * @param $object
     * @return self
     */
    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }

    /**
     * Get object
     *
     * @return $object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set objectType
     *
     * @param string $objectType
     * @return self
     */
    public function setObjectType($objectType)
    {
        $this->object_type = $objectType;
        return $this;
    }

    /**
     * Get objectType
     *
     * @return string $objectType
     */
    public function getObjectType()
    {
        return $this->object_type;
    }

    /**
     * Set childObject
     *
     * @param $childObject
     * @return self
     */
    public function setChildObject($childObject)
    {
        $this->child_object = $childObject;
        return $this;
    }

    /**
     * Get childObject
     *
     * @return $childObject
     */
    public function getChildObject()
    {
        return $this->child_object;
    }

    /**
     * Set childObjectType
     *
     * @param string $childObjectType
     * @return self
     */
    public function setChildObjectType($childObjectType)
    {
        $this->child_object_type = $childObjectType;
        return $this;
    }

    /**
     * Get childObjectType
     *
     * @return string $childObjectType
     */
    public function getChildObjectType()
    {
        return $this->child_object_type;
    }

    /**
     * [getObjectId description]
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("object")
     * @Serializer\Groups({"id"})
     */
    public function getObjectId()
    {
        if (!is_null($this->getObject())) {
            return $this->getObject()->getId();
        }
    }

    /**
     * [getChildObjectId description]
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("child_object")
     * @Serializer\Groups({"id"})
     */
    public function getChildObjectId()
    {
        if (!is_null($this->getChildObject())) {
            return $this->getChildObject()->getId();
        }
    }

    /**
     * [getFromUserId description]
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("from_user")
     * @Serializer\Groups({"id"})
     */
    public function getFromUserId()
    {
        if (!is_null($this->getFromUser())) {
            return $this->getFromUser()->getId();
        }
    }

    /**
     * [getToUserId description]
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("to_user")
     * @Serializer\Groups({"id"})
     */
    public function getToUserId()
    {
        if (!is_null($this->getToUser())) {
            return $this->getToUser()->getId();
        }
    }
}
