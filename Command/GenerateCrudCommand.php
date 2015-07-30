<?php

namespace Redking\Bundle\CoreRestBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;

use Redking\Bundle\CoreRestBundle\Generator\CrudGenerator;

class GenerateCrudCommand extends GeneratorCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('document', '', InputOption::VALUE_REQUIRED, 'The document class name to initialize (shortcut notation)'),
                new InputOption('bundle', '', InputOption::VALUE_REQUIRED, 'The bundle where to generate'),
                new InputOption('no-controller', null, InputOption::VALUE_NONE, "Don't generate controller"),
                new InputOption('no-service', null, InputOption::VALUE_NONE, "Don't add service definition"),
                new InputOption('no-test', null, InputOption::VALUE_NONE, "Don't generate test file"),
                new InputOption('no-route', null, InputOption::VALUE_NONE, "Don't add route definition"),
            ))
            ->setDescription('Generates a crud rest controller for a Doctrine mongodb document')
            ->setHelp(<<<EOT
The <info>redking:core-rest:generate:crud</info> command generates a crud rest controller for a Doctrine mongodb document

<info>php app/console redking:core-rest:generate:crud AcmeBlogBundle:Post</info>

EOT
            )
            ->setName('redking:core-rest:generate:crud')
            ->setAliases(array('redking:core-rest:generate:crud'))
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln(sprintf('<info>Generation of Crud Rest Controller for "%s" Document in "%s" bundle.</info>', $input->getOption('document'), $input->getOption('bundle')));
        $output->writeln('');

        $document = $input->getOption('document');
        $bundle = $input->getOption('bundle');
        $bundle = Validators::validateBundleName($bundle);
        try {
            $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
        }

        // Get document metadata
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $class_metadata = $dm->getClassMetadata($document);

        $generator = $this->getGenerator($this->getContainer()->get('kernel')->getBundle('RedkingCoreRestBundle'));

        if ($input->getOption('no-controller') === false) {
            $generator->generate($bundle, $class_metadata);
            $output->writeln('Generating the CRUD code: <info>OK</info>');
        }

        if ($input->getOption('no-service') === false) {
            $generator->generateServices($bundle, $class_metadata);
            $output->writeln('Generating the services: <info>OK</info>');
        }

        if ($input->getOption('no-test') === false) {
            $generator->generateTests($bundle, $class_metadata);
            $output->writeln('Generating the functional test: <info>OK</info>');
        }

        if ($input->getOption('no-route') === false) {
            $generator->generateRouting($bundle, $class_metadata);
            $output->writeln('Add routing: <info>OK</info>');
        }
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Redking CRUD Rest generator');

        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

        while (true) {
            $document = $dialog->askAndValidate($output, $dialog->getQuestion('The Document shortcut name', $input->getOption('document')), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'), false, $input->getOption('document'));

            list($bundle, $document) = $this->parseShortcutNotation($document);


            try {
                $b = $this->getContainer()->get('kernel')->getBundle($bundle);

                if (file_exists($b->getPath().'/Document/'.str_replace('\\', '/', $document).'.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Document "%s:%s" does not exists</>.', $bundle, $document));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }
        $input->setOption('document', $bundle.':'.$document);

        if ($input->getOption('bundle') == '') {
            $input->setOption('bundle', $bundle);
        }
        while (true) {
            $bundle = $dialog->askAndValidate($output, $dialog->getQuestion('The Bundle where the generation takes place', $input->getOption('bundle')), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateBundleName'), false, $input->getOption('bundle'));

            try {
                $b = $this->getContainer()->get('kernel')->getBundle($bundle);
                break;
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }
        $input->setOption('document', $bundle.':'.$document);

    }

    protected function createGenerator()
    {
        return new CrudGenerator($this->getContainer()->get('filesystem'));
    }

    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }
}
