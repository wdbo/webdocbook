<?php
/**
 * PHP Library package of Les Ateliers Pierrot
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/library>
 */

namespace DocBook\WebFilesystem;

use \DocBook\WebFilesystem\DocBookFile;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
interface DocBookFileInterface
{

    /**
     * @param array $params
     */
    public function viewFileInfos(array $params = array());

    /**
     * @param array $params
     */
    public function getIntroduction(array $params = array());

}

// Endfile