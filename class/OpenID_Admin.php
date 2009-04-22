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
 * @version     $Id: OpenID_Admin.php,v 1.6 2008/02/10 21:28:36 blindman1344 Exp $
 */

class OpenID_Admin
{
    function action($action)
    {
        if (!Current_User::allow('openid'))
        {
            Current_User::disallow();
            return;
        }

        switch ($action)
        {
            case 'settings':
                $template['CONTENT'] = OpenID_Admin::editSettings();
                break;

            case 'postSettings':
                OpenID_Admin::postSettings();
                break;
        }

        $template['MESSAGE'] = OpenID_GetMsg();

        $display = PHPWS_Template::process($template, 'openid', 'admin.tpl');
        Layout::add(PHPWS_ControlPanel::display($display));
    }

    function editSettings()
    {
        PHPWS_Core::initModClass('openid', 'OpenID_Detect.php');

        $form = new PHPWS_Form;
        $form->addHidden('module', 'openid');
        $form->addHidden('action', 'postSettings');

        $form->addCheck('delegate');
        $form->setMatch('delegate', PHPWS_Settings::get('openid', 'delegate'));
        $form->setLabel('delegate', dgettext('openid', 'Delegate Site'));

        $form->addText('delegate_server', PHPWS_Settings::get('openid', 'delegate_server'));
        $form->setLabel('delegate_server', dgettext('openid', 'OpenID Provider Server URL'));
        $form->setSize('delegate_server', 55, 200);

        $form->addText('delegate_openid', PHPWS_Settings::get('openid', 'delegate_openid'));
        $form->setLabel('delegate_openid', dgettext('openid', 'OpenID Identifier'));
        $form->setSize('delegate_openid', 45, 200);
        $form->setClass('delegate_openid', (FORCE_MOD_TEMPLATES ? 'openid openid_mod' : 'openid'));

        $form->addCheck('delegate_ver_2');
        $form->setMatch('delegate_ver_2', PHPWS_Settings::get('openid', 'delegate_ver_2'));
        $form->setLabel('delegate_ver_2', dgettext('openid', 'Provider supports OpenID 2.0'));

        $errors = OpenID_Detect::run();
        if (empty($errors))
        {
            $form->addCheck('allow_openid');
            $form->setMatch('allow_openid', PHPWS_Settings::get('openid', 'allow_openid'));
            $form->setLabel('allow_openid', dgettext('openid', 'Allow users to log in using OpenID'));

            if (PHPWS_Settings::get('users', 'new_user_method') == 0)
            {
                $form->addTplTag('ALLOW_OPENID_NOTE', dgettext('openid', 'User signup mode is set to not allowed,
                                 so users will only be able to map their OpenID to an existing site user.'));
            }
        }
        else
        {
            $form->addTplTag('ALLOW_OPENID_NOTE', implode('<br />', $errors));
        }

        $form->addTplTag('ALLOW_OPENID_LEGEND', dgettext('openid', 'OpenID Log In'));
        $form->addSubmit('submit', dgettext('openid', 'Update Settings'));

        return PHPWS_Template::process($form->getTemplate(), 'openid', 'settings.tpl');
    }

    function postSettings()
    {
        $success_msg      = dgettext('openid', 'Your settings have been successfully saved.');
        $error_saving_msg = dgettext('openid', 'Error saving the settings. Check error log for details.');
        $error_inputs_msg = dgettext('openid', 'Missing or invalid input. Please correct and try again.');
        $ret_msg          = &$success_msg;

        $delegate_server  = trim($_POST['delegate_server']);
        $delegate_openid  = trim($_POST['delegate_openid']);

        if (strlen(PHPWS_Settings::get('openid', 'no_password_md5')) != 32)
        {
            /* Generate random MD5 to use as password for accounts created by OpenID.
             * Only do this once so all accounts with no real password have same MD5. */
            PHPWS_Settings::set('openid', 'no_password_md5', md5(rand()));
        }

        PHPWS_Settings::set('openid', 'delegate',        0               );
        PHPWS_Settings::set('openid', 'delegate_server', $delegate_server);
        PHPWS_Settings::set('openid', 'delegate_openid', $delegate_openid);
        PHPWS_Settings::set('openid', 'delegate_ver_2',  (int)isset($_POST['delegate_ver_2']));
        PHPWS_Settings::set('openid', 'allow_openid',    (int)isset($_POST['allow_openid']));

        if (isset($_POST['delegate']))
        {
            if (!empty($delegate_server) && !empty($delegate_openid) &&
                PHPWS_Text::isValidInput($delegate_server, 'url'))
            {
                PHPWS_Settings::set('openid', 'delegate', 1);
            }
            else
            {
                $ret_msg = &$error_inputs_msg;
            }
        }

        if (PHPWS_Error::logIfError(PHPWS_Settings::save('openid')))
        {
            $ret_msg = &$error_saving_msg;
        }

        OpenID_SendMsg($ret_msg, 'settings');
    }

}// END CLASS OpenID_Admin

?>