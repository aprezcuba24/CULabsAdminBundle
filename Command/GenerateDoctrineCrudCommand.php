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

class GenerateDoctrineCrudCommand extends BaseGenerateDoctrineCrudCommand
{
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
        
        return parent::execute($input, $output);
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
