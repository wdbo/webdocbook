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

namespace WebDocBook\WebFilesystem;

use \WebDocBook\FrontController;
use \WebDocBook\Helper;
use \WebDocBook\Kernel;
use \WebDocBook\Exception\Exception;
use \WebDocBook\Exception\RuntimeException;
use \WebDocBook\Util\Filesystem;
use \WebFilesystem\WebFilesystem;
use \WebFilesystem\WebFileInfo;
use \WebFilesystem\Finder;
use \Library\Helper\Text as TextHelper;
use \FilesystemIterator;

/**
 * Class WDBFile
 *
 * Default File class of WebDocBook
 */
class WDBFile
    extends WebFileInfo
{

    /**
     * @var bool
     */
    protected $is_root_link = false;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var \WebDocBook\FrontController
     */
    protected $wdb;

    /**
     * @var array
     */
    protected static $config;

    /**
     * @var array
     */
    protected $cache        = array();

    /**
     * @param string $file_name
     * @throws \WebDocBook\Exception\RuntimeException
     */
    public function __construct($file_name)
    {
        $this->wdb  = FrontController::getInstance();
        $_root          = Kernel::getPath('web');

        if (substr_count($file_name, $_root)>0) {
            $realpath = $_root.str_replace($_root, '', $file_name);
            parent::__construct($realpath);
        } else {
            parent::__construct($file_name);
        }

        try {
            $this->_init($file_name);
        } catch (RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * @param string $file_name
     * @throws \WebDocBook\Exception\RuntimeException
     */
    protected function _init($file_name)
    {
        if (empty(self::$config)) {
            self::$config = Kernel::getConfig('file_types', array());
        }

        $_root = Kernel::getPath('web');
        if (substr_count($file_name, $_root)>0) {
            $realpath   = $_root.str_replace($_root, '', $file_name);
            $this->type = $this->getWDBTypeByPath($realpath);
            $this->setRootDir($_root);
            $this->setWebPath(dirname($file_name));
            if (is_link($realpath)) {
                $this->setIsRootLink(true);
            }

        } else {
            $this->type = $this->getWDBTypeByPath($file_name);
            $this->setRootDir(dirname($file_name));
            $this->setWebPath($_root.$this->wdb->getInputPath());
        }

        $_class_name = isset(self::$config[$this->type]['class']) ? self::$config[$this->type]['class'] : null;
        if (is_null($_class_name) || !class_exists($_class_name)) {
            $_class_name = '\WebDocBook\WebFilesystem\WDBFile\\'
                .(isset(self::$config[$this->type]['class']) ? self::$config[$this->type]['class'] : 'WDBDefault');
        }
        if (class_exists($_class_name)) {
            $_file = new $_class_name($file_name);
        } else {
            throw new RuntimeException("WebDocBook file class '$_class_name' not found!");
        }

        $this->setFile($_file);
        $this->getFile()->setRootDir($_root);
        $this->getFile()->setWebPath($_root.$this->wdb->getInputPath());
    }

    /**
     * @param \WebDocBook\WebFileSystem\WDBFileInterface $file
     * @return $this
     */
    public function setFile(WDBFileInterface $file)
    {
        $this->_file = $file;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param bool $is_it
     * @return $this
     */
    public function setIsRootLink($is_it = false)
    {
        $this->is_root_link = $is_it;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsRootLink()
    {
        return $this->is_root_link;
    }

    /**
     * @return bool
     */
    public function isRootLink()
    {
        return true===$this->getIsRootLink();
    }

    /**
     * @return bool
     */
    public function isChildOfLink()
    {
        $_root  = Kernel::getPath('web');
        $_dir   = Filesystem::slashDirname($this->getRealPath());
        return (substr_count($_dir, $_root) === 0);
    }

    /**
     * @return array
     */
    public function getWDBPath()
    {
        if (!isset($this->cache['wdb_path'])) {
            $filepath = $this->getRealPath();
            if ($this->isLink() || $this->isRootLink()) {
                $filepath   = Filesystem::slashDirname($this->getWebPath()).$this->getFilename();
            } elseif ($this->isChildOfLink()) {
                $filepath   = $this->getFile()->getRealWebPath();
            }
            $this->cache['wdb_path'] = $filepath;
        }
        return $this->cache['wdb_path'];
    }

    /**
     * @param bool $recursive
     * @return array
     */
    public function getWDBScanStack($recursive = false)
    {
        if (!isset($this->cache['wdb_scan_stack'])) {
            $dir    = new WDBRecursiveDirectoryIterator($this->getRealPath());
            $hasWip = false;
            $paths  = $known_filenames = array();

            $stack = new \ArrayObject();
            foreach ($dir as $file) {
                $stack->offsetSet($file->getFilename(), $file);
            }
            $stack->uksort(function($a, $b) { return strcmp($a, $b); });

            foreach ($stack as $file) {
                /* @var \WebDocBook\\WebFilesystem\\WDBFile $file */
                $filename = $lang = null;
                if (
                    $file->isDir() &&
                    $file->getBasename() === Kernel::getConfig('user_config:wip_directory', 'wip')
                ) {
                    $hasWip = true;
                } else {

                    if ($file->isLink()) {
                        $filename_real = basename($file->getRealPath());
                        if (strpos($filename_real, '.')!==false) {
                            $filename_parts = explode('.', $filename_real);
                            $filename       = array_shift($filename_parts);
                            $lang           = array_shift($filename_parts);
                            if ($lang==='md') {
                                $lang = null;
                            }
                        }
                    } elseif ($file->isFile()) {
                        $filename_parts = explode('.', $file->getBasename());
                        $filename       = array_shift($filename_parts);
                        $lang           = array_shift($filename_parts);
                        if ($lang==='md') {
                            $lang = null;
                        }
                    } else {
                        $filename       = $file->getBasename();
                    }

                    if (array_key_exists($filename, $paths) && !empty($lang)) {
                        $paths[$filename]['trads'][$lang] = Helper::getRoute($file->getRealPath());
                    } elseif (array_key_exists($filename, $paths)) {
                        $original                   = $paths[$filename];
                        $dbfile                     = new WDBFile($file);
                        $paths[$filename]           = $dbfile->getWDBStack();
                        $paths[$filename]['trads']  = isset($original['trads']) ? $original['trads'] : array();
                        if ($file->isDir() && $recursive) {
                            $dirscan                = $dbfile->getWDBScanStack(true);
                            $paths[$filename]       = array_merge(
                                                        $paths[$filename], $dirscan
                                                    );
                        }
                    } else {
                        $dbfile = new WDBFile($file);
                        if ($this->isDir() && $this->isLink()) {
                            $dbfile->setIsRootLink(true);
                        }
                        $paths[$filename] = $dbfile->getWDBStack();
                        if ($file->isDir() && $recursive) {
                            $dirscan            = $dbfile->getWDBScanStack(true);
                            $paths[$filename]   = array_merge(
                                                    $paths[$filename], $dirscan
                                                );
                        }
                        if (!empty($lang)) {
                            $paths[$filename]['trads'][$lang] = Helper::getRoute($file->getRealPath());
                        }
                    }
                }
            }

            $dir_is_clone = Filesystem::isGitClone($dir->getPath());
            $remote = null;
            if ($dir_is_clone) {
                $git_config = Helper::getGitConfig($dir->getPath());
                if (
                    !empty($git_config) &&
                    isset($git_config['remote origin']) &&
                    isset($git_config['remote origin']['url'])
                ) {
                    $remote = $git_config['remote origin']['url'];
                }
            }

            $this->cache['wdb_scan_stack'] = array(
                'dirname'       => $this->getHumanReadableFilename(),
                'dirpath'       => $this->getWDBPath(),
                'dir_has_wip'   => $hasWip,
                'dir_is_clone'  => $dir_is_clone,
                'clone_remote'  => $remote,
                'dirscan'       => $paths,
            );

        }
        return $this->cache['wdb_scan_stack'];
    }

    /**
     * @return array
     */
    public function getWDBStack()
    {
        if (!isset($this->cache['wdb_stack'])) {
            $truefile = $this;
            $filepath = $truefile->getRealPath();
            if ($this->isLink() || $this->isRootLink()) {
                if ($this->isLink()) {
                    try {
                        $rp_tmp = realpath($this->getLinkTarget());
                    } catch (\Exception $e) {}
                    if (empty($rp_tmp)) {
                        $rp_tmp = $this->getRealPath();
                    }
                    $truefile = new WebFileInfo($rp_tmp);
                } elseif ($this->isRootLink()) {
                    try {
                        $rp_tmp = @readlink($this->getPathname());
                    } catch (\Exception $e) {}
                    if (empty($rp_tmp)) {
                        $rp_tmp = $this->getRealPath();
                    }
                    $truefile = new WebFileInfo($rp_tmp);
                }
            }
            $_size = $truefile->isDir() ? Helper::getDirectorySize($truefile->getPathname()) : $truefile->getSize();
            $this->cache['wdb_stack'] = array(
                'path'      => $this->getWDBPath(),
                'type'      => $this->getType(),
                'route'     => Helper::getRoute($this->getRealPath()),
                'name'      => $this->getHumanReadableFilename(),
                'size'      => WebFilesystem::getTransformedFilesize($_size),
                'plainsize' => $_size,
                'mtime'     => WebFilesystem::getDateTimeFromTimestamp($truefile->getMTime()),
                'description'=> $this->getDescription(),
                'trans'     => $this->isDir() ? array() : $this->findTranslations(),
                'dirpath'   => dirname($this->getPathname()),
                'lines_nb'  => $this->isDir() ? null : Helper::getFileLinesCount($this->getRealPath()),
                'extension' => $this->getExtension(),
            );
        }
        return $this->cache['wdb_stack'];
    }

    /**
     * @return array
     */
    public function getWDBFullStack()
    {
        if (!isset($this->cache['wdb_full_stack'])) {
            $data = $this->getWDBStack();
            $this->cache['wdb_full_stack'] = array_merge($data, array(
                'next'      => $this->findNext(),
                'previous'  => $this->findPrevious(),
            ));
        }
        return $this->cache['wdb_full_stack'];
    }

    /**
     * @return mixed
     * @throws \WebDocBook\Exception\Exception Any caught \WebFilesystem\Finder exceptions
     */
    public function findTranslations()
    {
        if (!isset($this->cache['wdb_translations'])) {
            $filepath = $this->getPathname();
            if ($this->isLink() || $this->isRootLink()) {
                $filepath = Filesystem::slashDirname($this->getWebPath()).$this->getFilename();
            }
            $parts = explode('.', $this->getBasename());
            try {
                $finder = Finder::create()
                    ->files()
                    ->name($parts[0].'.*.md')
                    ->in(dirname(realpath($filepath)))
                    ->depth('0');
            } catch (\Exception $e) {
                throw new Exception(
                    $e->getMessage(), $e->getCode(), $e
                );
            }
            $trads = array();
            foreach($finder->getIterator() as $_file) {
                $subparts                   = explode('.', $_file->getFilename());
                if (count($subparts)==3 && $_file->getRealPath()!=$this->getRealPath()) {
                    $trads[$subparts[1]]    = $_file->getRealPath();
                } elseif (count($subparts)==2 && $_file->getRealPath()!=$this->getRealPath()) {
                    $trads['en']            = $_file->getRealPath();
                }
            }
            $this->cache['wdb_translations'] = $trads;
        }
        return $this->cache['wdb_translations'];
    }

    /**
     * Find next file in current chapter
     *
     * @return array
     */
    public function findNext()
    {
        if (!isset($this->cache['wdb_next'])) {
            $this->cache['wdb_next'] = null;

            $filepath       = $this->getPathname();
            if ($this->isLink() || $this->isRootLink()) {
                $filepath = Filesystem::slashDirname($this->getWebPath()).$this->getFilename();
            }
            $dir_realpath   = dirname(realpath($filepath));
            $dir_targetpath = dirname($filepath);
            if (empty($dir_realpath)) {
                $dir_realpath = $dir_targetpath;
            }
            $dir            = new FilesystemIterator($dir_realpath, FilesystemIterator::CURRENT_AS_PATHNAME);
            $dir_table      = iterator_to_array($dir, false);
            $i              = array_search($this->getRealPath(), $dir_table);
            if (false!==$i) {
                $j = $i+1;
                while ($j<=count($dir_table) && array_key_exists($j, $dir_table) && (
                    (is_dir($dir_table[$j]) && !Helper::isDirValid($dir_table[$j])) || 
                    !Helper::isFileValid($dir_table[$j]) ||
                    Filesystem::isDotPath($dir_table[$j]) || Helper::isTranslationFile($dir_table[$j])
                )) {
                    $j = $j+1;
                }
                if ($j<=count($dir_table) && array_key_exists($j, $dir_table) && (
                        (is_dir($dir_table[$j]) && Helper::isDirValid($dir_table[$j])) ||
                        (!is_dir($dir_table[$j]) && Helper::isFileValid($dir_table[$j]) && !Helper::isTranslationFile($dir_table[$j])) 
                    ) && !Filesystem::isDotPath($dir_table[$j])
                ) {
                    $next = new WDBFile($dir_table[$j]);
                    $this->cache['wdb_next'] = $next->getWDBStack();
                }
            }
        }
        return $this->cache['wdb_next'];
    }
    
    /**
     * Find previous file in current chapter
     *
     * @return array
     */
    public function findPrevious()
    {
        if (!isset($this->cache['wdb_previous'])) {
            $this->cache['wdb_previous'] = null;

            $filepath       = $this->getPathname();
            if ($this->isLink() || $this->isRootLink()) {
                $filepath   = Filesystem::slashDirname($this->getWebPath()).$this->getFilename();
            }
            $dir_realpath   = dirname(realpath($filepath));
            $dir_targetpath = dirname($filepath);
            if (empty($dir_realpath)) {
                $dir_realpath = $dir_targetpath;
            }
            $dir                = new FilesystemIterator($dir_realpath, FilesystemIterator::CURRENT_AS_PATHNAME);
            $dir_table          = iterator_to_array($dir, false);
            $i                  = array_search($this->getRealPath(), $dir_table);
            if (false!==$i) {
                $j = $i-1;
                while ($j>=0 && array_key_exists($j, $dir_table) && (
                    (is_dir($dir_table[$j]) && !Helper::isDirValid($dir_table[$j])) || 
                    !Helper::isFileValid($dir_table[$j]) ||
                    Filesystem::isDotPath($dir_table[$j]) || Helper::isTranslationFile($dir_table[$j])
                )) {
                    $j = $j-1;
                }
                if ($j>=0 && array_key_exists($j, $dir_table) && (
                        (is_dir($dir_table[$j]) && Helper::isDirValid($dir_table[$j])) ||
                        (!is_dir($dir_table[$j]) && Helper::isFileValid($dir_table[$j]) && !Helper::isTranslationFile($dir_table[$j])) 
                    ) && !Filesystem::isDotPath($dir_table[$j])
                ) {
                    $previous = new WDBFile($dir_table[$j]);
                    $this->cache['wdb_previous'] = $previous->getWDBStack();
                }
            }
        }
        return $this->cache['wdb_previous'];
    }

    /**
     * @return string
     */
    public function getHumanReadableFilename()
    {
        if (!isset($this->cache['wdb_human_readable_filename'])) {
            if (
                Filesystem::slashDirname($this->getRealPath())===Kernel::getPath('web') ||
                Filesystem::slashDirname($this->getRealPath())==='/'
            ) {
                $this->cache['wdb_human_readable_filename'] = _T('Home');
            } else {
                $this->cache['wdb_human_readable_filename'] = parent::getHumanReadableFilename();
            }
        }
        return $this->cache['wdb_human_readable_filename'];
    }

    /**
     * @return mixed
     */
    public function findReadme()
    {
        if (!isset($this->cache['wdb_readme'])) {
            $readme = Filesystem::slashDirname($this->getRealPath()).
                Kernel::getConfig('user_config:readme_filename', 'README.md');
            $this->cache['wdb_readme'] = file_exists($readme) ? $readme : null;
        }
        return $this->cache['wdb_readme'];
    }

    /**
     * @return mixed
     */
    public function findIndex()
    {
        if (!isset($this->cache['wdb_index'])) {
            $index = Filesystem::slashDirname($this->getRealPath()).
                Kernel::getConfig('user_config:index_filename', 'INDEX.md');
            $this->cache['wdb_index'] = file_exists($index) ? $index : null;
        }
        return $this->cache['wdb_index'];
    }

    /**
     * @return \I18n\str|string
     */
    public function getDescription()
    {
        if (!isset($this->cache['wdb_description'])) {

            $name = strtolower($this->getBasename());
            $cfg_esc = Kernel::getConfig('descriptions', array());
            if (!empty($cfg_esc) && is_array($cfg_esc) && array_key_exists($name, $cfg_esc)) {
                return _T($cfg_esc[$name]);
            }

            $extension = strtolower($this->getExtension());
            $cfg_ext = Kernel::getConfig('descriptions_extensions', array());
            if (!empty($cfg_ext) && is_array($cfg_ext) && array_key_exists($extension, $cfg_ext)) {
                $this->cache['wdb_description'] = _T($cfg_ext[$extension]);
            } else {
                $this->cache['wdb_description'] = '';
            }
        }
        return $this->cache['wdb_description'];
    }

    /**
     * @param int $str_len
     * @param bool $strip_tags
     * @return string
     */
    public function viewIntroduction($str_len = 600, $strip_tags = true , $end_str = '')
    {
        if (!isset($this->cache['wdb_introduction'])) {
            $intro = $this->getFile()->getIntroduction();
            $this->cache['wdb_introduction'] = TextHelper::cut(
                ($strip_tags ? strip_tags($intro) : $intro),
                $str_len,
                $end_str
            );
        }
        return $this->cache['wdb_introduction'];
    }

    /**
     * @return string
     */
    public function viewFileInfos()
    {
        if (!isset($this->cache['wdb_file_infos'])) {
            $this->cache['wdb_file_infos'] = $this->getFile()->viewFileInfos(array(
                'page'      => $this->getWDBStack(),
                'contents'  => $this->getWDBFullStack(),
                'dirscan'   => $this->isDir() ? $this->getWDBScanStack() : null,
            ));
        }
        return $this->cache['wdb_file_infos'];
    }

    /**
     * @param null $path
     * @return int|string
     */
    public function getWDBTypeByPath($path = null)
    {
        $_file = new WebFileInfo($path);
        if ($_file->isDir()) {
            return 'directory';
        }
        $config = Kernel::getConfig('file_types', array());
        foreach ($config as $type=>$infos) {
            $extensions = isset($infos['extensions']) ? explode(',', $infos['extensions']) : array();
            if (in_array($_file->getExtension(), $extensions)) {
                return $type;
            }            
        }
        return 'default';
    }
    
}

// Endfile
