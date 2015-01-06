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

namespace DocBook\WebFilesystem;

use \DocBook\FrontController;
use \DocBook\Helper;
use \WebFilesystem\WebFilesystem;
use \WebFilesystem\WebFileInfo;
use \WebFilesystem\WebFilesystemIterator;
use \WebFilesystem\Finder;
use \Library\Helper\Directory as DirectoryHelper;
use \Library\Helper\Text as TextHelper;
use \FilesystemIterator;

/**
 */
class DocBookFile
    extends WebFileInfo
{

    protected $is_root_link = false;
    protected $type;

    protected $docbook;
    protected static $config;

    protected $cache        = array();

    public function __construct($file_name)
    {
        $this->docbook = FrontController::getInstance();
        $_root = DirectoryHelper::slashDirname($this->docbook->getPath('base_dir_http'));
        if (substr_count($file_name, $_root)>0) {
            $realpath = $_root.str_replace($_root, '', $file_name);
            parent::__construct($realpath);
        } else {
            parent::__construct($file_name);
        }
        $this->_init($file_name);
    }

    protected function _init($file_name)
    {
        if (empty(self::$config)) {
            self::$config = $this->docbook->getRegistry()->get('file_types', array(), 'docbook');
        }

        $_root = DirectoryHelper::slashDirname($this->docbook->getPath('base_dir_http'));
        if (substr_count($file_name, $_root)>0) {
            $realpath = $_root.str_replace($_root, '', $file_name);
            $this->type = $this->getDocBookTypeByPath($realpath);
            $this->setRootDir($_root);
            $this->setWebPath(dirname($file_name));
            if (is_link($realpath)) {
                $this->setIsRootLink(true);
            }
        } else {
            $this->type = $this->getDocBookTypeByPath($file_name);
            $this->setRootDir(dirname($file_name));
            $this->setWebPath($_root.$this->docbook->getInputPath());
        }

        $_class_name = isset(self::$config[$this->type]['class']) ? self::$config[$this->type]['class'] : null;
        if (is_null($_class_name) || !class_exists($_class_name)) {
            $_class_name = '\DocBook\WebFilesystem\DocBookFile\\'
                .(isset(self::$config[$this->type]['class']) ? self::$config[$this->type]['class'] : 'DBDefault');
        }
        if (class_exists($_class_name)) {
            $_file = new $_class_name($file_name);
        } else {
            throw new ErrorException("DocBook file class '$_class_name' not found!");
        }
        $this->setFile($_file);
        $this->getFile()->setRootDir($_root);
        $this->getFile()->setWebPath($_root.$this->docbook->getInputPath());
    }

    public function setFile(DocBookFileInterface $file)
    {
        $this->_file = $file;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function setIsRootLink($is_it = false)
    {
        $this->is_root_link = $is_it;
        return $this;
    }

    public function getIsRootLink()
    {
        return $this->is_root_link;
    }

    public function isRootLink()
    {
        return true===$this->getIsRootLink();
    }

    public function isChildOfLink()
    {
        $_root  = DirectoryHelper::slashDirname($this->docbook->getPath('base_dir_http'));
        $_dir   = DirectoryHelper::slashDirname($this->getRealPath());
        return (substr_count($_dir, $_root) == 0);
    }

    public function getDocBookPath()
    {
        if (!isset($this->cache['docbook_path'])) {
            $filepath = $this->getRealPath();
            if ($this->isLink() || $this->isRootLink()) {
                $filepath   = DirectoryHelper::slashDirname($this->getWebPath()).$this->getFilename();
            } elseif ($this->isChildOfLink()) {
                $filepath   = DirectoryHelper::slashDirname($this->getWebPath()).$this->getFilename();
            }
            $this->cache['docbook_path'] = $filepath;
        }
        return $this->cache['docbook_path'];
    }
    
    public function getDocBookScanStack($recursive = false)
    {
        if (!isset($this->cache['docbook_scan_stack'])) {
            $dir = new DocBookRecursiveDirectoryIterator($this->getRealPath());
            $hasWip = false;
            $paths = $known_filenames = array();
            foreach ($dir as $file) {
                /* @var \DocBook\\WebFilesystem\\DocBookFile $file */
                $filename = $lang = null;
                if ($file->isDir() && $file->getBasename()===FrontController::WIP_DIR) {
                    $hasWip = true;
                } else {
                    if ($file->isLink()) {
                        $filename_real = basename($file->getRealPath());
                        if (strpos($filename_real, '.')!==false) {
                            $filename_parts = explode('.', $filename_real);
                            $filename = array_shift($filename_parts);
                            $lang = array_shift($filename_parts);
                            if ($lang==='md') $lang = null;
                        }
                    } elseif ($file->isFile()) {
                        $filename_parts = explode('.', $file->getBasename());
                        $filename = array_shift($filename_parts);
                        $lang = array_shift($filename_parts);
                        if ($lang==='md') $lang = null;
                    } else {
                        $filename = $file->getBasename();
                    }
                    if (array_key_exists($filename, $paths) && !empty($lang)) {
                        $paths[$filename]['trads'][$lang] = Helper::getRoute($file->getRealPath());
                    } elseif (array_key_exists($filename, $paths)) {
                        $original = $paths[$filename];
                        $dbfile = new DocBookFile($file);
                        $paths[$filename] = $dbfile->getDocBookStack();
                        $paths[$filename]['trads'] = isset($original['trads']) ? $original['trads'] : array();
                        if ($file->isDir() && $recursive) {
                            $dirscan = $dbfile->getDocBookScanStack(true);
                            $paths[$filename] = array_merge(
                                $paths[$filename], $dirscan
                            );
                        }
                    } else {
                        $dbfile = new DocBookFile($file);
                        if ($this->isDir() && $this->isLink()) {
                            $dbfile->setIsRootLink(true);
                        }
                        $paths[$filename] = $dbfile->getDocBookStack();
                        if ($file->isDir() && $recursive) {
                            $dirscan = $dbfile->getDocBookScanStack(true);
                            $paths[$filename] = array_merge(
                                $paths[$filename], $dirscan
                            );
                        }
                        if (!empty($lang)) {
                            $paths[$filename]['trads'][$lang] = Helper::getRoute($file->getRealPath());
                        }
                    }
                }
            }

            $dir_is_clone = DirectoryHelper::isGitClone($dir->getPath());
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

            $this->cache['docbook_scan_stack'] = array(
                'dirname'       => $this->getHumanReadableFilename(),
                'dirpath'       => $this->getDocBookPath(),
                'dir_has_wip'   => $hasWip,
                'dir_is_clone'  => $dir_is_clone,
                'clone_remote'  => $remote,
                'dirscan'       => $paths,
            );

        }
        return $this->cache['docbook_scan_stack'];
    }
    
    public function getDocBookStack()
    {
        if (!isset($this->cache['docbook_stack'])) {
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
            $this->cache['docbook_stack'] = array(
                'path'      => $this->getDocBookPath(),
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
        return $this->cache['docbook_stack'];
    }
    
    public function getDocBookFullStack()
    {
        if (!isset($this->cache['docbook_full_stack'])) {
            $data = $this->getDocBookStack();
            $this->cache['docbook_full_stack'] = array_merge($data, array(
                'next'      =>$this->findNext(),
                'previous'  =>$this->findPrevious(),
            ));
        }
        return $this->cache['docbook_full_stack'];
    }
    
    public function findTranslations()
    {
        if (!isset($this->cache['docbook_translations'])) {
            $filepath = $this->getPathname();
            if ($this->isLink() || $this->isRootLink()) {
                $filepath = DirectoryHelper::slashDirname($this->getWebPath()).$this->getFilename();
            }
            $parts = explode('.', $this->getBasename());
            $finder = Finder::create()
                ->files()
                ->name($parts[0].'.*.md')
                ->in(dirname(realpath($filepath)))
                ->depth('0');
            $trads = array();
            foreach($finder->getIterator() as $_file) {
                $subparts = explode('.', $_file->getFilename());
                if (count($subparts)==3 && $_file->getRealPath()!=$this->getRealPath()) {
                    $trads[$subparts[1]] = $_file->getRealPath();
                } elseif (count($subparts)==2 && $_file->getRealPath()!=$this->getRealPath()) {
                    $trads['en'] = $_file->getRealPath();
                }
            }
            $this->cache['docbook_translations'] = $trads;
        }
        return $this->cache['docbook_translations'];
    }

    /**
     * Find next file in current chapter
     *
     * @return array
     */
    public function findNext()
    {
        if (!isset($this->cache['docbook_next'])) {
            $this->cache['docbook_next'] = null;

            $filepath = $this->getPathname();
            if ($this->isLink() || $this->isRootLink()) {
                $filepath = DirectoryHelper::slashDirname($this->getWebPath()).$this->getFilename();
            }
            $dir_realpath = dirname(realpath($filepath));
            $dir_targetpath = dirname($filepath);
            if (empty($dir_realpath)) {
                $dir_realpath = $dir_targetpath;
            }
            $dir = new FilesystemIterator($dir_realpath, FilesystemIterator::CURRENT_AS_PATHNAME);
            $dir_table = iterator_to_array($dir, false);
            $i = array_search($this->getRealPath(), $dir_table);
            if (false!==$i) {
                $j = $i+1;
                while ($j<=count($dir_table) && array_key_exists($j, $dir_table) && (
                    (is_dir($dir_table[$j]) && !Helper::isDirValid($dir_table[$j])) || 
                    !Helper::isFileValid($dir_table[$j]) || 
                    DirectoryHelper::isDotPath($dir_table[$j]) || Helper::isTranslationFile($dir_table[$j])
                )) {
                    $j = $j+1;
                }
                if ($j<=count($dir_table) && array_key_exists($j, $dir_table) && (
                        (is_dir($dir_table[$j]) && Helper::isDirValid($dir_table[$j])) ||
                        (!is_dir($dir_table[$j]) && Helper::isFileValid($dir_table[$j]) && !Helper::isTranslationFile($dir_table[$j])) 
                    ) && !DirectoryHelper::isDotPath($dir_table[$j])
                ) {
                    $next = new DocBookFile($dir_table[$j]);
                    $this->cache['docbook_next'] = $next->getDocBookStack();
                }
            }
        }
        return $this->cache['docbook_next'];
    }
    
    /**
     * Find previous file in current chapter
     *
     * @return array
     */
    public function findPrevious()
    {
        if (!isset($this->cache['docbook_previous'])) {
            $this->cache['docbook_previous'] = null;

            $filepath = $this->getPathname();
            if ($this->isLink() || $this->isRootLink()) {
                $filepath = DirectoryHelper::slashDirname($this->getWebPath()).$this->getFilename();
            }
            $dir_realpath = dirname(realpath($filepath));
            $dir_targetpath = dirname($filepath);
            if (empty($dir_realpath)) {
                $dir_realpath = $dir_targetpath;
            }
            $dir = new FilesystemIterator($dir_realpath, FilesystemIterator::CURRENT_AS_PATHNAME);
            $dir_table = iterator_to_array($dir, false);
            $i = array_search($this->getRealPath(), $dir_table);
            if (false!==$i) {
                $j = $i-1;
                while ($j>=0 && array_key_exists($j, $dir_table) && (
                    (is_dir($dir_table[$j]) && !Helper::isDirValid($dir_table[$j])) || 
                    !Helper::isFileValid($dir_table[$j]) || 
                    DirectoryHelper::isDotPath($dir_table[$j]) || Helper::isTranslationFile($dir_table[$j])
                )) {
                    $j = $j-1;
                }
                if ($j>=0 && array_key_exists($j, $dir_table) && (
                        (is_dir($dir_table[$j]) && Helper::isDirValid($dir_table[$j])) ||
                        (!is_dir($dir_table[$j]) && Helper::isFileValid($dir_table[$j]) && !Helper::isTranslationFile($dir_table[$j])) 
                    ) && !DirectoryHelper::isDotPath($dir_table[$j])
                ) {
                    $previous = new DocBookFile($dir_table[$j]);
                    $this->cache['docbook_previous'] = $previous->getDocBookStack();
                }
            }
        }
        return $this->cache['docbook_previous'];
    }
    
    public function getHumanReadableFilename()
    {
        if (!isset($this->cache['docbook_human_readable_filename'])) {
            $docbook = FrontController::getInstance();
            if (
                DirectoryHelper::slashDirname($this->getRealPath())===DirectoryHelper::slashDirname($docbook->getPath('base_dir_http')) ||
                DirectoryHelper::slashDirname($this->getRealPath())==='/'
            ) {
                $this->cache['docbook_human_readable_filename'] = _T('Home');
            } else {
                $this->cache['docbook_human_readable_filename'] = parent::getHumanReadableFilename();
            }
        }
        return $this->cache['docbook_human_readable_filename'];
    }

    public function findReadme()
    {
        if (!isset($this->cache['docbook_readme'])) {
            $readme = DirectoryHelper::slashDirname($this->getRealPath()).FrontController::README_FILE;
            $this->cache['docbook_readme'] = file_exists($readme) ? $readme : null;
        }
        return $this->cache['docbook_readme'];
    }

    public function findIndex()
    {
        if (!isset($this->cache['docbook_index'])) {
            $index = DirectoryHelper::slashDirname($this->getRealPath()).FrontController::INDEX_FILE;
            $this->cache['docbook_index'] = file_exists($index) ? $index : null;
        }
        return $this->cache['docbook_index'];
    }

    public function getDescription()
    {
        if (!isset($this->cache['docbook_description'])) {
            $docbook = FrontController::getInstance();

            $name = strtolower($this->getBasename());
            $cfg_esc = $docbook->getRegistry()->get('descriptions', array(), 'docbook');
            if (!empty($cfg_esc) && is_array($cfg_esc) && array_key_exists($name, $cfg_esc)) {
                return _T($cfg_esc[$name]);
            }

            $extension = strtolower($this->getExtension());
            $cfg_ext = $docbook->getRegistry()->get('descriptions_extensions', array(), 'docbook');
            if (!empty($cfg_ext) && is_array($cfg_ext) && array_key_exists($extension, $cfg_ext)) {
                $this->cache['docbook_description'] = _T($cfg_ext[$extension]);
            } else {
                $this->cache['docbook_description'] = '';
            }
        }
        return $this->cache['docbook_description'];
    }

    /**
     * @param int $str_len
     * @param bool $strip_tags
     * @return string
     */
    public function viewIntroduction($str_len = 600, $strip_tags = true , $end_str = '')
    {
        if (!isset($this->cache['docbook_introduction'])) {
            $intro = $this->getFile()->getIntroduction();
            $this->cache['docbook_introduction'] = TextHelper::cut(
                ($strip_tags ? strip_tags($intro) : $intro),
                $str_len,
                $end_str
            );
        }
        return $this->cache['docbook_introduction'];
    }

    /**
     * @return string
     */
    public function viewFileInfos()
    {
        if (!isset($this->cache['docbook_file_infos'])) {
            $this->cache['docbook_file_infos'] = $this->getFile()->viewFileInfos(array(
                'page'=>$this->getDocBookStack(),
                'contents'=>$this->getDocBookFullStack(),
                'dirscan'=>$this->isDir() ? $this->getDocBookScanStack() : null,
            ));
        }
        return $this->cache['docbook_file_infos'];
    }

    public function getDocBookTypeByPath($path = null)
    {
        $_file = new WebFileInfo($path);
        if ($_file->isDir()) {
            return 'directory';
        }
        $config = $this->docbook->getRegistry()->get('file_types', array(), 'docbook');
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
