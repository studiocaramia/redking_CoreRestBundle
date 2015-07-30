<?php
namespace Redking\Bundle\CoreRestBundle\Helper;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FormattedCommandHelper extends ContainerAwareCommand
{

    /**
     * @var OutputInterface
     */
    protected $output;
    
    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @var Monolog/Logger
     */
    protected $logger;

    protected function initHandlers(OutputInterface $output)
    {
        $this->output     = $output;
        $this->formatter  = $this->getHelperSet()->get('formatter');
        $this->is_verbose = ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL);
        $this->logger     = $this->getContainer()->get('logger');
    }

    /**
     * Display banner
     * @param  string|array  $text          [description]
     * @param  boolean $force_display [description]
     * @return [type]                 [description]
     */
    public function banner($text, $force_display = false)
    {
        if ($this->is_verbose || $force_display == true) {
            $this->output->writeln("\n");
            $formattedBlock = $this->formatter->formatBlock($text, 'bg=blue', true);
            $this->output->writeln($formattedBlock);
            $this->output->writeln("\n");
        }
    }

    /**
     * Output message in a section
     * @param  [type] $section_name [description]
     * @param  [type] $message      [description]
     * @return [type]               [description]
     */
    protected function outputSection($section_name, $message)
    {
        $formattedLine = $this->formatter->formatSection(
            $section_name,
            $message
        );
        if ($this->is_verbose) {
            $this->output->writeln($formattedLine);
        }
        $this->log('['.$section_name.'] '.$message, 'info');
    }

    /**
     * Affiche un block dans la commande
     * @param  string  $message       [description]
     * @param  [string]  $type          [description]
     * @param  [boolean] $force_display [description]
     * @return [type]                 [description]
     */
    protected function outputMessage($message, $type = 'info', $force_display = false)
    {
        if ($this->is_verbose || $force_display == true) {
            $messages = array($message);
            $formattedBlock = $this->formatter->formatBlock($messages, $type);
            $this->output->writeln($formattedBlock);
        }
        $this->log($message, $type);
    }

    /**
     * Output error block message
     * @param  string $message [description]
     * @param  boolean $force_display      [description]
     * @return [type]          [description]
     */
    protected function outputError($message, $force_display = false)
    {
        $this->outputMessage($message, 'error', $force_display);
    }

    /**
     * Send message to log
     * @param  string $message [description]
     * @param  string $type    [description]
     * @return [type]          [description]
     */
    protected function log($message, $type)
    {
        switch($type) {
            case 'comment':
                $this->logger->info($message);
                break;
            case 'error':
                $this->logger->error($message);
                break;
            case 'info':
            default:
                $this->logger->debug($message);
                break;
        }
    }
}
