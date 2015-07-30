<?php

if (!is_file($autoloadFile = __DIR__.'/../vendor/autoload.php')) {
    throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install --dev"?');
}

$loader = require $autoloadFile;

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

if (getenv('BOOTSTRAP_INIT_DB_ENV') !== false && substr($_SERVER['SCRIPT_NAME'], -7) != 'console') {
    // Suppression de la base
    passthru(sprintf(
        'php "%s/Fixtures/app/console" doctrine:mongodb:schema:drop --env=%s',
        __DIR__,
        getenv('BOOTSTRAP_INIT_DB_ENV')
    ));
    // Création de la base avec les indexes
    passthru(sprintf(
        'php "%s/Fixtures/app/console" doctrine:mongodb:schema:update --env=%s',
        __DIR__,
        getenv('BOOTSTRAP_INIT_DB_ENV')
    ));
}

return $loader;

