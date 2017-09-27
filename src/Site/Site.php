<?php
/**
 * Site.php
 *
 * Created By: jonathan
 * Date: 26/09/2017
 * Time: 21:34
 */

namespace Stati\Site;

use Iterator;
use Stati\Entity\Collection;
use Stati\Event\WillResetSiteEvent;
use Stati\Reader\CollectionReader;
use Stati\Reader\StaticFileReader;
use Stati\Reader\PageReader;
use Stati\Renderer\CollectionRenderer;
use Stati\Renderer\PageRenderer;
use Stati\Writer\CollectionWriter;
use Stati\Writer\StaticFileWriter;
use Stati\Writer\PageWriter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Stati\Site\SiteEvents;


class Site
{
    /**
     * Config from _config.yml
     *
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $posts;

    /**
     * @var array
     */
    protected $pages;

    /**
     * @var array
     */
    protected $staticFiles;

    /**
     * @var array
     */
    protected $relatedPosts;

    /**
     * @var array
     */
    private $plugins = [];

    /**
     * @var array
     */
    protected $collections;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(array $config){
        $this->config = $config;
        $this->dispatcher = new EventDispatcher();
    }

    public function process()
    {
        $this->dispatcher->dispatch(SiteEvents::WILL_RESET_SITE, new WillResetSiteEvent($site));
        $this->reset();
        $this->read();
        $this->generate();
        $this->render();
        $this->write();
    }

    public function reset() {
        $fs = new Filesystem();
        $fs->remove($this->getDestination());
        $fs->mkdir($this->getDestination());
    }

    public function generate() {
    }

    public function write() {
        // Write static files
        $staticFileWriter = new StaticFileWriter($this);
        $staticFileWriter->writeAll();

        // Write Collection files
        $collectionWriter = new CollectionWriter($this);
        $collectionWriter->writeAll();

        // Write Page files
        $pageWriter = new PageWriter($this);
        $pageWriter->writeAll();
    }

    public function read() {
        //Read static files
        $staticFileReader = new StaticFileReader();
        $this->setStaticFiles($staticFileReader->read());

        //Read collections
        $collections = [
            'posts' => [
                'permalink' => $this->permalink
            ]
        ];

        /*
         * TODO make sure each item in the collection config is an array
         * because jekyll supports the collections_dir: my_collections
         * configuration option
         */
        if (isset($this->config['collections'])) {
            $collections = array_merge($collections, $this->config['collections']);
        }
        $collectionReader = new CollectionReader(['collections' => $collections]);
        $this->setCollections($collectionReader->read());

        //Read pages
        $pageReader = new PageReader($this->config);
        $this->setPages($pageReader->read());
    }

    public function render() {
        //Render Pages
        $pageRenderer = new PageRenderer($this);
        $this->setPages($pageRenderer->renderAll());

        //Render Collections
        $collectionRenderer = new CollectionRenderer($this);
        $this->setCollections($collectionRenderer->renderAll());

//        var_dump($this->pages);
    }

    /**
     * @param $item
     * @return mixed
     */
    public function __get($item)
    {
        $getter = implode('', array_map(function($part){
            return ucfirst($part);
        }, explode('_', $item)));
        $field = lcfirst($getter);

        //If the property exists, return it
        if (property_exists(self::class, $field)) {

            return $this->{$field};
        }
        //If the method exists, return it
        if (method_exists(self::class, 'get'.$getter)) {
            return $this->{'get'.$getter}();
        }
        // If this is a config item, return it;
        if (isset($this->config[$item])) {
            return $this->config[$item];
        }

        // If it's a collection, return the corresponding one
        if (isset($this->collections[$item])) {
            return $this->collections[$item]->getDocs();
        }

        return null;
    }

    public function get($item)
    {
        return $this->__get($item);
    }

    public function getDestination() {
        if (isset($this->config['destination'])) {
            return $this->config['destination'];
        }
        return './_site/';
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return Collection
     */
    public function getPosts()
    {
        if (isset($this->collections['posts'])) {
            return $this->collections['posts'];
        }
        return new Collection('posts');
    }

    /**
     * @param Collection $posts
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    }

    /**
     * @return array
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param array $pages
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }

    /**
     * @return array
     */
    public function getStaticFiles()
    {
        return $this->staticFiles;
    }

    /**
     * @param array $staticFiles
     */
    public function setStaticFiles($staticFiles)
    {
        $this->staticFiles = $staticFiles;
    }

    /**
     * @return array
     */
    public function getRelatedPosts()
    {
        return $this->relatedPosts;
    }

    /**
     * @param array $relatedPosts
     */
    public function setRelatedPosts($relatedPosts)
    {
        $this->relatedPosts = $relatedPosts;
    }

    /**
     * @return array
     */
    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * @param array $collections
     */
    public function setCollections($collections)
    {
        $this->collections = $collections;
    }

    public function addPlugin($plugin)
    {
        $this->plugins[] = $plugin;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

}