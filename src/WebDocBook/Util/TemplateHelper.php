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

namespace WebDocBook\Util;

use \WebDocBook\Kernel;
use \Library\Helper\Text as TextHelper;
use \Library\Helper\Url as UrlHelper;
use \DateTime;

/**
 * Class TemplateHelper
 *
 * This is the global templating helper class
 */
class TemplateHelper
{

    /**
     * @param $string
     * @return string
     */
    public static function getSafeIdString($string)
    {
        return TextHelper::stripSpecialChars(
            TextHelper::slugify($string), '-_'
        );
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function getSlug($string)
    {
        return str_replace(array(' '), '_', strtolower($string));
    }

    /**
     * @param null $path
     * @return array
     */
    public static function getBreadcrumbs($path = null)
    {
        $breadcrumbs = array();
        if (!empty($path)) {
            $parts          = explode('/', str_replace(Kernel::getPath('web'), '', $path));
            $breadcrumbs    = array_filter($parts);
        }
        return $breadcrumbs;
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function getSecuredRealpath($path)
    {
        return str_replace(Kernel::getPath('app_base_path'), '/[***]', $path);
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function getRelativePath($path)
    {
        return str_replace(Kernel::getPath('web'), '', $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function getAbsolutePath($path)
    {
        return Kernel::getPath('web')
            .str_replace(Kernel::getPath('web'), '', $path);
    }

    /**
     * @param $path
     * @return string
     */
    public static function getAbsoluteRoute($path)
    {
        $url            = UrlHelper::parse(UrlHelper::getRequestUrl());
        $url['path']    = self::getRelativePath(self::getRoute($path));
        $url['params']  = array();
        return UrlHelper::build($url);
    }

    /**
     * @param $path
     * @param null $type
     * @param bool $with_interface
     * @return string
     */
    public static function getRoute($path, $type = null, $with_interface = false)
    {
        $route          = $path;
        $rel_path       = str_replace(Kernel::getPath('web'), '', $path);
        $add_last_slash = (!empty($rel_path) && file_exists($path) && is_dir($path));
        return (true===$with_interface ?
                Kernel::getPath('app_interface').'?'
                :
                (!empty($rel_path) ? '/' : '')
            )
            .trim($rel_path, '/')
            .($add_last_slash ? '/' : '')
            .(!empty($type) ? ($add_last_slash ? '' : '/').$type : '');
    }

    /**
     * @param $filename
     * @return string
     */
    public static function buildPageTitle($filename)
    {
        $name = basename($filename);
        return ucfirst(
            str_replace(array('_', '.'), ' ',
                str_replace('.md', '', $name)
            )
        );
    }

    /**
     * @return array
     */
    public static function getProfiler()
    {
        return array(
            'date'              => new DateTime(),
            'timezone'          => date_default_timezone_get(),
            'php_uname'         => php_uname(),
            'php_version'       => phpversion(),
            'php_sapi_name'     => php_sapi_name(),
            'apache_version'    => function_exists('apache_get_version') ? apache_get_version() : '?',
            'user_agent'        => $_SERVER['HTTP_USER_AGENT'],
            'git_clone'         => FilesystemHelper::isGitClone(Kernel::getPath('app_base_path')),
            'request'           => UrlHelper::getRequestUrl(),
        );
    }

    /**
     * @param $str
     * @param int $cut
     * @return mixed|string
     */
    public static function rssEncode($str, $cut = 800)
    {
        $str = preg_replace(',<h1(.*)</h1>,i', '', $str);
        $str = TextHelper::cut($str, $cut);
        return $str;
    }

    /**
     * @var array The icons names cache
     */
    private static $_cfg_icons = null;

    /**
     * @param null $type
     * @param string $class
     * @return string
     */
    public static function getIcon($type = null, $class = '')
    {
        if (!empty($type)) {
            if (is_null(self::$_cfg_icons)) {
                self::$_cfg_icons = Kernel::getConfig('icons', array());
            }
            return '<span class="fa fa-'
                .(isset(self::$_cfg_icons[$type]) ? self::$_cfg_icons[$type] : self::$_cfg_icons['default'])
                .(!empty($class) ? ' '.$class : '')
                .'"></span>';
        }
        return '';
    }

}

// Endfile
