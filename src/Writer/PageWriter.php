<?php

/*
 * This file is part of the Stati package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Stati
 */

namespace Stati\Writer;

use Symfony\Component\Filesystem\Filesystem;
use Stati\Entity\Doc;

class PageWriter extends Writer
{
    public function writeAll()
    {
        $fs = new Filesystem();
        $destDir = $this->site->getDestination();

        foreach ($this->site->getPages() as $doc) {
            /**
             * @var Doc $doc
             */
            $dest = str_replace('//', '/', $destDir.'/'.$doc->getPath());
            $fs->dumpFile($dest, $doc->getOutput());
        }
    }
}
