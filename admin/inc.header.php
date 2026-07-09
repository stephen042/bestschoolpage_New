<div class="topbar">
	<div class="topbar-left">
		<div class="text-center"> <img src="../uploads/dezven-logo.png" alt="user-img" class="img-circle" style="border:none!important; visibility:hidden"> </div>
	</div>
	<div class="navbar navbar-default" role="navigation">
    <div class="container">
		<div class="">
			<div class="pull-left">
				<button class="button-menu-mobile open-left"><i class="fa fa-bars"></i></button>
				<span class="clearfix"></span> 
			</div>
							
			<ul class="nav navbar-nav navbar-right pull-right">
				 
				<li class="hidden-xs"> <a href="#" id="btn-fullscreen" class="waves-effect waves-light"><i class="icon-size-fullscreen"></i></a> </li>
				
				<li class="dropdown hidden-xs"> </li>
				<li class="dropdown"> <a class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="true"> <img src="../uploads/<?php echo $iLoginUserDetail['profileimage'] ?>" style="border:none!important;"> </a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo ADMIN_URL; ?>login_profile.php"><i class="ti-user m-r-5"></i> Update Profile</a></li>
						<li><a href="<?php echo ADMIN_URL; ?>login_pass.php"><i class="ti-user m-r-5"></i>Change Password</a></li>
						<li><a href="<?php echo ADMIN_URL; ?>logout.php"><i class="ti-power-off m-r-5"></i> Logout</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
	</div>
</div>