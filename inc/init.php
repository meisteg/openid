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
 * @package OpenID
 * @author Greg Meiste <greg.meiste+github@gmail.com>
 */

PHPWS_Core::configRequireOnce('openid', 'config.php');
PHPWS_Core::initModClass('openid', 'OpenID_Runtime.php');

function OpenID_SendMsg($message, $command=NULL)
{
    $_SESSION['openid_message'] = $message;

    if (!empty($command))
    {
        PHPWS_Core::reroute(PHPWS_Text::linkAddress('openid', array('action'=>$command), true));
    }

    PHPWS_Core::goBack();
}

function OpenID_GetMsg()
{
    if (isset($_SESSION['openid_message']))
    {
        $message = $_SESSION['openid_message'];
        unset($_SESSION['openid_message']);
        return $message;
    }

    return NULL;
}

function OpenID_Bookmark()
{
    $_SESSION['openid_bookmark'] = PHPWS_Core::getCurrentUrl();
}

function OpenID_ReturnToBookmark()
{
    if (isset($_SESSION['openid_bookmark']))
    {
        $bm = $_SESSION['openid_bookmark'];
        unset($_SESSION['openid_bookmark']);

        PHPWS_Core::reroute($bm);
    }
    else
    {
        PHPWS_Core::goBack();
    }
}

?>