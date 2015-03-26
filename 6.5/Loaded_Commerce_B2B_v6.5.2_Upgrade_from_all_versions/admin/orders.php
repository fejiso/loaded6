<?php
/*
  $Id: orders.php,v 1.2 2004/03/05 00:36:41 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
  
  // RCI code start
  echo $cre_RCI->get('global', 'top', false);
  echo $cre_RCI->get('orders', 'top', false); 
  // RCI code eof
  
  // multi-vendor shipping
  function vendors_email($vendors_id, $oID, $status, $vendor_order_sent) {
  if (defined('MVS_STATUS') && MVS_STATUS == 'true') {
    $vendor_order_sent =  false;
    $debug='no';
    $vendor_order_sent = 'no';
    $index2 = 0;
    // get the Vendors
    $vendor_data_query = tep_db_query("SELECT v.vendors_id, v.vendors_name, v.vendors_email, v.vendors_contact, v.vendor_add_info, v.vendor_street, v.vendor_city, v.vendor_state, v.vendors_zipcode, v.vendor_country, v.account_number, v.vendors_status_send, os.shipping_module, os.shipping_method, os.shipping_cost, os.shipping_tax, os.vendor_order_sent 
                                         from " . TABLE_VENDORS . " v,  
                                              " . TABLE_ORDERS_SHIPPING . " os 
                                       WHERE v.vendors_id=os.vendors_id 
                                         and v.vendors_id='" . $vendors_id . "' 
                                         and os.orders_id='" . (int)$oID . "' 
                                         and v.vendors_status_send='" . $status . "'");
    while ($vendor_order = tep_db_fetch_array($vendor_data_query)) {
      $vendor_products[$index2] = array('Vid' => $vendor_order['vendors_id'],
                                        'Vname' => $vendor_order['vendors_name'],
                                        'Vemail' => $vendor_order['vendors_email'],
                                        'Vcontact' => $vendor_order['vendors_contact'],
                                        'Vaccount' => $vendor_order['account_number'],
                                        'Vstreet' => $vendor_order['vendor_street'],
                                        'Vcity' => $vendor_order['vendor_city'],
                                        'Vstate' => $vendor_order['vendor_state'],
                                        'Vzipcode' => $vendor_order['vendors_zipcode'],
                                        'Vcountry' => $vendor_order['vendor_country'],
                                        'Vaccount' => $vendor_order['account_number'],                               
                                        'Vinstructions' => $vendor_order['vendor_add_info'],
                                        'Vmodule' => $vendor_order['shipping_module'],                               
                                        'Vmethod' => $vendor_order['shipping_method']);
      if ($debug == 'yes') {
        echo 'The vendor query: ' . $vendor_order['vendors_id'] . '<br>';
      }
      $index = 0;
      $vendor_orders_products_query = tep_db_query("SELECT o.orders_id, o.orders_products_id, o.products_model, o.products_id, o.products_quantity, o.products_name, p.vendors_id,  p.vendors_prod_comments, p.vendors_prod_id, p.vendors_product_price 
                                                      from " . TABLE_ORDERS_PRODUCTS . " o, 
                                                           " . TABLE_PRODUCTS . " p 
                                                    WHERE p.vendors_id='" . (int)$vendor_order['vendors_id'] . "' 
                                                      and o.products_id=p.products_id 
                                                      and o.orders_id='" . $oID . "' order by o.products_name");
      while ($vendor_orders_products = tep_db_fetch_array($vendor_orders_products_query)) {
        $vendor_products[$index2]['vendor_orders_products'][$index] = array(
                                  'Pqty' => $vendor_orders_products['products_quantity'],
                                  'Pname' => $vendor_orders_products['products_name'],
                                  'Pmodel' => $vendor_orders_products['products_model'],
                                  'Pprice' => $vendor_orders_products['products_price'],
                                  'Pvendor_name' => $vendor_orders_products['vendors_name'],
                                  'Pcomments' => $vendor_orders_products['vendors_prod_comments'],
                                  'PVprod_id' => $vendor_orders_products['vendors_prod_id'],
                                  'PVprod_price' => $vendor_orders_products['vendors_product_price'],
                                  'spacer' => '-');
        if ($debug == 'yes') {
          echo 'The products query: ' . $vendor_orders_products['products_name'] . '<br>';
        }
        $subindex = 0;
        $vendor_attributes_query = tep_db_query("SELECT products_options, products_options_values, options_values_price, price_prefix 
                                                   from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " 
                                                 WHERE orders_id = '" . (int)$oID . "' 
                                                   and orders_products_id = '" . (int)$vendor_orders_products['orders_products_id'] . "'");
        if (tep_db_num_rows($vendor_attributes_query)) {
          while ($vendor_attributes = tep_db_fetch_array($vendor_attributes_query)) {
            $vendor_products[$index2]['vendor_orders_products'][$index]['vendor_attributes'][$subindex] = array('option' => $vendor_attributes['products_options'],
                                      'value' => $vendor_attributes['products_options_values'],
                                      'prefix' => $vendor_attributes['price_prefix'],
                                      'price' => $vendor_attributes['options_values_price']);
            $subindex++;
          }
        }
        $index++;
      }
      $index2++;
      // build the email
      // Get the delivery address
      $delivery_address_query = tep_db_query("SELECT DISTINCT delivery_company, delivery_name, delivery_street_address, delivery_city, delivery_state, delivery_postcode 
                                                from " . TABLE_ORDERS . " 
                                              WHERE orders_id='" . $oID ."'") ;
      $vendor_delivery_address_list = tep_db_fetch_array($delivery_address_query);
      if ($debug == 'yes') {
        echo 'The number of vendors: ' . sizeof($vendor_products) . '<br>';
      }
      $email='';
      for ($l=0, $m=sizeof($vendor_products); $l<$m; $l++) {
        $vendor_country = tep_get_country_name($vendor_products[$l]['Vcountry']);
        $order_number= $oID;
        $vendors_id=$vendor_products[$l]['Vid'];
        $the_email=$vendor_products[$l]['Vemail'];
        $the_name=$vendor_products[$l]['Vname'];
        $the_contact=$vendor_products[$l]['Vcontact'];
        $email=  '<b>To: ' . $the_contact . '  <br>' . $the_name . '<br>' . $the_email . '<br>' .
        $vendor_products[$l]['Vstreet'] .'<br>' .
        $vendor_products[$l]['Vcity'] .', ' .
        $vendor_products[$l]['Vstate'] .'  ' .
        $vendor_products[$l]['Vzipcode'] . ' ' . $vendor_country . '<br>' . '<br>' . EMAIL_SEPARATOR . '<br>' . 'Special Comments or Instructions:  ' . $vendor_products[$l]['Vinstructions'] .'<br>' . '<br>' . EMAIL_SEPARATOR . '<br>' . 'From: ' . STORE_OWNER . '<br>' . STORE_NAME_ADDRESS . '<br>' . 'Accnt #: ' . $vendor_products[$l]['Vaccount'] . '<br>' . EMAIL_SEPARATOR . '<br>' . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . '<br>' .  EMAIL_SEPARATOR . '<br>' . '<br> Shipping Method: ' .  $vendor_products[$l]['Vmodule'] . ' -- '  .  $vendor_products[$l]['Vmethod'] .  '<br>' .  EMAIL_SEPARATOR . '<br>' . '<br>Dropship deliver to:<br>' .
        $vendor_delivery_address_list['delivery_company'] .'<br>' .
        $vendor_delivery_address_list['delivery_name'] .'<br>' .
        $vendor_delivery_address_list['delivery_street_address'] .'<br>' .
        $vendor_delivery_address_list['delivery_city'] .', ' .
        $vendor_delivery_address_list['delivery_state'] . ' ' . $vendor_delivery_address_list['delivery_postcode'] . '<br><br>' ;
        $email = $email .  '<table width="75%" border=1 cellspacing="0" cellpadding="3"><tr><td>Qty:</td><td>Product Name:</td><td>Item Code/Number:</td><td>Product Model:</td><td>Per Unit Price:</td><td>Item Comments: </td></tr>';
        for ($i=0, $n=sizeof($vendor_products[$l]['vendor_orders_products']); $i<$n; $i++) {
          $product_attribs ='';
          if (isset($vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes']) && (sizeof($vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes']) > 0)) {
            for ($j = 0, $k = sizeof($vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes']); $j < $k; $j++) {
              $product_attribs .= '&nbsp;&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes'][$j]['option'] . ': ' .  $vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes'][$j]['value'] . '<br>';
            }
          }
          $email = $email . '<tr><td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['Pqty'] .
                            '</td><td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['Pname'] . '<br>&nbsp;&nbsp;<i>Option<br> ' . $product_attribs .
                            '</td><td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['PVprod_id'] .
                            '</td><td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['Pmodel'] .
                            '</td><td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['PVprod_price'] . '</td><td>' .
          $vendor_products[$l]['vendor_orders_products'][$i]['Pcomments'] . '</b></td></tr>';
        }
      }
      $email = $email . '</table><br><HR><br>';
      tep_mail($the_name, $the_email, EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID ,  $email .  '<br>', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS)  ;
      $vendor_order_sent = true;
      if ($debug == 'yes') {
        echo 'The $email(including headers:<br>Vendor Email Addy' . $the_email . '<br>Vendor Name' . $the_name . '<br>Vendor Contact' . $the_contact . '<br>Body--<br>' . $email . '<br>';
      }
      if ($vendor_order_sent == true) {
        tep_db_query("update " . TABLE_ORDERS_SHIPPING . " set vendor_order_sent = 'yes' where orders_id = '" . (int)$oID . "'");
      }
    }
    return true;
  } else {
    return false;
  }
  }
  // multi-vendor shipping //eof  
  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status_query = tep_db_query("SELECT orders_status_id, orders_status_name 
                                         from " . TABLE_ORDERS_STATUS . " 
                                       WHERE language_id = '" . (int)$languages_id . "'");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                               'text' => $orders_status['orders_status_name']);
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      
      case 'accept_order':
        include(DIR_FS_CATALOG_MODULES.'payment/paypal/admin/AcceptOrder.inc.php');
        break;

      case 'update_order':
        $oID = tep_db_prepare_input($_GET['oID']);
        $status = tep_db_prepare_input($_POST['status']);
        $comments = tep_db_prepare_input($_POST['comments']);
        
        $order_updated = false;
        $check_status_query = tep_db_query("SELECT customers_name, customers_email_address, orders_status, date_purchased
                                            FROM " . TABLE_ORDERS . "
                                            WHERE orders_id = " . (int)$oID);
        $check_status = tep_db_fetch_array($check_status_query);
        // always update date and time on order_status
        //check to see if can download status change
        if ( ($check_status['orders_status'] != $status) || tep_not_null($comments) || ($status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) ) {
          // RCI update order
          echo $cre_RCI->get('orders', 'updateorder', false);
          tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . tep_db_input($status) . "', last_modified = now() where orders_id = '" . (int)$oID . "'");
          if ( $status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE ) {
            tep_db_query("update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays = '" . tep_get_configuration_key_value('DOWNLOAD_MAX_DAYS') . "', download_count = '" . tep_get_configuration_key_value('DOWNLOAD_MAX_COUNT') . "' where orders_id = '" . (int)$oID . "'");
          }
          // multi-vendor shipping
          if (defined('MVS_STATUS') && MVS_STATUS == 'true') {
            if (defined('MVS_VENDOR_EMAIL_WHEN') && (MVS_VENDOR_EMAIL_WHEN == 'Admin' || MVS_VENDOR_EMAIL_WHEN == 'Both')) {
              if (isset($status)) {
                $order_sent_query = tep_db_query("SELECT vendor_order_sent, vendors_id 
                                                    from " . TABLE_ORDERS_SHIPPING . " 
                                                  WHERE orders_id = '" . $oID . "'");
                while ($order_sent_data = tep_db_fetch_array($order_sent_query)) {
                  $order_sent_ckeck = $order_sent_data['vendor_order_sent'];
                  $vendors_id = $order_sent_data['vendors_id'];
                  if ($order_sent_ckeck == 'no') {
                    $vendor_order_sent = false;
                    vendors_email($vendors_id, $oID, $status, $vendor_order_sent);
                  }
                }
              }
            } 
          }
          // multi-vendor shipping //eof          
          $customer_notified = '0';
          if (isset($_POST['notify']) && ($_POST['notify'] == 'on')) {
            $notify_comments = '';

            if (isset($_POST['notify_comments']) && ($_POST['notify_comments'] == 'on')) {
              $notify_comments = sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
            }

            $email = STORE_NAME . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" . EMAIL_TEXT_INVOICE_URL . ' ' . 
              tep_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($check_status['date_purchased']) . "\n\n" . $notify_comments . sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]);
            tep_mail($check_status['customers_name'], $check_status['customers_email_address'], EMAIL_TEXT_SUBJECT, $email, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            $customer_notified = '1';
          }

          tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . (int)$oID . "', '" . tep_db_input($status) . "', now(), '" . tep_db_input($customer_notified) . "', '" . tep_db_input($comments)  . "')");
          $order_updated = true;
        }

        if ($order_updated == true) {
          $messageStack->add_session('search', SUCCESS_ORDER_UPDATED, 'success');
        } else {
          $messageStack->add_session('search', WARNING_ORDER_NOT_UPDATED, 'warning');
        }

        tep_redirect(tep_href_link(FILENAME_ORDERS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=edit', 'SSL'));
        break;
        
      case 'deleteconfirm':
        $oID = tep_db_prepare_input($_GET['oID']);
        tep_remove_order($oID, $_POST['restock']);

        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')), 'SSL'));
        break;

      default :
        // RCI extend switch
        echo $cre_RCI->get('orders', 'actionswitch');
        break;        
    }
  }

  // enhanced search
  $order_exists = false;
  if (($action == 'edit') && isset($_GET['SoID'])) {
    if (is_numeric($_GET['SoID'])) {  // this must be an order id, so use the old format
      $_GET['oID'] = $_GET['SoID'];
      unset($_GET['SoID']);
    }
    // see if there are any matches
    $SoID = tep_db_input(tep_db_prepare_input($_GET['SoID']));
    
    $sql = "SELECT orders_id
            FROM " . TABLE_ORDERS . "
            WHERE customers_name LIKE '%" . $SoID . "%'
               OR LOWER( customers_email_address ) LIKE '%" . $SoID . "%'
               OR customers_company LIKE '%" . $SoID . "%'"; 
    $orders_query = tep_db_query($sql);
    $row_count = tep_db_num_rows($orders_query);
    if ($row_count < 1) {
      unset($_GET['SoID']);
      $messageStack->add('search', sprintf(ERROR_ORDER_DOES_NOT_EXIST, $SoID), 'error');
    } elseif ($row_count == 1) {
      // special case, only one, so go direct to edit
      $orders = tep_db_fetch_array($orders_query);
      $_GET['oID'] = $orders['orders_id'];
      $order_exists = true;
      unset($_GET['SoID']);
    } // if greater than 1, list all the matches
  }
  
  if (($action == 'edit') && isset($_GET['oID']) && $order_exists === false) {
    $oID = tep_db_prepare_input($_GET['oID']);

    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
    if (tep_db_num_rows($orders_query) > 0) {
      $order_exists = true;
    } else {
      unset($_GET['oID']);
      $messageStack->add('search', sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
    }
  }
  
  include(DIR_WS_CLASSES . 'order.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<script type="text/javascript" src="<?php echo (($request_type == 'SSL') ? 'https:' : 'http:'); ?>//ajax.googleapis.com/ajax/libs/jquery/<?php echo JQUERY_VERSION; ?>/jquery.min.js"></script>
<script type="text/javascript">
  if (typeof jQuery == 'undefined') {
    //alert('You are running a local copy of jQuery!');
    document.write(unescape("%3Cscript src='includes/javascript/jquery-1.6.2.min.js' type='text/javascript'%3E%3C/script%3E"));
  }
</script>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="includes/stylesheet-ie.css">
<![endif]-->
<script language="javascript" src="includes/general.js"></script>
<link type="text/css" rel="StyleSheet" href="includes/helptip.css">
<script type="text/javascript" src="includes/javascript/helptip.js"></script>
<script language="javascript"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=650,height=500,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
<?php 
// rci for javascript include
echo $cre_RCI->get('orders', 'javascript');
?>
<link rel="stylesheet" type="text/css" href="includes/headernavmenu.css">
<script type="text/javascript" src="includes/menu.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<div id="body">
<table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="body-table">
  <tr>  
    <!-- left_navigation //-->
    <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
    <!-- left_navigation_eof //-->
    <!-- body_text //-->
    <td valign="top" class="page-container"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <?php
      if (($action == 'edit') && ($order_exists == true)) {
        $order = new order($oID);
        ?>
        <tr>
          <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
              <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              <td align="right" width="100px"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action','referer')), 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
              <td align="right" width="100px">
                <?php echo '<a href="' . tep_href_link(FILENAME_EDIT_ORDERS, tep_get_all_get_params(array('action')), 'SSL') . '">' . tep_image_button('button_page_edit.png', IMAGE_EDIT) . '</a> &nbsp; '; ?>
              </td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
            <tr>
              <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="main" valign="top"><b><?php echo ENTRY_CUSTOMER; ?></b></td>
                  <td class="main">
                    <?php 
                    $formatId = (isset($order->customer['format_id']) && !empty($order->customer['format_id'])) ? $order->customer['format_id'] : '1';
                    echo tep_address_format($formatId, $order->customer, 1, '', '<br>'); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo ENTRY_TELEPHONE_NUMBER; ?></b></td>
                  <td class="main"><?php echo $order->customer['telephone']; ?></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo ENTRY_EMAIL_ADDRESS; ?></b></td>
                  <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo ENTRY_IPADDRESS; ?></b></td>
                  <td class="main"><?php echo $order->customer['ipaddy']; ?></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo ENTRY_IPISP; ?></b></td>
                  <td class="main"><?php echo $order->customer['ipisp']; ?></td>
                </tr>
              </table></td>
              <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="main" valign="top"><b><?php echo ENTRY_SHIPPING_ADDRESS; ?></b></td>
                  <td class="main">
                    <?php
                    $formatId = (isset($order->delivery['format_id']) && !empty($order->delivery['format_id'])) ? $order->delivery['format_id'] : '1';
                    echo tep_address_format($formatId, $order->delivery, 1, '', '<br>'); ?></td>
                </tr>
              </table></td>
              <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="main" valign="top"><b><?php echo ENTRY_BILLING_ADDRESS; ?></b></td>
                  <td class="main">
                    <?php 
                    $formatId = (isset($order->billing['format_id']) && !empty($order->billing['format_id'])) ? $order->billing['format_id'] : '1';
                    echo tep_address_format($formatId, $order->billing, 1, '', '<br>'); ?></td>
                </tr>
              </table></td>
            </tr>
          </table></td>
        </tr>
<?php
      // RCI code start
      echo $cre_RCI->get('orders', 'specialform');
      // RCI code eof
?>      
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <!-- add Order # // -->
          <tr>
            <td class="main"><b><!-- Order # --> <?php echo ORDER; ?></b></td>
            <td class="main"><?php echo tep_db_input($oID); ?></td>
          </tr>
          <!-- add date/time // -->
          <tr>
            <td class="main"><b><!-- Order Date & Time --><?php echo ORDER_DATE_TIME; ?></b></td>
            <td class="main"><?php echo tep_datetime_short($order->info['date_purchased']); ?></td>
          </tr>
          <?php  // begin PayPal_Shopping_Cart_IPN V3.15 DMG
          if (stristr($order->info['payment_method'], 'paypal')) {
            include(DIR_FS_CATALOG_MODULES . 'payment/paypal/admin/TransactionSummaryLogs.inc.php');
          }
          ?>
          <tr>
            <td class="main"><b><?php echo ENTRY_PAYMENT_METHOD; ?></b></td>
            <td class="main"><?php echo $order->info['payment_method']; ?></td>
          </tr>
          <?php
             if ($order->info['payment_method'] == 'Purchase Order') {
        ?>
        <tr>
          <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <tr>
          <td class="main" valign="top" align="left"><b><?php echo TEXT_INFO_PO ?></b></td>
          <td><table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="main"><?php echo TEXT_INFO_NAME ?></td>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              <td class="main"><?php echo $order->info['account_name']; ?></td></td>
            </tr>
            <tr>
              <td class="main"><?php echo TEXT_INFO_AC_NR ?></td>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              <td class="main"><?php echo $order->info['account_number'] ; ?></td>
            </tr>
            <tr>
              <td class="main"><?php echo TEXT_INFO_PO_NR ?></td>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              <td class="main"><?php echo $order->info['po_number'] ; ?></td>
            </tr>
          </table></td>
        </tr>
        <?php
        } 
          // RCI orders transaction
          echo $cre_RCI->get('orders', 'transaction'); 
                      
        // multi-vendor shipping
        if (defined('MVS_STATUS') && MVS_STATUS == 'true') {
          echo '<tr><td>' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td></tr>' . "\n";
          $orders_vendors_data_query = tep_db_query("select distinct ov.orders_id, ov.vendors_id, ov.vendor_order_sent, v.vendors_name from " . TABLE_ORDERS_SHIPPING . " ov, " . TABLE_VENDORS . " v where v.vendors_id=ov.vendors_id and orders_id='" . (int)$oID . "' group by vendors_id");
          while ($orders_vendors_data=tep_db_fetch_array($orders_vendors_data_query)) {
            echo '<tr class="dataTableRow"><td class="dataTableContent" valign="top" align="left">Order Sent to ' .$orders_vendors_data['vendors_name'] . ':<b> ' . $orders_vendors_data['vendor_order_sent'] . '</b><br></td>';
          }
          echo '</tr>';
        }
        // multi-vendor shipping //eof    
        ?>
      </table>
      <?php
      require(DIR_WS_MODULES . 'afs_v1.0/algo_fraud_screener.php');
      ?> 
      </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <?php 
      // multi-vendor shipping
      if (defined('MVS_STATUS') && MVS_STATUS == 'true') {
        require ('vendor_order_info.php'); 
      }
      // multi-vendor shipping //eof
      ?>      
      <!-- Begin Products Listings Block -->
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent" colspan="2" style="white-space:nowrap;"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
            <td class="dataTableHeadingContent" style="white-space:nowrap;"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
            <td class="dataTableHeadingContent" align="right" style="white-space:nowrap;"><?php echo TABLE_HEADING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right" style="white-space:nowrap;"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right" style="white-space:nowrap;"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right" style="white-space:nowrap;"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right" style="white-space:nowrap;"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
          </tr>
<?php

if (defined('MVS_STATUS') && MVS_STATUS == 'true') {
  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    for ($x=0, $y=sizeof($order->products[$i]['orders_products_id']); $x<$y; $x++) {
      if (isset($order->products[$x]['id']) && (int)$order->products[$x]['id'] > 0 ) {
        // Begin RMA Returns System
        $returns_check_query = tep_db_query("SELECT r.rma_value, rp.products_id FROM " . TABLE_RETURNS . " r, " . TABLE_RETURNS_PRODUCTS_DATA . " rp where r.returns_id = rp.returns_id and r.order_id = '" . $oID . "' and rp.products_id = '" .  (int)$order->products[$x]['id'] . "' ");

        if (!tep_db_num_rows($returns_check_query)) {
          if (isset($order->products[$x]['return']) && $order->products[$x]['return'] != '1') {
            $return_link = '<a href="' . tep_href_link(FILENAME_RETURN, 'order_id=' . $oID . '&products_id=' . ((isset($order->products[$x]['id']) ? (int)$order->products[$x]['id'] : 0)), 'SSL') . '"><u>' . '<font color="818180">Schedule Return</font>' .'</a></u>';
          }
          // Don't show Return link if order is still pending or processing
          // You can change this or comment it out as best fits your store configuration
          if (($order->info['orders_status'] == 'Pending') OR ($order->info['orders_status'] == 'Processing')) {
            $return_link = '';
          }
        } else {
          $returns = tep_db_fetch_array($returns_check_query);
          $return_link = '<a href=' . tep_href_link(FILENAME_RETURNS, 'cID=' . $returns['rma_value'], 'SSL') . '><font color=red><b><i>Returns</b></i></font></a>';
        }
        // End RMA Returns System

        echo '          <tr class="dataTableRow">' . "\n" .
        '            <td class="dataTableContent" valign="top" align="right">' . $order->products[$x]['qty'] . '&nbsp;x</td>' . "\n" .
        '            <td class="dataTableContent" valign="top">' . $order->products[$x]['name'] . '&nbsp;&nbsp;' . (isset($return_link) ? $return_link : '');

        if (isset($order->products[$x]['attributes']) && (sizeof($order->products[$x]['attributes']) > 0)) {
          for ($j = 0, $k = sizeof($order->products[$x]['attributes']); $j < $k; $j++) {
            echo '<br><small>&nbsp;<i> - ' . $order->products[$x]['attributes'][$j]['option'] . ': ' . $order->products[$x]['attributes'][$j]['value'];
            if ($order->products[$x]['attributes'][$j]['price'] != '0') echo ' (' .    
              $order->products[$x]['attributes'][$j]['prefix'] . $currencies->format($order->products[$x]['attributes'][$j]['price'] * $order->products[$x]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
            echo '</i></small>';
          }
        }
        
        echo '            </td>' . "\n" .
        '            <td class="dataTableContent" valign="top">' . $order->products[$x]['model'] . '</td>' . "\n" .
        '            <td class="dataTableContent" align="right" valign="top">' . tep_display_tax_value($order->products[$x]['tax']) . '%</td>' . "\n" .
        '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format($order->products[$x]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
        '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format(tep_add_tax($order->products[$x]['final_price'], $order->products[$x]['tax'], true), true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
        '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format($order->products[$x]['final_price'] * $order->products[$x]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
        '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format(tep_add_tax($order->products[$x]['final_price'], $order->products[$x]['tax'], true) * $order->products[$x]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n";
        echo '          </tr>' . "\n";
      }
    }
  }
} else {
  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    // Begin RMA Returns System
    $returns_check_query = tep_db_query("SELECT r.rma_value, rp.products_id FROM " . TABLE_RETURNS . " r, " . TABLE_RETURNS_PRODUCTS_DATA . " rp where r.returns_id = rp.returns_id and r.order_id = '" . $oID . "' and rp.products_id = '" . (isset($order->products[$i]['id']) ? (int)$order->products[$i]['id'] : 0) . "' ");

    if (!tep_db_num_rows($returns_check_query)) {
      if (isset($order->products[$i]['return']) && $order->products[$i]['return'] != '1') {
        $return_link = '<a href="' . tep_href_link(FILENAME_RETURN, 'order_id=' . $oID . '&products_id=' . ((isset($order->products[$i]['id']) ? (int)$order->products[$i]['id'] : 0)), 'SSL') . '"><u>' . '<font color="818180">Schedule Return</font>' .'</a></u>';
      }
      // Don't show Return link if order is still pending or processing
      // You can change this or comment it out as best fits your store configuration
      if (($order->info['orders_status'] == 'Pending') OR ($order->info['orders_status'] == 'Processing')) {
        $return_link = '';
      }
    } else {
      $returns = tep_db_fetch_array($returns_check_query);
      $return_link = '<a href=' . tep_href_link(FILENAME_RETURNS, 'cID=' . $returns['rma_value'], 'SSL') . '><font color=red><b><i>Returns</b></i></font></a>';
    }
    // End RMA Returns System
    
    echo '          <tr class="dataTableRow">' . "\n" .
         '            <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
         '            <td class="dataTableContent" valign="top">' . $order->products[$i]['name'] . '&nbsp;&nbsp;' . (isset($return_link) ? $return_link : '');

    if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
      for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
        echo '<br><small>&nbsp;<i> - ' . ($order->products[$i]['attributes'][$j]['option'] == '' ? $order->products[$i]['attributes'][$j]['option_name'] : $order->products[$i]['attributes'][$j]['option']) . ': ' . $order->products[$i]['attributes'][$j]['value'];
        if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
        echo '</i></small>';
      }
    }
    echo '            </td>' . "\n" .
         '            <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . '</td>' . "\n" .
         '            <td class="dataTableContent" align="right" valign="top">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
         '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
         '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true), true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
         '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
         '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n";
    echo '          </tr>' . "\n";
  }
}

?>
          <tr>
            <td align="right" colspan="8"><table border="0" cellspacing="0" cellpadding="2">
<?php
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      echo '              <tr>' . "\n" .
           '                <td align="right" class="smallText">' . $order->totals[$i]['title'] . '</td>' . "\n" .
           '                <td align="right" class="smallText">' . $order->totals[$i]['text'] . '</td>' . "\n" .
           '              </tr>' . "\n";
    }
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><table border="1" cellspacing="0" cellpadding="5">
          <tr>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_DATE_ADDED; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_STATUS; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
          </tr>
<?php
    $orders_history_query = tep_db_query("select orders_status_id, date_added, customer_notified, comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . tep_db_input($oID) . "' order by date_added");
    if (tep_db_num_rows($orders_history_query)) {
      while ($orders_history = tep_db_fetch_array($orders_history_query)) {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" align="center">' . tep_datetime_short($orders_history['date_added']) . '</td>' . "\n" .
             '            <td class="smallText" align="center">';
        if ($orders_history['customer_notified'] == '1') {
          echo tep_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK) . "</td>\n";
        } else {
          echo tep_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS) . "</td>\n";
        }
        echo '            <td class="smallText">' . $orders_status_array[$orders_history['orders_status_id']] . '</td>' . "\n" .
             '            <td class="smallText">' . nl2br(tep_db_output($orders_history['comments'])) . '&nbsp;</td>' . "\n" .
             '          </tr>' . "\n";
      }
    } else {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" colspan="5">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '          </tr>' . "\n";
    }
?>
        </table></td>
      </tr>
      <tr>
        <td class="main"><br><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr><?php echo tep_draw_form('status', FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=update_order', 'post', '', 'SSL'); ?>
        <td class="main"><?php echo tep_draw_textarea_field('comments', 'soft', '60', '5'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><b><?php echo ENTRY_STATUS; ?></b> <?php echo tep_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status_number']); ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_NOTIFY_CUSTOMER; ?></b> <?php echo tep_draw_checkbox_field('notify', '', true); ?></td>
                <td class="main"><b><?php echo ENTRY_NOTIFY_COMMENTS; ?></b> <?php echo tep_draw_checkbox_field('notify_comments', '', true); ?></td>
              </tr>
            </table></td>
            <td valign="top"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
          </tr>
          <?php
          // RCI start
          echo $cre_RCI->get('orders', 'bottom');
          // RCI eof
          ?>
        </table></td>
      </form></tr>
      <tr>
        <?php  
        // RCI start
        $buttons_bottom = $cre_RCI->get('orders', 'buttonsbottom');
        //Begin PayPal IPN V3.15 DMG (I improvised here.)
        $oscid = '&' . tep_session_name() . '=' . tep_session_id();
        if (strstr(strtolower($order->info['payment_method']), 'paypal') && isset($_GET['referer']) && $_GET['referer'] == 'ipn'){
          ?>
          <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_PAYPAL, tep_get_all_get_params(array('action')), 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a><a href="javascript:popupWindow(\'' . (HTTP_SERVER . DIR_WS_ADMIN . FILENAME_ORDERS_INVOICE) . '?' . (tep_get_all_get_params(array('oID')) . 'oID=' . $_GET['oID']) . $oscid . '\')">' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a><a href="javascript:popupWindow(\'' . (HTTP_SERVER . DIR_WS_ADMIN . FILENAME_ORDERS_PACKINGSLIP) . '?' . (tep_get_all_get_params(array('oID')) . 'oID=' . $_GET['oID']) . $oscid . '\')">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>' . $buttons_bottom; ?></td>
          <?php
        } else { //not paypal
          ?>
          <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')), 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a><a href="javascript:popupWindow(\'' . (HTTP_SERVER . DIR_WS_ADMIN . FILENAME_ORDERS_INVOICE) . '?' . (tep_get_all_get_params(array('oID')) . 'oID=' . $_GET['oID']) . $oscid . '\')">' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a><a href="javascript:popupWindow(\'' . (HTTP_SERVER . DIR_WS_ADMIN . FILENAME_ORDERS_PACKINGSLIP) . '?' . (tep_get_all_get_params(array('oID')) . 'oID=' . $_GET['oID']) . $oscid . '\')">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>' . $buttons_bottom; ?></td>
          <?php
        }  //end PapPal IPN V3.15
        ?>
      </tr>
<?php
  } else {
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr><?php 
                  echo tep_draw_form('orders', FILENAME_ORDERS, '', 'get', '', 'SSL'); 
                  tep_hide_session_id();
                ?>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('SoID', '', 'size="12"') . tep_draw_hidden_field('action', 'edit'); ?></td>
              </form></tr>
              <tr><?php echo tep_draw_form('status', FILENAME_ORDERS, '', 'get', '', 'SSL'); ?>
                <td class="smallText" align="right">
                  <?php echo HEADING_TITLE_STATUS . ' ' . tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), (isset($_GET['status']) ? (int)$_GET['status']: ''), 'onChange="this.form.submit();"');
                  tep_hide_session_id(); ?>
                </td>
              </form></tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
<?php
      // RCI start
      echo $cre_RCI->get('orders', 'listingtop');
      // RCI eof
?>          
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="data-table">
<?php
    $oscid = '&' . tep_session_name() . '=' . $_GET[tep_session_name()];
    if (isset($_GET['SoID'])) {
      $oscid .= '&SoID=' . $_GET['SoID'];
    }
    
    $HEADING_CUSTOMERS = TABLE_HEADING_CUSTOMERS;
    $HEADING_CUSTOMERS .= '<a href="' . tep_href_link(basename($PHP_SELF), 'sort=customer&order=ascending') . '">';
    $HEADING_CUSTOMERS .= '&nbsp;<img src="images/arrow_up.gif" border="0"></a>';
    $HEADING_CUSTOMERS .= '<a href="' . tep_href_link(basename($PHP_SELF), 'sort=customer&order=decending') . '">';
    $HEADING_CUSTOMERS .= '&nbsp;<img src="images/arrow_down.gif" border="0"></a>';
    $HEADING_DATE_PURCHASED = TABLE_HEADING_DATE_PURCHASED;
    $HEADING_DATE_PURCHASED .= '<a href="' . tep_href_link(basename($PHP_SELF), 'sort=date&order=ascending') . '">';
    $HEADING_DATE_PURCHASED .= '&nbsp;<img src="images/arrow_up.gif" border="0"></a>';
    $HEADING_DATE_PURCHASED .= '<a href="' . tep_href_link(basename($PHP_SELF), 'sort=date&order=decending') . '">';
    $HEADING_DATE_PURCHASED .= '&nbsp;<img src="images/arrow_down.gif" border="0"></a>';
?>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDERID; ?></td>
                <td class="dataTableHeadingContent"><?php echo $HEADING_CUSTOMERS; ?></td>
                <?php if ((MODULE_ADDONS_POINTS_STATUS == 'True') && (DISPLAY_ORDERS_POINTS_INFO == 'true')) { ?>
                <!-- Added for Points & Rewards -->
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_CART_TOTAL; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_POINTS_REDEEMED; ?></td>
                <!-- Added for Points & Rewards -->
                <?php } ?>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo $HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $sortorder = 'order by ';
    $sort = (isset($_GET["sort"]) ? $_GET["sort"] : '');
    if  ($sort == 'customer') {
      if ($_GET["order"] == 'ascending') {
        $sortorder .= 'o.customers_name  asc, ';
      } else {
        $sortorder .= 'o.customers_name desc, ';
      }
    } elseif ($sort == 'date') {
      if ($_GET["order"] == 'ascending') {
        $sortorder .= 'o.date_purchased  asc, ';
      } else {
        $sortorder .= 'o.date_purchased desc, ';
      }
    }
    $sortorder .= 'o.orders_id DESC';
    if (isset($_GET['cID'])) {
      $cID = tep_db_prepare_input($_GET['cID']);
      $orders_query_raw = "select o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$cID . "' and ot.orders_id = o.orders_id and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' order by orders_id DESC";
    } elseif (isset($_GET['status']) && (tep_not_null($_GET['status']))) {
      $status = tep_db_prepare_input($_GET['status']);
      $orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and s.orders_status_id = '" . (int)$status . "' order by o.orders_id DESC";
    } elseif (isset($_GET['SoID'])) {
      $SoID = tep_db_input(tep_db_prepare_input($_GET['SoID']));
      $orders_query_raw = "SELECT o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased,
                                  o.last_modified, o.currency, o.currency_value, s.orders_status_name
                           FROM " . TABLE_ORDERS . " o,
                                " . TABLE_ORDERS_STATUS . " s
                           WHERE o.orders_status = s.orders_status_id
                             AND s.language_id = " . (int)$languages_id . "
                             AND (o.customers_name LIKE '%" . $SoID . "%'
                                  OR LOWER( o.customers_email_address ) LIKE '%" . $SoID . "%'
                                  OR o.customers_company LIKE '%" . $SoID . "%'
                                 ) " . $sortorder;
    } else {
      $orders_query_raw = "SELECT o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name
                           FROM " . TABLE_ORDERS . " o,
                                " . TABLE_ORDERS_STATUS . " s
                           WHERE o.orders_status = s.orders_status_id
                             AND s.language_id = " . (int)$languages_id . "
                           " . $sortorder;
    }
    $orders_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_query_raw, $orders_query_numrows);
    $orders_query = tep_db_query($orders_query_raw);
    while ($orders = tep_db_fetch_array($orders_query)) {
      unset($order_total1);
      $order_total1 = TEXT_INFO_ABANDONDED;
      $orders_total_query_raw = "select ot.text as order_total from " . TABLE_ORDERS_TOTAL . " ot where  ot.orders_id = '" . $orders['orders_id'] . "' and ot.class = 'ot_total' ";
      $orders_query_total = tep_db_query($orders_total_query_raw);
      while ($orders1 = tep_db_fetch_array($orders_query_total)) {
        $order_total1 = $orders1['order_total'];
        if (!$order_total1){
          $order_total1 = TEXT_INFO_ABANDONDED;
        }
      }
      
      //Added for Points & Rewards
      if ((MODULE_ADDONS_POINTS_STATUS == 'True') && (DISPLAY_ORDERS_POINTS_INFO == 'true')) {
        $ot_text_query = tep_db_query("select text from " . TABLE_ORDERS_TOTAL . " where title = 'Points Redeemed:' and orders_id = '" . $orders['orders_id'] . "'");
        $ot_text = tep_db_fetch_array($ot_text_query);
        $st_text_query = tep_db_query("select text from " . TABLE_ORDERS_TOTAL . " where class = 'ot_subtotal' and orders_id = '" . $orders['orders_id'] . "'");
        $st_text = tep_db_fetch_array($st_text_query);
        if (tep_not_null($ot_text)) {
          $ot = $ot_text['text'];
        } else {
          $ot = '--';
        }
      }
      //Added for Points & Rewards
    
      if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ($_GET['oID'] == $orders['orders_id']))) && !isset($oInfo)) {
        $oInfo = new objectInfo($orders);
      }
      // RCO start
      if ($cre_RCO->get('orders', 'listingselect') !== true) {
        if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) {
          echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'SSL') . '\'">' . "\n";
        } else {
          echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id'], 'SSL') . '\'">' . "\n";
        }
?>
                <td class="dataTableContent" align="left">
<?php
        echo '<b>' . $orders['orders_id'] . '</b>';
        $products = "";
        $products_query = tep_db_query("SELECT orders_products_id, products_name, products_quantity 
                                        from " . TABLE_ORDERS_PRODUCTS . " 
                                        WHERE orders_id = '" . tep_db_input($orders['orders_id']) . "' ");
        while ($products_rows = tep_db_fetch_array($products_query)) {
          $products .= ($products_rows["products_quantity"]) . "x " . (tep_html_noquote($products_rows["products_name"])) . "<br>";
          $result_attributes = tep_db_query("SELECT products_options, products_options_values 
                                             from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " 
                                             WHERE orders_id = '" . tep_db_input($orders['orders_id']). "' 
                                               and orders_products_id = '" . $products_rows["orders_products_id"] . "' 
                                             ORDER BY products_options");
          while ($row_attributes = tep_db_fetch_array($result_attributes)) {
            $products .= " - " . (tep_html_noquote($row_attributes["products_options"])) . ": " . (tep_html_noquote($row_attributes["products_options_values"])) . "<br>";
          }
        }
?>  
                  <img src="images/icons/comment2.gif" onmouseover="showhint('<?php echo '' . $products . ''; ?>', this, event, '300px'); return false" align="top" border="0">
                </td>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders['orders_id'] . '&action=edit', 'SSL') . '">' . tep_image(DIR_WS_ICONS . 'magnifier.png', ICON_PREVIEW) . '</a>&nbsp;' . $orders['customers_name']; ?></td>
                <?php if ((MODULE_ADDONS_POINTS_STATUS == 'True') && (DISPLAY_ORDERS_POINTS_INFO == 'true')) { ?>
                <!-- Added for Points & Rewards -->
                <td class="dataTableContent" align="right"><?php echo $st_text['text']; ?></td>
                <td class="dataTableContent" align="right"><font color="red"><?php echo $ot; ?></font></td>
                <!-- Added for Points & Rewards -->
                <?php } ?>
                <td class="dataTableContent" align="right"><?php echo strip_tags($order_total1); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_datetime_short($orders['date_purchased']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $orders['orders_status_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) { echo tep_image(DIR_WS_IMAGES . 'arrow_right_blue.png', ''); } else { echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id'], 'SSL') . '">' . tep_image(DIR_WS_IMAGES . 'information.png', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      }  // RCO eof
    }
?>            </table><table border="0" cellpadding="0" cellspacing="0" width="100%" class="data-table-foot">
              <tr>
                <td colspan="8"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                    <td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], tep_get_all_get_params(array('page', 'oID', 'action'))); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
<?php
                    // RCI code start
                    echo $cre_RCI->get('orders', 'listingbottom');
                    // RCI code eof
?>
                  </tr>
                </table></td>
              </tr>                
            </table></td>
<?php
    $heading = array();
    $contents = array();

    switch ($action) {
      case 'delete':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ORDER . '</b>');
        $contents = array('form' => tep_draw_form('orders', FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm', 'post' , '', 'SSL'));
        $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br><br>');
        $contents[] = array('text' => TEXT_INFO_DELETE_DATA . '&nbsp;' . $oInfo->customers_name . '<br>');
        $contents[] = array('text' => TEXT_INFO_DELETE_DATA_OID . '&nbsp;<b>' . $oInfo->orders_id . '</b><br>');
        $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
        $contents[] = array('align' => 'center', 'text' => '<br><a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id, 'SSL') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . tep_image_submit('button_delete.gif', IMAGE_DELETE));
        break;

      default:
        if (isset($oInfo) && is_object($oInfo)) {
          $heading[] = array('text' => '<b>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . $oInfo->customers_name . '</b>');  
          // RCO start
          if ($cre_RCO->get('orders', 'sidebarbuttons') !== true) {  
            $contents[] = array('align' => 'center', 'text' => '<br><a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'SSL') . '">' . tep_image_button('button_edit_status.gif', IMAGE_EDIT_STATUS) . '</a><a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete', 'SSL') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>'); 
            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_EDIT_ORDERS, 'oID=' . $oInfo->orders_id, 'SSL'). '">' . tep_image_button('button_edit_order.gif', IMAGE_EDIT_ORDER) . '</a><a href="' . tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id, 'SSL') . '" TARGET="_blank">' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> ');
            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id, 'SSL') . '" TARGET="_blank">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>');
          }
          // RCO eof        
          // RCI sidebar buttons
          $returned_rci = $cre_RCI->get('orders', 'sidebarbuttons');
          $contents[] = array('align' => 'center', 'text' => $returned_rci);
          $contents[] = array('text' => '<br>' . TEXT_DATE_ORDER_CREATED . ' <b>' . tep_date_short($oInfo->date_purchased) . '</b>');  
          if (tep_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' <b>' . tep_date_short($oInfo->last_modified) . '</b>');
          $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENT_METHOD . ' <b>'  . $oInfo->payment_method . '</b>');
          // multi-vendor shipping
          if (defined('MVS_STATUS') && MVS_STATUS == 'true') {
            $orders_vendors_data_query = tep_db_query("select distinct ov.orders_id, ov.vendors_id, ov.vendor_order_sent, v.vendors_name from " . TABLE_ORDERS_SHIPPING . " ov, " . TABLE_VENDORS . " v where v.vendors_id=ov.vendors_id and orders_id='" . $oInfo->orders_id . "' group by vendors_id");
            while ($orders_vendors_data=tep_db_fetch_array($orders_vendors_data_query)) {
              $contents[] = array('text' => VENDOR_ORDER_SENT . '<b>' . $orders_vendors_data['vendors_name'] . '</b>:<b> ' . $orders_vendors_data['vendor_order_sent'] . '</b><br>');
            }
          }
          // multi-vendor shipping //eof          
          // RCI sidebar bottom
          $returned_rci = $cre_RCI->get('orders', 'sidebarbottom');
          $contents[] = array('text' => $returned_rci);
        }
        break;
    }
    if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
      echo '            <td width="25%" valign="top">' . "\n";
      $box = new box;
      echo $box->infoBox($heading, $contents);
      echo '            </td>' . "\n";
    }
?>
          </tr>
        </table></td>
      </tr>
<?php
  }
  // RCI code start
  echo $cre_RCI->get('global', 'bottom');                                        
  // RCI code eof
?>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
</div>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>