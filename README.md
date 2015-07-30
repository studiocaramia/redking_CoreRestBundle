RedkingCoreRestBundle
=====================

[ ![Codeship Status for redkingteam/RedkingCoreRestBundle](https://www.codeship.io/projects/bce62480-46ed-0132-16d3-124ef0ebe42e/status)](https://www.codeship.io/projects/45433)

This bundle facilitates the creation of CRUD Rest methods based on MongoDB Documents.

## Installation

Add bundle to composer.json

```js
{
    "require": {
        "redking/core-rest-bundle": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "git@bitbucket.org:redkingteam/redkingcorerestbundle.git"
        }
    ]
}
```

Register the bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Redking\Bundle\CoreRestBundle\RedkingCoreRestBundle(),
        
        new FOS\RestBundle\FOSRestBundle(),
        new JMS\SerializerBundle\JMSSerializerBundle(),
        new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
        new DavidBadura\FixturesBundle\DavidBaduraFixturesBundle(),
        new DavidBadura\FakerBundle\DavidBaduraFakerBundle(),
        new Redking\Bundle\ODMTranslatorBundle\RedkingODMTranslatorBundle(),
        new Sbk\Bundle\CronBundle\SbkCronBundle(),
    );
}
```

## Usage

### Generate MongoDB Document

Define the private members of your documents, then generate accessors : 

```bash
php app/console doctrine:generate doctrine:mongodb:generate:documents AcmeDemoBundle
```

### Generate CRUD

Use the built-in command to generate the crud system based on your document : 

```bash
php app/console redking:core-rest:generate:crud
```

This command generates the following files : 

`AcmeDemoBundle/Controller/<YourDocument>Controller.php`
`AcmeDemoBundle/Form/<YourDocument>Type.php`

This new classes are declared as services in 
`AcmeDemoBundle/Resources/config/services.xml`

And the routing is declared in 
`AcmeDemoBundle/Resources/config/routing_rest.xml`


### Customization

You can customize the generated controller by creating/modifying/deleting some methods.

You can also define your handler which must inherit `Redking\Bundle\CoreRestBundle\Handler\BaseHandler`
and be declared in the service configuration file


## Activity recorder

You can track the life cycle of objects by recording theses actions in a activity table
Exemple : 

```yaml
# app/config/config.yml
redking_core_rest:
    document_for_activities:
        Vendor\Bundle\CoreBundle\Document\Post:
            actions: [insert, update, delete]
```
