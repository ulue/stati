<?php
/**
 * PostRenderer.php
 *
 * Created By: jonathan
 * Date: 24/09/2017
 * Time: 22:17
 */

namespace Stati\Renderer;

use Stati\Entity\Post;
use Stati\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Liquid\Template;
use Liquid\Liquid;
use Stati\Parser\MarkdownParser;
use Stati\Link\Generator;
use Stati\LiquidBlock\Highlight;
use Symfony\Component\Finder\SplFileInfo;
use Stati\Parser\FrontMatterParser;
use Stati\Parser\ContentParser;
use Stati\LiquidTag\PostUrl;
use Liquid\Cache\File;

class PostsRenderer
{
    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function render()
    {
        // Get top level files and parse
        $finder = new Finder();
        $finder->depth(' <= 1')
        ->files()
        ->in('./_posts/')
        ->name('/(\.mkd|\.md|\.markdown)$/');
        //            ->name('/\.md$/');

        $linkGenerator = new Generator($this->config);
        $posts = [];
        foreach ($finder as $file) {

            $post = new Post($file, $this->config);
            $posts[] = $post;
            //Put rendered file in _site directory, in it's right place
            $dir = './_site'.pathinfo($post->getPath(),PATHINFO_DIRNAME);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $content = $this->renderPage($post);

            file_put_contents('./_site'.$post->getPath(), $content);
        }
        return $posts;
    }


    private function renderPage(Post $post)
    {
        Liquid::set('INCLUDE_ALLOW_EXT', true);
        Liquid::set('INCLUDE_PREFIX', './_includes/');
        Liquid::set('HAS_PROPERTY_METHOD', 'get');
        $frontMatter = $post->getFrontMatter();
        $content = $post->getContent();

        //If we have a layout
        if (isset($frontMatter['layout'])) {
            $config = [
                'content' => $content,
                'page' => $post,
                'post' => $post,
                'site' => $this->config
            ];

            try {
                return $this->renderWithLayout($frontMatter['layout'], $config);
            } catch (FileNotFoundException $err) {
                throw new FileNotFoundException($err->getMessage(). ' for post "'.$post->getTitle().'"');
            }
        }

        return $content;
    }

    private function renderWithLayout($layoutFile, $config)
    {

        $layout = @file_get_contents('./_layouts/'.$layoutFile.'.html');
        if (!$layout) {
            throw new FileNotFoundException('Layout file "'.$layoutFile.'" not found in layout folder');
        }
        $layoutFrontMatter = FrontMatterParser::parse($layout);
        $layoutContent = ContentParser::parse($layout);

        if (isset($layoutFrontMatter['layout'])) {
            $template = new Template('./_includes/');
            $template->registerTag('highlight', Highlight::class);
            $template->registerTag('post_url', PostUrl::class);
            $template->parse($layoutContent);
            $config['content'] = $template->render($config);
            return $this->renderWithLayout($layoutFrontMatter['layout'], $config);
        }

        $template = new Template('./_includes/');
        $template->registerTag('highlight', Highlight::class);
        $template->registerTag('post_url', PostUrl::class);
        $template->parse($layoutContent);
        return $template->render($config);
    }

}