<?php
  /*
  $Id: specialsbycategory.php,v 2.0 2008/05/05 00:36:41 datazen Exp $

  CRE Loaded, Commerical Open Source eCommerce
  http://www.creloaded.com

  Copyright (c) 2008 CRE Loaded
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
  */

  require('includes/application_top.php');

  if( isset($_GET['action']) && isset($_GET['flag']) && isset($_GET['id']) && $_GET['action'] == 'setflag' ) {
    tep_db_query ("UPDATE " . TABLE_SPECIALS . " SET status = '".$_GET['flag']."' where specials_id = '". $_GET['id'] ."'");
  } 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <title><?php echo HEADING_TITLE; ?></title>
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

    <link rel="stylesheet" type="text/css" href="includes/javascript/calendar.css">
    <script language="JavaScript" src="includes/javascript/calendarcode.js"></script>

    <link rel="stylesheet" type="text/css" href="includes/headernavmenu.css">
    <script type="text/javascript" src="includes/menu.js"></script>
  </head>
  <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">  
    <div id="popupcalendar" class="text"></div>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div id="body">
    <table border="0" width="100%" cellspacing="0" cellpadding="0" class="body-table">
      <tr>
        <td>
          <!-- left_navigation //-->
          <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
          <!-- left_navigation_eof //-->
        </td>
        <!-- body_text //-->
        <td valign="top" class="page-container">
          <?php
            //Fetch all variables
            $fullprice = (isset($_GET['fullprice']) ? $_GET['fullprice'] : '');
            $productid = (isset($_GET['productid']) ? (int)$_GET['productid'] : '0');
            $inputupdate = (isset($_GET['inputupdate']) ? $_GET['inputupdate'] : '');
            $categories = (isset($_GET['categories']) ? (int)$_GET['categories'] : '0');
            $manufacturer = (isset($_GET['manufacturer']) ? (int)$_GET['manufacturer'] : '0');
            if ($manufacturer) {
              $man_filter = " and manufacturers_id = '$manufacturer' ";
            } else {
              $man_filter = ' ';
            }
            if (array_key_exists('discount', $_GET)) {
              if (is_numeric($_GET['discount'])) {
                $discount = (float)$_GET['discount'];
              } else {
                $discount = -1;     
              }
            } else { 
              $discount = -1;
            }
            if ($fullprice == 'yes') {
              tep_db_query("DELETE FROM " . TABLE_SPECIALS . " WHERE products_id=$productid;");
            } elseif ($inputupdate == "yes") {
              $inputspecialprice = (isset($_GET['inputspecialprice']) ? $_GET['inputspecialprice'] : '');

              if (substr($inputspecialprice, -1) == '%') {
                $productprice = (isset($_GET['productprice']) ? (float)$_GET['productprice'] : 0);
                $specialprice = ($productprice - (($inputspecialprice / 100) * $productprice));
              } elseif (substr($inputspecialprice, -1) == 'i') {
                $taxrate = (isset($_GET['taxrate']) ? (float)$_GET['taxrate'] : 1);
                $productprice = (isset($_GET['productprice']) ? (float)$_GET['productprice'] : 0);
                $specialprice = ($inputspecialprice /(($taxrate/100)+1));
              } else {
                $specialprice = $inputspecialprice;
              }
              $alreadyspecial = tep_db_query ("SELECT * FROM " . TABLE_SPECIALS . " WHERE products_id=$productid");
              $specialproduct= tep_db_fetch_array($alreadyspecial);
              if ($specialproduct["specials_id"]){
                //print ("Database updated. Status:".$specialproduct["status"]);
                tep_db_query ("UPDATE " . TABLE_SPECIALS . " SET specials_new_products_price='$specialprice' where products_id=$productid  "); 
              } else {
                //print("New product added to specials table");
                $today = date("Y-m-d H:i:s");
                tep_db_query ("INSERT INTO " . TABLE_SPECIALS . " (specials_id, products_id, specials_new_products_price, specials_date_added, specials_last_modified, expires_date, date_status_change, status ) VALUES ('','$productid','$specialprice','$today','$today','0','','1')");
              }
            }
            $expire_date = $_GET['year'].'-'.$_GET['month'].'-'.$_GET['day'].' 00:00:00';

            $category_pid = tep_db_query ("SELECT products_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE categories_id = '".(int)$_GET['categories']."'");
            while($cid_pid = tep_db_fetch_array($category_pid)) {
              $prod_id = $cid_pid['products_id'];    

              if (isset($_GET['year']) && isset($_GET['month']) && isset($_GET['day'])){
                tep_db_query ("UPDATE " . TABLE_SPECIALS . " SET expires_date = '". $expire_date ."' where products_id = $prod_id");      
              }
            }
          ?>
          <form name="specialbycategory" action="<?php echo $current_page; ?>" method="get">
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr class="dataTableHeadingRow">
              <td colspan="8">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                  <tr class="dataTableHeadingRow">
                    <td width="22%" align="left">
                      <?php
                        echo '<FONT SIZE="2" COLOR="white">'.TEXT_SELECT_CAT.':'.'</FONT>';
                        echo tep_draw_pull_down_menu('categories', tep_get_category_tree(), $categories);

                        if (isset($_GET[tep_session_name()])) {
                          echo tep_draw_hidden_field(tep_session_name(), $_GET[tep_session_name()]);
                        }
                        $manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
                        $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
                        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
                          $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
                            'text' => $manufacturers['manufacturers_name']);
                        }

                      ?>
                    </td>
                    <td  width="20%" align="left">
                      <?php
                        echo '<FONT SIZE="2" COLOR="white">'.TEXT_SELECT_MAN.':'.'</FONT>';
                        echo tep_draw_pull_down_menu('manufacturer',$manufacturers_array, $manufacturer);
                      ?>
                    </td>
                    <td  width="34%">
                      <?php
                        echo '<FONT SIZE="2" COLOR="white">'.TEXT_ENTER_DISCOUNT.':'.'</FONT>';
                      ?>          
                      <input type="text" size="4" name="discount" value="<?php if ($discount > 0) { echo $discount; }; ?>">
                      <?php 
                        echo '<FONT SIZE="2" COLOR="white">'.TEXT_PCT_AND.'</FONT>'; 
                      ?>
                    </td>
                  </tr>
                  <tr>
                    <td colspan ="2"> 
                      <table cellpadding="0" cellspacing="0" border="0">
                        <tr>         
                          <td><?php
                              echo '<FONT SIZE="2" COLOR="white">'.EXPIRATION_DATE.'</FONT>'.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                              echo tep_draw_input_field('day', (''), 'size="2" maxlength="2" class="cal-TextBox"') . tep_draw_input_field('month', (''), 'size="2" maxlength="2" class="cal-TextBox"') . tep_draw_input_field('year', (''), 'size="4" maxlength="4" class="cal-TextBox"'); ?>
                          </td>
                          <td><a class="so-BtnLink" href="javascript:calClick();return false;" onmouseover="calSwapImg('BTN_date', 'img_Date_OVER',true);" onmouseout="calSwapImg('BTN_date', 'img_Date_UP',true);" onclick="calSwapImg('BTN_date', 'img_Date_DOWN');showCalendar('specialbycategory','dteWhen','BTN_date');return false;">
                            <?php echo tep_image(DIR_WS_IMAGES . 'cal_date_up.gif', 'Calendar', '22', '17', 'align="absmiddle" name="BTN_date"'); ?></a></td>
                          <td> <input type="submit" value="<?php echo TEXT_BUTTON_SUBMIT; ?>"></td>
                        </tr>
                      </table>
                    </td> 
                  </tr>
                </table>
                </form>
              </td>
            </tr>
            <tr class="dataTableContent">
              <td class="dataTableContent" colspan="6">
                <ul>
                  <li><?php echo TEXT_INSTRUCT_1; ?></li>
                  <li><?php echo TEXT_INSTRUCT_2; ?></li>
                </ul>
              </td>
            </tr>
            <?php
              if ($discount == -1) {
                //echo 'do nothing';
              } else if ($discount == 0) {
                if ($categories) {
                  $result2 = tep_db_query("SELECT p.products_id 
                    from " . TABLE_PRODUCTS . " p, 
                    " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc 
                    WHERE p.products_id = ptc.products_id 
                    and ptc.categories_id = $categories" . $man_filter);
                } else {
                  $result2 = tep_db_query("SELECT p.products_id 
                    from " . TABLE_PRODUCTS . " p 
                    WHERE 1=1" . $man_filter);
                }
                while ( $row = tep_db_fetch_array($result2) ){
                  $allrows[] = $row["products_id"];
                }
                tep_db_query("DELETE FROM " . TABLE_SPECIALS . " WHERE products_id in ('".implode("','",$allrows)."')");
              } else if ($discount > 0) {  
                $specialprice = $discount / 100;
                if ($categories) {
                  $result2 = tep_db_query("SELECT p.products_id, p.products_price 
                    from " . TABLE_PRODUCTS . " p, 
                    " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc 
                    WHERE p.products_id = ptc.products_id 
                    and ptc.categories_id = $categories" . $man_filter);
                } else {
                  $result2 = tep_db_query("select p.products_id, p.products_price from " . TABLE_PRODUCTS . " p where 1=1 " . $man_filter);
                }
                while ( $row = tep_db_fetch_array($result2) ){
                  $hello2 = $row["products_price"];
                  $hello3 = $hello2 * $specialprice;
                  $hello4 = $hello2 - $hello3;
                  $number = $row["products_id"];
                  $result3 = tep_db_query("select * from " . TABLE_SPECIALS . " where products_id = $number");
                  $num_rows = tep_db_num_rows($result3);
                  if ($num_rows == 0){
                    tep_db_query ("INSERT INTO " . TABLE_SPECIALS . " (products_id, specials_new_products_price, specials_date_added, specials_last_modified, expires_date, date_status_change, status ) VALUES ('$number','$hello4','$today','$today','0','','1')");
                  } else {
                    tep_db_query ("Update " . TABLE_SPECIALS . " set specials_new_products_price='$hello4' where products_id=$number");
                  }
                }
              }
              print ("
            <tr class=\"dataTableHeadingRow\">
              <td class=\"dataTableHeadingContent\">". TABLE_HEADING_PRODUCTS ."</td>
              <td class=\"dataTableHeadingContent\">" . TABLE_HEADING_PRODUCTS_PRICE ."</td>
              <td class=\"dataTableHeadingContent\">" . TABLE_HEADING_SPECIAL_PRICE ."</td>
              <td class=\"dataTableHeadingContent\">" . TABLE_HEADING_PCT_OFF ."</td>
              <td class=\"dataTableHeadingContent\" align ='center'>" . TABLE_HEADING_STATUS . "</td>
              <td class=\"dataTableHeadingContent\"  align ='center'>" . TABLE_HEADING_EXPIRES . "</td>
              <td class=\"dataTableHeadingContent\" align ='center'></td>
            </tr>");

              if ($categories) {
                $result2 = tep_db_query("SELECT * 
                  from " . TABLE_PRODUCTS_DESCRIPTION . " pd, 
                  " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc, 
                  " . TABLE_PRODUCTS . " p 
                  WHERE pd.products_id = ptc.products_id 
                  and p.products_id = ptc.products_id 
                  and ptc.categories_id = $categories 
                  and pd.language_id = " .(int)$languages_id . $man_filter . " 
                ORDER BY pd.products_name asc ");
              } else if ($manufacturer) {
                $result2 = tep_db_query("SELECT * 
                  from " . TABLE_PRODUCTS_DESCRIPTION . " pd, 
                  " . TABLE_PRODUCTS . " p 
                  WHERE pd.products_id=p.products_id 
                  and pd.language_id = " .(int)$languages_id . $man_filter . " 
                ORDER BY pd.products_name asc ");
              } else {
                $result2 = tep_db_query("SELECT * 
                  from " . TABLE_PRODUCTS_DESCRIPTION . " pd, 
                  " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc, 
                  " . TABLE_PRODUCTS . " p 
                  WHERE pd.products_id = ptc.products_id 
                  and p.products_id = ptc.products_id 
                  and ptc.categories_id = $categories 
                  and pd.language_id = " .(int)$languages_id . $man_filter . " 
                ORDER BY pd.products_name asc ");   
              }
              while ( $row = tep_db_fetch_array($result2) ) {
                $number = $row["products_id"];
                $result3 = tep_db_query("SELECT * FROM " . TABLE_SPECIALS . " where products_id=$number");
                $num_rows = tep_db_num_rows($result3);
                if ($num_rows == 0) {
                  $specialprice = "none";
                  $implieddiscount = '';
                } else {
                  while ( $row2 = tep_db_fetch_array($result3) ) {
                    $specialprice = $row2["specials_new_products_price"];
                    if ($row["products_price"] > 0) {
                      $implieddiscount = '-'.(int)(100-(round(($specialprice / $row["products_price"])*100))).'%';
                    } else {
                      $implieddiscount = '';
                    }
                  }
                }

                $status_query = tep_db_query("SELECT * FROM " . TABLE_SPECIALS . " where products_id=$number");
                $status_data = tep_db_fetch_array($status_query);
                $status = $status_data['status'];
                $specials_id = $status_data['specials_id'];
                $expires_date = $status_data['expires_date'];


                if ($status == '1') {
                  $product_status = tep_image(DIR_WS_IMAGES . 'accept.png', IMAGE_ICON_STATUS_GREEN, 16, 16) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALSBYCAT, 'action=setflag&flag=0&manufacturer='.$manufacturer.'&categories='.$categories.'&id=' . $specials_id, 'NONSSL') . '">' . tep_image(DIR_WS_IMAGES . 'cancel-off.png', IMAGE_ICON_STATUS_RED_LIGHT, 16, 16) . '</a>';
                } else {
                  $product_status = '<a href="' . tep_href_link(FILENAME_SPECIALSBYCAT, 'action=setflag&flag=1&manufacturer='.$manufacturer.'&categories='.$categories.'&id=' . $specials_id, 'NONSSL') . '">' . tep_image(DIR_WS_IMAGES . 'accept-off.png', IMAGE_ICON_STATUS_GREEN_LIGHT, 16, 16) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'cancel.png', IMAGE_ICON_STATUS_RED, 16, 16);
                }

                $tax_rate = tep_get_tax_rate($row['products_tax_class_id']);
                print("<form action=\"$current_page\" method=\"get\">");
                print("
              <tr class=\"dataTableRow\" onmouseover=\"rowOverEffect(this)\" onmouseout=\"rowOutEffect(this)\" >
                <td class=\"dataTableContent\">" . $row["products_name"] . "</td>
                <td class=\"dataTableContent\">" . $row["products_price"] . "</td>
                <td class=\"dataTableContent\"><input name=\"inputspecialprice\" type=\"text\" value=\"$specialprice\"></td>
                <td class=\"dataTableContent\">$implieddiscount </td>
                <!-- start -->
                <td class=\"dataTableContent\" align ='center'>". $product_status ."</td>
                <td class=\"dataTableContent\" align ='center'>". tep_date_short($expires_date) ." </td>
                <!-- end -->  
                <td class=\"dataTableContent\">
                  <input type=\"hidden\" name=\"categories\" value=\"" . $categories ."\">
                  <input type=\"hidden\" name=\"productprice\" value=\"" . $row["products_price"] . "\">
                  <input type=\"hidden\" name=\"taxrate\" value=\"" . $tax_rate . "\">    
                  <input type=\"hidden\" name=\"productid\" value=\"" . $row["products_id"] . "\">
                  <input type=\"hidden\" name=\"inputupdate\" value=\"yes\">
                  <input type=\"submit\" value=\"" . TEXT_BUTTON_UPDATE . "\">
                </td>
              </tr>
              </form>");
              }
            ?>
          </table>
        </td>
      </tr>
    </table>
    <!-- body_text_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>