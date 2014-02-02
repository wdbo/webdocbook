<?php
/**
 * PHP / Markdown Extended : DocBook
 * @author      Pierre Cassat & contributors
 * @package     DocBook
 * @copyleft    Les Ateliers Pierrot <ateliers-pierrot.fr>
 * @license     GPL-v3
 * @sources     http://github.com/atelierspierrot/docbook
 */

namespace DocBook\WebFilesystem\DocBookFile;

use \DocBook\FrontController,
    \DocBook\Helper,
    \DocBook\WebFilesystem\DocBookFile,
    \DocBook\WebFilesystem\DocBookFileInterface;

use \WebFilesystem\WebFilesystem,
    \WebFilesystem\WebFileInfo,
    \WebFilesystem\WebFilesystemIterator,
    \WebFilesystem\FileType\WebImage,    
    \WebFilesystem\Finder;

use \Library\Helper\Directory as DirectoryHelper;

use \FilesystemIterator;

/**
 */
class DBImage
    extends WebImage
    implements DocBookFileInterface
{

    /**
     * @param array $params
     * @return string
     */
    public function viewFileInfos(array $params = array())
    {
        $img = new WebImage($this->getRealPath());
        $params = array_merge($params, array(
            'height'=>$this->getHeight(),
            'width'=>$this->getWidth(),
        ));
        return FrontController::getInstance()
            ->display( $this->getBase64Content(true), 'embed_content', $params);
    }

    /**
     * @param array $params
     * @return string
     */
    public function getIntroduction(array $params = array())
    {
        return '';
    }

}

// Endfile
