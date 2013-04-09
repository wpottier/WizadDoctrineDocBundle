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

class MarkdownFormatter
{
    private $buffer;

    public function __construct()
    {

    }

    public function dump($file)
    {
        file_put_contents($file, $this->buffer);
    }

    /*
     * Advanced styling
     */


    public function addTitle($title)
    {
        $this->writeln(PHP_EOL . $title . PHP_EOL . '====================');
    }

    public function addSection($title)
    {
        $this->writeln(PHP_EOL . $title . PHP_EOL . '--------------------');
    }

    public function addParagraph($text)
    {
        $this->writeln(PHP_EOL . $text . PHP_EOL);
    }

    public function addLink($url, $text = null)
    {
        if (!$text) {
            $this->write(sprintf('[[%s]]', $url));
        } else {
            $this->write(sprintf('[%s](%s)', $text, $url));
        }
    }

    public function addBlankLine()
    {
        $this->write(PHP_EOL . PHP_EOL);
    }

    /*
     * RAW manipulation
     */

    protected function writeln($line)
    {
        $this->write($line . PHP_EOL);
    }

    protected function write($text)
    {
        $this->buffer .= $text;
    }
}