<?php

/**
 * Staff signin page
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20707
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// If a request has been submitted, try perfomring a login
if (isset($_POST['email']) && isset($_POST['pass'])) {
    
    // Does email address exist in the database?
    if (!Security::checkEmail($_POST['email'])) {
        Common::logAction('http.post.stafflogin', 'failed', 'EMAIL='.$_POST['email'], '[failed] inexistent');
        header('Location: ./login.php?msg=error_email');
        exit();
    }
    
    // Check password
    $login_check = Security::checkLogin($_POST['email'], $_POST['pass']);
    if (!$login_check) {
        Common::logAction('http.post.login', 'failed', 'password mismatch.');
        header('Location: ./login.php?msg=error_pass');
        exit();
    } elseif ($login_check == 0) {
        // Account is inactive
        header('Location: ./login.php?msg=error_inactive');
        exit();
    }
    
    // Everything's good, set session info and we're good to transfer to dashboard
    Security::generateSession($login_check);
    header('Location: ./dashboard.php');
    
}

// Set page switch variables
$h['title'] = 'Sign In';
$n['signin'] = 'active';

// Error page
if (array_key_exists('msg', $_GET)) {
	switch ($_GET['msg']) {
		case 'activated':
			$error = '<div class="alert alert-green"><img src="/assets/icons/tick.png" title="[OK]" /> Thank you for activating your account. You may now sign in.</div>';
		break;
		case 'error_email':
			$error = '<div class="alert alert-red"><strong>Whoops!</strong> The email address you used doesn\'t exist. Have you made an account yet?</div>';
		break;
		case 'error_pass':
			$error = '<div class="alert alert-red">The password you entered doesn\'t match the one we have on file. Please try again. If you forgot your password, use the Forgot Password link.</div>';
		break;
		case 'error_inactive':
			$error = '<div class="alert alert-red">Your account is not yet active. To activate your account, please refer to the original welcome email we sent to your account when you registered.</div>';
		break;
		default:
			$error = '<div class="alert alert-yellow">This computer system is restricted to authorized users only. All access attempts are logged and unauthorized accesses are strictly forbidden.</div>';
		break;
	}
} else {
	$error = '<div class="alert">This computer system is restricted to authorized users only. All access attempts are logged and unauthorized accesses are strictly forbidden.</div>';
}


// Include header section
echo UX::makeHead($h, $n);

// Page info
echo UX::makeBreadcrumb(array(	'Sign In'		=> '/staff/index.php'));
echo UX::grabPage('staff/login', array('error' => $error), true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>