<?php include('../config.php');
include('inc.session-create.php'); 
$validate = new Validation();

if($_POST['action']=='getsubcatgorysearch')
 {   
?>
<select  class="required form-control" id="subcategory_id" name="subcategory_id" onchange="searchgetAttributes()" >
  <option>Select only category</option>
  <?php     
						  $aryData=$db->getRows("select * from subcategory where category_id='" .$_POST['subcategory']."'");
									  foreach($aryData as $iList)   {  ?>
  <option value="<?php echo $iList['id']; ?>"><?php echo $iList['subcategory']; ?></option>
  <?php  } ?>
</select>
<?php } 
elseif($_POST['action']=='getAttributessearch'){ ?>
<select  class="required form-control" name="attributes_id" id="attributes_id" onchange="getAttributesValues()"  >
  <option>Select Attribute</option>
  <?php $attrib=$db->getRows("select * from search_attributes where  subcategory_id='".$_POST['subcategory_id']."'");
					              foreach($attrib as $abc)
									{	$i=$i+1;?>
  <option value="<?php echo $abc['id'];?>" <?php  if($_POST['attributes_id']==$abc['id']) { echo "selected"; }  ?> > <?php echo $abc['attributes']; ?></option>
  <?php } ?>
</select>
<?php } 
elseif($_POST['action']=='getAttributes'){ ?>
<select  class="required form-control" name="attributes_id" id="attributes_id" onchange="getAttributesValues()"  >
  <option>Select Attribute</option>
  <?php $attrib=$db->getRows("select * from attributes where  subcategory_id='".$_POST['subcategory_id']."'");
					              foreach($attrib as $abc)
									{	$i=$i+1;?>
  <option value="<?php echo $abc['id'];?>" <?php  if($_POST['attributes_id']==$abc['id']) { echo "selected"; }  ?> > <?php echo $abc['attributes']; ?></option>
  <?php } ?>
</select>
<?php } 
elseif($_POST['action']=='getAttributesValues'){ ?>
<select  class="required form-control"  id="attributes_values_id" name="attributes_values_id[]" >
  <option>Select Attribute value</option>
  <?php $attrib=$db->getRows("select * from attribute_values where attributes_id='".$_POST['attributes_id']."' ");
					              foreach($attrib as $abc)
									{	$i=$i+1;?>
  <option value="<?php echo $abc['id']; ?>" <?php  if($_POST['attributes_values_id']==$abc['id']) { echo "selected"; }  ?>><?php echo $abc['attributesname_values']; ?></option>
  <?php } ?>
</select>
<?php }  elseif($_POST['action']=='getattributesdata'){ ?>
<table class="table">
	<tr  id="thisattid_<?php echo $_POST['countid']; ?>">
    <td><input type="hidden" name="countid[]" class=" form-control" value="<?php echo $_POST['countid']; ?>"/>
    	<input type="hidden" name="forinsert<?php echo $_POST['countid']; ?>" id="forinsertid_<?php echo $_POST['countid']; ?>" class=" form-control" value="<?php echo $_POST['countid']; ?>"/></td>
  <?php $iMainAttribute=$db->getRows("select * from attributes where  subcategory_id='".$_POST['subcategory_id']."' and status = '1'");
					              foreach($iMainAttribute as $iMainAtt)
									{	?>
    <td><select  class="required form-control"  id="attributes_values_id" name="attributes_values_id<?php echo $_POST['countid']; ?>[]"  >
        <option>Select <?php echo $iMainAtt['attributes']; ?> value</option>
        <?php $attrib=$db->getRows("select * from attribute_values where attributes_id='".$iMainAtt['id']."' ");
					              foreach($attrib as $abc)
									{	?>
        <option value="<?php echo $abc['id']; ?>" <?php  if($_POST['attributes_values_id']==$abc['id']) { echo "selected"; }  ?>><?php echo $abc['attributesname_values']; ?></option>
        <?php } ?>
      </select>
      <input type="hidden" name="attribute_id<?php echo $_POST['countid']; ?>[]" class=" form-control" value="<?php echo $iMainAtt['id']; ?>"/>
      </td>
      
    <?php } ?>
    <td><input type="text" placeholder="Enter Quantity"  name="qty<?php echo $_POST['countid']; ?>" class=" form-control"/></td>
    <td><input type="text" placeholder="Enter Price"  name="price<?php echo $_POST['countid']; ?>" class=" form-control"/></td>
    <td><input type="file" name="proimages<?php echo $_POST['countid']; ?>[]" multiple /></td>
     <td><a class="btn btn-danger" onClick="removethisatt('<?php echo $_POST['countid']; ?>');" >Remove</a></td>
     
  </tr>
</table>
<?php sleep(1); } 
elseif($_POST['action']=='getcategory')	{	?>

 
 		
         <select name="p_cid" id="p_cid" class="form-control" required onchange="getchildcategory()">
              <option value="">Select Choice</option>
				<?php $aryList=$db->getRows("select * from category_parent where m_cid='".$_POST['m_cid']."' order by id desc");
							foreach($aryList as $iList){  ?>
				<option <?php if($aryDetail['p_cid']==$iList['id']){ echo "selected";} ?> value="<?php echo $iList['id'] ?>"><?php echo $iList['pcategory']; ?> </option>
				<?php } ?>
         </select>
	
<?php } 
elseif($_POST['action']=='getchildcategory')	{	?>
 		<div class="form-group">
		<label>Child Category</label>
         <select name="c_cid" id="c_cid" required class="form-control">
              <option value="">Select Choice</option>
				<?php $aryList=$db->getRows("select * from category_child where m_cid='".$_POST['m_cid']."' and p_cid	='".$_POST['p_cid']."'  order by id desc");
							foreach($aryList as $iList){  ?>
				<option <?php if($aryDetail['c_cid']==$iList['id']){ echo "selected";} ?> value="<?php echo $iList['id'] ?>"><?php echo $iList['c_category']; ?> </option>
				<?php } ?>
         </select>
	</div>
<?php } 
elseif($_POST['action']=="Action_changepass")
{
	$validate->addRule($_POST['old_password'],'','Old Password',true);
	$validate->addRule($_POST['new_password'],'','New Password',true);  
	$validate->addRule($_POST['confirm_password'],'','Confirm Password',true);  
	
	if($validate->validate() && count($stat)==0) 
	{	
		$iRecord=$db->getRow("select * from school_register where id='".$_SESSION['userid']."'");
	
		if($iRecord['password']==$_POST['old_password'])
		{
			if($_POST['new_password']==$_POST['confirm_password'])
			{
				$aryData=array(
								'password'		=>	$_POST['new_password'],
								);
					$flgIn1 = $db->updateAry("school_register",$aryData, "where id ='".$_SESSION['userid']."'");
					echo "1";
					exit;
		    }
			else
			{
				$stat['error'] = "Confirm password do not match";	
			}
		}
		else
		{
			$stat['error'] = "Incorrect old password";
		}
	}	
	else
	{
		$stat['error'] = $validate->errors();
	}
	echo msg($stat);
}	
elseif($_POST['action']=="changesession")
		{
				 echo"1";
	 
	$validate->addRule($_POST['showsession'],'','Session',true);  
	
	if($validate->validate() && count($stat)==0) 
	{	
		 
		 
			 
				$aryData=array(
								'session'        	 => $_POST['showsession'],
								);
					$flgIn1 = $db->updateAry("school_session",$aryData, "where id ='".$_POST['sessionid']."'");
					echo $flgIn1 = $db->getLastQuery();
		 
	}	
		 
	 
}	
elseif($_POST['action']=="displaysection")
{
	$iRecord1=$db->getRow("select * from  school_section where section = '".$_POST['section']."' ");
	$iRecord2=$db->getRow("select * from  school_section where short_name = '".$_POST['otherstext']."' ");
	
	
		if($iRecord1['id']!='' && $_POST['section']=="OTHERS")
	 {
		 if($iRecord2['id']== "")
			{
				
				$aryData1=array(	
								'section'     	 	         			=>	$_POST['section'],
								'short_name'     	 	         		=>	$_POST['otherstext'],
								'sid'     	 	         		 		=>	$_POST['sid'],
								'userid'     	 	         			=>	$_POST['userid'],
								);  
					$flgIn2 = $db->insertAry("school_section", $aryData1);
				
			}
			else{
				
				echo "you cannot add same secttion repeatedly!!";
			}
			 	
	 }
		elseif($iRecord1['id']=='')
		{
			
			$aryData1=array(	
								'section'     	 	         			=>	$_POST['section'],
								'short_name'     	 	         		=>	$_POST['otherstext'],
								'sid'     	 	         		 		=>	$_POST['sid'],
								'userid'     	 	         			=>	$_POST['userid'],
								);  
					$flgIn2 = $db->insertAry("school_section", $aryData1);		
		}	
		
	else
	{ 
		echo "You Cannot Add Same Sections Repeatedly!!"; 
	}

}	
		elseif($_POST['action']=="changesection")
		{
				 echo"1";
	$validate->addRule($_POST['showsection'],'','Session',true);  
	
	if($validate->validate() && count($stat)==0) 
	{	
		 
		 
			 
				$aryData=array(
								'section'        	 => $_POST['showsection'],
								);
					$flgIn1 = $db->updateAry("school_section",$aryData, "where id ='".$_POST['sectionid']."'");
					echo $flgIn1 = $db->getLastQuery();
		 
	}	
		 
	 
		}	
		
		
		
