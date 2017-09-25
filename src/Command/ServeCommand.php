<?php

namespace Stati\Command;


use Stati\Exception\FileNotFoundException;
use Stati\Renderer\FilesRenderer;
use Stati\Renderer\PostsRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Application;


class ServeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('serve')
            ->setAliases(['s'])
            ->setDescription('Serve static site from localhost')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input = new ArrayInput([]);
        $output = new ConsoleOutput();
        $application = new Application('Stati', '@package_version@');
        $application->add(new GenerateCommand());
        $command = $application->find('generate');
        $returnCode = $command->run($input, $output);
        if ($returnCode === 0) {
            $output->writeln('');
            $output->writeln('');
            $output->writeln('');
            $output->writeln('Stati is now serving your website');
            $output->writeln('Open http://localhost:4000 to view it');
            $output->writeln('Press Ctrl-C to quit.');
            shell_exec('cd _site && php -S localhost:4000 &');

        }
    }

}