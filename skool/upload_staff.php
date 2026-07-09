<?php include('../config.php'); 
//include('inc.session-create.php'); 
$PageTitle="Upload Staff";
$FileName = 'upload_staff.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
$stat['success']=$_SESSION['success'];
unset($_SESSION['success']);
}






	if(isset($_POST['uploadfile']))
{ 
  $file = $_FILES['file']['tmp_name'];
  $handle = fopen($file, "r");
  $c = 0;
  $csvMimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','text/xls');
  while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
{ $c=$c+1;
    
    if($c>1)
    {
  
    $staffid    = ($filesop[0]);
    $gender            = ($filesop[1]);
    $title      = ($filesop[2]);
    $date_of_birth    = ($filesop[3]);
    $last_name    = ($filesop[4]);
    $first_name    = ($filesop[5]);
    $date_of_appointment    = ($filesop[6]);
    $state_of_origin    = ($filesop[7]);
    $other_name    = ($filesop[8]);
    $lga_of_origin    = ($filesop[9]);
    $marrital_status    = ($filesop[10]);
    $religion = ($filesop[11]);
    $nationality = ($filesop[12]);
	$denomination    = ($filesop[13]);
	$no_of_children    = ($filesop[14]);
	$branch    = ($filesop[15]);
	
	$blood_group    = ($filesop[16]);
	$genotype    = ($filesop[17]);
	$address_1    = ($filesop[18]);
	$address_2    = ($filesop[19]);
	$state    = ($filesop[20]);
	$city    = ($filesop[21]);
	$p_o_box    = ($filesop[22]);
	$mobile    = ($filesop[23]);
	$email    = ($filesop[24]);
	$phone    = ($filesop[25]);
       
   $aryData=array( 
            'staff_id'                   =>  $staffid,
            'gender'                     => $gender,
            'title'                      =>  $title,
			'date_of_birth'              =>  $date_of_birth,
            'last_name'                  => $last_name,
            'first_name'                 =>  $first_name,
			'date_of_appointment'        =>  $date_of_appointment,
            'state_of_origin'            => $state_of_origin,
            'other_name'                 =>  $other_name,
			'lga_of_origin'              =>  $lga_of_origin,
            'marrital_status'            => $marrital_status,
            'religion'                   =>  $religion,
			'nationality'                =>  $nationality,
			'denomination'               =>  $denomination,
            'no_of_children'             => $no_of_children,
            'branch'                     =>  $branch,
			
			'blood_group'              =>  $blood_group,
            'genotype'            => $genotype,
            'address_1'                   =>  $address_1,
			'address_2'                =>  $address_2,
			'state'               =>  $state,
            'city'             => $city,
            'p_o_box'                     =>  $p_o_box,
			
			'mobile'               =>  $mobile,
            'email'             => $email,
            'phone'                     =>  $phone,
			'randomid'                     =>  randomFix(15),
            );  
        $flgIn1 = $db->insertAry("staff_manage",$aryData);
		//echo $flgIn1 = $db->getLastQuery();
		//exit;

  }
}
  $_SESSION['success']="Update Successfully";
  unset($_POST);
  redirect($iClassName.$FileName);
}			else {
					$stat['error'] = $validate->errors();
				}
			
			
			 	
			if(($_GET['action']=='delete_student_father'))
                {
                  $flgIn1 = $db->delete("staff_manage","where randomid='".$_GET['randomid']."'");
                  $_SESSION['success'] = 'Deleted Successfully';
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
            <h4 class="page-title licat">UPLOAD STAFF</h4>
            <ol class="breadcrumb">
              
              <?php echo msg($stat); ?>
            </ol>
          </div>
        </div>
		
        <!-- Basic Form Wizard -->
        <div class="row">
          <div class="col-md-12">
              <div class="row">
                <div class="col-md-1">  </div>
                
			  
			  <div class="col-md-11">
				<div class="gokul">
				<a href="<?php echo $FileName; ?>?action=add_staff"  class="btn btn-default" style="float:right">Upload Staff Here<i class="fa fa-plus" aria-hidden="true"></i></a>
				</div>
              </div>
			  
			  			  
			  
			  
            </div>
			</div>
			 <div class="col-md-12 ">
            
			
			
			
			
			
			
			
			
			<?php if($_GET['action']=='add_staff') { ?>
			
			<div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
				  <div class="form-group clearfix">
		<label class="col-lg-2 control-label " for="price">Upload staff*</label>
		<div class="col-lg-10">
			<input type="file" class="form-control"  name="file" value="<?php echo $_POST['file'];?>" />
		</div></div>
	
		
	
	
	
					<div class="form-group clearfix bfrcs ">
                      <div class="col-lg-12 sgot">
					   <div class="row">
                    <div class="savdtls"><button type="submit" name="uploadfile" class="btn btn-default">Save details</button></div>
					</div>
					</div>
					</div>
                </div>
              </form>
			  
			  <a download href="sample.csv">Download Sample Here</a>
          
			  </div>
			  
			  
			   <div class="card-box">
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
                <th>#</th>
 				<th>Staff ID</th>
 				<th> First Name</th>
 				<th> Last Name</th>
				<th>Occupation</th>
				<th>Email</th>
				<th>Phone</th>
				<th>Action</th>
            </tr>
		</thead>
		<tbody>
		<?php $i=0;
			$aryList=$db->getRows("select *from  staff_manage order by id desc");
					foreach($aryList as $iList)
					{	$i=$i+1;
					
							 ?>       
			<tr>
				<td><?php echo $i ?></td>
				<td><?php echo $iList['staff_id']; ?></td>
					<td><?php echo $iList['first_name']; ?></td>
				
					<td><?php echo $iList['last_name']; ?></td>
				
				
 				
 				<td><?php echo $iList['nationality']; ?></td>
				<td><?php echo $iList['email']; ?></td>
				<td><?php echo $iList['phone']; ?></td>
				
				<td>
					<!--<a href="<?php echo $FileName; ?>?action=edit_staff&randomid=<?php echo $iList['randomid']?>"  class="table-action-btn" >
					<i class="fa fa-pencil"></i> </a>--->
					<a href="javascript:del('<?php echo $FileName; ?>?action=delete_student_father&randomid=<?php echo $iList['randomid']; ?>')"    class="table-action-btn" > Delete </a>
               </td>
            </tr>
				  <?php } ?>
        </tbody>
	</table>
</div>
			  </div>	
			
			
			
			
			<?php } ?>
		
		
		
		</div>
    </div>
  </div>
  <?php include('inc.footer.php'); ?>
</div>
</div>
<?php include('inc.js.php'); ?>
<script>

function getguardian(){
	document.getElementById("guardianno").style.display = "block";
	 
	 
}
</script>

  <script>
  $( function() {
    $( "#datepicker" ).datepicker();
  } );
  </script>
  <script>
  $( function() {
    $( "#datepicker1" ).datepicker();
  } );
  </script>
</body>
</html>
