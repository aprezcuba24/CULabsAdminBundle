<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Renier Ricardo Figueredo <aprezcuba24@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CULabs\AdminBundle\Command;

use CULabs\AdminBundle\Generator\DoctrineModelGenerator;
use CULabs\AdminBundle\Theme\DoctrineCrudGeneratorInterface;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use CULabs\AdminBundle\Theme\ThemeDoctrineCrudCollectionInterface;
use CULabs\AdminBundle\Generator\DoctrineFiterGenerator;

class GenerateDoctrineCrudCommand extends \Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand
{
    protected $filterFormGenerator;
    protected $modelGenerator;

    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('bundle', '', InputOption::VALUE_OPTIONAL, 'Bundle where generate the CRUD', 'AppBundle'),
                new InputOption('route-prefix', '', InputOption::VALUE_REQUIRED, 'The route prefix'),
                new InputOption('test-environment', '', InputOption::VALUE_OPTIONAL, 'Test environment, behat or phpunit', 'behat'),
                new InputOption('theme', '', InputOption::VALUE_OPTIONAL, 'The Theme name', 'default'),
                new InputOption('with-write', '', InputOption::VALUE_NONE, 'Whether or not to generate create, new and delete actions'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation'),
                new InputOption('overwrite', '', InputOption::VALUE_NONE, 'Do not stop the generation if crud controller already exist, thus overwriting all generated files'),
            ))
            ->setDescription('Generates a CRUD based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>doctrine:culabsgenerate:crud</info> command generates a CRUD based on a Doctrine entity.

The default command only generates the list and show actions.

<info>php app/console doctrine:culabsgenerate:crud --entity=AcmeBlogBundle/Entity/Post --route-prefix=post_admin</info>

Using the --with-write option allows to generate the new, edit and delete actions.

<info>php app/console doctrine:generate:crud --entity=AcmeBlogBundle/Entity/Post --route-prefix=post_admin --with-write</info>

Every generated file is based on a template. There are default templates but they can be overriden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/CULabsAdminBundle/skeleton/crud
APP_PATH/Resources/CULabsAdminBundle/skeleton/default/crud</info>

And

<info>__bundle_path__/Resources/CULabsAdminBundle/skeleton/default/form
__project_root__/app/Resources/CULabsAdminBundle/skeleton/default/form</info>
EOT
            )
            ->setName('doctrine:culabsgenerate:crud')
            ->setAliases(array('culabs:generate:crud'))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $theme_collection = $this->getContainer()->get('cu_labs_admin.theme.collection');

        if (!$theme_collection instanceof ThemeDoctrineCrudCollectionInterface) {
            throw new InvalidArgumentException('The service "cu_labs_admin.theme.collection" most implements CULabs\AdminBundle\Theme\ThemeDoctrineCrudCollectionInterface');
        }

        $generator = $theme_collection->getTheme($input->getOption('theme'))->getGenerator();
        if (!($generator instanceof DoctrineCrudGeneratorInterface)) {
            throw new \InvalidArgumentException('The generator must implements DoctrineCrudGeneratorInterface');
        }
        $generator->testEnvironment(strtoupper($input->getOption('test-environment')));
        $this->setGenerator($generator);

        $this->setFormGenerator($theme_collection->getTheme($input->getOption('theme'))->getFormGenerator());
        $this->setFilterFormGenerator($theme_collection->getTheme($input->getOption('theme'))->getFilterFormGenerator());
        $this->setModelGenerator($theme_collection->getTheme($input->getOption('theme'))->getModelGenerator());

        $entityClass = str_replace('/', '\\', $input->getOption('entity'));
        $bundle      = $input->getOption('bundle');

        $bundle      = $this->getContainer()->get('kernel')->getBundle($bundle);
        $metadata    = $this->getEntityMetadata($entityClass);

        $part = explode('\Entity\\', $entityClass);
        if (count($part) > 1) {
            $entityClass_short = $part[1];
        }

        $this->generateFilterForm($bundle, $entityClass_short, $metadata);
        $this->generateModel($bundle, $entityClass_short, $metadata);
        $output->writeln('Generating the Form code: <info>OK</info>');

        //-------------------
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $format = Validators::validateFormat($input->getOption('format'));
        $prefix = $this->getRoutePrefix($input, $entityClass_short);
        $withWrite = $input->getOption('with-write');
        $forceOverwrite = $input->getOption('overwrite');

        $dialog->writeSection($output, 'CRUD generation');

        $metadata    = $this->getEntityMetadata($entityClass);

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $entityClass_short, $metadata[0], $format, $prefix, $withWrite, $forceOverwrite);

        $output->writeln('Generating the CRUD code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        // form
        if ($withWrite) {
            $this->generateForm($bundle, $entityClass_short, $metadata);
            $output->writeln('Generating the Form code: <info>OK</info>');
        }

        // routing
        if ('annotation' != $format) {
            $runner($this->updateRouting($dialog, $input, $output, $bundle, $format, $entityClass, $prefix));
        }

        $dialog->writeGeneratorSummary($output, $errors);
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
            $this->filterFormGenerator = new DoctrineFiterGenerator($this->getContainer()->get('filesystem'),  $this->getSkeletonPath('filter'));
        }

        return $this->filterFormGenerator;
    }

    public function setModelGenerator(DoctrineModelGenerator $modelGenerator)
    {
        $this->modelGenerator = $modelGenerator;
    }

    private function generateModel($bundle, $entity, $metadata)
    {
        try {
            $this->getModelGenerator()->generate($bundle, $entity, $metadata[0]);
        } catch (\RuntimeException $e ) {
            // form already exists
        }
    }

    protected function getModelGenerator()
    {
        if (null === $this->modelGenerator) {
            $this->modelGenerator = new DoctrineModelGenerator($this->getContainer()->get('filesystem'),  $this->getSkeletonPath('model'));
        }

        return $this->modelGenerator;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Doctrine2 CRUD generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate CRUD controllers and templates.',
            '',
            'First, you need to give the entity for which you want to generate a CRUD.',
            'You can give an entity that does not exist yet and the wizard will help',
            'you defining it.',
            '',
            'You must use the the complete name of entity <comment>AcmeBlogBundle/Entity/Post</comment>.',
            '',
        ));


        if ($input->hasArgument('entity') && $input->getArgument('entity') != '') {
            $input->setOption('entity', $input->getArgument('entity'));
        }

        $entity = $dialog->ask($output, $dialog->getQuestion('The Entity shortcut name', $input->getOption('entity')), $input->getOption('entity'));
        $input->setOption('entity', $entity);

        if ($input->hasArgument('bundle') && $input->getArgument('bundle') != '') {
            $input->setOption('bundle', $input->getArgument('bundle'));
        }

        $bundle = $dialog->ask($output, $dialog->getQuestion('The bundle name', $input->getOption('bundle')), $input->getOption('bundle'));
        $input->setOption('bundle', $bundle);

        // write?
        $withWrite = $input->getOption('with-write') ?: false;
        $output->writeln(array(
            '',
            'By default, the generator creates two actions: list and show.',
            'You can also ask it to generate "write" actions: new, update, and delete.',
            '',
        ));
        $withWrite = $dialog->askConfirmation($output, $dialog->getQuestion('Do you want to generate the "write" actions', $withWrite ? 'yes' : 'no', '?'), $withWrite);
        $input->setOption('with-write', $withWrite);

        // format
        $format = $input->getOption('format');
        $output->writeln(array(
            '',
            'Determine the format to use for the generated CRUD.',
            '',
        ));
        $format = $dialog->askAndValidate($output, $dialog->getQuestion('Configuration format (yml, xml, php, or annotation)', $format), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'), false, $format);
        $input->setOption('format', $format);

        // route prefix
        $prefix = $this->getRoutePrefix($input, $entity);
        $output->writeln(array(
            '',
            'Determine the routes prefix (all the routes will be "mounted" under this',
            'prefix: /prefix/, /prefix/new, ...).',
            '',
        ));
        $prefix = $dialog->ask($output, $dialog->getQuestion('Routes prefix', '/'.$prefix), '/'.$prefix);
        $input->setOption('route-prefix', $prefix);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a CRUD controller for \"<info>%s:%s</info>\"", $bundle, $entity),
            sprintf("using the \"<info>%s</info>\" format.", $format),
            '',
        ));
        //------------------------

        $dialog = $this->getDialogHelper();

        $theme = $input->getOption('theme');
        $output->writeln(array(
            '',
            'Determine the theme to use for the generated CRUD.',
            '',
        ));
        $theme = $dialog->ask($output, $dialog->getQuestion('Select the theme', 'default'), 'default');
        $input->setOption('theme', $theme);

        $test_environment = $input->getOption('test-environment');
        $output->writeln(array(
            '',
            'Determine the test environment to use for CRUD.',
            '',
        ));
        $test_environment = $dialog->ask($output, $dialog->getQuestion('Select the test environment', 'behat'), 'behat');
        $input->setOption('test-environment', $test_environment);
    }

    protected function getSkeletonPath($dir)
    {
        $bundle = $this->getContainer()->get('kernel')->getBundle('CULabsAdminBundle');
        $dir_path = sprintf('%s/Resources/skeleton/default/%s', $bundle->getPath(), $dir);

        if (!file_exists($dir_path)) {

            throw new InvalidArgumentException(sprintf('%s is no directory', $dir_path));
        }

        return $dir_path;
    }
}
