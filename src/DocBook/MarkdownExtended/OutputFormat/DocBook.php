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

namespace DocBook\MarkdownExtended\OutputFormat;

use \MarkdownExtended\MarkdownExtended;
use \MarkdownExtended\API\OutputFormatInterface;
use \MarkdownExtended\OutputFormat\HTML;
use \MarkdownExtended\Helper as MDE_Helper;
use \MarkdownExtended\Exception as MDE_Exception;
use \DocBook\Helper;

/**
 * All '$_defaults' entries can be overwritten in config.
 */
class DocBook
    extends HTML
    implements OutputFormatInterface
{

    /**
     * @var array
    protected $tags_map = array(
    'block' => array(
    'tag'=>'div',
    ),
    'paragraph' => array(
    'tag'=>'p',
    ),
    'bold' => array(
    'tag'=>'strong',
    ),
    'italic' => array(
    'tag'=>'em',
    ),
    'preformated' => array(
    'tag'=>'pre',
    ),
    'link' => array(
    'tag'=>'a',
    ),
    'abbreviation' => array(
    'tag'=>'abbr',
    ),
    'definition_list' => array(
    'tag'=>'dl',
    ),
    'definition_list_item_term' => array(
    'tag'=>'dt',
    ),
    'definition_list_item_definition' => array(
    'tag'=>'dd',
    ),
    'list' => array(
    'tag'=>'ul',
    ),
    'list_item' => array(
    'tag'=>'li',
    ),
    'unordered_list' => array(
    'tag'=>'ul',
    ),
    'unordered_list_item' => array(
    'tag'=>'li',
    ),
    'ordered_list' => array(
    'tag'=>'ol',
    ),
    'ordered_list_item' => array(
    'tag'=>'li',
    ),
    'table_caption' => array(
    'tag'=>'caption',
    ),
    'table_header' => array(
    'tag'=>'thead',
    ),
    'table_body' => array(
    'tag'=>'tbody',
    ),
    'table_footer' => array(
    'tag'=>'tfoot',
    ),
    'table_line' => array(
    'tag'=>'tr',
    ),
    'table_cell' => array(
    'tag'=>'td',
    ),
    'table_cell_head' => array(
    'tag'=>'th',
    ),
    'meta_title' => array(
    'tag'=>'title',
    ),
    'image' => array(
    'tag'=>'img',
    'closable'=>true,
    ),
    'new_line' => array(
    'tag'=>'br',
    'closable'=>true,
    ),
    'horizontal_rule' => array(
    'tag'=>'hr',
    'closable'=>true,
    ),
    );
     */

    /**
     * @param $var string
     * @return mixed
     */
    protected function _getConfigOrDefault($var)
    {
        return DocBookHelper::getConfigOrDefault($var);
    }

// -------------------
// Tag specific builder
// -------------------

    /**
     * @param $text
     * @param array $attributes
     * @return string
     */
    public function buildTitle($text, array $attributes = array())
    {
        if (!isset($attributes['id']) || empty($attributes['id'])) {
            $attributes['id'] = uniqid();
        } else {
            $attributes['id'] = Helper::getSafeIdString($attributes['id']);
        }
        if (!isset($attributes['name']) || empty($attributes['name'])) {
            $attributes['name'] = $attributes['id'];
        }

        if (isset($attributes['level'])) {
            $level = $attributes['level'];
            unset($attributes['level']);
        } else {
            $level = MarkdownExtended::getVar('baseheaderlevel');
        }
        $tag = 'h' . $level;

        if (($level > 1) && (!isset($attributes['no-addon']) || $attributes['no-addon']!==true)) {
            $text = $this->addTitleAddon($text, $attributes);
        }
        if (isset($attributes['no-addon'])) {
            unset($attributes['no-addon']);
        }
        $_ttl = $this->getTagString($text, $tag, $attributes);
        return $_ttl;
    }

    /**
     * @param null $text
     * @param array $attributes
     * @return null|string
     */
    public function buildMetaData($text = null, array $attributes = array())
    {
        if (empty($attributes['content']) && !empty($text)) {
            $attributes['content'] = $text;
        }
        if (!empty($attributes['name']) || !empty($attributes['http-equiv'])) {
            return $this->getTagString($text, 'meta', $attributes, true);
        }
        return $text;
    }

    /**
     * @param null $text
     * @param array $attributes
     * @return string
     */
    public function buildLink($text = null, array $attributes = array())
    {
        if (isset($attributes['email'])) {
            unset($attributes['email']);
        }
        return $this->getTagString($text, 'a', $attributes);
    }

// -------------------
// DocBook specifics
// -------------------

    /**
     * Title-add-ons
     *
     * Add the link to the table of contents and the permalink of the title
     * after each title content.
     *
     * @param $text
     * @param array $attributes
     * @return string
     */
    public function addTitleAddon($text, array $attributes = array())
    {
        $backlink_text = $this->_getConfigOrDefault('backlink_text');
        $backlink_attributes = array();
        $backlink_attributes['title'] = '';

        // permalink links class
        $cfg_permalink_class = $this->_getConfigOrDefault('permalink_class');
        if (isset($attributes['permalink_class'])) {
            $backlink_attributes['class'] = $attributes['permalink_class'];
            unset($attributes['permalink_class']);
        } else {
            $backlink_attributes['class'] = $cfg_permalink_class;
        }

        // toc backlink id
        $cfg_permalink_title = $this->_getConfigOrDefault('permalink_mask_title');
        if (isset($attributes['id'])) {
            $backlink_attributes['href'] = '#'.$attributes['id'];
            if (isset($attributes['permalink_title'])) {
                $backlink_attributes['title'] .= $attributes['permalink_title'];
                unset($attributes['permalink_title']);
            } elseif (isset($attributes['permalink_mask_title'])) {
                $backlink_attributes['title'] .= str_replace('%%', '#'.$attributes['id'], $attributes['permalink_mask_title']);
                unset($attributes['permalink_mask_title']);
            } else {
                $backlink_attributes['title'] .= str_replace('%%', '#'.$attributes['id'], $cfg_permalink_title);
            }
        }

        // toc backlink id
        $cfg_toc_id = $this->_getConfigOrDefault('toc_id');
        $cfg_toc_title = $this->_getConfigOrDefault('toc_backlink_title');
        $cfg_backlink_onclick_mask = $this->_getConfigOrDefault('backlink_onclick_mask');
        $cfg_permalink_title_separator = $this->_getConfigOrDefault('permalink_title_separator');        
        if (isset($attributes['permalink_title_separator'])) {
            $permalink_title_separator = $attributes['permalink_title_separator'];
            unset($attributes['permalink_title_separator']);
        } else {
            $permalink_title_separator = $cfg_permalink_title_separator;
        }
        if (isset($attributes['toc_id'])) {
            $toc_id = $attributes['toc_id'];
            unset($attributes['toc_id']);
            if (isset($attributes['toc_backlink_title'])) {
                $backlink_attributes['title'] .= $permalink_title_separator.$attributes['toc_backlink_title'];
                unset($attributes['toc_back_title']);
            } else {
                $backlink_attributes['title'] .= $permalink_title_separator.$cfg_toc_title;
            }
        } elseif (!empty($cfg_toc_id) && !empty($cfg_toc_title)) {
            $toc_id = $cfg_toc_id;
            $backlink_attributes['title'] .= $permalink_title_separator.$cfg_toc_title;
        }
        if (isset($attributes['backlink_onclick_mask'])) {
            $backlink_onclick_mask = $attributes['backlink_onclick_mask'];
            unset($attributes['backlink_onclick_mask']);
        } else {
            $backlink_onclick_mask = $cfg_backlink_onclick_mask;
        }
        $backlink_attributes['onclick'] = str_replace('%%', $toc_id, $backlink_onclick_mask);

        // add-on
        if (!empty($backlink_text) && !empty($backlink_attributes['title'])) {
            $text .= $this->buildTag('link', $backlink_text, $backlink_attributes);
        }
        return $text;
    }
    
}

// Endfile