<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Controller;

use DocBook\FrontController,
    DocBook\Helper,
    DocBook\Locator,
    DocBook\Abstracts\AbstractController,
    DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator;

use Markdown\Parser,
    Markdown\ExtraParser;

use WebFilesystem\WebFilesystem,
    WebFilesystem\WebFileInfo;

/**
 */
class DefaultController extends AbstractController
{

    public function indexAction($path)
    {
        if (@is_dir($path)) {
            return $this->directoryAction($path);
        } else {
            return $this->fileAction($path);
        }
    }

    public function fileAction($path)
    {
        $this->setPath($path);
        $tpl_params = array();

        $tpl_params['breadcrumbs'] = Helper::getBreadcrumbs($this->getPath());

        $tpl_params['title'] = Helper::buildPageTitle($this->getPath());
        if (empty($tpl_params['title'])) {
            if (!empty($tpl_params['breadcrumbs'])) {
                $tpl_params['title'] = Helper::buildPageTitle(end($tpl_params['breadcrumbs']));
            } else {
                $tpl_params['title'] = 'Home';
            }
        }

        $update_time = Helper::getDateTimeFromTimestamp(filemtime($path));

        $file_content = file_get_contents($this->getPath());
        $md_parser = $this->docbook->getMarkdownParser();

        $page_infos = array(
            'name'      => basename($this->getPath()),
            'path'      => $this->getPath(),
            'update'    => $update_time
        );
        $tpl_params['page'] = $page_infos;

        $content = $this->docbook->display(
            $md_parser->transform($file_content),
            'content',
            array('page'=>$page_infos)
        );

        return array('default', $content, $tpl_params);
    }

    public function directoryAction($path)
    {
        $this->setPath($path);
        $tpl_params = array();
        $readme_content = $dir_content = '';

        $index = Locator::findPathIndex($this->getPath());
        if (file_exists($index)) {
            return $this->fileAction($index);
        }

        $readme = Locator::findPathReadme($this->getPath());
        if (file_exists($readme)) {
            $update_time = Helper::getDateTimeFromTimestamp(filemtime($readme));
            $this->docbook->setInputFile($readme);
            $md_parser = $this->docbook->getMarkdownParser();
            $content = file_get_contents($readme);
            $readme_content = $this->docbook->display(
                $md_parser->transform($content), 
                'content',
                array('page'=>array(
                    'name'      => basename($readme),
                    'path'      => $readme,
                    'update'    => $update_time
                ))
            );
        }

        $tpl_params['page_tools'] = 'false';

        $tpl_params['breadcrumbs'] = Helper::getBreadcrumbs($this->getPath());

        $tpl_params['title'] = Helper::buildPageTitle($this->getPath());
        if (empty($tpl_params['title'])) {
            if (!empty($tpl_params['breadcrumbs'])) {
                $tpl_params['title'] = Helper::buildPageTitle(end($tpl_params['breadcrumbs']));
            } else {
                $tpl_params['title'] = 'Home';
            }
        }

        $dir = new DocBookRecursiveDirectoryIterator($this->getPath());
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
                    $paths[$filename] = $this->getNewScanEntry($file);
                    $paths[$filename]['trads'] = $original['trads'];
                } else {
                    $paths[$filename] = $this->getNewScanEntry($file);
                    if (!empty($lang)) {
                        $paths[$filename]['trads'][$lang] = Helper::getRoute($file->getRealPath());
                    }
                }
            }
        }

        $dir_content = $this->docbook->display(array(
            'dirname'       => WebFilesystem::getHumanReadableName(end(explode('/', $this->getPath()))),
            'dirpath'       => $dir->getPath(),
            'dir_has_wip'   => $hasWip,
            'dirscan'       => $paths
        ), 'dirindex');

        return array('default', $dir_content.$readme_content, $tpl_params);
    }

    public static function getNewScanEntry(WebFileInfo $file)
    {
        return array(
            'type'      =>$file->isDir() ? 'dir' : 'file',
            'path'      =>Helper::getSecuredRealpath($file->getRealPath()),
            'route'     =>Helper::getRoute($file->getRealPath()),
            'name'      =>$file->getHumanReadableFilename(),
            'size'      =>$file->isDir() ? 
                Helper::getDirectorySize($file->getRealPath()) : WebFilesystem::getTransformedFilesize($file->getSize()),
            'mtime'     =>WebFilesystem::getDateTimeFromTimestamp($file->getMTime()),
            'description'=>'',
            'trads'     => array()
        );
    }
    
    public function headerOnlyAction()
    {
        return array('layout_header_only', '');
    }

    public function footerOnlyAction($path)
    {
        if (!empty($path)) {
            $readme = Locator::findPathReadme($path);
            if (file_exists($readme)) {
                $this->docbook->setInputFile($readme);
                $md_parser = $this->docbook->getMarkdownParser();
                $content = file_get_contents($readme);
                return array('layout_noheader', $md_parser->transform($content));
            }
        }
        return array('layout_noheader', '');
    }

    public function htmlOnlyAction($path)
    {
        $this->setPath($path);
        $md_parser = $this->docbook->getMarkdownParser();
        $content = file_get_contents($this->getPath());
        return array('layout_empty_html', $md_parser->transform($content));
    }

    public function plainTextAction($path)
    {
        $this->setPath($path);
        $ctt = $this->docbook->getResponse()->flush(file_get_contents($this->getPath()));
        return array('layout_empty_txt', $ctt);
    }


}

// Endfile
