<?php include('../config.php'); 
include('inc.session-create.php');
$PageTitle="Manage Parent Accounts";
$FileName = 'manage_parents_accounts.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
	$stat['success']=$_SESSION['success'];
	unset($_SESSION['success']);
}

if(isset($_POST['update']))
{                
	
	$validate->addRule($_POST['password'],'','password',true);
	$validate->addRule($_POST['name'],'alpha','Name',true);
	$validate->addRule($_POST['contact_no'],'num','Phone',true);
	$validate->addRule($_POST['email'],'','Email',true);
	$validate->addRule($_POST['location'],'','Address',true);
					
									
	if($validate->validate() && count($stat)==0)
	{
		
			$aryData=array(	
							'password'					=>	$_POST['password'],
							'name'				        =>	$_POST['name'],
							'contact_no'				=>	$_POST['contact_no'],
							'email'				    	=>	$_POST['email'],
							'location'			        =>	$_POST['location'],			
							);  
				$flgIn = $db->updateAry("school_register",$aryData,"where randomid='".$_GET['randomid']."' and usertype=2");
						
				$stat['success']="Updated Successfully";					
				unset($_POST);
				redirect($FileName.'?action=edit_parent_data&randomid='.$_GET['randomid']);
		
	}
	else    
	{
		$stat['error'] = $validate->errors();
	}
}
if($_GET["action"]=="delete")
{
	$flgIn = $db->delete("school_register","where randomid='".$_GET['id']."' and usertype=2");
	redirect($FileName.'?action=edit_parent_data&randomid='.$_GET['id']);}
?>
<!DOCTYPE html>
<html>
<head>
<?php include('inc.meta.php'); ?>
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Droid+Serif" />
<style>

