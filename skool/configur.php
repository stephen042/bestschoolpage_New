<?php include('../config.php'); 
//include('inc.session-create.php'); 
$PageTitle="Form";
$FileName = 'form.php';
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
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>

<style>

.abhi .nav-tabs { 
    border-bottom: 2px solid #DDD; 
}
.abhi .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover { 
    border-width: 0;	
}
.abhi .nav.nav-tabs>li>a:hover, .nav.tabs-vertical>li>a:hover {
    color: #1B3058!important;
}
.abhi .nav-tabs > li > a { 
    border: none; 
	color: #1B3058!important;
}

.abhi .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover, .tabs-vertical>li.active>a, .tabs-vertical>li.active>a:focus, .tabs-vertical>li.active>a:hover {
    color:#1B3058!important;
}

.abhi .nav>li>a i {
    font-size: 16px;
    padding-right: 5px;
}

.abhi .nav-tabs > li > a::after { 
    content: ""; 
	background: #1B3058; 
	height: 2px; 
	position: absolute; 
	width: 100%; 
	left: 0px; 
	bottom: -1px; 
	transition: all 250ms ease 0s; 
	transform: scale(0);
}
.abhi .nav-tabs > li.active > a::after, .nav-tabs > a::after { 
   transform: scale(1);
}
.abhi .tab-nav > li > a::after { 
   background: ##5a4080 none repeat scroll 0% 0%; 
   color: #fff; 
}
.abhi .tab-pane { 
   padding: 25px 0; 
}
.abhi .tab-content{
	padding:20px;
}
.abhi .nav-tabs > li  {
	width:25%; 
	text-align:center;
}
.abhi .card {
	background: #FFF none repeat scroll 0% 0%;
	box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.3); 
	margin-bottom: 30px; 
}
.abhi body{ 
    background: #EDECEC; 
	padding:50px;
}

.abhi .ass .select-hide {
  display: none;
}

.abhi .ass .custom-select select {
  display: none; 
}

.abhi .ass .select-selected {
    border-bottom: 1px solid #9e9e9e;
}

.abhi .ass .ytr {
    margin-top: 22px;
}

.abhi .fdg {
    border-bottom: 1px solid #9e9e9e2b;
}

.abhi .shg p {
    color: #1B3058;
}

.abhi .ass .bgb i {
    color: #1B3058;
	font-size: 19px;
}

.abhi .ass .col-md-4 i {
    background: #F44336;
    padding: 8px;
    border-radius: 50%;
    color: #fff;
    font-size: 14px;
}

.abhi .ass .shg {
    padding-top: 29px;
}

.abhi .ass .select-items {
    border: 1px solid #ddd;
    padding: 9px;
    position: relative;
    bottom: 20px;
    background: #fff;
}

.abhi .ass .select-items div {
    padding-bottom: 7px;
}

.abhi .ab-1 {
    text-align: center;
}

.abhi .icon i {
    color: #0b4587;
    background: #fff;
    font-size: 32px;
    border-radius: 50%;
    position: absolute;
    bottom: -25px;
    left: 0;
    right: 0;
    width: 100%;
    margin: 0 auto;
    padding: 15px 10px 15px 10px;
}

.abhi .icon input {
    position: absolute;
    left: 0;
    opacity: 0;
    width: 100%;
    right: 0;
    top: -18px;
}

.abhi .abhish .input-field {
    padding-bottom: 0;
}

.abhi .abh {
    margin-top: 35px;
}

.abhi .input-field input {
    background-color: transparent;
    border: none;
    border-bottom: 1px solid #9e9e9e;
    border-radius: 0;
    outline: none;
    width: 100%;
    margin: 0 0 15px 0;
    padding: 0;
    box-shadow: none;
    box-sizing: content-box;
    transition: all .3s;
}

.abhi .input-field label {
	color: #9e9e9e;
}

.abhi .icon {
    position: relative;
    left: 0;
    bottom: 0;
    width: 7%;
    margin: 0 auto;
    right: 0;
    height: 0;
    top: 0;
}

