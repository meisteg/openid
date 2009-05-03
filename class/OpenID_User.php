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

class OpenID_User
{
    function action($action)
    {
        if (PHPWS_Settings::get('openid', 'allow_openid'))
        {
            $tags = array();

            switch ($action)
            {
                case 'signup':
                    if (!Current_User::isLogged() && (PHPWS_Settings::get('users', 'new_user_method') != 0))
                    {
                        $tags['TITLE'] = dgettext('openid', 'New Account Sign-up');
                        $tags['CONTENT'] = OpenID_User::showSignUp();
                    }
                    break;

                case 'login':
                    OpenID_User::login();
                    break;

                case 'completeLogin':
                    $tags['TITLE'] = dgettext('openid', 'New Account Sign-up');
                    $tags['CONTENT'] = OpenID_User::completeLogin();
                    break;

                case 'createUser':
                    $tags['TITLE'] = dgettext('openid', 'New Account Sign-up');
                    $tags['CONTENT'] = OpenID_User::createUser();
                    break;

                case 'removeMapping':
                    OpenID_User::removeMapping();
                    break;
            }

            Layout::add(PHPWS_Template::process($tags, 'openid', 'user.tpl'));
        }
    }

    function showSignUp()
    {
        OpenID_Bookmark();

        $form = new PHPWS_Form;
        $form->addHidden('module', 'openid');
        $form->addHidden('user', 'login');

        $form->addText('openid_identifier');
        $form->setLabel('openid_identifier', dgettext('openid', 'Sign in using OpenID'));
        $form->setSize('openid_identifier', 40, 200);
        $form->setClass('openid_identifier', (FORCE_MOD_TEMPLATES ? 'openid openid_mod' : 'openid'));

        $form->addSubmit('submit', dgettext('openid', 'Sign in'));

        $tags = $form->getTemplate();
        $tags['WHAT_IS_OPENID_LABEL'] = dgettext('openid', 'What is OpenID?');
        $tags['WHAT_IS_OPENID'] = dgettext('openid', 'OpenID is a decentralised authentication system that lets you
            sign in to web sites with a single URL. This eliminates the need for multiple usernames across different
            websites. With OpenID, you have a single password with a Provider that you trust. Best of all, it is
            completely free!');
        $tags['LEARN_MORE'] = dgettext('openid', 'Learn more');
        $tags['MESSAGE'] = OpenID_GetMsg();

        return PHPWS_Template::process($tags, 'openid', 'signup.tpl');
    }

    function includePhpOpenID()
    {
        $path_extra = dirname(__FILE__);
        $path = ini_get('include_path');
        $path = $path_extra . PATH_SEPARATOR . $path;
        ini_set('include_path', $path);

        /* Require the OpenID consumer code. */
        require_once "Auth/OpenID/Consumer.php";
        /* Require the "file store" module, which we'll need to store OpenID information. */
        require_once "Auth/OpenID/FileStore.php";
        /* Require the Simple Registration extension API. */
        require_once "Auth/OpenID/SReg.php";
    }

    function &getPhpOpenIDConsumer()
    {
        if (!file_exists(OPENID_STORE_PATH))
        {
            @mkdir(OPENID_STORE_PATH);
        }

        // Create the store and consumer objects.
        $store = new Auth_OpenID_FileStore(OPENID_STORE_PATH);
        return new Auth_OpenID_Consumer($store);
    }

    function getReturnURL($root_only=false)
    {
        $retval = sprintf("%s%s:%s%s/", PHPWS_Core::getHttp(), $_SERVER['SERVER_NAME'],
                          $_SERVER['SERVER_PORT'], dirname($_SERVER['PHP_SELF']));

        if (!$root_only)
        {
            $retval .= PHPWS_Text::linkAddress('openid', array('user'=>'completeLogin'), false, false, false);
        }

        return $retval;
    }

    function login()
    {
        OpenID_User::includePhpOpenID();

        $openid = trim($_REQUEST['openid_identifier']);
        if (empty($openid))
        {
            OpenID_SendMsg(dgettext('openid', 'Please specify an OpenID identifier.'));
        }

        // Begin the OpenID authentication process.
        $consumer = OpenID_User::getPhpOpenIDConsumer();
        $auth_request = $consumer->begin($openid);

        // No auth request means we can't begin OpenID.
        if (!$auth_request)
        {
            OpenID_SendMsg(dgettext('openid', 'Specified identifier does not appear to be a valid OpenID.'));
        }

        $request_required = array('nickname', 'email');
        $request_optional = array('fullname');
        $sreg_request = Auth_OpenID_SRegRequest::build($request_required, $request_optional);

        if ($sreg_request)
        {
            $auth_request->addExtension($sreg_request);
        }

        $trust_root = OpenID_User::getReturnURL(true);
        $return_to = OpenID_User::getReturnURL();

        // Redirect the user to the OpenID server for authentication. Store
        // the token for this authentication so we can verify the response.
        $redirect_url = $auth_request->redirectURL($trust_root, $return_to);

        // If the redirect URL can't be built, display an error message.
        if (Auth_OpenID::isFailure($redirect_url))
        {
            OpenID_SendMsg(dgettext('openid', 'Could not redirect to server. Try again later.'));
        }

        // Send redirect
        header("Location: " . $redirect_url);
        exit();
    }

    function completeLogin()
    {
        OpenID_User::includePhpOpenID();

        // Complete the authentication process using the server's response.
        $consumer = OpenID_User::getPhpOpenIDConsumer();
        $response = $consumer->complete(OpenID_User::getReturnURL());

        // Check the response status.
        if ($response->status == Auth_OpenID_CANCEL)
        {
            $_SESSION['openid_message'] = dgettext('openid', 'Verification cancelled.');
            OpenID_ReturnToBookmark();
        }
        else if ($response->status == Auth_OpenID_SUCCESS)
        {
            // This means the authentication succeeded; extract the identity
            // URL and Simple Registration data (if it was returned).
            $openid = $response->getDisplayIdentifier();

            $db = new PHPWS_DB('openid_mapping');
            $db->addWhere('openid_identifier', $openid);
            $db->addColumn('user_id');
            $result = $db->select('col');
            if (!PHPWS_Error::logIfError($result))
            {
                if (!empty($result))
                {
                    if (!Current_User::isLogged())
                    {
                        // Log in user
                        $user = new PHPWS_User($result[0]);
                        if ($user->approved && $user->active)
                        {
                            $user->login();
                            $_SESSION['User'] = $user;
                            PHPWS_Core::returnToBookmark();
                        }

                        $_SESSION['openid_message'] = dgettext('openid', 'User account for this site is not active.');
                    }
                    OpenID_ReturnToBookmark();
                }
                else if (Current_User::isLogged())
                {
                    // Link this OpenID to user
                    $db->reset();
                    $values['user_id'] = Current_User::getId();
                    $values['openid_identifier'] = $openid;
                    $db->addValue($values);
                    if (!PHPWS_Error::logIfError($db->insert()))
                    {
                        $_SESSION['openid_message'] = dgettext('openid', 'OpenID added to your account.');
                    }

                    OpenID_ReturnToBookmark();
                }
                else if (PHPWS_Settings::get('users', 'new_user_method') != 0)
                {
                    // Create new site user (or link) for this OpenID
                    $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
                    $sreg = $sreg_resp->contents();

                    $session_key = rand();
                    $_SESSION['openid_identifier'][$session_key] = $openid;

                    return OpenID_User::createUserForm($session_key, $sreg['email'],
                                                       $sreg['nickname'], $sreg['fullname']);
                }

                $_SESSION['openid_message'] = dgettext('openid', 'OpenID not registered in database.');
                OpenID_ReturnToBookmark();
            }
        }

        $_SESSION['openid_message'] = dgettext('openid', 'Authentication failed.');
        OpenID_ReturnToBookmark();
    }

    function createUserForm($session_key, $email, $nickname, $fullname, $error=NULL)
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'openid');
        $form->addHidden('user', 'createUser');
        $form->addHidden('session_key', $session_key);

