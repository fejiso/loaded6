<?php
/*
  $Id: affiliate_password_forgotten.php,v 1.2 2004/03/05 00:36:42 ccwjr Exp $

  OSC-Affiliate

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 - 2003 osCommerce

  Released under the GNU General Public License
*/

define('NAVBAR_TITLE_1', 'Login');
define('NAVBAR_TITLE_2', 'Affiliate Password Forgotten');
define('HEADING_TITLE', 'I\'ve Forgotten My Affiliate Password!');
define('TEXT_MAIN', 'If you\'ve forgotten your Affiliate Password, enter your e-mail address below and we\'ll send you an e-mail message containing your new password.');
define('TEXT_NO_EMAIL_ADDRESS_FOUND', '<font color="#ff0000"><b>NOTE:</b></font> The E-Mail Address was not found in our records. Please try again.');
define('EMAIL_PASSWORD_REMINDER_SUBJECT', STORE_NAME . ' - New Affiliate Password');
define('EMAIL_PASSWORD_REMINDER_BODY', 'A new affiliate password was requested from ' . $REMOTE_ADDR . '.' . "\n\n" . 'Your new affiliate password to \'' . STORE_NAME . '\' is:' . "\n\n" . '   %s' . "\n\n");
define('TEXT_PASSWORD_SENT', 'A New Affiliate Password Has Been Sent To Your Email Address');
?>