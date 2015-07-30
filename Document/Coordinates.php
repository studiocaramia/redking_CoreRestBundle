<?php

namespace Redking\Bundle\CoreRestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Coordinates
 *
 * @MongoDB\EmbeddedDocument
 * @Serializer\ExclusionPolicy("none")
 */
class Coordinates
{
    /**
     * @var float
     *
     * @MongoDB\Float
     * @Serializer\Type("double")
     */
    protected $longitude;

    /**
     * @var float
     *
     * @MongoDB\Float
     * @Serializer\Type("double")
     */
    protected $latitude;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Serializer\Type("string")
     * @Assert\Country
     */
    protected $country;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Serializer\Type("string")
     * @MongoDB\Index
     */
    protected $city;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Serializer\Type("string")
     * @MongoDB\Index
     */
    protected $postal_code;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Serializer\Type("string")
     */
    protected $adress;

    /**
     * Set latitude
     *
     * @param float $latitude
     * @return self
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * Get latitude
     *
     * @return float $latitude
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return self
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * Get longitude
     *
     * @return float $longitude
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    public function toArray()
    {
        return array($this->getLatitude(), $this->getLongitude());
    }

    public function toRequest()
    {
        return array(
            'latitude'    => $this->getLatitude(), 
            'longitude'   => $this->getLongitude(),
            'country'     => $this->getCountry(),
            'city'        => $this->getCity(),
            'postal_code' => $this->getPostalCode(),
            'adress'      => $this->getAdress(),
            );
    }

    public function __toString()
    {
        return $this->getLatitude().' X '.$this->getLongitude();
    }

    /**
     * Set country
     *
     * @param string $country
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return string $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return self
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Get city
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set postalCode
     *
     * @param string $postalCode
     * @return self
     */
    public function setPostalCode($postalCode)
    {
        $this->postal_code = $postalCode;
        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string $postalCode
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * Set adress
     *
     * @param string $adress
     * @return self
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;
        return $this;
    }

    /**
     * Get adress
     *
     * @return string $adress
     */
    public function getAdress()
    {
        return $this->adress;
    }
}
