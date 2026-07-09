<?php
include('../config.php');
$validate = new validation();
$pageTitle = 'Board Sheet';
$Filename = 'board_sheet.php';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
	
<style>
.gwt-CheckBox{
	    display: inline-flex;
		  color: #000000d4;
}
label{
	color: black;
    font-weight: 600;
}
.boldInfoLabel {
       padding: 5px;
    font-size: 15px;
    font-weight: 500;
    text-shadow: #ddf 1px 1px 0;
    text-align: center;
    color: #000000de;
}

.infoLabel {
    font-size: 13px;
    padding: 5px;
	text-align: center;
    color: #000000de;
}



                .page-title {
                    font-size: 20px;
                    margin-bottom: 0;
                    margin-top: 7px;
                    text-align: center;
                    background: white;
                    padding: 23px 0 30px 0px;
                    border-bottom: 5px solid gainsboro;
                }
                
                .zasw {
                    border: 1px solid gainsboro;
                    height: 1000px;
                    margin-top: 18px;
                }
                
                .zasw1 {overflow: -webkit-paged-x;
                    height: 1000px;
                    margin-top: 18px;
                }
                
                .sectionza {
                    background: white;
                    height: 1000px;
                }
                
               
                .top-serche input {
                    padding: 5px 49px 5px 14px;
                    border: 2px solid gainsboro;
                    border-radius: 4px;
                    position: relative;
                }
                
                .top-serche {
                    padding: 32px 0 9px 30px;
                }
                
                .content-page>.content {
                    margin-bottom: 60px;
                    margin-top: 60px;
                    padding: 20px 30px 15px 78px;
                    background: white;
                }
                
                .zswqas ul {
                    list-style: none;
                }
                
                .zswqas li a span i {
                    font-size: 29px;
                    padding-top: 9px;
                }
                
                .zswqas li a span {
                    padding-right: 16px;
                }
                
                .zswqas li a {
                    width: 239px;
                    display: block;
                    padding: 16px 14px 14px 18px;
                    border-bottom: 2px solid gainsboro;
                }
                
                .topside-section ul {
                    display: inline-flex;
                    list-style: none;
                }
                
                .topside-section li {
                    padding: 0 11px 0 0;
                }
                
                .topside-section {
                    padding-top: 8px;
                    border: 1px solid gainsboro;
                    box-shadow: 1px 6px 4px gainsboro;
                    padding: 14px 8px 11px 1px;
                }
                
				.zqw22 .panel-success>.panel-heading {
    background: white;
}

.zqw22 .nav.nav-tabs>li>a:hover, .nav.tabs-vertical>li>a:hover {
    color:black!important;
	 font-weight: 700;
 
}


.zqw22 .nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
  
    border-top-right-radius: 10px;
    border-top-left-radius: 10px;
    font-size: 10px;
    height: 38px;
    margin-top: 0;
}

div.dataTables_filter label {
    font-weight: 400;
    white-space: nowrap;
    text-align: left;
    border: 1px solid gainsboro;
    padding: 4px 13px 4px 0px;
    border-radius: 5px;
    color: black;
}


#example .active {
    background: #1565c0;
    color: white;
}
.zqw22 .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover, .tabs-vertical>li.active>a, .tabs-vertical>li.active>a:focus, .tabs-vertical>li.active>a:hover {
    color: black!important;
    font-weight: 700;
	    line-height: 38px;

    background: gainsboro;

}

