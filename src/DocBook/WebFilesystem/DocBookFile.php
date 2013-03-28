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
    WebFilesystem\WebFileInfo;

use Symfony\Component\Finder\Finder;

use \FilesystemIterator;

/**
 */
class DocBookFile extends WebFileInfo
{

    protected $docbook;

    public function __construct($file_name)
    {
        $this->docbook = FrontController::getInstance();
        $_root = Helper::slashDirname($this->docbook->getPath('base_dir_http'));
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
            'dirscan'       => $paths
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
            'type'      =>$this->isDir() ? 'dir' : 'file',
            'route'     =>Helper::getRoute($this->getRealPath()),
            'name'      =>$this->getHumanReadableFilename(),
            'size'      =>$truefile->isDir() ? 
                Helper::getDirectorySize($truefile->getPathname()) : WebFilesystem::getTransformedFilesize($truefile->getSize()),
            'mtime'     =>WebFilesystem::getDateTimeFromTimestamp($truefile->getMTime()),
            'description'=>'',
            'next'      =>$this->findNext(),
            'previous'  =>$this->findPrevious(),
            'trans'     =>$this->isDir() ? array() : $this->findTranslations(),
            'dirpath'   =>dirname($this->getPathname()),
        );
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
        return ($i && array_key_exists($i+1, $dir_table) && !is_dir($dir_table[$i+1])) ? $dir_table[$i+1] : null;
    }
    
    public function findPrevious()
    {
        $dir = new FilesystemIterator(dirname($this->getRealPath()), FilesystemIterator::CURRENT_AS_PATHNAME);
        $dir_table = iterator_to_array($dir, false);
        $i = array_search($this->getRealPath(), $dir_table);
        return ($i && array_key_exists($i-1, $dir_table) && !is_dir($dir_table[$i-1])) ? $dir_table[$i-1] : null;
    }
    
	public function getHumanReadableFilename()
	{
        $docbook = FrontController::getInstance();
        if (
            Helper::slashDirname($this->getRealPath())===Helper::slashDirname($docbook->getPath('base_dir_http')) ||
            Helper::slashDirname($this->getRealPath())==='/'
        ) {
            return 'Home';
        }
		return parent::getHumanReadableFilename();
	}

}

// Endfile
