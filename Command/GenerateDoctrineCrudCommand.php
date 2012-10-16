<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Alejandro Pérez Cuba <aprezcuba24@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CULabs\AdminBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand as BaseGenerateDoctrineCrudCommand;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use CULabs\AdminBundle\Theme\ThemeDoctrineCrudCollectionInterface;
use CULabs\AdminBundle\Generator\DoctrineFiterGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Validators;

class GenerateDoctrineCrudCommand extends BaseGenerateDoctrineCrudCommand
{
    protected $filterFormGenerator;
    
    protected function configure()
    {        
        parent::configure();
        
        $this
             ->addOption('theme', null, InputOption::VALUE_OPTIONAL, 'The Theme name', 'default')
             ->setName('doctrine:culabsgenerate:crud')
             ->setAliases(array('culabs:doctrine:crud'))
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $theme_colletion = $this->getContainer()->get('cu_labs_admin.theme.collection');
        
        if (!$theme_colletion instanceof ThemeDoctrineCrudCollectionInterface) {
            throw new InvalidArgumentException('The service "cu_labs_admin.theme.collection" most implements CULabs\AdminBundle\Theme\ThemeDoctrineCrudCollectionInterface');
        }
        
        $this->setGenerator($theme_colletion->getTheme($input->getOption('theme'))->getGenerator());
        $this->setFormGenerator($theme_colletion->getTheme($input->getOption('theme'))->getFormGenerator());
        $this->setFilterFormGenerator($theme_colletion->getTheme($input->getOption('theme'))->getFilterFormGenerator());
        
        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);
        
        $entityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($bundle).'\\'.$entity;
        $bundle      = $this->getContainer()->get('kernel')->getBundle($bundle);
        $metadata    = $this->getEntityMetadata($entityClass);
        
        $this->generateFilterForm($bundle, $entity, $metadata);
        $output->writeln('Generating the Form code: <info>OK</info>');
        
        return parent::execute($input, $output);
    }
    public function setFilterFormGenerator(DoctrineFiterGenerator $filterFormGenerator)
    {
        $this->filterFormGenerator = $filterFormGenerator;
    }
    private function generateFilterForm($bundle, $entity, $metadata)
    {
        try {
            $this->getFilterFormGenerator()->generate($bundle, $entity, $metadata[0]);
        } catch (\RuntimeException $e ) {
            // form already exists
        }
    }
    protected function getFilterFormGenerator()
    {
        if (null === $this->filterFormGenerator) {
            $this->filterFormGenerator = new DoctrineFiterGenerator($this->getContainer()->get('filesystem'),  __DIR__.'/../Resources/skeleton/fiter');
        }

        return $this->filterFormGenerator;
    }
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);
        
        $dialog = $this->getDialogHelper();
        
        $theme = $input->getOption('theme');
        $output->writeln(array(
            '',
            'Determine the theme to use for the generated CRUD.',
            '',
        ));
        $theme = $dialog->ask($output, $dialog->getQuestion('Select the theme', 'default'), 'default');
        $input->setOption('theme', $theme);
    }
}
