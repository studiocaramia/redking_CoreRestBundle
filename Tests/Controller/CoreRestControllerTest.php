<?php
namespace Redking\Bundle\CoreRestBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class CoreRestControllerTest extends WebTestCase
{
    private $controller;

    protected $client;

    protected static $kernel;
    protected static $fixtures_loaded = false;
    protected static $api_prefix;

    protected static $timestamp_fields_to_ignore = array('created_at', 'updated_at', 'date');
    protected static $delete_response;
    
    protected static $test_id;
    protected static $post_invalid_params;
    protected static $post_params;
    protected static $post_expected;
    protected static $put_invalid_params;
    protected static $put_params;
    protected static $put_expected;

    protected static $oauth_token;

    /**
     * Executé avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        self::$kernel = self::createKernel();
        self::$kernel->boot();

        static::defineBasics();
        static::defineParams();
        
        self::$delete_response = new \stdClass();
        self::$delete_response->success = true;

        static::loadFixtures();
    }

    /**
     * Charge les fixtures d'un Test
     * @return [type]         [description]
     */
    public static function loadFixtures()
    {
        $current_path = null;
        $fixtures_bundle_exists = false;
        self::$fixtures_loaded = false;

        // Parcours des bundle pour déterminer le répertoire du bundle courant et si le bundle des fixtures est chargé
        foreach(self::$kernel->getBundles() as $bundle) {
            if ($bundle->getName() == 'DoctrineFixturesBundle') {
                $fixtures_bundle_exists = true;
            }
            if (strpos(get_called_class(), $bundle->getNamespace()) === 0) {
                $current_path = $bundle->getPath();
            }
        }
        
        // Chargement du répertoire de fixtures concernant le test si il existe
        if (!is_null($current_path) && $fixtures_bundle_exists === true) {
            $test_name = explode('\\', get_called_class());
            $test_name = array_pop($test_name);

            $fixture_test_dir = $current_path.'/Tests/Fixtures/'.$test_name;
            $fixture_global_test_dir = $current_path.'/Tests/Fixtures/Global';

            if (file_exists($fixture_test_dir) && is_dir($fixture_test_dir)) {
                $fixtures = '--fixtures='.$fixture_test_dir;
                if (is_dir($fixture_global_test_dir)) {
                    $fixtures .= ' --fixtures='.$fixture_global_test_dir;
                }
                passthru(sprintf(
                    'php "%s/console" doctrine:mongodb:fixtures:load --env=%s --append %s',
                    self::$kernel->getRootDir(),
                    getenv('BOOTSTRAP_INIT_DB_ENV'),
                    $fixtures
                ));
                self::$fixtures_loaded = true;
            }
        }
    }

    /**
     * Vidage de la base si des fixtures ont été utilisées
     * @return [type] [description]
     */
    public static function tearDownAfterClass()
    {
        if (self::$fixtures_loaded == true) {
            passthru(sprintf(
                'php "%s/console" doctrine:mongodb:schema:drop --env=%s',
                self::$kernel->getRootDir(),
                getenv('BOOTSTRAP_INIT_DB_ENV')
            ));
            passthru(sprintf(
                'php "%s/console" doctrine:mongodb:schema:create --env=%s',
                self::$kernel->getRootDir(),
                getenv('BOOTSTRAP_INIT_DB_ENV')
            ));
        }
    }

    protected static function defineBasics(){}
    protected static function defineParams(){}
    protected static function definePostExpected(){}
    protected static function definePutExpected(){}

    /**
     * Executé avant chaque test
     */
    public function setUp() 
    {
        $this->client = static::createClient();
    }

    /**
     * Retourne des headers additionnels (pour l'auth par ex)
     * @return array
     */
    public function getAdditionalHeader()
    {
        return array();
    }

    /**
     * Retourne le token associé à un user
     * @param  \Redking\Bundle\SonataUserBundle\Document\User $user [description]
     * @return [type]                                               [description]
     */
    public function getOAuthToken(\Redking\Bundle\SonataUserBundle\Document\User $user)
    {
        if (is_null(static::$oauth_token) && !is_null($user->getOAuthClient())) {
            $params = [
                'client_id'     => $user->getOAuthClient()->getPublicId(),
                'client_secret' => $user->getOAuthClient()->getSecret(),
                'grant_type'    => 'client_credentials',
            ];
            $url = '/oauth/v2/token';

            $this->client->request('GET', $url, $params);
            $response = $this->assertJsonResponse($this->client->getResponse(), 200);

            $this->assertTrue(property_exists($response, 'access_token'));

            static::$oauth_token = $response->access_token;
        }

        return static::$oauth_token;
    }

    public function getOAuthTokenHeader(\Redking\Bundle\SonataUserBundle\Document\User $user)
    {
        return [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->getOAuthToken($user)
        ];
    }


