<?php
/*
  $Id: admin/includes/runtime/index/incentibox_index_rightcolumn.php,v 1.0.0.0 2011/07/11 awaage Exp $

  CRE Loaded, Open Source E-Commerce Solutions
  http://www.creloaded.com

  Copyright (c) 2008 CRE Loaded
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
if ((!defined(INCENTIBOX_PROGRAM_ID) && MODULE_ADDONS_INCENTIBOX_STATUS != 'True') || (INCENTIBOX_PROGRAM_ID == '' && MODULE_ADDONS_INCENTIBOX_STATUS == 'True')) {
?>
<script type="text/javascript">
<!--
  function toggle_visibility(incentibox) {
    var ib = document.getElementById(incentibox);
    if (ib.style.display == 'block')
      ib.style.display = 'none';
    else
      ib.style.display = 'block';
  }
//-->
</script>
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 1em;">
	<tr>
		<td class="box-top-left">&nbsp;</td>
		<td class="box-top">&nbsp;</td>
		<td class="box-top-right">&nbsp;</td>              
	</tr>
 <tr>
		<td class="box-left">&nbsp;</td><td class="box-content">
			<img src="../images/incentiBox_logo.png" style='width:95px; height:20px; display:block;margin:0 auto 10px auto;' />
			<div style='width:100%; text-align:center; font-weight:bold'>Social Media Sharing Rewards Module</div> 
			<p align="center"><a href="#" onclick="toggle_visibility('incentibox');">more info...</a></p>
   <div id="incentibox" style="display:none;">
     <ul style='padding:0px;margin:5px 0px 0px 6px;list-style-type:disc; font-size:10px'>
				  <li>Transform ordinary customers into word-of-mouth brand advocates</li>
				  <li>Incentivize customers to share your products on their social networks (Facebook wall posts, Tweets, blog posts, etc.)</li>
				  <li>Generate Facebook likes and increase brand awareness</li>
			  </ul>
			  <div style='margin-top:6px;'>In return, customers receive store credit, <strong>redeemable only at your store</strong>!</div><br />
   </div>
   <div style='width:100%; text-align:center; font-weight:bold'>
     <?php if (!defined(MODULE_ADDONS_INCENTIBOX_STATUS) || MODULE_ADDONS_INCENTIBOX_STATUS != 'True') { ?>
     <a href='modules.php?set=addons&module=incentibox&action=install'>Enable This Module</a>
     <?php } else { ?>
     <a href='modules.php?set=addons&module=incentibox&action=edit'>Enter Module Details</a>
     <?php } ?>
   </div>
			</td>
			<td class="box-right">&nbsp;</td>
	</tr>
	<tr>
		<td class="box-bottom-left">&nbsp;</td>
		<td class="box-bottom">&nbsp;</td>
		<td class="box-bottom-right">&nbsp;</td>
	</tr>
</table>
<?php
}
?>