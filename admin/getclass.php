<?php
include("../config.php");
$nameid2=$_POST['datapost'];
$sub2 =$db->getRows("select * from school_class where section_id='$nameid2'");
echo "select * from school_class where section_id='$nameid2'";
?>
<option value="">select class</option>
<?php
foreach($sub2 as $sub2)
{
?>
<option value="<?php echo $sub2['id'];?>"><?php echo $sub2['name'];?></option>
<?php
}
?>