body, label, span, a, .gwt-Button {
	
	 font-family: 'Droid Serif' !important; 
}
.page-title {
	font-size: 20px;
	margin-bottom: 0;
	margin-top: 7px;
	text-align: center;
	background: white;
	padding: 23px 0 30px 0px;
	border-bottom: 5px solid gainsboro;
}
.zasw {
	border: 1px solid gainsboro;
	height: 1000px;
	margin-top: 18px;
}
.zasw1 {
	height: 1000px;
	margin-top: 18px;
}
.sectionza {
	background: white;
	height: 1000px;
}
.top-serche input {
	padding: 5px 49px 5px 14px;
	border: 2px solid gainsboro;
	border-radius: 4px;
	position: relative;
}
.top-serche {
	padding: 32px 0 9px 30px;
}
.content-page>.content {
	margin-bottom: 60px;
	margin-top: 60px;
	padding: 20px 30px 15px 78px;
	background: white;
}
.zswqas ul {
	list-style: none;
}
.zswqas li a span i {
	font-size: 29px;
	padding-top: 9px;
}
.zswqas li a span {
	padding-right: 16px;
}
.zswqas li a {
	width: 239px;
	display: block;
	padding: 16px 14px 14px 18px;
	border-bottom: 2px solid gainsboro;
}
.topside-section ul {
	display: inline-flex;
	list-style: none;
}
.topside-section li {
	padding: 0 11px 0 0;
}
.topside-section {
	padding-top: 8px;
	border: 1px solid gainsboro;
	box-shadow: 1px 6px 4px gainsboro;
	padding: 14px 8px 11px 1px;
}              
.zqw22 .panel-success>.panel-heading {
    background: white;
}
.zqw22 .nav.nav-tabs>li>a:hover, .nav.tabs-vertical>li>a:hover {
    color:black!important;
	font-weight: 700;
}
.zqw22 .nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
    border-top-right-radius: 10px;
    border-top-left-radius: 10px;
    font-size: 10px;
    height: 38px;
    margin-top: 0;
}
div.dataTables_filter label {
    font-weight: 400;
    white-space: nowrap;
    text-align: left;
    border: 1px solid gainsboro;
    padding: 4px 13px 4px 0px;
    border-radius: 5px;
    color: black;
}
#example .active {
    background: #1B3058;
    color: white;
}
.zqw22 .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover, .tabs-vertical>li.active>a, .tabs-vertical>li.active>a:focus, .tabs-vertical>li.active>a:hover {
    color: black!important;
    font-weight: 700;
	line-height: 38px;
    background: gainsboro;
}
.zqw22 .panel-success>.panel-heading {
    background: white;
    padding: 0;
}
.zqw22 .panel .panel-body {
    border-right: none!important;
    border: 1px solid gainsboro;
}
.gwt-Label {
	color:black;
	font-weight:600;
    padding: 8px;
}
.zqw22  input {
    padding: 8px 3px 10px 0;
    border: 1px solid gainsboro;
    background: #dcdcdc45;
    border-radius: 5px;
    margin-right: 8px;
	margin-bottom: 5px;
}
.zqw22 button {
    border: 1px solid #1B3058;
    padding: 4px 3px 4px 3px;
    margin-right: 7px;
    background: transparent;
    color: #1B3058;
}
.zqw22 select {
    padding: 5px 0 8px 0;
    background: #dcdcdc2e;
}
.zqw22 .nav-tabs>li {
    padding: 0 4px 0 0;
}
#tab3success ,#tab4success .middleCenterInner{
    border: 1px solid gainsboro;
    padding: 17px 11px 51px 19px;
}
#tab3success  .middleCenterInner{
    border: 1px solid gainsboro;
    padding: 17px 11px 51px 19px;
}
#tab3success ,#tab4success .BFOGCKB-c-h{
    border-bottom: 3px solid;
    width: 300px;
}
#tab3success  .BFOGCKB-c-h{
    border-bottom: 3px solid;
    width: 300px;
}
#tab3success ,#tab4success  {
    border: 1px solid gainsboro;
    padding: 14px 4px 42px 11px;
    width: 361px;
}
#tab3success ,#tab4success .gwt-DecoratorPanel {
   
    padding: 21px 21px 43px 4px;
}
#tab3success .gwt-DecoratorPanel {
  
    padding: 21px 21px 43px 4px;
}
.zqw22 .panel .panel-body {

    border-bottom: 3px solid gainsboro!important;
}
.zqw22 .nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
    
    background: #dcdcdc4f!important;
}
.zqw22 .nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
    color: black!important;
    font-weight: 700;
	line-height: 38px;
    background: gainsboro;
}
.xza{margin: 0;
    width: 294px;
    border-bottom: 1px solid;
}
.dataTables_paginate a {
    background-color: transparent;
    margin: 0 0px 0;
    padding: 8px 15px 9px;
    color: white;
    cursor: pointer;
    border: none;
}
.zqw22 .nav-tabs>li.active, .nav-tabs>li.active:focus, .nav-tabs>li.active:hover, .tabs-vertical>li.active, .tabs-vertical>li.active:focus, .tabs-vertical>li.active:hover {
    color: black!important;
    font-weight: 700;
}
.zswqas .activate a {
    width: 239px;
    display: block;
    padding: 16px 14px 14px 18px;
    border-bottom: 2px solid gainsboro;
    background: #1B3058;
    color: white;
}
.zqw22 .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover, .tabs-vertical>li.active>a, .tabs-vertical>li.active>a:focus, .tabs-vertical>li.active>a:hover {
	border-bottom: 3px solid #1B3058;
}
.topside-section li a {
	border: 1px solid #1B3058;
	padding: 5px 5px 4px 5px;
	display: block;
}
.zswqas li a:hover {
	width: 239px;
	display: block;
	padding: 16px 14px 14px 18px;
	border-bottom: 2px solid gainsboro;
	background: #1B3058;
	color: white;
}
.zswqas .active {
	width: 239px;
	display: block;
	padding: 16px 14px 14px 18px;
	border-bottom: 2px solid gainsboro;
	background: #1B3058;
	color: white;
}
.Wizard-a1 #example_length  {
	display:none;
}		
div.dataTables_filter label {
    font-weight: 400;
    white-space: nowrap;
    text-align: left;
}
div.dataTables_filter input {
    margin-left: .5em;
    display: inline-block;
    float: right;
    border: none;
}
div.dataTables_filter label {
    padding: 10px;
}
div.dataTables_filter input {
    margin-left: .5em;
    display: inline-block;
    float: right;
}
div.dataTables_filter {
    text-align: center;
}		
.Wizard-a1 .zwq img {
    width: 50px;
}			
.Wizard-a1 .zwq {
    padding-right: 8px;
}
.Wizard-a1 .setting {
    display: none;
}			
.Wizard-a1 .dataTables_info {
    margin: 0 auto !important;
    text-align: center;
    font-size: 12px;
    float: initial;
    position: absolute;
    bottom: 11px;
    left: 0;
    right: 0;
}			
#example {
    width: 85%!important;
    margin:0 auto;
}	

