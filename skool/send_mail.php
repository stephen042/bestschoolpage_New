<?php include('../config.php');
include('inc.session-create.php');
$validate = new validation();
$PageTitle = "SEND EMAIL";
$FileName = 'send_mail.php';
if ($_SESSION['success'] != "")
{
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}	
$iupdatedetails=$db->getRow("select * from  school_register where id='".$_SESSION['userid']."'");
$skoolName=$iupdatedetails['name'];		

mail($to, $subject, $message, $headers);
{
	$to      = $iupdatedetails['email'];
	$subject = 'the subject';
	$message = 'hello';
	$headers = 'From: mail@schoolinfo.com' . "\r\n" .
    'Reply-To: mail@schoolinfo.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
}

if(isset($_POST['parentMAIL']))
{
	$validate->addRule($_POST['pSubject'],'','Subject',true);	
	$validate->addRule($_POST['pSMS'],'','Message',true);	
		
	if ($validate->validate() && count($stat) == 0)
	{	
		$mainMessagee = $_POST['pSMS'];
		$Subject = $_POST['pSubject'];
		
		foreach($_POST['sendMAILToParent'] as $Key => $Val)
		{
			$Email = $Val;
			
			$headers  = "From: SCHOOL INFO < mail@schoolinfo.com >\n";
			$headers .= "X-Sender: SCHOOL INFO < mail@schoolinfo.com >\n";
			$headers .= 'X-Mailer: PHP/' . phpversion();
			$headers .= "X-Priority: 1\n"; // Urgent message!
			$headers .= "Return-Path: mail@schoolinfo.com\n"; // Return path for errors
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n";
			
			mail($Email,$Subject,$mainMessagee,$headers);
		}
		$_SESSION['success'] = "Email has been sent successfully";
		redirect('send_mail.php');
	}
	else
	{
		$stat['error']= $validate->errors();
	}
}

