<?php include('../config.php'); 
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

include('inc.session-create.php'); 
$PageTitle="SUBJECT SPECIFIC COMMENT";
$FileName = 'subject_specific_comment.php';
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



?>
<!DOCTYPE html>
<html>

<head>
<?php include('inc.meta.php'); ?>
<style>

.sectsab  ul {
	
	padding:0px;
}

	#example td {
    padding: 10px 11px 10px 13px;
    border-bottom: 3px solid;
    margin: 0 0 0;
}

.Wizard-a1 .zwq {
    padding-right: 8px;
    float: left;
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
    border: 1px solid #1565c0;
    padding: 4px 3px 4px 3px;
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


<div class="zawq Wizard-a1">

<table id="example" class="display" >
<thead class="setting">
<tr>
<th>Position</th>
<th>Position</th>

</tr>
</thead>

<tbody>
<?php $aryDetail=$db->getRows("select * from school_subject"); 


 
foreach($aryDetail as $iList) {

   
	?>
<tr> 
	<td style="padding:0px;"></td>
	<td class="sectsab <?php if($_GET['class_id']==$iList['id']) { echo "active"; }?>">
	<a href="<?php echo $FileName; ?>?class_id=<?php echo $iList['class_id']?>">
	 <ul><li>
	<span class="zwq"> <img class="table-img" src="https://i0.wp.com/www.winhelponline.com/blog/wp-content/uploads/2017/12/user.png?fit=256%2C256&quality=100&ssl=1" ></span><?php echo $iList['subject']; ?><br>
	<?php echo $db->getVal("select name from school_class where id='".$iList['class_id']."'");?><br>
<?php echo $db->getVal("select session from school_session where id='".$iList['session_id']."'");?>	</li>
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
<div class="col-md-8">
<div class="zasw1">

<div class="card-box">
                         
                                <table  class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                       
                                        <th>Student id</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Other Name</th>
                                        <th>Action</th>

                                    </tr>
                                    </thead>
									  <?php  $aryDetail=$db->getRows("select * from manage_student where class='".$_GET['class_id']."'"); 
						   
						   foreach($aryDetail as $iList) {
						   
						   ?>
                                    <tbody>
                                   
                                        <tr>
                                      <td><?php echo $db->getVal("select  from ");$iList['student_id']; ?></td>
									    <td><?php echo $iList['first_name']; ?></td>
										  <td><?php echo $iList['last_name']; ?></td>
										    <td><?php echo $iList['other-name']; ?></td>
											 <td><a href="">comment </a></td>
                                        </tr>
                                  
                                    </tbody>
									 <?php } ?>
                                </table>
						  
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