div.dataTables_filter input {
    width: 77%;
}	

div.dataTables_filter label {
    line-height: 23px;
}
.dataTables_paginate #example_previous:before{
	content: "";
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-right: 12px solid #555;
    border-bottom: 6px solid transparent;
    position: absolute;
    z-index: 999999;
    left: 15px;
    bottom: 3px;
}
.Wizard-a1 .dataTables_info {
    position: sticky!important;
}
div.dataTables_paginate {
    position: relative;
    top: -47px;
}
.dataTables_paginate a {
    background-color: transparent;
    margin: 0 0px 0;
    padding: 8px 15px 9px;
    color: white;
    cursor: pointer;
    border: none;
	position: static;
}
.dataTables_paginate .next {
    background: none;
    border: navajowhite;
    position: relative;
    color: white!important;
    position: relative;
  
}
div.dataTables_paginate {
    margin: 0;
    white-space: nowrap;
    text-align: center!important;
    padding-top: 27px;
}
.dataTables_paginate .disabled {
	background: none;
    color: white;
    border: none!important;
    padding: unset;
    display: block;
    width: 90%;
    color: transparent !important;
    margin: 0 auto;
}
div.dataTables_info {
    white-space: nowrap;
	padding-top: 0px;
}
.dataTables_paginate #example_next:before {
    content: "";
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-left: 12px solid #555;
    border-bottom: 6px solid transparent;
    position: absolute;
    z-index: 999999;
    right: 0;
    bottom: 9px;
    top: 4px;
}
div.dataTables_paginate {
    margin: 0;
    white-space: nowrap;
    text-align: center!important;
}
 .paging_simple_numbers span{
    /* display: none; */
    opacity: 0;
}
#example td {
    padding: 15px 11px 18px 13px;
    border-bottom: 3px solid;
    margin: 0 0 0;
}
#example .active:hover {
    background: #1B3058;
    color: white;
}
.Wizard-a1	.sorting_1 {
    display: none;
}	
.dataTables_filter label:before {
    position: absolute;
    /* left: 0; */
    right: 46px;
    top: 62px!important;
    /* bottom: 0; */
    border: 1px solid #1B3058;
}
.dataTables_filter:before{
	content:'';
	position:absolute;
}
div.dataTables_filter label {
	position: relative;
    width: 85%;
    text-align: left;
}
div.dataTables_filter {
    margin-top: 20px;
}
.sectsab li {	
	list-style:none;
}
div.dataTables_paginate {
    margin: 0 auto;
}
#hover:hover { 
	background-color: #1B3058;
	color:#fff;
}
</style>
</head>
<body class="fixed-left">
<div id="wrapper">
	<?php include('inc.header.php'); ?>
	<?php include('inc.sideleft.php'); ?>
