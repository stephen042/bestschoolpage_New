<?php include('../config.php');
include('inc.session-create.php');
$PageTitle = "Principal Signature And Next Term Begins";
$FileName = 'principal_sign_term.php';
$validate = new Validation();
if($_SESSION['success']!="")
{
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}
if(isset($_POST['add_nextTerm']))
{
    
	/*$validate->addRule($_POST['session'],'','session',true);
	$validate->addRule($_POST['class'],'','class',true);
	$validate->addRule($_POST['term_id'],'','Term',true);
	$validate->addRule($_POST['nex_term'],'','Begin Date',true);*/

    if($validate->validate() && count($stat) == 0)
    {
		
		if(isset($_FILES["principal_sign"]["name"]) && !empty($_FILES["principal_sign"]["name"]))
			{
				$filename = basename($_FILES['principal_sign']['name']);
				$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
				if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
				{ 	  
					$newfile=md5(time())."_".$filename;
					move_uploaded_file($_FILES['principal_sign']['tmp_name'],"../uploads/".$newfile);
				}				
			}		
				
        $iLastId=$db->getVal("select id from principal_sign_nextTerm order by id desc ")+1;
		$iRandomId=randomFix(15).'-'.$iLastId;        
		 
        $aryData = array(
					'usertype'     	 	         			     =>	$_SESSION['usertype'],
					'userid'     	 	         			     =>	$_SESSION['userid'],
					'sign'     	 	         			         =>	$newfile,
					'session_id'     	 	         			 =>	$_POST['session'],
					'class_id'     	 	         			     =>	$_POST['class'],
					'term_id'     	 	         			     =>	$_POST['term_id'],
					'nextTerm'     	 	         			     =>	$_POST['nex_term'],		
					'create_by_userid'                           => $create_by_userid,
					'create_by_usertype'                         => $create_by_usertype,
					'randomid'     	 	         		         =>	$iRandomId,
					);
				
				$flgIn = $db->insertAry("principal_sign_nextTerm", $aryData);		
				
				$_SESSION['success']="Save successfully.";
				redirect($FileName.'?action=manage_trait');
    }
    else
	{
		$stat['error'] = $validate->errors();
	}
}
elseif(isset($_POST['edit_next_term'])) 
{
   
    
		$aryData = array(
						
					'nextTerm'     	 	         			     =>	$_POST['nex_term'],		
						);
			$flgIn2 = $db->updateAry("principal_sign_nextTerm", $aryData, "where randomid='".$_GET['randomid']."'");
			
			$_SESSION['success']="Saved successfully.";
			redirect($FileName.'?action=manage_trait');
   
}
elseif($_GET['action']=='delete_mas') 
{
	$flgIn1 = $db->delete("principal_sign_nextTerm", "where randomid='".$_GET['randomid']."'");
	$_SESSION['success'] = 'Deleted Successfully';
   redirect($FileName.'?action=manage_trait');
    
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
                <?php if($_GET['action']=='' || $_GET['action']=='manage_trait') {
						$iupdatedetails = $db->getRow("select * from  principal_sign_nextTerm where create_by_userid='".$create_by_userid."'"); 
						
						
						$NewcrearyLisCredtet=$db->getRows("select * from principal_sign_nextTerm where create_by_userid='".$create_by_userid."' order by id desc");
					
						?>
                <!-- Tab panes -->
                <div role="tabpanel" class="tab-pane active" id="home">
                  <div class="ab-1">
                  <?php 	if(count($NewcrearyLisCredtet)=='' || count($NewcrearyLisCredtet)=='0') { ?>
                    <form action="" method="POST" enctype="multipart/form-data">
                      <div class="row">
                        <div class="col-md-1"></div>
                        <div class="col-md-10 col-xs-12">
                          <div class="form-group clearfix">
                            <label class="col-lg-2 control-label " for="userName">Pirncipal Signature </label>
                            <div class="col-lg-10">
                              <input type="file" class="form-control required" id="principal_sign" name="principal_sign" value="<?php echo $_POST['principal_sign']; ?>">
                            </div>
                          </div>
                         <!-- <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="employee_id">Session </label>
                            <div class="col-lg-10">
                              <select  class="required form-control" name="session" id="session"  >
                                <option  value="">Select Session</option>
                                <?php $aryDetail=$db->getRows("select * from  school_session  where create_by_userid='".$create_by_userid."'");
													
                                                    foreach($aryDetail as $iList)
                                                    {	$i=$i+1;?>
                                <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['session']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['session']; ?></option>
                                <?php }?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="employee_id" > Class </label>
                            <div class="col-lg-10">
                              <select  class="required form-control" name="class" id="class_id" onchange="getassesment();" >
                                <option  value="">Select Class</option>
                                <?php $aryDetail=$db->getRows("select * from school_class where create_by_userid='".$create_by_userid."'");
                                                    foreach($aryDetail as $iList)
                                                    {	$i=$i+1;?>
                                <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['class']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['name']; ?></option>
                                <?php }?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="employee_id">Ending Term</label>
                            <div class="col-lg-10">
                              <select  class="required form-control" name="term_id" id="term_id" >
                                <option  value="">Select Term</option>
                                <?php $aryDetail=$db->getRows("select * from  school_term where create_by_userid='".$create_by_userid."'");
                                                    foreach($aryDetail as $iList)
                                                    {	$i=$i+1;?>
                                <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['term_id']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['term']; ?></option>
                                <?php }?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group clearfix">
                            <label class="col-lg-2 control-label " for="userName">Next Term Begins</label>
                            <div class="col-lg-10">
                              <input type="text" class="form-control required" id="nex_term" name="nex_term" placeholder="Eg.January 07, 2019" value="<?php echo $_POST['nex_term']; ?>">
                            </div>
                          </div>-->
                          <div class="form-group clearfix bfrcs ">
                            <div class="col-lg-12 ">
                              <button type="submit" name="add_nextTerm" class="btn"> <i class="fa fa-plus" aria-hidden="true"></i><span>Save</span> </button>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-1"></div>
                      </div>
                    </form>
                    <?php } ?>
                    <div class="row">
                      <div class="col-md-12 col-xs-12">
                        <form action="" method="POST">
                          <div class="card-box table-responsive tablthisresponsive">
                            <table class="table table-striped table-bordered">
                              <thead>
                                <tr>
                                  <th>#</th>
                                  <th>Signature</th>
                                  <!--<th>Session</th>
                                  <th>Class</th>
                                  <th>Ending Term</th>
                                  <th>Next Term Begins</th>
                                  <th>Edit</th>-->
                                  <th>Remove</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php $i=0;
				
						foreach($NewcrearyLisCredtet as $iList)
							{	$i=$i+1;
								$aryPgAct["id"]=$iList['id'];
							 ?>
                                <tr>
                                  <td><?php echo $i ?></td>
                                  <td><img src="../uploads/<?php echo $iList['sign']; ?>" style="width: 70px;"></td>
                                
                                 <!-- <td><?php echo $db->getVal("select session from school_session where id='".$iList['session_id']."'"); ?></td>
                                  <td><?php echo $db->getVal("select name from school_class where id='".$iList['class_id']."'"); ?></td>
                                  <td><?php echo $db->getVal("select term from school_term where id='".$iList['term_id']."'"); ?></td>
                                  
                                  <td><?php if($_GET['randomid']==$iList['randomid']) { ?>
                                    <input type="text" name="nex_term" value="<?php echo $iList['nextTerm']; ?>" class="form-control">
                                    <?php } else { echo $iList['nextTerm']; } ?></td>
                                  <td><?php if($_GET['randomid']==$iList['randomid']) { ?>
                                    <input type="submit" name="edit_next_term" value="SAVE" class="btn btn-primary" style="color:white;">
                                    <?php } else { ?>
                                    <a href="<?php echo $FileName; ?>?action=manage_trait&randomid=<?php echo $iList['randomid']; ?>" class="table-action-btn"> <i class="fa fa-pencil"></i> </a>
                                    <?php } ?></td>-->
                                  <td><a href="javascript:del('<?php echo $FileName; ?>?action=delete_mas&randomid=<?php echo $iList['randomid']; ?>')" class="table-action-btn"> <i class="fa fa-times"></i> </a></td>
                                </tr>
                                <?php } ?>
                              </tbody>
                            </table>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                <?php } ?>
                <!-- Tab panes --> 
                
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
