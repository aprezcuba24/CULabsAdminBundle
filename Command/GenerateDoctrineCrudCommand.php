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
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
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

<info>php app/console doctrine:culabsgenerate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin</info>
<info>php app/console doctrine:culabsgenerate:crud --entity=AcmeBlogBundle:Blog/Post --route-prefix=blog_post_admin</info>

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

        $part = explode(':', $entityClass);
        $entityClass_short = $part[1];

        $this->generateFilterForm($bundle, $entityClass_short, $metadata);
        $this->generateModel($bundle, $entityClass_short, $metadata);
        $output->writeln('Generating the Form code: <info>OK</info>');

        parent::execute($input, $output);
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
        return $this->modelGenerator;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        $dialog = $this->getQuestionHelper();

        $theme = $input->getOption('theme');
        $output->writeln(array(
            '',
            'Determine the theme to use for the generated CRUD.',
            '',
        ));
        $question = new Question($dialog->getQuestion('Select the theme', 'default'), 'default');
        $theme = $dialog->ask($input, $output, $question);
        $input->setOption('theme', $theme);

        $test_environment = $input->getOption('test-environment');
        $output->writeln(array(
            '',
            'Determine the test environment to use for CRUD.',
            '',
        ));
        $question = new Question($dialog->getQuestion('Select the test environment', 'behat'), 'behat');
        $test_environment = $dialog->ask($input, $output, $question);
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
