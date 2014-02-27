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
        $this
            ->setDefinition(array(
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('route-prefix', '', InputOption::VALUE_REQUIRED, 'The route prefix'),
                new InputOption('theme', '', InputOption::VALUE_OPTIONAL, 'The Theme name', 'default'),
                new InputOption('with-write', '', InputOption::VALUE_NONE, 'Whether or not to generate create, new and delete actions'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation'),
                new InputOption('overwrite', '', InputOption::VALUE_NONE, 'Do not stop the generation if crud controller already exist, thus overwriting all generated files'),
            ))
            ->setDescription('Generates a CRUD based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>doctrine:culabsgenerate:crud</info> command generates a CRUD based on a Doctrine entity.

The default command only generates the list and show actions.

<info>php app/console doctrine:culabsgenerate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin</info>

Using the --with-write option allows to generate the new, edit and delete actions.

<info>php app/console doctrine:generate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin --with-write</info>

Every generated file is based on a template. There are default templates but they can be overriden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/CULabsAdminBundle/skeleton/crud
APP_PATH/Resources/CULabsAdminBundle/skeleton/crud</info>

And

<info>__bundle_path__/Resources/CULabsAdminBundle/skeleton/form
__project_root__/app/Resources/CULabsAdminBundle/skeleton/form</info>
EOT
            )
            ->setName('doctrine:culabsgenerate:crud')
            ->setAliases(array('culabs:generate:crud'))
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
