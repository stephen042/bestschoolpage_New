<?php  
if($_SESSION[LOGIN_ADMIN]['id']=='')
	{
		redirect(ADMIN_URL."index.php");	
 	}
$iLoginUserDetail=$db->getRow("select * from admin_login where id='".$_SESSION[LOGIN_ADMIN]['id']."'");
?>