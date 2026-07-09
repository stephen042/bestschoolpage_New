<?php include('../config.php');

include('inc.session-create.php');
 $GetCampaigns=$db->getRow("select * from  campaigns where id='".$_GET['id']."'");
?>
<!DOCTYPE html>
<html>
<head>
<?php include('inc.meta.php'); ?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
 <link href="http://jdewit.github.io/bootstrap-timepicker/assets/prettify/prettify.css" type="text/css" rel="stylesheet" />
    <link rel="stylesheet/less" type="text/css" href="http://jdewit.github.io/bootstrap-timepicker/css/timepicker.less" />

<style>
.li-nav a {
    line-height: 36px !important;
    color: #bbb;
    text-decoration: none;
    padding: 28px 0 29px 41px !IMPORTANT;
    position: relative;
    display: block;
    float: left;
    border-radius: 0;
    outline-style: none;
    background: #ddd;
    text-align: left !important;
}
.li-nav a:before {
    content: " ";
    display: block;
    width: 0;
    height: 0;
    border-top: 50px solid transparent;
    border-bottom: 50px solid transparent;
    border-left: 30px solid white;
    position: absolute;
    top: 50%;
    margin-top: -50px;
    margin-left: 1px;
    left: 100%;
    z-index: 1;
}
.li-nav {
  
    width: AUTO!important;
}
.thumbnail {
    
    BORDER: NONE;
}
.li-nav a:after {
    content: " ";
    display: block;
    width: 0;
    height: 0;
    border-top: 50px solid transparent;
    border-bottom: 50px solid transparent;
    border-left: 30px solid #ddd;
    position: absolute;
    top: 50%;
    margin-top: -50px;
    left: 100%;
    z-index: 2;
}
.li-nav.active a:after {
    border-left:30px solid #5fbeaa!important;
}
.li-nav a:focus, .li-nav a:hover {
    text-decoration: none;
    background-color: #5fbeaa!important;
    /* background: none; */
}
 .li-nav a:focus:after, .li-nav a:hover:after {
    border-left:30px solid #5fbeaa!important
}
.btn-info{margin-top: 20px;
 background: #5fbeaa !important;}

.btn-inffoo {
    background: #5fbeaa;
    margin-top: 20px;
    color: white!important;
}

.getSuccess
{
	color: green;
	
}
.reg-error
	
{
	color: red;
}

.modal-open .modal {
    overflow-x: hidden;
    overflow-y: auto;
}
.fade.show {
    opacity: 1;
}
.modal {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1050;
    display: none;
    overflow: hidden;
    outline: 0;
}
.fade {
    opacity: 0;
    transition: opacity 0.15s linear;
}
*, *::before, *::after {
    box-sizing: border-box;
}

</style>
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
              <li> <a href="<?php echo $iClassName; ?>">Home</a> </li>
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
               
              </div>
            </div>
			
           

            <div class="card-box">
             <div class="container12">
        <div class="row form-group">
            <div class="col-xs-12">
                <ul class="nav nav-pills nav-justified thumbnail setup-panel" id="myNav">
                    <li id="navStep1" class="li-nav disabled" step="#step-1">
                        <a href="edit_rebate_basics.php?id=<?php echo $_GET['id'];?>">
                            <h4 class="list-group-item-heading">Basics &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</h4>
                        </a>
                    </li>
                    <li id="navStep2" class="li-nav disabled" step="#step-2">
                       <a href="edit_rebate_pictures.php?id=<?php echo $_GET['id'];?>">
                            <h4 class="list-group-item-heading">Pictures &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</h4>
                        </a>
                    </li>
                    <li id="navStep3" class="li-nav active" step="#step-3">
                        <a>
                            <h4 class="list-group-item-heading">Settings &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</h4>
                        </a>
                    </li>
                    <li id="navStep4" class="li-nav disabled" step="#step-4">
                        <a>
                            <h4 class="list-group-item-heading">Preview &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</h4>
                        </a>
                    </li>
					 <li id="navStep5" class="li-nav disabled" step="#step-5">
                        <a>
                            <h4 class="list-group-item-heading">Payment &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</h4>
                        </a>
                    </li>
					 <li id="navStep6" class="li-nav disabled" step="#step-6">
                        <a>
                            <h4 class="list-group-item-heading">Summary &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</h4>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
<span id="showcampaigns"></span>
 <form class="container12" role="form" action="" id="third_campaignsform" method="post" enctype="multipart/form-data">
        <div class="row setup-content" id="step-3">
            <div class="col-xs-12">
                <div class="col-md-12 well text-center">
				<span id="showcampaigns_settings"></span>
                <div>
                  <section>

                    <div class="form-group clearfix">
                      <label class="col-lg-1 control-label " for="userName">Price <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="The full price of your product without any discounts" aria-describedby="tooltip783772"></i> </label>
                      <div class="col-lg-5">
                        <input type="number" class="form-control required" id="price" name="price"  step="0.01" value="<?php echo $GetCampaigns['price'];?>">
                      </div>
					   <label class="col-lg-1 control-label " for="userName">Price After Rebate <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="The price you will be offering your product for on this website"></i></label>
					   <span id="showmessage"></span>
                      <div class="col-lg-5">
                        <input type="number" class="form-control required" id="price_after_rebate"  step="0.01" onkeyup="GetValidate();" name="price_after_rebate" value="<?php echo $GetCampaigns['price_after_rebate'];?>">
                   <a onClick="showtest('1');"  id="testid"> <small>Set to % Rebate <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="This tool can help you calculate the discounted price."></i></small></a>
					  
					   <div class="ddddd" style="display:<?php if($GetCampaigns['price_after_rebate']!='') { echo 'block'; } else { echo 'none'; } ?>;" id="showt_1" onChange="getPercentage();">
					   <select    name="rebate_percentage" id="rebate_percentage" >
                               
				                  <option value="0"> Rebate %</option>
                              <?php for($i=1; $i<100+1; $i++){
								  
								  
					             ?>                                                  
                                  <option value="<?php echo $i;?>" <?php if($GetCampaigns['rebate_percentage']== $i) { echo "selected";  } ?>><?php echo $i;?></option>
                                  <?php } ?>
                                </select>
					  </div>
					  
					  </div>
					 
                    </div>
					
										
					<div class="form-group clearfix">
                      <label class="col-lg-1 control-label " for="userName">Start Date <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="When will this rebate become available?" aria-describedby="tooltip919246"></i></label>
                      <div class="col-lg-5">
                        <input type="text" class="form-control required " placeholder="Start Date" id="start_date" name="start_date" value="<?php echo $GetCampaigns['start_date'];?>">
                      </div>
					   <label class="col-lg-1 control-label " for="userName">Start Time</label>
                      <div class="col-lg-5">
                        <input type="text" class="form-control timepicker" id="start_time" name="start_time" value="<?php echo $GetCampaigns['start_time'];?>">
                      </div>
                    </div>
				
					
					
					<div class="form-group clearfix">
                      <label class="col-lg-1 control-label " for="userName">Daily Keys <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Daily limit the amount of people that can use this rebate." aria-describedby="tooltip343694"></i></label>
                      <div class="col-lg-3">
                        <input type="number" class="form-control required"  value="<?php if($GetCampaigns['daily_keys']!='') { echo $GetCampaigns['daily_keys']; } else { echo '10'; } ?>" id="daily_keys" name="daily_keys" >
                      </div>
					  <label class="col-lg-1 control-label  wi-edi" for="userName">Running strategy <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Run your rebate campaign indefinitely or limit the total number of people that can use this rebate. You can pause it at any moment." aria-describedby="tooltip430097"></i></label>
                      <div class="col-lg-3   wi-reb">
                       Run indefinitely <input type="checkbox"  onClick="showid('1');"  id="run_indefinitely" <?php if($GetCampaigns['run_indefinitely']== 1) { echo "checked";  } ?> name="run_indefinitely" value="1">
                      </div>
					  <div class="dddd" style="display:<?php if($GetCampaigns['run_indefinitely']=='1') { echo 'none'; } else { echo 'block'; } ?>;" id="show_1"> 
					   <label class="col-lg-1 control-label " for="userName">Total Keys <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Total amount of people that can use this rebate."></i></label>
                      <div class="col-lg-3">
                        <input type="number" class="form-control required" id="total_keys"  value="100" name="total_keys" value="<?php echo $GetCampaigns['total_keys'];?>">
                      </div>
					  </div>
                    </div>
					
					
					
					<div class="form-group clearfix">
                      <label class="col-lg-1 control-label " for="userName">Product Url <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="The URL of your Product Listing"></i></label>
                      <div class="col-lg-5">
                        <input type="text" class="form-control required" id="product_url" name="product_url" value="<?php echo $GetCampaigns['product_url'];?>">
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-1 control-label  rea-ak" for="userName">Free Shipping <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Do you offer free shipping on your product?" aria-describedby="tooltip402124"></i></label>
                      <div class="col-lg-5">
                        <input type="checkbox"  id="free_shipping" name="free_shipping" value="1" <?php if($GetCampaigns['free_shipping']== 1) { echo "checked";  } ?>>
                      </div>
                    </div>
                   
                    <button type="button" onclick="get_settings('<?php echo $_GET['id'];?>');" class="btn btn-default">Continue</button>
                  
                </div>
            
                   

                </div>
            </div>
        </div>
    </form>
    
	
   
	
        </div>
      </div>
    </div>
  </div>
  <?php include('inc.footer.php'); ?>
</div>
</div>
<?php include('inc.js.php'); ?>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script> 
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> 
<script>
  $("#start_date").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
			numberOfMonths: 1,
             maxDate: "+1y",
             minDate: +0,
        });


	  </script> 

