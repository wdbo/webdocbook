<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook\WebFilesystem;

use DocBook\FrontController,
    DocBook\Helper;

use WebFilesystem\WebFilesystem,
    WebFilesystem\WebFileInfo,
    WebFilesystem\WebFilesystemIterator,
    WebFilesystem\Finder;

use Library\Helper\Directory as DirectoryHelper;

use \FilesystemIterator;

/**
 */
class DocBookFile extends WebFileInfo
{

    protected $docbook;

    public function __construct($file_name)
    {
        $this->docbook = FrontController::getInstance();
        $_root = DirectoryHelper::slashDirname($this->docbook->getPath('base_dir_http'));
        parent::__construct($_root.str_replace($_root, '', $file_name));
        $this->setRootDir($this->docbook->getPath('base_dir_http'));
        $this->setWebPath( dirname($file_name) );
    }

    public function getDocBookScanStack()
    {
        $dir = new DocBookRecursiveDirectoryIterator($this->getRealPath());
        $hasWip = false;
        $paths = $known_filenames = array();
        foreach($dir as $file) {
            $filename = $lang = null;
            if ($file->isDir() && $file->getBasename()===FrontController::WIP_DIR) {
                $hasWip = true;
            } else {
                if ($file->isFile()) {
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
                    $paths[$filename]['trads'] = $original['trads'];
                } else {
                    $dbfile = new DocBookFile($file);
                    $paths[$filename] = $dbfile->getDocBookStack();
                    if (!empty($lang)) {
                        $paths[$filename]['trads'][$lang] = Helper::getRoute($file->getRealPath());
                    }
                }
            }
        }

        return array(
            'dirname'       => $this->getHumanReadableFilename(),
            'dirpath'       => $dir->getPath(),
            'dir_has_wip'   => $hasWip,
            'dir_is_clone'  => DirectoryHelper::isGitClone($dir->getPath()),
            'dirscan'       => $paths,
        );
    }
    
    public function getDocBookStack()
    {
        $truefile = $this;
        if (is_link($this->getFilename())) {
            $truefile = new WebFileInfo(realpath($this->getFilename()));
        }
        return array(
            'path'      =>$this->getRealPath(),
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
        $parts = explode('.', $this->getBasename());
        $finder = Finder::create()
            ->files()
            ->name(array_shift($parts).'*.md')
            ->in(dirname($this->getPathname()))
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
        $dir = new FilesystemIterator(dirname($this->getRealPath()), FilesystemIterator::CURRENT_AS_PATHNAME);
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
                return $dir_table[$j];
            }
        }
        return null;
    }
    
    public function findPrevious()
    {
        $dir = new FilesystemIterator(dirname($this->getRealPath()), FilesystemIterator::CURRENT_AS_PATHNAME);
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
                return $dir_table[$j];
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
