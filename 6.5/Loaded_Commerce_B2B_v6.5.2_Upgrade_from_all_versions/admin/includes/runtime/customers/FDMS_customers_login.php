<?php
/*
  $Id: FDMS_customers_login.php, v 1.1.1.1 2007/01/25 datazen Exp $

  CRE Loaded, Open Source E-Commerce Solutions
  http://www.creloaded.com

  Copyright (c) 2006 CRE Loaded
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
global $cInfo;
if (defined('MODULE_ADDONS_FDM_STATUS') && MODULE_ADDONS_FDM_STATUS == 'True') { 
  $rci .= '<a href="' . tep_href_link(FILENAME_CUSTOMER_DOWNLOADS, 'cID=' . $cInfo->customers_id) . '">' . tep_image_button('button_login_as_customer.gif', IMAGE_LOGIN_AS_CUSTOMER) . '</a>';
}
?>