<?php

/*
 * This file is part of the WizadDoctrineDocBundle.
 *
 * (c) William POTTIER <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wizad\DoctrineDocBundle\Formatter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Eloquent\Blox\AST\DocumentationBlock;
use Eloquent\Blox\BloxParser;
use Symfony\Component\Filesystem\Filesystem;

class IndexFormatter extends MarkdownFormatter
{
    private $entities;


    public function __construct()
    {
        $this->entities = array();
    }

    public function addEntity(EntityFormatter $entity)
    {
        if (!isset($this->entities[$entity->getBundleShortName()])) {
            $this->entities[$entity->getBundleShortName()] = array();
        }

        $this->entities[$entity->getBundleShortName()][] = $entity;

    }

    public function end($outputDirectory)
    {
        $this->build();

        $docFile = $outputDirectory . DIRECTORY_SEPARATOR . 'README.md';
        $this->dump($docFile);
    }

    private function build()
    {
        $this->addTitle('Doctrine Model Documentation');

        foreach ($this->entities as $bundleName => $entities) {

            $this->writeln(sprintf('### %s', $bundleName));

            foreach ($entities as $entityFormatter) {
                /** @var $entityFormatter EntityFormatter */
                $this->writeln(sprintf(' - %s', $this->createLink($bundleName . DIRECTORY_SEPARATOR . $entityFormatter->getName() . '.md', $entityFormatter->getName())));
            }

        }
    }
}