<?php
/**
 * OpenID module for phpWebSite
 *
 * See docs/CREDITS for copyright information
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author      Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
 * @version     $Id: OpenID_Detect.php,v 1.2 2008/07/11 17:42:42 blindman1344 Exp $
 */

/**
 * Based on the detect script included in the PHP OpenID library by JanRain, Inc.
 * For more information about the PHP OpenID library, visit:
 *
 * http://www.openidenabled.com
 */
class OpenID_Detect
{
    function run()
    {
        $path_extra = dirname(__FILE__);
        $path = ini_get('include_path');
        $path = $path_extra . PATH_SEPARATOR . $path;
        ini_set('include_path', $path);

        require_once 'Auth/OpenID.php';
        require_once 'Auth/Yadis/Yadis.php';

        $errors = array();

        OpenID_Detect::detect_random($errors);
        OpenID_Detect::detect_fetcher($errors);
        OpenID_Detect::detect_xml($errors);

        return $errors;
    }

    function detect_random(&$errors)
    {
        if (Auth_OpenID_RAND_SOURCE !== null)
        {
            $numbytes = 6;

            $f = @fopen(Auth_OpenID_RAND_SOURCE, 'r');
            if ($f !== false)
            {
                $data = fread($f, $numbytes);
                $stat = fstat($f);
                $size = $stat['size'];
                fclose($f);

                if (Auth_OpenID::bytes($data) != $numbytes)
                {
                    $errors[] = dgettext('openid', 'Reading from randomness source failed.');
                }
                if ($size)
                {
                    $errors[] = dgettext('openid', 'Randomness source appears to be a regular file.');
                }
            }
            else
            {
                $errors[] = sprintf(dgettext('openid', '%s could not be opened.'), Auth_OpenID_RAND_SOURCE);
            }
        }
    }

    function detect_fetcher(&$errors)
    {
        $fetcher = Auth_Yadis_Yadis::getHTTPFetcher();
        $fetch_url = 'http://www.openidenabled.com/resources/php-fetch-test';
        $expected_url = $fetch_url . '.txt';
        $result = $fetcher->get($fetch_url);

        if (isset($result))
        {
            if (($result->status != '200') && ($result->status != '206'))
            {
                $errors[] = dgettext('openid', 'HTTP Fetching: Unexpected HTTP status code.');
            }

            if ($result->final_url != $expected_url)
            {
                if ($result->final_url == $fetch_url)
                {
                    $errors[] = dgettext('openid', 'HTTP Fetching: The redirected URL was not returned.');
                }
                else
                {
                    $errors[] = dgettext('openid', 'HTTP Fetching: An unexpected URL was returned.');
                }
            }

            $data = $result->body;
            if ($data != 'Hello World!')
            {
                $errors[] = dgettext('openid', 'HTTP Fetching: Unexpected data was returned.');
            }
        }
        else
        {
            $errors[] = dgettext('openid', 'HTTP Fetching: Fetching failed!');
        }

        if (!$fetcher->supportsSSL())
        {
            $errors[] = dgettext('openid', 'Your PHP installation does not support SSL, so it will NOT
                                  be able to process HTTPS URLs.');
        }
    }

    function detect_xml(&$errors)
    {
        // Try to get an XML extension.
        $ext = Auth_Yadis_getXMLParser();

        if ($ext === null)
        {
            $errors[] = dgettext('openid', 'XML parsing support is absent.');
        }
    }

}// END CLASS OpenID_Detect

?>