<script>
					 function GetValidate() {
					//alert(GetValidate);
						 
						var price =document.getElementById('price').value;
					
						var price_after_rebate =document.getElementById('price_after_rebate').value;
						
						if(parseInt(price)<parseInt(price_after_rebate))
						{
							
                      document.getElementById('showmessage').innerHTML ='<p class="reg-error" id="myPP"  style="display:block">The discounted price should be lower than the full price.</p>';
			              }
						else
						{
							document.getElementById("myPP").style.display='none';
							 document.getElementById("price_after_rebate").value = price_after_rebate;
						}
					
					 }
</script>
	<script>
					 function getPercentage() {
					//	alert(getPercentage);
						 
						var price =document.getElementById('price').value;
						
						var rebate_percentage =document.getElementById('rebate_percentage').value;
					
						
					if(price!='')
                         {
                  var discount =   parseInt(price)*rebate_percentage/100;
				 var finaldiscount =  parseInt(price)- parseInt(discount);

	              document.getElementById("price_after_rebate").value = finaldiscount;
                        }
						 else
						 {
						  document.getElementById("price_after_rebate").value = 0.0;	 
							 
						 }
					
					   }
</script>
<script>
					 function showid(getid) {
						 
						
						 $(".dddd").css('display','none');
		              var lfckv = document.getElementById("run_indefinitely").checked;
					
	
	                 if(lfckv==true)
	                 {
						 
						 document.getElementById("show_"+getid).style.display='none';
						 
					 }
					 else
	                 {
						  document.getElementById("show_"+getid).style.display='block';
					 }
					 }
					 </script>
						 
						 <script>
					 function showtest(getid) {
						 
						
						 $(".ddddd").css('display','none');
		              var lfckv = document.getElementById("testid").value;
					
	
	                 if(lfckv==true)
	                 {
						 
						 document.getElementById("showt_"+getid).style.display='none';
						 
					 }
					 else
	                 {
						  document.getElementById("showt_"+getid).style.display='block';
					 }
					 }
					 </script>




