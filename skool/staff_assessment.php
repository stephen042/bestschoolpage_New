<?php include('../config.php');
include('inc.session-create.php');
$PageTitle = "Manage Assessment";
$FileName = 'staff_assessment.php';
$validate = new Validation();
if($_SESSION['success']!="")
{
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}
if(isset($_POST['addnewrecord']))
{
    
	$validate->addRule($_POST['assessment'],'','Assessment',true);
	$validate->addRule($_POST['mark'],'','Mark',true);

    if($validate->validate() && count($stat) == 0)
    {
        
				$iLastId=$db->getVal("select id from manage_student order by id desc")+1;		
				$randomId=randomFix(15).'-'.$iLastId;

		
		$aryData = array(
					'assessment'     	 	         			 =>	$_POST['assessment'],
					'min_mark'     	 	         			     	 =>	$_POST['min_mark'],
					'mark'     	 	         			     	 =>	$_POST['mark'],
 					'create_by_userid'                           => $create_by_userid,
					'create_by_usertype'                         => $create_by_usertype,
					'randomid'									 =>	$randomId,
 					);
				
				$flgIn = $db->insertAry("staff_assessment", $aryData);		
				
				$_SESSION['success']="Save successfully.";
				redirect($FileName);
    }
    else
	{
		$stat['error'] = $validate->errors();
	}
}
elseif(isset($_POST['updaterecord'])) 
{
   
	$validate->addRule($_POST['assessment'],'','Assessment',true);
	$validate->addRule($_POST['mark'],'','Mark',true);
    if($validate->validate() && count($stat) == 0)
		{

		$aryData = array(
								'assessment'     	 	         			 =>	$_POST['assessment'],
								'min_mark'     	 	         			     	 =>	$_POST['min_mark'],
								'mark'     	 	         			     	 =>	$_POST['mark'],
								'create_by_userid'                           => $create_by_userid,
								'create_by_usertype'                         => $create_by_usertype,
						);
			$flgIn2 = $db->updateAry("staff_assessment", $aryData, "where randomid='".$_GET['randomid']."'");
			
			$_SESSION['success']="Save successfully.";
			redirect($FileName);
		}
    else
	{
		$stat['error'] = $validate->errors();
	}
	
   
}
elseif($_GET['action']=='delete_mas') 
{
	$flgIn1 = $db->delete("staff_assessment", "where randomid='".$_GET['randomid']."'");
	$_SESSION['success'] = 'Deleted Successfully';
    redirect($FileName);
    
}

?>
<!DOCTYPE html>
<html>
<head>
<?php include('inc.meta.php'); ?>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Droid+Serif" />
<style>

body, label, span, a, .gwt-Button {
	
	 font-family: 'Droid Serif' !important; 
}
.abhi .nav-tabs {
	border-bottom: 2px solid #DDD;
}

.abhi .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover {
	border-width: 0;
}

.abhi .nav.nav-tabs > li > a:hover, .nav.tabs-vertical > li > a:hover {
	color: #1B3058 !important;
}

.abhi .nav-tabs > li > a {
	border: none;
	color: #1B3058 !important;
}

.abhi .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover, .tabs-vertical > li.active > a, .tabs-vertical > li.active > a:focus, .tabs-vertical > li.active > a:hover {
	color: #1B3058 !important;
}

.abhi .nav > li > a i {
	font-size: 16px;
	padding-right: 5px;
}

.abhi .nav-tabs > li > a::after {
	content: "";
	background: #1B3058;
	height: 2px;
	position: absolute;
	width: 100%;
	left: 0px;
	bottom: -1px;
	transition: all 250ms ease 0s;
	transform: scale(0);
}

.abhi .nav-tabs > li.active > a::after, .nav-tabs > a::after {
	transform: scale(1);
}

.abhi .tab-nav > li > a::after {
	background: # #5a4080 none repeat scroll 0% 0%;
	color: #fff;
}

.abhi .tab-pane {
	padding: 25px 0;
}

.abhi .tab-content {
	padding: 20px;
}

.abhi .nav-tabs > li {
	
	text-align: center;
}

.abhi .card {
	background: #FFF none repeat scroll 0% 0%;
	box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.3);
	margin-bottom: 30px;
}

.abhi body {
	background: #EDECEC;
	padding: 50px;
}

.abhi .ass .select-hide {
	display: none;
}