elseif($_POST['action']=="getclasschkbox")
		{ 
		?>
      <h5>Select Class</h5>
		<?php
		$isection=$db->getRows("select * from school_class where section_id='".$_POST['sec_id']."'");
		foreach($isection as $list)
		{?>
		  <span class="gwt-CheckBox" style="display: block;"><input type="checkbox" value="<?php echo $list['id'];?>" name="selectclass[]"  tabindex="0"  ><label  for="gwt-uid-9"></label>  <?php echo $list['name'];?> </span>
		<?php } ?>
        
        
		 
		
		
<?php }



elseif($_POST['action']=="getsubclass")
		{ 
		?>
		<select name="selectclass" id="selectclass" class="form-control" onchange="getclass()">
		<option value="">Select Class</option>
		<?php
		$isection=$db->getRows("select * from school_class where section_id='".$_POST['sec_id']."'");
		foreach($isection as $list)
		{?>
		<option value="<?php echo $list['id'];?>"<?php if($_POST['selectclass']==$list['id']){echo "selected";}?>>
		<?php echo $list['name'];?></option>
		<?php } ?>
		</select>
		
		
<?php } elseif($_POST['action']=="Action_getClass") { ?>
<select name="class_id" id="class_id" class="form-control">
<?php $i=0;
$iSectionList = $db->getRows("select * from school_class where create_by_userid='".$create_by_userid."' and section_id='".$_POST['section_id']."'");
foreach($iSectionList as $iList) 
{ $i=$i+1;
?>
	<option value="<?php echo $iList['id']; ?>" <?php if($_POST['section_id']==$iList['id']) { echo "selected"; } ?>><?php echo $iList['name']; ?></option>
<?php } ?>
</select>


<?php } elseif($_POST['action']=="Action_getassesment") { 
 ?>
  <select class=" form-control" name="assesment" id="assesment" >
 <option>Select Assesment</option>
	<?php $assesment=$db->getRows("select * from school_assessment where class_id='".$_POST['class_id']."' and create_by_userid='".$create_by_userid."'");
	
	
	
	foreach ($assesment as $iList)
	{
		?>
	
	<option  value="<?php echo $iList['id']; ?>" <?php if($_POST['assesment']==$iList['id']) { echo  "selected"; } ?> > <?php echo $iList['assesment']; ?>
	 </option>
	 
	<?php } ?>

                                                   
                                                </select>

<?php } elseif($_POST['action']=="getSubject") { 
 ?>
 <select name="school_subject" id="school_subject" class="form-control">
                      <option value="" >Select Subject </option>
                      <?php $i=0;
                      $aryList=$db->getRows("select * from school_subject where class_id='".$_POST['school_class']."' and create_by_userid='".$create_by_userid."' ");
                      foreach($aryList as $iList)
                      {	$i=$i+1;

                          ?>
						  
						  <option value="<?php echo $iList['id']; ?>" <?php if($_POST['school_subject']==$iList['id']){echo "selected";} ?>> <?php echo $iList['subject'];?></option>
						  
                         
                      <?php } ?>
                  </select>
<?php } ?>

	
		
		
	 