<?php

namespace Redking\Bundle\CoreRestBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Bundle\FrameworkBundle\Controller\Controller
;
use FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\FOSRestController,
    FOS\RestBundle\Controller\Annotations,
    FOS\RestBundle\Request\ParamFetcherInterface,
    FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ApiController extends FOSRestController
{
    /**
     * Description de l'APÏ
     *
     * Chaque méthode de l'API est retournée sous la forme d'un objet de la forme suivante : 
     *
     * <pre>
     * {
     *   "resource": "/api/url_of_the_method",
     *   "method": "POST",
     *   "collection": "objects_name",
     *   "description": "Description of the method",
     *   "parameters": {
     *     {
     *       "parameter": "object_id",
     *       "dataType": "choice",
     *       "required": true,
     *       "description": "Identifiant d'un objet lié à la ressource",
     *       "format": "MongoDB ID (string)",
     *       "source_collection": "linked_objects",
     *       "saveAs": "string"
     *     },
     *     {
     *       "parameter": "my_string",
     *       "dataType": "string",
     *       "required": false,
     *       "description": "Chaine de caractère classique",
     *       "saveAs": "string"
     *     },
     *     {
     *       "parameter": "my_integer",
     *       "dataType": "integer",
     *       "required": true,
     *       "description": "Entier",
     *       "saveAs": "integer"
     *     },
     *     {
     *       "parameter": "my_timestamp",
     *       "dataType": "date",
     *       "required": true,
     *       "description": "Entier",
     *       "saveAs": "integer"
     *       
     *     },
     *     {
     *       "parameter": "my_float",
     *       "dataType": "float",
     *       "required": true,
     *       "description": "Nombre à virgule",
     *       "saveAs": "float"
     *     },
     *     {
     *       "parameter": "my_boolean",
     *       "dataType": "boolean",
     *       "required": true,
     *       "description": "Booléen"
     *       "saveAs": "boolean"
     *     },
     *     {
     *       "parameter": "my_choice",
     *       "dataType": "choice",
     *       "required": true,
     *       "description": "Champ à valeurs prédéfinies (exposées dans format)",
     *       "saveAs": "integer"
     *       "format": [
     *         {
     *           "key": 0,
     *           "value": "choice 1"
     *         },
     *         {
     *           "key": 1,
     *           "value": "choice 2"
     *         }
     *       ]
     *     }
     *   }
     * }
     * </pre>
     * 
     *
     * @param Request $request the request object
     * 
     * @Annotations\Get("/api/description")
     * @ApiDoc(
     *   resource = true,
     *   description = "Description de l'API",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   })
     */
    public function getDescriptionAction(Request $request)
    {
        $extractedDoc = $this->get('nelmio_api_doc.extractor.api_doc_extractor')->all();
        
        foreach ($extractedDoc as $wsdoc) {
            $webservice = array();
            
            // On explose la route pour récupérer la première partie qui doit être le nom de la collection
            $route_prefix = $this->container->getParameter('redking_core_rest.api_route_prefix');
            $route_parts = explode('/', str_replace($route_prefix.'/', '', $wsdoc['resource']));

            $doc = $wsdoc['annotation']->toArray();

            $webservice['resource']    = $wsdoc['resource'];
            $webservice['collection']  = $route_parts[0];
            $webservice['method']      = $doc['method'];
            $webservice['description'] = $doc['description'];

            if (isset($doc['filters']) && count($doc['filters']) > 0) {
                $webservice['filters'] = $doc['filters'];
            }
            // Retraitement des paramètres
            if (isset($doc['parameters']) && count($doc['parameters']) > 0) {
                $params = array();
                foreach ($doc['parameters'] as $param_name => $parameter) {
                    $parameter['parameter'] = $param_name;
                    if (isset($parameter['readonly'])) {
                        unset($parameter['readonly']);
                    }
                    if (isset($parameter['actualType'])) {
                        unset($parameter['actualType']);
                    }
                    if (isset($parameter['subType'])) {
                        unset($parameter['subType']);
                    }
                    if (isset($parameter['format'])) {
                        if ($param_name == 'locale') {
                            unset($parameter['format']);
                        } else {
                            $decoded = json_decode($parameter['format'], true);
                            if (!is_null($decoded)) {
                                $formats = array();
                                foreach($decoded as $key => $value)
                                {
                                    if (is_numeric($key)) {
                                        $parameter['saveAs'] = 'integer';
                                        $key = (int) $key;
                                    } else {
                                        $parameter['saveAs'] = 'string';
                                    }
                                    $formats[] = array('key' => $key, 'value' => $value);
                                }
                                $parameter['format'] = $formats;
                            }
                        }
                    }
                    $params[] = $parameter;
                }
                $webservice['parameters'] = $params;
            }
            $webservices[] = $webservice;
        }

        $view = $this->view($webservices);
        return $this->handleView($view);
    }

    /**
     * @Annotations\Get("/api/load_test_fixtures")
     * @ApiDoc(
     *   resource = true,
     *   description = "Vide la base et charge les données de test",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   })
     */
    public function loadFixturesAction(Request $request)
    {
        $fixture_manager = $this->get('davidbadura_fixtures.fixture_manager');
        $logger = new \DavidBadura\FixturesBundle\Logger\NullLogger();
        try {
            
            // Suppression et re-création des collections
            $shema_manager = $this->get('doctrine.odm.mongodb.document_manager')->getSchemaManager();
            $shema_manager->dropCollections();
            $shema_manager->createCollections();
            $shema_manager->ensureIndexes();
            
            
            // Insertion des fixtures
            $fixture_manager->load(array(null, null, null), $logger);
            

            $view = $this->view(array('success' => true));
            return $this->handleView($view);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