.abhi .ass .custom-select select {
	display: none;
}

.abhi .ass .select-selected {
	border-bottom: 1px solid #9e9e9e;
}

.abhi .ass .ytr {
	margin-top: 22px;
}

.abhi .fdg {
	border-bottom: 1px solid #9e9e9e2b;
}

.abhi .shg p {
	color: #1B3058;
}

.abhi .ass .bgb i {
	color: #1B3058;
	font-size: 19px;
}

.abhi .ass .col-md-4 i {
	background: #F44336;
	padding: 8px;
	border-radius: 50%;
	color: #fff;
	font-size: 14px;
}

.abhi .ass .shg {
	padding-top: 29px;
}

.abhi .ass .select-items {
	border: 1px solid #ddd;
	padding: 9px;
	position: relative;
	bottom: 20px;
	background: #fff;
}

.abhi .ass .select-items div {
	padding-bottom: 7px;
}

.abhi .ab-1 {
	text-align: center;
}

.abhi .icon i {
	color: #0b4587;
	background: #fff;
	font-size: 32px;
	border-radius: 50%;
	position: absolute;
	bottom: -25px;
	left: 0;
	right: 0;
	width: 100%;
	margin: 0 auto;
	padding: 15px 10px 15px 10px;
}

.abhi .icon input {
	position: absolute;
	left: 0;
	opacity: 0;
	width: 100%;
	right: 0;
	top: -18px;
}

.abhi .abhish .input-field {
	padding-bottom: 0;
}

.abhi .abh {
	margin-top: 35px;
}

.abhi .input-field input {
	background-color: transparent;
	border: none;
	border-bottom: 1px solid #9e9e9e;
	border-radius: 0;
	outline: none;
	width: 100%;
	margin: 0 0 15px 0;
	padding: 0;
	box-shadow: none;
	box-sizing: content-box;
	transition: all .3s;
}

.abhi .input-field label {
	color: #9e9e9e;
}

.abhi .icon {
	position: relative;
	left: 0;
	bottom: 0;
	width: 7%;
	margin: 0 auto;
	right: 0;
	height: 0;
	top: 0;
}

.abhi .ab-2 {
	background: #0b4587;
	color: #fff;
	width: 23%;
	padding: 28px;
	margin: 0 auto;
}

.abhi .imgage {
	padding-bottom: 13px;
}

.abhi .abb {
	text-align: center;
}

.abhi .ab-3 {
	margin-top: 30px;
}

.abhi .plp {
	margin-bottom: 80px;
}

.abhi .ab-3 .col-md-1 i {
	font-size: 17px;
	color: #000000d6;
}

.abhi .ab-3 .col-md-4 i {
	background: #F44336;
	padding: 8px;
	border-radius: 50%;
	color: #fff;
	font-size: 14px;
}

.abhi .ab-3 input {
	color: rgba(0, 0, 0, 0.26);
	border-bottom: 1px dotted rgba(0, 0, 0, 0.26);
}

.abhi button {
	cursor: pointer;
	float: right;
	background: #1B3058;
	color: #fff;
}

.abhi button:hover {

	background: #1B3058;
	color: #fff;
}

.abhi button i {
	padding-right: 45px;
	font-size: 13px;
}

.abhi .input-field {
	padding-bottom: 20px;
}

.abhi .assde {
	margin-top: 50px;
}

.abhi .ade {
	margin-top: 40px;
}

.abhi .bgb {
	text-align: center;
	padding-top: 3px;
}

.abhi .bgb i {
	color: #1B3058;
	font-size: 19px;
}

@media all and (max-width: 724px) {
	.abhi .nav-tabs > li > a > span {
		display: none;
	}

	.abhi .nav-tabs > li > a {
		padding: 5px 5px;
	}
}