.abhi .ab-2 {
    background: #0b4587;
    color: #fff;
    width: 23%;
    padding: 28px;
    margin: 0 auto;
}

.abhi .imgage {
    padding-bottom: 13px;
}

.abhi .abb {
    text-align: center;
}

.abhi .ab-3 {
    margin-top: 30px;
}

.abhi .plp {
    margin-bottom: 80px;
}

.abhi .ab-3 .col-md-1 i {
    font-size: 17px;
    color: #000000d6;
}

.abhi .ab-3 .col-md-4 i {
    background: #F44336;
    padding: 8px;
    border-radius: 50%;
    color: #fff;
    font-size: 14px;
}


.abhi .ab-3 input {
    color: rgba(0,0,0,0.26);
    border-bottom: 1px dotted rgba(0,0,0,0.26);
}

.abhi button {
    cursor: pointer;
    float: right;
    background: #1B3058;
    color: #fff;
}

.abhi button:hover {
   
    background: #1B3058;
    color: #fff;
}

.abhi button i {
    padding-right: 45px;
    font-size: 13px;
}

.abhi .input-field {
    padding-bottom: 20px;
}

.abhi .assde {
    margin-top: 50px;
}

.abhi .ade {
    margin-top: 40px;
}

.abhi .bgb {
    text-align: center;
    padding-top: 3px;
}

.abhi .bgb i {
    color: #1B3058;
    font-size: 19px;
}

