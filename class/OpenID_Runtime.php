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

class OpenID_Runtime
{
    function delegate()
    {
        $key = Key::getCurrent();
        if (!empty($key) && $key->isHomeKey() && PHPWS_Settings::get('openid', 'delegate'))
        {
            $server = PHPWS_Settings::get('openid', 'delegate_server');
            $openid = PHPWS_Settings::get('openid', 'delegate_openid');

            Layout::addLink('<link rel="openid.server" href="' . $server . '" />');
            Layout::addLink('<link rel="openid.delegate" href="' . $openid . '" />');

            if (PHPWS_Settings::get('openid', 'delegate_ver_2'))
            {
                Layout::addLink('<link rel="openid2.provider" href="' . $server . '" />');
                Layout::addLink('<link rel="openid2.local_id" href="' . $openid . '" />');
            }
        }
    }

    function show()
    {
        Layout::addStyle('openid');

        if (!Current_User::isLogged() && PHPWS_Settings::get('openid', 'allow_openid'))
        {
            if ((@$_REQUEST['module'] == 'users') && (@$_REQUEST['action'] == 'user'))
            {
                if (@$_REQUEST['command'] == 'login_page')
                {
                    OpenID_Runtime::showLoginBox();
                }
                else if ((@$_REQUEST['command'] == 'signup_user') &&
                         (PHPWS_Settings::get('users', 'new_user_method') != 0))
                {
                    Layout::add(dgettext('openid', 'Another way to create your account') . ': ');
                    Layout::add(PHPWS_Text::moduleLink(dgettext('openid', 'Sign in with an OpenID'),
                                'openid', array('user'=>'signup')));
                }
            }

            if ((@$_REQUEST['module'] == 'openid') && (@$_REQUEST['user'] == 'signup') &&
                (PHPWS_Settings::get('users', 'new_user_method') != 0))
            {
                Layout::add(PHPWS_Text::moduleLink(dgettext('openid', 'Create account without using OpenID'),
                            'users', array('action'=>'user', 'command'=>'signup_user')));
            }
        }
    }

    function showLoginBox()
    {
        OpenID_Bookmark();

        $form = new PHPWS_Form;
        $form->addHidden('module', 'openid');
        $form->addHidden('user', 'login');

        $form->addText('openid_identifier');
        $form->setSize('openid_identifier', 40, 200);
        $form->setClass('openid_identifier', (FORCE_MOD_TEMPLATES ? 'openid openid_mod' : 'openid'));

        $form->addSubmit('submit', dgettext('openid', 'Sign in'));
        $form->addTplTag('MESSAGE', OpenID_GetMsg());

        $tags['TITLE'] = dgettext('openid', 'Sign in using OpenID');
        $tags['CONTENT'] = PHPWS_Template::process($form->getTemplate(), 'openid', 'login.tpl');
        Layout::add(PHPWS_Template::process($tags, 'openid', 'user.tpl'));
    }
}

?>