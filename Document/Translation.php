<?php

namespace Redking\Bundle\CoreRestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation\ExclusionPolicy as ExclusionPolicy;
use JMS\Serializer\Annotation\Expose as Expose;
use JMS\Serializer\Annotation\Accessor as Accessor;
use Symfony\Component\Validator\Constraints as Assert;

use Redking\Bundle\ODMTranslatorBundle\Mapping\Annotation\Translatable;

/**
 * Translation
 *
 * @MongoDB\Document(collection="translation")
 * @ExclusionPolicy("all")
 */
class Translation
{
    /**
     * @var string
     *
     * @MongoDB\Id(strategy="NONE")
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Expose
     */
    protected $id;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Assert\Choice(choices = {"iphone", "ipad", "android", "web"})
     * @Expose
     */
    protected $support;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Expose
     */
    protected $screen;

    /**
     * @var string $content
     *
     * @MongoDB\String
     * @Translatable
     * @Accessor(getter="getContentTranslations")
     * @Expose
     */
    protected $content;

    // Import traits (define a "translations" hash attribute to save data)
    use \Redking\Bundle\ODMTranslatorBundle\Document\TranslatableTrait;

    public static function getSupportChoices()
    {
        return array(
            'iphone'  => 'iphone',
            'ipad'    => 'ipad',
            'android' => 'android',
            'web'     => 'web',
            );
    }

    /**
     * Set id
     *
     * @param custom_id $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return custom_id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set support
     *
     * @param string $support
     * @return self
     */
    public function setSupport($support)
    {
        $this->support = $support;
        return $this;
    }

    /**
     * Get support
     *
     * @return string $support
     */
    public function getSupport()
    {
        return $this->support;
    }

    /**
     * Set screen
     *
     * @param string $screen
     * @return self
     */
    public function setScreen($screen)
    {
        $this->screen = $screen;
        return $this;
    }

    /**
     * Get screen
     *
     * @return string $screen
     */
    public function getScreen()
    {
        return $this->screen;
    }

    public function __toString()
    {
        return $this->getId();
    }

    /**
     * Set content
     *
     * @param string $content
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get content
     *
     * @return string $content
     */
    public function getContent()
    {
        return $this->content;
    }

    public function getContentTranslations()
    {
        return $this->getTranslationsFor('content');
    }
}