<script>
function get_settings(primarykey)
 	{

var price =document.getElementById('price').value;
var price_after_rebate =document.getElementById('price_after_rebate').value;
var rebate_percentage =document.getElementById('rebate_percentage').value;
var start_date =document.getElementById('start_date').value;
var start_time =document.getElementById('start_time').value;
var daily_keys =document.getElementById('daily_keys').value;
var total_keys =document.getElementById('total_keys').value;
var product_url =document.getElementById('product_url').value;

if($('input[name="run_indefinitely"]:checked').length != 0)
		  {
		var run_indefinitely  = document.querySelector('input[name="run_indefinitely"]:checked').value; 
		
	      }
		  else
		  {
			 var run_indefinitely =0; 
		  }


if($('input[name="free_shipping"]:checked').length != 0)
		  {
		var free_shipping  = document.querySelector('input[name="free_shipping"]:checked').value; 
		
	      }
		  else
		  {
			 var free_shipping =0; 
		  }
if(price=='')
			{
			
			$("#showcampaigns_settings").html('<p class="reg-error" >Price can not be left blank.</p>');
			
			}
else if(price_after_rebate=='')
			{
			
			$("#showcampaigns_settings").html('<p class="reg-error" >Rebate after Rebate  can not be left blank.</p>');
			
			}
			
			
else if(rebate_percentage=='')
			{
			
			$("#showcampaigns_settings").html('<p class="reg-error" >Rebate Percentage  can not be left blank.</p>');
			
			}
else if(start_date=='')
			{
			
			$("#showcampaigns_settings").html('<p class="reg-error" >Start Date can not be left blank.</p>');
			
			}
else if(start_time=='')
			{
			
			$("#showcampaigns_settings").html('<p class="reg-error" >Start Time not be left blank.</p>');
			
			}
else if(daily_keys=='')
			{
			
			$("#showcampaigns_settings").html('<p class="reg-error" >Daily keys can not be left blank.</p>');
			
			}

else if(product_url=='')
			{
			
			$("#showcampaigns_settings").html('<p class="reg-error" >Product Url can not be left blank.</p>');
			
			}
			
			
			else
			{
			
			$.post("ajax.php",
				{		
						    action		        	:   "campaigns_settings",
							primarykey		    	:	primarykey,
							price		    		:	price,
							price_after_rebate		:	price_after_rebate,
							rebate_percentage		:	rebate_percentage,
							free_shipping		    :	free_shipping,
							run_indefinitely	    :	run_indefinitely,
							start_date		    	:	start_date,
							start_time		    	:	start_time,				
							daily_keys		    	:	daily_keys,				
							total_keys		    	:	total_keys,				
							product_url		    	:	product_url,				
				},
		function(data){
					
				if(data==1)
				{	
					//alert(data);
					document.getElementById('showcampaigns_settings').innerHTML ='<p class="getSuccess"> Your rebate settings have been saved..</p>';
					
					window.location.href ='rebate_preview.php?id='+primarykey.trim(); 
					
					
				}
				else if(data==2)
				{	
					//alert(data);
					document.getElementById('showcampaigns_settings').innerHTML ='<p class="getSuccess" style="color:red"> You can not edit because you have alreday pay for this rebate..</p>';
					
					window.location.href ='rebate_preview.php?id='+primarykey.trim(); 
					
					
				}
				else
				{
					//alert(data);
					document.getElementById('showcampaigns_settings').innerHTML =data;
				}

				});
			}
	}
