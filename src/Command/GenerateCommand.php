<?php

namespace Stati\Command;


use Stati\Exception\FileNotFoundException;
use Stati\Paginator\Paginator;
use Stati\Renderer\FilesRenderer;
use Stati\Renderer\PostsRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;


class GenerateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setAliases(['g'])
            ->setDescription('Generate static site')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        // Read config file
        $configFile = './_config.yml';
        if (is_file($configFile)) {
            $config = Yaml::parse(file_get_contents($configFile));
        } else {
            $style->error('No config file present. Are you in a jekyll directory ?');
            return 1;
        }
        // Create _site directory
        if (!is_dir('./_site')) {
            mkdir('./_site');
        }
        $time = microtime(true);
        $style->section('Generating posts');
        $postsRenderer = new PostsRenderer($config, $style);
        try {
            $posts = $postsRenderer->render();
        } catch (FileNotFoundException $err) {
            $posts = [];
            $style->error($err->getMessage());
        }
        $style->text('');
        $style->text('');
        $style->section('Generating files');
        $paginator = new Paginator($posts, $config);
        $filesRenderer = new FilesRenderer($config, $style);
        $filesRenderer->render();
        $elapsed = microtime(true) - $time;

        $style->text('');
        $style->title('Generated in '.number_format($elapsed, 2).'s');
    }

}