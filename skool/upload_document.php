<?php include('../config.php');
include('inc.session-create.php');
$PageTitle = "Upload Documents";
$FileName = 'upload_document.php';
$validate = new Validation();
if($_SESSION['success']!="")
{
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}
$igetId=$db->getRow("select id from skool_settings where userid='".$_SESSION['userid']."' and usertype='".$_SESSION['usertype']."' and create_by_userid='".$create_by_userid."' and create_by_usertype='".$create_by_usertype."'  ");

if(isset($_POST['upload_document']))
{
    if($validate->validate() && count($stat) == 0)
    {
        if(isset($_FILES["skool_syllabus"]["name"]) && !empty($_FILES["skool_syllabus"]["name"]))
			{
				$filename1 = basename($_FILES['skool_syllabus']['name']);
				$ext1 = strtolower(substr($filename1, strrpos($filename1, '.')+1));				  
				$newfile1=md5(time())."_".$filename1;
				move_uploaded_file($_FILES['skool_syllabus']['tmp_name'],"../uploads/".$newfile1);
								
			}
			
			if(isset($_FILES["learning_materials"]["name"]) && !empty($_FILES["learning_materials"]["name"]))
			{
				$filename2 = basename($_FILES['learning_materials']['name']);
				$ext2 = strtolower(substr($filename2, strrpos($filename2, '.')+1));				  
				$newfile2=md5(time())."_".$filename2;
				move_uploaded_file($_FILES['learning_materials']['tmp_name'],"../uploads/".$newfile2);
								
			}
			
			if(isset($_FILES["exam_timetable"]["name"]) && !empty($_FILES["exam_timetable"]["name"]))
			{
				$filename3 = basename($_FILES['exam_timetable']['name']);
				$ext3 = strtolower(substr($filename3, strrpos($filename3, '.')+1));				  
				$newfile3=md5(time())."_".$filename3;
				move_uploaded_file($_FILES['exam_timetable']['tmp_name'],"../uploads/".$newfile3);
								
			}
			
			if(isset($_FILES["exam_pass_question"]["name"]) && !empty($_FILES["exam_pass_question"]["name"]))
			{
				$filename4 = basename($_FILES['exam_pass_question']['name']);
				$ext4 = strtolower(substr($filename4, strrpos($filename4, '.')+1));				  
				$newfile4=md5(time())."_".$filename4;
				move_uploaded_file($_FILES['exam_pass_question']['tmp_name'],"../uploads/".$newfile4);
								
			}
				if(isset($_FILES["admission_list"]["name"]) && !empty($_FILES["admission_list"]["name"]))
			{
				$filename5= basename($_FILES['admission_list']['name']);
				$ext5 = strtolower(substr($filename5, strrpos($filename5, '.')+1));				  
				$newfile5=md5(time())."_".$filename5;
				move_uploaded_file($_FILES['admission_list']['tmp_name'],"../uploads/".$newfile5);
								
			}
			
        $aryData = array(
								
								'userid'                                     =>  $_SESSION['userid'],
                                'usertype'                                   =>  $_SESSION['usertype'],
			                    'create_by_userid'                           =>  $create_by_userid,
                                'create_by_usertype'                         =>  $create_by_usertype,
								'skool_syllabus'                             =>  $newfile1,
                                'learning_materials'                         =>  $newfile2,
			                    'exam_timetable'                             =>  $newfile3,
                                'exam_pass_question'                         =>  $newfile4,
								'admission_list'                             =>  $newfile5,
						);
				
				$flgIn = $db->insertAry("skool_settings", $aryData);
										
				$_SESSION['success']="Uploaded successfully.";
				redirect($FileName.'?action=upload_document');
				
    }
    else
	{
		$stat['error'] = $validate->errors();
	}
}


