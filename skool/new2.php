<?php include('../config.php'); 
//include('inc.session-create.php'); 
$PageTitle="Staff";
$FileName = 'staff_old.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
$stat['success']=$_SESSION['success'];
unset($_SESSION['success']);
}

	if(isset($_POST['submit']))
{                
				$validate->addRule($_POST['staff_id'],'','staff_id',true);
				$validate->addRule($_POST['gender'],'',' gender',true);
				$validate->addRule($_POST['last_name'],'','last_name',true);
				$validate->addRule($_POST['date_of_appointment'],'','date_of_appointment ',true);
				$validate->addRule($_POST['first_name'],'','first_name',true);
				$validate->addRule($_POST['state_of_origin'],'','state_of_origin',true);
				$validate->addRule($_POST['lga_of_origin'],'','lga_of_origin',true);
				$validate->addRule($_POST['religion'],'',' religion',true);
				$validate->addRule($_POST['nationality'],'','nationality',true);
									
			    if($validate->validate() && count($stat)==0)
				  {
				  	  
				$randomId=randomFix(15);
					$aryData=array(	
					'usertype'                               =>2,
				'staff_id'                                   => $_POST['staff_id'],
				'gender'                              => $_POST['gender'],
				'title'                                    => $_POST['title'],
				'date_of_birth'  			                      => $_POST['date_of_birth'],
				'last_name'                                    => $_POST['last_name'],	
				 'first_name'                                   => $_POST['first_name'],
				'date_of_appointment'                            => $_POST['date_of_appointment'], 
			    'state_of_origin'                              => $_POST['state_of_origin'],
				'other_name'                                         =>  $_POST['other_name'],
				'lga_of_origin'                                 => $_POST['lga_of_origin'],
				'marrital_status'  			                          => $_POST['marrital_status'],		
				'religion'  			                        => $_POST['religion'],	
				'nationality'  			                         => $_POST['nationality'],	   
				'denomination'  			                  =>  $_POST['denomination'],	
				'no_of_children'                                    => $_POST['no_of_children'],
				'branch'  			                           => $_POST['branch'],	
				'blood_group'  			                           => $_POST['blood_group'],
                 'genotype'  			                           => $_POST['genotype'],				
				'address_1'                                      => $_POST['address_1'],
				'address_2'  			                        => $_POST['address_2'],		
			     'state'  			                             => $_POST['state'],
                   'city'                                           => $_POST['city'],				
				'p_o_box'                                         => $_POST['p_o_box'],
				'email'  			                           => $_POST['email'],	
				'phone'                                         => $_POST['phone'],
				'mobile'  			                            => $_POST['mobile'],	
				'randomid'  			                          => $randomId,	 
				
					            );  
					$flgIn1 = $db->insertAry("staff_manage",$aryData);
					redirect($FileName.'?action=add_next_of_kin_details&randomid='.$randomId);
                    $stat['success']="Submited Successfully";					
					unset($_POST);
				}


		else    
		        {
					$stat['error'] = $validate->errors();
				}
			}
			
			
	if($_SESSION['success']!="")
{
$stat['success']=$_SESSION['success'];
unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html>

<head>
<?php include('inc.meta.php'); ?>
         <style>
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
                
                .sectionza {    overflow-x: scroll;
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
    font-size: 7px;
    height: 38px;
    margin-top: 0;
}

.zqw22 .nav.nav-tabs>li>a, .nav.tabs-vertical>li>a{
	padding-left:0px!important;
	padding-right:0px!important;
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


.beddy{
	    padding-top: 13px;
}

.bedd{
    font-size: 17px;
    color: black!important;	
}


.beddy img{    margin-top: 13px;
	    width: 100%;
    border: 2px solid black !important;
}
.beddy-a{
    text-align: center;	
}
.beddy-a button{
    border: transparent;
    background: transparent;
    text-align: center;
    color: #1B3058;
}
.beddy-b{
	    padding-top: 27px;
}
.beddy-b input{
	    width: 56%;
    height: 81px;
    margin-top: 8px;
}

.beddy-c{
     padding-left: 8px;	
}
.beddy-c button{
    border: transparent;
    background: transparent;
   
    color: #1B3058;
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
<div class="zasw">
<div class="zawq Wizard-a1">

<table id="example" class="display" >
<thead class="setting">
<tr>
<th>Position</th>
<th>Position</th>

</tr>
</thead>

<tbody>

 <tr>
                <td></td>
                <td class="sectsab active">
				 <ul><li>
				<span class="zwq"> <img class="table-img" src="https://i0.wp.com/www.winhelponline.com/blog/wp-content/uploads/2017/12/user.png?fit=256%2C256&quality=100&ssl=1"></span>System Architect</li></ul></td>
                
            </tr>
			
			
			 <tr>
                <td width="10%">  <ul id="myDIV"></td>
                <td class="sectsab active" width="90%">
				 <ul><li>
				<span class="zwq"> <img class="table-img" src="https://i0.wp.com/www.winhelponline.com/blog/wp-content/uploads/2017/12/user.png?fit=256%2C256&quality=100&ssl=1"></span>System Architect</li></ul></td>
                
            </tr>




</tbody>


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

<div class="col-md-7">
<div class="zasw1">
<div class="topside-section">
<ul>
<li><a href="">delete new staff</a>
</li>
<li><a href="">delete new staff</a>
</li>
<li><a href="">delete new staff</a>
</li>
<li><a href="">delete new staff</a>
</li>

</ul>
</div>
<div class="zqw22">
<div class="panel with-nav-tabs panel-success">
<div class="panel-heading">
<ul class="nav nav-tabs">
<li class="active"><a href="#tab1success" data-toggle="tab">Basic Info</a></li>
<li><a href="#tab2success" data-toggle="tab">Nest of  Kin Details</a></li>
<li><a href="#tab3success" data-toggle="tab">Educational Qualification</a></li>
<li><a href="#tab4success" data-toggle="tab">Previous Employment</a></li>
<li><a href="#tab5success" data-toggle="tab">Refree</a></li>
</ul>
</div>
<div class="panel-body">
<div class="tab-content">
<div class="tab-pane fade in active" id="tab1success">

<div class="gwt-TabPanelBottom" role="tabpanel">
<div style="width: 100%; height: 100%; padding: 0px; margin: 0px;" class="table-responsive">
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
<table cellspacing="0" cellpadding="0" style="width: 100%;">
<tbody>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" class="contacts-ListContainer" cellpadding="4" style="width: 100%;">
<colgroup>
<col>
<col class="add-contact-input">
</colgroup>
<tbody>
<tr>
<td>
<div class="gwt-Label">Staff ID: *</div>
</td>
<td>
<input type="text" name="staff_id" value="<?php echo$_POST['staff_id']; ?>" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Gender: *</div>
</td>
<td>
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;"><span class="gwt-RadioButton"><input type="radio" name="gender" value="<?php echo$_POST['gender']; ?>" id="gwt-uid-1" tabindex="0"><label for="gwt-uid-1">Male</label></span></td>
<td align="left" style="vertical-align: top;"><span class="gwt-RadioButton"><input type="radio" name="gender" value="<?php echo$_POST['gender']; ?>" id="gwt-uid-2" tabindex="0"><label for="gwt-uid-2">Female</label></span></td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Title</div>
</td>
<td>
<select class="gwt-ListBox" name="title">
<option>Select Title</option>
<option value="Mr."<?php if($_POST['title']=='mr.') { echo "selected"; } ?>>Mr.</option>
<option value="Mrs."<?php if($_POST['title']=='Mrs.') { echo "selected"; } ?>>Mrs.</option>
<option value="Miss."<?php if($_POST['title']=='Miss.') { echo "selected"; } ?>>Miss.</option>
<option value="Dr."<?php if($_POST['title']=='Dr.') { echo "selected"; } ?>>Dr.</option>
<option value="Prof."<?php if($_POST['title']=='Prof.') { echo "selected"; } ?>>Prof.</option>
<option value="Alh."<?php if($_POST['title']=='Alh.') { echo "selected"; } ?>>Alh.</option>
<option value="Malam."<?php if($_POST['title']=='Malam.') { echo "selected"; } ?>>Malam.</option>
<option value="Hajia."<?php if($_POST['title']=='Hajia.') { echo "selected"; } ?>>Hajia.</option>
<option value="Pst."<?php if($_POST['title']=='Pst.') { echo "selected"; } ?>>Pst.</option>
<option value="Sen."<?php if($_POST['title']=='Sen.') { echo "selected"; } ?>>Sen.</option>
<option value="Barr."<?php if($_POST['title']=='Barr.') { echo "selected"; } ?>>Barr.</option>


</select>
</td>
<td>
<div class="gwt-Label">Date of Birth:</div>
</td>
<td>
<input type="text" name="date_of_birth" value="<?php echo $_POST['date_of_birth']; ?>" class="gwt-DateBox datepicker">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label" name="last_name">Last Name: *</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="last_name" value="<?php echo $_POST['last_name']; ?>">
</td>
<td>
<div class="gwt-Label">State of Origin:</div>
</td>
<td>
 <select class="gwt-ListBox" name="state_of_origin">
 <option>Select State</option>
<option value="Abia">Abia</option>

</select>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">First Name: *</div>
</td>
<td>
<input type="text" class="gwt-TextBox" name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
<td>
<div class="gwt-Label">LGA of Origin:</div>
</td>
<td>
<select class="gwt-ListBox">
<option>Select Lga</option>
</select>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Other Names:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
<td>
<div class="gwt-Label">Date of Appointment:</div>
</td>
<td>
<input type="text" class="gwt-DateBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Marital Status:</div>
</td>
<td>
<select class="gwt-ListBox">
<option >SELECT STATUS</option>
<option value="SINGLE">SINGLE</option>
<option value="MARRIED">MARRIED</option>
<option value="WIDOWED">WIDOWED</option>
<option value="DIVORCED">DIVORCED</option>
</select>
</td>
<td>
<div class="gwt-Label">Religion:</div>
</td>
<td>
<select class="gwt-ListBox">
<option >SELECT RELIGION</option>
<option value="ISLAM">ISLAM</option>
<option value="CHRISTIAN">CHRISTIAN</option>
<option value="OTHERS">OTHERS</option>
</select>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Nationality:</div>
</td>
<td>
<select class="gwt-ListBox">
<option >SELECT Nationality</option>
<option value="NIGERIAN">NIGERIAN</option>
<option value="NON-NIGERIAN">NON-NIGERIAN</option>
</select>
</td>
<td>
<div class="gwt-Label">Denomination:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">No of children:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
<td>
<div class="gwt-Label">Branch:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">BloodGroup:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>

<td>
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<div class="gwt-Label">Genotype:</div>
</td>
<td align="left" style="vertical-align: top;">
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td>
<div class="gwt-HTML">
<hr style="border: 1px dashed #CCC;">
</div>
</td>
<td>
<div class="gwt-HTML">
<hr style="border: 1px dashed #CCC;">
</div>
</td>
<td>
<div class="gwt-HTML">
<hr style="border: 1px dashed #CCC;">
</div>
</td>
<td>

<div class="gwt-HTML">
<hr style="border: 1px dashed #CCC;">
</div>


</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Address Line 1:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
<td>
<div class="gwt-Label">P.O.Box:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Address Line 2:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
<td>
<div class="gwt-Label">State:</div>
</td>
<td>
<select class="gwt-ListBox">
<option >Select State</option>
<option value="Abia">Abia</option>

</select>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">City:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
<td>
<div class="gwt-Label">Phone:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Initials:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
<td>
<div class="gwt-Label">Email:</div>
</td>
<td>
<input type="text" class="gwt-TextBox"  name="first_name" value="<?php echo $_POST['first_name']; ?>">
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" style="height: 30px;">
<tbody>
<tr>
<td align="left" style="vertical-align: bottom;">
<button type="button" class="gwt-Button">Save Staff Details</button>
</td>
<td align="left" style="vertical-align: bottom;">
<button type="button" class="gwt-Button">Add New Staff</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</td>
<td class="middleRight">
<div class="middleRightInner"></div>
</td>
</tr>
<tr class="bottom">
<td class="bottomLeft">
<div class="bottomLeftInner"></div>
</td>
<td class="bottomCenter">
<div class="bottomCenterInner"></div>
</td>
<td class="bottomRight">
<div class="bottomRightInner"></div>
</td>
</tr>
</tbody>
</table>
</div>
<div style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;" aria-hidden="true">
<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel" style="width: 100%; height: 100%; display: none;" aria-hidden="true">
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
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="center" style="vertical-align: top;">
<table cellpadding="4">
<colgroup>
<col>
</colgroup>
<tbody>
<tr>
<td>
<div class="gwt-Label">First Name:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Address 1:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Last Name:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Address 2:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Other Name:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Relationship:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Phone:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Email:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" style="height: 30px;">
<tbody>
<tr>
<td align="left" style="vertical-align: bottom;">
<button type="button" class="gwt-Button">Save Next of Kin Details</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</td>
<td class="middleRight">
<div class="middleRightInner"></div>
</td>
</tr>
<tr class="bottom">
<td class="bottomLeft">
<div class="bottomLeftInner"></div>
</td>
<td class="bottomCenter">
<div class="bottomCenterInner"></div>
</td>
<td class="bottomRight">
<div class="bottomRightInner"></div>
</td>
</tr>
</tbody>
</table>
</div>
<div style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;" aria-hidden="true">
<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel" style="width: 100%; height: 100%; display: none;" aria-hidden="true">
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
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel">
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
<table class="gridTable" style="width: 475px;border:1px solid gainsboro;">
<colgroup>
<col>
</colgroup>
<tbody>
<tr>
<td class="contacts-ListContainer contacts-ListMenu" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" style="width: 100%;">
<tbody>
<tr>
<td align="left" style="vertical-align: top;padding:7px;">
<table cellspacing="2" cellpadding="0" border="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Add</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Delete</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Update</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 4</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 5</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 6</button>
</td>
<td align="left" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" aria-hidden="true" style="display: none;">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<input type="text" class="gwt-TextBox" style="width: 350px; height: 35px;">
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" style="height: 35px;">Search</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td align="left" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 7</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 8</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 9</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 10</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 11</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 12</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
<td align="right" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">&lt;&lt;</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td>
<table cellspacing="0" cellpadding="0" style="height: 0px;">
<tbody>
<tr>
<td align="right" style="vertical-align: top;">
<input type="text" class="gwt-TextBox" placeholder="Enter some text">
</td>
</tr>
<tr>
<td align="left" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<table __gwtcellbasedwidgetimpldispatchingfocus="true" __gwtcellbasedwidgetimpldispatchingblur="true" class="BFOGCKB-c-y" cellspacing="0">
<colgroup>
<col>
<col>
<col>
</colgroup>
<thead>
<tr __gwt_header_row="0">
<th colspan="1" class="BFOGCKB-c-h BFOGCKB-c-f" __gwt_column="column-gwt-uid-4" __gwt_header="header-gwt-uid-5">Qualification</th>
<th colspan="1" class="BFOGCKB-c-h" __gwt_column="column-gwt-uid-6" __gwt_header="header-gwt-uid-7">Institution</th>
<th colspan="1" class="BFOGCKB-c-h BFOGCKB-c-p" __gwt_column="column-gwt-uid-8" __gwt_header="header-gwt-uid-9">Date Issued</th>
</tr>
</thead>
<tbody style="display: none;"></tbody>
<tbody>
<tr>
<td align="center" colspan="3">
<div>
<div style="width: 100%; height: 100%; padding: 0px; margin: 0px;">
<div style="width: 100%; height: 100%;"></div>
</div>
<div aria-hidden="true" style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;">
<div aria-hidden="true" class="BFOGCKB-c-q" style="width: 100%; height: 100%; display: none;"><img src="data:image/gif;base64,R0lGODlhKwALAPEAAP///0tKSqampktKSiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAKwALAAACMoSOCMuW2diD88UKG95W88uF4DaGWFmhZid93pq+pwxnLUnXh8ou+sSz+T64oCAyTBUAACH5BAkKAAAALAAAAAArAAsAAAI9xI4IyyAPYWOxmoTHrHzzmGHe94xkmJifyqFKQ0pwLLgHa82xrekkDrIBZRQab1jyfY7KTtPimixiUsevAAAh+QQJCgAAACwAAAAAKwALAAACPYSOCMswD2FjqZpqW9xv4g8KE7d54XmMpNSgqLoOpgvC60xjNonnyc7p+VKamKw1zDCMR8rp8pksYlKorgAAIfkECQoAAAAsAAAAACsACwAAAkCEjgjLltnYmJS6Bxt+sfq5ZUyoNJ9HHlEqdCfFrqn7DrE2m7Wdj/2y45FkQ13t5itKdshFExC8YCLOEBX6AhQAADsAAAAAAAAAAAA=" width="43" height="11" class="gwt-Image"></div>
</div>
</div>
</td>
</tr>
</tbody>
<tfoot aria-hidden="true" style="display: none;"></tfoot>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABTUlEQVR42r2UO26EQAyGOQtnyVlyllTQcADqUIA4A1Cl5QLQ8H4/Smf/0Xo1M7CskmKRRiPM7w/bY49hvOPZ9/1zXVea55mmaRI73mH/C8RcloW6rqOyLA8LdnyH7hXoYxxH4dS2LfV9f1iw4zt00D+FIR0IzyD6KopCpH8KQj2qqhJpQJwkCYVheIDEcSx26KCH3wE2DAPVdf1w0mFN01AQBGRZ1sMGPfwU0LZtXxDLEciwPM/JdV0BkmH8E/jLKXZ6rRiWpik5jkO2bZ/C7ql2CgxGHeZ5ngAx5AyGIBTYLcxvuV5caESWZZlI8Soy+CuNyj2kw9jB9/0DjPWHBsa4yHXTTxOtEEWRAkOvwe+0+/m4r/oMByLrns7qLfcfbsar7ufmhv5yPlFMnk95IvgnsKNRlaK/ujkwJpg9BmHH+/0aMv97r5m8jHc+v9PgJIofYq0vAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="First page" aria-disabled="true"></td>
<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABOElEQVR42r1UO66DMBDkLJwlZ3lneRU0HID6NXAI6NJCjxAS//+n3GRQLMX2Ql5SxNLKsj0z8q5nbRjfGNu2/SzLQtM00TiO+4w19t8RMed5prZtqSgKLbCPc+BeCV2GYaA8z6lpGuq6TgvsQxQ44A/FkA6AnIgawAHPCqEeZVnuaXDkMAylNXDAg6eJ9X1PVVVpInVdk+/7ZNu2dgY80pWE1nX9BUkFp2lKruuSZVl7cDcGD/znFFu1VlEUkeM4+43OxMAD/1AsyzJN6N9i92v+qfVKkkRK8UgMjwC+ZFThIRXoed6hmMBrBka7cB6DBYIgYMWAB491v3huLp04jjVbPG7F9+o996voyTP3C3MDf9qfKCYMDMHnjhCOxz7OpaK/+jnQJug9IYQZ68c3ZH76r5kijG+OGyVGL0Z2EQ8bAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Previous page" aria-disabled="true"></td>
<td align="left" class="BFOGCKB-a-c" style="vertical-align: middle;">
<div class="gwt-HTML">1-1 of 0</div>
</td>
<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABNUlEQVR42r2UOY6EQAxFOQtnmbPMWTqChAMQdwKHgGxSyBFCYt+X0N0fjSVoVw3qCRrJQlT9/yhbdhnGJ55t276XZaFpmmgcx/2Nb6y/AzHneaa2bakoChFYxz50V6CvYRh2U9M01HWdCKzneU7QQa+FIR2AVJDXgA56JQj1KMtyT+NoCsNQCYMOevgEDMeuqkqYbNsm3/eprmuxB33f92fYuq43lRhhWdYerutSmqZiHz74jym2uloxDCd0HIeiKBK1g/8t2BGYZZke9jzmHcW8giHVJElE3eA/NSr3kA7meR69/pD1ooExLqpUAQqCQLQMpwifchb52EdDHMfK9FmnnYJn7j/cjFfdDx30f84niolGhOE4EfwTrGP/VPSrmwNjgtljEN74/r2GzP/eayaH8cnnAW4+L0Ycj6d3AAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Next page" aria-disabled="true"></td>
<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABR0lEQVR42r2UOY6DQBBFOQtnmbPMWSaChAMQj5BAnAGInHIBSNj3JSz7t6YsoNvGnsBIJUz3/89UUdWa9olrXdfveZ5pHEcahkHc8Yz1dyD6NE3UNA3leS4F1rEP3Rnoq+97Yarrmtq2lQLr2IcO+ocwpJNlmRJyDAChV4JQD04D4jAMJYDv+xRFkfgNXVEUoo4SrOs6KsvybjQMgzzPo6qqlDAE9PDtQMuy/GxNDEPYtk1pmiphCPjg36bYIEUVDGFZFsVxLGDH9OGD/2UYAx3Hkd5Mgt1e8xfFVMFM0xSpJkmiTBM++HeNyj10hLmuS/xHxzRZLzUwxmWbKkBBENxbRfUBoIdPOYv8uXFHwZ/1GeseTsEt9wvP5Fn3Qwf90/lEMdGIMKBWnOZ28LG/K/rZyYExwewxCHc8/x1D+n/PNZ1D++R1BRuAJHUT4bDpAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Last page" aria-disabled="true"></td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</td>
<td class="middleRight">
<div class="middleRightInner"></div>
</td>
</tr>
<tr class="bottom">
<td class="bottomLeft">
<div class="bottomLeftInner"></div>
</td>
<td class="bottomCenter">
<div class="bottomCenterInner"></div>
</td>
<td class="bottomRight">
<div class="bottomRightInner"></div>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</td>
<td class="middleRight">
<div class="middleRightInner"></div>
</td>
</tr>
<tr class="bottom">
<td class="bottomLeft">
<div class="bottomLeftInner"></div>
</td>
<td class="bottomCenter">
<div class="bottomCenterInner"></div>
</td>
<td class="bottomRight">
<div class="bottomRightInner"></div>
</td>
</tr>
</tbody>
</table>
</div>
<div style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;" aria-hidden="true">
<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel" style="width: 100%; height: 100%; display: none;" aria-hidden="true">
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
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel">
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
<table class="gridTable" style="width: 475px;border:1px solid gainsboro;">
<colgroup>
<col>
</colgroup>
<tbody>
<tr>
<td class="contacts-ListContainer contacts-ListMenu" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" style="width: 100%;">
<tbody>
<tr>
<td align="left" style="vertical-align: top;padding:7px;">
<table cellspacing="2" cellpadding="0" border="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Add</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Delete</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Update</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 4</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 5</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 6</button>
</td>
<td align="left" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" aria-hidden="true" style="display: none;">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<input type="text" class="gwt-TextBox" style="width: 350px; height: 35px;">
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" style="height: 35px;">Search</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td align="left" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 7</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 8</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 9</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 10</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 11</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 12</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
<td align="right" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">&lt;&lt;</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td>
<table cellspacing="0" cellpadding="0" style="height: 0px;">
<tbody>
<tr>
<td align="right" style="vertical-align: top;">
<input type="text" class="gwt-TextBox" placeholder="Enter some text">
</td>
</tr>
<tr>
<td align="left" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<table __gwtcellbasedwidgetimpldispatchingfocus="true" __gwtcellbasedwidgetimpldispatchingblur="true" class="BFOGCKB-c-y" cellspacing="0">
<colgroup>
<col>
<col>
<col>
</colgroup>
<thead>
<tr __gwt_header_row="0">
<th colspan="1" class="BFOGCKB-c-h BFOGCKB-c-f" __gwt_column="column-gwt-uid-10" __gwt_header="header-gwt-uid-11">Institution</th>
<th colspan="1" class="BFOGCKB-c-h" __gwt_column="column-gwt-uid-12" __gwt_header="header-gwt-uid-13">Reason for Leaving</th>
<th colspan="1" class="BFOGCKB-c-h BFOGCKB-c-p" __gwt_column="column-gwt-uid-14" __gwt_header="header-gwt-uid-15">Date Left</th>
</tr>
</thead>
<tbody style="display: none;"></tbody>
<tbody>
<tr>
<td align="center" colspan="3">
<div>
<div style="width: 100%; height: 100%; padding: 0px; margin: 0px;">
<div style="width: 100%; height: 100%;"></div>
</div>
<div aria-hidden="true" style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;">
<div aria-hidden="true" class="BFOGCKB-c-q" style="width: 100%; height: 100%; display: none;"><img src="data:image/gif;base64,R0lGODlhKwALAPEAAP///0tKSqampktKSiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAKwALAAACMoSOCMuW2diD88UKG95W88uF4DaGWFmhZid93pq+pwxnLUnXh8ou+sSz+T64oCAyTBUAACH5BAkKAAAALAAAAAArAAsAAAI9xI4IyyAPYWOxmoTHrHzzmGHe94xkmJifyqFKQ0pwLLgHa82xrekkDrIBZRQab1jyfY7KTtPimixiUsevAAAh+QQJCgAAACwAAAAAKwALAAACPYSOCMswD2FjqZpqW9xv4g8KE7d54XmMpNSgqLoOpgvC60xjNonnyc7p+VKamKw1zDCMR8rp8pksYlKorgAAIfkECQoAAAAsAAAAACsACwAAAkCEjgjLltnYmJS6Bxt+sfq5ZUyoNJ9HHlEqdCfFrqn7DrE2m7Wdj/2y45FkQ13t5itKdshFExC8YCLOEBX6AhQAADsAAAAAAAAAAAA=" width="43" height="11" class="gwt-Image"></div>
</div>
</div>
</td>
</tr>
</tbody>
<tfoot aria-hidden="true" style="display: none;"></tfoot>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABTUlEQVR42r2UO26EQAyGOQtnyVlyllTQcADqUIA4A1Cl5QLQ8H4/Smf/0Xo1M7CskmKRRiPM7w/bY49hvOPZ9/1zXVea55mmaRI73mH/C8RcloW6rqOyLA8LdnyH7hXoYxxH4dS2LfV9f1iw4zt00D+FIR0IzyD6KopCpH8KQj2qqhJpQJwkCYVheIDEcSx26KCH3wE2DAPVdf1w0mFN01AQBGRZ1sMGPfwU0LZtXxDLEciwPM/JdV0BkmH8E/jLKXZ6rRiWpik5jkO2bZ/C7ql2CgxGHeZ5ngAx5AyGIBTYLcxvuV5caESWZZlI8Soy+CuNyj2kw9jB9/0DjPWHBsa4yHXTTxOtEEWRAkOvwe+0+/m4r/oMByLrns7qLfcfbsar7ufmhv5yPlFMnk95IvgnsKNRlaK/ujkwJpg9BmHH+/0aMv97r5m8jHc+v9PgJIofYq0vAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="First page" aria-disabled="true"></td>
<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABOElEQVR42r1UO66DMBDkLJwlZ3lneRU0HID6NXAI6NJCjxAS//+n3GRQLMX2Ql5SxNLKsj0z8q5nbRjfGNu2/SzLQtM00TiO+4w19t8RMed5prZtqSgKLbCPc+BeCV2GYaA8z6lpGuq6TgvsQxQ44A/FkA6AnIgawAHPCqEeZVnuaXDkMAylNXDAg6eJ9X1PVVVpInVdk+/7ZNu2dgY80pWE1nX9BUkFp2lKruuSZVl7cDcGD/znFFu1VlEUkeM4+43OxMAD/1AsyzJN6N9i92v+qfVKkkRK8UgMjwC+ZFThIRXoed6hmMBrBka7cB6DBYIgYMWAB491v3huLp04jjVbPG7F9+o996voyTP3C3MDf9qfKCYMDMHnjhCOxz7OpaK/+jnQJug9IYQZ68c3ZH76r5kijG+OGyVGL0Z2EQ8bAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Previous page" aria-disabled="true"></td>
<td align="left" class="BFOGCKB-a-c" style="vertical-align: middle;">
<div class="gwt-HTML">1-1 of 0</div>
</td>
<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABNUlEQVR42r2UOY6EQAxFOQtnmbPMWTqChAMQdwKHgGxSyBFCYt+X0N0fjSVoVw3qCRrJQlT9/yhbdhnGJ55t276XZaFpmmgcx/2Nb6y/AzHneaa2bakoChFYxz50V6CvYRh2U9M01HWdCKzneU7QQa+FIR2AVJDXgA56JQj1KMtyT+NoCsNQCYMOevgEDMeuqkqYbNsm3/eprmuxB33f92fYuq43lRhhWdYerutSmqZiHz74jym2uloxDCd0HIeiKBK1g/8t2BGYZZke9jzmHcW8giHVJElE3eA/NSr3kA7meR69/pD1ooExLqpUAQqCQLQMpwifchb52EdDHMfK9FmnnYJn7j/cjFfdDx30f84niolGhOE4EfwTrGP/VPSrmwNjgtljEN74/r2GzP/eayaH8cnnAW4+L0Ycj6d3AAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Next page" aria-disabled="true"></td>
<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABR0lEQVR42r2UOY6DQBBFOQtnmbPMWSaChAMQj5BAnAGInHIBSNj3JSz7t6YsoNvGnsBIJUz3/89UUdWa9olrXdfveZ5pHEcahkHc8Yz1dyD6NE3UNA3leS4F1rEP3Rnoq+97Yarrmtq2lQLr2IcO+ocwpJNlmRJyDAChV4JQD04D4jAMJYDv+xRFkfgNXVEUoo4SrOs6KsvybjQMgzzPo6qqlDAE9PDtQMuy/GxNDEPYtk1pmiphCPjg36bYIEUVDGFZFsVxLGDH9OGD/2UYAx3Hkd5Mgt1e8xfFVMFM0xSpJkmiTBM++HeNyj10hLmuS/xHxzRZLzUwxmWbKkBBENxbRfUBoIdPOYv8uXFHwZ/1GeseTsEt9wvP5Fn3Qwf90/lEMdGIMKBWnOZ28LG/K/rZyYExwewxCHc8/x1D+n/PNZ1D++R1BRuAJHUT4bDpAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Last page" aria-disabled="true"></td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</td>
<td class="middleRight">
<div class="middleRightInner"></div>
</td>
</tr>
<tr class="bottom">
<td class="bottomLeft">
<div class="bottomLeftInner"></div>
</td>
<td class="bottomCenter">
<div class="bottomCenterInner"></div>
</td>
<td class="bottomRight">
<div class="bottomRightInner"></div>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</td>
<td class="middleRight">
<div class="middleRightInner"></div>
</td>
</tr>
<tr class="bottom">
<td class="bottomLeft">
<div class="bottomLeftInner"></div>
</td>
<td class="bottomCenter">
<div class="bottomCenterInner"></div>
</td>
<td class="bottomRight">
<div class="bottomRightInner"></div>
</td>
</tr>
</tbody>
</table>
</div>
<div style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;" aria-hidden="true">
<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel" style="width: 100%; height: 100%; display: none;" aria-hidden="true">
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
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="center" style="vertical-align: top;">
<table cellpadding="4">
<colgroup>
<col>
</colgroup>
<tbody>
<tr>
<td>
<div class="gwt-Label">Name:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox" style="width: 500px;">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Occupation:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Home Address:</div>
</td>
<td>
<input type="text" class="gwt-TextBox" style="width: 500px;">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Office Address:</div>
</td>
<td>
<input type="text" class="gwt-TextBox" style="width: 500px;">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Phone:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Any Ailment:</div>
</td>
<td><span class="gwt-CheckBox"><input type="checkbox" value="on" id="gwt-uid-3" tabindex="0"><label for="gwt-uid-3"></label></span></td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" style="height: 30px;">
<tbody>
<tr>
<td align="left" style="vertical-align: bottom;">
<button type="button" class="gwt-Button">Save Referee Details</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</td>
<td class="middleRight">
<div class="middleRightInner"></div>
</td>
</tr>
<tr class="bottom">
<td class="bottomLeft">
<div class="bottomLeftInner"></div>
</td>
<td class="bottomCenter">
<div class="bottomCenterInner"></div>
</td>
<td class="bottomRight">
<div class="bottomRightInner"></div>
</td>
</tr>
</tbody>
</table>
</div>
</div>

</div>
<div class="tab-pane fade" id="tab2success">
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
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="center" style="vertical-align: top;">
<table cellpadding="4">
<colgroup>
<col>
</colgroup>
<tbody>
<tr>
<td>
<div class="gwt-Label">First Name:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Address 1:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Last Name:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Address 2:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Other Name:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Relationship:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Phone:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Email:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" style="height: 30px;">
<tbody>
<tr>
<td align="left" style="vertical-align: bottom;">
<button type="button" class="gwt-Button">Save Next of Kin Details</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</td>
<td class="middleRight">
<div class="middleRightInner"></div>
</td>
</tr>
<tr class="bottom">
<td class="bottomLeft">
<div class="bottomLeftInner"></div>
</td>
<td class="bottomCenter">
<div class="bottomCenterInner"></div>
</td>
<td class="bottomRight">
<div class="bottomRightInner"></div>
</td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="tab-pane fade" id="tab3success">
<div class="gwt-TabPanelBottom" role="tabpanel">
<div aria-hidden="true" style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;">
<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel" style="width: 100%; height: 100%; display: none;" aria-hidden="true">
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
<table cellspacing="0" cellpadding="0" style="width: 100%;">
<tbody>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" class="contacts-ListContainer" cellpadding="4" style="width: 100%;">
<colgroup>
<col>
<col class="add-contact-input">
</colgroup>
<tbody>
<tr>
<td>
<div class="gwt-Label">Staff ID: *</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Gender: *</div>
</td>
<td>
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;"><span class="gwt-RadioButton"><input type="radio" name="gender" value="on" id="gwt-uid-1" tabindex="0"><label for="gwt-uid-1">Male</label></span></td>
<td align="left" style="vertical-align: top;"><span class="gwt-RadioButton"><input type="radio" name="gender" value="on" id="gwt-uid-2" tabindex="0"><label for="gwt-uid-2">Female</label></span></td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Title</div>
</td>
<td>
<select class="gwt-ListBox">
<option value="- Select Title -">- Select Title -</option>
<option value="Mr.">Mr.</option>
<option value="Mrs.">Mrs.</option>
<option value="Miss">Miss</option>
<option value="Dr.">Dr.</option>
<option value="Prof.">Prof.</option>
<option value="Alh.">Alh.</option>
<option value="Malam">Malam</option>
<option value="Hajia">Hajia</option>
<option value="Pst.">Pst.</option>
<option value="Sen.">Sen.</option>
<option value="Barr.">Barr.</option>
</select>
</td>
<td>
<div class="gwt-Label">Date of Birth:</div>
</td>
<td>
<input type="text" class="gwt-DateBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Last Name: *</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">State of Origin:</div>
</td>
<td>
<select class="gwt-ListBox">
<option value="- Select State -">- Select State -</option>
<option value="Abia">Abia</option>
<option value="Adamawa">Adamawa</option>
<option value="Akwa ibom">Akwa ibom</option>
<option value="Anambra">Anambra</option>
<option value="Bauchi">Bauchi</option>
<option value="Benue">Benue</option>
<option value="Borno">Borno</option>
<option value="Bayelsa">Bayelsa</option>
<option value="Cross river">Cross river</option>
<option value="Delta">Delta</option>
<option value="Ebonyi">Ebonyi</option>
<option value="Edo">Edo</option>
<option value="Ekiti">Ekiti</option>
<option value="Enugu">Enugu</option>
<option value="Fct">Fct</option>
<option value="Gombe">Gombe</option>
<option value="Imo">Imo</option>
<option value="Jigawa">Jigawa</option>
<option value="Kebbi">Kebbi</option>
<option value="Kaduna">Kaduna</option>
<option value="Kogi">Kogi</option>
<option value="Kano">Kano</option>
<option value="Katsina">Katsina</option>
<option value="Kwara">Kwara</option>
<option value="Lagos">Lagos</option>
<option value="Niger">Niger</option>
<option value="Nassarawa">Nassarawa</option>
<option value="Ondo">Ondo</option>
<option value="Ogun">Ogun</option>
<option value="Osun">Osun</option>
<option value="Oyo">Oyo</option>
<option value="Plateau">Plateau</option>
<option value="Rivers">Rivers</option>
<option value="Sokoto">Sokoto</option>
<option value="Taraba">Taraba</option>
<option value="Yobe">Yobe</option>
<option value="Zamfara">Zamfara</option>
</select>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">First Name: *</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">LGA of Origin:</div>
</td>
<td>
<select class="gwt-ListBox">
<option value="- Select Lga -">- Select Lga -</option>
</select>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Other Names:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Date of Appointment:</div>
</td>
<td>
<input type="text" class="gwt-DateBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Marital Status:</div>
</td>
<td>
<select class="gwt-ListBox">
<option value="--SELECT STATUS--">--SELECT STATUS--</option>
<option value="SINGLE">SINGLE</option>
<option value="MARRIED">MARRIED</option>
<option value="WIDOWED">WIDOWED</option>
<option value="DIVORCED">DIVORCED</option>
</select>
</td>
<td>
<div class="gwt-Label">Religion:</div>
</td>
<td>
<select class="gwt-ListBox">
<option value="--SELECT RELIGION--">--SELECT RELIGION--</option>
<option value="ISLAM">ISLAM</option>
<option value="CHRISTIAN">CHRISTIAN</option>
<option value="OTHERS">OTHERS</option>
</select>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Nationality:</div>
</td>
<td>
<select class="gwt-ListBox">
<option value="--SELECT NATION--">--SELECT NATION--</option>
<option value="NIGERIAN">NIGERIAN</option>
<option value="NON-NIGERIAN">NON-NIGERIAN</option>
</select>
</td>
<td>
<div class="gwt-Label">Denomination:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">No of children:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Branch:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">BloodGroup:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<select class="gwt-ListBox">
<option value="Select Department >>">Select Department &gt;&gt;</option>
<option value="001">001</option>
<option value="ARTS">ARTS</option>
<option value="Daycare">Daycare</option>
<option value="preK">preK</option>
</select>
</td>
<td>
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<div class="gwt-Label">Genotype:</div>
</td>
<td align="left" style="vertical-align: top;">
<input type="text" class="gwt-TextBox">
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td>
<div class="gwt-HTML">
<hr style="border: 1px dashed #CCC;">
</div>
</td>
<td>
<div class="gwt-HTML">
<hr style="border: 1px dashed #CCC;">
</div>
</td>
<td>
<div class="gwt-HTML">
<hr style="border: 1px dashed #CCC;">
</div>
</td>
<td>
<div class="gwt-HTML">
<hr style="border: 1px dashed #CCC;">
</div>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Address Line 1:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">P.O.Box:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Address Line 2:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">State:</div>
</td>
<td>
<select class="gwt-ListBox">
<option value="- Select State -">- Select State -</option>
<option value="Abia">Abia</option>
<option value="Adamawa">Adamawa</option>
<option value="Akwa ibom">Akwa ibom</option>
<option value="Anambra">Anambra</option>
<option value="Bauchi">Bauchi</option>
<option value="Benue">Benue</option>
<option value="Borno">Borno</option>
<option value="Bayelsa">Bayelsa</option>
<option value="Cross river">Cross river</option>
<option value="Delta">Delta</option>
<option value="Ebonyi">Ebonyi</option>
<option value="Edo">Edo</option>
<option value="Ekiti">Ekiti</option>
<option value="Enugu">Enugu</option>
<option value="Fct">Fct</option>
<option value="Gombe">Gombe</option>
<option value="Imo">Imo</option>
<option value="Jigawa">Jigawa</option>
<option value="Kebbi">Kebbi</option>
<option value="Kaduna">Kaduna</option>
<option value="Kogi">Kogi</option>
<option value="Kano">Kano</option>
<option value="Katsina">Katsina</option>
<option value="Kwara">Kwara</option>
<option value="Lagos">Lagos</option>
<option value="Niger">Niger</option>
<option value="Nassarawa">Nassarawa</option>
<option value="Ondo">Ondo</option>
<option value="Ogun">Ogun</option>
<option value="Osun">Osun</option>
<option value="Oyo">Oyo</option>
<option value="Plateau">Plateau</option>
<option value="Rivers">Rivers</option>
<option value="Sokoto">Sokoto</option>
<option value="Taraba">Taraba</option>
<option value="Yobe">Yobe</option>
<option value="Zamfara">Zamfara</option>
</select>
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">City:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Phone:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Initials:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Email:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" style="height: 30px;">
<tbody>
<tr>
<td align="left" style="vertical-align: bottom;">
<button type="button" class="gwt-Button">Save Staff Details</button>
</td>
<td align="left" style="vertical-align: bottom;">
<button type="button" class="gwt-Button">Add New Staff</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</td>
<td class="middleRight">
<div class="middleRightInner"></div>
</td>
</tr>
<tr class="bottom">
<td class="bottomLeft">
<div class="bottomLeftInner"></div>
</td>
<td class="bottomCenter">
<div class="bottomCenterInner"></div>
</td>
<td class="bottomRight">
<div class="bottomRightInner"></div>
</td>
</tr>
</tbody>
</table>
</div>
<div style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;" aria-hidden="true">
<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel" style="width: 100%; height: 100%; display: none;" aria-hidden="true">
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
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="center" style="vertical-align: top;">
<table cellpadding="4">
<colgroup>
<col>
</colgroup>
<tbody>
<tr>
<td>
<div class="gwt-Label">First Name:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Address 1:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Last Name:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Address 2:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Other Name:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Relationship:*</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
<tr>
<td>
<div class="gwt-Label">Phone:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
<td>
<div class="gwt-Label">Email:</div>
</td>
<td>
<input type="text" class="gwt-TextBox">
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" style="height: 30px;">
<tbody>
<tr>
<td align="left" style="vertical-align: bottom;">
<button type="button" class="gwt-Button">Save Next of Kin Details</button>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</td>
<td class="middleRight">
<div class="middleRightInner"></div>
</td>
</tr>
<tr class="bottom">
<td class="bottomLeft">
<div class="bottomLeftInner"></div>
</td>
<td class="bottomCenter">
<div class="bottomCenterInner"></div>
</td>
<td class="bottomRight">
<div class="bottomRightInner"></div>
</td>
</tr>
</tbody>
</table>
</div>
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
<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="center" style="vertical-align: top;">
<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel">
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
<table class="gridTable" style="width: 475px;border:1px solid gainsboro;">
<colgroup>
<col>
</colgroup>
<tbody>
<tr>
<td class="contacts-ListContainer contacts-ListMenu" style="vertical-align: top;">
<table class="xza"
">
<tbody>
<tr>
<td align="left" style="vertical-align: top;padding:7px;">
<table cellspacing="2" cellpadding="0" border="0">
<tbody>
<tr>

<div class="stupa">
<tr>
		<td>
		<div class="gwt-Label">Qualification:*</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox">
		</td>
		</tr>


<tr>
		<td>
		<div class="gwt-Label">Issuing Body:*</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox" >
		</td>
		</tr>

<tr>
		<td>
		<div class="gwt-Label">Dated Issued:*</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox" >
		</td>
		</tr>

		
		
<tr>
<center>
		<td>
		<button type="button" class="gwt-Button" style="margin-left: 107px;margin-top:10px;">Save</button>
		</td>
		
		</center>
		</tr>		
		
</div>







<td align="left" style="vertical-align: top;display:none;">
<table cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Add</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Delete</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Update</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 4</button>
</td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 5</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 6</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" aria-hidden="true" style="display: none;">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<input type="text" class="gwt-TextBox" style="width: 350px; height: 35px;">
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" style="height: 35px;">Search</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td align="left" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" border="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 7</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 8</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 9</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 10</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 11</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 12</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		<td align="right" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">&lt;&lt;</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td>
		<table cellspacing="0" cellpadding="0" style="height: 0px;">
		<tbody>
		<tr>
		<td align="right" style="vertical-align: top;">
		<input type="text" class="gwt-TextBox" placeholder="Enter some text" style="width:40%;">
		</td>
		</tr>
		<tr>
		<td align="left" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" border="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<table __gwtcellbasedwidgetimpldispatchingfocus="true" __gwtcellbasedwidgetimpldispatchingblur="true" class="BFOGCKB-c-y" cellspacing="0">
		<colgroup>
		<col>
		<col>
		<col>
		</colgroup>
		<thead>
		<tr __gwt_header_row="0">
		<th colspan="1" class="BFOGCKB-c-h BFOGCKB-c-f" __gwt_column="column-gwt-uid-4" __gwt_header="header-gwt-uid-5">Qualification</th>
		<th colspan="1" class="BFOGCKB-c-h" __gwt_column="column-gwt-uid-6" __gwt_header="header-gwt-uid-7">Institution</th>
		<th colspan="1" class="BFOGCKB-c-h BFOGCKB-c-p" __gwt_column="column-gwt-uid-8" __gwt_header="header-gwt-uid-9">Date Issued</th>
		</tr>
		</thead>
		<tbody style="display: none;"></tbody>
		<tbody>
		<tr>
		<td align="center" colspan="3">
		<div>
		<div style="width: 100%; height: 100%; padding: 0px; margin: 0px;">
		<div style="width: 100%; height: 100%;"></div>
		</div>
		<div aria-hidden="true" style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;">
		<div aria-hidden="true" class="BFOGCKB-c-q" style="width: 100%; height: 100%; display: none;"><img src="data:image/gif;base64,R0lGODlhKwALAPEAAP///0tKSqampktKSiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAKwALAAACMoSOCMuW2diD88UKG95W88uF4DaGWFmhZid93pq+pwxnLUnXh8ou+sSz+T64oCAyTBUAACH5BAkKAAAALAAAAAArAAsAAAI9xI4IyyAPYWOxmoTHrHzzmGHe94xkmJifyqFKQ0pwLLgHa82xrekkDrIBZRQab1jyfY7KTtPimixiUsevAAAh+QQJCgAAACwAAAAAKwALAAACPYSOCMswD2FjqZpqW9xv4g8KE7d54XmMpNSgqLoOpgvC60xjNonnyc7p+VKamKw1zDCMR8rp8pksYlKorgAAIfkECQoAAAAsAAAAACsACwAAAkCEjgjLltnYmJS6Bxt+sfq5ZUyoNJ9HHlEqdCfFrqn7DrE2m7Wdj/2y45FkQ13t5itKdshFExC8YCLOEBX6AhQAADsAAAAAAAAAAAA=" width="43" height="11" class="gwt-Image"></div>
		</div>
		</div>
		</td>
		</tr>
		</tbody>
		<tfoot aria-hidden="true" style="display: none;"></tfoot>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td align="center" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0">
		<tbody>
		<tr>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABTUlEQVR42r2UO26EQAyGOQtnyVlyllTQcADqUIA4A1Cl5QLQ8H4/Smf/0Xo1M7CskmKRRiPM7w/bY49hvOPZ9/1zXVea55mmaRI73mH/C8RcloW6rqOyLA8LdnyH7hXoYxxH4dS2LfV9f1iw4zt00D+FIR0IzyD6KopCpH8KQj2qqhJpQJwkCYVheIDEcSx26KCH3wE2DAPVdf1w0mFN01AQBGRZ1sMGPfwU0LZtXxDLEciwPM/JdV0BkmH8E/jLKXZ6rRiWpik5jkO2bZ/C7ql2CgxGHeZ5ngAx5AyGIBTYLcxvuV5caESWZZlI8Soy+CuNyj2kw9jB9/0DjPWHBsa4yHXTTxOtEEWRAkOvwe+0+/m4r/oMByLrns7qLfcfbsar7ufmhv5yPlFMnk95IvgnsKNRlaK/ujkwJpg9BmHH+/0aMv97r5m8jHc+v9PgJIofYq0vAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="First page" aria-disabled="true"></td>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABOElEQVR42r1UO66DMBDkLJwlZ3lneRU0HID6NXAI6NJCjxAS//+n3GRQLMX2Ql5SxNLKsj0z8q5nbRjfGNu2/SzLQtM00TiO+4w19t8RMed5prZtqSgKLbCPc+BeCV2GYaA8z6lpGuq6TgvsQxQ44A/FkA6AnIgawAHPCqEeZVnuaXDkMAylNXDAg6eJ9X1PVVVpInVdk+/7ZNu2dgY80pWE1nX9BUkFp2lKruuSZVl7cDcGD/znFFu1VlEUkeM4+43OxMAD/1AsyzJN6N9i92v+qfVKkkRK8UgMjwC+ZFThIRXoed6hmMBrBka7cB6DBYIgYMWAB491v3huLp04jjVbPG7F9+o996voyTP3C3MDf9qfKCYMDMHnjhCOxz7OpaK/+jnQJug9IYQZ68c3ZH76r5kijG+OGyVGL0Z2EQ8bAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Previous page" aria-disabled="true"></td>
		<td align="left" class="BFOGCKB-a-c" style="vertical-align: middle;">
		<div class="gwt-HTML">1-1 of 0</div>
		</td>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABNUlEQVR42r2UOY6EQAxFOQtnmbPMWTqChAMQdwKHgGxSyBFCYt+X0N0fjSVoVw3qCRrJQlT9/yhbdhnGJ55t276XZaFpmmgcx/2Nb6y/AzHneaa2bakoChFYxz50V6CvYRh2U9M01HWdCKzneU7QQa+FIR2AVJDXgA56JQj1KMtyT+NoCsNQCYMOevgEDMeuqkqYbNsm3/eprmuxB33f92fYuq43lRhhWdYerutSmqZiHz74jym2uloxDCd0HIeiKBK1g/8t2BGYZZke9jzmHcW8giHVJElE3eA/NSr3kA7meR69/pD1ooExLqpUAQqCQLQMpwifchb52EdDHMfK9FmnnYJn7j/cjFfdDx30f84niolGhOE4EfwTrGP/VPSrmwNjgtljEN74/r2GzP/eayaH8cnnAW4+L0Ycj6d3AAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Next page" aria-disabled="true"></td>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABR0lEQVR42r2UOY6DQBBFOQtnmbPMWSaChAMQj5BAnAGInHIBSNj3JSz7t6YsoNvGnsBIJUz3/89UUdWa9olrXdfveZ5pHEcahkHc8Yz1dyD6NE3UNA3leS4F1rEP3Rnoq+97Yarrmtq2lQLr2IcO+ocwpJNlmRJyDAChV4JQD04D4jAMJYDv+xRFkfgNXVEUoo4SrOs6KsvybjQMgzzPo6qqlDAE9PDtQMuy/GxNDEPYtk1pmiphCPjg36bYIEUVDGFZFsVxLGDH9OGD/2UYAx3Hkd5Mgt1e8xfFVMFM0xSpJkmiTBM++HeNyj10hLmuS/xHxzRZLzUwxmWbKkBBENxbRfUBoIdPOYv8uXFHwZ/1GeseTsEt9wvP5Fn3Qwf90/lEMdGIMKBWnOZ28LG/K/rZyYExwewxCHc8/x1D+n/PNZ1D++R1BRuAJHUT4bDpAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Last page" aria-disabled="true"></td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		</td>
		<td class="middleRight">
		<div class="middleRightInner"></div>
		</td>
		</tr>
		<tr class="bottom">
		<td class="bottomLeft">
		<div class="bottomLeftInner"></div>
		</td>
		<td class="bottomCenter">
		<div class="bottomCenterInner"></div>
		</td>
		<td class="bottomRight">
		<div class="bottomRightInner"></div>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		</td>
		<td class="middleRight">
		<div class="middleRightInner"></div>
		</td>
		</tr>
		<tr class="bottom">
		<td class="bottomLeft">
		<div class="bottomLeftInner"></div>
		</td>
		<td class="bottomCenter">
		<div class="bottomCenterInner"></div>
		</td>
		<td class="bottomRight">
		<div class="bottomRightInner"></div>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		<div aria-hidden="true" style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;">
		<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel" aria-hidden="true" style="width: 100%; height: 100%; display: none;">
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
		<table cellspacing="0" cellpadding="0">
		<tbody>
		<tr>
		<td align="center" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel">
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
		<table class="gridTable" style="width: 475px;border:1px solid gainsboro;">
		<colgroup>
		<col>
		</colgroup>
		<tbody>
		<tr>
		<td class="contacts-ListContainer contacts-ListMenu" style="vertical-align: top;">
		<table  class="xza">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;padding:7px;">
		<table cellspacing="2" cellpadding="0" border="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" border="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button">Add</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button">Delete</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button">Update</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 4</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 5</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 6</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" aria-hidden="true" style="display: none;">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<input type="text" class="gwt-TextBox" style="width: 350px; height: 35px;">
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" style="height: 35px;">Search</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td align="left" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" border="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 7</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 8</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 9</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 10</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 11</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 12</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		<td align="right" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">&lt;&lt;</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td>
		<table cellspacing="0" cellpadding="0" style="height: 0px;">
		<tbody>
		<tr>
		<td align="center" style="vertical-align: top;">
		<input type="text" class="gwt-TextBox" placeholder="Enter some text">
		</td>
		</tr>
		<tr>
		<td align="left" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" border="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<table __gwtcellbasedwidgetimpldispatchingfocus="true" __gwtcellbasedwidgetimpldispatchingblur="true" class="BFOGCKB-c-y" cellspacing="0">
		<colgroup>
		<col>
		<col>
		<col>
		</colgroup>
		<thead>
		<tr __gwt_header_row="0">
		<th colspan="1" class="BFOGCKB-c-h BFOGCKB-c-f" __gwt_column="column-gwt-uid-10" __gwt_header="header-gwt-uid-11">Institution</th>
		<th colspan="1" class="BFOGCKB-c-h" __gwt_column="column-gwt-uid-12" __gwt_header="header-gwt-uid-13">Reason for Leaving</th>
		<th colspan="1" class="BFOGCKB-c-h BFOGCKB-c-p" __gwt_column="column-gwt-uid-14" __gwt_header="header-gwt-uid-15">Date Left</th>
		</tr>
		</thead>
		<tbody style="display: none;"></tbody>
		<tbody>
		<tr>
		<td align="center" colspan="3">
		<div>
		<div style="width: 100%; height: 100%; padding: 0px; margin: 0px;">
		<div style="width: 100%; height: 100%;"></div>
		</div>
		<div aria-hidden="true" style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;">
		<div aria-hidden="true" class="BFOGCKB-c-q" style="width: 100%; height: 100%; display: none;"><img src="data:image/gif;base64,R0lGODlhKwALAPEAAP///0tKSqampktKSiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAKwALAAACMoSOCMuW2diD88UKG95W88uF4DaGWFmhZid93pq+pwxnLUnXh8ou+sSz+T64oCAyTBUAACH5BAkKAAAALAAAAAArAAsAAAI9xI4IyyAPYWOxmoTHrHzzmGHe94xkmJifyqFKQ0pwLLgHa82xrekkDrIBZRQab1jyfY7KTtPimixiUsevAAAh+QQJCgAAACwAAAAAKwALAAACPYSOCMswD2FjqZpqW9xv4g8KE7d54XmMpNSgqLoOpgvC60xjNonnyc7p+VKamKw1zDCMR8rp8pksYlKorgAAIfkECQoAAAAsAAAAACsACwAAAkCEjgjLltnYmJS6Bxt+sfq5ZUyoNJ9HHlEqdCfFrqn7DrE2m7Wdj/2y45FkQ13t5itKdshFExC8YCLOEBX6AhQAADsAAAAAAAAAAAA=" width="43" height="11" class="gwt-Image"></div>
		</div>
		</div>
		</td>
		</tr>
		</tbody>
		<tfoot aria-hidden="true" style="display: none;"></tfoot>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td align="center" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0">
		<tbody>
		<tr>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABTUlEQVR42r2UO26EQAyGOQtnyVlyllTQcADqUIA4A1Cl5QLQ8H4/Smf/0Xo1M7CskmKRRiPM7w/bY49hvOPZ9/1zXVea55mmaRI73mH/C8RcloW6rqOyLA8LdnyH7hXoYxxH4dS2LfV9f1iw4zt00D+FIR0IzyD6KopCpH8KQj2qqhJpQJwkCYVheIDEcSx26KCH3wE2DAPVdf1w0mFN01AQBGRZ1sMGPfwU0LZtXxDLEciwPM/JdV0BkmH8E/jLKXZ6rRiWpik5jkO2bZ/C7ql2CgxGHeZ5ngAx5AyGIBTYLcxvuV5caESWZZlI8Soy+CuNyj2kw9jB9/0DjPWHBsa4yHXTTxOtEEWRAkOvwe+0+/m4r/oMByLrns7qLfcfbsar7ufmhv5yPlFMnk95IvgnsKNRlaK/ujkwJpg9BmHH+/0aMv97r5m8jHc+v9PgJIofYq0vAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="First page" aria-disabled="true"></td>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABOElEQVR42r1UO66DMBDkLJwlZ3lneRU0HID6NXAI6NJCjxAS//+n3GRQLMX2Ql5SxNLKsj0z8q5nbRjfGNu2/SzLQtM00TiO+4w19t8RMed5prZtqSgKLbCPc+BeCV2GYaA8z6lpGuq6TgvsQxQ44A/FkA6AnIgawAHPCqEeZVnuaXDkMAylNXDAg6eJ9X1PVVVpInVdk+/7ZNu2dgY80pWE1nX9BUkFp2lKruuSZVl7cDcGD/znFFu1VlEUkeM4+43OxMAD/1AsyzJN6N9i92v+qfVKkkRK8UgMjwC+ZFThIRXoed6hmMBrBka7cB6DBYIgYMWAB491v3huLp04jjVbPG7F9+o996voyTP3C3MDf9qfKCYMDMHnjhCOxz7OpaK/+jnQJug9IYQZ68c3ZH76r5kijG+OGyVGL0Z2EQ8bAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Previous page" aria-disabled="true"></td>
		<td align="left" class="BFOGCKB-a-c" style="vertical-align: middle;">
		<div class="gwt-HTML">1-1 of 0</div>
		</td>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABNUlEQVR42r2UOY6EQAxFOQtnmbPMWTqChAMQdwKHgGxSyBFCYt+X0N0fjSVoVw3qCRrJQlT9/yhbdhnGJ55t276XZaFpmmgcx/2Nb6y/AzHneaa2bakoChFYxz50V6CvYRh2U9M01HWdCKzneU7QQa+FIR2AVJDXgA56JQj1KMtyT+NoCsNQCYMOevgEDMeuqkqYbNsm3/eprmuxB33f92fYuq43lRhhWdYerutSmqZiHz74jym2uloxDCd0HIeiKBK1g/8t2BGYZZke9jzmHcW8giHVJElE3eA/NSr3kA7meR69/pD1ooExLqpUAQqCQLQMpwifchb52EdDHMfK9FmnnYJn7j/cjFfdDx30f84niolGhOE4EfwTrGP/VPSrmwNjgtljEN74/r2GzP/eayaH8cnnAW4+L0Ycj6d3AAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Next page" aria-disabled="true"></td>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABR0lEQVR42r2UOY6DQBBFOQtnmbPMWSaChAMQj5BAnAGInHIBSNj3JSz7t6YsoNvGnsBIJUz3/89UUdWa9olrXdfveZ5pHEcahkHc8Yz1dyD6NE3UNA3leS4F1rEP3Rnoq+97Yarrmtq2lQLr2IcO+ocwpJNlmRJyDAChV4JQD04D4jAMJYDv+xRFkfgNXVEUoo4SrOs6KsvybjQMgzzPo6qqlDAE9PDtQMuy/GxNDEPYtk1pmiphCPjg36bYIEUVDGFZFsVxLGDH9OGD/2UYAx3Hkd5Mgt1e8xfFVMFM0xSpJkmiTBM++HeNyj10hLmuS/xHxzRZLzUwxmWbKkBBENxbRfUBoIdPOYv8uXFHwZ/1GeseTsEt9wvP5Fn3Qwf90/lEMdGIMKBWnOZ28LG/K/rZyYExwewxCHc8/x1D+n/PNZ1D++R1BRuAJHUT4bDpAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Last page" aria-disabled="true"></td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		</td>
		<td class="middleRight">
		<div class="middleRightInner"></div>
		</td>
		</tr>
		<tr class="bottom">
		<td class="bottomLeft">
		<div class="bottomLeftInner"></div>
		</td>
		<td class="bottomCenter">
		<div class="bottomCenterInner"></div>
		</td>
		<td class="bottomRight">
		<div class="bottomRightInner"></div>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		</td>
		<td class="middleRight">
		<div class="middleRightInner"></div>
		</td>
		</tr>
		<tr class="bottom">
		<td class="bottomLeft">
		<div class="bottomLeftInner"></div>
		</td>
		<td class="bottomCenter">
		<div class="bottomCenterInner"></div>
		</td>
		<td class="bottomRight">
		<div class="bottomRightInner"></div>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		<div aria-hidden="true" style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;">
		<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel" aria-hidden="true" style="width: 100%; height: 100%; display: none;">
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
		<table cellspacing="0" cellpadding="0">
		<tbody>
		<tr>
		<td align="center" style="vertical-align: top;">
		<table cellpadding="4">
		<colgroup>
		<col>
		</colgroup>
		<tbody>
		<tr>
		<td>
		<div class="gwt-Label">Name:*</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox" style="width: 500px;">
		</td>
		</tr>
		<tr>
		<td>
		<div class="gwt-Label">Occupation:*</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox">
		</td>
		</tr>
		<tr>
		<td>
		<div class="gwt-Label">Home Address:</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox" style="width: 500px;">
		</td>
		</tr>
		<tr>
		<td>
		<div class="gwt-Label">Office Address:</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox" style="width: 500px;">
		</td>
		</tr>
		<tr>
		<td>
		<div class="gwt-Label">Phone:</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox">
		</td>
		</tr>
		<tr>
		<td>
		<div class="gwt-Label">Any Ailment:</div>
		</td>
		<td><span class="gwt-CheckBox"><input type="checkbox" value="on" id="gwt-uid-3" tabindex="0"><label for="gwt-uid-3"></label></span></td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td align="center" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" style="height: 30px;">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: bottom;">
		<button type="button" class="gwt-Button">Save Referee Details</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		</td>
		<td class="middleRight">
		<div class="middleRightInner"></div>
		</td>
		</tr>
		<tr class="bottom">
		<td class="bottomLeft">
		<div class="bottomLeftInner"></div>
		</td>
		<td class="bottomCenter">
		<div class="bottomCenterInner"></div>
		</td>
		<td class="bottomRight">
		<div class="bottomRightInner"></div>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		</div>
		</div>
		<div class="tab-pane fade" id="tab4success">
		<div class="middleCenterInner">
		<table cellspacing="0" cellpadding="0">
		<tbody>
		<tr>
		<td align="center" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" class="gwt-DecoratorPanel">
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
		<table class="gridTable" style="width: 475px;border: 1px solid gainsboro;">
		<colgroup>
		<col>
		</colgroup>
		<tbody>
		<tr>
		<td class="contacts-ListContainer contacts-ListMenu" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" style="width: 100%;">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;border-bottom: 1px solid;">
		<table cellspacing="2" cellpadding="0" border="0">
		<tbody>
		
		
<div class="stupa">
<tr>
		<td>
		<div class="gwt-Label">Qualification:*</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox">
		</td>
		</tr>


<tr>
		<td>
		<div class="gwt-Label">Issuing Body:*</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox" >
		</td>
		</tr>

<tr>
		<td>
		<div class="gwt-Label">Dated Issued:*</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox" >
		</td>
		</tr>

		
		
<tr>

		<td>
		<button type="button" class="gwt-Button" style="margin-left: 107px;margin-top:10px;margin-bottom: 6px;">Save</button>
		</td>
		
	
		</tr>		
		
</div>
		
		
		
		
		
		
		
		
		
		
		
		
		
		<tr>
		<td align="left" style="vertical-align: top;padding:7px;display:none;">
		<table cellspacing="0" cellpadding="0" border="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button">Add</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button">Delete</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button">Update</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 4</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 5</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 6</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" aria-hidden="true" style="display: none;">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<input type="text" class="gwt-TextBox" style="width: 350px; height: 35px;">
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" style="height: 35px;">Search</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td align="left" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" border="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 7</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 8</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 9</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 10</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 11</button>
		</td>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">Button 12</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		<td align="right" style="vertical-align: top;display:none;">
		<table cellspacing="0" cellpadding="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<button type="button" class="gwt-Button" aria-hidden="true" style="display: none;">&lt;&lt;</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td>
		<table cellspacing="0" cellpadding="0" style="height: 0px;">
		<tbody>
		<tr>
		<td align="right" style="vertical-align: top;width:40%;">
		<input type="text" class="gwt-TextBox" style="width:40%;" placeholder="Enter some text">
		</td>
		</tr>
		<tr>
		<td align="left" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" border="0">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: top;">
		<table __gwtcellbasedwidgetimpldispatchingfocus="true" __gwtcellbasedwidgetimpldispatchingblur="true" class="BFOGCKB-c-y" cellspacing="0">
		<colgroup>
		<col>
		<col>
		<col>
		</colgroup>
		<thead>
		<tr __gwt_header_row="0">
		<th colspan="1" class="BFOGCKB-c-h BFOGCKB-c-f" __gwt_column="column-gwt-uid-10" __gwt_header="header-gwt-uid-11">Institution</th>
		<th colspan="1" class="BFOGCKB-c-h" __gwt_column="column-gwt-uid-12" __gwt_header="header-gwt-uid-13">Reason for Leaving</th>
		<th colspan="1" class="BFOGCKB-c-h BFOGCKB-c-p" __gwt_column="column-gwt-uid-14" __gwt_header="header-gwt-uid-15">Date Left</th>
		</tr>
		</thead>
		<tbody style="display: none;"></tbody>
		<tbody>
		<tr>
		<td align="center" colspan="3">
		<div>
		<div style="width: 100%; height: 100%; padding: 0px; margin: 0px;">
		<div style="width: 100%; height: 100%;"></div>
		</div>
		<div aria-hidden="true" style="width: 100%; height: 100%; padding: 0px; margin: 0px; display: none;">
		<div aria-hidden="true" class="BFOGCKB-c-q" style="width: 100%; height: 100%; display: none;"><img src="data:image/gif;base64,R0lGODlhKwALAPEAAP///0tKSqampktKSiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAKwALAAACMoSOCMuW2diD88UKG95W88uF4DaGWFmhZid93pq+pwxnLUnXh8ou+sSz+T64oCAyTBUAACH5BAkKAAAALAAAAAArAAsAAAI9xI4IyyAPYWOxmoTHrHzzmGHe94xkmJifyqFKQ0pwLLgHa82xrekkDrIBZRQab1jyfY7KTtPimixiUsevAAAh+QQJCgAAACwAAAAAKwALAAACPYSOCMswD2FjqZpqW9xv4g8KE7d54XmMpNSgqLoOpgvC60xjNonnyc7p+VKamKw1zDCMR8rp8pksYlKorgAAIfkECQoAAAAsAAAAACsACwAAAkCEjgjLltnYmJS6Bxt+sfq5ZUyoNJ9HHlEqdCfFrqn7DrE2m7Wdj/2y45FkQ13t5itKdshFExC8YCLOEBX6AhQAADsAAAAAAAAAAAA=" width="43" height="11" class="gwt-Image"></div>
		</div>
		</div>
		</td>
		</tr>
		</tbody>
		<tfoot aria-hidden="true" style="display: none;"></tfoot>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td align="center" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0">
		<tbody>
		<tr>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABTUlEQVR42r2UO26EQAyGOQtnyVlyllTQcADqUIA4A1Cl5QLQ8H4/Smf/0Xo1M7CskmKRRiPM7w/bY49hvOPZ9/1zXVea55mmaRI73mH/C8RcloW6rqOyLA8LdnyH7hXoYxxH4dS2LfV9f1iw4zt00D+FIR0IzyD6KopCpH8KQj2qqhJpQJwkCYVheIDEcSx26KCH3wE2DAPVdf1w0mFN01AQBGRZ1sMGPfwU0LZtXxDLEciwPM/JdV0BkmH8E/jLKXZ6rRiWpik5jkO2bZ/C7ql2CgxGHeZ5ngAx5AyGIBTYLcxvuV5caESWZZlI8Soy+CuNyj2kw9jB9/0DjPWHBsa4yHXTTxOtEEWRAkOvwe+0+/m4r/oMByLrns7qLfcfbsar7ufmhv5yPlFMnk95IvgnsKNRlaK/ujkwJpg9BmHH+/0aMv97r5m8jHc+v9PgJIofYq0vAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="First page" aria-disabled="true"></td>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABOElEQVR42r1UO66DMBDkLJwlZ3lneRU0HID6NXAI6NJCjxAS//+n3GRQLMX2Ql5SxNLKsj0z8q5nbRjfGNu2/SzLQtM00TiO+4w19t8RMed5prZtqSgKLbCPc+BeCV2GYaA8z6lpGuq6TgvsQxQ44A/FkA6AnIgawAHPCqEeZVnuaXDkMAylNXDAg6eJ9X1PVVVpInVdk+/7ZNu2dgY80pWE1nX9BUkFp2lKruuSZVl7cDcGD/znFFu1VlEUkeM4+43OxMAD/1AsyzJN6N9i92v+qfVKkkRK8UgMjwC+ZFThIRXoed6hmMBrBka7cB6DBYIgYMWAB491v3huLp04jjVbPG7F9+o996voyTP3C3MDf9qfKCYMDMHnjhCOxz7OpaK/+jnQJug9IYQZ68c3ZH76r5kijG+OGyVGL0Z2EQ8bAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Previous page" aria-disabled="true"></td>
		<td align="left" class="BFOGCKB-a-c" style="vertical-align: middle;">
		<div class="gwt-HTML">1-1 of 0</div>
		</td>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABNUlEQVR42r2UOY6EQAxFOQtnmbPMWTqChAMQdwKHgGxSyBFCYt+X0N0fjSVoVw3qCRrJQlT9/yhbdhnGJ55t276XZaFpmmgcx/2Nb6y/AzHneaa2bakoChFYxz50V6CvYRh2U9M01HWdCKzneU7QQa+FIR2AVJDXgA56JQj1KMtyT+NoCsNQCYMOevgEDMeuqkqYbNsm3/eprmuxB33f92fYuq43lRhhWdYerutSmqZiHz74jym2uloxDCd0HIeiKBK1g/8t2BGYZZke9jzmHcW8giHVJElE3eA/NSr3kA7meR69/pD1ooExLqpUAQqCQLQMpwifchb52EdDHMfK9FmnnYJn7j/cjFfdDx30f84niolGhOE4EfwTrGP/VPSrmwNjgtljEN74/r2GzP/eayaH8cnnAW4+L0Ycj6d3AAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Next page" aria-disabled="true"></td>
		<td align="left" class="BFOGCKB-a-a BFOGCKB-a-b" style="vertical-align: middle;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAABR0lEQVR42r2UOY6DQBBFOQtnmbPMWSaChAMQj5BAnAGInHIBSNj3JSz7t6YsoNvGnsBIJUz3/89UUdWa9olrXdfveZ5pHEcahkHc8Yz1dyD6NE3UNA3leS4F1rEP3Rnoq+97Yarrmtq2lQLr2IcO+ocwpJNlmRJyDAChV4JQD04D4jAMJYDv+xRFkfgNXVEUoo4SrOs6KsvybjQMgzzPo6qqlDAE9PDtQMuy/GxNDEPYtk1pmiphCPjg36bYIEUVDGFZFsVxLGDH9OGD/2UYAx3Hkd5Mgt1e8xfFVMFM0xSpJkmiTBM++HeNyj10hLmuS/xHxzRZLzUwxmWbKkBBENxbRfUBoIdPOYv8uXFHwZ/1GeseTsEt9wvP5Fn3Qwf90/lEMdGIMKBWnOZ28LG/K/rZyYExwewxCHc8/x1D+n/PNZ1D++R1BRuAJHUT4bDpAAAAAElFTkSuQmCC" width="19" height="19" class="gwt-Image" role="button" aria-label="Last page" aria-disabled="true"></td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		</td>
		<td class="middleRight">
		<div class="middleRightInner"></div>
		</td>
		</tr>
		<tr class="bottom">
		<td class="bottomLeft">
		<div class="bottomLeftInner"></div>
		</td>
		<td class="bottomCenter">
		<div class="bottomCenterInner"></div>
		</td>
		<td class="bottomRight">
		<div class="bottomRightInner"></div>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		</div>
		<div class="tab-pane fade" id="tab5success">
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
		<table cellspacing="0" cellpadding="0">
		<tbody>
		<tr>
		<td align="center" style="vertical-align: top;">
		<table cellpadding="4">
		<colgroup>
		<col>
		</colgroup>
		<tbody>
		<tr>
		<td>
		<div class="gwt-Label">Name:*</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox" style="width: 500px;">
		</td>
		</tr>
		<tr>
		<td>
		<div class="gwt-Label">Occupation:*</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox">
		</td>
		</tr>
		<tr>
		<td>
		<div class="gwt-Label">Home Address:</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox" style="width: 500px;">
		</td>
		</tr>
		<tr>
		<td>
		<div class="gwt-Label">Office Address:</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox" style="width: 500px;">
		</td>
		</tr>
		<tr>
		<td>
		<div class="gwt-Label">Phone:</div>
		</td>
		<td>
		<input type="text" class="gwt-TextBox">
		</td>
		</tr>
		<tr>
		<td>
		<div class="gwt-Label">Any Ailment:</div>
		</td>
		<td><span class="gwt-CheckBox"><input type="checkbox" value="on" id="gwt-uid-3" tabindex="0"><label for="gwt-uid-3"></label></span></td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		<tr>
		<td align="center" style="vertical-align: top;">
		<table cellspacing="0" cellpadding="0" style="height: 30px;">
		<tbody>
		<tr>
		<td align="left" style="vertical-align: bottom;">
		<button type="button" class="gwt-Button">Save Referee Details</button>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		</td>
		<td class="middleRight">
		<div class="middleRightInner"></div>
		</td>
		</tr>
		<tr class="bottom">
		<td class="bottomLeft">
		<div class="bottomLeftInner"></div>
		</td>
		<td class="bottomCenter">
		<div class="bottomCenterInner"></div>
		</td>
		<td class="bottomRight">
		<div class="bottomRightInner"></div>
		</td>
		</tr>
		</tbody>
		</table>
		</div>
		</div>
		</div>
		</div>
		</div>
		</div>

		</div>
		</div>
<div class="col-md-1">
       		
	<div class="beddy">
	   <span class="bedd"> Picture</span>
       <img src="./assets/image/abc.jpg">	
		
		</div>
		<div class="beddy-a">
		<button>Change>>></button>
		
		</div>
		
	<div class="beddy-b">
 <span class="bedd"> Signature</span>
	<input text="text">
		<div class="beddy-c">
		<button>Change>>></button>
		
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
		} );
		</script>	

		</body>

		</html>