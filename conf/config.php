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

/**
 * This is where the module will store its OpenID information.
 */
define('OPENID_STORE_PATH', '/tmp/openid_consumer');

/**
 * The filename for a source of random bytes. If your platform does not provide
 * a secure randomness source, this module can operate in pseudorandom mode,
 * but it is then vulnerable to theoretical attacks. If you wish to operate in
 * pseudorandom mode, define Auth_OpenID_RAND_SOURCE to null.
 *
 * On a Unix-like platform, try /dev/random and /dev/urandom.
 */
define('Auth_OpenID_RAND_SOURCE', '/dev/urandom');

?>