.page-title {
    margin-bottom: 30px;
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
        <h4 class="page-title licat" style="text-align: center;"><?php echo $PageTitle ?></h4>
        <?php echo msg($stat);?> </div>
    </div>
    <!-- Basic Form Wizard -->
    <div class="abhi">
      <div class="container">
        <div class="row">
          <div class="col-md-12"> 
            <!-- Nav tabs -->
            <div class="card">
              <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="<?php if($_GET['action']=='' || $_GET['action']=='manage_trait') { echo "active"; } ?>"> <a href="<?php echo $Filename; ?>?action=manage_trait"> <i class="fa fa-exclamation-circle" aria-hidden="true"></i> <span>Principal Signature And Next Term Begins</span> </a> </li>
              </ul>
              <div class="tab-content">
 
                <div role="tabpanel" class="tab-pane active" id="home">
                  <div class="ab-1">
                 
                    <form action="" method="POST" enctype="multipart/form-data">
                      <div class="row">
                        <div class="col-md-1"></div>
                        <div class="col-md-10 col-xs-12">
                           
                          
                            <div class="form-group clearfix">
                            <label class="col-lg-2 control-label " for="userName">Assessment</label>
                            <div class="col-lg-10">
                              <input type="text" class="form-control required" id="assessment" name="assessment"  value="<?php echo $_POST['assessment']; ?>">
                            </div>
                          </div>
                           
                          <div class="form-group clearfix">
                            <label class="col-lg-2 control-label " for="userName">Mark</label>
                            <div class="col-lg-10">
                              <input type="text" class="form-control required" id="mark" name="mark"  value="<?php echo $_POST['mark']; ?>">
                            </div>
                          </div>
                          
                           <div class="form-group clearfix">
                            <label class="col-lg-2 control-label " for="userName">Min Mark</label>
                            <div class="col-lg-10">
                              <input type="text" class="form-control required" id="min_mark" name="min_mark"  value="<?php echo $_POST['min_mark']; ?>">
                            </div>
                          </div>
                          
                          
                          <div class="form-group clearfix bfrcs ">
                            <div class="col-lg-12 ">
                              <button type="submit" name="addnewrecord" class="btn"> <i class="fa fa-plus" aria-hidden="true"></i><span>Save</span> </button>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-1"></div>
                      </div>
                    </form>
               
                    <div class="row">
                      <div class="col-md-12 col-xs-12">
                       
                          <div class="card-box table-responsive tablthisresponsive">
                            <table class="table table-striped table-bordered">
                              <thead>
                                <tr>
                                  <th>#</th>
                                  <th>Assessment</th>
                                  <th>Mark</th>  
                                  <th>Min Mark</th>
                                  <th>Edit</th>
                                  <th>Remove</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php $i=0;
				$NewcrearyLisCredtet=$db->getRows("select * from staff_assessment where create_by_userid='".$create_by_userid."' order by id desc");
						foreach($NewcrearyLisCredtet as $iList)
							{	$i=$i+1;
								 
							 ?>
                              <form action="" method="POST">
                                <tr>
                                  <td><?php echo $i ?></td>
                                   <td>
                                   <?php if($_GET['randomid']==$iList['randomid']) { ?>
                                   <input type="text" name="assessment" value="<?php echo $iList['assessment']; ?>" class="form-control">
                                    <?php } else { echo $iList['assessment']; } ?></td>
                                   <td>
                                   <?php if($_GET['randomid']==$iList['randomid']) { ?>
                                   <input type="text" name="mark" value="<?php echo $iList['mark']; ?>" class="form-control">
                                    <?php } else { echo $iList['mark']; } ?></td>
                                    
                                    <td>
                                   <?php if($_GET['randomid']==$iList['randomid']) { ?>
                                   <input type="text" name="min_mark" value="<?php echo $iList['min_mark']; ?>" class="form-control">
                                    <?php } else { echo $iList['min_mark']; } ?></td>
                                 
                                 
                                 
                                  <td><?php if($_GET['randomid']==$iList['randomid']) { ?>
                                    <input type="submit" name="updaterecord" value="SAVE" class="btn btn-primary" style="color:white;">
                                    <?php } else { ?>
                                    <a href="<?php echo $FileName; ?>?action=manage_trait&randomid=<?php echo $iList['randomid']; ?>" class="table-action-btn"> <i class="fa fa-pencil"></i> </a>
                                    <?php } ?></td>
                                    
                                    
                                  <td><a href="javascript:del('<?php echo $FileName; ?>?action=delete_mas&randomid=<?php echo $iList['randomid']; ?>')" class="table-action-btn"> <i class="fa fa-times"></i> </a></td>
                                </tr>
                                 </form>
                                <?php } ?>
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
      <?php include('inc.footer.php'); ?>
    </div>
  </div>
   </div>
    </div>

  <?php include('inc.js.php'); ?>
</body>
</html>
