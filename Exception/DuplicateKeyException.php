<?php

namespace Redking\Bundle\CoreRestBundle\Exception;

class DuplicateKeyException extends \Exception
{
    
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        if (preg_match("/index\:\s(\w+)\.(\w+)\.(.+)\sdup\skey/", $message, $matchs)) {
            $database = $matchs[1];
            $table    = $matchs[2];
            $field    = ltrim(trim($matchs[3]), '$');
            $field = preg_replace('/^(.+)(\_\d+)$/i', '$1', $field);
            $field = str_replace('Canonical', '', $field);
            $message = 'An object with the same attribute "'.$field.'" exists in database, can not proceed.';
        }
        parent::__construct($message, $code, $previous);
    }

}
