<?php $iCurrentFileName = basename($_SERVER['PHP_SELF']);
$currentfie = basename($_SERVER["SCRIPT_FILENAME"], '.php').".php"; 

if($_SESSION[LOGIN_ADMIN]['usertype']!='0') 
{
	if($currentfie!='dashboard.php') 
	{
		$aryDetail123Per=$db->getVal("select id from file_permission where user_role='".$_SESSION[LOGIN_ADMIN]['user_type']."' and file_name='".$currentfie."'");
		if($aryDetail123Per=='') 
		{ 
			redirect('dashboard.php');
		} 					
	}
	
	$menu=$db->getRows("select * from file_permission where user_role='".$_SESSION[LOGIN_ADMIN]['user_type']."'");	
	foreach($menu as $iList)
	{
		$file[] = $iList['file_name'];
	} 
}
else 
{
	$menu=$db->getRows("select * from file_name ");	
	
	foreach($menu as $iList)
	{
		$file[] = $iList['file_name'];
	}
}


 ?>

<div class="left side-menu">
  <div class="sidebar-inner slimscrollleft">
    <div class="user-details">
      <div class="pull-left"> <img src="../uploads/<?php echo $iLoginUserDetail['profileimage'] ?>" style="height:50px;"> </div>
      <div class="user-info">
        <div class="dropdown"> <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"> <?php echo $iLoginUserDetail['fullname']; ?> <span class="caret"></span></a>Administrator
          <ul class="dropdown-menu">
            <li> <a href="<?php echo ADMIN_URL; ?>login_profile.php"><i class="md md-face-unlock"></i> Profile
              <div class="ripple-wrapper"></div>
              </a> </li>
            <li><a href="<?php echo ADMIN_URL; ?>settings.php"><i class="md md-settings"></i> Settings</a></li>
            <li><a href="javascript:void(0)"><i class="md md-lock"></i> Lock screen</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div id="sidebar-menu">
      <ul>
        <li class="text-muted menu-title">Navigation</li>
        <li><a href="dashboard.php" class="waves-effect <?php if($iCurrentFileName=='dashboard.php') { echo "active"; } ?>"><i class="ti-home"></i> <span> Dashboard </span> </a></li>
        <li class=""> <a href="school_register.php" class="waves-effect <?php if($iCurrentFileName=='school_register.php') { echo "active"; } ?>"><i class="fa fa-anchor"></i> <span>Registerd school</span> </a></li>
        <li class=""> <a href="sms_paln.php" class="waves-effect <?php if($iCurrentFileName=='sms_paln.php') { echo "active"; } ?>"><i class="fa fa-anchor"></i> <span>Sms Plan</span> </a></li>
        <?php  if(in_array( 'manage_staff.php', $file) || in_array( 'user_type.php', $file) || in_array( 'file_name.php', $file))  { ?>
        <li class="has_sub"><a href="#" class="waves-effect "><i class="ti-paint-bucket"></i> <span>Manage Staff</span> </a>
          <ul class="list-unstyled">
            <?php if(in_array( 'manage_staff.php', $file)) { ?>
            <li class=""><a href="<?php echo ADMIN_URL; ?>manage_staff.php" class="waves-effect <?php if($iCurrentFileName=='manage_staff.php') { echo "active"; } ?>"> <i class="fa fa-user-plus" aria-hidden="true"></i> <span>Register Staff</span> </a> </li>
            <?php } ?>
            <?php if(in_array( 'user_type.php', $file)) { ?>
            <li class=""><a href="<?php echo ADMIN_URL; ?>user_type.php" class="waves-effect <?php if($iCurrentFileName=='user_type.php') { echo "active"; } ?>"> <i class="fa fa-user-secret" aria-hidden="true"></i> <span>Staff Persimission</span> </a> </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
        <li class=""> <a href="slider.php" class="waves-effect <?php if($iCurrentFileName=='slider.php') { echo "active"; } ?>"><i class="fa fa-industry"></i> <span>Parent Login Slider</span> </a></li>
        <li class=""> <a href="state.php" class="waves-effect <?php if($iCurrentFileName=='state.php') { echo "active"; } ?>"><i class="fa fa-industry"></i> <span>State</span> </a></li>
        <li class=""> <a href="local_gov.php" class="waves-effect <?php if($iCurrentFileName=='local_gov.php') { echo "active"; } ?>"><i class="fa fa-industry"></i> <span>Local Govt.</span> </a></li>
        <li class=""> <a href="package.php" class="waves-effect <?php if($iCurrentFileName=='package.php') { echo "active"; } ?>"><i class="fa fa-user"></i> <span>Package</span> </a></li>
        
        
        
        
          <li class="has_sub"> <a href="#" class="waves-effect <?php if($iCurrentFileName=='groups.php' || $iCurrentFileName=='group-contact.php'  ) { echo "active"; } ?>"><i class="ti-comment"></i> <span>Manage Website</span> <span class="menu-arrow"></span></a>
          <ul class="list-unstyled">
            <li class=""><a href="cms.php" class="waves-effect <?php if($iCurrentFileName=='cms.php') { echo "active"; } ?>"><i class="ti-arrow-right"></i> <span> CMS </span> </a></li>
            <li class=""><a href="clients.php" class="waves-effect <?php if($iCurrentFileName=='clients.php') { echo "active"; } ?>"><i class="ti-arrow-right"></i> <span> Our Best Client </span> </a></li>
            <li class=""><a href="home-image.php" class="waves-effect <?php if($iCurrentFileName=='home-image.php') { echo "active"; } ?>"><i class="ti-arrow-right"></i> <span> Home Image </span> </a></li>

<li class=""><a href="contact.php" class="waves-effect <?php if($iCurrentFileName=='contact.php') { echo "active"; } ?>"><i class="ti-arrow-right"></i> <span> Contact Us </span> </a></li>

            
            <li class=""><a href="subscribe.php" class="waves-effect <?php if($iCurrentFileName=='subscribe.php') { echo "active"; } ?>"><i class="ti-arrow-right"></i> <span> Subscribe </span> </a></li>
            <li class=""><a href="why-chose-us.php" class="waves-effect <?php if($iCurrentFileName=='why-chose-us.php') { echo "active"; } ?>"><i class="ti-arrow-right"></i> <span>Why Choose Us </span> </a></li>
          </ul>
        </li>
        
        
        
        
 
       
        <li class=""> <a href="settings.php" class="waves-effect <?php if($iCurrentFileName=='settings.php') { echo "active"; } ?>"><i class="ti-settings"></i> <span>Settings</span> </a></li>
        <li class=""> <a href="logout.php" class="waves-effect <?php if($iCurrentFileName=='logout.php') { echo "active"; } ?>"><i class="fa fa-sign-out"></i> <span>Logout</span> </a></li>
      </ul>
      <div class="clearfix"></div>
    </div>
    <div class="clearfix"></div>
  </div>
</div>
