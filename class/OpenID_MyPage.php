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
 * @version     $Id: OpenID_MyPage.php,v 1.3 2008/07/11 17:42:42 blindman1344 Exp $
 */

class OpenID_MyPage
{
    function show()
    {
        if (PHPWS_Settings::get('openid', 'allow_openid'))
        {
            OpenID_Bookmark();
            PHPWS_Core::initCoreClass('DBPager.php');

            $form = new PHPWS_Form;
            $form->addHidden('module', 'openid');
            $form->addHidden('user', 'login');

            $form->addText('openid_identifier');
            $form->setSize('openid_identifier', 40, 200);
            $form->setClass('openid_identifier', (FORCE_MOD_TEMPLATES ? 'openid openid_mod' : 'openid'));

            $form->addSubmit('submit', dgettext('openid', 'Add'));
            $form->addTplTag('MESSAGE', OpenID_GetMsg());

            $tags = $form->getTemplate();
            $tags['TITLE'] = dgettext('openid', 'OpenID Identifiers');
            $tags['ACTION'] = dgettext('openid', 'Action');

            $pager = new DBPager('openid_mapping');
            $pager->setModule('openid');
            $pager->setTemplate('my_page.tpl');
            $pager->addToggle(PHPWS_LIST_TOGGLE_CLASS);
            $pager->addPageTags($tags);
            $pager->addRowFunction(array('OpenID_MyPage', 'getRowTpl'));
            $pager->setDefaultOrder('openid_identifier', 'desc');
            $pager->addSortHeader('openid_identifier', dgettext('openid', 'OpenID'));
            $pager->setEmptyMessage(dgettext('openid', 'No OpenID identifiers linked to this account.'));
            $pager->addWhere('user_id', Current_User::getId());
            $pager->cacheQueries();

            $content = $pager->get();
        }
        else
        {
            $content = dgettext('openid', 'OpenID login is not enabled at this time.');
        }

        return $content;
    }

    function getRowTpl($row)
    {
        static $num_openid = 0;
        static $no_password = -1;

        $template['OPENID_IDENTIFIER'] = $row['openid_identifier'];
        $template['ACTION'] = '';

        if ($num_openid == 0)
        {
            $db = new PHPWS_DB('openid_mapping');
            $db->addWhere('user_id', Current_User::getId());
            $result = $db->count();
            if (!PHPWS_Error::logIfError($result))
            {
                $num_openid = $result;
            }
        }

        if ($no_password == -1)
        {
            $db_user = new PHPWS_DB('user_authorization');
            $db_user->addWhere('username', Current_User::getUsername());
            $db_user->addWhere('password', PHPWS_Settings::get('openid', 'no_password_md5'));
            $result = $db_user->count();
            if (!PHPWS_Error::logIfError($result))
            {
                $no_password = $result;
            }
        }

        /* Only allow user to remove OpenID if this isn't the last OpenID and no password is set. */
        if (($num_openid > 1) || ($no_password == 0))
        {
            $vars['user'] = 'removeMapping';
            $vars['mapping_id'] = $row['id'];
            $confirm_vars['QUESTION'] = dgettext('openid', 'Are you sure you want to remove this OpenID?');
            $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('openid', $vars, TRUE);
            $confirm_vars['LINK'] = dgettext('openid', 'Remove');
            $template['ACTION'] = javascript('confirm', $confirm_vars);
        }

        return $template;
    }
}

?>