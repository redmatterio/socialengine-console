<?php

namespace SocialEngine\Console;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Class Command
 *
 * All comments must extend this abstract class.
 *
 * @package SocialEngine\Console
 */
abstract class Command
{
    /**
     * @var Helper\Config
     */
    private $config;

    /**
     * @var SymfonyCommand
     */
    private $symfony;

    /**
     * Local bin paths
     *
     * @var array
     */
    private $bin = [
        'git' => 'git',
        'php' => 'php',
        'phpcs' => 'phpcs'
    ];

    /**
     * Output color.
     *
     * @var string
     */
    private $color;

    /**
     * Command constructor.
     * @param SymfonyCommand $symfony
     * @param array $config
     */
    public function __construct(SymfonyCommand $symfony, $config = [])
    {
        $this->config = new Helper\Config($this, $config);
        $this->symfony = $symfony;
    }

    /**
     * Return the value of an option.
     *
     * @see Symfony\Component\Console\Input\Input::getOption()
     * @param string $key Name of the option.
     * @return mixed
     */
    protected function getOption($key)
    {
        return $this->symfony->input->getOption($key);
    }

    /**
     * Return the value of an argument.
     *
     * @see Symfony\Component\Console\Input\Input::getArgument()
     * @param string $name Argument name
     * @return string
     */
    protected function getArgument($name)
    {
        return $this->symfony->input->getArgument($name);
    }

    /**
     * Ask a question to the user.
     *
     * @param string $question Question to ask
     * @return string Users answer
     */
    protected function ask($question)
    {
        $helper = $this->symfony->getHelper('question');
        if ($helper instanceof QuestionHelper) {
            return $helper->ask($this->symfony->input, $this->symfony->output, new Question($question));
        }

        return null;
    }

    /**
     * Execute a command.
     *
     * @param string $command
     * @return string
     */
    protected function exec($command)
    {
        if ($this->getOption('v')) {
            $this->write($command);
        }
        return shell_exec($command);
    }

    /**
     * Execute a git command.
     *
     * @param string $command
     * @return string
     */
    protected function git($command)
    {
        return $this->exec($this->getBin('git') . ' ' . $command);
    }

    /**
     * Attempt to load bin for certain programs, such as PHP or GIT.
     *
     * @see $this->bin
     * @param string $program Program to load
     * @return string
     * @throws \Exception
     */
    protected function getBin($program)
    {
        if (!isset($this->bin[$program])) {
            throw new \Exception('Unable to find the bin: ' . $program);
        }

        return $this->getConfig($program . '-path', $this->bin[$program]);
    }

    /**
     * @inheritdoc
     */
    public function getConfig($name, $default = null)
    {
        return $this->config->get($name, $default);
    }

    /**
     * Return a working temporary directory.
     *
     * @return string
     */
    public function getTempDir()
    {
        $tmp = $this->getBaseDir() . 'tmp/';
        if (!is_dir($tmp)) {
            mkdir($tmp);
        }

        return $tmp;
    }

    /**
     * Return the base working directory.
     *
     * @return string
     */
    public function getBaseDir()
    {
        return dirname(dirname(__FILE__)) . '/';
    }

    /**
     * @inheritdoc
     */
    public function setConfig($name, $value)
    {
        return $this->config->set($name, $value);
    }

    /**
     * Set a string color for the output.
     *
     * @param string $color Color
     * @return $this
     */
    public function color($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Write to CLI
     *
     * @param mixed $string
     */
    public function write($string)
    {
        if (!is_string($string)) {
            $string = print_r($string, true);
        }

        if ($this->color) {
            $string = '<fg=' . $this->color . '>' . $string . '</>';
        }

        $this->symfony->output->writeln($string);

        $this->color = null;
    }

    /**
     * Output results
     *
     * @param string|array $result
     */
    public function writeResults($result)
    {
        if (!is_array($result)) {
            $result = [$result];
        }

        $hashTag = ' ' . str_repeat('#', strlen($result) + 4);
        $this->write('');
        $this->write($hashTag);
        $this->write(' #');
        foreach ($result as $value) {
            $this->write(' # ' . $value);
        }
        $this->write(' #');
        $this->write($hashTag);
        $this->write('');
    }
}
