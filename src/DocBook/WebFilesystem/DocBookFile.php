<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        http://github.com/atelierspierrot/docbook
 */

namespace DocBook\WebFilesystem;

use \DocBook\FrontController,
    \DocBook\Helper;

use \WebFilesystem\WebFilesystem,
    \WebFilesystem\WebFileInfo,
    \WebFilesystem\WebFilesystemIterator,
    \WebFilesystem\Finder;

use \Library\Helper\Directory as DirectoryHelper;

use \FilesystemIterator;

/**
 */
class DocBookFile
    extends WebFileInfo
{

    protected $is_root_link = false;

    protected $docbook;

    public function __construct($file_name)
    {
        $this->docbook = FrontController::getInstance();
        $_root = DirectoryHelper::slashDirname($this->docbook->getPath('base_dir_http'));
        if (substr_count($file_name, $_root)>0) {
            $realpath = $_root.str_replace($_root, '', $file_name);
            parent::__construct($realpath);
            $this->setRootDir($_root);
            $this->setWebPath(dirname($file_name));
            if (is_link($realpath)) {
                $this->setIsRootLink(true);
            }
        } else {
            parent::__construct($file_name);
            $this->setRootDir(dirname($file_name));
            $this->setWebPath($_root.$this->docbook->getInputPath());
        }
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

    public function getDocBookPath()
    {
        $filepath = $this->getRealPath();
        if ($this->isLink() || $this->isRootLink()) {
            $filepath = DirectoryHelper::slashDirname($this->getWebPath()).$this->getFilename();
        }
        return $filepath;
    }
    
    public function getDocBookScanStack()
    {
        $dir = new DocBookRecursiveDirectoryIterator($this->getRealPath());
        $hasWip = false;
        $paths = $known_filenames = array();
        foreach ($dir as $file) {
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
                } else {
                    $dbfile = new DocBookFile($file);
                    if ($this->isDir() && $this->isLink()) {
                        $dbfile->setIsRootLink(true);
                    }
                    $paths[$filename] = $dbfile->getDocBookStack();
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

        return array(
            'dirname'       => $this->getHumanReadableFilename(),
            'dirpath'       => $dir->getPath(),
            'dir_has_wip'   => $hasWip,
            'dir_is_clone'  => $dir_is_clone,
            'clone_remote'  => $remote,
            'dirscan'       => $paths,
        );
    }
    
    public function getDocBookStack()
    {
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
        return array(
            'path'      =>$this->getDocBookPath(),
            'type'      =>$this->getDocBookType(),
            'route'     =>Helper::getRoute($this->getRealPath()),
            'name'      =>$this->getHumanReadableFilename(),
            'size'      =>$truefile->isDir() ? 
                Helper::getDirectorySize($truefile->getPathname()) : WebFilesystem::getTransformedFilesize($truefile->getSize()),
            'mtime'     =>WebFilesystem::getDateTimeFromTimestamp($truefile->getMTime()),
            'description'=>$this->getDescription(),
            'next'      =>$this->isDir() ? false : $this->findNext(),
            'previous'  =>$this->isDir() ? false : $this->findPrevious(),
            'trans'     =>$this->isDir() ? array() : $this->findTranslations(),
            'dirpath'   =>dirname($this->getPathname()),
            'lines_nb'  =>$this->isDir() ? null : Helper::getFileLinesCount($this->getRealPath()),
            'extension' =>$this->getExtension(),
        );
    }
    
    public function getDocBookType()
    {
        if ($this->isDir()) {
            return 'dir';
        } elseif (WebFilesystem::isCommonImage($this->getFilename())) {
            return 'img';
        } elseif ('md'===$this->getExtension()) {
            return 'md';
        }
        return 'file';
    }
    
    public function findTranslations()
    {
        $filepath = $this->getPathname();
        if ($this->isLink() || $this->isRootLink()) {
            $filepath = DirectoryHelper::slashDirname($this->getWebPath()).$this->getFilename();
        }
        $parts = explode('.', $this->getBasename());
        $finder = Finder::create()
            ->files()
            ->name(array_shift($parts).'*.md')
            ->in(dirname(realpath($filepath)))
            ->depth('0');
        $trads = array();
        foreach($finder->getIterator() as $_file) {
            $parts = explode('.', $_file->getFilename());
            if (count($parts)==3 && $_file->getRealPath()!=$this->getRealPath()) {
                $trads[$parts[1]] = $_file->getRealPath();
            } elseif (count($parts)==2 && $_file->getRealPath()!=$this->getRealPath()) {
                $trads['en'] = $_file->getRealPath();
            }
        }
        return $trads;
    }

    public function findNext()
    {
        $filepath = $this->getPathname();
        if ($this->isLink() || $this->isRootLink()) {
            $filepath = DirectoryHelper::slashDirname($this->getWebPath()).$this->getFilename();
        }
        $dir_realpath = dirname(realpath($filepath));
        $dir_targetpath = dirname($filepath);
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
                return str_replace($dir_realpath, $dir_targetpath, $dir_table[$j]);
            }
        }
        return null;
    }
    
    public function findPrevious()
    {
        $filepath = $this->getPathname();
        if ($this->isLink() || $this->isRootLink()) {
            $filepath = DirectoryHelper::slashDirname($this->getWebPath()).$this->getFilename();
        }
        $dir_realpath = dirname(realpath($filepath));
        $dir_targetpath = dirname($filepath);
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
                return str_replace($dir_realpath, $dir_targetpath, $dir_table[$j]);
            }
        }
        return null;
    }
    
    public function getHumanReadableFilename()
    {
        $docbook = FrontController::getInstance();
        if (
            DirectoryHelper::slashDirname($this->getRealPath())===DirectoryHelper::slashDirname($docbook->getPath('base_dir_http')) ||
            DirectoryHelper::slashDirname($this->getRealPath())==='/'
        ) {
            return _T('Home');
        }
        return parent::getHumanReadableFilename();
    }

    public function findReadme()
    {
        $readme = DirectoryHelper::slashDirname($this->getRealPath()).FrontController::README_FILE;
        return file_exists($readme) ? $readme : null;
    }

    public function findIndex()
    {
        $index = DirectoryHelper::slashDirname($this->getRealPath()).FrontController::INDEX_FILE;
        return file_exists($index) ? $index : null;
    }

    public function getDescription()
    {
        $docbook = FrontController::getInstance();
        $name = strtolower($this->getBasename());
        $cfg = $docbook->getRegistry()->get('descriptions', array(), 'docbook');
        if (array_key_exists($name, $cfg)) {
            return _T($cfg[$name]);
        }
        return '';
    }

}

// Endfile
