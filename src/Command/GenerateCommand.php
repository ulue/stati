<?php

namespace Stati\Command;


use Stati\Exception\FileNotFoundException;
use Stati\Paginator\Paginator;
use Stati\Renderer\DirectoryRenderer;
use Stati\Renderer\FilesRenderer;
use Stati\Renderer\PostsRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Stati\Entity\StaticFile;
use Symfony\Component\Yaml\Yaml;
use Stati\Renderer\PaginatorRenderer;
use Symfony\Component\Finder\Finder;

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
        $finder = new Finder();
        $finder
            ->in('./')
            ->files()
            ->notPath('/^_/')
            ->notPath('node_modules')
            ->notContains('/---\s+(.*)\s+---\s+/s')
            ->notName('/^_/');

        $config['static_files'] = [];
        foreach ($finder as $file) {
            $config['static_files'][] = new StaticFile($file->getPathname(), $file->getRelativePath(), $file->getRelativePathname());
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

            usort($posts, function($a, $b) {
                return $a->getDate() < $b->getDate();
            });
        } catch (FileNotFoundException $err) {
            $posts = [];
            $style->error($err->getMessage());
        }
        if (isset($config['paginate']) && count($posts) > 0) {
            $paginator = new Paginator($posts, $config);
            $config = array_merge($config, ['paginator' => $paginator]);
        }
        $style->text('');
        $style->text('');
        $style->section('Generating files');

        $filesRenderer = new FilesRenderer($config, $style);
        $filesRenderer->render();
        $style->text('');
        $style->text('');
        $style->section('Generating files with paginator');
        $paginatorRenderer = new PaginatorRenderer($config, $style);
        $paginatorRenderer->render();
        $style->text('');
        $style->text('');
        $style->section('Generating Directories');
        $dirRenderer = new DirectoryRenderer($config, $style);
        $dirRenderer->render();
        $elapsed = microtime(true) - $time;

        $style->text('');
        $style->title('Generated in '.number_format($elapsed, 2).'s');
    }

}