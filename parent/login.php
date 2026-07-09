<?php include('../config.php'); 
$validate=new Validation();
$stat=array(); // Initialize $stat
if(isset($_POST['login']))
{ 


		
		 
		if($validate->validate() && count($stat)==0)
			{
				
	$aryChkName=$db->getRow("select * from school_register where username='".$_POST['username']."'");

	if(is_array($aryChkName) && count($aryChkName)>0)
			{   
            $aryChkPswd=$db->getRow("select * from school_register where username='".$aryChkName['username']."' and usertype='2' ");
			
            if(is_array($aryChkPswd) && count($aryChkPswd)>0)
                    { 	
                        $storedPassword = $aryChkPswd['password'] ?? '';
                        $passwordValid = false;

                        if (strpos($storedPassword, '$2y$') === 0 || strpos($storedPassword, '$argon2') === 0) {
                            $passwordValid = password_verify($_POST['password'], $storedPassword);
                        } else {
                            $passwordValid = ($storedPassword === $_POST['password']);
							
                            if ($passwordValid) {
                                $aryChkPswd['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                                $db->query("update school_register set password='".$aryChkPswd['password']."' where id='".$aryChkPswd['id']."'");
                            }
                        }

                        if($passwordValid && $aryChkPswd['id']!='')
                        {
                            $_SESSION['userid']=$aryChkPswd['id'];
                            echo "logged in successfully";
                            redirect(SITE_URL.'home.php');	
                        }
                        else{
                            $stat['error']='INVALID_PASSWORD';
                        }
                    }
                else{
                        $stat['error']='INVALID_PASSWORD';
                    }
			}
		else{
				$stat['error']='THIS_USER_NAME_DO_NOT_EXIST';
			}
			}
		else{
			$stat['error'] = $validate->errors();
			}
		
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Skool</title>
  <meta charset="utf-8">
  <meta nam="viewport" content="width=device-width, initial-scale=1">

<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

		
<style>
@-moz-keyframes Gradient {
	0% {
		background-position: 0% 50%
	}
	50% {
		background-position: 100% 50%
	}
	100% {
		background-position: 0% 50%
	}
}

@keyframes Gradient {
	0% {
		background-position: 0% 50%
	}
	50% {
		background-position: 100% 50%
	}
	100% {
		background-position: 0% 50%
	}
}

.carousel-indicators {
display:none;}
.form-group {
    margin-bottom: 20px !important;
}

.input-group-icon, .input-search {
    width: 100%;
    table-layout: fixed;
}

.mr-xs {
margin-right:5px;
}

.input-group {
    position: relative;
    display: table;
    border-collapse: separate;
    border: none;
    border-bottom: .1px solid;
    border-color: #DDD;
}

.input-group-addon, .input-group-btn, .input-group .form-control {
    display: table-cell;
}

.input-group .form-control {
    position: relative;
    z-index: 2;
    float: left;
    width: 100%;
    margin-bottom: 0;
}


.input-group-addon {
    padding: 6px 12px;
    font-size: 14px;
    font-weight: normal;
    line-height: 1;
    color: #555;
    text-align: center;
    border-radius: 4px;
	    background: transparent;
    border: 0;
}

.btn-signin:hover {
    background-color: #04337d;
    color: #ffffff;
    border-color: #1f5a99;
}

.btn-signin {
    background-color: #fff;
    color: #1f5a99;
    border-color: #04337d;
}
.btn-lg, .btn-group-lg > .btn {
    line-height: 1.334;
}
.btn-block {
    display: block;
    width: 100%;
}

.btn-lg, .btn-group-lg > .btn {
    padding: 10px 16px;
    font-size: 18px;
    line-height: 1.3333333;
    border-radius: 6px;
}

.input-group input {
    background: none;
    font-size: 16px;
    border: none;
    border-bottom: .1px solid;
    border-color: #DDD;
    box-shadow: none;}
	
.input-group input:focus {
	    box-shadow: none;}

.ad-nav-head nav {
    background: #1f5a99;
    margin: 0;	
    border: 0;	
}

.panel-sign .panel-body {
    background: #FFF;
    border-radius: 5px 0 5px 5px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    padding: 33px 33px 15px;
}


.navbar-default .navbar-nav>li>a:hover, .navbar-default .navbar-nav>li>a:focus {
    color: #fff;
	}
	
	
	.body-sign-add{
	position: absolute;
    top: 0;
    bottom: 0;
     height: 50%;
    margin: auto;
    left: 0;
    right: 0;
    VERTICAL-ALIGN: MIDDLE;}
	
	
	.carousel-inner>.item>img, .carousel-inner>.item>a>img {
    height: 607px;
}
	
	
	

.panel-sign .panel-title-sign .title {
    background-color: #337ab7;
    border-radius: 5px 5px 0 0;
    color: #FFF;
    display: inline-block;
    font-size: 1.2rem;
    line-height: 2rem;
    padding: 13px 17px;
    vertical-align: bottom;
}
 .panel-sign .panel-title-sign .title {
    background-color: #0088cc;
	    margin: 0;
    border: 0;
}
.bg-colorflow2 {
    color: #fff !important;
    background-image: linear-gradient(-45deg, #ffffff,#1f5a99,#ffffff,#1f5a99,#ffffff,#1f5a99,#ffffff,#1f5a99,#04337d) !important;
    /* background-image: linear-gradient(-45deg, #EE7752, #E73C7E, #23A6D5, #23D5AB) !important; */
    background-size: 400% 400% !important;
    -webkit-animation: Gradient 15s ease infinite !important;
    -moz-animation: Gradient 15s ease infinite !important;
    animation: Gradient 15s ease infinite !important;
}
	
	
	.center-sign {
	width: 71%;
    border: 0;
    padding: 0;
    margin: 0 auto;}
	
	.panel.panel-sign {
	
	border:0px;}
	
</style>




</head>
<!------ Include the above in your HEAD tag ---------->

<body>
<div class="ad-nav-head">
<div class="">
    <!-- Second navbar for categories -->
    <nav class="navbar navbar-default">
      <div class="">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Brand</a>
        </div>
    
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="navbar-collapse-1">
          <ul class="nav navbar-nav navbar-right">
        
                    <li>
                        <a href="#" class="btn-default"> <i class="fa fa-home"></i> Home  </a>
                    </li>
                     <li>
                        <a href="#" class="btn-default"> <i class="fa fa-user"></i> School Portal Login  </a>
                    </li>
                    <li>
                        <a href="#" class="btn-default"> <i class="fa fa-user-plus"></i> Admission Application  </a>
                    </li>
                                          <li>
                          <a href="#" class="btn-default"> <i class="fa fa-envelope"></i>  Contact  </a>
                      </li>
                                    </ul>
          
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container -->
    </nav><!-- /.navbar -->

</div><!-- /.container-fluid -->

</div>



<div class="fade-bannner">

<div class="banner-inn">

  <div id="myCarousel" class="carousel slide" data-ride="carousel">
    <!-- Indicators -->
    <ol class="carousel-indicators">
      <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
      <li data-target="#myCarousel" data-slide-to="1"></li>
      <li data-target="#myCarousel" data-slide-to="2"></li>
    </ol>

    <!-- Wrapper for slides -->
    <div class="carousel-inner">
      <div class="item active">
        <img src="image/bnn1.jpg"  style="width:100%;">
      </div>

      <div class="item">
        <img src="image/bnn.jpg" style="width:100%;">
      </div>
    
    
    </div>

  
  </div>
  
  
  
  
  <section class="body-sign-add" style="">
  <div class="container">
  <div class="row">

		<div class="col-sm-3"> </div>
		<div class="col-sm-6">

		<div class="center-sign">
			
			<div class="panel panel-sign" style="">

				<div class="panel-title-sign text-center">
					<h4 class="title m-none bg-colorflow2" style="width:100%; font-size:18px; color:#fff;">
						<i class="fa fa-user mr-xs"></i>School Portal Login 					</h4>
				</div>

				<div class="panel-body " style="">

					<form id="login-add" method="post" action="">
												<div class="form-group mb-lg">
							<div class="input-group input-group-icon">
								<input  id="email" type="text" name="username"  placeholder="E-Mail or Reg. No." class="form-control input-lg">
							</div>
						</div>
						<div class="form-group mb-lg">
							<div class="input-group">
								<input name="password"  type="password" name="password" placeholder="Password" class="form-control input-lg">
							
							
							<span class="input-group-addon" style="">
									<a href="" class="icon icon-sm"  style="color:inherit; text-decoration:none; size:width:100%;">
										<i class="fa fa-eye" id="eye_icon"></i> 
										<span id="show_txt">show</span>
									</a>
								</span>
							
							
							
							</div>
							
							
							
							
						</div>
						
						<br>
						<div class="row">
							<div class="col-sm-12 text-center">
								<button type="submit" name="login" class=" btn-signin btn-block btn-lg "> Sign In <i class="fa fa-sign-in"></i> </button>
							</div>
						</div>
						<br>
							
					</form>
					
					
					<div class="text-left">
				<a href="#">
					<b style=""> Forgot your Password ? </b>
				</a>
			</div>
				</div>
				
			</div>
			
		</div>

		</div>
	
	<div class="col-sm-3"> </div>
		
	</div>
	</div>
	</section>
  
  
  
  
  
  
  
  
  
  
</div>


</div>




<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
</body>
</html>







































