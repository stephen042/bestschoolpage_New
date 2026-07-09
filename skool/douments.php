<?php include('../config.php'); 
//include('inc.session-create.php'); 
$PageTitle="Form";
$FileName = 'douments.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
$stat['success']=$_SESSION['success'];
unset($_SESSION['success']);
}
	if(isset($_POST['submit']))
		{ 
			$validate->addRule($_POST['title'],'','Title',true);
			$validate->addRule($_POST['description'],'','Description',true);

			if($validate->validate() && count($stat)==0)
				{
	
					if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
					{	 
					$filename = basename($_FILES['image']['name']);
					$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
					if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
					{ 	  
					$newfile=md5(time())."_".$filename;
					move_uploaded_file($_FILES['image']['tmp_name'],"../uploads/".$newfile);
					}				
					}


                   if(isset($_FILES["android_icon"]["name"]) && !empty($_FILES["android_icon"]["name"]))
					{	 
					$filename = basename($_FILES['android_icon']['name']);
					$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
					if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
					{ 	  
					$newfile1=md5(time())."_".$filename;
					move_uploaded_file($_FILES['android_icon']['tmp_name'],"../uploads/".$newfile1);
					}				
					} 					

					$aryData=array(	
					'title'     	 	         		=>	$_POST['title'],
					
					'android_icon'     	 	         		 =>	$newfile1,

					'description'     	 	            =>	$_POST['description'],

					'image'     	 	         		 =>	$newfile,

					'status'     	 	         		 =>	$_POST['status'],	

					);  
					$flgIn1 = $db->insertAry("slider",$aryData);
					
					$_SESSION['success']="Submited Successfully";
					redirect($FileName);
					unset($_POST);
					 
				}
			else {
					$stat['error'] = $validate->errors();
				}
			} 
	elseif(isset($_POST['update']))
		{ 
		if($validate->validate() && count($stat)==0)
		{ 
 			if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
			{	 
			$filename = basename($_FILES['image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
				if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
				{ 	  
					$newfile=md5(time())."_".$filename;
					move_uploaded_file($_FILES['image']['tmp_name'],"../uploads/".$newfile);
				}				
			}         
			else { $newfile =$_POST['image_old']; }
			
			if(isset($_FILES["android_icon"]["name"]) && !empty($_FILES["android_icon"]["name"]))
			{	 
			$filename = basename($_FILES['android_icon']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
				if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
				{ 	  
					$newfile1=md5(time())."_".$filename;
					move_uploaded_file($_FILES['android_icon']['tmp_name'],"../uploads/".$newfile1);
				}				
			}         
			else { $newfile =$_POST['android_icon_old']; }

					$aryData=array(	
					
					'title'     	 	         		=>	$_POST['title'],
					
					'android_icon'     	 	         		 =>	$newfile1,

					'description'     	 	            =>	$_POST['description'],

					'image'     	 	         		 =>	$newfile,

					'status'     	 	         		 =>	$_POST['status'],	
					);  
					
					$flgIn = $db->updateAry("slider", $aryData , "where id='".$_GET['id']."' ");
					
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
		
			$flgIn1 = $db->delete("slider","where id='".$_GET['id']."' ");			
			$_SESSION['success'] = 'Deleted Successfully';
			redirect($FileName);
		} 
?>
<!DOCTYPE html>
<html>
<head>
<?php include('inc.meta.php'); ?>
<link href="assets/css/pak-page.css" rel="stylesheet" type="text/css" />
</head>
<body class="fixed-left">
<div id="wrapper">
  <?php include('inc.header.php'); ?>
  <?php include('inc.sideleft.php'); ?>
  <div class="content-page">
  
    <!-- Start content -->
    <div class="content">
      <div class="container">
	    <div class="pak-page">
	    <div class="modal fade" id="pawan" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content man">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title shailu">Upload Document/Report Template</h4>
        </div>
        <div class="modal-body">
          <div class="modal-content  ">
   
   <div class="row">
      <div class="l8 s12 m12 col">
         <div class="input-field" style="margin-top: 30px;">
            <div class="select-wrapper gwt-ListBox">
               
			   
			   <div class="dropdown">
  <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">DEMO
  <span class="caret"></span></button>
  <ul class="dropdown-menu kan">
    <li><a href="#">DEMO</a></li>
  
  </ul>
</div>
            </div>
            <label>Select Application</label><span class="material-label"></span>
         </div>
      </div>
      <div class="s12 m12 l4 col">
         <div class="input-field spnki" style="margin-top: 30px;">
            <div class="select-wrapper gwt-ListBox">
               
						   <div class="dropdown kal">
  <button class="btn btn-primary dropdown-toggle selLabel" type="button" data-toggle="dropdown">DEMO
  <span class="caret"></span></button>
    <input type="hidden" name="cd-dropdown">
  <ul class="dropdown-menu dropdown-list">
    <li class=" active selected" data-value="1"><span>APPLICATION_FORM</span></li>
                  <li class="" data-value="2"><span>ADMISSION_LETTER</span></li>
                  <li class="" data-value="3"><span>OTHER_DOCUMENTS</span></li>
                  <li class="" data-value="4"><span>EXAM_RESULT</span></li>
                  
  
  </ul>
</div>

		</div>
            <label>Choose Document Type</label><span class="material-label"></span>
         </div>
      </div>
      <div aria-hidden="true" class="s12 l12 m12 col" style="display: none;">
         <div class="input-field"><input type="text" class="gwt-TextBox" id="gwt-uid-2"><label for="gwt-uid-2">Enter document description.</label><span aria-hidden="true" class="material-label" style="display: none;"></span></div>
      </div>
   </div>
   <div class="row">
      <div class="fargusan pull-right">
<button type="button" class="white btn-flat right teal-text pathan" style="cursor: pointer; margin-top: 20px;" data-toggle="modal" data-target="#myModal"><span class="scetion"><i class="fa fa-cloud-upload" aria-hidden="true"></i>Choose Document</span><span class="shi"><input type="file" ></button>
   </div>
   </div>
   
    <div class="row">
      <div class="sectio-select">
      <button type="button" class="white btn-flat right teal-text" style="cursor: pointer; margin-top: 20px;"><span>cancel</span></button>
   </div>
   </div>
</div>
        </div>
      </div>
      
    </div>
  </div>
  

        <!-- Page-Title -->
        <div class="row">
          <div class="col-sm-12">
            <h4 class="page-title licat">REPORT TEMPLATE CONFIGURATION</h4>
            <ol class="breadcrumb">
              <li class="dippi"> <a href="<?php echo $iClassName; ?>">Upload word document</a> </li>
              
            </ol>
          </div>
        
		
		</div>
        <!-- Basic Form Wizard -->
        <div class="row">
          <div class="col-md-12">
              <div class="row">
                <div class="col-md-9"> <?php echo msg($stat); ?> </div>
                <div class="col-md-3">
				<div class="gokul">
				<a href=""  class="btn btn-default" style="float:right" data-toggle="modal" data-target="#pawan"><span >New Document</span><span class="din"><i class="fa fa-cloud-upload" aria-hidden="true"></i></span></a>
				</div>
              </div>
            </div>
			</div>
			 <div class="col-md-12 ">
            <?php if($_GET['action']=='add') { ?>
            <div class="card-box bfrcs">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
				  <div class="col-lg-12">
				  <div class="apli">
				  <span class="romte">Enter Application Form Details. </span>
				  </div>
				  </div>
                     
                    <div class="form-group clearfix plims">
                      <div class="col-lg-12">
					 	<input autocomplete="off" class="form-control" placeholder="Application Name" type="text">
                      </div>
                    </div>
					<div class="form-group clearfix plims">
                      <div class="col-lg-12">
					  <div class="row">
					  <div class="col-md-4 selson">
					<label class="active" for="gwt-uid-16">Select Session</label>
                    <select class="volsab">
                    <option value="volvo" selected>2017-18</option>
                    <option value="saab">2018-19</option>
                    </select>
                    </div>
					 <div class="col-md-4 selson">
					  <label class="active" for="gwt-uid-16">Select section</label>
					 <select class="volsab">
                    <option value="volvo" selected>Nursery</option>
                    <option value="saab">Primary</option>
					<option value="saab">Secondary</option>
                    </select>
					 </div>
					</div>
					</div>
                    </div>


					<div class="form-group clearfix plims ">
                      <div class="col-lg-12">
                    <div class="row">
					  <div class="col-md-4">
                    <input autocomplete="off" class="form-control" placeholder="Application start date" type="text">
                    </div>
					 <div class="col-md-4">
					<input autocomplete="off" class="form-control" placeholder="Application end date" type="text">
					 </div>
					 <div class="col-md-4">
					<input autocomplete="off" class="form-control" placeholder="Application Fee" type="text">
					 </div>
					</div>
                      </div>
                    </div>

					<div class="form-group clearfix gardin">
                      <div class="col-lg-12">
                       <div class="row">
					  <div class="col-md-5">
                    <span class="gwt-CheckBox" style="display: block;"><input type="checkbox" value="on" id="gwt-uid-2" tabindex="0"><label for="gwt-uid-2">Require Guardian</label></span>
                    </div>
					 <div class="col-md-7">
					<span class="green-text material-label">Select Payment Methods</span>
					<div><span class="gwt-CheckBox" style="display: block;"><input type="checkbox" value="on" id="gwt-uid-9" tabindex="0" name="payment"><label for="gwt-uid-9">Scratch Card</label></span>
					<span class="gwt-CheckBox" style="display: block;"><input type="checkbox" value="on" id="gwt-uid-10" tabindex="0" name="payment"><label for="gwt-uid-10">Online</label></span>
					<span class="gwt-CheckBox gwt-CheckBox-disabled" style="display: block;"><input type="checkbox" value="on" id="gwt-uid-11" tabindex="0" name="payment" disabled=""><label for="gwt-uid-11">No Payment</label></span>
					</div>
					 </div>
					 
					</div>
                      </div>
                    </div>

                    <div class="form-group clearfix plims dokra">
                      <div class="col-lg-12">
					   <div class="row pots">
					  <div class="col-md-7">
                        <div class="input-field">
						<label class="active" for="gwt-uid-5">Application Form Prefix</label>
						<input type="text" class="gwt-TextBox" placeholder="SKL/2014/" id="gwt-uid-5">
						<span aria-hidden="true" class="material-label" style="display: none;"></span>
						</div>
						</div>
					  <div class="col-md-5">
					  <div class="input-field">
					   <label class="active" for="gwt-uid-16">Form No Range</label>
					  <input type="text" class="gwt-TextBox" placeholder="0000" id="gwt-uid-16">
					 <span aria-hidden="true" class="material-label" style="display: none;"></span>
					 </div>
					  </div>
						</div>
                      </div>
                    </div>
					<div class="form-group clearfix  ">
                      <div class="col-lg-12 sgot">
					   <div class="row">
                    <div class="savdtls"><button type="submit" name="submit" class="btn btn-default">Save details</button></div>
					</div>
					</div>
					</div>
                </div>
              </form>
            </div>
            <?php } elseif($_GET['action']=='edit') { 
			$aryDetail=$db->getRow("select * from  slider where id='".$_GET['id']."'");	
					?>
            <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
                   
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Title</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control " id="title" name="title" value="<?php echo $aryDetail['title']; ?>">
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Android Icon </label>
                      <div class="col-lg-10">
                        <input type="file" class="form-control "  id="android_icon" name="android_icon">
                        <input type="hidden" class="form-control "  id="android_icon_old" name="android_icon_old"  value="<?php echo $aryDetail['android_icon'] ?>" >
                        <img src="../uploads/<?php echo $aryDetail['android_icon'] ?>" style="height:50px;"> </div>
                    </div>

					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Description </label>
                      <div class="col-lg-10">
                     <textarea class="form-control  "  name="description" ><?php echo $aryDetail['description']; ?></textarea>
                      </div>
                    </div>

					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Image </label>
                      <div class="col-lg-10">
                        <input type="file" class="form-control "  id="image" name="image">
                        <input type="hidden" class="form-control "  id="image_old" name="image_old"  value="<?php echo $aryDetail['image'] ?>" >
                        <img src="../uploads/<?php echo $aryDetail['image'] ?>" style="height:50px;"> </div>
                    </div>

                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="confirm">Status </label>
                      <div class="col-lg-10">
                        <select  class=" form-control" name="status">
                          <option value="1" <?php if($aryDetail['status']=='1') { echo "selected"; } ?>>Active</option>
                          <option value="0" <?php if($aryDetail['status']=='0') { echo "selected"; } ?>>Inactive</option>
                        </select>
                      </div>
                    </div>
                    <button type="submit" name="update" class="btn btn-default">Submit</button>
                    <a  href="<?php echo $FileName; ?>"  class="btn btn-default" >Back</a> </section>
                </div>
              </form>
			  
            </div>
            <?php  } 
	elseif($_GET['action']=='view') { 
	$GetEmailId=$db->getRow("select * from  slider where id='".$_GET['id']."'");
	?>
            <div class="card-box">
              <section>
                 
                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Title :</label>
                  <?php echo $GetEmailId['title']; ?> </div>
				  
				  
				   <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Android Icon :</label>
                  <img src="../uploads/<?php echo $GetEmailId['android_icon']; ?>" style="height:50px;"> </div>

                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Description :</label>
                  <?php echo $GetEmailId['description']; ?> </div>

                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Image :</label>
                  <img src="../uploads/<?php echo $GetEmailId['image']; ?>" style="height:50px;"> </div>

                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Status :</label>
                  <?php if($GetEmailId['status']=='1'){echo "Active";}
				  elseif($GetEmailId['status']=='0'){echo "Inactive";} ?>
                </div>
                <a  href="<?php echo $FileName; ?>"  class="btn btn-default" >Back</a> </section>
            </div>
          </div>
          <?php } else { ?>
		  
		  <div class="s12 m12 l12 col">
         <div class="white">
            <div class="page-wrapper">
               <div class="grey lighten-3 grey-text valign-wrapper empty-state center-align" style="height: 40vh;">
                  <div class="valign center" style="width: 100%;">
                     <div>
					 <span class="whiet-spe">
                        <i class="fa fa-file" aria-hidden="true"></i>
						</span>
                        <h4 class="toddadil">Document/Reports Empty</h4>
                        <p>Please wait while loading your information</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
        
		  <?php } ?>
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
$(document).ready(function() {
  
  $(".selLabel").click(function () {
    $('.dropdown').toggleClass('active');
  });
  
  $(".dropdown-list li").click(function() {
    $('.selLabel').text($(this).text());
    $('.dropdown').removeClass('active');
    $('.selected-item p span').text($('.selLabel').text());
  });
  
});
</script>
</body>
</html>
