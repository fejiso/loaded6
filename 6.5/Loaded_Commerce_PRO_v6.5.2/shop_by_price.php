<?php
/*
  $Id: shop_by_price.php,v 1.1.1.1 2004/03/04 23:38:03 ccwjr Exp $
  
  Contribution edit by David van der Wel
  http//www.xlwel.nl
  
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_SHOP_BY_PRICE);
  
  $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                       'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                       'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                       'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                       'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                       'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                       'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
                       'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW);

  asort($define_list);

  $column_list = array();
  reset($define_list);
  while (list($key, $value) = each($define_list)) {
    if ($value > 0) $column_list[] = $key;
  }

  $select_column_list = '';
  $need_manufacturer = false;
  for ($i = 0, $n = sizeof($column_list); $i < $n; ++$i) {
    switch ($column_list[$i]) {
      case 'PRODUCT_LIST_MODEL':
        $select_column_list .= 'p.products_model, ';
        break;
      case 'PRODUCT_LIST_NAME':
        $select_column_list .= 'pd.products_name, ';
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $select_column_list .= 'm.manufacturers_name, p.manufacturers_id, ';
        $need_manufacturer = true;
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $select_column_list .= 'p.products_quantity, ';
        break;
      case 'PRODUCT_LIST_IMAGE':
        $select_column_list .= 'p.products_image, ';
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $select_column_list .= 'p.products_weight, ';
        break;
      case 'PRODUCT_LIST_PRICE':
        // no matter if slected, it is needed for the shop by price
        break;
    }
  }
   
  // start building the sql
  $listing_sql = "SELECT DISTINCT " . $select_column_list . "  
                          p.products_id,
                          p.products_image,
                          p.products_price,
                          p.products_tax_class_id,
                          IF(s.status, LEAST(s.specials_new_products_price, p.products_price), p.products_price) as final_price ";
  // add the tables to be selected from
  if ($need_manufacturer === true) {
    $listing_sql .= "  FROM (" . TABLE_PRODUCTS . " p 
                       LEFT JOIN " . TABLE_SPECIALS . " s using(products_id) )
                       LEFT JOIN " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id,
                         " . TABLE_PRODUCTS_DESCRIPTION . " pd ";
  } else {
    $listing_sql .= "  FROM (" . TABLE_PRODUCTS . " p 
                       LEFT JOIN " . TABLE_SPECIALS . " s using(products_id) ),
                        " . TABLE_PRODUCTS_DESCRIPTION . " pd ";
  }
  // now add the where conditions
  $listing_sql .= "  WHERE p.products_status = '1'
                       AND pd.products_id = p.products_id
                       AND pd.language_id = '" . (int)$languages_id . "' "; 
  // add the selection of the price rabge selected
  if (MODULE_SHOPBYPRICE_RANGES > 0  && (int)$_GET['range'] <= MODULE_SHOPBYPRICE_RANGES) {
    $sbp_array = unserialize(MODULE_SHOPBYPRICE_RANGE);
    $i = (int)$_GET['range'];
    if ((is_numeric($sbp_array[$i])) || (is_numeric($sbp_array[$i-1]))){
      if ($i == MODULE_SHOPBYPRICE_RANGES) {
        $listing_sql .= "  HAVING final_price >= " . $sbp_array[$i-1] . " ";
      } elseif ($i == 0) {
        $listing_sql .= "  HAVING final_price < " . $sbp_array[$i] . " ";
      } else {
        $listing_sql .= "  HAVING (final_price >= " . $sbp_array[$i-1] . " AND final_price < " . $sbp_array[$i] . ") ";
      }
    }
  }
  // and finially add the order by as needed
  if ( (!isset($_GET['sort'])) || (!preg_match('/[1-8][ad]/', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > sizeof($column_list)) ) {
    // before the CATEGORIES_SORT_ORDER can be used, we must check to see if the column was selected
      $sort_column = 'PRODUCT_LIST_NAME';
      for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
        if ($column_list[$i] == CATEGORIES_SORT_ORDER) {
          $sort_column = CATEGORIES_SORT_ORDER;
          break;
        }
      }
      $sort_order = 'a';
  } else {
    $sort_col = substr($_GET['sort'], 0 , 1);
    $sort_column = $column_list[$sort_col-1];
    $sort_order = substr($_GET['sort'], 1);
  }
  // check to see if it is one of the columns being allowed for
  switch ($sort_column) {
    case 'PRODUCT_LIST_MODEL':
      $listing_sql .= " order by p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
       break;
    case 'PRODUCT_LIST_NAME':
      $listing_sql .= " order by pd.products_name " . ($sort_order == 'd' ? 'desc' : '');
      break;
    case 'PRODUCT_LIST_MANUFACTURER':
      $listing_sql .= " order by m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
      break;
    case 'PRODUCT_LIST_QUANTITY':
      $listing_sql .= " order by p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
      break;
    case 'PRODUCT_LIST_IMAGE':
      // sorting by image name makes no sense, so just ignore it
      break;
    case 'PRODUCT_LIST_WEIGHT':
      $listing_sql .= " order by p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
      break;
    case 'PRODUCT_LIST_PRICE':
    default:
      $listing_sql .= " order by final_price " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
      break;
  }
  
  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_SHOP_BY_PRICE, '', 'NONSSL'));

  $content = CONTENT_SHOP_BY_PRICE;

  require(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/' . TEMPLATENAME_MAIN_PAGE);

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
