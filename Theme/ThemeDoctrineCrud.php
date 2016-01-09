<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Renier Ricardo Figueredo <aprezcuba24@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CULabs\AdminBundle\Theme;

use CULabs\AdminBundle\Generator\DoctrineFilterGenerator;
use CULabs\AdminBundle\Generator\DoctrineFormGenerator;
use CULabs\AdminBundle\Generator\DoctrineModelGenerator;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use CULabs\AdminBundle\Generator\DoctrineFiterGenerator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class ThemeDoctrineCrud implements ThemeDoctrineCrudInterface
{
    protected $generator;
    protected $theme_name;
    protected $formGenerator;
    protected $modelGenerator;
    protected $filterFormGenerator;
    protected $filesystem;
    protected $kernel;

    public function __construct(KernelInterface $kernel, Filesystem $filesystem, $theme_name)
    {
        $this->filesystem = $filesystem;
        $this->theme_name = $theme_name;
        $this->kernel     = $kernel;
    }

    /**
     * @return DoctrineCrudGenerator
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getGenerator()
    {
        if (null === $this->generator) {

            $dir_path = $this->skeletonPath().'crud';
            if (!file_exists($dir_path))
                throw new InvalidArgumentException(sprintf('%s is no directory', $dir_path));

            $this->generator = new DoctrineCrudGenerator($this->kernel, $this->filesystem, $dir_path);
        }

        return $this->generator;
    }

    /**
     * @return DoctrineFormGenerator
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getFormGenerator()
    {
        if (null === $this->formGenerator) {

            $dir_path = $this->skeletonPath().'form';
            if (!file_exists($dir_path))
                throw new InvalidArgumentException(sprintf('%s is no directory', $dir_path));

            $this->formGenerator = new DoctrineFormGenerator($this->kernel, $this->filesystem);
            $this->formGenerator->setSkeletonDirs($this->skeletonPath());
        }

        return $this->formGenerator;
    }

    /**
     * @return DoctrineModelGenerator
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getModelGenerator()
    {
        if (null === $this->modelGenerator) {

            $dir_path = $this->skeletonPath().'model';
            if (!file_exists($dir_path))
                throw new InvalidArgumentException(sprintf('%s is no directory', $dir_path));

            $this->modelGenerator = new DoctrineModelGenerator($this->kernel, $this->filesystem, $dir_path);
        }

        return $this->modelGenerator;
    }

    /**
     * @return DoctrineFiterGenerator
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getFilterFormGenerator()
    {
        if (null === $this->filterFormGenerator) {

            $dir_path = $this->skeletonPath().'filter';
            if (!file_exists($dir_path))
                throw new InvalidArgumentException(sprintf('%s is no directory', $dir_path));

            $this->filterFormGenerator = new DoctrineFilterGenerator($this->kernel, $this->filesystem);
            $this->filterFormGenerator->setSkeletonDirs($this->skeletonPath());
        }

        return $this->filterFormGenerator;
    }

    /**
     * @return string
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    protected function skeletonPath()
    {
        $part_name = explode(':', $this->theme_name);
        if (count($part_name) != 2)
            throw new InvalidArgumentException('The thene name most similar to CULabsAdminBundle:default');

        $bundle = $this->kernel->getBundle($part_name[0]);

        $dir_path = $bundle->getPath().sprintf('/Resources/skeleton/%s/',$part_name[1]);
        if (!file_exists($dir_path))
            throw new InvalidArgumentException(sprintf('%s is no directory', $dir_path));

        return $dir_path;
    }
}
