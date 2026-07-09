<?php
 include('../config.php');
include('inc.session-create.php'); 
$PageTitle="Custom Field";
$FileName ='custom_field.php';
$iClassName = ADMIN_URL;
$validate=new Validation();
$iGetDet=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");
if($_SESSION['success']!="")
{
   $stat['success']=$_SESSION['success'];
   unset($_SESSION['success']);
}
if(isset($_POST['addnewrecord']))
 	{
	    $validate->addRule($_POST['field_type'],'','Field type',true);
	    $validate->addRule($_POST['field_name'],'','Field Name',true);
		 
		if($validate->validate() && count($stat)==0)
		{			 												
 			$aryData=array(	
							'app_id'     	 	       =>   $iGetDet['id'],
							'field_type'     	 	   =>	$_POST['field_type'],
 							'field_name'     	 	   =>	$_POST['field_name'],
							'status'     	 	       =>	$_POST['status'],
							);  
					$flgIn = $db->insertAry("custom_field",$aryData);				  
			
		foreach($_POST['value'] as $key=>$val)
		{
			$aryData=array(	
							'app_id'     	 	   =>	$iGetDet['id'],
							'field_id'     	 	   =>	$flgIn,
							'value'     	 	   =>	$_POST['value'][$key],
							);  
				$flgIn12 = $db->insertAry("custom_field_value",$aryData);		
		}
					$_SESSION['success']="Submit Successfully";
					redirect($FileName.'?randomid='.$_GET['randomid']);
		}
	else{
			$stat["error"]=$validate->errors();
		}
	} 
elseif(isset($_POST['update']))
	{ 
		
	    $validate->addRule($_POST['field_type'],'','Field Type',true);
	    $validate->addRule($_POST['field_name'],'','Field Name',true);
		
	if($validate->validate() && count($stat)==0)
		{
			$aryData=array(	
							'app_id'     	 	 	=>	$iGetDet['id'],
							'field_name'     	 	=>	$_POST['field_name'],
							'field_type'     	 	=>	$_POST['field_type'],
							'status'     	 	   	=>	$_POST['status'],
							);  
					$flgIn = $db->updateAry("custom_field", $aryData , "where id='".$_GET['id']."'");
		
		$flgIn13 = $db->delete("custom_field_value","where field_id='".$_GET['id']."'");		
		foreach($_POST['value'] as $key=>$val)
		{
			$aryData=array(	
			                'app_id'     	 	   =>	$iGetDet['id'],
							'field_id'     	 	   =>	$_GET['id'],
							'value'     	 	   =>	$_POST['value'][$key],
							);  
				$flgIn12 = $db->insertAry("custom_field_value",$aryData);		
		}	
			
					$_SESSION['success']="Update Successfully";
					redirect($FileName.'?randomid='.$_GET['randomid']);
		}
	else{
			$stat["error"]=$validate->errors();
		}
 	} 
