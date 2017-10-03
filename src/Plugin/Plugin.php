<?php

/*
 * This file is part of the Stati package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Stati
 */

namespace Stati\Plugin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Plugin implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array();
    }
}