if(isset($_POST['staffEMAIL']))
{
	
	$validate->addRule($_POST['sSubject'],'','Subject',true);	
	$validate->addRule($_POST['sSMS'],'','Message',true);	
		
	if ($validate->validate() && count($stat) == 0)
	{	
		$mainMessagee = $_POST['sSMS'];
		$Subject = $_POST['sSubject'];
		
		foreach($_POST['sendMAILToStaff'] as $Key => $Val)
		{
			$Email = $Val;
			
			$headers  = "From: SCHOOL INFO < mail@schoolinfo.com >\n";
			$headers .= "X-Sender: SCHOOL INFO < mail@schoolinfo.com >\n";
			$headers .= 'X-Mailer: PHP/' . phpversion();
			$headers .= "X-Priority: 1\n"; // Urgent message!
			$headers .= "Return-Path: mail@schoolinfo.com\n"; // Return path for errors
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n";
			
			mail($Email,$Subject,$mainMessagee,$headers);
		}
		$_SESSION['success'] = "Email has been sent successfully";
		redirect('send_mail.php?action=staff');
	}
	else
	{
		$stat['error']= $validate->errors();
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
							<li role="presentation" class="<?php if($_GET['action']=='' || $_GET['action']=='parents') { echo "active"; } ?>">
								<a href="<?php echo $Filename; ?>?action=parents">
									<i class="fa fa-exclamation-circle" aria-hidden="true"></i> <span>Parents</span>
								</a>
                            </li>
							
							<li role="presentation" class="<?php if($_GET['action']=='staff') { echo "active"; } ?>">
								<a href="<?php echo $FileName; ?>?action=staff">
									<i class="fa fa-users" aria-hidden="true"></i>
                                    <span>Staff</span>
								</a>
							</li>
						</ul>
						<div class="tab-content">
						<?php if($_GET['action']=='' || $_GET['action']=='parents') {
						$iupdatedetails = $db->getRow("select * from  school_register where create_by_userid='".$create_by_userid."'"); 
						?>
						<!-- Tab panes -->
						<div role="tabpanel" class="tab-pane active" id="home">
                            <div class="ab-1">
								<form action="" method="POST">
								<div class="row">
									<div class="col-md-1"></div>
									<div class="col-md-10"></div>
									<div class="col-md-1"></div>
                                   <div class="col-md-12 col-xm-12"> 
								
								Subject
								<input name="pSubject" value="<?php echo $_POST['pSubject']; ?>" class="form-control" placeholder="Type your subject here...">
								<br>
								Message
								<textarea class="form-control" name="pSMS" placeholder="Type your message here..."><?php echo $_POST['text'];?></textarea>
								
							<br>
								<button type="submit" name="parentMAIL"style="float:left;">SEND MAIL </button>
							</div>
                            	
								 <div class="card-box table-responsive tablthisresponsive">
                    				
                                    <table  class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>#</th>
										<th>Parent ID</th>
										<th>First Name</th>
										<th>Last Name</th>
										<th>Phone</th>
										<th>Email Id</th>
									</tr>
								</thead>
								<tbody>
								<?php $i=0;
								$aryList=$db->getRows("select DISTINCT parent_id from student_guardian where create_by_userid='".$create_by_userid."'");
								foreach($aryList as $iNeList)
								{	$i=$i+1;
									$aryPgAct["id"]=$iList['id'];
									$iList=$db->getRow("select * from student_guardian where parent_id='".$iNeList['parent_id']."'");	
								?>
									<tr>
										<td><input type="checkbox" name="sendMAILToParent[]" style="width:15px;height:15px" value="<?php echo $iList['email'];?>"></td>
											
											<td>
												<?php echo $iList['parent_id'];  ?>
											</td>
											<td>
												<?php echo $iList['first_name'];  ?>
											</td>
											<td>
												<?php echo $iList['last_name'];  ?>
											</td>
											<td>
												<?php echo $iList['phone'];  ?>
											</td>
											<td>
												<?php echo $iList['email'];  ?>
											</td>
									</tr>
                                    <?php } ?>
								</tbody>
								</table>
								
                                </div>
                                </div>	
                                </form>	
								</div>
							</div>
						</div> 
						<?php } elseif($_GET['action']=='staff') {
						$iupdatedetails = $db->getRow("select * from  staff_mange where id='".$_SESSION['userid']."' and create_by_userid='".$create_by_userid."'"); 
						?>
							<!-- Tab panes -->
							<div role="tabpanel" class="tab-pane active" id="home">
                            <div class="ab-1">
							<form action="" method="POST">
							Subject
							<input name="sSubject" value="<?php echo $_POST['pSubject']; ?>" class="form-control" placeholder="Type your subject here...">
							<br>
							Message
							<textarea class="form-control" name="sSMS" placeholder="Type your message here..."><?php $_POST['text'];?></textarea>
									<div class="card-box">
									
									
									<button type="submit" name="staffEMAIL"style="float:left;">SEND EMAIL </button>

									<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>#</th>
											
											
											<th>Staff ID</th>											
											<th>First Name</th>
											<th>Last Name</th>
											<th>Phone</th>
											<th>Email</th>
										</tr>
									</thead>
                                    <tbody>
                                    <?php $i=0;
				$aryList=$db->getRows("select * from staff_manage where create_by_userid='".$create_by_userid."'");
						foreach($aryList as $iList)
							{	$i=$i+1;
							  
							 ?>
                                        <tr>
											<td><input type="checkbox" name="sendMAILToStaff[]" value="<?php echo $iList['email']; ?>"></td>
											 
												
													 
												   <td>
												 <?php  echo $iList['staff_id'];    ?>
												 
												 </td>
											
											<td>
												 <?php  echo $iList['first_name'];    ?>
												 
												 </td>
											<td>
												 <?php  echo $iList['last_name'];    ?>
												 
												 </td>
												 <td>
												 <?php  echo $iList['phone'];    ?>
												 
												 </td>
												 <td>
												 <?php  echo $iList['email'];    ?>
												 
												 </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                    </table>
								</form>	
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

<script> 
function getassesment()
{
	
	var session = document.getElementById("session").value;
	

	$.post("ajax.php",  
			{	
				"action"	     	:	"Action_getassesment",
				session	     	:	session,
			},
		function(data){
			
			$("#getasses").html(data);
					
				
			});
}



</script>	
</body>
</html>
