<?php
/*
  $Id: validcategories.php,v 0.01 2002/08/17 15:38:34 Richard Fielder

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com



  Copyright (c) 2002 Richard Fielder

  Released under the GNU General Public License
*/

require('includes/application_top.php');


?>
<html>
<head>
<title><!-- Valid Categories/Products List --><?php echo VALID_CATEGORIES_PRODUCTS_LIST;?></title>
<script type="text/javascript" src="<?php echo (($request_type == 'SSL') ? 'https:' : 'http:'); ?>//ajax.googleapis.com/ajax/libs/jquery/<?php echo JQUERY_VERSION; ?>/jquery.min.js"></script>
<script type="text/javascript">
  if (typeof jQuery == 'undefined') {
    //alert('You are running a local copy of jQuery!');
    document.write(unescape("%3Cscript src='includes/javascript/jquery-1.6.2.min.js' type='text/javascript'%3E%3C/script%3E"));
  }
</script> 
<style type="text/css">
<!--
h4 {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: x-small; text-align: center}
p {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: xx-small}
th {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: xx-small}
td {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: xx-small}
-->
</style>
<head>
<body>
<table width="550" border="1" cellspacing="1" bordercolor="gray">
<tr>
<td colspan="4">
<h4><!-- Valid Categories List --><?php echo VALID_CATEGORIES_LIST;?></h4>
</td>
</tr>
<?php 
   $coupon_get=tep_db_query("select restrict_to_categories from " . TABLE_COUPONS . " where coupon_id='".$_GET['cid']."'");
   $get_result=tep_db_fetch_array($coupon_get);
   echo "<tr><th>Category ID</th><th>Category Name</th></tr><tr>";
   $cat_ids = preg_split("/[,]/", $get_result['restrict_to_categories']);
   for ($i = 0; $i < count($cat_ids); $i++) {
     $result = tep_db_query("SELECT * FROM categories, categories_description WHERE categories.categories_id = categories_description.categories_id and categories_description.language_id = '" . $languages_id . "' and categories.categories_id='" . $cat_ids[$i] . "'");
     if ($row = tep_db_fetch_array($result)) {
       echo "<td>".$row["categories_id"]."</td>\n";
       echo "<td>".$row["categories_name"]."</td>\n";
       echo "</tr>\n";
     }
   }
    echo "</table>\n";
?>
<br>
<table width="550" border="0" cellspacing="1">
<tr>
<td align=middle><input type="button" value="Close Window" onClick="window.close()"></td>
</tr></table>
</body>
</html>
