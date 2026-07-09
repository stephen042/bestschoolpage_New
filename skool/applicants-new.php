<?php include('../config.php'); 
//include('inc.session-create.php'); 
$PageTitle="Home Service";
$FileName = 'applicants.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
$stat['success']=$_SESSION['success'];
unset($_SESSION['success']);
}
	if(isset($_POST['submit']))
		{ 
			$validate->addRule($_POST['title'],'','Title',true);
			
			if($validate->validate() && count($stat)==0)
				{
	
					if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
					{	 
					$filename = basename($_FILES['image']['name']);
					$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
					if(in_array($ext1,array('jpg','png', 'gif')))
					{ 	  
					$newfile=md5(time())."_".$filename;
					move_uploaded_file($_FILES['image']['tmp_name'],"../uploads/".$newfile);
					}				
					} 
					
					$aryData=array(	
					'title'     	 	         		=>	$_POST['title'],
					'description'     	 	            =>	$_POST['description'],
					'image'     	 	         		=>	$newfile,
					'status'     	 	         		=>	$_POST['status'],	
					);  
					$flgIn1 = $db->insertAry("service",$aryData);
					
					$_SESSION['success']="Submited Successfully";
					redirect($FileName);
					unset($_POST);
					 
				}
			else {
					$stat['error'] = $validate->errors();
				}
			} 
	elseif(isset($_POST['update']))
		{ 
		if($validate->validate() && count($stat)==0)
		{ 
 			if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
			{	 
			$filename = basename($_FILES['image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
				if(in_array($ext1,array('jpg','png', 'gif')))
				{ 	  
					$newfile=md5(time())."_".$filename;
					move_uploaded_file($_FILES['image']['tmp_name'],"../uploads/".$newfile);
				}				
			}         
			else { $newfile =$_POST['image_old']; }
			
			
					$aryData=array(	
					'title'     	 	         		=>	$_POST['title'],
					'description'     	 	            =>	$_POST['description'],
					'image'     	 	         		=>	$newfile,
					'status'     	 	         	    =>	$_POST['status'],	
					);  
					
					$flgIn = $db->updateAry("service", $aryData , "where id='".$_GET['id']."' ");
					$_SESSION['success']="Update Successfully";
					unset($_POST);
					redirect($FileName);
 			 	
			}	  
			else {
				$stat['error'] = $validate->errors();
			}
		}
		elseif(($_REQUEST['action']=='delete'))
		{
		
			$flgIn1 = $db->delete("service","where id='".$_GET['id']."' ");			
			$_SESSION['success'] = 'Deleted Successfully';
			redirect($FileName);
		} 
?>
<!DOCTYPE html>
<html>
<head>

<?php include('inc.meta.php'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
<style>

.dataTables_paginate .paginate_button {
    
    color: #33333303 !important;
   
}

.next {background-color: #a0989875  !important;
    right: 76px  !important;
    padding: 0 0 0 0  !important;
    border-radius: 29px  !important;
    width: 0  !important;
    top: 2px  !important;
    color: #ffffff08 !important;
    z-index: 9999999 !important;}
	
	.previous {background-color: #a0989875  !important;
    left: -2px  !important;
    padding: 0 0 0 0  !important;
    border-radius: 29px  !important;
    width: 0  !important;
    top: 2px  !important;
    color: #ffffff08 !important;
    z-index: 9999999 !important;}
	
.dataTables_paginate a {}

.Wizard-a1  .paging_simple_numbers span{opacity: 0;}

.Wizard-a1 table.dataTable.display tbody td {
border-top: 1px solid #9a8e8e6b;}


 .Wizard-a1   .paging_simple_numbers{position:relative;}
 
.Wizard-a1 table.dataTable.display tbody tr.odd {
        background-color: #f9f9f903;
}

.Wizard-a1 table.dataTable.display tbody tr.odd>.sorting_1, table.dataTable.order-column.stripe tbody tr.odd>.sorting_1 {
    background-color: #f1f1f105;
}

.Wizard-a1 table.dataTable.display tbody tr:hover>.sorting_1, table.dataTable.order-column.hover tbody tr:hover>.sorting_1 {
    background-color: #eaeaea00;
}
 
 .Wizard-a1 .dataTables_paginate .paginate_button{position:relative;}
 
 .Wizard-a1 .dataTables_paginate.paging_simple_numbers:after{
	 content: "";
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-left: 12px solid #555;
    border-bottom: 6px solid transparent;
    position: absolute;
    z-index: 999999;
    right: 78px;
    bottom: 9px;	 }
	
	.Wizard-a1 .dataTables_paginate.paging_simple_numbers:before{
	 content: "";
    width: 0;
	height: 0;
	border-top: 6px solid transparent;
	border-right: 12px solid #555;
	border-bottom: 6px solid transparent;
    position: absolute;
    z-index: 999999;
    left: 2px;
    bottom: 9px;	 }
 
.Wizard-a1 .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    color: #fffdfd03 !important;
    border: 1px solid #1111110d;
    background-color: #585858;
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #585858), color-stop(100%, #111));
    background: -webkit-linear-gradient(top, #585858 0%, #111 100%);
    background: -moz-linear-gradient(top, #585858 0%, #111 100%);
    background: -ms-linear-gradient(top, #585858 0%, #111 100%);
    background: -o-linear-gradient(top, #585858 0%, #111 100%);
    background: linear-gradient(to bottom, #58585805 0%, #11111133 100%);
}

.Wizard-a1 .dataTables_wrapper .dataTables_paginate .paginate_button {
    box-sizing: border-box;
    display: inline-block;
    min-width: 1.5em;
    padding: 0.5em 1em;
    margin-left: 2px;
    text-align: center;
    text-decoration: none !important;
    cursor: pointer;
    *cursor: hand;
    color: #33333305 !important;
    border: 1px solid transparent;
    border-radius: 2px;
}

 
.Wizard-a1 .dataTables_length{    opacity: 0;     position: absolute;}

.Wizard-a1 .display .setting{    opacity: 0;}

.Wizard-a1 .dataTables_info{margin: 0 auto !important;
    text-align: center;
    font-size: 12px;
    float: initial;
	position: absolute;
	bottom: 11px;
    left: 0;
    right: 0;}

.Wizard-a1 div.dataTables_filter label {
    font-weight: 400;
    white-space: nowrap;
    text-align: left;
    opacity: 0;
	position: absolute;
}

.Wizard-radio-female{margin-left: 15px !important;}
.Wizard-a1 {background-color: white;
           text-align: center;
           padding: 40px 0 40px 0;
		   border: 1px solid #bab3b3;
           box-shadow: 1px 1px #d9d1d1;}

.nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
   cursor: pointer;
    line-height: 38px;
   letter-spacing: .03em;
    font-weight: 300;
    text-transform: capitalize;
	color:black!important;
    
}
		   
.Wizard-a1   .Wizard-a1-a1{margin-top: 15px;}

.Wizard-a1   .Wizard-a1-a1 .Wizard-a1-a1-li{background-color: #efe2e2;
    padding: 0px 5px 0 4px;
    border-radius: 13px;
    text-align: center;
    font-size: 15px;}
	
.Wizard-a1   .Wizard-a1-a1	.Wizard-a1-a1-lin{    margin-right: 15px;}
.Wizard-a1   .Wizard-a1-a1	.Wizard-a1-a1-rin{    margin-left: 15px;}
	
.Wizard-a1   .Wizard-a1-a1 .Wizard-a1-a1-ri{background-color: #efe2e2;
    padding: 0px 4px 0 5px;
    border-radius: 13px;
    text-align: center;
    font-size: 15px;}
	
.Wizard-a1 .Wizard-a1-in{width: 200px; height: 30px;
               margin: 3px 0;
             padding: 6px 12px;
             font-size: 14px;
             line-height: 1.42857;
             color: #555555;
             
             background-color: #fbfbfb;
              background-image: none;
              border: 0px solid #ddd;
              border-radius: 3px;
             box-shadow: inset 1px 1px #e5dbdb;}
	
.Wizard-a1 .Wizard-a1-but   {margin-left: -4px;
                width: 63px;
                height: 30px;
                background-color: white;
                border: 1px solid blue;
               color: blue;
                border-radius: 5px;
             font-size: 12px;}

.Wizard-a1 .Wizard-a1-but:hover{background-color: blue;color: white;}

.Wizard-radio{display:flex;}

.Wizard-a1 .Wizard-a1-ul {padding: 0;
    list-style: none;
    text-align: justify;
    width: 72%;
    margin: 0 auto;
    margin-top: 35px;}
			 
.Wizard-a1 .Wizard-a1-ul .Wizard-a1-li{padding: 6px 0 6px 0; border-bottom: 1px solid #9d9b9b; font-size: 13px;}  
.Wizard-a1 .Wizard-a1-ul .Wizard-a1-li i{font-size: 41px;}  
.Wizard-a1 .Wizard-a1-ul .Wizard-a1-li:hover{background-color: blue;color: white;}

.Wizard-a2{background-color: white;}

.Wizard-radio-female{margin-left: 15px;}

 .Wizard-a2-1{padding: 15px 0 15px 15px;
                      width: 76%;
                     border: 1px solid #bab2b2;
                     box-shadow: 1px 1px #c0b9b9;
					     background-color: white;}

 .Wizard-a2-1 .Wizard-a2-but{height: 30px;
                                 background-color: white;
                                 border: 1px solid blue;
                                 color: blue;
                                 border-radius: 5px;
                                 font-size: 12px;
								 margin-left: 4px;}
			 
.Wizard-a2-1 .Wizard-a2-but:hover{background-color: blue;color: white;}

.Wizard-a2 .Wizard-a2-ul{list-style: none;
                       color: black;
                       
                      font-size: 13px;}

.Wizard-a2 .Wizard-a2-ul .Wizard-a2-li{padding: 5px 0 5px 0; margin-bottom: 8px;}

.Wizard-a2 .Wizard-a2-ul2{list-style: none;
                      color: #3a3737;
                     
                      font-size: 14px;}
					  
.Wizard-a2 .Wizard-a2-ul2 div {margin-bottom: 8px;}	

.Wizard-a2 .Wizard-a2-in{width: 200px; height: 30px;
               
             padding: 6px 12px;
             font-size: 14px;
             line-height: 1.42857;
             color: #555555;
            
             background-color: #fbfbfb;
              background-image: none;
              border: 0px solid #ddd;
              border-radius: 3px;
             box-shadow: inset 1px 1px #e5dbdb;}
			 
 .Wizard-new{padding-right:0;}
 
select{height: 30px;
        padding: 0 21px 0 21px;
		     background-color: #fbfbfb;
            background-image: none;
    border: 0px solid #ddd;
    border-radius: 3px;
    box-shadow: inset 1px 1px #e5dbdb;} 
	
.Wizard-line	{    border: 1px dashed black;
}
.Wizard-a2-but1{height: 30px;
                                 background-color: white;
                                 border: 1px solid blue;
                                 color: blue;
                                 border-radius: 5px;
                                 font-size: 12px;}
								 
.Wizard-a2-but1:hover{background-color: blue;color: white;}

.Wizard-a2 .Wizard-a3-in{width: 420px; height: 30px;
               
             padding: 6px 12px;
             font-size: 14px;
             line-height: 1.42857;
             color: #555555;
            
             background-color: #fbfbfb;
              background-image: none;
              border: 0px solid #ddd;
              border-radius: 3px;
             box-shadow: inset 1px 1px #e5dbdb;}
			 
.Wizard-a2 .Wizard-a3-ul {
    list-style: none;
    color: black;
    padding: 30px 0 30px 35px;
    font-size: 13px;
}	


.Wizard-a2 .panel.with-nav-tabs .panel-heading{
    padding: 5px 5px 0 5px;
}
.Wizard-a2 .panel.with-nav-tabs .nav-tabs{
	border-bottom: none;
}
.Wizard-a2 .panel.with-nav-tabs .nav-justified{
	margin-bottom: -1px;
}
/********************************************************************/
/*** PANEL DEFAULT ***/
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li > a,
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li > a:hover,
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li > a:focus {
    color: #777;
}
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > .open > a,
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > .open > a:hover,
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > .open > a:focus,
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li > a:hover,
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li > a:focus {
    color: #777;
	background-color: #ddd;
	border-color: transparent;
}
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li.active > a,
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li.active > a:hover,
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li.active > a:focus {
	color: #555;
	background-color: #fff;
	border-color: #ddd;
	border-bottom-color: transparent;
}
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu {
    background-color: #f5f5f5;
    border-color: #ddd;
}
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > li > a {
    color: #777;   
}
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > li > a:hover,
.with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > li > a:focus {
    background-color: #ddd;
}
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > .active > a,
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > .active > a:hover,
.Wizard-a2 .with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > .active > a:focus {
    color: #fff;
    background-color: #555;
}

.panel-default>.panel-heading {
    background-color: #ffffff;
    border-bottom: none;
    color: #797979;
}

.nav-tabs .nav-tabs-li{background-color: whitesmoke;
    color: black;
    font-size: 12px;
    border-top-left-radius: 7px;
    border-top-right-radius: 7px;
	height: 40px;
    width: 110px;
    margin-left: 7px;
	text-align:center;}

 .nav-tabs .active a{border-bottom: 4px solid blue;}	

.nav.nav-tabs>li.active>a {
    background-color: #fff;
    border: 0;
    border-bottom: 2px solid blue;
    padding-bottom: 0;
	color: black !important;
    font-weight: 600;
} 
.Wizard-a2-n .Wizard-a2-n1{text-align: right;
    margin-top: 6px;}

.Wizard-a2-n {}
.Wizard-a2-n .Wizard-a2-n1 .Wizard-a2-n1in{padding: 6px 12px; font-size: 14px;
    line-height: 1.42857;
    color: #555555;
    vertical-align: middle;
    background-color: #fbfbfb;
        border: 0px solid #ddd;
    border-radius: 3px;
	box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
	transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;}
	
.Wizard-a1 .Wizard-table-1{    width: 100%;    text-align: left;
    padding-left: 40px;}
	
.Wizard-a1 .Wizard-table-tr:hover{ background-color: blue !important;
    color: white;}
	
.Wizard-a1	.Wizard-table-img{width:50px; height:50px;}

.table-img{width:50px; height:50px;}
tr:hover{background-color:blue !important;
       color:white !important;}
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
            <ol class="breadcrumb">
              <li> <a href="<?php echo $iClassName; ?>">Home</a> </li>
              <li class="active"> <?php echo $PageTitle; ?> </li>
            </ol>
          </div>
        </div>
        <!-- Basic Form Wizard -->
     
<div class="row">
  <div class="col-md-4">
       <div class="Wizard-a1">
	       <input class="Wizard-a1-in" type="text">
		   <button class="Wizard-a1-but">Search</button>
		   
		   
		   
<table id="example" class="display" style="width:100%">
        <thead class="setting">
            <tr>
                <th>Position</th>
                <th>Position</th>
                
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><img class="table-img" src="https://i0.wp.com/www.winhelponline.com/blog/wp-content/uploads/2017/12/user.png?fit=256%2C256&quality=100&ssl=1"></td>
                <td>System Architect</td>
                
            </tr>
            <tr>
                <td><img class="table-img" src="https://i0.wp.com/www.winhelponline.com/blog/wp-content/uploads/2017/12/user.png?fit=256%2C256&quality=100&ssl=1"></td>
                <td>Accountant</td>
                
            </tr>
            <tr>
                <td>Ashton Cox</td>
                <td>Junior Technical Author</td>
                
            </tr>
            <tr>
                <td>Cedric Kelly</td>
                <td>Senior Javascript Developer</td>
               
            </tr>
            <tr>
                <td>Airi Satou</td>
                <td>Accountant</td>
                
            </tr>
            <tr>
                <td>Brielle Williamson</td>
                <td>Integration Specialist</td>
                
            </tr>
            <tr>
                <td>Herrod Chandler</td>
                <td>Sales Assistant</td>
                
            </tr>
            <tr>
                <td>Rhona Davidson</td>
                <td>Integration Specialist</td>
                
            </tr>
            <tr>
                <td>Colleen Hurst</td>
                <td>Javascript Developer</td>
               
            </tr>
            <tr>
                <td>Sonya Frost</td>
                <td>Software Engineer</td>
                
            </tr>
            <tr>
                <td>Jena Gaines</td>
                <td>Office Manager</td>
                
            </tr>
            <tr>
                <td>Quinn Flynn</td>
                <td>Quinn Flynn</td>
                
            </tr>
            <tr>
                <td>Charde Marshall</td>
                <td>Regional Director</td>
               
            </tr>
            <tr>
                <td>Haley Kennedy</td>
                <td>Senior Marketing Designer</td>
                
            </tr>
            <tr>
                <td>Tatyana Fitzpatrick</td>
                <td>Regional Director</td>
                
            </tr>
            <tr>
                <td>Michael Silva</td>
                <td>Marketing Designer</td>
               
            </tr>
            <tr>
                <td>Paul Byrd</td>
                <td>Chief Financial Officer (CFO)</td>
                
            </tr>
            <tr>
                <td>Gloria Little</td>
                <td>Systems Administrator</td>
               
            </tr>
            <tr>
                <td>Bradley Greer</td>
                <td>Software Engineer</td>
              
            </tr>
            <tr>
                <td>Dai Rios</td>
                <td>Personnel Lead</td>
                
            </tr>
            <tr>
                <td>Jenette Caldwell</td>
                <td>Development Lead</td>
               
            </tr>
            <tr>
                <td>Yuri Berry</td>
                <td>Yuri Berry</td>
                
            </tr>
            <tr>
                <td>Caesar Vance</td>
                <td>Pre-Sales Support</td>
                
            </tr>
            <tr>
                <td>Doris Wilder</td>
                <td>Sales Assistant</td>
                
            </tr>
            <tr>
                <td>Angelica Ramos</td>
                <td>Chief Executive Officer (CEO)</td>
               
            </tr>
            <tr>
                <td>Gavin Joyce</td>
                <td>Developer</td>
                
            </tr>
            <tr>
                <td>Jennifer Chang</td>
                <td>Regional Director</td>
               
            </tr>
            <tr>
                <td>Brenden Wagner</td>
                <td>Software Engineer</td>
                
            </tr>
            <tr>
                <td>Fiona Green</td>
                <td>Chief Operating Officer (COO)</td>
               
            </tr>
            <tr>
                <td>Shou Itou</td>
                <td>Regional Marketing</td>
                
            </tr>
            <tr>
                <td>Michelle House</td>
                <td>Integration Specialist</td>
               
			   
            </tr>
            <tr>
                <td>Suki Burks</td>
                <td>Developer</td>
                
            </tr>
            <tr>
                <td>Prescott Bartlett</td>
                <td>Technical Author</td>
               
            </tr>
            <tr>
                <td>Gavin Cortez</td>
                <td>Team Leader</td>
                
            </tr>
            <tr>
                <td>Martena Mccray</td>
                <td>Post-Sales support</td>
                
            </tr>
            <tr>
                <td>Unity Butler</td>
                <td>Marketing Designer</td>
                
            </tr>
            <tr>
                <td>Howard Hatfield</td>
                <td>Office Manager</td>
                
				
            </tr>
            <tr>
                <td>Hope Fuentes</td>
                <td>Secretary</td>
               
            </tr>
            <tr>
                <td>Vivian Harrell</td>
                <td>Financial Controller</td>
               
            </tr>
            <tr>
                <td>Timothy Mooney</td>
                <td>Office Manager</td>
                
            </tr>
            <tr>
                <td>Jackson Bradshaw</td>
                <td>Director</td>
                
            </tr>
            <tr>
                <td>Olivia Liang</td>
                <td>Support Engineer</td>
                
            </tr>
            <tr>
                <td>Bruno Nash</td>
                <td>Software Engineer</td>
                
				
            </tr>
            <tr>
                <td>Sakura Yamamoto</td>
                <td>Support Engineer</td>
               
			   
            </tr>
            <tr>
                <td>Thor Walton</td>
                <td>Developer</td>
                
				
            </tr>
            <tr>
                <td>Finn Camacho</td>
                <td>Support Engineer</td>
                
				
            </tr>
            <tr>
                <td>Serge Baldwin</td>
                <td>Serge Baldwin</td>
               
			   
            </tr>
            <tr>
                <td>Zenaida Frank</td>
                <td>Software Engineer</td>
                
				
            </tr>
            <tr>
                <td>Zorita Serrano</td>
                <td>Software Engineer</td>
               
            </tr>
            <tr>
                <td>Jennifer Acosta</td>
                <td>Junior Javascript Developer</td>
               
            </tr>
            <tr>
                <td>Cara Stevens</td>
                <td>Sales Assistant</td>
                
            </tr>
            <tr>
                <td>Hermione Butler</td>
                <td>Regional Director</td>
               
            </tr>
            <tr>
                <td>Lael Greer</td>
                <td>Systems Administrator</td>
                
            </tr>
            <tr>
                <td>Jonas Alexander</td>
                <td>Developer</td>
               
			   
            </tr>
            <tr>
                <td>Shad Decker</td>
                <td>Regional Director</td>
               
			   
            </tr>
            <tr>
                <td>Michael Bruce</td>
                <td>Javascript Developer</td>
               
			   
            </tr>
            <tr>
                <td>Donna Snider</td>
                <td>Customer Support</td>
               
			   
            </tr>
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
  
  <div class="col-md-7" style="background-color:white;">
       <div class="Wizard-a2-1">
			     <button class="Wizard-a2-but">Delete New Staff</button>
			     <button class="Wizard-a2-but">Update Staff ID</button>
			     <button class="Wizard-a2-but">Print Staff Profile</button>
			     <button class="Wizard-a2-but">Print Staff Full Profile</button>
             </div>
            <div class="panel with-nav-tabs panel-default">
                <div class="panel-heading">
                        <ul class="nav nav-tabs">
                            <li class="active nav-tabs-li"><a href="#tab1default" data-toggle="tab">Default 1</a></li>
                            <li class="nav-tabs-li"><a href="#tab2default" data-toggle="tab">Default 2</a></li>
                            <li class="nav-tabs-li"><a href="#tab3default" data-toggle="tab">Default 3</a></li>
							<li class="nav-tabs-li"><a href="#tab4default" data-toggle="tab">Default 4</a></li>
                            <li class="nav-tabs-li"><a href="#tab5default" data-toggle="tab">Default 5</a></li>
                            
                        </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                         <div class="tab-pane fade in active" id="tab1default">
						             <div class="Wizard-a2">
		     	
     <div class="row">
	    <div class="col-md-6">
		   <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		   </div>
		   
		   <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <select>
                                  <option value="volvo">Volvo</option>
                                  <option value="saab">Saab</option>
                                  <option value="opel">Opel</option>
                                  <option value="audi">Audi</option>
                                   </select></div> </div> 
			 </div>
		    </div>
			
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
			
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		  
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		  
		   <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <select>
                                  <option value="volvo">Volvo</option>
                                  <option value="saab">Saab</option>
                                  <option value="opel">Opel</option>
                                  <option value="audi">Audi</option>
                                   </select></div> </div> 
			 </div>
		    </div>
			
		
		   <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <select>
                                  <option value="volvo">Volvo</option>
                                  <option value="saab">Saab</option>
                                  <option value="opel">Opel</option>
                                  <option value="audi">Audi</option>
                                   </select></div> </div> 
			 </div>
		    </div>
			
		
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		  
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		
		   <div class="row">
		        
				
		   </div>
		</div>
		
		 <div class="col-md-6">
		       <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div><form class="Wizard-radio" action="">
                           <input type="radio" name="gender" value="male"> Male<br>
                            <input class="Wizard-radio-female" type="radio" name="gender" value="female"> Female<br>
                            
                            </form> </div> </div> 
			 </div>
		   </div>
		 
		        
		   <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <select>
                                  <option value="volvo">Volvo</option>
                                  <option value="saab">Saab</option>
                                  <option value="opel">Opel</option>
                                  <option value="audi">Audi</option>
                                   </select></div> </div> 
			 </div>
		    </div>
		
		 
		   <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <select>
                                  <option value="volvo">Volvo</option>
                                  <option value="saab">Saab</option>
                                  <option value="opel">Opel</option>
                                  <option value="audi">Audi</option>
                                   </select></div> </div> 
			 </div>
		    </div>
		
		<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		   </div>
		  
		  <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		   </div>
		  
		  <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		   </div>
		  
		  <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <select>
                                  <option value="volvo">Volvo</option>
                                  <option value="saab">Saab</option>
                                  <option value="opel">Opel</option>
                                  <option value="audi">Audi</option>
                                   </select></div> </div> 
			 </div>
		    </div>
			
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		   </div>
		  
		  <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		   </div>
		  
			
		   
		</div>
		
     </div>	
                     <hr class="Wizard-line">	

      <div class="row">
	    <div class="col-md-6">
		        <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
			
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		  
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		   <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		
		   
		</div>
		
		 <div class="col-md-6">
		        <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
			
			 <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <select>
                                  <option value="volvo">Volvo</option>
                                  <option value="saab">Saab</option>
                                  <option value="opel">Opel</option>
                                  <option value="audi">Audi</option>
                                   </select></div> </div> 
			 </div>
		    </div>
		
		  
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		   <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		
		   
		</div>
		
     </div>	
            <button class="Wizard-a2-but1">add new staaf  </button> 
		<button class="Wizard-a2-but1">add new staaf  </button> 
					 
		 </div>
		
						
						
						</div>
                        
						
						
						<div class="tab-pane fade" id="tab2default">
						
						      <div class="Wizard-a2">
	 <div class="row">
	    <div class="col-md-6">
		        <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
			
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		  
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		   <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		
		   
		</div>
		
		 <div class="col-md-6">
		        <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
			
			 <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <select>
                                  <option value="volvo">Volvo</option>
                                  <option value="saab">Saab</option>
                                  <option value="opel">Opel</option>
                                  <option value="audi">Audi</option>
                                   </select></div> </div> 
			 </div>
		    </div>
		
		  
			<div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		   <div class="row">
		      <div class="col-md-3 Wizard-new">
			  <div class="Wizard-a2-ul"> <div class="Wizard-a2-li">Staff ID: * </div>   </div>
			  </div>
			 <div class="col-md-9">
			  <div class="Wizard-a2-ul2">  <div> <input class="Wizard-a2-in" type="text"> </div> </div> 
			 </div>
		    </div>
		  
		
		   
		</div>
		
     </div>	           

            <button class="Wizard-a2-but1">add new staaf  </button> 
		<button class="Wizard-a2-but1">add new staaf  </button> 
					 
		 </div>
		
						
						</div>
                        
						
						<div class="tab-pane fade" id="tab3default">
						     <div class="Wizard-a2">
							    <div class="row">
								    <div class="col-md-7">
									    <button class="Wizard-a2-but1">add new staaf  </button> 
		                         <button class="Wizard-a2-but1">add new staaf  </button> 
		                         <button class="Wizard-a2-but1">add new staaf  </button> 
								 <div class="Wizard-a2-n">
								       <div class="Wizard-a2-n1">
									       <input class="Wizard-a2-n1in" placeholder="Enter Some Text">
										   <div class="row">
										      <div class="col-md-4"> </div>
										      <div class="col-md-4"> </div>
										      <div class="col-md-4"> </div>
										   </div>
									   </div>
								 
								 </div>
								    </div>
									<div class="col-md-4"> </div>
								</div>
							     
							         
							 </div>
						
						</div>
						    
						
						
                        <div class="tab-pane fade" id="tab4default"></div>
						
                        <div class="tab-pane fade" id="tab5default">
						          <div class="Wizard-a2">
		     
     <div class="row">
	    <div class="col-md-12">
		   <div class="row">
		        <div class="col-md-3 Wizard-new">
				   <div class="Wizard-a2-ul">  <div class="Wizard-a2-li">Staff ID: * </div> </div></div>
				   <div class="col-md-9"> <div class="Wizard-a2-ul2"> <div> <input class="Wizard-a3-in" type="text"> </div>  </div> </div>
				
		   </div>
		   
		    <div class="row">
		        <div class="col-md-3 Wizard-new">
				   <div class="Wizard-a2-ul">  <div class="Wizard-a2-li">Staff ID: * </div> </div></div>
				   <div class="col-md-9"> <div class="Wizard-a2-ul2"> <div> <input class="Wizard-a3-in" type="text"> </div>  </div> </div>
				
		   </div>
		   
		   
		    <div class="row">
		        <div class="col-md-3 Wizard-new">
				   <div class="Wizard-a2-ul">  <div class="Wizard-a2-li">Staff ID: * </div> </div></div>
				   <div class="col-md-9"> <div class="Wizard-a2-ul2"> <div> <input class="Wizard-a3-in" type="text"> </div>  </div> </div>
				
		   </div>
		   
		   
		    <div class="row">
		        <div class="col-md-3 Wizard-new">
				   <div class="Wizard-a2-ul">  <div class="Wizard-a2-li">Staff ID: * </div> </div></div>
				   <div class="col-md-9"> <div class="Wizard-a2-ul2"> <div> <input class="Wizard-a3-in" type="text"> </div>  </div> </div>
				
		   </div>
		   
		   
		   
		   
		  </div>
		
		 
     </div>	
                   

            
		<button class="Wizard-a2-but1">add new staaf  </button> 
					 
		 </div>
	
						
						
						</div>
                    </div>
                </div>
            </div>
       
        
	
		
  </div>
  
  <div class="col-md-1">
  </div>
</div>





















	 </div>
    </div>
  </div>
  <?php include('inc.footer.php'); ?>
</div>
</div>
<?php include('inc.js.php'); ?>
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#example').DataTable();
} );
</script>
</body>
</html>