</script>








<script>
 var currentStep = 1;

$(document).ready(function () {

    $('.li-nav').click(function () {

        var $targetStep = $($(this).attr('step'));
        currentStep = parseInt($(this).attr('id').substr(7));

        if (!$(this).hasClass('disabled')) {
            $('.li-nav.active').removeClass('active');
            $(this).addClass('active');
            $('.setup-content').hide();
            $targetStep.show();
        }
    });

    $('#navStep1').click();

});

        

function step1Next() {
    //You can make only one function for next, and inside you can check the current step
    if (true) {//Insert here your validation of the first step
        currentStep += 1;
        $('#navStep' + currentStep).removeClass('disabled');
        $('#navStep' + currentStep).click();
    }
}

function prevStep() {
    //Notice that the btn prev not exist in the first step
    currentStep -= 1;
    $('#navStep' + currentStep).click();
}

function step2Next() {
    if (true) {
        $('#navStep3').removeClass('disabled');
        $('#navStep3').click();
    }
}

function step3Next() {
    if (true) {
        $('#navStep4').removeClass('disabled');
        $('#navStep4').click();
    }
}
function step4Next() {
    if (true) {
        $('#navStep5').removeClass('disabled');
        $('#navStep5').click();
    }
}
</script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/2.5.1/less.min.js"></script>
    <script type="text/javascript" src="http://jdewit.github.io/bootstrap-timepicker/js/bootstrap-timepicker.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#start_time').timepicker();
        });
    </script>
</body>
</html>
