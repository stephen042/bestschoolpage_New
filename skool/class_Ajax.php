<?php include('../config.php'); 
include('inc.session-create.php'); 
$validate=new Validation();
if($_POST['action']=="getclass")
		{
		?>
		<select name="class" id="class_id" class="form-control" onchange="getStudent()">
		<option value="">Select Class</option>
		<?php
		$isection=$db->getRows("select * from school_class where session_id='".$_POST['ses_id']."'  and create_by_userid='".$create_by_userid."'");
		foreach($isection as $list)
		{ ?>
		<option value="<?php echo $list['id'];?>"<?php if($_POST['class']==$list['id']){echo "selected";}?>>
		<?php echo $list['name'];?></option>
		<?php } ?>
		</select>
<?php }
elseif($_POST['action']=="getStudentclass")
	{
		?>
		<select name="class" id="class_id" class="form-control" onchange="getsubject()">
		<option value="">Select Class</option>
		<?php
		$isection=$db->getRows("select * from school_class where session_id='".$_POST['ses_id']."'  and create_by_userid='".$create_by_userid."'");
		foreach($isection as $list)
		{?>
		<option value="<?php echo $list['id'];?>"<?php if($_POST['class']==$list['id']){echo "selected";}?>>
		<?php echo $list['name'];?></option>
		<?php } ?>
		</select>
<?php }
elseif($_POST['action']=="getsubject")
	  { 
	
	?>
		<select name="class" id="class_id" class="form-control" onchange="getStudent()">
		<option value="">Select Class</option>
		<?php
		$isection=$db->getRows("select * from school_class where session_id='".$_POST['ses_id']."'  and create_by_userid='".$create_by_userid."'");
		foreach($isection as $list)
		{?>
		<option value="<?php echo $list['id'];?>"<?php if($_POST['class']==$list['id']){echo "selected";}?>>
		<?php echo $list['name'];?></option>
		<?php } ?>
		</select>
<?php } 
elseif($_POST['action']=="getstudent")
{ ?>
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
								$iclaas=$db->getRows("select * from class_member where class_id='".$_POST['class_id']."'  and create_by_userid='".$create_by_userid."' ");
								foreach($iclaas as $clist)
								{  
								$i=$i+1;
								$istudent=$db->getRow("select * from  manage_student where student_id='".$clist['student_id']."'  and create_by_userid='".$create_by_userid."' ");
								?>	
							<tr class="<?php if($i%2=='0'){echo "info";}else{echo "success";}?>" >
									<td><?php echo $i;?></td>
									<td><?php echo $istudent['student_id'];?></td>
									<td><?php  echo $istudent['first_name']."  ".$istudent['last_name'];?></td>
									<td>
								<input type="checkbox" name="is_present[]" id="uid_<?php echo $istudent['student_id'];?>" value="<?php echo $istudent['student_id'];?>" onchange="getLate(late_<?php echo $istudent['student_id'];?>)"/>
									</td>
									<td>
                                <input type="checkbox" name="is_late[]" id="late_<?php echo $istudent['student_id'];?>"
								value="<?php echo $istudent['student_id'];?>" 
								onchange="getPresent(uid_<?php echo $istudent['student_id'];?>)"  /> 
								    </td>
							</tr>
		            <?php } ?>
								</tbody>
		                    </table>	
<?php }
elseif($_POST['action']=="studentList")
{ 
$aryDetail=$db->getRows("select * from  manage_student where  student_session='".$_POST['student_session']."'  and create_by_userid='".$create_by_userid."'"); 
								
                                    foreach($aryDetail as $iList)
									{
 ?>
                                                <tr>
												<td>
									<input type="checkbox" class="memberList" name="class_member[]" value="<?php echo $iList['id']; ?>"/>            </td>
														<td><?php echo $iList['student_id']; ?></td>
														<td><?php echo $iList['first_name']; ?></td>
														<td><?php echo $iList['last_name']; ?></td>
														<td><?php echo $iList['other_name']; ?></td>
														<td><?php echo $iList['gender']; ?></td>
														<td><?php echo $iList['date_of_birth']; ?></td>
												</tr>
							  <?php } ?>
 
<?php }