@media all and (max-width:724px){
.abhi .nav-tabs > li > a > span {
	display:none;
}	
.abhi .nav-tabs > li > a {
	padding: 5px 5px;
}

.nav.nav-tabs+.tab-content {
   
    padding: 0!important;
}

.abhi .tab-content {
    padding: 0!important;
}

.abhi .ab-2 {
    width: 92%!important;
    margin-left: 10px;
}

.abhi .input-field label {
    padding-left: 5px;
}

.abhi .input-field input {
    margin: 0;
	padding-left: 5px;
}

.abhi .icon {
	width: 29%!important;
}
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
            <h4 class="page-title licat">CONFIGURATIONS</h4>
            <ol class="breadcrumb">
              <li class="dippi"> <a href="<?php echo $iClassName; ?>">Create session, sections, classes and school address</a> </li>
              
            </ol>
          </div>
        </div>
        <!-- Basic Form Wizard -->
  
<div class="abhi">  
<div class="container">
  <div class="row">
    <div class="col-md-12"> 
      <!-- Nav tabs -->
      <div class="card">
        <ul class="nav nav-tabs" role="tablist">
          <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> <span>SCHOOL INFO</span></a></li>
          <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-line-chart" aria-hidden="true"></i>  <span>SESSION</span></a></li>
          <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab"><i class="fa fa-users" aria-hidden="true"></i> <span>SECTIONS</span></a></li>
          <li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab"><i class="fa fa-book" aria-hidden="true"></i>  <span>CLASS</span></a></li>
        </ul>
        
        <!-- Tab panes -->
        <div class="tab-content">
          <div role="tabpanel" class="tab-pane active" id="home">
		  <div class="ab-1">
		  <div class="imgage">
		  <img src="assets/image/download.png" style="width: 200px; height: 200px;"></div>
		  <div class="icon"><i class="fa fa-cloud-upload" aria-hidden="true"></i><input type="file"></div>
		  <div class="ab-2">
		  <span class="title">Drag image to upload</span><br>
		  <span class="title">School Logo</span>
		  </div>
		  </div>
		  <div class="abh">
		  <div class="input-field">
		  <label>School Name</label></br>
		  <input type="text">
		  </div>
		  <div class="input-field">
		  <label>About the School</label></br>
		  <input type="text">
		  </div>
		  <div class="input-field">
		  <label>Address</label></br>
		  <input type="text">
		  </div>
		  <div class="input-field">
		  <label>Area</label></br>
		  <input type="text">
		  </div>
		  <div class="input-field">
		  <label>Phone</label></br>
		  <input type="text">
		  </div>
		  <div class="input-field">
		  <label>Email</label></br>
		  <input type="text">
		  </div>
		  <div class="input-field">
		  <label>Website( optional )</label></br>
		  <input type="text">
		  </div>
		  <button type="button" class="btn"><span>Save</span></button>
		  </div>
		  </div>
          <div role="tabpanel" class="tab-pane" id="profile">
		  <div class="abhish">
		  <div class="row">
		  <div class="col-md-12">
		   <div class="input-field">
		  <input type="text" placeholder="Session e.g 2014-2015,Summer 2015">
		  <button type="button" class="btn"><i class="fa fa-plus" aria-hidden="true"></i><span>Save Session</span></button>
		  </div>
		  </div>
		  </div>
		  <div class="ab-3">
		  <div class="row">
		  <div class="plp">
		  <div class="col-md-7">
		  <div class="input-field">
		  <input type="text" placeholder="2017-18"></div></div>
		  <div class="col-md-1">
		  <a href="#"><i class="fa fa-pencil" aria-hidden="true"></i></a>
		  </div>
		  <div class="col-md-4">
		  <div class="abb">
		  <a href="#"><i class="fa fa-times" aria-hidden="true"></i></a></div>
		  </div>
		  </div>
		  </div>
		   <div class="row">
		  <div class="col-md-7">
		  <div class="input-field">
		  <input type="text" placeholder="2018-19"></div></div>
		  <div class="col-md-1">
		  <a href="#"><i class="fa fa-pencil" aria-hidden="true"></i></a>
		  </div>
		  <div class="col-md-4">
		  <div class="abb">
		  <a href="#"><i class="fa fa-times" aria-hidden="true"></i></a></div>
		  </div>
		  </div>
		  </div>
		  
		  </div>
		  </div>
          <div role="tabpanel" class="tab-pane" id="messages">
		  <div class="ass">
		  <div class="row">
		  <div class="col-md-7">
		  <div class="custom-select">
		  <select>
		  <option>CRECHE</option>
		  <option>NUR</option>
		  <option>OTHERS</option>
		  <option>PRI</option>
		  <option>SEC</option>
		  </select>
		  </div>
		  </div>
		  <div class="col-md-2">
		  <button type="button" class="btn"><i class="fa fa-plus" aria-hidden="true"></i><span>Add</span></button>
		  </div>
		  </div>
		  <div class="ytr">
		  <div class="row fdg">
		  <div class="shg">
		  <div class="col-md-3">
		  <p>CRECHE</p></div>
		  <div class="col-md-4">
		   <div class="input-field">
		  <input type="text"></div></div>
		  <div class="col-md-1">
		  <div class="bgb"><i class="fa fa-check" aria-hidden="true"></i></div></div>
		  <div class="col-md-4">
		  <div class="abb">
		  <a href="#"><i class="fa fa-times" aria-hidden="true"></i></a></div>
		  </div>
		  </div>
		  </div>
		  
		   <div class="row fdg">
		  <div class="shg">
		  <div class="col-md-3">
		  <p>PRIMARY</p></div>
		  <div class="col-md-4">
		   <div class="input-field">
		  <input type="text"></div></div>
		  <div class="col-md-1">
		  <div class="bgb"><i class="fa fa-check" aria-hidden="true"></i></div></div>
		  <div class="col-md-4">
		  <div class="abb">
		  <a href="#"><i class="fa fa-times" aria-hidden="true"></i></a></div>
		  </div>
		  </div>
		  </div>
		  
		   <div class="row fdg">
		  <div class="shg">
		  <div class="col-md-3">
		  <p>SECONDARY</p></div>
		  <div class="col-md-4">
		   <div class="input-field">
		  <input type="text"></div></div>
		  <div class="col-md-1">
		  <div class="bgb"><i class="fa fa-check" aria-hidden="true"></i></div></div>
		  <div class="col-md-4">
		  <div class="abb">
		  <a href="#"><i class="fa fa-times" aria-hidden="true"></i></a></div>
		  </div>
		  </div>
		  </div>
		  </div>
		  
		  </div>
		  </div>
	
          <div role="tabpanel" class="tab-pane" id="settings">
		  <div class="ass">
		  <div class="row">
		  <div class="col-md-12">
		  <label>Select Section</label>
		  <div class="custom-select">
		  <select>
		  <option>CRECHE</option>
		  <option>PRIMARY</option>
		  <option>SECONDARY</option>
		  </select>
		  </div>
		  </div>
		  </div>
		  </div>
		  <div class="assde">
		  <div class="row">
		   <div class="col-md-8">
		  <div class="input-field">
		  <input type="text" placeholder="Name"></div></div>
		   <div class="col-md-4">
		  <div class="input-field">
		  <input type="text" placeholder="Short Name"></div></div>
		  </div>
		  </div>
		  <div class="asde">
		  <div class="row">
		  <div class="col-md-12">
		  <button type="button" class="btn"><i class="fa fa-plus" aria-hidden="true"></i><span>Add</span></button>
		  </div>
		  </div>
		  </div>
		  <div class="ade">
		  <div class="row">
		   <div class="col-md-6">
		  <div class="input-field">
		  <input type="text"></div></div>
		   <div class="col-md-4">
		  <div class="input-field">
		  <input type="text"></div></div>
		   <div class="col-md-2">
		  <div class="bgb"><i class="fa fa-check" aria-hidden="true"></i></div></div>
		  </div>
		  </div>
		  </div>
		  
		  
         
    
      </div>
    </div>
  </div>
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
var x, i, j, selElmnt, a, b, c;
/*look for any elements with the class "custom-select":*/
x = document.getElementsByClassName("custom-select");
for (i = 0; i < x.length; i++) {
  selElmnt = x[i].getElementsByTagName("select")[0];
  /*for each element, create a new DIV that will act as the selected item:*/
  a = document.createElement("DIV");
  a.setAttribute("class", "select-selected");
  a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
  x[i].appendChild(a);
  /*for each element, create a new DIV that will contain the option list:*/
  b = document.createElement("DIV");
  b.setAttribute("class", "select-items select-hide");
  for (j = 1; j < selElmnt.length; j++) {
    /*for each option in the original select element,
    create a new DIV that will act as an option item:*/
    c = document.createElement("DIV");
    c.innerHTML = selElmnt.options[j].innerHTML;
    c.addEventListener("click", function(e) {
        /*when an item is clicked, update the original select box,
        and the selected item:*/
        var y, i, k, s, h;
        s = this.parentNode.parentNode.getElementsByTagName("select")[0];
        h = this.parentNode.previousSibling;
        for (i = 0; i < s.length; i++) {
          if (s.options[i].innerHTML == this.innerHTML) {
            s.selectedIndex = i;
            h.innerHTML = this.innerHTML;
            y = this.parentNode.getElementsByClassName("same-as-selected");
            for (k = 0; k < y.length; k++) {
              y[k].removeAttribute("class");
            }
            this.setAttribute("class", "same-as-selected");
            break;
          }
        }
        h.click();
    });
    b.appendChild(c);
  }
  x[i].appendChild(b);
  a.addEventListener("click", function(e) {
      /*when the select box is clicked, close any other select boxes,
      and open/close the current select box:*/
      e.stopPropagation();
      closeAllSelect(this);
      this.nextSibling.classList.toggle("select-hide");
      this.classList.toggle("select-arrow-active");
    });
}
function closeAllSelect(elmnt) {
  /*a function that will close all select boxes in the document,
  except the current select box:*/
  var x, y, i, arrNo = [];
  x = document.getElementsByClassName("select-items");
  y = document.getElementsByClassName("select-selected");
  for (i = 0; i < y.length; i++) {
    if (elmnt == y[i]) {
      arrNo.push(i)
    } else {
      y[i].classList.remove("select-arrow-active");
    }
  }
  for (i = 0; i < x.length; i++) {
    if (arrNo.indexOf(i)) {
      x[i].classList.add("select-hide");
    }
  }
}
/*if the user clicks anywhere outside the select box,
then close all select boxes:*/
document.addEventListener("click", closeAllSelect);
</script>
</body>
</html>
