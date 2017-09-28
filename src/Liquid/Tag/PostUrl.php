<?php
/**
 * Highlight.php
 *
 * Created By: jonathan
 * Date: 23/09/2017
 * Time: 00:02
 */

namespace Stati\Liquid\Tag;

use Liquid\AbstractTag;
use Liquid\Context;
use Stati\Exception\FileNotFoundException;
use Stati\Link\Generator;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Finder\Finder;
use Stati\Entity\Post;

class PostUrl extends AbstractTag
{
    public function render(Context $context)
    {
        $post = trim($this->markup);
        $pattern = '*'.$post.'.*';

        if (!pathinfo($post, PATHINFO_EXTENSION)) {
            $finder = new Finder();
            $finder->depth(' <= 1')
                ->files()
                ->in('./_posts/')
                ->name($pattern)
                ;
            if ($finder->count() === 0) {
                throw new FileNotFoundException('Could not find the post to link to');
            }
            foreach ($finder as $f) {
                $file = $f;
            }
        } else {
            $file = new SplFileInfo('./_posts/'.$post, '_posts/', $post);
        }

        if (!isset($file) || $file === null) {
            return '';
        }
        $post = new Post($file, $context->get('site'));
        return $post->getUrl();
    }
}