elseif($_POST['action']=="addMember")
    {
		
		
	            $validate->addRule($_POST['class_id'],'','Class',true);
				$validate->addRule($_POST['school_session'],'','Session',true);
				$validate->addRule($_POST['stu_iid'],'','Class Member',true);
			    $studentid1=$_POST['stu_iid'];
				$studentid=explode(',',$studentid1);
			    if($validate->validate() && count($stat)==0)
				{
					foreach($studentid as $list)
				    {
					$student=$db->getRow("select * from  manage_student where id='$list'"); 
$classMember=$db->getRow("select * from  class_member where student_id='$list' and session_id='".$_POST['school_session']."' and class_id='".$_POST['class_id']."'"); 
 
    if($classMember['id']=='')
	{

						$aryData=array
						(	
						'usertype'                                        => $_SESSION['usertype'],
						'userid'                                          => $_SESSION['userid'],
						'session_id'                                      => $_POST['school_session'],
						'class_id'                                        => $_POST['class_id'],
						'student_id'  			                          => $list,
						'create_by_userid'  			                  => $create_by_userid,
						'create_by_usertype'  			                  => $create_by_usertype,
						'last_name'  			                          => $student['last_name'],
						'first_name'                                      => $student['first_name'],
						'other_name'  			                          => $student['other_name'],
						'gender'  			                              => $student['gender'],
						'date_of_birth'  			                      => $student['date_of_birth'],
						'create_at'  			                          => date("Y-m-d,H:i:s"),
						'randomid'  			                          => randomFix(15),
						);  
						$flgIn1 = $db->insertAry("class_member",$aryData);
						//echo $flgIn1 = $db->getLastQuery();
						$stat['success']="Added successfully";
	}
		else
		{
			
			$stat['error']="Record already exist";
			
		}
						
		        }	
  
				}


		else    
		        {
					
					$stat['error'] = $validate->errors();
				}
				
			echo msg($stat);
				
    }
	
elseif($_POST['action']=="showResults")
{

	$assesment=$_POST['asse'];
	$offering=$_POST['offering'];
	if($assesment=='CAT 1')
	{ 
    $asses=",ca_1"; 
	$aa="ca_1";
	$max="30";
	
    }
	if($assesment=='CAT 2')
	{ 
    $asses=",ca_2";
	$aa="ca_2";
	$max="30";
    }
	
	if($assesment=='Exam')
	{ 
    $asses=",exams";
	$aa="exams";
	$max="40";
    }
	
	if($offering=='0')
	{ 
    $offer="and offering='$offering'";
	
    }
	if($offering=='1')
	{ 
    $offer="and offering='$offering'";
    }
	if($offering=='2')
	{ 
    $offer="";
    }
	

?>
	 
                                                        <thead>
                                                            <tr style="width: 20px;">
																<th>Offering</th>
																<th>Student id</th>
																<th>First Name</th>
																<th>Last Name</th>
																<th>Other Name</th>
																<th>
													<?php 
													if($assesment=='Exam')
													{
													echo $assesment."($max%)";
													
													} 
													else
													{ 
												echo $assesment."($max%)";
												
													} ?>
											<input type="hidden" name="dynaAsses" value="<?php echo $aa; ?>" />
																</th>
                                                                       
                                                            </tr>
                                                        </thead>
                                                                    
                            <tbody>
	  <?php
	$arystudent = $db->getRows("select id,class_id,session_id,subject_id,student_id,userid,usertype,offering $asses from input_score_subject_teacher where class_id = '".$_POST['class_id']."' $offer  and create_by_userid='".$create_by_userid."'");
	
                            foreach($arystudent as $studentid)
                            {
	$getStudent = $db->getRow("select * from manage_student where class = '".$_POST['class_id']."' and id='".$studentid['student_id']."'  and create_by_userid='".$create_by_userid."'");
				            ?>
							<tr>
								<td> 
						<input type="checkbox" name="offering[]" value="1"<?php if($studentid['offering']=="1") { echo "checked";  } ?> />   
          						</td>
						        <input type="hidden" name="stu_iid[]"  value="<?php echo $studentid['student_id'];?>" />
								<td><?php echo $getStudent['student_id'];?> </td>
								<td><?php echo $getStudent['first_name'];?></td>
								<td><?php echo $getStudent['last_name'];?> </td>
								<td><?php echo $getStudent['other_name'];?></td>

								<td>
	<input class="equipCatValidation"  type="text" name="assesments[]" value="<?php echo $studentid["$aa"];?>"max="<?php echo $max;?>"  />	
				<span class="maxQuantityMsg">
				Max value is 30 
				</span>		
</td>
							</tr>
                             <?php } ?>
                            </tbody>
                                                                   
    
<?php }	
elseif($_POST['action']=="inputTimeFrame")  
{
	
	$score=$db->getRow("select * from  score_entry_time_frame where class ='".$_POST['class_id']."' and session='".$_POST['session_id']."' and assesment='".$_POST['assse']."'  and create_by_userid='".$create_by_userid."'");
	
	 if($score['end_date']>date("Y-m-d")) 
	{ 
	
	echo "<span style='color:green;'>Score Entry Open-".$score['start_date'].",".$score['end_date']."</span>";
	}
	else 
	{
		
	 echo "<span style='color:red;'> Score Entry Closed</span>";
	 

	}
								
}
 ?>