<?php

namespace Redking\Bundle\CoreRestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation\ExclusionPolicy as ExclusionPolicy;
use JMS\Serializer\Annotation\Expose as Expose;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Configuration
 *
 * @MongoDB\Document(collection="configuration", repositoryClass="Redking\Bundle\CoreRestBundle\Document\Repository\ConfigurationRepository")
 * @ExclusionPolicy("all")
 */
class Configuration
{
    
    /**
     * @var integer
     *
     * @MongoDB\Id(strategy="INCREMENT")
     * @Expose
     */
    protected $id;

    /**
     * @var boolean
     *
     * @MongoDB\Boolean
     * @Expose
     */
    protected $offline;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Expose
     */
    protected $offline_message;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Assert\Email
     * @Assert\NotNull
     * @Expose
     */
    protected $email_support;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Assert\Email
     * @Assert\NotNull
     * @Expose
     */
    protected $email_marketing;

    /**
     * @var date $translation_updated_at
     * Date de mise à jour des translations
     *
     * @MongoDB\Date
     * @Expose
     */
    protected $translation_updated_at;

    /**
     * Configuration pour les iphones
     * 
     * @var ConfigurationPlatform
     *
     * @MongoDB\EmbedMany(targetDocument="Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform")
     * @Expose
     */
    protected $iphone;

    /**
     * Configuration pour les android
     * 
     * @var ConfigurationPlatform
     *
     * @MongoDB\EmbedMany(targetDocument="Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform")
     * @Expose
     */
    protected $android;

    /**
     * Configuration pour les ipad
     * 
     * @var ConfigurationPlatform
     *
     * @MongoDB\EmbedMany(targetDocument="Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform")
     * @Expose
     */
    protected $ipad;

    /**
     * Configuration pour le web
     * 
     * @var ConfigurationPlatform
     *
     * @MongoDB\EmbedMany(targetDocument="Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform")
     * @Expose
     */
    protected $web;

    public function __construct()
    {
        $this->iphone  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->android = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ipad    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->web     = new \Doctrine\Common\Collections\ArrayCollection();

        $this->translation_updated_at = new \DateTime();
    }
    
    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set offline
     *
     * @param boolean $offline
     * @return self
     */
    public function setOffline($offline)
    {
        $this->offline = $offline;
        return $this;
    }

    /**
     * Get offline
     *
     * @return boolean $offline
     */
    public function getOffline()
    {
        return $this->offline;
    }

    /**
     * Set offlineMessage
     *
     * @param string $offlineMessage
     * @return self
     */
    public function setOfflineMessage($offlineMessage)
    {
        $this->offline_message = $offlineMessage;
        return $this;
    }

    /**
     * Get offlineMessage
     *
     * @return string $offlineMessage
     */
    public function getOfflineMessage()
    {
        return $this->offline_message;
    }

    /**
     * Set emailSupport
     *
     * @param string $emailSupport
     * @return self
     */
    public function setEmailSupport($emailSupport)
    {
        $this->email_support = $emailSupport;
        return $this;
    }

    /**
     * Get emailSupport
     *
     * @return string $emailSupport
     */
    public function getEmailSupport()
    {
        return $this->email_support;
    }

    /**
     * Set emailMarketing
     *
     * @param string $emailMarketing
     * @return self
     */
    public function setEmailMarketing($emailMarketing)
    {
        $this->email_marketing = $emailMarketing;
        return $this;
    }

    /**
     * Get emailMarketing
     *
     * @return string $emailMarketing
     */
    public function getEmailMarketing()
    {
        return $this->email_marketing;
    }

    /**
     * Add iphone
     *
     * @param Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $iphone
     */
    public function addIphone(\Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $iphone)
    {
        $this->iphone[] = $iphone;
    }

    /**
     * Remove iphone
     *
     * @param Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $iphone
     */
    public function removeIphone(\Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $iphone)
    {
        $this->iphone->removeElement($iphone);
    }

    /**
     * Get iphone
     *
     * @return Doctrine\Common\Collections\Collection $iphone
     */
    public function getIphone()
    {
        return $this->iphone;
    }

    /**
     * Add android
     *
     * @param Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $android
     */
    public function addAndroid(\Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $android)
    {
        $this->android[] = $android;
    }

    /**
     * Remove android
     *
     * @param Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $android
     */
    public function removeAndroid(\Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $android)
    {
        $this->android->removeElement($android);
    }

    /**
     * Get android
     *
     * @return Doctrine\Common\Collections\Collection $android
     */
    public function getAndroid()
    {
        return $this->android;
    }

    /**
     * Add ipad
     *
     * @param Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $ipad
     */
    public function addIpad(\Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $ipad)
    {
        $this->ipad[] = $ipad;
    }

    /**
     * Remove ipad
     *
     * @param Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $ipad
     */
    public function removeIpad(\Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $ipad)
    {
        $this->ipad->removeElement($ipad);
    }

    /**
     * Get ipad
     *
     * @return Doctrine\Common\Collections\Collection $ipad
     */
    public function getIpad()
    {
        return $this->ipad;
    }

    /**
     * Add web
     *
     * @param Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $web
     */
    public function addWeb(\Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $web)
    {
        $this->web[] = $web;
    }

    /**
     * Remove web
     *
     * @param Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $web
     */
    public function removeWeb(\Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform $web)
    {
        $this->web->removeElement($web);
    }

    /**
     * Get web
     *
     * @return Doctrine\Common\Collections\Collection $web
     */
    public function getWeb()
    {
        return $this->web;
    }

    /**
     * 
     * @param  string $name            [description]
     * @param  string $store_version [description]
     * @return \Redking\Bundle\CoreRestBundle\Document\ConfigurationPlatform
     */
    public function getPlatformByNameAndVersion($name, $store_version)
    {
        if (!isset($this->$name)) {
            throw new \Exception("Unsupported platform name");
        }
        $collection = $this->$name->filter(function($entity) use($store_version){
            return ($entity->getStoreVersion() == $store_version);
        });
        return ($collection->count() == 1) ? $collection->first() : null;
    }

    /**
     * Set translationUpdateAt
     *
     * @param date $translationUpdateAt
     * @return self
     */
    public function setTranslationUpdatedAt($translationUpdatedAt)
    {
        $this->translation_updated_at = $translationUpdatedAt;
        return $this;
    }

    /**
     * Get translationUpdateAt
     *
     * @return date $translationUpdateAt
     */
    public function getTranslationUpdatedAt()
    {
        return $this->translation_updated_at;
    }

    public function __toString()
    {
        if (!is_null($this->getId())) {
            return ''.$this->getId();
        } else {
            return '';
        }
    }
}