elseif(($_REQUEST['action']=='delete'))
	{
		$flgIn1 = $db->delete("custom_field","where id='".$_GET['id']."'");	
		$_SESSION['success'] = 'Deleted Successfully';
		redirect($FileName.'?randomid='.$_GET['randomid']);
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
								<?php echo $isubId; ?>
								<ol class="breadcrumb">
									<li>
										<a href="<?php echo $iClassName; ?>">Home</a>
									</li>
									<li class="active">
										<?php echo $PageTitle; ?>
									</li>
								</ol>
							</div>
						</div>
                        <!-- Basic Form Wizard -->
                        <div class="row">
                            <div class="col-md-12">
							<div class="card-box">
							<div class="row">
								<div class="col-sm-8">
									<?php echo msg($stat); ?>
								</div>	
								<div class="col-sm-4">
				<a href="<?php echo $FileName; ?>?randomid=<?php echo$_GET['randomid'];?>&action=add"  class="btn btn-default" style="float:right">Add New Record</a>
								</div>         
							</div>
							</div>
                            </div>
						</div>

<?php if($_GET['action']=='edit') {
	$aryDetail=$db->getRow("select * from  custom_field where id='".$_GET['id']."'"); ?>
	
<div class="card-box">
	<form id="basic-form"  action="" method="post" enctype="multipart/form-data">
        
	<div class="form-group clearfix">
		<label class="col-lg-2 control-label " for="price">Field Type*</label>
		<div class="col-lg-10">
			<select class="form-control" name="field_type" onChange="showDiv(this)">
				<option value="">Select</option>
				<option value="1" <?php if($aryDetail['field_type']=='1') { echo "selected"; } ?>>Text</option>
				<option value="2" <?php if($aryDetail['field_type']=='2') { echo "selected"; } ?>>List</option>
				<option value="3" <?php if($aryDetail['field_type']=='3') { echo "selected"; } ?>>Paragraph</option>
				<option value="4" <?php if($aryDetail['field_type']=='4') { echo "selected"; } ?>>Multi Choice</option>
				<option value="5" <?php if($aryDetail['field_type']=='5') { echo "selected"; } ?>>Single Choice</option>
			</select>
		</div>
	</div>
                                    
	<div class="form-group clearfix">
		<label class="col-lg-2 control-label " for="price">Field Name*</label>
		<div class="col-lg-10">
			<input type="text" class="form-control"  name="field_name" value="<?php echo $aryDetail['field_name'];?>" />
		</div>
	</div>
    
	<div class="form-group clearfix" id="drop-down"
style="display:<?php if($aryDetail['field_type']=='2'or $aryDetail['field_type']=='4'or $aryDetail['field_type']=='5') { echo "block"; } else{ echo "none"; } ?>">
		<label class="col-lg-2 control-label " for="price">Field Value*</label>
	<div class="col-lg-10">
		<table  class="table table-bordered" id="table-data"  style="width:100%!important">
			<thead>
				<tr>
					<th>Value</th>
					<th></th>
				</tr>
			</thead>
			<tbody class="appenew">
			<?php
			$iGetValue=$db->getRows("select * from custom_field_value where field_id='".$_GET['id']."' order by id asc");
			if(count($iGetValue)>0) {
			foreach($iGetValue as $iValue)
			{
			?>
				<tr  class="tr_clone">
					<td><input type="text" name="value[]" class="form-control" value="<?php echo $iValue['value']; ?>"/></td>
					<td><a class="btnAddnew btn btn-danger btn-xs" style="font-size:15px; cursor:pointer" onClick="javascript:deleteRow(this, 'table-data'); return false;"    id="btnremove">Remove</a></td>
				</tr>
			<?php } } else{ ?>	
				<tr  class="tr_clone">
					<td><input type="text" name="value[]" class="form-control" value="<?php echo $iValue['value']; ?>"/></td>
					<td><a class="btnAddnew btn btn-danger btn-xs" style="font-size:15px; cursor:pointer" onClick="javascript:deleteRow(this, 'table-data'); return false;"    id="btnremove">Remove</a></td>
				</tr>
			<?php } ?>
			</tbody>
        </table>
        <table>
            <tr>
				<td><a id="btnAdd" class="btn btn-info" style="font-size:15px; cursor:pointer">Add new</a></td>
            </tr>
		</table>
	</div>
	</div>	
	
	<div class="form-group clearfix">
		<label class="col-lg-2 control-label " for="confirm">Status* </label>
	<div class="col-lg-10">
		<select  class="required form-control" name="status">
			<option value="1" <?php if($aryDetail['status']=='1') { echo "selected"; } ?>>Active</option>
			<option value="0" <?php if($aryDetail['status']=='0') { echo "selected"; } ?>>Inactive</option>
		</select>
	</div>
	</div>
		<input type="submit" name="update" class="btn btn-default"/>
		<a  href="<?php echo  $FileName;  ?>?randomid=<?php echo$_GET['randomid'];?>"  class="btn btn-default" >Back</a>
	</form> 
</div>
<?php  } elseif($_GET['action']=='view')
 { 
	$GetEmailId=$db->getRow("select * from  custom_field where id='".$_GET['id']."'");
		?>
<div class="card-box">
    <section>  
    
	
	<div class="form-group clearfix">
		<label class="col-lg-2 control-label " for="userName">Application :</label>
		<?php echo $db->getVal("select name from application_form where id='".$GetEmailId['app_id']."'"); ?>
    </div>
	<div class="form-group clearfix">
		<label class="col-lg-2 control-label " for="userName">Field Type :</label>
		        <?php if($GetEmailId['field_type']=='1') { echo "Text"; } ?>
				<?php if($GetEmailId['field_type']=='2') { echo "List"; } ?>
				<?php if($GetEmailId['field_type']=='3') { echo "Paragraph"; } ?>
				<?php if($GetEmailId['field_type']=='4') { echo "Multi Choice"; } ?>
				<?php if($GetEmailId['field_type']=='5') { echo "Single Choice"; } ?>
    </div>
	<div class="form-group clearfix">
		<label class="col-lg-2 control-label " for="userName">Field Name:</label>
		<?php echo $GetEmailId['field_name']; ?>
    </div>
	<div class="form-group clearfix" style="display:<?php if($GetEmailId['field_type']=='2'or $GetEmailId['field_type']=='4'or $GetEmailId['field_type']=='5') { echo "block"; } else{ echo "none"; } ?>">
		<label class="col-lg-2 control-label " for="userName">Field Value:</label>
		<table class="table table-striped table-bordered">
		<thead>
			<tr>
                <th>#</th>
				<th><?php echo $GetEmailId['field_name']; ?></th>
            </tr>
		</thead>
		<tbody>
		<?php 
		     $i=0;
			$aryList=$db->getRows("select *from  custom_field_value where field_id='".$_GET['id']."' order by id asc");
					foreach($aryList as $iList)
					{	
					$i=$i+1;
					$aryPgAct["id"]=$iList['id'];
							 ?>       
			<tr>
				<td><?php echo $i ?></td>
				<td><?php echo $iList['value']; ?></td>
            </tr>
			<?php } ?>
        </tbody>
	</table>
    </div>
	<div class="form-group clearfix">
		<label class="col-lg-2 control-label " for="userName">Status :</label>
		<?php if($GetEmailId['status']=='1'){echo "Active";}
			elseif($GetEmailId['status']=='0'){echo "Inactive";} ?>
    </div>
	<a  href="<?php echo  $FileName;  ?>"  class="btn btn-default" >Back</a> 
	</section>
</div>	

<?php  } else { ?>




<div class="card-box">
	<form id="basic-form"  action="" method="post" enctype="multipart/form-data">
	<div class="form-group clearfix">
		<label class="col-lg-2 control-label " for="price">Field Type*</label>
		<div class="col-lg-10">
			<select class="form-control" name="field_type" onChange="showDiv(this)">
				<option value="">Select</option>
				<option value="1" <?php if($_POST['field_type']=='1') { echo "selected"; } ?>>Text</option>
				<option value="2" <?php if($_POST['field_type']=='2') { echo "selected"; } ?>>List</option>
				<option value="3" <?php if($_POST['field_type']=='3') { echo "selected"; } ?>>Paragraph</option>
				<option value="4" <?php if($_POST['field_type']=='4') { echo "selected"; } ?>>Multi Choice</option>
				<option value="5" <?php if($_POST['field_type']=='5') { echo "selected"; } ?>>Single Choice</option>
			</select>
		</div>
	</div>
	
 	<div class="form-group clearfix">
		<label class="col-lg-2 control-label " for="price">Field Name*</label>
		<div class="col-lg-10">
			<input type="text" class="form-control"  name="field_name" value="<?php echo $_POST['field_name'];?>" />
		</div>
	</div>
								
	<div class="form-group clearfix" id="drop-down" 
style="display:<?php if($_POST['field_type']=='2'or $_POST['field_type']== '4'or $_POST['field_type']=='5') { echo "block"; } else{ echo "none"; } ?>">
		<label class="col-lg-2 control-label " for="price">Field Value*</label>
	<div class="col-lg-10">
		<table  class="table table-bordered" id="table-data"  style="width:100%!important">
			<thead>
				<tr>
					<th>Value</th>
					<th></th>
				</tr>
			</thead>
			<tbody class="appenew">
			<?php if($_POST['value']!='') { 
				foreach($_POST['value'] as $key=>$val) { ?>
				<tr  class="tr_clone">
					<td><input type="text" name="value[]" class="form-control" value="<?php echo $_POST['value'][$key]; ?>"/></td>
					<td><a class="btnAddnew btn btn-danger btn-xs" style="font-size:15px; cursor:pointer" onClick="javascript:deleteRow(this, 'table-data'); return false;"    id="btnremove">Remove</a></td>
				</tr>
			<?php } } else{ ?>
				<tr  class="tr_clone">
					<td><input type="text" name="value[]" class="form-control" value="<?php echo $_POST['value']; ?>"/></td>
					<td><a class="btnAddnew btn btn-danger btn-xs" style="font-size:15px; cursor:pointer" onClick="javascript:deleteRow(this, 'table-data'); return false;"    id="btnremove">Remove</a></td>
				</tr>
			<?php } ?>	
			</tbody>
        </table>
        <table>
            <tr>
				<td><a id="btnAdd" class="btn btn-info" style="font-size:15px; cursor:pointer">Add new</a></td>
            </tr>
		</table>
	</div>
	</div>
   
	<div class="form-group clearfix">
        <label class="col-lg-2 control-label " for="confirm">Status* </label>
	<div class="col-lg-10">
		<select  class="required form-control" name="status">
            <option value="1" <?php if($_POST['status']=='1') { echo "selected"; } ?>>Active</option>
            <option value="0" <?php if($_POST['status']=='0') { echo "selected"; } ?>>Inactive</option>
		</select>
	</div>
	</div>
										
	<button type="submit" name="addnewrecord" class="btn btn-default">Submit</button>
									
	</form>
</div>
<div class="card-box">
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
                <th>#</th>
 				<th>Application Name</th>
 				<th>Field Type</th>
 				<th>Field Name</th>
				<th>Status</th>
				<th>Action</th>
            </tr>
		</thead>
		<tbody>
		<?php $i=0;
			$aryList=$db->getRows("select *from  custom_field order by id desc");
					foreach($aryList as $iList)
					{	$i=$i+1;
					$aryPgAct["id"]=$iList['id'];
					 $appDetails=$db->getRow("select * from application_form where id='".$iList['app_id']."'");
							 ?>       
			<tr>
				<td><?php echo $i ?></td>
				<td><?php echo $appDetails['name']; ?></td>
				
 				<td>
				<?php if($iList['field_type']=='1') { echo "Text"; } ?>
				<?php if($iList['field_type']=='2') { echo "List"; } ?>
				<?php if($iList['field_type']=='3') { echo "Paragraph"; } ?>
				<?php if($iList['field_type']=='4') { echo "Multi Choice"; } ?>
				<?php if($iList['field_type']=='5') { echo "Single Choice"; } ?>
				</td>
 				<td><?php echo $iList['field_name']; ?></td>
				<td><?php if($iList['status']=='1') { echo "Active"; } ?>
					<?php if($iList['status']=='0') { echo "Inactive"; } ?></td>
				<td><a href="<?php echo $FileName; ?>?action=view&id=<?php echo $iList['id']; ?>" class="table-action-btn"> 
				<i class="fa fa-search"></i></a>
					<a href="<?php echo $FileName; ?>?action=edit&randomid=<?php echo $appDetails['randomid']?>&id=<?php echo $iList['id']; ?>"  class="table-action-btn" >
					<i class="fa fa-pencil"></i> </a>
					<a href="javascript:del('<?php echo $FileName; ?>?action=delete&id=<?php echo $iList['id']; ?>')"    class="table-action-btn" > <i class="fa fa-times"></i> </a>
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
<?php include('inc.footer.php'); ?>
<?php include('inc.js.php'); ?>
<script type="text/javascript">
function showDiv(select)
{ 
	if(select.value==2 || select.value==4 || select.value==5){
		document.getElementById('drop-down').style.display = "block";
	}
	else{
		document.getElementById('drop-down').style.display = "none";
	}
} 
</script>	

<script type="text/javascript">
$(document).ready(function() {	
		$("#btnAdd").on("click",function(){
		var $tableBody = $('#table-data').find("tbody"),
		$trLast = $tableBody.find("tr:last"),
		$trNew = $trLast.clone();
		$trLast.after($trNew);
		 });	 
});
</script> 
<script type="text/javascript">
 var deleteRow = function (link, getthis) {
 var rowCount = $('#'+getthis+' >tbody >tr').length;	
  if(rowCount>1)	
 	{
     var row = link.parentNode.parentNode;
     var table = row.parentNode; 
     table.removeChild(row);
	 } 
 }
</script>
</body>
</html>