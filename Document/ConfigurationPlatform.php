<?php

namespace Redking\Bundle\CoreRestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation\ExclusionPolicy as ExclusionPolicy;
use JMS\Serializer\Annotation\Expose as Expose;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Configuration
 *
 * @MongoDB\EmbeddedDocument
 * @ExclusionPolicy("all")
 */
class ConfigurationPlatform
{

    /**
     * @var string
     *
     * @MongoDB\String
     * @Assert\Url()
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Expose
     */
    private $url_store;

    /**
     * @var float
     *
     * @MongoDB\Id(strategy="NONE")
     * @MongoDB\String
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Expose
     */
    private $store_version;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Expose
     */
    private $store_message;

    /**
     * @var float
     *
     * @MongoDB\String
     * @Expose
     */
    private $block_version;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Expose
     */
    private $block_message;

    /**
     * Set urlStore
     *
     * @param string $urlStore
     * @return self
     */
    public function setUrlStore($urlStore)
    {
        $this->url_store = $urlStore;
        return $this;
    }

    /**
     * Get urlStore
     *
     * @return string $urlStore
     */
    public function getUrlStore()
    {
        return $this->url_store;
    }

    /**
     * Set storeVersion
     *
     * @param float $storeVersion
     * @return self
     */
    public function setStoreVersion($storeVersion)
    {
        $this->store_version = $storeVersion;
        return $this;
    }

    /**
     * Get storeVersion
     *
     * @return float $storeVersion
     */
    public function getStoreVersion()
    {
        return $this->store_version;
    }

    /**
     * Set storeMessage
     *
     * @param string $storeMessage
     * @return self
     */
    public function setStoreMessage($storeMessage)
    {
        $this->store_message = $storeMessage;
        return $this;
    }

    /**
     * Get storeMessage
     *
     * @return string $storeMessage
     */
    public function getStoreMessage()
    {
        return $this->store_message;
    }

    /**
     * Set blockVersion
     *
     * @param float $blockVersion
     * @return self
     */
    public function setBlockVersion($blockVersion)
    {
        $this->block_version = $blockVersion;
        return $this;
    }

    /**
     * Get blockVersion
     *
     * @return float $blockVersion
     */
    public function getBlockVersion()
    {
        return $this->block_version;
    }

    /**
     * Set blockMessage
     *
     * @param string $blockMessage
     * @return self
     */
    public function setBlockMessage($blockMessage)
    {
        $this->block_message = $blockMessage;
        return $this;
    }

    /**
     * Get blockMessage
     *
     * @return string $blockMessage
     */
    public function getBlockMessage()
    {
        return $this->block_message;
    }
}
