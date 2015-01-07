<?php
/**
 * This file is part of the DocBook package.
 *
 * Copyleft (ↄ) 2008-2015 Pierre Cassat <me@e-piwi.fr> and contributors
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * The source code of this package is available online at 
 * <http://github.com/atelierspierrot/docbook>.
 */

namespace DocBook\MDEOutputFormat;

use \MarkdownExtended\MarkdownExtended;
use \MarkdownExtended\API\ContentInterface;
use \MarkdownExtended\API\OutputFormatInterface;
use \MarkdownExtended\API\OutputFormatHelperInterface;
use \MarkdownExtended\Helper as MDE_Helper;
use \MarkdownExtended\Exception as MDE_Exception;
use \MarkdownExtended\OutputFormat\HTMLHelper;
use \DocBook\Helper;

/**
 * DocBook output Helper
 *
 * All '$_defaults' entries can be overwritten in config.
 */
class DocBookHelper
    extends HTMLHelper
    implements OutputFormatHelperInterface
{

    protected static $_defaults = array(
        'toc_max_level'     => '6',
        'toc_title'         => 'Table of contents',
        'toc_title_level'   => '4',
        'toc_id'            => 'toc',
        'toc_class'         => 'toc-menu',
        'toc_item_title'    => 'Reach this section',
        'permalink_mask_title' => 'Copy this link URL to get this title permanent link: #%%',
        'permalink_title_separator' => ' - ',
        'toc_backlink_title'    => 'Click to go back to table of contents',
        'backlink_onclick_mask' => "document.location.hash='%%'; return false;",
    );

    public static function getConfigOrDefault($var)
    {
        $cfg_val = MarkdownExtended::getConfig($var);
        if (empty($cfg_val)) $cfg_val = self::$_defaults[$var];
        return $cfg_val;
    }

    /**
     * Build a hierarchical menu
     *
     * @param object $content \MarkdownExtended\API\ContentInterface
     * @param object $formatter \MarkdownExtended\API\OutputFormatInterface
     *
     * @return string
     */
    public function getToc(ContentInterface $md_content, OutputFormatInterface $formatter, array $attributes = null)
    {
        $cfg_toc_max_level = $this->getConfigOrDefault('toc_max_level');
        $cfg_toc_title = $this->getConfigOrDefault('toc_title');
        $cfg_toc_title_level = $this->getConfigOrDefault('toc_title_level');
        $cfg_toc_id = $this->getConfigOrDefault('toc_id');
        $cfg_toc_class = $this->getConfigOrDefault('toc_class');
        $cfg_toc_item_title = $this->getConfigOrDefault('toc_item_title');

        $menu = $md_content->getMenu();
        $content = $list_content = '';
        $max_level = isset($attributes['max_level']) ? $attributes['max_level'] : $cfg_toc_max_level;
        if (!empty($menu) && count($menu) > 1) {
            $depth = 0;
            $current_level = null;
            foreach ($menu as $item_id=>$menu_item) {
                $_item_id = Helper::getSafeIdString($item_id);
                if (isset($max_level) && $menu_item['level']>$max_level) {
                    continue;
                }
                $diff = $menu_item['level']-(is_null($current_level) ? $menu_item['level'] : $current_level);
                if ($diff > 0) {
                    $list_content .= str_repeat('<ul><li>', $diff);
                } elseif ($diff < 0) {
                    $list_content .= str_repeat('</li></ul></li>', -$diff);
                    $list_content .= '<li>';
                } else {
                    if (!is_null($current_level)) $list_content .= '</li>';
                    $list_content .= '<li>';
                }
                $depth += $diff;
                $list_content .= $formatter->buildTag('link', $menu_item['text'], array(
                    'href'=>'#'.$_item_id,
                    'title'=>isset($attributes['toc_item_title']) ? $attributes['toc_item_title'] : $cfg_toc_item_title,
                ));
                $current_level = $menu_item['level'];
            }
            if ($depth!=0) {
                $list_content .= str_repeat('</ul></li>', $depth);
            }
            $content .= $formatter->buildTag('title', $cfg_toc_title, array(
                'level'=>isset($attributes['toc_title_level']) ? $attributes['toc_title_level'] : $cfg_toc_title_level,
                'id'=>isset($attributes['toc_id']) ? $attributes['toc_id'] : $cfg_toc_id,
                'no-addon'=>true
            ));
            $content .= $formatter->buildTag('unordered_list', $list_content, array(
                'class'=>isset($attributes['class']) ? $attributes['class'] : $cfg_toc_class,
            ));
        }
        return $content;
    }

}

// Endfile
