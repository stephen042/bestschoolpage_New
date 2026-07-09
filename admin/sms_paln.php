<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="SMS Plan";
$FileName = 'sms_paln.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
$stat['success']=$_SESSION['success'];
unset($_SESSION['success']);
}
	if(isset($_POST['add_plan']))
{                
				$validate->addRule($_POST['title'],'','Title',true);
				$validate->addRule($_POST['no_of_sms'],'',' No Of Sms',true);
				$validate->addRule($_POST['price'],'','Price',true);
				//$validate->addRule($_POST['exp_date'],'','Expiry Date',true);
	
									
			    if($validate->validate() && count($stat)==0)
				  {
				  	  
					  
					$aryData=array(	
								'title'                                      => $_POST['title'],
								'no_of_sms'                                   => $_POST['no_of_sms'],
								'price'                                   => $_POST['price'],
								//'exp_date'                                   => $_POST['exp_date'],
								'status'                                     => $_POST['status'],
								'randomid'                                    => randomFix(20),
								
					            );  
					$flgIn1 = $db->insertAry("sms_plan",$aryData);
					
					
					
					$stat['success']="Submited Successfully";
					redirect($FileName);
					unset($_POST);
					 
				}
			else {
					$stat['error'] = $validate->errors();
				}
			}

elseif(isset($_POST['edit_plan']))
		{ 
		
		$validate->addRule($_POST['title'],'','Title',true);
				$validate->addRule($_POST['no_of_sms'],'',' No Of Sms',true);
				$validate->addRule($_POST['price'],'','Price',true);
		//$validate->addRule($_POST['exp_date'],'','Expiry Date',true);
									
			    if($validate->validate() && count($stat)==0)
				  {
				  	  
					  
					$aryData=array(	
								'title'                                      => $_POST['title'],
								'no_of_sms'                                   => $_POST['no_of_sms'],
								'price'                                       => $_POST['price'],
								//'exp_date'                                   => $_POST['exp_date'],
								'status'                                     => $_POST['status'],
								
								
					            );
					$flgIn = $db->updateAry("sms_plan", $aryData , "where id='".$_GET['id']."' ");
					
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
			$flgIn1 = $db->delete("sms_plan","where id='".$_GET['id']."'");			
			$_SESSION['success'] = 'Deleted Successfully';
			redirect($FileName);
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
              <li> <a href="attendancedashbord.php">Home</a> </li>
              <li class="active"> <?php echo $PageTitle; ?> </li>
            </ol>
          </div>
        </div>
        <!-- Basic Form Wizard -->
        <div class="row">
          <div class="col-md-12">
            <div class="card-box aplhanewclass">
              <div class="row">
                <div class="col-md-9"> <?php echo msg($stat); ?> </div>
                <div class="col-md-3">
				<a href="<?php echo $FileName; ?>?action=add"  class="btn btn-default" style="float:right">Add New Plan</a> 
				</div>
              </div>
            </div>
              <?php 	if($_GET['action']=='add') { ?>
              <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>				 				  
				   <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Title</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control" id="title"  name="title" value="<?php echo $_POST['title']; ?>">
                      </div>
                    </div>
                   
					  
					  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">No. Of Sms</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="no_of_sms"  value="<?php echo $_POST['no_of_sms']; ?>"></textarea>
                      </div>
                      </div>
					  
					   <!---<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Expiry Date</label>
                      <div class="col-lg-10"> 
                      <input type="text" class="form-control datepicker"  name="exp_date"  value="<?php echo $_POST['exp_date']; ?>">
                      </div>
                      </div>--->
					  
					  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Price</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="price"  value="<?php echo $_POST['price']; ?>">
                      </div>
                      </div>
	 					
					
                      
                      
					   
					  
					  
						<div class="form-group clearfix">
						<label class="col-lg-2 control-label " for="confirm">Status :</label>
						<div class="col-lg-10">
						<select  class="required form-control" name="status">
						<option value="1" <?php if($_POST['status']=='1') { echo "selected"; } ?>>Active</option>
						<option value="0" <?php if($_POST['status']=='0') { echo "selected"; } ?>>Inactive</option>
						</select>
						</div>
						</div>
                  <button type="submit" name="add_plan" class="btn btn-default">Submit</button>
					<a  href="<?php echo ADMIN_URL; ?>sms_paln.php"  class="btn btn-default" >Back</a> </section>
                </div>
              </form>
            </div>
		
			  <?php } elseif($_GET['action']=='edit') {
               $getDetails=$db->getRow("select * from  sms_plan where id='".$_GET['id']."'"); ?>
			   
			   <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>				 				  
				   <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Title</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control" id="title"  name="title" value="<?php echo $getDetails['title']; ?>">
                      </div>
                    </div>
                   
					  
					  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">No. Of Sms</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="no_of_sms"  value="<?php echo $getDetails['no_of_sms']; ?>"></textarea>
                      </div>
                      </div>
					  
					  <!---<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Expiry Date</label>
                      <div class="col-lg-10"> 
                      <input type="text" class="form-control datepicker"  name="exp_date"  value="<?php echo $getDetails['exp_date']; ?>">
                      </div>
                      </div>--->
					  
					  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Price</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="price"  value="<?php echo $getDetails['price']; ?>">
                      </div>
                      </div>
	 					
					
                      
                      
					   
					  
					  
						<div class="form-group clearfix">
						<label class="col-lg-2 control-label " for="confirm">Status :</label>
						<div class="col-lg-10">
						<select  class="required form-control" name="status">
						<option value="1" <?php if($getDetails['status']=='1') { echo "selected"; } ?>>Active</option>
						<option value="0" <?php if($getDetails['status']=='0') { echo "selected"; } ?>>Inactive</option>
						</select>
						</div>
						</div>
                  <button type="submit" name="edit_plan" class="btn btn-default">Submit</button>
					<a  href="<?php echo ADMIN_URL; ?>sms_paln.php"  class="btn btn-default" >Back</a> </section>
                </div>
              </form>
            </div>
			   
			  
			
				<?php  } 
				 else { ?>
			  <div class="card-box">
            <table id="datatable" class="table table-striped table-bordered">
              <thead>
                <tr>			
                  <th>#</th>			
                  <th>Title</th>
				  <th>No. OF Sms</th>
				  <th>Price</th>
				 <!-- <th>Expiry Date</th>--->
				  
				  <th>Status</th>
				  <th>Action</th>
                </tr>
              </thead>
              <tbody>
			   
				<?php
				$i=0;
				$aryList=$db->getRows("select * from sms_plan  order by id desc");
				foreach($aryList as $iList)
				{	
				$i++;
				
				?>
                <tr>
				<td><?php echo $i ?></td>
				<td><?php echo $iList['title']?></td>
				<td><?php echo $iList['no_of_sms']; ?></td>
				
				<td><?php echo $iList['price']; ?></td>
				<!---<td><?php echo $iList['exp_date']; ?></td>--->
				<td><?php if($iList['status']=='0'){echo "Inactive";} else{ echo "Active";}?></td>
                  <td>
				  
				  </a> 
                  <a href="<?php echo $FileName; ?>?action=edit&id=<?php echo $iList['id']; ?>"  class="table-action-btn" >
				  <i class="fa fa-pencil"></i> </a> 
                  <a href="javascript:del('<?php echo $FileName; ?>?action=delete&id=<?php echo $iList['id']; ?>')"class="table-action-btn" >
				   <i class="fa fa-times"></i> </a>
				   
				   
				 
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
  <script>
  function getguaidian(getid)
  {
	  if(getid==1)
	  {
		
		  document.getElementById("no_of_guardi").style="display:block";
	  }
	  else
	  {
		  document.getElementById("no_of_guardi").style="display:none";
		  document.getElementById("no_of_guardian").value="0";
	  }
	  
  }
  </script>
  <script>
  function getsection()
  {
  var ses_id= document.getElementById("session").value;
  $.post("ajax.php",
  {
	 action:"getsection",
     ses_id:ses_id,   	 
  },
  function(data)
  {  
	  document.getElementById('showsection').innerHTML=data;
  });
  }
</script> 
<script>
  function getclass()
  {
	
  var sec_id= document.getElementById("section").value;
 
  $.post("ajax.php",
  {
	 action:"getclass",
     sec_id:sec_id,   	 
  },
  function(data)
  { 
	  document.getElementById('showclass').innerHTML=data;
  });
  }
</script>
<script>
function getsubject()
  {
		var selected = new Array();
		var chks = document.getElementsByClassName("classList");
		for (var i = 0; i < chks.length; i++)
		{
		if (chks[i].checked) {
		selected.push(chks[i].value);
		}
		}

		var class_iid=selected.join(",");

		$.post("ajax.php",
		{
		action:"getsubject",
		class_iid:class_iid,   	 
		},
		function(data)
		{ 
		
		document.getElementById('showsubject').innerHTML=data;
		});


};
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
  <?php include('inc.footer.php'); ?>
</div>
</div>
<?php include('inc.js.php'); ?>
</body>
</html>