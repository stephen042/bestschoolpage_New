<?php include('../config.php'); 

include('inc.session-create.php'); 
$PageTitle="Manage Staff";
$FileName = 'staff.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
$stat['success']=$_SESSION['success'];
unset($_SESSION['success']);
}

if(isset($_POST['add_staff_detail']))
{        
				/*$validate->addRule($_POST['staff_id'],'','staff_id',true);
				$validate->addRule($_POST['gender'],'',' gender',true);
				$validate->addRule($_POST['last_name'],'','last_name',true);
				$validate->addRule($_POST['date_of_appointment'],'','date_of_appointment ',true);
				$validate->addRule($_POST['first_name'],'','first_name',true);
				$validate->addRule($_POST['state_of_origin'],'','state_of_origin',true);
				$validate->addRule($_POST['lga_of_origin'],'','lga_of_origin',true);
				$validate->addRule($_POST['religion'],'',' religion',true);
				$validate->addRule($_POST['nationality'],'','nationality',true);*/
									
			    if($validate->validate() && count($stat)==0)
				  {
					  
				  	  
				$randomId=randomFix(15);
					$aryData=array(	
				'usertype'                                    =>2,
				'staff_id'                                        => $_POST['staff_id'],
				'gender'                                          => $_POST['gender'],
				'title'                                           => $_POST['title'],
				'date_of_birth'  			                      => $_POST['date_of_birth'],
				'last_name'                                       => $_POST['last_name'],	
				 'first_name'                                     => $_POST['first_name'],
				'date_of_appointment'                             => $_POST['date_of_appointment'], 
			    'state_of_origin'                                 => $_POST['state_of_origin'],
				'other_name'                                      =>  $_POST['other_name'],
				'lga_of_origin'                                   => $_POST['lga_of_origin'],
				'marrital_status'  			                      => $_POST['marrital_status'],		
				'religion'  			                          => $_POST['religion'],	
				'nationality'  			                          => $_POST['nationality'],	   
				'denomination'  			                      =>  $_POST['denomination'],	
				'no_of_children'                                  => $_POST['no_of_children'],
				'branch'  			                              => $_POST['branch'],	
				'blood_group'  			                          => $_POST['blood_group'],
                'genotype'  			                          => $_POST['genotype'],				
				'address_1'                                       => $_POST['address_1'],
				'address_2'  			                          => $_POST['address_2'],		
			    'state'  			                              => $_POST['state'],
                'city'                                            => $_POST['city'],				
				'p_o_box'                                         => $_POST['p_o_box'],
				'email'  			                              => $_POST['email'],	
				'phone'                                           => $_POST['phone'],
				'mobile'  			                              => $_POST['mobile'],
				'create_by_usertype'                              => $create_by_usertype,
				'create_by_userid'  			                  => $create_by_userid,
				'randomid'  			                          => $randomId,	 
				
					            );  
					$flgIn1 = $db->insertAry("staff_manage",$aryData);
					//echo $flgIn1 = $db->getLastQuery();
					//exit;
					
					redirect($FileName.'?action=add_next_of_kin_details&randomid='.$randomId);
                    $stat['success']="Submited Successfully";					
					//unset($_POST);
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

if(isset($_POST['edit_staff_detail']))
{        
				/*$validate->addRule($_POST['staff_id'],'','staff_id',true);
				$validate->addRule($_POST['gender'],'',' gender',true);
				$validate->addRule($_POST['last_name'],'','last_name',true);
				$validate->addRule($_POST['date_of_appointment'],'','date_of_appointment ',true);
				$validate->addRule($_POST['first_name'],'','first_name',true);
				$validate->addRule($_POST['state_of_origin'],'','state_of_origin',true);
				$validate->addRule($_POST['lga_of_origin'],'','lga_of_origin',true);
				$validate->addRule($_POST['religion'],'',' religion',true);
				$validate->addRule($_POST['nationality'],'','nationality',true);*/
									
			    if($validate->validate() && count($stat)==0)
				  {
					  
				  	  
				$randomId=randomFix(15);
					$aryData=array(	
				'usertype'                                    =>2,
				'staff_id'                                        => $_POST['staff_id'],
				'gender'                                          => $_POST['gender'],
				'title'                                           => $_POST['title'],
				'date_of_birth'  			                      => $_POST['date_of_birth'],
				'last_name'                                       => $_POST['last_name'],	
				 'first_name'                                     => $_POST['first_name'],
				'date_of_appointment'                             => $_POST['date_of_appointment'], 
			    'state_of_origin'                                 => $_POST['state_of_origin'],
				'other_name'                                      =>  $_POST['other_name'],
				'lga_of_origin'                                   => $_POST['lga_of_origin'],
				'marrital_status'  			                      => $_POST['marrital_status'],		
				'religion'  			                          => $_POST['religion'],	
				'nationality'  			                          => $_POST['nationality'],	   
				'denomination'  			                      =>  $_POST['denomination'],	
				'no_of_children'                                  => $_POST['no_of_children'],
				'branch'  			                              => $_POST['branch'],	
				'blood_group'  			                          => $_POST['blood_group'],
                'genotype'  			                          => $_POST['genotype'],				
				'address_1'                                       => $_POST['address_1'],
				'address_2'  			                          => $_POST['address_2'],		
			    'state'  			                              => $_POST['state'],
                'city'                                            => $_POST['city'],				
				'p_o_box'                                         => $_POST['p_o_box'],
				'email'  			                              => $_POST['email'],	
				'phone'                                           => $_POST['phone'],
				'mobile'  			                              => $_POST['mobile'],
				'create_by_usertype'                              => $create_by_usertype,
				'create_by_userid'  			                  => $create_by_userid,
				'randomid'  			                          => $randomId,	 
				
					            );  
					$flgIn1 = $db->updateAry("staff_manage",$aryData, "where randomid='".$_GET['randomid']."'");
					//echo $flgIn1 = $db->getLastQuery();
					//exit;
					
					redirect($FileName);
                    $stat['success']="Submited Successfully";					
					//unset($_POST);
				}


		else    
		        {
					$stat['error'] = $validate->errors();
				}
			}
			
			


elseif(isset($_POST['add_next_of_kin_details']))
{                
				
				$validate->addRule($_POST['last_name'],'','Last Name',true);
				$validate->addRule($_POST['first_name'],'','First Name',true);
				$validate->addRule($_POST['phone'],'','Phone',true);
				$validate->addRule($_POST['email'],'','Email',true);
				
				
				$staffid=$db->getrow("select * from staff_manage where randomid='".$_GET['randomid']."'");
									
			    if($validate->validate() && count($stat)==0)
				  {
				  	 
				
					$aryData=array(	
					
				'staff_manage_id'                                   => $staffid['id'],
				'first_name'                                   => $_POST['first_name'],     
				'last_name'                                  => $_POST['last_name'],
			    'relationship'                                       => $_POST['relationship'],
				'phone'                                       => $_POST['phone'],
				'email'                                       => $_POST['email'],
				'mobile'                                       => $_POST['mobile'],
				'address_1'                                       => $_POST['address_1'],
				'address_2'                                       => $_POST['address_2'],
				'create_by_usertype'                             => $create_by_usertype,
				'create_by_userid'                              => $create_by_userid,
			    'randomid'  			                        => $_GET['randomid'],
             				
				
				         );  
					$flgIn1 = $db->insertAry("staff_manage_kin_details",$aryData);
					//echo $flgIn1 = $db->getLastQuery();
					//exit;
					
					$stat['success']="Submited Successfully";
					  redirect($FileName.'?action=add_staff_educational&randomid='.$_GET['randomid']); 
					
                   
					unset($_POST);
					 
				}
			else {
					$stat['error'] = $validate->errors();
				}
			}
			
			elseif(isset($_POST['edit_next_of_kin_details']))
{                
				
				$validate->addRule($_POST['last_name'],'','Last Name',true);
				$validate->addRule($_POST['first_name'],'','First Name',true);
				$validate->addRule($_POST['phone'],'','Phone',true);
				$validate->addRule($_POST['email'],'','Email',true);
				
				
				$staffid=$db->getrow("select * from staff_manage where randomid='".$_GET['randomid']."'");
									
			    if($validate->validate() && count($stat)==0)
				  {
				  	 
				
					$aryData=array(	
					
				'staff_manage_id'                                   => $staffid['id'],
				'first_name'                                   => $_POST['first_name'],     
				'last_name'                                  => $_POST['last_name'],
			    'relationship'                                       => $_POST['relationship'],
				'phone'                                       => $_POST['phone'],
				'email'                                       => $_POST['email'],
				'mobile'                                       => $_POST['mobile'],
				'address_1'                                       => $_POST['address_1'],
				'address_2'                                       => $_POST['address_2'],
				'create_by_usertype'                             => $create_by_usertype,
				'create_by_userid'                              => $create_by_userid,
			    'randomid'  			                        => $_GET['randomid'],
             				
				
				         );  
					$flgIn1 = $db->updateAry("staff_manage_kin_details",$aryData);
					//echo $flgIn1 = $db->getLastQuery();
					//exit;
					
					$stat['success']="Updated Successfully";
					  redirect($FileName.'?action=add_staff_educational&randomid='.$_GET['randomid']); 
					
                   
					unset($_POST);
					 
				}
			else {
					$stat['error'] = $validate->errors();
				}
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
    background: #1565c0;
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
	margin-bottom: 15px;
}
.zqw22 .panel .panel-body {
    border-right: none!important;
    border: 1px solid gainsboro;
}
.gwt-Label {
    padding: 8px;

}
.zqw22  input {
	width:60%;
    padding: 8px 3px 8px 0;
    border: 1px solid gainsboro;
    background: #dcdcdc45;
    border-radius: 5px;
	margin-bottom: 5px;
	margin-top: 5px;
}
.zqw22 button {
    border: 1px solid #1565c0;
    padding: 4px 5px 4px 5px;
    margin-right: 7px;
    background: transparent;
    color: #1565c0;
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
	overflow-x:scroll;
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
.nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
    padding-left: 15px!important;
    padding-right: 15px !important;
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
    background: #1565c0;
    color: white;
}
.zqw22 .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover, .tabs-vertical>li.active>a, .tabs-vertical>li.active>a:focus, .tabs-vertical>li.active>a:hover {
      border-bottom: 3px solid #1565c0;
}
                .topside-section li a {
                    border: 1px solid #1565c0;
                    padding: 5px 5px 4px 5px;
                    display: block;
                }
                
                .zswqas li a:hover {
                    width: 239px;
                    display: block;
                    padding: 16px 14px 14px 18px;
                    border-bottom: 2px solid gainsboro;
                    background: #1565c0;
                    color: white;
                }
                
                .zswqas .active {
                    width: 239px;
                    display: block;
                    padding: 16px 14px 14px 18px;
                    border-bottom: 2px solid gainsboro;
                    background: #1565c0;
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
    background: #1565c0;
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
    border: 1px solid #1565c0;
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

.gwt-Label{
	width:40%;
	float:left;
	font-size: 13px;
}
#setB input {
    width: 15%;
}
.gwt-ListBox{
	width:60%
}
.beddy img{
	width:100%;
}
.beddy-b input{
    height: 50px;
    width: 100%;
}

.hhf button{
	margin-top:10px;
	margin-bottom:20px;
}
.desh{
	border-bottom: 2px solid;
    border-bottom-style: dashed;
	margin: 20px 0 20px 0px;
}
.ssd{
	text-align: center;
	margin:10px 0 0 0;
	padding-bottom: 10px;
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
<div class="col-md-3">
<div class="zasw ">


<div class="zawq Wizard-a1">

<table id="example" class="display" >
<thead class="setting">
<tr>
<th>Position</th>
<th>Position</th>

</tr>
</thead>

<tbody>
<?php $aryDetail=$db->getRows("select * from staff_manage "); 

foreach($aryDetail as $iList) { ?>
<tr> 
	<td style="padding:0px;"></td>
	<td class="sectsab <?php if($_GET['randomid']==$iList['randomid']) { echo "active"; }?>">
	<a href="<?php echo $FileName; ?>?action=basic_info&randomid=<?php echo $iList['randomid']?>">
	 <ul><li>
	<span class="zwq"> <img class="table-img" src="https://i0.wp.com/www.winhelponline.com/blog/wp-content/uploads/2017/12/user.png?fit=256%2C256&quality=100&ssl=1"></span><?php echo$iList['staff_id']; ?></li>
	</ul>
	</a>
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
<div class="col-md-9">
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
<li class="<?php if($_GET['action']=='basic_info') {echo "active"; }?>"><a href="<?php echo $FileName; ?>?action=basic_info&randomid=<?php echo $_GET['randomid']; ?>">Basic Info</a></li>
<li class="<?php if($_GET['action']=='add_next_of_kin_details') {echo "active"; }?>"><a href="<?php echo $FileName; ?>?action=add_next_of_kin_details&randomid=<?php echo $_GET['randomid']; ?>">Next of  Kin Details</a></li>
<li class="<?php if($_GET['action']=='add_staff_educational') {echo "active"; }?>"><a href="<?php echo $FileName; ?>?action=add_staff_educational&randomid=<?php echo $_GET['randomid']; ?>">Educational Qualification</a></li>
<li class="<?php if($_GET['action']=='add_previous_employment') {echo "active"; }?>"><a href="<?php echo $FileName; ?>?action=add_previous_employment&randomid=<?php echo $_GET['randomid']; ?>">Previous Employment</a></li>
<li class="<?php if($_GET['action']=='add_Referee') {echo "active"; }?>"><a href="<?php echo $FileName; ?>?action=add_Referee&randomid=<?php echo $_GET['randomid']; ?>">Refree</a></li>
</ul>
</div>

<div class="row ">
<div class="col-md-5">
<div class="col-md-12">
<div class="gwt-Label">Staff ID: *</div>
<input type="text" name="staff_id" value="" class="nnh">
</div>
<div class="col-md-12">
<div class="gwt-Label">Title</div>
<select class="gwt-ListBox" name="title">
</div>
<div class="col-md-12">
<option>Select Title</option>
<option value="Mr.">Mr.</option>
<option value="Mrs.">Mrs.</option>
<option value="Miss.">Miss.</option>
<option value="Dr.">Dr.</option>
<option value="Prof.">Prof.</option>
<option value="Alh.">Alh.</option>
<option value="Malam.">Malam.</option>
<option value="Hajia.">Hajia.</option>
<option value="Pst.">Pst.</option>
<option value="Sen.">Sen.</option>
<option value="Barr.">Barr.</option>
</select>
</div>
<div class="col-md-12">
<div class="gwt-Label">Last Name</div>
<input type="text">
</div>
<div class="col-md-12">
<div class="gwt-Label">First Name</div>
<input type="text">
</div>
<div class="col-md-12">
<div class="gwt-Label">Other Names</div>
<input type="text">
</div>
<div class="col-md-12">
<div class="gwt-Label">Marital Status:</div>
<select class="gwt-ListBox" name="marrital_status">
<option>SELECT STATUS</option>
<option value="SINGLE">SINGLE</option>
<option value="MARRIED">MARRIED</option>
<option value="WIDOWED">WIDOWED</option>
<option value="DIVORCED">DIVORCED</option>
</select>

</div>
<div class="col-md-12">
<div class="gwt-Label">Nationality:</div>
<input type="text" >
</div>
<div class="col-md-12">
<div class="gwt-Label">No of children:</div>
<input type="text" name="staff_id" value="" class="nnh">
</div>
<div class="col-md-12">
<div class="gwt-Label">BloodGroup:</div>
<input type="text" name="staff_id" value="" class="nnh">
</div>
</div>


<div class="col-md-5">
<div class="col-md-12">
<div class="gwt-Label">Gender: *</div>
<fieldset id="setB">
	<input id="setB_male" type="radio" name="setB_gender">
		<label for="setB_male">Male</label>
	<input id="setB_female" type="radio" name="setB_gender">
		<label for="setB_female">Female</label>

</fieldset>
</div>
<div class="col-md-12">
<div class="gwt-Label">	Date of Birth:</div>
<input type="text" name="date_of_birth" value="" class="gwt-DateBox datepicker">
</div>
<div class="col-md-12">
<div class="gwt-Label">	State of Origin:</div>
<input type="text">
</div>
<div class="col-md-12">
<div class="gwt-Label">LGA of Origin:</div>
<select class="gwt-ListBox" name="lga_of_origin">
<option value="">Select LGA Origin</option>
                                    <option value="698">Tambuwal</option>
                                    <option value="697">Talata Mafara</option>
                                    <option value="696">Takum</option>
                                    <option value="695">Takai</option>
                                    <option value="694">Tai</option>
                                    <option value="693">Tafawa Balewa</option>
                                    <option value="692">Tafa</option>
                                    <option value="691">Surulere</option>
                                    <option value="690">Surulere</option>
                                    <option value="689">Suru</option>
                                    <option value="688">Sumaila</option>
                                    <option value="687">Sule Tankarkar</option>
                                    <option value="686">Suleja</option>
                                    <option value="685">Southern Ijaw</option>
                                    <option value="684">Song</option>
                                    <option value="683">Sokoto South</option>
                                    <option value="682">Sokoto North</option>
                                    <option value="681">Soba</option>
                                    <option value="680">Silame</option>
                                    <option value="679">Shomolu</option>
                                    <option value="678">Shongom</option>
                                    <option value="677">Shiroro</option>
                                    <option value="676">Shira</option>
                                    <option value="675">Shinkafi</option>
                                    <option value="674">Shendam</option>
                                    <option value="673">Shelleng</option>
                                    <option value="672">Shanono</option>
                                    <option value="671">Shani</option>
                                    <option value="670">Shanga</option>
                                    <option value="669">Shagari</option>
                                    <option value="668">Shagamu</option>
                                    <option value="667">Sardauna</option>
                                    <option value="666">Sapele</option>
                                    <option value="665">Sanga</option>
                                    <option value="664">Sandamu</option>
                                    <option value="663">Saki West</option>
                                    <option value="662">Saki East</option>
                                    <option value="661">Sakaba</option>
                                    <option value="660">Sagbama</option>
                                    <option value="659">Safana</option>
                                    <option value="658">Sabuwa</option>
                                    <option value="657">Sabon Gari</option>
                                    <option value="656">Sabon Birni</option>
                                    <option value="655">Roni</option>
                                    <option value="654">Rogo</option>
                                    <option value="653">Riyom</option>
                                    <option value="652">Ringim</option>
                                    <option value="651">Rimin Gado</option>
                                    <option value="650">Rimi</option>
                                    <option value="649">Rijau</option>
                                    <option value="648">Remo North</option>
                                    <option value="647">Rano</option>
                                    <option value="646">Rafi</option>
                                    <option value="645">Rabah</option>
                                    <option value="644">Potiskum</option>
                                    <option value="643">Port Harcourt</option>
                                    <option value="642">Pategi</option>
                                    <option value="641">Patani</option>
                                    <option value="640">Pankshin</option>
                                    <option value="639">Paikoro</option>
                                    <option value="638">Oyun</option>
                                    <option value="637">Oyo East</option>
                                    <option value="636">Oyo</option>
                                    <option value="635">Oyigbo</option>
                                    <option value="634">Oyi</option>
                                    <option value="633">Oye</option>
                                    <option value="632">Owo</option>
                                    <option value="631">Owerri West</option>
                                    <option value="630">Owerri North</option>
                                    <option value="629">Owerri Municipal</option>
                                    <option value="628">Owan West</option>
                                    <option value="627">Owan East</option>
                                    <option value="626">Ovia South-West</option>
                                    <option value="625">Ovia North-East</option>
                                    <option value="624">Oturkpo</option>
                                    <option value="623">Osogbo</option>
                                    <option value="622">Osisioma</option>
                                    <option value="621">Oshodi-Isolo</option>
                                    <option value="620">Oshimili South</option>
                                    <option value="619">Oshimili North</option>
                                    <option value="618">Ose</option>
                                    <option value="617">Oru West</option>
                                    <option value="616">Orumba South</option>
                                    <option value="615">Orumba North</option>
                                    <option value="614">Oruk Anam</option>
                                    <option value="613">Oru East</option>
                                    <option value="612">Orsu</option>
                                    <option value="611">Oron</option>
                                    <option value="610">Orolu</option>
                                    <option value="609">Orlu</option>
                                    <option value="608">Ori Ire</option>
                                    <option value="607">Oriade</option>
                                    <option value="606">Orelope</option>
                                    <option value="605">Oredo</option>
                                    <option value="604">Opobo/Nkoro</option>
                                    <option value="603">Okpokwu</option>
                                    <option value="602">Onna</option>
                                    <option value="601">Onitsha South</option>
                                    <option value="600">Onitsha North</option>
                                    <option value="599">Onicha</option>
                                    <option value="598">Ondo West</option>
                                    <option value="597">Ondo East</option>
                                    <option value="596">Ona Ara</option>
                                    <option value="595">Omuma</option>
                                    <option value="594">Omala</option>
                                    <option value="593">Oluyole</option>
                                    <option value="592">Olorunsogo</option>
                                    <option value="591">Olorunda</option>
                                    <option value="590">Ola Oluwa</option>
                                    <option value="589">Olamaboro</option>
                                    <option value="588">Okrika</option>
                                    <option value="587">Okpe</option>
                                    <option value="586">Okobo</option>
                                    <option value="585">Okitipupa</option>
                                    <option value="584">Okigwe</option>
                                    <option value="583">Oke Ero</option>
                                    <option value="582">Okene</option>
                                    <option value="581">Okehi</option>
                                    <option value="580">Oju</option>
                                    <option value="579">Ojo</option>
                                    <option value="578">Oji River</option>
                                    <option value="577">Orhionmwon</option>
                                    <option value="576">Ohimini</option>
                                    <option value="575">Ohaukwu</option>
                                    <option value="574">Ohaozara</option>
                                    <option value="573">Ohaji/Egbema</option>
                                    <option value="572">Ohafia</option>
                                    <option value="571">Oguta</option>
                                    <option value="570">Ogun Waterside</option>
                                    <option value="569">Ogori/Magongo</option>
                                    <option value="568">Ogo Oluwa</option>
                                    <option value="567">Ogoja</option>
                                    <option value="566">Ogu/Bolo</option>
                                    <option value="565">Ogbomosho South</option>
                                    <option value="564">Ogbomosho North</option>
                                    <option value="563">Ogbia</option>
                                    <option value="562">Ogbaru</option>
                                    <option value="561">Ogbadibo</option>
                                    <option value="560">Ogba/Egbema/Ndoni</option>
                                    <option value="559">Ofu</option>
                                    <option value="558">Offa</option>
                                    <option value="557">Odukpani</option>
                                    <option value="556">Odo Otin</option>
                                    <option value="555">Odogbolu</option>
                                    <option value="554">Odigbo</option>
                                    <option value="553">Odeda</option>
                                    <option value="552">Obudu</option>
                                    <option value="551">Obubra</option>
                                    <option value="550">Obowo</option>
                                    <option value="549">Obot Akara</option>
                                    <option value="548">Obokun</option>
                                    <option value="547">Obio/Akpor</option>
                                    <option value="546">Obi Ngwa</option>
                                    <option value="545">Obi</option>
                                    <option value="544">Obi</option>
                                    <option value="543">Obanliku</option>
                                    <option value="542">Obafemi Owode</option>
                                    <option value="541">Nwangele</option>
                                    <option value="540">Numan</option>
                                    <option value="539">Nsukka</option>
                                    <option value="538">Nsit-Ubium</option>
                                    <option value="537">Nsit-Ibom</option>
                                    <option value="536">Nsit-Atai</option>
                                    <option value="535">Nnewi South</option>
                                    <option value="534">Nnewi North</option>
                                    <option value="533">Nkwerre</option>
                                    <option value="532">Nkanu West</option>
                                    <option value="531">Nkanu East</option>
                                    <option value="530">Njikoka</option>
                                    <option value="529">Njaba</option>
                                    <option value="528">Ningi</option>
                                    <option value="527">Nguru</option>
                                    <option value="526">Ngor Okpala</option>
                                    <option value="525">Ngaski</option>
                                    <option value="524">Nganzai</option>
                                    <option value="523">Ngala</option>
                                    <option value="522">Nembe</option>
                                    <option value="521">Ndokwa West</option>
                                    <option value="520">Ndokwa East</option>
                                    <option value="519">Nasarawa Egon</option>
                                    <option value="518">Nasarawa</option>
                                    <option value="517">Nasarawa</option>
                                    <option value="516">Nangere</option>
                                    <option value="515">Nafada</option>
                                    <option value="514">Mushin</option>
                                    <option value="513">Musawa</option>
                                    <option value="512">Municipal Area Council</option>
                                    <option value="511">Mkpat-Enin</option>
                                    <option value="510">Moya</option>
                                    <option value="509">Moro</option>
                                    <option value="508">Mopa Muro</option>
                                    <option value="507">Monguno</option>
                                    <option value="506">Mokwa</option>
                                    <option value="505">Mubi South</option>
                                    <option value="504">Mubi North</option>
                                    <option value="503">Mobbar</option>
                                    <option value="502">Moba</option>
                                    <option value="501">Misau</option>
                                    <option value="500">Minjibir</option>
                                    <option value="499">Mikang</option>
                                    <option value="498">Miga</option>
                                    <option value="497">Michika</option>
                                    <option value="496">Mbo</option>
                                    <option value="495">Mbaitoli</option>
                                    <option value="494">Mayo Belwa</option>
                                    <option value="493">Matazu</option>
                                    <option value="492">Mashi</option>
                                    <option value="491">Mashegu</option>
                                    <option value="490">Maru</option>
                                    <option value="489">Marte</option>
                                    <option value="488">Makurdi</option>
                                    <option value="487">Mariga</option>
                                    <option value="486">Maradun</option>
                                    <option value="485">Mani</option>
                                    <option value="484">Mangu</option>
                                    <option value="483">Malumfashi</option>
                                    <option value="482">Malam Madori</option>
                                    <option value="481">Makoda</option>
                                    <option value="480">Makarfi</option>
                                    <option value="479">Maiyama</option>
                                    <option value="478">Maiha</option>
                                    <option value="477">Maigatari</option>
                                    <option value="476">Maiduguri</option>
                                    <option value="475">Magumeri</option>
                                    <option value="474">Magama</option>
                                    <option value="473">Mafa</option>
                                    <option value="472">Madobi</option>
                                    <option value="471">Madagali</option>
                                    <option value="470">Machina</option>
                                    <option value="469">Lokoja</option>
                                    <option value="468">Logo</option>
                                    <option value="467">Lere</option>
                                    <option value="466">Lavun</option>
                                    <option value="465">Lau</option>
                                    <option value="464">Lamurde</option>
                                    <option value="463">Lapai</option>
                                    <option value="462">Langtang North</option>
                                    <option value="461">Langtang South</option>
                                    <option value="460">Lagos Mainland</option>
                                    <option value="459">Lagos Island</option>
                                    <option value="458">Lagelu</option>
                                    <option value="457">Lafia</option>
                                    <option value="456">Kwaya Kusar</option>
                                    <option value="455">Kware</option>
                                    <option value="454">Kwami</option>
                                    <option value="453">Kwande</option>
                                    <option value="452">Kwali</option>
                                    <option value="451">Kusada</option>
                                    <option value="450">Kurfi</option>
                                    <option value="449">Kura</option>
                                    <option value="448">Kunchi</option>
                                    <option value="447">Kumi</option>
                                    <option value="446">Kumbotso</option>
                                    <option value="445">Kukawa</option>
                                    <option value="444">Kuje</option>
                                    <option value="443">Kudan</option>
                                    <option value="442">Kubau</option>
                                    <option value="441">Kaugama</option>
                                    <option value="440">Kosofe</option>
                                    <option value="439">Kontagora</option>
                                    <option value="438">Konshisha</option>
                                    <option value="437">Konduga</option>
                                    <option value="436">Kolokuma/Opokuma</option>
                                    <option value="435">Kokona</option>
                                    <option value="434">Koko/Besse</option>
                                    <option value="433">Kogi</option>
                                    <option value="432">Kiyawa</option>
                                    <option value="431">Kiru</option>
                                    <option value="430">Kiri Kasama</option>
                                    <option value="429">Kirfi</option>
                                    <option value="428">Kibiya</option>
                                    <option value="427">Khana</option>
                                    <option value="426">Keffi</option>
                                    <option value="425">Kebbe</option>
                                    <option value="424">Keana</option>
                                    <option value="423">Kazaure</option>
                                    <option value="422">Kauru</option>
                                    <option value="421">Kaura Namoda</option>
                                    <option value="420">Kaura</option>
                                    <option value="419">Katsina-Ala</option>
                                    <option value="418">Katsina</option>
                                    <option value="417">Katcha</option>
                                    <option value="416">Katagum</option>
                                    <option value="415">Karu</option>
                                    <option value="414">Karim Lamido</option>
                                    <option value="413">Karaye</option>
                                    <option value="412">Karasuwa</option>
                                    <option value="411">Kano Municipal</option>
                                    <option value="410">Kankia</option>
                                    <option value="409">Kanke</option>
                                    <option value="408">Kankara</option>
                                    <option value="407">Kanam</option>
                                    <option value="406">Kaltungo</option>
                                    <option value="405">Kalgo</option>
                                    <option value="404">Kala/Balge</option>
                                    <option value="403">Kajuru</option>
                                    <option value="402">Kajola</option>
                                    <option value="401">Kaita</option>
                                    <option value="400">Kaiama</option>
                                    <option value="399">Kagarko</option>
                                    <option value="398">Kaga</option>
                                    <option value="397">Kafur</option>
                                    <option value="396">Kafin Hausa</option>
                                    <option value="395">Kaduna South</option>
                                    <option value="394">Kaduna North</option>
                                    <option value="393">Kachia</option>
                                    <option value="392">Kabo</option>
                                    <option value="391">Kabba/Bunu</option>
                                    <option value="390">Jos South</option>
                                    <option value="389">Jos North</option>
                                    <option value="388">Jos East</option>
                                    <option value="387">Jibia</option>
                                    <option value="386">Jere</option>
                                    <option value="385">Jega</option>
                                    <option value="384">Jalingo</option>
                                    <option value="383">Jakusko</option>
                                    <option value="382">Jahun</option>
                                    <option value="381">Jada</option>
                                    <option value="380">Jaba</option>
                                    <option value="379">Izzi</option>
                                    <option value="378">Iwo</option>
                                    <option value="377">Iwajowa</option>
                                    <option value="376">Ivo</option>
                                    <option value="375">Itu</option>
                                    <option value="374">Itesiwaju</option>
                                    <option value="373">Itas/Gadau</option>
                                    <option value="372">Isuikwuato</option>
                                    <option value="371">Isu</option>
                                    <option value="370">Isoko South</option>
                                    <option value="369">Isoko North</option>
                                    <option value="368">Isokan</option>
                                    <option value="367">Isi Uzo</option>
                                    <option value="366">Isin</option>
                                    <option value="365">Isiala Ngwa South</option>
                                    <option value="364">Isiala Ngwa North</option>
                                    <option value="363">Isiala Mbano</option>
                                    <option value="362">Ishielu</option>
                                    <option value="361">Iseyin</option>
                                    <option value="360">Ise/Orun</option>
                                    <option value="359">Isa</option>
                                    <option value="358">Irewole</option>
                                    <option value="357">Irepodun/Ifelodun</option>
                                    <option value="356">Irepodun</option>
                                    <option value="355">Irepodun</option>
                                    <option value="354">Irepo</option>
                                    <option value="353">Irele</option>
                                    <option value="352">Ipokia</option>
                                    <option value="351">Ini</option>
                                    <option value="350">Ingawa</option>
                                    <option value="349">Imeko Afon</option>
                                    <option value="348">Ilorin West</option>
                                    <option value="347">Ilorin South</option>
                                    <option value="346">Ilorin East</option>
                                    <option value="345">Illela</option>
                                    <option value="344">Ilesa West</option>
                                    <option value="343">Ilesa East</option>
                                    <option value="342">Ile Oluji/Okeigbo</option>
                                    <option value="341">Ilejemeje</option>
                                    <option value="340">Ila</option>
                                    <option value="339">Ikwuano</option>
                                    <option value="338">Ikwo</option>
                                    <option value="337">Ikwerre</option>
                                    <option value="336">Ikpoba Okha</option>
                                    <option value="335">Ikot Ekpene</option>
                                    <option value="334">Ikot Abasi</option>
                                    <option value="333">Ikorodu</option>
                                    <option value="332">Ikono</option>
                                    <option value="331">Ikom</option>
                                    <option value="330">Ikole</option>
                                    <option value="329">Ikere</option>
                                    <option value="328">Ikenne</option>
                                    <option value="327">Ikeja</option>
                                    <option value="326">Ikeduru</option>
                                    <option value="325">Ika South</option>
                                    <option value="324">Ikara</option>
                                    <option value="323">Ika North East</option>
                                    <option value="322">Ika</option>
                                    <option value="321">Ijumu</option>
                                    <option value="320">Ijero</option>
                                    <option value="319">Ijebu Ode</option>
                                    <option value="318">Ijebu North East</option>
                                    <option value="317">Ijebu North</option>
                                    <option value="316">Ijebu East</option>
                                    <option value="315">Ilaje</option>
                                    <option value="314">Ihitte/Uboma</option>
                                    <option value="313">Ihiala</option>
                                    <option value="312">Igueben</option>
                                    <option value="311">Igbo Eze South</option>
                                    <option value="310">Igbo Eze North</option>
                                    <option value="309">Igbo Etiti</option>
                                    <option value="308">Igalamela Odolu</option>
                                    <option value="307">Igabi</option>
                                    <option value="306">Ifo</option>
                                    <option value="305">Ifelodun</option>
                                    <option value="304">Ifelodun</option>
                                    <option value="303">Ifedore</option>
                                    <option value="302">Ifedayo</option>
                                    <option value="301">Ifako-Ijaiye</option>
                                    <option value="300">Ido Osi</option>
                                    <option value="299">Ido</option>
                                    <option value="298">Idemili South</option>
                                    <option value="297">Idemili North</option>
                                    <option value="296">Ideato South</option>
                                    <option value="295">Ideato North</option>
                                    <option value="294">Idanre</option>
                                    <option value="293">Idah</option>
                                    <option value="292">Ibiono-Ibom</option>
                                    <option value="291">Ibi</option>
                                    <option value="290">Ibesikpo Asutan</option>
                                    <option value="289">Ibeno</option>
                                    <option value="288">Ibeju-Lekki</option>
                                    <option value="287">Ibarapa North</option>
                                    <option value="286">Ibarapa East</option>
                                    <option value="285">Ibarapa Central</option>
                                    <option value="284">Ibaji</option>
                                    <option value="283">Ibadan South-West</option>
                                    <option value="282">Ibadan South-East</option>
                                    <option value="281">Ibadan North-West</option>
                                    <option value="280">Ibadan North-East</option>
                                    <option value="279">Ibadan North</option>
                                    <option value="278">Hong</option>
                                    <option value="277">Hawul</option>
                                    <option value="276">Hadejia</option>
                                    <option value="275">Gwoza</option>
                                    <option value="274">Gwiwa</option>
                                    <option value="273">Gwer West</option>
                                    <option value="272">Gwer East</option>
                                    <option value="271">Gwarzo</option>
                                    <option value="270">Gwaram</option>
                                    <option value="269">Gwandu</option>
                                    <option value="268">Gwale</option>
                                    <option value="267">Gwagwalada</option>
                                    <option value="266">Gwadabawa</option>
                                    <option value="265">Guzamala</option>
                                    <option value="264">Gusau</option>
                                    <option value="263">Guri</option>
                                    <option value="262">Gurara</option>
                                    <option value="261">Gummi</option>
                                    <option value="260">Gumel</option>
                                    <option value="259">Guma</option>
                                    <option value="258">Gulani</option>
                                    <option value="257">Gujba</option>
                                    <option value="256">Gudu</option>
                                    <option value="255">Gubio</option>
                                    <option value="254">Grie</option>
                                    <option value="253">Goronyo</option>
                                    <option value="252">Gombi</option>
                                    <option value="251">Gombe</option>
                                    <option value="250">Gokana</option>
                                    <option value="249">Giwa</option>
                                    <option value="248">Giade</option>
                                    <option value="247">Geidam</option>
                                    <option value="246">Gbonyin</option>
                                    <option value="245">Gboko</option>
                                    <option value="244">Gbako</option>
                                    <option value="243">Gezawa</option>
                                    <option value="242">Gayuk</option>
                                    <option value="241">Gaya</option>
                                    <option value="240">Gassol</option>
                                    <option value="239">Gashaka</option>
                                    <option value="238">Garun Mallam</option>
                                    <option value="237">Garko</option>
                                    <option value="236">Garki</option>
                                    <option value="235">Ganye</option>
                                    <option value="234">Ganjuwa</option>
                                    <option value="233">Gamawa</option>
                                    <option value="232">Gagarawa</option>
                                    <option value="231">Gada</option>
                                    <option value="230">Gabasawa</option>
                                    <option value="229">Funtua</option>
                                    <option value="228">Fune</option>
                                    <option value="227">Funakaye</option>
                                    <option value="226">Fufure</option>
                                    <option value="225">Fika</option>
                                    <option value="224">Faskari</option>
                                    <option value="223">Fakai</option>
                                    <option value="222">Fagge</option>
                                    <option value="221">Ezza South</option>
                                    <option value="220">Ezza North</option>
                                    <option value="219">Ezinihitte</option>
                                    <option value="218">Ezeagu</option>
                                    <option value="217">Ewekoro</option>
                                    <option value="216">Etung</option>
                                    <option value="215">Etsako West</option>
                                    <option value="214">Etsako East</option>
                                    <option value="213">Etsako Central</option>
                                    <option value="212">Eti Osa</option>
                                    <option value="211">Etinan</option>
                                    <option value="210">Etim Ekpo</option>
                                    <option value="209">Ethiope West</option>
                                    <option value="208">Ethiope East</option>
                                    <option value="207">Etche</option>
                                    <option value="206">Essien Udim</option>
                                    <option value="205">Esit Eket</option>
                                    <option value="204">Ese Odo</option>
                                    <option value="203">Esan West</option>
                                    <option value="202">Esan South-East</option>
                                    <option value="201">Esan North-East</option>
                                    <option value="200">Esan Central</option>
                                    <option value="199">Epe</option>
                                    <option value="198">Enugu South</option>
                                    <option value="197">Enugu North</option>
                                    <option value="196">Enugu East</option>
                                    <option value="195">Emure</option>
                                    <option value="194">Emuoha</option>
                                    <option value="193">Eleme</option>
                                    <option value="192">Ekwusigo</option>
                                    <option value="191">Ekiti West</option>
                                    <option value="190">Ekiti South-West</option>
                                    <option value="189">Ekiti East</option>
                                    <option value="188">Ekiti</option>
                                    <option value="187">Eket</option>
                                    <option value="186">Ekeremor</option>
                                    <option value="185">Ejigbo</option>
                                    <option value="184">Ehime Mbano</option>
                                    <option value="183">Egor</option>
                                    <option value="182">Egbedore</option>
                                    <option value="181">Egbeda</option>
                                    <option value="180">Egbado South</option>
                                    <option value="179">Egbado North</option>
                                    <option value="178">Efon</option>
                                    <option value="177">Ife South</option>
                                    <option value="176">Ife North</option>
                                    <option value="175">Ife East</option>
                                    <option value="174">Ife Central</option>
                                    <option value="173">Edu</option>
                                    <option value="172">Ede South</option>
                                    <option value="171">Ede North</option>
                                    <option value="170">Edati</option>
                                    <option value="169">Ebonyi</option>
                                    <option value="168">Eastern Obolo</option>
                                    <option value="167">Dutsin Ma</option>
                                    <option value="166">Dutsi</option>
                                    <option value="165">Dutse</option>
                                    <option value="164">Dunukofia</option>
                                    <option value="163">Dukku</option>
                                    <option value="162">Donga</option>
                                    <option value="161">Doma</option>
                                    <option value="160">Doguwa</option>
                                    <option value="159">Dikwa</option>
                                    <option value="158">Demsa</option>
                                    <option value="157">Dekina</option>
                                    <option value="156">Degema</option>
                                    <option value="155">Dawakin Tofa</option>
                                    <option value="154">Dawakin Kudu</option>
                                    <option value="153">Daura</option>
                                    <option value="152">Dass</option>
                                    <option value="151">Darazo</option>
                                    <option value="150">Dan Musa</option>
                                    <option value="149">Danja</option>
                                    <option value="148">Dange Shuni</option>
                                    <option value="147">Dandume</option>
                                    <option value="146">Dandi</option>
                                    <option value="145">Damboa</option>
                                    <option value="144">Dambatta</option>
                                    <option value="143">Damban</option>
                                    <option value="142">Damaturu</option>
                                    <option value="141">Dala</option>
                                    <option value="140">Chikun</option>
                                    <option value="139">Chibok</option>
                                    <option value="138">Charanchi</option>
                                    <option value="137">Chanchaga</option>
                                    <option value="136">Calabar South</option>
                                    <option value="135">Calabar Municipal</option>
                                    <option value="134">Bwari</option>
                                    <option value="133">Burutu</option>
                                    <option value="132">Bunza</option>
                                    <option value="131">Bunkure</option>
                                    <option value="130">Bungudu</option>
                                    <option value="129">Buruku</option>
                                    <option value="128">Bukkuyum</option>
                                    <option value="127">Buji</option>
                                    <option value="126">Brass</option>
                                    <option value="125">Bosso</option>
                                    <option value="124">Bursari</option>
                                    <option value="123">Boripe</option>
                                    <option value="122">Borgu</option>
                                    <option value="121">Bonny</option>
                                    <option value="120">Bomadi</option>
                                    <option value="119">Boluwaduro</option>
                                    <option value="118">Boki</option>
                                    <option value="117">Bogoro</option>
                                    <option value="116">Bodinga</option>
                                    <option value="115">Biu</option>
                                    <option value="114">Birnin Magaji/Kiyaw</option>
                                    <option value="113">Birnin Kudu</option>
                                    <option value="112">Birnin Kebbi</option>
                                    <option value="111">Birnin Gwari</option>
                                    <option value="110">Biriniwa</option>
                                    <option value="109">Binji</option>
                                    <option value="108">Bindawa</option>
                                    <option value="107">Billiri</option>
                                    <option value="106">Bida</option>
                                    <option value="105">Bichi</option>
                                    <option value="104">Biase</option>
                                    <option value="103">Bende</option>
                                    <option value="102">Bekwarra</option>
                                    <option value="101">Bebeji</option>
                                    <option value="100">Bayo</option>
                                    <option value="99">Baure</option>
                                    <option value="98">Bauchi</option>
                                    <option value="97">Batsari</option>
                                    <option value="96">Batagarawa</option>
                                    <option value="95">Bassa</option>
                                    <option value="94">Bassa</option>
                                    <option value="93">Baruten</option>
                                    <option value="92">Barkin Ladi</option>
                                    <option value="91">Bade</option>
                                    <option value="90">Bama</option>
                                    <option value="89">Bali</option>
                                    <option value="88">Balanga</option>
                                    <option value="87">Bakura</option>
                                    <option value="86">Bakori</option>
                                    <option value="85">Bokkos</option>
                                    <option value="84">Bakassi</option>
                                    <option value="83">Bagwai</option>
                                    <option value="82">Bagudo</option>
                                    <option value="81">Badagry</option>
                                    <option value="80">Babura</option>
                                    <option value="79">Aiyedire</option>
                                    <option value="78">Aiyedaade</option>
                                    <option value="77">Ayamelum</option>
                                    <option value="76">Awka South</option>
                                    <option value="75">Awka North</option>
                                    <option value="74">Awgu</option>
                                    <option value="73">Awe</option>
                                    <option value="72">Auyo</option>
                                    <option value="71">Augie</option>
                                    <option value="70">Atisbo</option>
                                    <option value="69">Atiba</option>
                                    <option value="68">Atakunmosa West</option>
                                    <option value="67">Atakunmosa East</option>
                                    <option value="66">Askira/Uba</option>
                                    <option value="65">Asari-Toru</option>
                                    <option value="64">Asa</option>
                                    <option value="63">Arochukwu</option>
                                    <option value="62">Argungu</option>
                                    <option value="61">Arewa Dandi</option>
                                    <option value="60">Ardo Kola</option>
                                    <option value="59">Ado</option>
                                    <option value="58">Apapa</option>
                                    <option value="57">Apa</option>
                                    <option value="56">Ankpa</option>
                                    <option value="55">Anka</option>
                                    <option value="54">Aniocha South</option>
                                    <option value="53">Aniocha North</option>
                                    <option value="52">Aninri</option>
                                    <option value="51">Andoni</option>
                                    <option value="50">Anaocha</option>
                                    <option value="49">Anambra West</option>
                                    <option value="48">Anambra East</option>
                                    <option value="47">Amuwo-Odofin</option>
                                    <option value="46">Alkaleri</option>
                                    <option value="45">Alimosho</option>
                                    <option value="44">Aleiro</option>
                                    <option value="43">Albasu</option>
                                    <option value="42">Akwanga</option>
                                    <option value="41">Akure South</option>
                                    <option value="40">Akure North</option>
                                    <option value="39">Akuku-Toru</option>
                                    <option value="38">Akpabuyo</option>
                                    <option value="37">Akoko South-East</option>
                                    <option value="36">Akoko South-West</option>
                                    <option value="35">Akoko North-West</option>
                                    <option value="34">Akoko North-East</option>
                                    <option value="33">Akoko-Edo</option>
                                    <option value="32">Akko</option>
                                    <option value="31">Akinyele</option>
                                    <option value="30">Akamkpa</option>
                                    <option value="29">Ajingi</option>
                                    <option value="28">Ajeromi-Ifelodun</option>
                                    <option value="27">Ajaokuta</option>
                                    <option value="26">Ahoada West</option>
                                    <option value="25">Ahoada East</option>
                                    <option value="24">Ahiazu Mbaise</option>
                                    <option value="23">Aguata</option>
                                    <option value="22">Agege</option>
                                    <option value="21">Agwara</option>
                                    <option value="20">Agatu</option>
                                    <option value="19">Agaie</option>
                                    <option value="18">Afikpo South</option>
                                    <option value="17">Afikpo North</option>
                                    <option value="16">Afijio</option>
                                    <option value="15">Ado-Odo/Ota</option>
                                    <option value="14">Ado Ekiti</option>
                                    <option value="13">Adavi</option>
                                    <option value="12">Abua/Odual</option>
                                    <option value="11">Aboh Mbaise</option>
                                    <option value="10">Abi</option>
                                    <option value="9">Abeokuta South</option>
                                    <option value="8">Abeokuta North</option>
                                    <option value="7">Aba South</option>
                                    <option value="6">Aba North</option>
                                    <option value="5">Abakaliki</option>
                                    <option value="4">Abak</option>
                                    <option value="3">Abaji</option>
                                    <option value="2">Abadam</option>
                </select>
</div>
<div class="col-md-12">
<div class="gwt-Label">Date of Appointment:</div>
<input type="text">
</div>
<div class="col-md-12">
<div class="gwt-Label">Religion:</div>
<select class="gwt-ListBox" name="religion">
 <option value="">Select Religion</option>
                                    <option value="5">Quraniyoon</option>
                                    <option value="4">Ahmadiyya</option>
                                    <option value="3">Sunni</option>
                                    <option value="2">Shia</option>
                                    <option value="1">Sufi</option>
                </select>

</div>
<div class="col-md-12">
<div class="gwt-Label">	Denomination:</div>
<input type="text" >
</div>
<div class="col-md-12">
<div class="gwt-Label">Branch:</div>
<input type="text" name="staff_id" value="" class="nnh">
</div>
<div class="col-md-12">
<div class="gwt-Label">	Genotype:</div>
<input type="text" >
</div>
</div>

<div class="col-md-2 hhf">
       		
	<div class="beddy">
	   <center><span class="bedd"> Picture</span></center>
       <img src="./assets/image/abc.jpg">	
		</div>
		<button onclick="myFunction()">Change>>></button>

		
	<div class="beddy-b">
 <span class="bedd"> Signature</span>
	<input text="text">
		<div class="beddy-c">
		<button onclick="myFunction()">Change>>></button>
		
		</div>
		</div>
		
		
		</div>

</div>
<div class="row desh"></div>
<div class="row">
<div class="col-md-5">
<div class="col-md-12">
<div class="gwt-Label">Address Line 1:</div>
<input type="text">
</div>
<div class="col-md-12">
<div class="gwt-Label">Address Line 2:</div>
<input type="text">
</div>
<div class="col-md-12">
<div class="gwt-Label">City:</div>
<input type="text">
</div>
<div class="col-md-12">
<div class="gwt-Label">Initials:</div>
<input type="text">
</div>
</div>
<div class="col-md-5">
<div class="col-md-12">
<div class="gwt-Label">P.O.Box:</div>
<input type="text">
</div>
<div class="col-md-12">
<div class="gwt-Label">State:</div>
<select class="gwt-ListBox" name="state">
<option>Select state</option>
									<option value="41">Kano</option>
												<option value="40">FCT</option>
												<option value="39">Zamfara</option>
												<option value="38">Yobe</option>
												<option value="37">Taraba</option>
												<option value="36">Sokoto</option>
												<option value="35">Rivers</option>
												<option value="34">Plateau</option>
												<option value="33">Oyo</option>
												<option value="32">Osun</option>
												<option value="31">Ondo</option>
												<option value="30">Ogun</option>
												<option value="29">Niger </option>
												<option value="28">Nasarawa</option>
												<option value="27">Lagos</option>
												<option value="26">Kwara</option>
												<option value="25">Kogi</option>
												<option value="24">Kebbi </option>
												<option value="23">Kebbi </option>
												<option value="22">Katsina</option>
												<option value="21">Kaduna</option>
												<option value="20">Jigawa</option>
												<option value="19">Imo</option>
												<option value="18">Gombe</option>
												<option value="17">Enugu</option>
												<option value="16">Ekiti</option>
												<option value="15">Edo</option>
												<option value="14">Ebonyi</option>
												<option value="13">Delta </option>
												<option value="12">Cross River</option>
												<option value="11">Borno</option>
												<option value="10">Benue</option>
												<option value="9">Bauchi</option>
												<option value="8">Bayelsa</option>
												<option value="7">Akwa Ibom</option>
												<option value="6">Adamawa</option>
												<option value="5">Anambra</option>
												<option value="4">Abia</option>
						
</select>
</div>
<div class="col-md-12">
<div class="gwt-Label">Phone:</div>
<input type="text">
</div>
<div class="col-md-12">
<div class="gwt-Label">Email:</div>
<input type="text">
</div>
</div>
</div>

<div class="row ssd">
<button type="submit" name="add_staff_detail" class="gwt-Button">Save Staff Details</button>
<button type="" class="gwt-Button"><a href="staff.php?action=basic_info"> Add New Staff </a></button>
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

<script>
function myFunction() {
  var x = document.createElement("INPUT");
  x.setAttribute("type", "file");
  document.body.appendChild(x);
}
</script>		

		</body>

		</html>