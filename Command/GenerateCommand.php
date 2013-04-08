<?php

/*
 * This file is part of the WizadDoctrineDocBundle.
 *
 * (c) William POTTIER <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wizad\DoctrineDocBundle\Command;

use Wizad\DoctrineDocBundle\Formatter\EntityFormatter;
use Wizad\DoctrineDocBundle\Formatter\IndexFormatter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateCommand extends ContainerAwareCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:generate:documentation')
            ->addArgument('output', InputArgument::REQUIRED, 'Output directory of documentation')
            ->setDescription('Create doctrine model documentation')
            ->setHelp(<<<EOF
The <info>%command.name%</info> create the doctrine model documentation:

  <info>%command.full_name% output</info>
EOF
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        $outputDirectory = realpath($input->getArgument('output'));

        if (!$fs->exists($outputDirectory)) {
            $fs->mkdir($outputDirectory);
        }

        /** @var $em EntityManager */
        $em        = $this->getContainer()->get('doctrine')->getManager();
        $metadatas = $em->getMetadataFactory()->getAllMetadata();

        // Prepare index formatter
        $indexFormatter = new IndexFormatter();

        foreach ($metadatas as $metadata) {
            /** @var $metadata ClassMetadata */
            if (!$metadata->isMappedSuperclass) {
                $output->writeln($metadata->getName());

                $formatter = new EntityFormatter($metadata);
                $formatter->end($outputDirectory);

                $indexFormatter->addEntity($formatter);
            }
        }

        $indexFormatter->end($outputDirectory);
    }

}