<div class="content-page">
<!-- Start content -->
<div class="content">
<div class="container">
	<!-- Page-Title -->
	<div class="row">
		<div class="col-sm-12">
			<h4 class="page-title"><?php echo $PageTitle; ?></h4>
		</div>
	</div>
	<!-- Basic Form Wizard -->
	<div class="row">
		<div class="sectionza">
		<div class="col-md-12">
			<div class="col-md-4">
				<div class="zasw ">
					<div class="top-serche">
						<span class="zaq"><a href=""></a></span>
					</div>
					<div class="zawq Wizard-a1">
					<table id="example" class="display" style="width:100%">
					<thead class="setting">
						<tr>
							<th>Position</th>
							<th>Position</th>
						</tr>
					</thead>
					<tbody>
					<?php 
						$manageParent=$db->getRows("select * from school_register where create_by_userid='".$create_by_userid."' and usertype=2"); 
						foreach($manageParent as $iList)  
						{ 
						?>
						<tr>
							<td></td>
							<td class="sectsab <?php if($_GET['randomid']==$iList['randomid']) { echo "active"; } ?>">
								<ul>
									<a href="<?php echo $FileName;?>?action=edit_parent_data&randomid=<?php echo $iList['randomid']; ?>">
									<li>
										<span class="zwq"> 
											<img class="table-img" src="https://i0.wp.com/www.winhelponline.com/blog/wp-content/uploads/2017/12/user.png?fit=256%2C256&quality=100&ssl=1">
										</span>
										<span style="color:#000">
											<?php echo $iList['name']." (".$iList['username'].")"; ?> 
										</span>
									</li>
									</a>
								</ul>
							</td>
						</tr>
						<?php } ?>
					</tbody>
					<tfoot>
						<tr class="setting">
							<th>Name</th>
							<th>Position</th>
						</tr>
					</tfoot>
					</table>
					</div>
				</div>
			</div>
			<div class="col-md-8">
				<div class="zasw1">
					<?php echo msg($stat);?>
					<div class="topside-section">
						<ul>
							<li>
								<!--<a id="hover" href="<?php echo $FileName;?>?action=delete&id=<?php echo $iParents['randomid'];?>">Delete Account</a>-->
							</li>
						</ul>
					</div>
