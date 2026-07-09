<?php include('config.php'); 

$iCurrentProductDetail=$db->getRow("select * from products where pageurl='".$_GET['productname']."'");

$cut_discount=$iCurrentProductDetail['price']*$iCurrentProductDetail['discount_percent']/100;
$iCurrentProductAttributes=$db->getRow("select * from products_attributes where product_id='".$iCurrentProductDetail['id']."'"); 
 if($_POST['action']=='addtocart')
	{  
$iPostProductDetail=$db->getRow("select * from cart where pid='".$iCurrentProductDetail['id']."' and  pro_qty_id='".$_POST['pro_qty_id']."' and randomid = '".$_SESSION['randomid']."'");
	if($iPostProductDetail['id']!='')
		{
			$aryData=array(	
							'randomid'  	  			=>	$_SESSION['randomid'],
							'pid'  	  		  			=>	$iCurrentProductDetail['id'],
							'qty'  	      	  			=>	$_POST['qty'],
							'event_id'  	            =>	$_POST['event'],
 							'price'  	 		 		=>	$_POST['price'],
							'totalprice'	  			=>	$_POST['price']*$_POST['qty'],
 							'pro_qty_id'    		 	=>	$_POST['pro_qty_id'],
						);  
			$flgIn1 = $db->updateAry("cart", $aryData , "where id='".$iPostProductDetail['id']."'");
		} else {
			
		$iCurPrDetail=$db->getRow("select * from products where id = '".$iCurrentProductDetail['id']."'");	
			$aryData=array(	
							'randomid'  	  =>	$_SESSION['randomid'],
							'pid'  	  		  =>	$iCurrentProductDetail['id'],
							'seller_id'  	  =>	$iCurrentProductDetail['userid'],
							'qty'  	      	  =>	$_POST['qty'],
							'event_id'  	  =>	$_POST['event'],
 							'price'  	 	  =>	$_POST['price'],
							'totalprice'	  =>	$_POST['price']*$_POST['qty'],
 							'pro_qty_id'      =>	$_POST['pro_qty_id'],
						);  
			$flgIn1 = $db->insertAry("cart",$aryData);
		}
		redirect(SITE_URL.'cart.php');
	}
?>
<?php
$iMakeArray = array();
$iQuanityId = array();
$iSearchAtt='';
if($_GET['att1']!='')	
	{
		$iSearchAtt .= " and att_value='".$_GET['att1']."'";
	}
if($_GET['att2']!='')	
	{
		//$iSearchAtt .= " and att_value='".$_GET['att2']."'";
	}
$iPrdAttributeQty=$db->getRows("select DISTINCT qty_id from pro_att where     pid = '".$iCurrentProductDetail['id']."' $iSearchAtt");
								  foreach($iPrdAttributeQty as $iProAttQty)
									{	
									$iQuanityId[] = $iProAttQty['qty_id'];	 
$iPrdAttribute=$db->getRows("select DISTINCT att_id from pro_att where  qty_id='".$iProAttQty['qty_id']."' and pid = '".$iCurrentProductDetail['id']."'");
											  foreach($iPrdAttribute as $iProAtt)
												{   
														$iMakeArray[] = $iProAtt['att_id'];	 
												}	
									}
$iUniqueArray = implode(', ',array_unique($iMakeArray));
$iUniqueiQuanityId = implode(', ',array_unique($iQuanityId));



if($_GET['att1']!='' && $_GET['att2']!='' && $_GET['att3']!='' && $_GET['att4']!='' ) 
					{
						$iSearchAttribute=$_GET['att1'].",".$_GET['att2'].",".$_GET['att3'].",".$_GET['att4'];
 					}
if($_GET['att1']!='' && $_GET['att2']!='' && $_GET['att3']!='' ) 
					{
						$iSearchAttribute=$_GET['att1'].",".$_GET['att2'].",".$_GET['att3'];
 					}
