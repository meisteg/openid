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
 * @version     $Id: index.php,v 1.1.1.1 2008/02/04 04:54:30 blindman1344 Exp $
 */

if (!defined('PHPWS_SOURCE_DIR'))
{
    include '../../config/core/404.html';
    exit();
}

if (isset($_REQUEST['action']))
{
    PHPWS_Core::initModClass('openid', 'OpenID_Admin.php');
    OpenID_Admin::action($_REQUEST['action']);
}

if (isset($_REQUEST['user']))
{
    PHPWS_Core::initModClass('openid', 'OpenID_User.php');
    OpenID_User::action($_REQUEST['user']);
}

?>