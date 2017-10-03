<?php

/*
 * This file is part of the Stati package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Stati
 */

namespace Stati\Liquid;

class TemplateEvents
{

    /**
     * This event is triggered just before the template variables are set for the post/page content
     * It allows plugin to modify the variables passed to Liquid just before rendering post/page content
     *
     * @Event("Stati\Event\SettingTemplateVarsEvent")
     */
    const SETTING_TEMPLATE_VARS = 'template.setting_vars';

    /**
     * This event is triggered just before the template variables are set for the layouts
     * It allows plugin to modify the variables passed to Liquid when rendering the layouts containing posts/pages
     *
     * @Event("Stati\Event\SettingTemplateVarsEvent")
     */
    const SETTING_LAYOUT_TEMPLATE_VARS = 'template.setting_layout_vars';

    /**
     * This event is trigerred just before we parse the template
     *
     * @Event("Stati\Event\SiteEvent")
     */
    const WILL_PARSE_TEMPLATE = 'template.will_parse';
}