elseif($_GET['att1']!='' && $_GET['att2']!='' ) 
					{
						$iSearchAttribute=$_GET['att1'].",".$_GET['att2'];
 					}
			else
					{
						$iSearchAttribute=$_GET['att1'];	
					}
$iTotalCurrentPrdAttribute=$db->getRow("select * from pro_qty where   pid = '".$iCurrentProductDetail['id']."' and att_array = '".$iSearchAttribute."'");


if($iTotalCurrentPrdAttribute['id']=='')
	{	
		if($_GET['click']!='') {
		$iGetThisVlaueNew=$db->getVal("select qty_id from  pro_att where   pid = '".$iCurrentProductDetail['id']."' and att_value = '".$_GET['click']."'");
		} else {
		$iGetThisVlaueNew=$db->getVal("select qty_id from  pro_att where   pid = '".$iCurrentProductDetail['id']."' and att_value = '".$_GET['att1']."'");
		}
		$iCurrentValueNow=$db->getRows("select * from  pro_att where   pid = '".$iCurrentProductDetail['id']."' and qty_id = '".$iGetThisVlaueNew."'");
		foreach($iCurrentValueNow as $iCurValN) {
			
 			 $email_list.= $iCurValN['att_value'].",";
			 
		}
		$iSearchAttribute = rtrim($email_list,',');
$iTotalCurrentPrdAttribute=$db->getRow("select * from pro_qty where   pid = '".$iCurrentProductDetail['id']."' and att_array = '".$iSearchAttribute."'");
echo "select * from pro_qty where   pid = '".$iCurrentProductDetail['id']."' and att_array = '".$iSearchAttribute."'";
	$iToCurPtdet = explode(',', $iTotalCurrentPrdAttribute['att_array']);
		$ikohoi = 0;
	 	$iConcatNe  = '';
		foreach ($iToCurPtdet as $val)
			{			$ikohoi = $ikohoi+1;
						 $iConcatNe  .= '&att'.$ikohoi.'='.$val.'&att'.$ikohoi.'='.$val;

			}
		redirect(SITE_URL.'product-detail.php?productname='.$_GET['productname'].'&click='.$_GET['click'].$iConcatNe);
	}
if($iTotalCurrentPrdAttribute['id']=='')
	{
		$iTotalCurrentPrdAttribute=$db->getRow("select * from pro_qty where   pid = '".$iCurrentProductDetail['id']."'");
		$myArrayImage = explode(',', $iTotalCurrentPrdAttribute['proimages']);
	}
else{
		$myArrayImage = explode(',', $iTotalCurrentPrdAttribute['proimages']);
	}

?>

<!DOCTYPE html>
<html lang="en">
<head><?php include('inc.meta.php');?>
   <style>
   .home-event {
    padding: 46px 0 95px 0;
    position: relative;
}

.home-event .event-box {
    padding: 42px 0 0 0;
    display: block;
    text-align: center;
}

.home-event .event-box .img {
    width: 100%;
    display: block;
    margin-bottom: 22px;
    text-align: center;
    position: relative;
    background: #000;
}

.home-event .event-box a {
    display: block;
    font-size: 14px;
    color: #f15b25;
    line-height: 24px;
}

.event-box .img a img {
    height: 200px;
    width: 100%;
    box-shadow: 3px 0px 7px 1px grey;
}
   </style>
</head>
    