    /**
     * Execute une Request GET
     * @param  string $url    
     * @param  array  $params 
     * @return mixed         
     */
    public function get($url, $params = array())
    {
        $this->client->request(
            'GET', 
            $url, 
            $params,
            array(),
            $this->getAdditionalHeader()
            );
    }

    /**
     * Execute une Request POST
     * @param  string $url    
     * @param  array  $params 
     * @return mixed         
     */
    protected function post($url, $params = array())
    {
        if (!is_string($params)) {
            $params = json_encode($params, JSON_FORCE_OBJECT);
        }
        try {
            $this->client->request(
                'POST', 
                $url, 
                array(), 
                array(), 
                array_merge(
                    [
                        'HTTP_ACCEPT'  => 'application/json', 
                        'CONTENT_TYPE' => 'application/json'
                    ],
                    $this->getAdditionalHeader()
                ), 
                $params
            );
        } 
        catch (\InvalidArgumentException $ex) {}
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Execute une Request PUT
     * @param  string $url    
     * @param  array  $params 
     * @return mixed         
     */
    protected function put($url, $params = array())
    {
        if (!is_string($params)) {
            $params = json_encode($params, JSON_FORCE_OBJECT);
        }
        try {
            $this->client->request(
                'PUT', 
                $url, 
                array(), 
                array(), 
                array_merge(
                    [
                        'HTTP_ACCEPT'  => 'application/json', 
                        'CONTENT_TYPE' => 'application/json'
                    ],
                    $this->getAdditionalHeader()
                ), 
                $params
            );
        } 
        catch (\InvalidArgumentException $ex) {}
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Execute une Request DELETE
     * @param  string $url    
     * @param  array  $params 
     * @return mixed         
     */
    protected function delete($url, $params = array())
    {
        try {
            $this->client->request(
                'DELETE', 
                $url, 
                $params,
                array(),
                $this->getAdditionalHeader()
            );
        } 
        catch (\InvalidArgumentException $ex) {}
        catch (Exception $e) {
            throw $e;
        }
    }

    protected function assertJsonResponse(
        $response, 
        $statusCode = 200, 
        $checkValidJson =  true, 
        $contentType = 'application/json'
    )
    {
        $this->assertEquals(
            $statusCode, $response->getStatusCode(),
            (json_decode($response->getContent()) !== false) ? json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) : $response->getContent()
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', $contentType),
            $response->headers
        );

        if ($checkValidJson) {
            $decode = json_decode($response->getContent());
            $this->assertTrue(($decode != null && $decode != false),
                'is response valid json: [' . $response->getContent() . ']'
            );
            return $decode;
        }
    }

    public function getController()
    {
        return $this->controller;
    }

    public function createGuzzleClient()
    {
        return new \GuzzleHttp\Client();
    }

    public function getRessourceBaseUrl($id)
    {
        return static::$api_prefix.'/'.$id;
    }

    /**
     * Wrapper sur le test d'égalité entre deux objects pour corriger les timestamps différents de quelques secondes
     * @param  [type] $expected [description]
     * @param  [type] $actual   [description]
     * @param  string $message  [description]
     * @return [type]           [description]
     */
    public function assertObjectEquals($expected, $actual, $message = '')
    {
        list($expected, $actual) = $this->correctTimestampsBetweenObjects($expected, $actual);
        return $this->assertEquals($expected, $actual, $message);
    }

    protected function correctTimestampsBetweenObjects($expected, $actual)
    {
        $marge = 5;
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach($expected as $field => $value) {
            // Traitement des objets
            if (is_object($value) && isset($actual->$field)) {
                list($expected->$field, $actual->$field) = $this->correctTimestampsBetweenObjects($expected->$field, $actual->$field);
            } 
            // Traitement des tableaux pouvant contenir des objets
            elseif (is_array($value) && isset($actual->$field) && is_array($actual->$field) && count($expected->$field) == count($actual->$field)) {

                for ($i = 0; $i < count($value); $i++) {
                    if (is_array($value[$i]) || is_object($value[$i])) {
                        list($expected_element, $actual_element) = $this->correctTimestampsBetweenObjects($accessor->getValue($expected, $field.'['.$i.']'), $accessor->getValue($actual, $field.'['.$i.']'));
                        $accessor->setValue($expected, $field.'['.$i.']', $expected_element);
                        $accessor->setValue($actual, $field.'['.$i.']', $actual_element);
                    }
                }
            }
            if (!is_null($actual) && in_array($field, static::$timestamp_fields_to_ignore) && property_exists($actual, $field) && !is_null($accessor->getValue($actual, $field))) {
                $diff = $accessor->getValue($expected, $field) - $accessor->getValue($actual, $field);
                if ($diff <= $marge || $diff >= $marge*-1) {
                    $accessor->setValue($expected, $field, $accessor->getValue($actual, $field));
                }
            }
        }

        return [$expected, $actual];
    }

    /**
     * Vérifie que la dernière activité correspond aux paramètres
     * @param  string $action          
     * @param  string $from_user_id    
     * @param  string $object_id          
     * @param  mixed  $child_object_id
     * @param  mixed  $child_object_type
     * @param  mixed  $to_user_id
     * @return void
     */
    public function assertLastActivity($action, $from_user_id, $object_id, $object_type, $child_object_id = null, $child_object_type = null, $to_user_id = null)
    {
        $params = [
            'limit'           => 1,
            '_return_objects' => 0,
        ];
        $this->get('/api/v1/activities', $params);
        $response = $this->assertJsonResponse($this->client->getResponse(), 200);

        $expected = new \stdClass();
        $expected->count = (is_object($response) && property_exists($response, 'count')) ? $response->count : 1;
        $result = new \stdClass();
        $result->action      = $action;
        $result->from_user   = $from_user_id;
        $result->object      = $object_id;
        $result->object_type = $object_type;
        $result->date        = time();

        if (is_object($response) && property_exists($response, 'results') && is_array($response->results) && count($response->results) == 1) {
            $result->id = $response->results[0]->id;
        }

        if (!is_null($child_object_id)) {
            $result->child_object = $child_object_id;
        }
        if (!is_null($child_object_type)) {
            $result->child_object_type = $child_object_type;
        }

        $expected->results = [$result];

        $this->assertObjectEquals($expected, $response);
    }


    /**
     * ***************************************************************
     *  _____         _
     * |_   _|__  ___| |_ ___
     *   | |/ _ \/ __| __/ __|
     *   | |  __/\__ \ |_\__ \
     *   |_|\___||___/\__|___/
     * 
     * ***************************************************************
     */
    
    /**
     * Test liste vide
     * @return [type] [description]
     */
    public function testListEmptyAction()
    {
        $this->get(static::$api_prefix);
        $response = $this->assertJsonResponse($this->client->getResponse(), 200);
        
        $expected = new \stdClass;
        $expected->count = 0;
        $expected->results = array();

        $this->assertEquals($expected, $response);
    }

    /**
     * Test post vide
     * @return [type] [description]
     */
    public function testPostWithNoParamsAction()
    {
        // Test post vide
        $this->post(static::$api_prefix);
        $response = $this->assertJsonResponse($this->client->getResponse(), 400);
    }

    /**
     * Test de post invalid valide
     * @return [type] [description]
     */
    public function testPostParamInvalidAction()
    {
        $this->post(static::$api_prefix, static::$post_invalid_params);
        $response = $this->assertJsonResponse($this->client->getResponse(), 400);
    }

    /**
     * Test de post valide
     * @return [type] [description]
     */
    public function testPostAction()
    {
        static::definePostExpected();
        $this->post(static::$api_prefix, static::$post_params);
        $response = $this->assertJsonResponse($this->client->getResponse(), 200);

        
        if (property_exists($response, 'id') && is_null(static::$test_id)) 
        {
            static::$post_expected->id = $response->id;
        }
        $this->assertObjectEquals(static::$post_expected, $response);
        return $response->id;
    }

    /**
     * Test de PUT invalid sur un id inexistant
     * @return [type] [description]
     */
    public function testPutIdInvalidAction()
    {
        $this->put(static::$api_prefix.'/invalid_id', static::$put_invalid_params);
        $response = $this->assertJsonResponse($this->client->getResponse(), 404);
    }

    /**
     * Test de PUT invalid sur des paramètres
     * @return [type] [description]
     * @depends testPostAction
     */
    public function testPutParamInvalidAction($id)
    {
        $this->put($this->getRessourceBaseUrl($id), static::$put_invalid_params);
        $response = $this->assertJsonResponse($this->client->getResponse(), 400);
    }

    /**
     * Test de put valid
     * @return [type] [description]
     * @depends testPostAction
     */
    public function testPutAction($id)
    {
        static::definePutExpected();
        $this->put($this->getRessourceBaseUrl($id), static::$put_params);
        $response = $this->assertJsonResponse($this->client->getResponse(), 200);
        
        $put_expected = static::$put_expected;
        if (property_exists($response, 'id') && is_null(static::$test_id)) 
        {
            $put_expected->id = $response->id;
        }
        $this->assertObjectEquals($put_expected, $response);
    }

    /**
     * Test delete valid
     * @return [type] [description]
     * @depends testPostAction
     */
    public function testDeleteAction($id)
    {
        $this->delete($this->getRessourceBaseUrl($id));
        $response = $this->assertJsonResponse($this->client->getResponse(), 200);

        $this->assertEquals(self::$delete_response, $response);
    }

}
