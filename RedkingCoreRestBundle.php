<?php

namespace Redking\Bundle\CoreRestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Redking\Bundle\CoreRestBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;

class RedkingCoreRestBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddDependencyCallsCompilerPass());
    }
}