.zqw22 .panel-success>.panel-heading {
    background: white;
    padding: 0;
} 
.zqw22 .panel .panel-body {
    border-right: none!important;
    border: 1px solid gainsboro;
}
.gwt-Label {
    padding: 8px;
}
.zqw22  input {
    padding: 8px 3px 10px 0;
    border: 1px solid gainsboro;
    background: #dcdcdc45;
    border-radius: 5px;
    margin-right: 8px;
	    margin-bottom: 5px;
}
.zqw22 button {
    border: 1px solid #1565c0;
    padding: 4px 3px 4px 3px;
    margin-right: 7px;
    background: transparent;
    color: #1565c0;
}
.zqw22 select {
    padding: 5px 0 8px 0;
    background: #dcdcdc2e;
}
.zqw22 .nav-tabs>li {
  
    padding: 0 4px 0 0;
}
#tab3success ,#tab4success .middleCenterInner{
    border: 1px solid gainsboro;
    padding: 17px 11px 51px 19px;
}
#tab3success  .middleCenterInner{
    border: 1px solid gainsboro;
    padding: 17px 11px 51px 19px;
}
#tab3success ,#tab4success .BFOGCKB-c-h{
    border-bottom: 3px solid;
    width: 300px;
}
#tab3success  .BFOGCKB-c-h{
    border-bottom: 3px solid;
    width: 300px;
}
#tab3success ,#tab4success  {
    border: 1px solid gainsboro;
    padding: 14px 4px 42px 11px;
    width: 361px;
}
#tab3success ,#tab4success .gwt-DecoratorPanel {
   
    padding: 21px 21px 43px 4px;
}
#tab3success .gwt-DecoratorPanel {
  
    padding: 21px 21px 43px 4px;
}
.zqw22 .panel .panel-body {

    border-bottom: 3px solid gainsboro!important;
}
.zqw22 .nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
    
    background: #dcdcdc4f!important;
}
.zqw22 .nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
    color: black!important;
    font-weight: 700;
	    line-height: 38px;

    background: gainsboro;
   
}
.xza{margin: 0;
    width: 294px;
    border-bottom: 1px solid;

}
.dataTables_paginate a {
    background-color: transparent;
    margin: 0 0px 0;
    padding: 8px 15px 9px;
    color: white;
    cursor: pointer;
    border: none;
}
.zqw22 .nav-tabs>li.active, .nav-tabs>li.active:focus, .nav-tabs>li.active:hover, .tabs-vertical>li.active, .tabs-vertical>li.active:focus, .tabs-vertical>li.active:hover {
    color: black!important;
    font-weight: 700;
	
   
}
.zswqas .activate a {
    width: 239px;
    display: block;
    padding: 16px 14px 14px 18px;
    border-bottom: 2px solid gainsboro;
    background: #1565c0;
    color: white;
}
.zqw22 .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover, .tabs-vertical>li.active>a, .tabs-vertical>li.active>a:focus, .tabs-vertical>li.active>a:hover {
      border-bottom: 3px solid #1565c0;
}
                .topside-section li a {
                    border: 1px solid #1565c0;
                    padding: 5px 5px 4px 5px;
                    display: block;
                }
                
                .zswqas li a:hover {
                    width: 239px;
                    display: block;
                    padding: 16px 14px 14px 18px;
                    border-bottom: 2px solid gainsboro;
                    background: #1565c0;
                    color: white;
                }
                
                .zswqas .active {
                    width: 239px;
                    display: block;
                    padding: 16px 14px 14px 18px;
                    border-bottom: 2px solid gainsboro;
                    background: #1565c0;
                    color: white;
                }
				
				
				
				
				
				
				
				
				
				
		.Wizard-a1 #example_length  {
  
	display:none;
}		
div.dataTables_filter label {
    font-weight: 400;
    white-space: nowrap;
    text-align: left;
    
}
div.dataTables_filter input {
    margin-left: .5em;
    display: inline-block;
    float: right;
    border: none;

}


div.dataTables_filter label {
    padding: 10px;
}


		div.dataTables_filter input {
    margin-left: .5em;
    display: inline-block;
    float: right;
}
div.dataTables_filter {
    text-align: center;
}		
.Wizard-a1 .zwq img {
    width: 50px;
}			
.Wizard-a1 .zwq {
    padding-right: 8px;
}
				
		.Wizard-a1 .setting {
    display: none;
}			
	.Wizard-a1 .dataTables_info {
    margin: 0 auto !important;
    text-align: center;
    font-size: 12px;
    float: initial;
    position: absolute;
    bottom: 11px;
    left: 0;
    right: 0;
}			
		#example {
    width: 85%!important;
    margin:0 auto;
}	

div.dataTables_filter input {
    width: 66%;
}

.zasw1 .card-box {
height: 900px;
}

.sectsab ul {
    
padding:0px;
}

