<?php
/**
 * Post.php
 *
 * Created By: jonathan
 * Date: 24/09/2017
 * Time: 23:05
 */

namespace Stati\Entity;

use Stati\Site\Site;
use Symfony\Component\Finder\SplFileInfo;
use Stati\Parser\FrontMatterParser;
use Stati\Parser\ContentParser;
use Stati\Parser\MarkdownParser;
use Liquid\Template;
use Liquid\Liquid;
use Stati\Liquid\Block\Highlight;
use Stati\Liquid\Tag\PostUrl;
use Liquid\Cache\File;
use Stati\Entity\Doc;

class Post extends Doc
{
    /**
     * Next post
     * @var Doc
     */
    protected $next;

    /**
     *  Previous post
     * @var Doc
     */
    protected $previous;

    /**
     * @return \Stati\Entity\Doc
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param \Stati\Entity\Doc $next
     */
    public function setNext($next)
    {
        $this->next = $next;
    }

    /**
     * @return \Stati\Entity\Doc
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * @param \Stati\Entity\Doc $previous
     */
    public function setPrevious($previous)
    {
        $this->previous = $previous;
    }
}
