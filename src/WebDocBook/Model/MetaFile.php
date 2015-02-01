<?php
/**
 * Created by PhpStorm.
 * User: pierrecassat
 * Date: 25/01/15
 * Time: 00:29
 */

namespace WebDocBook\Model;

use \SplFileObject;

/**
 * Class MetaFile
 */
class MetaFile
    extends SplFileObject
{

    /**
     * Get the file contents stripping all lines beginning by a sharp `#`
     * @return string
     */
    public function getWDBContent()
    {
        $contents = array();
        foreach ($this as $line) {
            if (!empty($line) && $line!=="\n" && $line{0}!='#') {
                $contents[] = $line;
            }
        }
        return implode('', $contents);
    }

}