div.dataTables_filter label {

    line-height: 23px;
}

	.dataTables_paginate #example_previous:before{
	
  content: "";
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-right: 12px solid #555;
    border-bottom: 6px solid transparent;
    position: absolute;
    z-index: 999999;
    left: 15px;
    bottom: 3px;
	}
	.Wizard-a1 .dataTables_info {

    position: sticky!important;
  
}
div.dataTables_paginate {
 
    position: relative;
    top: -47px;
}
	.dataTables_paginate a {
    background-color: transparent;
    margin: 0 0px 0;
    padding: 8px 15px 9px;
    color: white;
    cursor: pointer;
    border: none;
      position: static;
}
.dataTables_paginate .next {
    background: none;
    border: navajowhite;
    position: relative;
    color: white!important;
    position: relative;
  
}
div.dataTables_paginate {
    margin: 0;
    white-space: nowrap;
    text-align: center!important;
    padding-top: 27px;
}
.dataTables_paginate .disabled {
       background: none;
    color: white;
    border: none!important;
    padding: unset;
    display: block;
    width: 90%;
    color: transparent !important;
    margin: 0 auto;
}
div.dataTables_info {
    white-space: nowrap;
 padding-top: 0px;
}
.dataTables_paginate #example_next:before {
    content: "";
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-left: 12px solid #555;
    border-bottom: 6px solid transparent;
    position: absolute;
    z-index: 999999;
    right: 0;
    bottom: 9px;
    top: 4px;
}

div.dataTables_paginate {
    margin: 0;
    white-space: nowrap;
    text-align: center!important;
}
 .paging_simple_numbers span{
    /* display: none; */
    opacity: 0;
}
	#example td {
    padding: 15px 11px 18px 13px;
    border-bottom: 3px solid;
    margin: 0 0 0;
}
#example .active:hover {
    background: #1565c0;
    color: white;
}
		.Wizard-a1	.sorting_1 {
    display: none;
}	
.dataTables_filter label:before {
    position: absolute;
    /* left: 0; */
    right: 46px;
    top: 62px!important;
    /* bottom: 0; */
    border: 1px solid #1565c0;
}



     
.dataTables_filter:before{
	content:'';
	position:absolute;
}
      
	  div.dataTables_filter label {
position: relative;
    width: 85%;
    text-align: left;
	  }
	  
div.dataTables_filter {
    margin-top: 20px;
}

.sectsab li {
	
	list-style:none;
}


div.dataTables_paginate {
  

    margin: 0 auto;
}





