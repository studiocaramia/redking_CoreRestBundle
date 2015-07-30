<?php
/**
 * Redéfinition de la seriaziation d'un DateTime en timestamp pour qu'il soit de l'integer
 */

namespace Redking\Bundle\CoreRestBundle\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;

class DateTimeHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'DateTime',
                'method'    => 'serializeDateTimeToJson',
            ),
        );
    }

    /**
     * Retourne le DateTime en timestamp integer
     * @param  JsonSerializationVisitor $visitor [description]
     * @param  DateTime                 $date    [description]
     * @param  array                    $type    [description]
     * @param  Context                  $context [description]
     * @return [type]                            [description]
     */
    public function serializeDateTimeToJson(JsonSerializationVisitor $visitor, \DateTime $date, array $type, Context $context)
    {
        return (int)$date->format('U');
    }
}