<div class="zqw22">
<div class="panel with-nav-tabs panel-success">
<div class="panel-body">
	<div class="tab-content">
	
	<?php if($_GET['action']=='edit_parent_data') { 
	
	$iParents=$db->getRow("select * from school_register where randomid='".$_GET['randomid']."' and usertype=2");
	?>
		<div class="tab-pane fade in active" id="tab1success">
		<div class="gwt-TabPanelBottom" role="tabpanel">
			<div style="width: 100%; height: 100%; padding: 0px; margin: 0px;">
				<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel" style="width: 100%; height: 100%;">
				<tbody>
					<tr class="top">
						<td class="topLeft">
							<div class="topLeftInner"></div>
						</td>
						<td class="topCenter">
							<div class="topCenterInner"></div>
						</td>
						<td class="topRight">
							<div class="topRightInner"></div>
						</td>
					</tr>
					<tr class="middle">
						<td class="middleLeft">
							<div class="middleLeftInner"></div>
						</td>
						<td class="middleCenter">
							<div class="middleCenterInner">
							<form action="" method="post" enctype="multipart/form-data">
							<table cellspacing="0" cellpadding="0" style="width: 100%;">
							<tbody>
								<tr>
									<td align="center" style="vertical-align: top;">
									<table cellspacing="0" class="contacts-ListContainer" cellpadding="4" style="width: 100%;">
									<colgroup><col><col class="add-contact-input"></colgroup>
									<tbody>
									
										<tr>
											<td><div class="gwt-Label">UserName: *</div></td>
											<td>
												<input type="text" name="" value="<?php echo $iParents['username']; ?>" class="gwt-TextBox" readonly diabled >
											</td>
											<td><div class="gwt-Label">Password: *</div></td>
											<td>
												<input type="text" name="password" value="<?php echo $iParents['password']; ?>" class="gwt-TextBox">
											</td>
										</tr>
										
										<tr>
											<td><div class="gwt-Label" name="last_name"> Name: *</div></td>
											<td>
												<input type="text" class="gwt-TextBox"  name="name" value="<?php echo $iParents['name']; ?>">
											</td>
											<td><div class="gwt-Label" name="last_name">Phone: *</div></td>
											<td>
												<input type="text" class="gwt-TextBox" name="contact_no" value="<?php echo $iParents['contact_no']; ?>">
											</td>
										</tr>
										<tr>
											
											<td><div class="gwt-Label" name="last_name">Email: *</div></td>
											<td>
												<input type="text" class="gwt-TextBox"  name="email" value="<?php echo $iParents['email']; ?>">
											</td>
											<td><div class="gwt-Label">Home Address :</div></td>
											
											<td>
												<input type="text" class="gwt-TextBox" name="location" value="<?php echo $iParents['location']; ?>">
											</td>
										</tr>
										
										<tr>
											<td><div class="gwt-HTML"><hr style="border: 1px dashed #CCC;"></div></td>
											<td><div class="gwt-HTML"><hr style="border: 1px dashed #CCC;"></div></td>
											<td><div class="gwt-HTML"><hr style="border: 1px dashed #CCC;"></div></td>
											<td><div class="gwt-HTML"><hr style="border: 1px dashed #CCC;"></div></td>
										</tr>
										
									</tbody>
									</table>
									</td>
								</tr>
								<tr><td>&nbsp;</td></tr>
								<tr>
									<td align="center" style="vertical-align: top;">
										<table cellspacing="0" cellpadding="0" style="height: 30px;">
											<tbody>
												<tr>
													<td align="left" style="vertical-align: bottom;">
														<button type="submit" name="update" id="hover" class="gwt-Button">Update Details</button>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
							</table>
							</form>
							</div>
						</td>
						<td class="middleRight"><div class="middleRightInner"></div></td>
					</tr>
					<tr class="bottom">
						<td class="bottomLeft"><div class="bottomLeftInner"></div></td>
						<td class="bottomCenter"><div class="bottomCenterInner"></div></td>
						<td class="bottomRight"><div class="bottomRightInner"></div></td>
					</tr>
				</tbody>
				</table>
			</div>
		</div>
		</div>
	<?php } ?>	

	</div>
</div>
</div>
</div>

									
				</div>
			</div>
		</div>
		</div>
	</div>
</div>
</div>
</div>
<?php include('inc.footer.php'); ?>
</div>
<?php include('inc.js.php'); ?>
<script>
(function() {
$(function() {
var toggle;
return toggle = new Toggle('.zswqas');
});

this.Toggle = (function() {
class Toggle {
constructor(toggleClass) {
this.el = $(toggleClass);
this.tabs = this.el.find(".xz");
this.panels = this.el.find(".panel");
this.bind();
}

show(index) {
var activePanel, activeTab;
//update tabs
this.tabs.removeClass('activate');
activeTab = this.tabs.get(index);
$(activeTab).addClass('activate');
//update panels
this.panels.hide();
activePanel = this.panels.get(index);
return $(activePanel).show();
}

bind() {
return this.tabs.unbind('click').bind('click', (e) => {
return this.show($(e.currentTarget).index());
});
}

};

Toggle.prototype.el = null;

Toggle.prototype.tabs = null;

Toggle.prototype.panels = null;

return Toggle;

}).call(this);

}).call(this);
</script>
<script>
// Add active class to the current button (highlight it)
var header = document.getElementById("example");
var btns = header.getElementsByClassName("sectsab");
for (var i = 0; i < btns.length; i++) {
btns[i].addEventListener("click", function() {
var current = document.getElementsByClassName("active");
current[0].className = current[0].className.replace(" active", "");
this.className += " active";
});
}
</script>
<script>		
$(document).ready(function() {
	$('#example').DataTable();
});
</script>	
</body>
</html>