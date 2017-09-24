<?php
/**
 * MarDownParser.php
 *
 * Created By: jonathan
 * Date: 24/09/2017
 * Time: 21:38
 */

namespace Stati\Parser;

use ParsedownExtra;

class MarkdownParser extends ParsedownExtra
{
    protected function paragraph($Line)
    {
        // In paragraph first line, check if it's a class
        $Block = array(
            'element' => array(
                'name' => 'p',
                'text' => $Line['text'],
                'handler' => 'line',
            ),
        );

        if (preg_match('/^\{\:(.+)\}/', $Line['text'], $matches)) {
            $item = trim($matches[1]);
            if(strpos($item, '.') === 0) {
                //Is a class
                $Block['element']['attributes'] = ['class' => substr($item, 1)];
                $Block['element']['text'] = '';
            }
        }

        return $Block;
    }
}