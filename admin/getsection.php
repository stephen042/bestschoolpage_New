<?php
include("../config.php");
$nameid1=$_POST['datapost'];
$sub1 =$db->getRows("select * from school_section where sid='$nameid1'");
?><option value="">--select--</option><?php
foreach($sub1 as $sub1)
{
?>
<option value="<?php echo $sub1['id'];?>"><?php echo $sub1['section'];?></option>
<?php
}
?>