<body>
    <div class="page"><?php include('inc.header.php');?>
		
		        <div class="raj-event">
		<section class="content">
        	<div class="container">
            	<div class="blog-page">
                	<div class="row">	
                   <div class=" preview col-md-6">
				   
						<div class="preview-pic tab-content">
							<?php
							$i=0;
							foreach($myArrayImage as $my_Array){ 	
							if(trim($my_Array)!='')
							{	
							$i=$i+1; ?>
							<div class="tab-pane <?php if($i=='1') { echo 'active'; } ?>" id="pic-<?php echo $i; ?>">
							<img src="<?php echo SITE_URL; ?>uploads/<?php  echo trim($my_Array); ?>">
							</div>
						  
						  <?php } 	} ?>
						</div>
						
						<ul class="preview-thumbnail nav nav-tabs">
							<?php
							$i=0;
							foreach($myArrayImage as $my_Array){ 	
							if(trim($my_Array)!='') 
							{
							$i=$i+1; ?>
							<li class="<?php if($i=='1') { echo 'active'; } ?>">
							<a data-target="#pic-<?php echo $i; ?>" data-toggle="tab">
							<img src="<?php echo SITE_URL; ?>uploads/<?php    echo trim($my_Array); ?>"></a>
							</li> 
							<?php } 	} ?>
						</ul> 
                    </div>
					   
	<div class="col-md-6 single-shop">
              
				<div class="rating-stars">
						<i class="fa fa-star" style="color:silver;"></i>
						<i class="fa fa-star" style="color:silver;"></i>
						<i class="fa fa-star" style="color:silver;"></i>
						<i class="fa fa-star" style="color:silver;"></i>
						<i class="fa fa-star" style="color:silver;"></i>
						<span class="text-muted align-middle">&nbsp;&nbsp;0 | 0 Customer Reviews</span>
				</div>
                <h2 class="padding-top-1x text-normal with-side"><?php echo $iCurrentProductDetail['products']; ?></h2>
                <span class="h2 d-block with-side"><!--<del class="text-muted text-normal">$899.00</del>-->&nbsp; 
					<?php showcurrency();  echo $iTotalCurrentPrdAttribute['price']; ?></span>
                <p><?php echo $iCurrentProductDetail['description']; ?></p>
                <div class="row margin-top-1x">
				<div class="col-md-12">
					<form action="" method="get" id="submitnow">
                    <input type="hidden" value="<?php echo $_GET['productname'] ; ?>" name="productname">
                    <table>
					<?php
					$i=0;
					$iAddAttub='';
					$iPrdAttribute=$db->getRows("select DISTINCT att_id from pro_att where  att_id  IN ($iUniqueArray) and att_value  !=0 and pid = '".$iCurrentProductDetail['id']."' ");
					foreach($iPrdAttribute as $iProAtt) {    $i=$i+1; 
					?>
                    <tbody>
					<tr>
                    <td style="width:50px; font-size: 15px;">
					<?php echo  $db->getVal("select attributes from attributes where  id='".$iProAtt['att_id']."'");  ?></td>
					<?php  
					$iPrdAttributeQtyA=$db->getRows("select DISTINCT att_value from pro_att where  att_id  ='".$iProAtt['att_id']."' and pid = '".$iCurrentProductDetail['id']."'");
					foreach($iPrdAttributeQtyA as $iProAttQtyA) { 	
					$iAlreadyExitCheck=$db->getVal("select id from pro_att where  att_value  ='".$iProAttQtyA['att_value']."' and qty_id IN ($iUniqueiQuanityId) and pid = '".$iCurrentProductDetail['id']."'"); ?>
					<?php if($iProAttQtyA['att_value']!='0') { ?>                
               
                    <td style="border:black 1px solid; width:100px; height:30px;  border: black 2px dotted; padding: 10px; background-color:<?php if($iAlreadyExitCheck!='') { echo '#94888842'; } ?>" class="<?php if($iProAttQtyA['att_value']==$_GET['att1'] ||  $iProAttQtyA['att_value']==$_GET['att2']||  $iProAttQtyA['att_value']==$_GET['att3']) {  echo 'selectednew'; } ?>">
                    <a style="cursor: pointer;" <?php if($iAlreadyExitCheck!='') { echo 'disable'; } ?>  onClick="makeitform('att<?php echo $i; ?>', '<?php echo $iProAttQtyA['att_value']; ?>' )">
                    <?php  echo  $db->getVal("select attributesname_values from attribute_values where  id='".$iProAttQtyA['att_value']."'");  ?></a>
                    </td>
					
                       <?php } ?>
                    
                    
                    <?php }	 ?>                 
                    
                    </tr>
                    <input type="hidden" name="att<?php echo $i; ?>" value="<?php echo $_GET['att'.$i]; ?>"  id="att<?php echo $i; ?>" >
                    <?php } ?>
                    <input type="hidden" name="click" value="<?php echo $_GET['click']; ?>"  id="click" >
				 </tbody>
					</table>
                    </form>
              <form action="" method="post" id="addquantity" class="has-validation-callback" style="padding-top: 15px;padding-bottom: 15px;">
                <?php if($iTotalCurrentPrdAttribute['qty']!='0') { ?>
			   <table>
				<tbody>
				
				<tr>
				<td>
                    <input type="hidden" value="<?php echo  $iTotalCurrentPrdAttribute['pid']; ?>" name="pid">
                    <input type="number" required  name="qty" id="qty" class="form-control" style="width:100px" placeholder="Quantity" min="1">
                    <input type="hidden" value="<?php echo  $iTotalCurrentPrdAttribute['qty']; ?>"  name="current_qty">
                    <input type="hidden" value="<?php echo  $iTotalCurrentPrdAttribute['price']; ?>"  name="price"id="price">
                    <input type="hidden" value="<?php echo  $iTotalCurrentPrdAttribute['id']; ?>"  name="pro_qty_id">
                  			</td>
                    <td>
                    <select class="form-control" name="event" id="event" onchange="check('1');" required>
					<option value="">Select Event</option>
				<?php	  $event_detail=$db->getRows("select * from invited_users where  userid='".$_SESSION['userid']."'");
				         
                           foreach($event_detail as $eventList)
						   {
						 $getresult=$db->getRow("select * from admin_event where id='".$eventList['event_id']."'");
							   
                    ?>
					<option value="<?php echo $getresult['id']; ?>"><?php echo $getresult['event_name']; ?></option>
					
						   <?php } ?>
					 <option value="-1">For Personal Use</option>
					</select>
					  <input type="hidden" value="addtocart"  name="action">	
                    </td>
					<td>
					<?php if($iCurrentProductDetail['where_to_buy']==2) { ?>
					<a href="#" name="event_cat"  id="event_cat"  style="width:200px" class="b1 btn btn-primary" onClick="shomge()">Buy Now</a>
		           <a href="<?php echo $iCurrentProductDetail['ref_url']?>"  onClick="buyNow()" name="select_cat"  id="select_cat"  style="width:200px; display: none;" class="b1 btn btn-primary">Buy Now </a> 

				    <span id="soresult"></span>
					
					<?php } else { ?>
                    <input type="button" onClick="addtocartbut()" name="addtocart"  style="width:200px" class="b1 btn btn-primary" value="Add To Cart">
					<?php } ?>					
                    </td>
                    </tr>
					
                    <tr><td colspan="2" id="shower"></td></tr>
				
					
                    </tbody></table>
					<?php  } else { ?>
                     <input type="button"  style="width:200px" class="b1 btn btn-primary" value="Out of stock">
                    <?php } ?>
                      </form>
					  <script>
					  function shomge()
					  {
						  document.getElementById("soresult").innerHTML='<p style="color :red">Please select Event</p>';
					  }
					  
							function check(getid) {
								
								if(getid==1)
								{
									
									document.getElementById('event_cat').style.display='none';
									document.getElementById('select_cat').style.display='block';
								}
								else
								{
									document.getElementById('event_cat').style.display='block';
									document.getElementById('select_cat').style.display='block';
									
									
								}
							}
					  
					  </script>
					  
					<script>
                    function makeitform(getid,getvalue)
                        {		document.getElementById("click").value = getvalue;
                                document.getElementById(getid).value = getvalue;
                                document.getElementById("submitnow").submit();
                        }
                    
                    function addtocartbut()
                        {		var quantity 	= '<?php echo  $iTotalCurrentPrdAttribute['qty']; ?>';
                                var qty 		= document.getElementById("qty").value; 
								var event 		= document.getElementById("event").value; 
							
                                if(event=='')
								{
									document.getElementById("shower").innerHTML='<p style="color :red">Please select event</p>';
								}
								else
								{
							   if(qty!='')	{ 
                                if(parseInt(quantity)>=parseInt(qty)) 
                                        {
                                                document.getElementById("addquantity").submit();
                                        }
                                    else {
                                            document.getElementById("shower").innerHTML='<p style="color :red">Only '+quantity+' product is available.</p>';
                                    }
                                }
								else
									{
                                    document.getElementById("shower").innerHTML='<p style="color :red">Please enter quantity</p>';
                                    }
								}
                        }
                    </script>
					<table class="table attrib" id="maintable">
                     <tr><td colspan="2"><h3>Product Specification</h3></td></tr>
                      <tr><th>Specification Heading</th><th>Specification Value</th></tr>
                    <?php 
				   $i=0;
				  $iMainAttribute=$db->getRows("select * from pro_specification where  pid='".$iCurrentProductDetail['id']."'");
                                   
				   			foreach($iMainAttribute as $iMainAtt)
                                                  {  ?>
                           <tr><td><?php echo $iMainAtt['specification_heading']; ?> </td>
                           <td><?php echo $iMainAtt['specification_value']; ?></td>
                          
                           </tr>
                         
                           <?php  } 	 ?>
                      </table>
		
                <div class="row">
				<div class="col-md-12">
				<span class="text-medium">SKU:</span> #<?php echo $iCurrentProductDetail['skucode']; ?></div>
				</div>
				<div class="row">
				<div class="col-md-12">
                <div class="padding-bottom-1x mb-2">
                    <span class="text-medium">Categories:&nbsp;</span>
                    <a class="navi-link" href="#">Apple,</a>
                    <a class="navi-link" href="#"> Smartphone,</a>
                    <a class="navi-link" href="#"> Mobile</a>
                </div>
				</div>
				</div>
         </div>
   </div>  
   
					   
					   
			<script>
