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
    \WebFilesystem\Finder;

use \Library\Helper\Directory as DirectoryHelper;

use \FilesystemIterator;

/**
 */
class DBDirectory
    extends WebFileInfo
    implements DocBookFileInterface
{

    /**
     * @param array $params
     * @return string
     */
    public function viewFileInfos(array $params = array())
    {
        return FrontController::getInstance()
            ->display('', 'default_content', $params);
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