.gwt-Button, input[type="file"], .maximizeButton {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    display: inline-block;
    margin: 0px 0;
    font-weight: normal;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    background-image: none;
    border: 1px solid transparent;
    white-space: nowrap;
    padding: 6px 12px;
    font-size: 12px;
    line-height: 1.42857;
    border-radius: 3px;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    -o-user-select: none;
    user-select: none;
    background-color: #fbfbfb;
    color: #78a440;
    color: #1565c0;
    border-color: #78a440;
    border-color: #1565c0;
    margin-right: 3px;
}
.gwt-Button:hover, .maximizeButton:hover, .gwt-Button:focus {
    color: #fff;
    text-decoration: none;
    background-color: #78a440;
    background-color: #1565c0;
table .gwt-CheckBox label {
    margin-bottom: -10px;
}

.flexTable-OddRow {
    background-color: #FFFFFF;
    font-size: 20px;
    padding: 8px;
    cursor: default;
}

.flexTable-EvenRow {
    background-color: #f8f8f8;
    font-size: 20px;
    padding: 8px;
    cursor: default;
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
<h4 class="page-title"><?php echo $pageTitle?></h4>

</div>
</div>
<!-- Basic Form Wizard -->
<div class="row">
<div class="sectionza">
<div class="col-md-12">
<div class="col-md-3">
<div class="zasw ">


<div class="zawq Wizard-a1">

<table id="example" class="display" >
<thead class="setting">
<tr>
<th>Position</th>
<th>Position</th>
</tr>
</thead>

<tbody>
<?php $aryDetail = $db->getRows("select * from input_score_subject_teacher ");
foreach ($aryDetail as $iList) {

    ?>
    <tr>
        <td style="padding:0px;"></td>
        <td class="sectsab <?php if ($_GET['class_id'] == $iList['class_id']) {
            echo "active";
        } ?>">
            <a href="<?php echo $FileName; ?>?action=board_sheet&class=<?php echo $iList['class_id'] ?>&session=<?php echo $iList['session_id'] ?>">
                <ul>
                        <span class="zwq">
                            <i class="fa fa-book" style="font-size:48px"></i>
                        </span>
                        <span class="subject">
                            <?php echo $db->getVal("select name from school_class where id ='" . $iList['class_id'] . "' "); ?>
                            <br/>
                            <?php echo $db->getVal("select session from school_session where id = '" . $iList['session_id'] . "' "); ?>
                                                                    </span>

                </ul>
            </a>
        </td>
    </tr>
<?php } ?>
</tbody>

<tfoot> 
<tr class="setting">
<th>Name</th>
<th>Position</th>


</tr>
</tfoot> 
</table>

</div>

</div>
</div>
<div class="col-md-9">

<table cellspacing="5" cellpadding="0">
<tbody>
<tr>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Print Broad Sheet</button></td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button" style="">Print Broad Sheet (Excel)</button></td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Print All Students' Report Sheets</button></td>
<td align="left" style="vertical-align: top;">
<button type="button" class="gwt-Button">Email Results</button></td>
<td align="left" style="vertical-align: top;">
<span class="gwt-CheckBox">
<input type="checkbox" value="on" id="gwt-uid-1" tabindex="0">
<label for="gwt-uid-1">Include Charts</label></span></td>
</tr>




</tbody>
</table>
    <div class="zasw1">

        <div class="card-box table-responsive">

            <tr><td align="left" style="vertical-align: top;">
                    <table cellspacing="5" cellpadding="0">
                        <tbody><tr><td align="left" style="vertical-align: top;">
                                <div class="gwt-Label">Assessment(s): </div></td>
                            <td align="left" style="vertical-align: top;">
<span class="gwt-CheckBox"><input type="checkbox" value="on" id="gwt-uid-6" tabindex="0" checked="">
<label for="gwt-uid-6">CA1 (30%)</label></span></td>
                            <td align="left" style="vertical-align: top;">
<span class="gwt-CheckBox">
<input type="checkbox" value="on" id="gwt-uid-7" tabindex="0" checked="">
<label for="gwt-uid-7">CA2 (30%)</label></span></td>
                            <td align="left" style="vertical-align: top;">
<span class="gwt-CheckBox">
<input type="checkbox" value="on" id="gwt-uid-8" tabindex="0" checked="">
<label for="gwt-uid-8">CA3 (40%)</label></span>
                            </td>

                            <td align="left" style="vertical-align: top;">
                                <button type="button" class="gwt-Button"style="margin-left:10px;">Open</button></td>

                        </tr></tbody></table></td>
            </tr>
            <tr>
                <td>
                    <div class="boldInfoLabel">
                        <?php echo $db->getVal("select session from school_session where id= '" . $_GET['session'] . "' ");
                        ?>
                    </div>
                </td>

            </tr>
            <tr>
                <td>
                    <div class="infoLabel">
                        Class: <?php echo $db->getVal("select name from school_class where id ='" . $_GET['class'] . "' ");

                    ?>
                    </div>
                </td>
            </tr>



            <table  class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Student ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Other Name</th>
                    <th>Agric</th>
                    <th>BSCI</th>
                    <th>BTECH</th>
                    <th>CRS/IRS</th>
                    <th>CEDU</th>
                    <th>Computer Studies</th>
                    <th>HECONS</th>
                    <th>PHE</th>
                    <th>Social Studies</th>
                    <th>No. of Sub.</th>
                    <th>Total Score</th>
                    <th>Average(100%)</th>
                    <th>Position</th>
                    <th>Final Grade</th>
                </tr>

                <?php
                $aryList = $db->getRows("select * from input_score_subject_teacher where class_id = '" .$_GET['class']. "'");
                foreach ($aryList as $iList) {
                $i = $i + 1;
                $aryPgAct["id"] = $iList['id'];
                    $aryStudent = $db->getRow("select * from manage_student where id = '" .$iList['student_id']. "'");
                ?>

                <tr class="flexTable-OddRow">
                    <td>
                        <div class="clickableElement" style="width: 100%;">
                            <?php echo $aryStudent['student_id'];?>

                        </div>
                    </td>


                    <td>
                        <div class="resultDataCell" style="width: 100%;">
                            <?php echo $aryStudent['first_name'];?>
                        </div>
                    </td>

                    <td>
                        <div class="resultDataCell" style="width: 100%;">
                            <?php echo $aryStudent['last_name'];?>
                        </div>
                    </td>

                    <td>
                        <div class="resultDataCell" style="width: 100%;">
                            <?php echo $aryStudent['other_name'];?>
                        </div>
                    </td>

                    <td>
                        <div class="gwt-Label" style="width: 100%;">
                            <?php echo $db->getVal("select cloud from manage_subject where class_id = '". $iList['subject_id'] ."'")?>
                        </div>
                    </td>

                    <td>
                        <div class="gwt-Label" style="width: 100%;">
                            <?php echo $iList['student_id'];?>
                        </div>
                    </td>

                    <td>
                        <div class="gwt-Label" style="width: 100%;">
                            <?php echo $iList['student_id'];?>
                        </div>
                    </td>

                    <td>
                        <div class="gwt-Label" style="width: 100%;">
                            <?php echo $iList['student_id'];?>
                        </div>
                    </td>

                    <td>
                        <div class="gwt-Label" style="width: 100%;">
                            <?php echo $iList['student_id'];?>
                        </div>
                    </td>

                    <td>
                        <div class="gwt-Label" style="width: 100%;">
                            <?php echo $iList['student_id'];?>
                        </div>
                    </td>

                    <td>
                        <div class="gwt-Label" style="width: 100%;">
                            28
                        </div>
                    </td>

                    <td>
                        <div class="gwt-Label" style="width: 100%;">
                            <?php echo $iList['student_id'];?>
                        </div>
                    </td>

                    <td></td>

                    <td>
                        <div class="gwt-Label" style="width: 100%;">
                            <?php echo $iList['student_id'];?>
                        </div>
                    </td>

                    <td>
                        <div class="resultDataCell" style="width: 100%;">
                            <?php echo $iList['student_id'];?>
                        </div>
                    </td>
                    <td>
                        <div class="resultDataCell" style="width: 100%;">
                            <?php echo $iList['total'];?>
                        </div>
                    </td>
                    <td>
                        <div class="gwt-Label" style="width: 100%;">
                            <?php echo $iList['total'];?>
                        </div>
                    </td>

                    <td>
                        <div class="resultDataCell" style="width: 100%;">
                            <?php echo $iList['total'];?>
                        </div>
                    </td>

                    <td>
                        <div class="resultDataCell" style="width: 100%;">
                            <?php echo $iList['total'];?>
                        </div>
                    </td>
                </tr>
                <?php }?>
                </thead>
                <tbody>
                </tbody>
            </table>
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

<?php include('inc.js.php'); ?>
<?php include('inc.footer.php'); ?>



<script>
		(function() {
		$(function() {
		var toggle;
		return toggle = new Toggle('.zswqas');
		});

		this.Toggle = (function() {
		class Toggle {
		constructor(toggleClass) {
		this.el = $(toggleClass);
		this.tabs = this.el.find(".xz");
		this.panels = this.el.find(".panel");
		this.bind();
		}

		show(index) {
		var activePanel, activeTab;
		//update tabs
		this.tabs.removeClass('activate');
		activeTab = this.tabs.get(index);
		$(activeTab).addClass('activate');
		//update panels
		this.panels.hide();
		activePanel = this.panels.get(index);
		return $(activePanel).show();
		}

		bind() {
		return this.tabs.unbind('click').bind('click', (e) => {
		return this.show($(e.currentTarget).index());
		});
		}

		};

		Toggle.prototype.el = null;

		Toggle.prototype.tabs = null;

		Toggle.prototype.panels = null;

		return Toggle;

		}).call(this);

		}).call(this);
		</script>





		<script>
		// Add active class to the current button (highlight it)
		var header = document.getElementById("example");
		var btns = header.getElementsByClassName("sectsab");
		for (var i = 0; i < btns.length; i++) {
		btns[i].addEventListener("click", function() {
		var current = document.getElementsByClassName("active");
		current[0].className = current[0].className.replace(" active", "");
		this.className += " active";
		});
		}
		</script>


		<script>		

		$(document).ready(function() {
		$('#example').DataTable();
		} );
		</script>	

</body>
</html>