function makeitform(getid, getvalue)
	{		document.getElementById("click").value = getvalue;
			document.getElementById(getid).value = getvalue;
			document.getElementById("submitnow").submit();
	}
</script>
<script>
function addtocart()
{	

	var productid = document.getElementById("productid").value;
	var quantity = document.getElementById("quantity").value;
	var event = document.getElementById("event").value;
	var price = document.getElementById("price").value;

	$.ajax({
			type: 'POST',
			url: 'ajax.php',
			data:{
					action		        :	  "addtocart",					
					productid	        :	   productid,
					quantity	    	:	   quantity,
					event	          	:	   event,
					price	    		:	   price,
					},
			success: function(data) { 
			
				if(data==1)
				{	
					window.location.href="cart.php";
				}
				else
				{
					document.getElementById('showloginmsg').innerHTML =data;
				}
			}
		});
}
</script>
<script>
function buyNow()
{
	var event = document.getElementById("event").value;
	var price = document.getElementById("price").value;
	var qty = document.getElementById("qty").value;
	
	$.post("ajax.php",
				   {		 
					  action : 'buyNow',
					 pid     : '<?php echo $iCurrentProductDetail['id']; ?>',
					 event   : event,
					 price   : price,
					 qty     : qty,
				 },
		function(data){	
		
         document.getElementById('soresult').innerHTML=data;
				});
	
}
</script>
   
  </div>
  </div>
 </div>
</div>
  </section>
</div>
        <?php include('inc.footer.php'); ?>
    </div>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <?php include('inc.js.php'); ?>
</body>
</html>