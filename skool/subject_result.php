<?php
include('../config.php');
include('inc.session-create.php');
$validate = new validation();
$pageTitle = 'Subject Result';
$Filename = 'subject_result.php';
if(isset($_POST['submit']))
{
    $validate->addRule($_POST['session'], '', 'Session', true);
    $validate->addRule($_POST['class'],'','Class',true); 
	$validate->addRule($_POST['roll_call_date'],'','Roll call date',true);

    if ($validate->validate() && count($stat) == 0)
	{    
     $arydetail = $db->getRow("select * from ft_rollCall where rollCall_date='" .$_POST['roll_call_date']. "' and class_id='" .$_POST['class']. "' and session_id='" .$_POST['session']. "'");
        if($arydetail['id']=='')
		{
         $is_present=$_POST['is_present'];
		 $present_student=implode(',',$is_present);
		
		 $is_late=$_POST['is_late'];
		 $late_student=implode(',',$is_late);
		 
         $aryData = array(
            'session_id'             => $_POST['session'],
            'class_id'               => $_POST['class'], 
			'rollCall_date'          => $_POST['roll_call_date'],
			'create_by_userid'       => $create_by_userid, 
			'create_by_usertype'     => $create_by_usertype,
            'present_student'        => $present_student,
			'late_student'           => $late_student,
			'create_at'              => date("Y-m-d H:i:s"),
			'status'                 => 1,
			'randomid'               => randomFix(20),
			);
        $flgIn2 = $db->insertAry("ft_rollCall",$aryData);
        $stat['success'] = "Submited successfully";
        unset($_POST);
        redirect($Filename);
		}
		
		else
	    {
			$stat['error'] ="Record already exist";
	    }
    }
	else
	{
			$stat['error'] = $validate->errors();
	}
}
elseif (isset($_POST['update'])) {


    $validate->addRule($_POST['session'], '', 'Session', true);
    $validate->addRule($_POST['class'],'','Class',true); 
	$validate->addRule($_POST['roll_call_date'],'','Roll call date',true);

    if ($validate->validate() && count($stat) == 0)
	{    
     
          $is_present=$_POST['is_present'];
		  $present_student=implode(',',$is_present);
		
		  $is_late=$_POST['is_late'];
		  $late_student=implode(',',$is_late);
		  
          $aryData = array(
            'session_id'             => $_POST['session'],
            'class_id'               => $_POST['class'], 
			'rollCall_date'          => $_POST['roll_call_date'],
			'create_by_userid'       => $create_by_userid, 
			'create_by_usertype'     => $create_by_usertype,
            'present_student'        => $present_student,
			'late_student'           => $late_student,
			'create_at'              => date("Y-m-d H:i:s"),
			'status'                 => 1,
			'randomid'               => randomFix(20),
			);
         $flgIn2 = $db->updateAry("ft_rollCall",$aryData);
         $stat['success'] = "Submited successfully";
         unset($_POST);
         redirect($Filename);
		
    }
	else
	{
			$stat['error'] = $validate->errors();
	}
} 
elseif (($_REQUEST['action'] == 'delete'))
{
    $flgIn1 = $db->delete("ft_rollCall", "where randomid='" . $_GET['randomid'] . "'");
    $stat['success'] = 'Deleted Successfully';
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
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
                            <li><a href="<?php echo $PageTitle; ?>">Home</a></li>
                            <li class="active"><?php echo $pageTitle; ?></li>
                        </ol>
                    </div>
                </div>

                <!-- Basic Form Wizard -->

                <div class="row">
                    <div class="col-md-12">
                        <div class="card-box aplhanewclass">
                            <div class="row">
                                <div class="col-md-9">
                                    <?php echo msg($stat); ?>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?php echo $Filename; ?>?action=add" class="btn btn-default"
                                       style="float:right;color:white;">Add New Record</a>
                                </div>
                            </div>
                        </div>

                        <!-- add section start -->
                        <?php if ($_GET['action'] == 'add') { ?>
                            <div class="card-box">
                                <form action="" method="POST" enctype="multipart/form-data">
                                <div>
                                    <section>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label " for="price"> Session Name:</label>
                                            <div class="col-lg-10">
                                                <select class="required form-control" name="session" id="session_id" onchange="getClass()">
                                                    <option value="">Select Session</option>
                                                    <?php $aryDetail = $db->getRows("select * from  school_session order by id desc");
                                                    foreach ($aryDetail as $iList) {
                                                        $i = $i + 1; ?>
                                                        <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['session'] == $iList['id']) {
                                                            echo "selected";
                                                        } ?>><?php echo $iList['session']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label " for="price"> Class Name:</label>
                                            <div class="col-lg-10"id="showclass">
                                                <select class="required form-control">
                                                    <option value="">Select Class</option>
                                                </select>
                                            </div>
                                     </div>

                                <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label " for="price">Roll Call Date:</label>
                                        <div class="col-lg-10">
                           <input type="text" class="form-control datepicker"  name="roll_call_date" placeholder="YYYY-MM-DD" value="<?php echo $_POST['roll_call_date'];?>" />
                                        </div>
                         </div>

								<div id="showstudentList">
								</div>
								

                                        <button type="submit" name="submit" style="color:white;" class="btn btn-default">Submit</button>
                                        <a href="<?php echo $Filename; ?>" style="color:white;" class="btn btn-default">Back</a>
                                    </section>
                                </div>
                       </form>
                            </div>


                        <?php } elseif ($_GET['action'] == 'edit') {
                            $arydetail = $db->getRow("select * from  ft_rollcall where randomid='" .$_GET['randomid']. "' "); ?>


                            <div class="card-box">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div>
                                    <section>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label " for="price"> Session Name</label>
                                            <div class="col-lg-10">
                                                <select class="required form-control" name="session" id="session_id" onchange="getClass()">
                                                    <option value="">Select Session</option>
                                                    <?php $aryDetail = $db->getRows("select * from  school_session order by id desc");
                                                    foreach ($aryDetail as $iList) {
                                                        $i = $i + 1; ?>
                                                        <option value="<?php echo $iList['id']; ?>"
														<?php if ($arydetail['session_id'] == $iList['id']) {
                                                            echo "selected";
                                                        } ?>><?php echo $iList['session']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                       <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label " for="price"> Class Name:</label>
                                            <div class="col-lg-10"id="showclass">
                                                <select class="required form-control"name="class">
                                                    <option value="">Select Class</option>
							<?php $aryDetail = $db->getRows("select * from  school_class where session_id='".$arydetail['session_id']."'");
                                                    foreach ($aryDetail as $iList) {
                                                        $i = $i + 1; ?>
                                                        <option value="<?php echo $iList['id']; ?>"
														<?php if ($arydetail['class_id'] == $iList['id']) {
                                                            echo "selected";
                                                        } ?>><?php echo $iList['name']; ?></option>
                            <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label " for="price">Roll Call Date:</label>
                                        <div class="col-lg-10">
                           <input type="text" class="form-control datepicker"  name="roll_call_date" placeholder="YYYY-MM-DD" value="<?php echo $arydetail['rollCall_date'];?>" />
                                        </div>
                         </div>
										<div id="showstudentList">
								          <table class="table table-bordered">
								<thead>
									<tr>
										<th>#</th>
										<th>Student Id</th>
										<th>Name</th>
										<th>Present/Absent</th>
										<th>Late</th>
									</tr>
								</thead>
								<tbody>
								<?php
								$i=0;
								$iclaas=$db->getRows("select * from class_member where class_id='".$arydetail['class_id']."'");
								foreach($iclaas as $clist)
								{  
								$i=$i+1;
								$istudent=$db->getRow("select * from  manage_student where student_id='".$clist['student_id']."' ");
  $iattand=$db->getRow("select * from   ft_rollcall where class_id='".$arydetail['class_id']."' and rollCall_date='".$arydetail['rollCall_date']."'and randomid='".$_GET['randomid']."'");
								?>	
							<tr class="<?php if($i%2=='0'){echo "info";}else{echo "success";}?>" >
									<td><?php echo $i;?></td>
									<td><?php echo $istudent['student_id'];?></td>
									<td><?php  echo $istudent['first_name']."  ".$istudent['last_name'];?></td>
									<td>
									<?php $presentId=$iattand['present_student'];
									$itotalPresent=explode(',',$presentId);
									$studentsId=$istudent['student_id'];
									?>
<input type="checkbox" name="is_present[]" id="uid_<?php echo $istudent['student_id'];?>"
 onchange="getLate(late_<?php echo $istudent['student_id'];?>)"
value="<?php echo $istudent['student_id'];?>"<?php if(in_array($istudent['student_id'],$itotalPresent)){echo "checked";}?> />
									
									</td>
									<td>
									<?php
									$latePresent=$iattand['late_student'];
									$latePresentStudent=explode(',',$latePresent);
									
									?>
                                <input type="checkbox" name="is_late[]" id="late_<?php echo $istudent['student_id'];?>"   value="<?php echo $istudent['student_id'];?>"
			                   <?php if(in_array($istudent['student_id'],$latePresentStudent)){echo "checked";}?> 
								onchange="getPresent(uid_<?php echo $istudent['student_id'];?>)"/> 
								
								    </td>
							</tr>
		            <?php } ?>
								</tbody>
		                    </table>  
									
										
                                        </div>
                                        <button type="submit" name="update" style="color:white;" class="btn btn-default">Update</button>
                                        <a href="<?php echo $Filename; ?>" style="color:white;" class="btn btn-default">Back</a>
                                    </section>
                                </div>
                                </form>
                            </div>


                        <?php } else { ?>
                            <div class="card-box">
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Roll Call Date</th>
										<th>Class Name</th>
                                        <th>No. of Students Present</th>
									    <th>No. of Students Absent</th>
                                        <th>No. of Students Late</th> 
										<th>Total Students</th>
                                        <th>Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $aryList = $db->getRows("select * from ft_rollcall order by id desc");
                                    foreach ($aryList as $iList) {
                                        $i = $i + 1;
                                     $count=$db->getVal("select count(id) from  class_member where class_id='".$iList['class_id']."'"); 
									$classDe = $db->getRow("select * from  school_class where id='".$iList['class_id']."'");
                                        ?>
                                        <tr>
                                            <td><?php echo $i ?></td>
											
											<td><?php echo $iList['rollCall_date']; ?></td>
											<td><?php echo $classDe['name']; ?></td>
                                            <td><?php echo $itotal=count($cc=explode(',',$iList['present_student'])); ?></td>
											<td><?php echo $count-$itotal; ?></td>
                                            <td><?php echo $itoa=count($ci=explode(',',$iList['late_student'])); ?></td>
											<td>
									         <?php echo $count; ?>
											</td>
                                            <td>
                                                <a href="<?php echo $Filename; ?>?action=edit&randomid=<?php echo $iList['randomid']; ?>"
                                                   class="table-action-btn"> <i class="fa fa-pencil"></i> </a>
                                                <a href="javascript:del('<?php echo $Filename; ?>?action=delete&randomid=<?php echo $iList['randomid']; ?>')"
                                                   class="table-action-btn"> <i class="fa fa-times"></i> </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
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

<?php include('inc.js.php'); ?>
<script>
  function getClass()
  {
	
  var ses_id= document.getElementById("session_id").value;

  $.post("class_Ajax.php",
  {
	 action:"getclass",
     ses_id:ses_id,   	 
  },
  function(data)
  { 
	  document.getElementById('showclass').innerHTML=data;
  });
  }
</script>
<script>
  function getStudent()
  {
	
  var class_id= document.getElementById("class_id").value;

  $.post("class_Ajax.php",
  {
	 action:"getstudent",
     class_id:class_id,   	 
  },
  function(data)
  { 
	  document.getElementById('showstudentList').innerHTML=data;
  });
  }
</script>

<script type="text/javascript">
    function getPresent(student_id)
	{
		var stu_id=student_id;
        if (!stu_id.checked) 
		{
			//alert("not checked");
			checkid=stu_id.value;
			document.getElementById("uid_"+checkid).setAttribute('checked', 'checked');
			document.getElementById("uid_"+checkid).checked =true;
        }
		
		
    }
</script>

<script type="text/javascript">
    function getLate(student_id)
	{
		var lstu_id=student_id;
        if (lstu_id.checked) 
		{ 
	     
			lateId=lstu_id.value;
			
			document.getElementById("late_"+lateId).checked =false;
			  //alert("checked");
        }
		else
		{
			//alert("not checked");
		}
		
		
    }
</script>

<?php include('inc.footer.php'); ?>

</body>
</html>