<?php
/**
 * This file is part of the WebDocBook package.
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
 * <http://github.com/wdbo/webdocbook>.
 */

namespace WebDocBook\Filesystem\WDBFileType;

use \WebDocBook\FrontController;
use \WebDocBook\Helper;
use \WebDocBook\Model\MetaFile;
use \WebDocBook\Filesystem\WDBFileInterface;
use \WebFilesystem\WebFileInfo;

/**
 * Class Markdown
 */
class Markdown
    extends WebFileInfo
    implements WDBFileInterface
{

    /**
     * @var string
     */
    protected $content;

    /**
     * @var array
     */
    protected $wdb_meta_data = array();

    /**
     * @param array $params
     * @return string
     */
    public function viewFileInfos(array $params = array())
    {
        $this
            ->setContent(file_get_contents($this->getRealPath()))
            ->parseMetaFiles(dirname($this->getRealPath()));

        $wdb            = FrontController::getInstance();
        $md_parser      = $wdb->getMarkdownParser();
        $md_content     = $md_parser->transformString($this->getContent());
        $page_notes     = $md_content->getNotesToString();
        $page_meta      = $md_content->getMetadata();
        $page_footnotes = $md_content->getFootnotes();
        $page_glossary  = $md_content->getGlossaries();
        $page_citations = $md_content->getCitations();

        $this->setMetaData(
            !empty($page_meta) && array_key_exists('wdb', $page_meta) ? $page_meta['wdb'] : null
        );
        $params['meta']       = $page_meta;
        $params['wdb_meta']   = $this->getMetaData();
        $params['page_notes'] = $page_notes;
        if (!empty($page_citations) || !empty($page_glossary)) {
            $params['page_footnotes']   = $page_footnotes;
            $params['page_glossary']    = $page_glossary;
            $params['page_citations']   = $page_citations;
        }

        if ( ! $this->hasMetaData('notoc')) {
            $output_bag    = $md_parser->get('OutputFormatBag');
            $params['toc'] = $output_bag->getHelper()
                ->getToc($md_content, $output_bag->getFormatter());
        }

        return $wdb->display($md_content->getBody(), 'content', $params);
    }

    /**
     * @param array $params
     * @return string
     */
    public function getIntroduction(array $params = array())
    {
        $wdb        = FrontController::getInstance();
        $md_parser  = $wdb->getMarkdownParser();
        $md_content = $md_parser->transformSource($this->getRealPath());
        return $md_content->getBody();
    }

    /**
     * @param $str
     * @return $this
     */
    public function setContent($str)
    {
        $this->content = $str;
        return $this;
    }

    /**
     * @param $str
     * @return $this
     */
    public function appendContent($str)
    {
        $this->content .= PHP_EOL.$str;
        return $this;
    }

    /**
     * @param $str
     * @return $this
     */
    public function prependContent($str)
    {
        $this->content = $str.PHP_EOL.$this->content;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param array|string $meta
     * @return $this
     */
    public function setMetaData($meta)
    {
        if (empty($meta)) {
            return;
        }
        if (is_string($meta)) {
            $meta_data = explode(',', $meta);
        } else {
            $meta_data = $meta;
        }
        foreach ($meta_data as $var=>$val) {
            if (!is_string($var)) {
                $meta_data[$val] = 1;
                unset($meta_data[$var]);
            }
        }
        $this->wdb_meta_data = $meta_data;
        return $this;
    }

    /**
     * @param string|null $name
     * @return array|int|null
     */
    public function getMetaData($name = null)
    {
        if (!empty($name)) {
            return ($this->hasMetaData($name) ? $this->wdb_meta_data[$name] : null);
        } else {
            return $this->wdb_meta_data;
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasMetaData($name)
    {
        return (bool) (!empty($this->wdb_meta_data) && array_key_exists($name, $this->wdb_meta_data));
    }

    /**
     * @param string $path
     * @return string
     */
    public function parseMetaFiles($path)
    {
        $meta_files = Helper::getDirectoryMetaFiles($path);
        if (!empty($meta_files)) {
            foreach ($meta_files as $type=>$fp) {
                if (is_null($fp)) {
                    continue;
                }
                switch($type) {
                    case 'meta_data':
                        $wdb_meta_file = new MetaFile($fp);
                        $this->prependContent($wdb_meta_file->getWDBContent());
                        break;
                    case 'references':
                        $wdb_meta_file = new MetaFile($fp);
                        $this->appendContent($wdb_meta_file->getWDBContent());
                        break;
                    case 'header':
                        $this->prependContent(file_get_contents($fp));
                        break;
                    case 'footer':
                        $this->appendContent(file_get_contents($fp));
                        break;
                }
            }
        }
        return $this;
    }

}

// Endfile
