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

function openid_update(&$content, $currentVersion)
{
    switch ($currentVersion)
    {
        case version_compare($currentVersion, '1.0.1', '<'):
            $content[] = '- Changed module priority so it starts sooner.';
            $content[] = '- Now using PHP OpenID 2.0.1 library.';

        case version_compare($currentVersion, '1.0.2', '<'):
            $files = array('templates/my_page.tpl');
            openid_update_files($files, $content);

            $content[] = '- Now using PHP OpenID 2.1.1 library.';
            $content[] = '- Added calls to cacheQueries and addSortHeader when using DBPager.';
            $content[] = '- Added remove_user.php to support new User mod feature.';

        case version_compare($currentVersion, '1.0.3', '<'):
            $content[] = '- Now using PHP OpenID 2.1.3 library.';
    }

    return true;
}

function openid_update_files($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'openid'))
    {
        $content[] = '- Updated the following files:';
    }
    else
    {
        $content[] = '- Unable to update the following files:';
    }

    foreach ($files as $file)
    {
        $content[] = '--- ' . $file;
    }
}

?>