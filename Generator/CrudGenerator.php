<?php 

namespace Redking\Bundle\CoreRestBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
/**
* Crud Generator
*/
class CrudGenerator extends Generator
{
    
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(BundleInterface $bundle, ClassMetadata $class_metadata)
    {
        $document_name = explode('\\',$class_metadata->getName());
        $document_name = array_pop($document_name);
        
        
        // Controller Generator
        $dir = $bundle->getPath();
        $controllerFile = $dir.'/Controller/'.$document_name.'sController.php';
        if (file_exists($controllerFile)) {
            throw new \RuntimeException(sprintf('Controller "%s" already exists', $document_name));
        }

        $parameters = array(
            'namespace'     => $bundle->getNamespace(),
            'bundle'        => $bundle->getName(),
            'controller'    => $document_name.'sController',
            'document_name' => $document_name,
        );

        $this->renderFile('controller/Controller.php.twig', $controllerFile, $parameters);

        
        // FormType Generator
        $formTypeFile = $dir.'/Form/'.$document_name.'Type.php';
        if (file_exists($formTypeFile)) {
            throw new \RuntimeException(sprintf('Form Type "%s" already exists', $document_name));
        }
        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'bundle'    => $bundle->getName(),
            'formtype'  => $document_name.'Type',
            'document'  => $class_metadata->getName(),
            'fields'    => $class_metadata->getFieldNames(),
        );
        $this->renderFile('form/FormType.php.twig', $formTypeFile, $parameters);

    }

    /**
     * Add controller and handler services in the bundle
     * @param  BundleInterface $bundle         [description]
     * @param  ClassMetadata   $class_metadata [description]
     * @return [type]                          [description]
     */
    public function generateServices(BundleInterface $bundle, ClassMetadata $class_metadata)
    {
        $basename = substr($bundle->getName(), 0, -6);
        $extension_alias = Container::underscore($basename);

        $document_name = explode('\\',$class_metadata->getName());
        $document_name = array_pop($document_name);
        $document_id = Container::underscore($document_name);

        $document_service_alias = $extension_alias.'.'.$document_id;

        $parameters = array(
            $document_service_alias.'.class'            => $class_metadata->getName(),
            $document_service_alias.'.form.class'       => $bundle->getNamespace().'\\Form\\'.$document_name.'Type',
            $document_service_alias.'.handler.class'    => 'Redking\Bundle\CoreRestBundle\Handler\BaseHandler',
            $document_service_alias.'.controller.class' => $bundle->getNamespace().'\\Controller\\'.$document_name.'sController'
        );


        $file = $bundle->getPath().'/Resources/config/services_rest.xml';

        if (file_exists($file)) {
            $content = file_get_contents($file);
        } elseif (!is_dir($dir = $bundle->getPath().'/Resources/config')) {
            mkdir($dir);
        }

        if (!isset($content)) {
            // new file
            $content = <<<EOT
<?xml version="1.0" ?>
<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
<parameters />
<services />
</container>
EOT;
        }

        $sxe = simplexml_load_string($content);

        if (!isset($sxe->{'parameters'}) || !isset($sxe->{'services'})) {
            throw new \RunTimeException(sprintf('We cannot update file "%s", as there is no parameters or services nodes', $file));
        }
        
        // Ajout des paramètres
        foreach ($parameters as $parameter_key => $parameter_value)
        {
            $parameter = $sxe->{'parameters'}->addChild('parameter', $parameter_value);
            $parameter->addAttribute('key', $parameter_key);
        }

        // Ajout du service handler
        $service_handler = $sxe->{'services'}->addChild('service');
        $service_handler->addAttribute('id', $document_service_alias.'.handler');
        $service_handler->addAttribute('class', '%'.$document_service_alias.'.handler.class'.'%');
        $arg1 = $service_handler->addChild('argument');
        $arg1->addAttribute('type', 'service');
        $arg1->addAttribute('id', 'doctrine_mongodb.odm.document_manager');
        $arg2 = $service_handler->addChild('argument', '%'.$document_service_alias.'.class'.'%');
        $arg3 = $service_handler->addChild('argument', '%'.$document_service_alias.'.form.class'.'%');
        $arg4 = $service_handler->addChild('argument');
        $arg4->addAttribute('type', 'service');
        $arg4->addAttribute('id', 'form.factory');
        $arg5 = $service_handler->addChild('argument');
        $arg5->addAttribute('type', 'service');
        $arg5->addAttribute('id', 'logger');
        $arg6 = $service_handler->addChild('argument');
        $arg6->addAttribute('type', 'service');
        $arg6->addAttribute('id', 'event_dispatcher');
        $arg7 = $service_handler->addChild('tag');
        $arg7->addAttribute('name', 'monolog.logger');
        $arg7->addAttribute('channel', 'redking_rest');

        // Ajout du service controller
        $service_controller = $sxe->{'services'}->addChild('service');
        $service_controller->addAttribute('id', $document_service_alias.'.controller');
        $service_controller->addAttribute('class', '%'.$document_service_alias.'.controller.class'.'%');
        $arg1 = $service_controller->addChild('argument');
        $arg1->addAttribute('type', 'service');
        $arg1->addAttribute('id', $document_service_alias.'.handler');
        $call = $service_controller->addChild('call');
        $call->addAttribute('method', 'setContainer');
        $call_arg = $call->addChild('argument');
        $call_arg->addAttribute('type', 'service');
        $call_arg->addAttribute('id', 'service_container');
        
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($sxe->asXML());
        $content = $dom->saveXML();

        $flink = fopen($file, 'w');
        if ($flink) {
            $write = fwrite($flink, $content);

            if ($write) {
                fclose($flink);
            } else {
                throw new \RunTimeException(sprintf('We cannot write into file "%s", has that file the correct access level?', $file));
            }
        } else {
            throw new \RunTimeException(sprintf('Problems with generating file "%s", did you gave write access to that directory?', $file));
        }
    }

    /**
     * Add controller declaration in routing
     * @param  BundleInterface $bundle         [description]
     * @param  ClassMetadata   $class_metadata [description]
     * @return [type]                          [description]
     */
    public function generateRouting(BundleInterface $bundle, ClassMetadata $class_metadata)
    {
        $basename = substr($bundle->getName(), 0, -6);
        $extension_alias = Container::underscore($basename);

        $document_name = explode('\\',$class_metadata->getName());
        $document_name = array_pop($document_name);
        $document_id = Container::underscore($document_name);

        $file = $bundle->getPath().'/Resources/config/routing_rest.xml';

        if (file_exists($file)) {
            $content = file_get_contents($file);
        } elseif (!is_dir($dir = $bundle->getPath().'/Resources/config')) {
            mkdir($dir);
        }

        if (!isset($content)) {
            // new file
            $content = <<<EOT
<?xml version="1.0" encoding="UTF-8" ?>
<routes xmlns="http://friendsofsymfony.github.com/schema/rest"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://friendsofsymfony.github.com/schema/rest https://raw.github.com/FriendsOfSymfony/FOSRestBundle/master/Resources/config/schema/routing/rest_routing-1.0.xsd">
</routes>
EOT;
        }

        $sxe = simplexml_load_string($content);

        $route = $sxe->addChild('import');
        $route->addAttribute('resource', $extension_alias.'.'.$document_id.'.controller');
        $route->addAttribute('type', 'rest');
        $route->addAttribute('name-prefix', 'api_');

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($sxe->asXML());
        $content = $dom->saveXML();

        $flink = fopen($file, 'w');
        if ($flink) {
            $write = fwrite($flink, $content);

            if ($write) {
                fclose($flink);
            } else {
                throw new \RunTimeException(sprintf('We cannot write into file "%s", has that file the correct access level?', $file));
            }
        } else {
            throw new \RunTimeException(sprintf('Problems with generating file "%s", did you gave write access to that directory?', $file));
        }
    }

    public function generateTests(BundleInterface $bundle, ClassMetadata $class_metadata)
    {
        $document_name = explode('\\',$class_metadata->getName());
        $document_name = array_pop($document_name);
        
        
        // Controller Generator
        $dir = $bundle->getPath();
        $testControllerFile = $dir.'/Tests/Controller/'.$document_name.'sControllerTest.php';
        if (file_exists($testControllerFile)) {
            throw new \RuntimeException(sprintf('Controller Test "%s" already exists', $document_name));
        }

        $parameters = array(
            'namespace'     => $bundle->getNamespace(),
            'bundle'        => $bundle->getName(),
            'controller'    => $document_name.'sControllerTest'
        );

        $this->renderFile('test/TestController.php.twig', $testControllerFile, $parameters);

        

    }
}