        $form->addText('username', $nickname);
        $form->setLabel('username', dgettext('openid', 'Username'));
        $form->setSize('username', 30, 30);

        $form->addText('displayname', $fullname);
        $form->setLabel('displayname', dgettext('openid', 'Display Name'));
        $form->setSize('displayname', 30, 30);

        $form->addText('email', $email);
        $form->setLabel('email', dgettext('openid', 'Email Address'));
        $form->setSize('email', 50, 100);

        $form->addSubmit('submit', dgettext('openid', 'Finish'));

        $tags = $form->getTemplate();
        $tags['INSTRUCTIONS'] = dgettext('openid',
                                         'Verify your account information then click finish to complete registration.');
        $tags['OPENID_LABEL'] = dgettext('openid', 'OpenID');
        $tags['OPENID'] = $_SESSION['openid_identifier'][$session_key];

        if (is_array($error))
        {
            $tags = array_merge($tags, $error);
        }

        return PHPWS_Template::process($tags, 'openid', 'createuser.tpl');
    }

    function createUser()
    {
        PHPWS_Core::initModClass('users', 'Action.php');
        $user = new PHPWS_User;

        if (PEAR::isError($user->setUsername($_POST['username'])) || !User_Action::testForbidden($user))
        {
            $error['USERNAME_ERROR'] = dgettext('openid', 'Please try another user name.');
        }

        if (PEAR::isError($user->setDisplayName($_POST['displayname'])))
        {
            $error['DISPLAYNAME_ERROR'] = dgettext('openid', 'Please try another display name.');
        }

        if (empty($_POST['email']))
        {
            $error['EMAIL_ERROR'] = dgettext('openid', 'Missing an email address.');
        }
        else if (PEAR::isError($user->setEmail($_POST['email'])))
        {
            $error['EMAIL_ERROR'] = dgettext('openid', 'This email address cannot be used.');
        }

        if (is_array($error))
        {
            $content = OpenID_User::createUserForm($_POST['session_key'], $_POST['email'],
                                                   $_POST['username'], $_POST['displayname'], $error);
        }
        else
        {
            $user->setPassword(PHPWS_Settings::get('openid', 'no_password_md5'), false);
            $content = User_Action::successfulSignup($user);

            /* Need to look up ID of new user since passing $user by reference apparently doesn't work on PHP 4. */
            $db = new PHPWS_DB('users');
            $db->addWhere('username', $_POST['username']);
            $db->addColumn('id');
            $user_id = $db->select('one');
            if (!PHPWS_Error::logIfError($user_id) && !empty($user_id))
            {
                /* Need to set password again to no_password_md5 password.  The call to
                 * successfulSignup above will call md5() on the password before saving. */
                $created_user = new PHPWS_User($user_id);
                $created_user->setPassword(PHPWS_Settings::get('openid', 'no_password_md5'), false);
                $created_user->saveLocalAuthorization();

                $db = new PHPWS_DB('openid_mapping');
                $values['user_id'] = $user_id;
                $values['openid_identifier'] = $_SESSION['openid_identifier'][$_POST['session_key']];
                $db->addValue($values);
                PHPWS_Error::logIfError($db->insert());
            }

            unset($_SESSION['openid_identifier'][$_POST['session_key']]);
        }

        return $content;
    }

    function removeMapping()
    {
        $db = new PHPWS_DB('openid_mapping');
        $db->addWhere('user_id', Current_User::getId());
        $db->addWhere('id', $_REQUEST['mapping_id']);
        PHPWS_Error::logIfError($db->delete());

        OpenID_ReturnToBookmark();
    }

}// END CLASS OpenID_User

?>