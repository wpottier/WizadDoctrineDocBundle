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

class EntityFormatter extends MarkdownFormatter
{
    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $metadatas;

    /**
     * @var DocumentationBlock
     */
    private $docs;

    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * @var \Eloquent\Blox\BloxParser
     */
    private $docBlockParser;


    public function __construct(ClassMetadata $metadatas)
    {
        $this->metadatas      = $metadatas;
        $this->docBlockParser = new BloxParser();

        $this->loadClassMetadata();
        $this->build();
    }

    public function end($outputDirectory)
    {
        $fs        = new Filesystem();
        $bundleDir = $outputDirectory . DIRECTORY_SEPARATOR . $this->getBundleShortName();

        if (!$fs->exists($bundleDir)) {
            $fs->mkdir($bundleDir);
        }

        $docFile = $bundleDir . DIRECTORY_SEPARATOR . $this->getName() . '.md';
        $this->dump($docFile);
    }

    public function getName($typeName = null)
    {
        if (!$typeName) {
            $typeName = $this->metadatas->getName();
        }

        $namespaceParts = explode('\\', $typeName);

        return end($namespaceParts);
    }

    public function getBundleName($name = null)
    {
        if (!$name) {
            $name = $this->metadatas->getName();
        }

        return ($p1 = strpos($ns = $name, '\\')) === false ? $ns :
            substr($ns, 0, ($p2 = strpos($ns, '\\', $p1 + 1)) === false ? strlen($ns) : $p2);
    }

    public function getBundleShortName($name = null)
    {
        return str_replace('\\', '', $this->getBundleName($name));
    }

    private function build()
    {
        $this->addTitle($this->getName());
        $this->writeln(sprintf('*%s*', $this->metadatas->getName()));

        $this->addParagraph($this->docs->summary());


        $this->addSection('Properties');

        $fields = $this->metadatas->fieldMappings;
        $self   = $this;
        usort($fields, function ($a, $b) use ($self) {
            if (isset($a['id']) && $a['id'] && (!isset($b['id']) || $b['id'])) {
                return -1;
            }

            if (isset($b['id']) && $b['id'] && (!isset($a['id']) || $a['id'])) {
                return 1;
            }

            if (isset($a['declared']) && !isset($b['declared'])) {
                return 1;
            }

            if (isset($b['declared']) && !isset($a['declared'])) {
                return -1;
            }

            return strcmp($a['fieldName'], $b['fieldName']);
        });

        // Create documentation for classic fields
        foreach ($fields as $field) {

            $this->writeln(sprintf(PHP_EOL . '### %s', $field['fieldName']));

            if (!isset($field['declared'])) {
                $reflectionProperty = $this->reflectionClass->getProperty($field['fieldName']);
                $docBlock           = $this->docBlockParser->parseBlockComment($reflectionProperty->getDocComment());
            } else {
                $tempReflectionClass = new \ReflectionClass($field['declared']);
                $reflectionProperty  = $tempReflectionClass->getProperty($field['fieldName']);
                $docBlock            = $this->docBlockParser->parseBlockComment($reflectionProperty->getDocComment());
            }

            /** @var $docBlock DocumentationBlock */
            if (strpos($docBlock->summary(), '@') !== 0) {
                $this->addParagraph($docBlock->summary());
            }

            $this->writeln(sprintf(' - *column name*: %s', $field['columnName']));
            $this->writeln(sprintf(' - *type*: %s', $field['type']));

            switch ($field['type']) {
                case 'string':
                    if (isset($field['length'])) {
                        $this->writeln(sprintf(' - *length*: %s', $field['length']));
                    }
                    break;
                case 'decimal':
                    if (isset($field['scale'])) {
                        $this->writeln(sprintf(' - *scale*: %s', $field['scale']));
                    }

                    if (isset($field['precision'])) {
                        $this->writeln(sprintf(' - *precision*: %s', $field['precision']));
                    }
                    break;
            }


                $this->writeln(sprintf(' - *nullable*: %s', $field['nullable'] ? 'yes' : 'no'));
                $this->writeln(sprintf(' - *unique*: %s', $field['unique'] ? 'yes' : 'no'));

        }

        // Create documentation for association
        $associations = $this->metadatas->associationMappings;

        foreach ($associations as $association) {

            $remoteEntity = $association['sourceEntity'] == $this->metadatas->getName() ? $association['targetEntity'] : $association['sourceEntity'];

            $this->writeln(sprintf(PHP_EOL . '### %s', $association['fieldName']));

            $page = $this->getName($remoteEntity) . '.md';
            if ($this->getBundleShortName($remoteEntity) != $this->getBundleShortName()) {
                $page = $this->getBundleShortName($remoteEntity) . DIRECTORY_SEPARATOR . $page;
            }
            $this->writeln(sprintf(' - *entity*: %s', $this->createLink($page, $remoteEntity)));

            switch ($association['type']) {
                case 1:
                case 2:
                    $this->writeln(' - *type*: hasOne');
                    break;
                case 4:
                case 8:
                    $this->writeln(' - *type*: hasMany');
                    break;
            }
        }
    }

    private function loadClassMetadata()
    {
        $this->reflectionClass = $this->metadatas->getReflectionClass();

        // Parse class documentation
        $this->docs = $this->docBlockParser->parseBlockComment($this->reflectionClass->getDocComment());
    }
}