<?php
namespace Redking\Bundle\CoreRestBundle\Tests\Controller;

use Redking\Bundle\CoreRestBundle\Tests\Controller\CoreRestControllerTest;

class TranslationsControllerTest extends CoreRestControllerTest
{
    
    protected static function defineBasics()
    {
        self::$api_prefix = '/api/v1/translations';
        self::$test_id    = 'test';
    }

    protected static function defineParams()
    {
        self::$post_invalid_params = array(
            'id'      => self::$test_id,
            'support' => 'invalid_support',
            'fr'      => 'test'
            );

        self::$post_params = array(
            'id'      => self::$test_id,
            'support' => 'iphone',
            'fr'      => 'test'
            );
   
        self::$put_invalid_params = array(
            'support' => 'test',
            );

        self::$put_params = array(
            'support' => 'android',
            'fr'      => 'test_edited'
            );
    }

    protected static function definePostExpected()
    {
        self::$post_expected = new \stdClass;
        self::$post_expected->id      = self::$test_id;
        self::$post_expected->support = 'iphone';
        self::$post_expected->fr      = 'test';
    }

    protected static function definePutExpected()
    {
        self::$put_expected          = clone self::$post_expected;
        self::$put_expected->fr      = 'test_edited';
        self::$put_expected->support = 'android';
    }

    
}