elseif(isset($_POST['update_document'])) 
{
	   if($validate->validate() && count($stat) == 0)
    {
        if(isset($_FILES["skool_syllabus"]["name"]) && !empty($_FILES["skool_syllabus"]["name"]))
			{
				$filename1 = basename($_FILES['skool_syllabus']['name']);
				$ext1 = strtolower(substr($filename1, strrpos($filename1, '.')+1));				  
				$newfile1=md5(time())."_".$filename1;
				move_uploaded_file($_FILES['skool_syllabus']['tmp_name'],"../uploads/".$newfile1);
								
			}
			else{
				$newfile1=$_POST['skool_syllabusold'];
			}
			
			if(isset($_FILES["learning_materials"]["name"]) && !empty($_FILES["learning_materials"]["name"]))
			{
				$filename2 = basename($_FILES['learning_materials']['name']);
				$ext2 = strtolower(substr($filename2, strrpos($filename2, '.')+1));				  
				$newfile2=md5(time())."_".$filename2;
				move_uploaded_file($_FILES['learning_materials']['tmp_name'],"../uploads/".$newfile2);
								
			}
			else{
				$newfile2=$_POST['learning_materialsold'];
			}
			if(isset($_FILES["exam_timetable"]["name"]) && !empty($_FILES["exam_timetable"]["name"]))
			{
				$filename3 = basename($_FILES['exam_timetable']['name']);
				$ext3 = strtolower(substr($filename3, strrpos($filename3, '.')+1));				  
				$newfile3=md5(time())."_".$filename3;
				move_uploaded_file($_FILES['exam_timetable']['tmp_name'],"../uploads/".$newfile3);
								
			}
			else{
				$newfile3=$_POST['exam_timetableold'];
			}
			if(isset($_FILES["exam_pass_question"]["name"]) && !empty($_FILES["exam_pass_question"]["name"]))
			{
				$filename4 = basename($_FILES['exam_pass_question']['name']);
				$ext4 = strtolower(substr($filename4, strrpos($filename4, '.')+1));				  
				$newfile4=md5(time())."_".$filename4;
				move_uploaded_file($_FILES['exam_pass_question']['tmp_name'],"../uploads/".$newfile4);
								
			}
			else{
				$newfile4=$_POST['exam_pass_questionold'];
			}
			if(isset($_FILES["admission_list"]["name"]) && !empty($_FILES["admission_list"]["name"]))
			{
				$filename5 = basename($_FILES['admission_list']['name']);
				$ext4 = strtolower(substr($filename5, strrpos($filename5, '.')+1));				  
				$newfile5=md5(time())."_".$filename5;
				move_uploaded_file($_FILES['admission_list']['tmp_name'],"../uploads/".$newfile5);
								
			}
			else{
				$newfile5=$_POST['admission_listold'];
			}
        $aryData = array(
								
								'userid'                                     =>  $_SESSION['userid'],
                                'usertype'                                   =>  $_SESSION['usertype'],
			                    'create_by_userid'                           =>  $create_by_userid,
                                'create_by_usertype'                         =>  $create_by_usertype,
								'skool_syllabus'                             =>  $newfile1,
                                'learning_materials'                         =>  $newfile2,
			                    'exam_timetable'                             =>  $newfile3,
                                'exam_pass_question'                         =>  $newfile4,
								'admission_list'                             =>  $newfile5,
						);
				
				$flgIn2 = $db->updateAry("skool_settings", $aryData, "where id='".$igetId['id']."'");						
				$_SESSION['success']="Uploaded successfully.";
				redirect($FileName.'?action=upload_document');
    }
    else
	{
		$stat['error'] = $validate->errors();
	}
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
	width: 30%;
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
    <?php include('inc.sideleft.php');
if($iPackageJsoneDecodeAllowFile['document_upload']!='1') { 
redirect(SKOOL_URL);
}
	
	 ?>
<div class="content-page">
	<!-- Start content -->
<div class="content">
<div class="container">
	<!-- Page-Title -->
	<div class="row">
		<div class="col-sm-12">
			<h4 class="page-title licat" style="text-align: center;"><?php echo $PageTitle ?></h4>
			<?php echo msg($stat);?>
		</div>
	</div>
	<!-- Basic Form Wizard -->
	<div class="abhi">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<!-- Nav tabs -->
					<div class="card">
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="<?php if($_GET['action']=='' || $_GET['action']=='upload_document') { echo "active"; } ?>">
								<a href="<?php echo $Filename; ?>?action=upload_document">
									<i class="fa fa-exclamation-circle" aria-hidden="true"></i> <span>Upload Documents</span>
								</a>
                            </li>
							
							

						</ul>
						<div class="tab-content">
						<?php if($_GET['action']=='' || $_GET['action']=='upload_document') {
						$iupdatedetails = $db->getRow("select * from  skool_settings where id='".$igetId['id']."'"); 
						?>
							<!-- Tab panes -->
							 <div role="tabpanel" class="tab-pane active" id="home">
                            <div class="ab-1">
								<form action="" method="POST" enctype="multipart/form-data">
								<div class="row">
									<div class="col-md-1"></div>
									<div class="col-md-10">
									
										

										<div class="form-group clearfix">
                                            <label class="col-lg-5 control-label" for="employee_id">School Syllabus</label>
											<div class="col-lg-5">
											<input type="file" class="form-control required" id="skool_syllabus" name="skool_syllabus" value="<?php echo $_POST['skool_syllabus']; ?>">
                                            </div>
                                            <?php if($iupdatedetails['skool_syllabus']!='') { ?>
                                         <a href="../uploads/<?php echo $iupdatedetails['skool_syllabus']; ?>" class="col-lg-2" download >Download</a>
                                         	<?php } ?>
										</div>
										<div class="form-group clearfix">
                                            <label class="col-lg-5 control-label" for="employee_id">Learning material for all classes</label>
											<div class="col-lg-5">
											<input type="file" class="form-control required" id="learning_materials" name="learning_materials" value="<?php echo $_POST['learning_materials']; ?>">
                                            </div>
                                            <?php if($iupdatedetails['learning_materials']!='') { ?>
                                          <a href="../uploads/<?php echo $iupdatedetails['learning_materials']; ?>" class="col-lg-2" download >Download</a>
                                          <?php } ?>
										</div>
										<div class="form-group clearfix">
                                            <label class="col-lg-5 control-label" for="employee_id">Exam timetable</label>
											<div class="col-lg-5">
											<input type="file" class="form-control required" id="exam_timetable" name="exam_timetable" value="<?php echo $_POST['exam_timetable']; ?>">
                                            </div>
                                            	<?php if($iupdatedetails['exam_timetable']!='') { ?>
											<a href="../uploads/<?php echo $iupdatedetails['exam_timetable']; ?>" class="col-lg-2" download >Download</a>
                                            <?php } ?>
										</div>
										<div class="form-group clearfix">
                                            <label class="col-lg-5 control-label" for="employee_id"> Exam pass questions </label>
											<div class="col-lg-5">
											<input type="file" class="form-control required" id="exam_pass_question" name="exam_pass_question" value="<?php echo $_POST['exam_pass_question']; ?>">
                                            </div>
                                            	<?php if($iupdatedetails['exam_pass_question']!='') { ?>
                                              <a href="../uploads/<?php echo $iupdatedetails['exam_pass_question']; ?>" class="col-lg-2" download >Download</a>
                                              <?php } ?>
										</div>
										<div class="form-group clearfix">
                                            <label class="col-lg-5 control-label" for="employee_id"> Admission List </label>
											<div class="col-lg-5">
											<input type="file" class="form-control required" id="admission_list" name="admission_list" value="<?php echo $_POST['admission_list']; ?>">
                                            </div>
                                            	<?php if($iupdatedetails['admission_list']!='') { ?>
                                              <a href="../uploads/<?php echo $iupdatedetails['admission_list']; ?>" class="col-lg-2" download >Download</a>
                                              <?php } ?>
										</div>
										
													
										<div class="form-group clearfix bfrcs ">
											<div class="col-lg-12 ">
											<?php if($iupdatedetails['id']!=''){ ?>
											<input type="hidden"  name="skool_syllabusold"      value="<?php echo $iupdatedetails['skool_syllabus']; ?>">
											<input type="hidden"  name="learning_materialsold"  value="<?php echo $iupdatedetails['learning_materials']; ?>">
											<input type="hidden"  name="exam_timetableold"      value="<?php echo $iupdatedetails['exam_timetable']; ?>">
											<input type="hidden"  name="exam_pass_questionold" value="<?php echo $iupdatedetails['exam_pass_question']; ?>">
											<input type="hidden"  name="admission_listold" value="<?php echo $iupdatedetails['admission_list']; ?>">
											<button type="submit" name="update_document" class="btn">
													<span>Update</span>
											</button>
											<?php } else { ?>
											<button type="submit" name="upload_document" class="btn">
													<span>Upload</span>
											</button>
											<?php } ?>
											</div>
										</div> 
										
									</div>
									<div class="col-md-1"></div>
								</div>
								</form>
								
							</div>
							</div>  
						
						
						<?php } ?>
						
						
						
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
	
	
</body>
</html>