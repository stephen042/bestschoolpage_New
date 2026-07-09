<?php include('../config.php');
include('inc.session-create.php');
$PageTitle = "Manage Attrition";
$FileName = 'update_student_status.php';
$validate = new Validation();
if($_SESSION['success']!="")
{
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}
if(isset($_POST['add_attrition_status']))
{
    $validate->addRule($_POST['attrition_id'],'','Attrition ID',true);
	$validate->addRule($_POST['description'],'','Description',true);

    if($validate->validate() && count($stat) == 0)
    {
        $iLastId=$db->getVal("select id from manage_attrition order by id desc")+1;
		$iRandomId=randomFix(15).'-'.$iLastId;

        $aryData = array(
						'usertype' 				=>	$_SESSION['usertype'],
						'userid' 				=>	$_SESSION['userid'],
						'attrition_id' 			=>	$_POST['attrition_id'],
						'description' 			=>	$_POST['description'],
						'create_by_usertype' 	=>	$create_by_usertype,
						'create_by_userid' 		=>	$create_by_userid,
						'randomid' 				=>	$iRandomId,
						);
				$flgIn = $db->insertAry("manage_attrition", $aryData);
				$_SESSION['success']="Added successfully.";
				redirect($FileName.'?action=manage_attrition_status');
    }
    else
	{
		$stat['error'] = $validate->errors();
	}
}
elseif(isset($_POST['editmas'])) 
{
    $validate->addRule($_POST['attrition_id'],'','Attrition ID',true);
	$validate->addRule($_POST['description'],'','Description',true);

    if($validate->validate() && count($stat) == 0)
    {
		$aryData = array(
						'attrition_id' 			=>	$_POST['attrition_id'],
						'description' 			=>	$_POST['description'],
						);
			$flgIn2 = $db->updateAry("manage_attrition", $aryData, "where randomid='".$_GET['randomid']."'");
			$_SESSION['success']="Saved successfully.";
			redirect($FileName.'?action=manage_attrition_status');
    }
	else
	{
		$stat['error'] = $validate->errors();
	}
}
elseif($_GET['action']=='delete_mas') 
{
	$flgIn1 = $db->delete("manage_attrition", "where randomid='".$_GET['randomid']."'");
	$_SESSION['success'] = 'Deleted Successfully';
    redirect($FileName.'?action=manage_attrition_status');
    
}
elseif(isset($_POST['update_status']))
{
    if($validate->validate() && count($stat) == 0)
    {
		$iStudent=$db->getRow("select * from manage_student where randomid='".$_GET['randomid']."'");
		
		$iStatus=$db->getRow("select * from manage_student_attrition where student_id='".$iStudent['id']."'");
		
		if($iStatus['id']=='')
		{
			$iLastId=$db->getVal("select id from manage_student_attrition order by id desc")+1;
			$iRandomId=randomFix(15).'-'.$iLastId;

			$aryData = array(
							'usertype' 				=>	$_SESSION['usertype'],
							'userid' 				=>	$_SESSION['userid'],
							'student_id' 			=>	$iStudent['id'],
							'status' 				=>	$_POST['status'],
							'date' 					=>	$_POST['date'],
							'comments' 				=>	$_POST['comments'],
							'create_by_usertype' 	=>	$create_by_usertype,
							'create_by_userid' 		=>	$create_by_userid,
							'randomid' 				=>	$iRandomId,
							);
					$flgIn = $db->insertAry("manage_student_attrition", $aryData);
					$_SESSION['success']="Updated successfully.";
					redirect($FileName.'?action=manage_student_attrition&view=status&randomid='.$_GET['randomid']);
		}
		else
		{
			$aryData = array(
							'status' 				=>	$_POST['status'],
							'date' 					=>	$_POST['date'],
							'comments' 				=>	$_POST['comments'],
							);
					$flgIn2 = $db->updateAry("manage_student_attrition", $aryData, "where randomid='".$iStatus['randomid']."'");
					$_SESSION['success']="Updated successfully.";
					redirect($FileName.'?action=manage_student_attrition&view=status&randomid='.$_GET['randomid']);
		}
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
    <?php include('inc.sideleft.php'); ?>
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
							<li role="presentation" class="<?php if($_GET['action']=='' || $_GET['action']=='manage_attrition_status') { echo "active"; } ?>">
								<a href="<?php echo $Filename; ?>?action=manage_attrition_status">
									<i class="fa fa-exclamation-circle" aria-hidden="true"></i> <span>Manage Attrition Status</span>
								</a>
                            </li>
							<li role="presentation" class="<?php if($_GET['action']=='view_attrition_record') { echo "active"; } ?>">
								<a href="<?php echo $Filename; ?>?action=view_attrition_record">
									<i class="fa fa-line-chart" aria-hidden="true"></i><span>View Attrition Record</span>
								</a>
							</li>
							<li role="presentation" class="<?php if($_GET['action']=='manage_student_attrition') { echo "active"; } ?>">
								<a href="<?php echo $FileName; ?>?action=manage_student_attrition">
									<i class="fa fa-users" aria-hidden="true"></i>
                                    <span>Manage Student Attrition</span>
								</a>
							</li>

						</ul>
						<div class="tab-content">
						<?php if($_GET['action']=='' || $_GET['action']=='manage_attrition_status') {
						$iupdatedetails = $db->getRow("select * from  school_register where id='".$_SESSION['userid']."' and create_by_userid='".$create_by_userid."'"); 
						?>
							<!-- Tab panes -->
							<div role="tabpanel" class="tab-pane active" id="home">
                            <div class="ab-1">
								<form action="" method="POST">
								<div class="row">
									<div class="col-md-1"></div>
									<div class="col-md-10">
									
										<div class="form-group clearfix">
											<label class="col-lg-2 control-label " for="price">Attrition ID:</label>
											<div class="col-lg-10">
												<input type="text" class="form-control" name="attrition_id" value="<?php echo $_POST['attrition_id']; ?>"/>
											</div>
										</div>

										<div class="form-group clearfix">
											<label class="col-lg-2 control-label " for="price">Description:</label>
											<div class="col-lg-10">
												<input type="text" class="form-control" name="description" value="<?php echo $_POST['description']; ?>"/>
											</div>
										</div>     
										<div class="form-group clearfix bfrcs ">
											<div class="col-lg-12 ">
												<button type="submit" name="add_attrition_status" class="btn">
													<i class="fa fa-plus" aria-hidden="true"></i><span>Add</span>
												</button>
											</div>
										</div> 
										
									</div>
									<div class="col-md-1"></div>
								</div>
								</form>
								<div class="card-box">
								<form action="" method="POST">
									<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th>Attrition ID</th>
											<th>Description</th>
											<th>Edit</th>
											<th>Remove</th>
										</tr>
									</thead>
                                    <tbody>
                                    <?php $i=0;
                                    $aryList = $db->getRows("select * from manage_attrition where create_by_userid='".$create_by_userid."' order by id desc");
									foreach($aryList as $iList) 
									{ $i=$i+1;
									?>
                                        <tr>
											<td><?php echo $i ?></td>
											<td>
												<?php if($_GET['randomid']==$iList['randomid']) { ?>
												<input type="text" name="attrition_id" value="<?php echo $iList['attrition_id']; ?>" class="form-control">
												<?php } else { echo $iList['attrition_id']; } ?>
											</td>
											<td>
												<?php if($_GET['randomid']==$iList['randomid']) { ?>
												<input type="text" name="description" value="<?php echo $iList['description']; ?>" class="form-control">
												<?php } else { echo $iList['description']; } ?>
											</td>
											<td>
												<?php if($_GET['randomid']==$iList['randomid']) { ?>
												<input type="submit" name="editmas" value="SAVE" class="btn btn-primary" style="color:white;"> 
												<?php } else { ?>
												<a href="<?php echo $FileName; ?>?action=manage_attrition_status&randomid=<?php echo $iList['randomid']; ?>" class="table-action-btn">
                                                    <i class="fa fa-pencil"></i> 
												</a>
												<?php } ?>
											</td>
											<td>
												<a href="javascript:del('<?php echo $FileName; ?>?action=delete_mas&randomid=<?php echo $iList['randomid']; ?>')" class="table-action-btn"> 
													<i class="fa fa-times"></i> 
												</a>
											</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                    </table>
								</form>	
								</div>
							</div>
							</div>
							<?php } elseif($_GET['action']=='view_attrition_record') { ?>
							<div role="tabpanel" class="tab-pane active" id="profilxe">
							<div class="abhish">
								
								<form action="" method="POST">
								<div class="row">
									<div class="col-md-12">
									
										<div class="form-group clearfix">
											<div class="col-lg-5">
												<select class="required form-control" name="attrition_id">
													<option value="">Select ID</option>
													<?php 
													$iAttritionList=$db->getRows("select * from manage_attrition where create_by_userid='".$create_by_userid."' order by id desc");
													foreach($iAttritionList as $iList) 
													{ $i=$i+1;
													?>
													<option value="<?php echo $iList['id']; ?>" <?php if($_POST['attrition_id']==$iList['id']) { echo "selected"; } ?>><?php echo $iList['attrition_id']; ?></option>
													<?php } ?>
												</select>
											</div>
											<div class="col-lg-2">
												<button type="submit" name="" class="btn">
													<span>Search</span>
												</button>
											</div>
											<div class="col-lg-5"></div>
										</div>

									</div>
								</div>
								</form>
								
								<div class="card-box">
								<form action="" method="POST">
									<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th>Student ID</th>
											<th>First Name</th>
											<th>Last Name</th>
											<th>Other(S)</th>
											<th>Status</th>
											<th>Date</th>
											<th>Comments</th>
										</tr>
									</thead>
                                    <tbody>
                                    <?php $i=0;
                                    $aryList = $db->getRows("select * from manage_student_attrition where create_by_userid='".$create_by_userid."' order by id desc");
									foreach($aryList as $iList) 
									{ $i=$i+1;
									$iStudent=$db->getRow("select * from manage_student where id='".$iList['student_id']."' and create_by_userid='".$create_by_userid."'");
									?>
                                        <tr>
											<td><?php echo $i ?></td>
											<td><?php echo $iStudent['student_id'];?></td>
											<td><?php echo $iStudent['first_name'];?></td>
											<td><?php echo $iStudent['last_name'];?></td>
											<td><?php echo $iStudent['other_name'];?></td>
											<td><?php echo $db->getVal("select attrition_id from manage_attrition where id='".$iList['status']."'"); ?></td>
											<td><?php echo $iList['date'];?></td>
											<td><?php echo $iList['comments'];?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                    </table>
								</form>	
								</div>
							</div>
							</div>
							<?php } elseif ($_GET['action']=='manage_student_attrition') { 
							$iStudent=$db->getRow("select * from manage_student where randomid='".$_GET['randomid']."' and create_by_userid='".$create_by_userid."'");
							?>
							<div role="tabpanel" class="tab-pane active" id="profilxe">
							<div class="abhish">
							<?php if($_GET['view']=='status') { 
							$iStaus=$db->getRow("select * from manage_student_attrition where student_id='".$iStudent['id']."' and create_by_userid='".$create_by_userid."'");
							?>	
								<form action="" method="POST">
								<div class="row">
									<div class="col-md-1"></div>
									<div class="col-md-10">
									
										<div class="form-group clearfix">
											<div class="col-lg-6">
												<label>Student Status:</label>
												<select class="required form-control" name="status">
													<?php 
													$iAttritionList=$db->getRows("select * from manage_attrition where create_by_userid='".$create_by_userid."' order by id desc");
													foreach($iAttritionList as $iList) 
													{ $i=$i+1;
													?>
													<option value="<?php echo $iList['id']; ?>" <?php if($iStaus['status']==$iList['id']) { echo "selected"; } ?>><?php echo $iList['attrition_id']; ?></option>
													<?php } ?>
												</select>
											</div>
											<div class="col-lg-6">
												<label>Date:</label>
												<input type="text" class="form-control datepicker" name="date" value="<?php echo $iStaus['date']; ?>" autocomplete="off"/>
											</div>
										</div>
									
										<div class="form-group clearfix">
											<div class="col-lg-12">
												<label>Comment:</label>
												<textarea class="form-control" name="comments"><?php echo $iStaus['comments']; ?></textarea>
											</div>
										</div>     
										<div class="form-group clearfix bfrcs ">
											<div class="col-lg-12 ">
												<button type="submit" name="update_status" class="btn btn-primary">
													<span>Update</span>
												</button>
											</div>
										</div> 
										
									</div>
									<div class="col-md-1"></div>
								</div>
								</form>
							<?php } ?>	
								<div class="card-box">
								<form action="" method="POST">
									<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th>Student Name</th>
											<th>Class</th>
											<th>Teacher</th>
											<th>Action</th>
										</tr>
									</thead>
                                    <tbody>
                                    <?php $i=0;
                                    $aryList = $db->getRows("select * from manage_student where create_by_userid='".$create_by_userid."'");
									foreach($aryList as $iList) 
									{ $i=$i+1;
									?>
                                        <tr>
											<td><?php echo $i ?></td>
											<td><?php echo $iList['first_name'].' '.$iList['last_name']; ?></td>
											<td><?php echo $iList['attrition_id']; ?></td>
											<td>Nil</td>
											<td>
												<a href="<?php echo $FileName; ?>?action=manage_student_attrition&view=status&randomid=<?php echo $iList['randomid']; ?>" class="btn btn-primary" style="color:white;">View</a>
											</td>
										 </tr>
                                    <?php } ?>
                                    </tbody>
                                    </table>
								</form>	
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
	<?php include('inc.footer.php'); ?>
</div>
</div>
	<?php include('inc.js.php'); ?>
<script> 
function getClass()
{
	
	var section_id = document.getElementById("section_id").value;

	$.post("ajax.php",  
			{	
				"action"	     	:	"Action_getClass",
				section_id	     	:	section_id,
			},
		function(data){
			
			$("#showclass").html(data);
					
				
			});
}
</script>	
</body>
</html>