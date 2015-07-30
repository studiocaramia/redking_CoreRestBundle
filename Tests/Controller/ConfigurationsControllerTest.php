<?php
namespace Redking\Bundle\CoreRestBundle\Tests\Controller;

use Redking\Bundle\CoreRestBundle\Tests\Controller\CoreRestControllerTest;

class ConfigurationsControllerTest extends CoreRestControllerTest
{
    
    protected static $post_invalid_params_platform; 
    protected static $post_params_platform; 
    protected static $post_params_platform_expected; 
    protected static $put_params_platform; 
    protected static $put_params_platform_expected; 

    protected static function defineBasics()
    {
        self::$api_prefix = '/api/v1/configuration';
    }

    public function getRessourceBaseUrl($id)
    {
        return self::$api_prefix;
    }

    protected static function defineParams()
    {
        self::$post_invalid_params = array(
            'email_support' => 'invalid_email',
            );

        self::$post_params = array(
            'email_support'   => 'test_support@phpunit.com',
            'email_marketing' => 'test_market@phpunit.com',
            'offline_message' => 'Offline Message',
            );

        self::$put_invalid_params = array(
            'email_support'   => 'invalid_email',
            );

        self::$put_params = array(
            'email_support'   => 'test_support_edited@phpunit.com',
            );
        
        self::$post_invalid_params_platform = array(
            'url_store' => ''
            );

        self::$post_params_platform = array(
            'url_store' => 'http://www.playstore.com',
            'store_version' => '1.0.0',
            );

        self::$put_params_platform = array(
            'url_store' => 'http://www.playstore.com/v2',
            'block_version' => '0.9',
            );

        
    }

    protected static function definePostExpected()
    {
        self::$post_expected                        = new \stdClass;
        self::$post_expected->email_support         = 'test_support@phpunit.com';
        self::$post_expected->email_marketing       = 'test_market@phpunit.com';
        self::$post_expected->offline_message       = 'Offline Message';
        self::$post_expected->translation_updated_at = time();
        self::$post_expected->offline               = false;
        self::$post_expected->iphone                = array();
        self::$post_expected->android               = array();
        self::$post_expected->ipad                  = array();
        self::$post_expected->web                   = array();
    }

    protected static function definePutExpected()
    {
        self::$put_expected                = clone self::$post_expected;
        self::$put_expected->email_support = 'test_support_edited@phpunit.com';
    }

    protected static function definePostPlatformExpected()
    {
        self::$post_params_platform_expected = clone self::$put_expected;
        $platform = array(json_decode(json_encode(self::$post_params_platform)));
        self::$post_params_platform_expected->android = $platform;
    }

    protected static function definePutPlatformExpected()
    {
        self::$put_params_platform_expected = clone self::$post_params_platform_expected;
        self::$put_params_platform_expected->android[0]->url_store = self::$put_params_platform['url_store'];
        self::$put_params_platform_expected->android[0]->block_version = self::$put_params_platform['block_version'];
    }

  
    public function testListEmptyAction(){parent::testListEmptyAction();}
    public function testPostWithNoParamsAction(){parent::testPostWithNoParamsAction();}
    public function testPostParamInvalidAction(){parent::testPostParamInvalidAction();}
    public function testPostAction(){return parent::testPostAction();}
    public function testPutIdInvalidAction(){parent::testPutIdInvalidAction();}
    
    /**
     * @depends testPostAction
     */
    public function testPutParamInvalidAction($id){parent::testPutParamInvalidAction($id);}
    /**
     * @depends testPostAction
     */
    public function testPutAction($id){parent::testPutAction($id);}

    
    /**
     * @depends testPostAction
     */
    public function testPostConfigurationPlatformInvalidNameAction()
    {
        $this->post(self::$api_prefix.'s/invalid_name/platforms');
        $response = $this->assertJsonResponse($this->client->getResponse(), 400);
    }

    /**
     * @depends testPostAction
     */
    public function testPostConfigurationPlatformInvalidParamAction()
    {
        $this->post(self::$api_prefix.'s/android/platforms', self::$post_invalid_params_platform);
        $response = $this->assertJsonResponse($this->client->getResponse(), 400);
    }

    /**
     * @depends testPostAction
     */
    public function testPostConfigurationPlatformAction()
    {
        self::definePostPlatformExpected();
        $this->post(self::$api_prefix.'s/android/platforms', self::$post_params_platform);
        $response = $this->assertJsonResponse($this->client->getResponse(), 200);

        if (property_exists($response, 'id') && is_null(self::$test_id)) 
        {
            self::$post_params_platform_expected->id = $response->id;
        }
        
        $this->assertEquals(self::$post_params_platform_expected, $response);
        return $response;
    }

    /**
     * @depends testPostConfigurationPlatformAction
     */
    public function testPutConfigurationPlatformAction()
    {
        self::definePutPlatformExpected();

        $this->put(self::$api_prefix.'s/android/platforms/'.self::$post_params_platform['store_version'], self::$put_params_platform);
        $response = $this->assertJsonResponse($this->client->getResponse(), 200);
        
        if (property_exists($response, 'id') && is_null(self::$test_id)) 
        {
            self::$put_params_platform_expected->id = $response->id;
        }
        
        $this->assertEquals(self::$put_params_platform_expected, $response);
    }

    public function testDeleteConfigurationPlatformAction()
    {
        $this->delete(self::$api_prefix.'s/android/platforms/'.self::$post_params_platform['store_version']);

        $response = $this->assertJsonResponse($this->client->getResponse(), 200);

        $this->assertEquals(self::$delete_response, $response);
    }

    /**
     * @depends testPostAction
     */
    public function testDeleteAction($id)
    {
        $this->assertTrue(true);
    }

    
}
