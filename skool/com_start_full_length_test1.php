<?php include('config.php'); 
include('inc.session.php');
$iMockDetail=$db->getRow("select * from com_mock_up where pageurl ='".$_GET['mockid']."' order by id desc"); 
$iMockCategory=$db->getRow("select * from com_category where id='".$iMockDetail['category']."'");

$iAlreadyFromMockQuesTime=$db->getRow("select * from com_mock_test_type where userid='".$_SESSION['userid']."' and mock_up_id='".$iMockDetail['id']."' and status='0' order by id desc");

$aryListMockDetail=$db->getRow("select id,pageurl from com_mock_up where pageurl ='".$_GET['mockid']."' order by id desc"); 
	$ijk=0;  
if($_POST['iThisRandomNumbernewAgain']!='')
{
	$iRandomid = $_POST['iThisRandomNumbernewAgain'];

	for($i=1; $i<=$_POST['count_ques']; $i++)
	{
		$iPostAns = $_POST['option'.$i];	
 		$iRightAnswer=$db->getVal("select answer from com_mock_up_question where id='".$_POST['autoid'.$i]."'");	
		$iCategoryId=$db->getVal("select category from com_mock_up_question where id='".$_POST['autoid'.$i]."'");	
		$iTypeId=$db->getVal("select type from com_mock_up_question where id='".$_POST['autoid'.$i]."'");	
		$iQuestion=$db->getRow("select * from com_mock_up_question where id='".$_POST['autoid'.$i]."'");	
		
		if($_POST['option'.$i]=='')
		{
			if($_POST['ques_type'.$i]=='1')
			{
				$iUserAnswer = '0'; // if user do not choose any option			
			}		
			else
			{
				$iUserAnswer = ''; // if user do not choose any option	
			}		
		}
		else
		{
			$iUserAnswer = $_POST['option'.$i]; // if user choose any option							
		}
		if($_POST['option'.$i]=='')
		{
			$iUserAnswerNew = '3'; // if user do not choose any option then it will go 3
		}
		elseif($_POST['option'.$i]==$iRightAnswer) 	
		{
			$iUserAnswerNew = '2'; // if user choose any coorect option then it will go 2										
		}		
		else 
		{
			$iUserAnswerNew = '1'; // if user choose wrong option then it will go 1							  		
		}
	
		$aryData=array(	
						'is_result'     	 	                =>	$iUserAnswerNew,
						'useranswer'     	 	                =>	$iUserAnswer,
						'positive_mark'     	 	            =>	$iQuestion['postive_mark'],
						'negative_mark'     	 	            =>	$iQuestion['negative_mark'],
						);  
			$flgIn111111 = $db->updateAry("com_mock_question_time", $aryData , "where uniqueid='".$iRandomid."' and question_id = '".$_POST['autoid'.$i]."'");		
		
		$aryData=array(	
						'userid'     	 	         			=>	$_SESSION['userid'],
						'mock_up_id'							=>	$aryListMockDetail['id'],
						'randomid'								=>	$iRandomid,
						'categoryid'     	 	         		=>	$iCategoryId,
						'typeid'     	 	         		    =>	$iTypeId,
						'question_id'     	 	         	    =>	$_POST['autoid'.$i],
						'correctanswer'							=>	$iRightAnswer,
						'useranswer'     	 	         		=>	$iUserAnswer,
						'is_result'     	 	                =>	$iUserAnswerNew,
						'positive_mark'     	 	            =>	$iQuestion['postive_mark'],
						'negative_mark'     	 	            =>	$iQuestion['negative_mark'],
						'ques_type'     	 	    			=>	$_POST['ques_type'.$i],
						);  
			$flgIn = $db->insertAry("com_mock_instantresult",$aryData);
			
			if(($iRightAnswer==$iPostAns) && ($iPostAns!=''))
			{	 	 
				$ijk = $ijk+1;		
			}
	}

	$aryData=array(
					'status'     	 	        	=>	1,
					);                      
		$flgIn12 = $db->updateAry("com_mock_test_type",$aryData,"where id='".$iAlreadyFromMockQuesTime['id']."'");
	
		unset($_POST);	
		redirect(SITE_URL.'full_length_test_sections/'.$_GET['mockid']);				
}

$MockTyidNew=$db->getRow("select * from com_mock_test_type where userid='".$_SESSION['userid']."' and mock_up_id='".$iMockDetail['id']."' and status='1' order by id desc");
$iComMockType=$db->getRow("select * from com_mock_up_type where mock_up_id='".$iMockDetail['id']."' and id>'".$MockTyidNew['type_id']."'");

if($iAlreadyFromMockQuesTime['id']!='')
{
	//$iThisRandomNumber = $iAlreadyFromMockQuesTime['uniqueid'];
	$iTotalSecond=$iAlreadyFromMockQuesTime['finaltotalsecond'];
	$iTime=$iTotalSecond/60;
	$iTiming=$iComMockType['section_time']-$iTime;
	if($iTiming<0)
	{
		$FinalTiming='0';
	}
	else
	{
		$FinalTiming=$iTiming;
	}
	$iTimer=round($FinalTiming,2);
}
else
{
	//$iThisRandomNumber = randomFix('25');
	$iTimer=$iComMockType['section_time'];
}

$iAlreadyFromMockQuesTimeNew=$db->getRow("select * from com_mock_test_type where userid='".$_SESSION['userid']."' and mock_up_id='".$iMockDetail['id']."' order by id desc");
if($iAlreadyFromMockQuesTimeNew['id']!='')
{
	$iThisRandomNumber = $iAlreadyFromMockQuesTimeNew['uniqueid'];
}
else
{
	$iThisRandomNumber = randomFix('25');
}

$CountTypeFromMockUpType=$db->getVal("select count(id) from com_mock_up_type where mock_up_id='".$iMockDetail['id']."'");
$CountTypeFromTest=$db->getVal("select count(id) from com_mock_test_type where mock_up_id='".$iMockDetail['id']."' and userid='".$_SESSION['userid']."' and status='1'");
?>
<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Academics | Home 1</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="<?php echo SITE_URL; ?>css/new-style.css">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo SITE_URL; ?>img/.png">
    <!-- Normalize CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo SITE_URL; ?>css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo SITE_URL; ?>css/font-awesome.min.css">
    <!-- Owl Caousel CSS -->
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/style.css">
<style>
html,body {
	height: 100%;
}
.ovr-f-hi h5{margin-bottom:10px;}
.sb-tb-fi {
    top: 15%;
    position: absolute;
    left: -20px;
	display:none;
}
.sec-tst-tab .card ul.nav.nav-tabs li.active a {
    background: #4e85c5;
    color: white;
}
.sec-tst-tab .tab-content .hr-intab {
    background: #4e85c5;
    height: 40px;
    margin-top: 0;
}
.sec-tst-tab .card a {
    color: #36ace9;
    font-weight: 600;
}
.tp-q {
    line-height: 1.5;
    margin: 0 0 5px 0;
}
.sec-tst-tab .changecolor i {
    color: #c1c1c1;
    font-weight: 600;
}
.sec-tst-tab {
    background: white;
}
.timebox
{
	background: white;
}
.group-button
{    
	margin: 0;
    padding: 0;
}
.group-section {
    padding: 5px 0 5px 0px;
    background: #eeeeee;
	float:left;
	width:50%;
	padding-left: 10px;
}
.group-section-right {
    padding: 9px 0 9px 0px;
    background: #eeeeee;
    float: left;
    width: 50%;
    padding-right: 10px;
}
.group-section h5
{    
	padding-top: 12px;
}
.group-section p {
    line-height: 1.5;
    margin: 0 0 8px 0;
    padding-top: 10px;
    padding-bottom: 0px;
}
.off-gruop {
    border: none;
    background: #38a9eb;
    color: white;
    font-size: 15px;
    padding: 4px 20px 4px 15px;
    border-radius: 3px;
}
#cccc button{
	padding: 1px;
}
#cccc button a{ font-size:12px; }
.off-gruop i {   
	margin-left: 12px;
}
.sec-tst-tab .card li {
		font-size: 14px;
}
.icon {
    width: 10%;
    float: left;
    padding: 1px 0 9px 28px;
    font-size: 19px;
    color: white;
}
.sec-tst-tab .tab-content {
    margin-top: -12px;
}
.battom-header {
    background: black;
    padding: 0;
    margin: 0;
    padding: 0 0 18px 0;
}
.clerk-test {
    background: #4e85c5;
    width: 90%;
    float: right;
    color: white;
        padding: 14px 0 16px 13px;
    /* margin: 0; */
    /* border-radius: 4px; */
}
.sec-main-test {
    padding-top: 0px;
}
.battom-heade h5
{
	color: white;
    float: right;
}
.navbar-header
{
}
.sec-not-two {
    border-top: 1px solid #607D8B;
}
.paper {
    background: #403e3e;
    text-align: right;
    color: white;
    padding-bottom: 7px;
    padding-top: 6px;
	font-size:14px;
}
.paper ul{
	display:block;
}
.ovr-f-hi ul {
    width: 225px;
  
    margin-bottom: 21px;
}
.sec-not-three ul li div a {
    color: black;
    display: b;
    display: block;
    padding: 9px 7px 9px 10px;
    width: 36px;
}
.sec-not-three ul li div {
    width: 30px;
}
.top-change
{    
	padding-top: 0px;
}
.not-visited {
    padding: 5px 8px 5px 8px;
    background: #cfcfcf;
    color: white;
    margin-right: 5px;
    font-size: 12px;
    border-radius: 4px;
}
.sec-not-three ul li {
    position: relative;
    display: inline-block;
    padding-right: 25px;
    width: 53px;
    margin-bottom: 7px;
    padding-left: 0px;
}
.paper li {
    display: inline-block;
    padding: 0 0 0 47px;
}
.battom-header {
    background: #403e3e;
    padding: 0;
    margin: 0;
    /* padding: 0 0 18px 0; */
}
.prof h4 {
    font-size: 19px;
    font-weight: 600;
    margin-bottom: 0px;
    text-align: center !important;
}
.maction{
    border-radius: 50px!important;
}
.prof a {
       color: #2b799c;
    font-size: 14px;
    font-weight: 600;
    margin-top: 42px;
    /* padding-top: 180px; */
    margin-top: 124px;
    position: relative;
    top: 15px;
    right: 11px;
}
.paper li {
    display: inline-block;
    padding: 0 0 0 8px;
}
.footer-test {
    position: fixed;
	z-index:99999999999999;
    left: 0;
    bottom: 0;
    width: 100%;
    background-color: white;
    color: white;
    text-align: center;
    padding-top: 15px;
    padding-bottom: 15px;
}
.sv-nxt a:first-child {
    color: white !important;
    background: #337ab7;
}
.footer-test a {
    color: black;
    padding-top: 5px;
    padding-bottom: 5px;
    border: 1px solid #ffffff29;
    padding-left: 11px;
    padding-right: 10px;
    /* height: 16px; */
}
.mark
{    
	border: 1px solid gray;
}
.sec-not-three h4 {
    background: #4e85c5;
    color: white;
    font-size: 16px;
    padding-top: 4px;
    padding-bottom: 4px;
    padding-left: 10px;
    margin-bottom: 0;
}
.sec-not-three ul li div a {
    color: black;
    display: b;
    display: block;
    padding: 7px 7px 5px 11px;
    width: 35px;
}
.sec-tst-tab ul {
    display: block;
}
.sec-tst-tab .card li {
    font-size: 14px;
    display: inherit;
}
.ansd label {
    display: inline-block;
    max-width: 100%;
    margin-bottom: 5px;
    font-weight: 700;
    width: 76%;
    /* float: right; */
}
.off-gruop:before {
    width: 0;
    height: 0;
    border-left: 7px solid transparent;
    border-right: 7px solid transparent;
    content: " ";
    border-top: 6px solid #38a9eb;
    position: relative;
    top: 30px;
    left: 51px;
}
@media screen and (min-width: 1799px){

}
.navbar-twitch {
    position: fixed;
    top: 25.5%;
    right: 0px;border-left:1px solid gray;
    width: 1px;
    height: 100%;
    border-radius: 0px;
    z-index: 1030;
    background: white;
}
.prof{height:70px;}
.sec-not-three.top-change{padding-top:0px;
padding-bottom:0px;}

.mk-f-rev-rgt.pull-right{z-index:99999999;}
.sec-not-three .not-answ:after {
    left: 24px !important;
}
.mk-f-rev-lft li {
	font-size: 14px !important;
    display: inline-block !important;
    border: 1px solid gray;
    padding: .2em;
    background-color: #fcf8e3;
    padding-right: 40px;
}
.mk-f-rev-lft li a{
    font-weight: 400 !important;
    color: black!important;
    padding-top: 5px;
    padding-bottom: 5px;
    border: 1px solid #ffffff29;
    padding-left: 11px;
    padding-right: 10px;

}
.sv-nxt a:first-child {
    color: white !important;
}



.activesubject{ display:none; }
.subjectpannel{ display:none; }
.givenanswer{ background: green; }						
.notgivenanswer{ background: red!important; }	
					
.markforreview{ background: #51265a!important; }						

.answeredandmarkforreview:after{
	content: "\f15c";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    font-size: 6px;
    padding-right: 0.5em;
    position: absolute;
    top: 15px;
    right: 7px;
    padding: 2px;
    background: #009000;
    border-radius: 100px;
}
.answeredandmarkforreview { background: #c84de4; }

		
.activequestion{ display:none; }	
.displayquestion{ display:block!important;}	
	
	
#cccc {
	display: block;
}
.tab-pane.activesubject #main {
	margin-right: 269px;
}
#mySidenav {
	width: 265px;
}
#mmm {
	display:none;
}
.ssasddd-hdge{
	overflow: hidden;
    position: absolute;
    width: 100%;
    bottom: 0px
}

.foot-frre {
      position: fixed;
    z-index: 99999999999999;
    left: 0;
    bottom: 0;
    width: 100%;
    background-color: white;
    color: white;
    text-align: center;
    padding-top: 15px;
    padding-bottom: 15px;
}


.ansd .ans-zero {
    border-radius:4px;
}
.not-answ {
    border-radius:4px;
}
.mancention{
    border-radius: 50px!important;
    padding: 3px 8px 4px 8px;
}
.manlan {
    border-radius: 50px!important;
    padding: 5px 9px 4px 8px;
}

html, body{ background: #ffffff00;}

@media only screen 
and (min-width : 1824px) {
 .cl-tmmm:after {
    content: "";
    position: absolute;
    background: #9e9e9e42;
    width: 101%;
    height: 1px;
    bottom: -2px;
    left: -14px;
    right: 0;
}
.secmulti-ques:before {
    content: "";
    position: absolute;
    background: #d0c5c5b8;
    width: 100%;
    height: 1px;
    bottom: -4px;
    left: 0px;
    right: 0;
    bottom: 1px;
}
.navbar-twitch-toggle {
    position: fixed;
    top: 0;
    right: -1px;
    height: 349px;
    bottom: 70px;
    margin: auto;
    background: #f8f8f6;
}
.navbar-twitch {
    position: fixed;
    top: 7.5% !important;
    right: 0px;
    border-left: 1px solid gray;
    width: 1px;
    height: 100%;
    border-radius: 0px;
    z-index: 1030;
    background: white;
}
.sb-tb-fi {
     
    position: absolute;
    left: -22px;
    display: none;
}

.prof img {
    padding: 10px;
}

.tm-sett {
    overflow: hidden;

    float: left;
    height: 40px;
    margin-top: 0;
    margin-right: 9px;
}
.sec-calcc i {
    color: orange;
    font-size: 21px;
    margin-top: 12px;
    position: absolute;
    right: 15px;
    top: 2px;
}
.tm-sett {
    overflow: hidden;
    float: left;
    height: 40px;
    margin-top: 0;
    margin-right: 9px;
}
.timer {
    background: #403e3e;
    max-width: 298px;
    color: #fff;
    text-align: center;
    margin-top: -31px!important;
    margin-left: 60px;
}
}
@media only screen and (min-width: 1224px) and (max-width :1780px){
.prof img {
    height: auto;
    border-left: 1px solid #403e3e4d;
    border-radius: 0px;
    border-right: 1px solid #403e3e4d;
    border: 1px solid #403e3e4d;
}



.poko {
    width: 53%;
}
.sec-calcc i {
    color: orange;
    font-size: 21px;
    margin-top: 12px;
    position: absolute;
    right: 10px;
    top: -1px;
}


.sec-calcc-inn {
    display: none;
    width: 223px;
    z-index: 999999;
    position: absolute;
    background: white;
    left: 46%;
    right: 0px;
    margin: auto;
    top: 65px;
}
.ovr-f-hi ul {
    width: 225px;
    /* height: auto; */
    margin-bottom: 515px;
}
}


html, body {
    background: #ffffff00;
    overflow: hidden;
}

</style>
</head>
<body>
<header class="top-header">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12 battom-header">
				<div class="icon">
				<a href="<?php echo SITE_URL; ?>">
					<img src="<?php echo SITE_URL; ?>uploads/<?php echo $iSettings['logo']; ?>">
				</a>
				</div>
				<div class="clerk-test"><?php echo $iMockDetail['title']; ?></div>
			</div>
			<div class="paper col-sm-12">
				<ul class="pull-left">
					<li class="colo-q"><?php echo $iMockDetail['title']; ?></li>	
				</ul>
				<ul>
					<li><i class="fa fa-info-circle" aria-hidden="true pull-right"></i>  </li>
					<li class="colo-q" data-toggle="modal" data-target="#myModal-paper">Question Paper</li>	
					<li><i class="fa fa-info-circle" aria-hidden="true pull-right"></i>  </li>
					<li class="color-i" data-toggle="modal" data-target="#myModal-ins">Instructions</li>
				</ul>
			</div>
		</div>
	</div>
</header>	

<div id="draggable" class="ui-widget-content">
	<div class=" sec-calcc-inn" id="myDiv-cal" style="display:none;">
		<div class="headdd-cal" ><div class="cl-h-l pull-left">Normal Calculator</div>
			<div class="cl-h-r pull-right"> <span onclick="myFunction()"><i class="fa fa-times-circle" aria-hidden="true"></i></span> </div>         
		</div>
        <div class="cal-pad">
            <input readonly id="display1" type="text" class="form-control-lg text-right">
			<input readonly id="display2" type="text" class="form-control-lg text-right form-input-light">
           
            <div class="d-flex justify-content-between button-row">
                <button id="left-parenthesis" type="button" class="operator-group">&#40;</button>
                <button id="right-parenthesis" type="button" class="operator-group">&#41;</button>
                <button id="square-root" type="button" class="operator-group">&#8730;</button>
                <button id="square" type="button" class="operator-group">&#120;&#178;</button>
            </div>
          
            <div class="d-flex justify-content-between button-row">
                <button id="clear" type="button">&#67;</button>
                <button id="backspace" type="button">&#9003;</button>
                <button id="ans" type="button" class="operand-group">&#65;&#110;&#115;</button>
                <button id="divide" type="button" class="operator-group">&#247;</button>
            </div>
            
            <div class="d-flex justify-content-between button-row">
                <button id="seven" type="button" class="operand-group">&#55;</button>
                <button id="eight" type="button" class="operand-group">&#56;</button>
                <button id="nine" type="button" class="operand-group">&#57;</button>
                <button id="multiply" type="button" class="operator-group">&#215;</button>
            </div>
        
            <div class="d-flex justify-content-between button-row">
                <button id="four" type="button" class="operand-group">&#52;</button>
                <button id="five" type="button" class="operand-group">&#53;</button> 
                <button id="six" type="button" class="operand-group">&#54;</button> 
                <button id="subtract" type="button" class="operator-group">&#8722;</button>
            </div>
     
            <div class="d-flex justify-content-between button-row">
                <button id="one" type="button" class="operand-group">&#49;</button> 
                <button id="two" type="button" class="operand-group">&#50;</button>
                <button id="three" type="button" class="operand-group">&#51;</button>
                <button id="add" type="button" class="operator-group">&#43;</button>
            </div>

            <div class="d-flex justify-content-between button-row">
                <button id="percentage" type="button" class="operand-group">&#37;</button>
                <button id="zero" type="button" class="operand-group">&#48;</button>
                <button id="decimal" type="button" class="operand-group">&#46;</button>
                <button id="equal" type="button">&#61;</button>
            </div>
        </div>
	</div>
</div>
<script>
function myFunction() {
    var x = document.getElementById("myDiv-cal");
    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
}
</script>
<script src="js/calc-main.js" type="text/javascript"></script>
<script type="text/javascript">
var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-36251023-1']);
  _gaq.push(['_setDomainName', 'jqueryscript.net']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>

<!-- Modal -->
<div id="myModal-ins" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4>General Instruction</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="">
					<div class="sec-l-intro">
						<h4>Please Read the Instruction Carefully </h4>
						<div class="gn-intr">
							<h5>General Introduction:-</h5>
							<!--<p>1. Test Name is <?php echo $iMockDetail['title']; ?>.</p>
							<p>2. Total Question is <?php echo $db->getVal("select sum(no_of_que) from com_mock_up_type where mock_up_id='".$iMockDetail['id']."'"); ?>.</p>
							<p>3. Total Duration of Examination is <?php if($iMockDetail['timing_type']=='0') { echo $iMockDetail['timing']; } else{ echo $db->getVal("select sum(section_time) from com_mock_up_type where mock_up_id='".$iMockDetail['id']."'"); } ?> minute.</p>
							<p>4. Mark For Right Answer is <?php echo $iMockDetail['mark_right_ans']; ?>.</p>
							<p>5. Mark For Wrong Answer is <?php echo $iMockDetail['mark_wrong_ans']; ?>.</p>-->
						</div>
						
						<div class="gn-intr">
							<?php echo $iMockDetail['description']; ?>
							<img src="<?php echo SITE_URL; ?>uploads/<?php echo $iMockDetail['instruction']; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<div id="myModal-paper" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4>Question Paper</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
			<div class="">
				<div class="sec-l-intro">
				<?php 
				$iPopUpMockUpTypeAll=$db->getRows("select * from com_mock_up_type where mock_up_id='".$iMockDetail['id']."'");
				foreach($iPopUpMockUpTypeAll as $PopUpMockTypeList)
				{
				?>
					<div class="section-pap">
						<h1>Section: <?php echo $db->getVal("select typename from com_type where id='".$PopUpMockTypeList['type']."' "); ?></h1>
					<?php $PopQues=0;
					$iPopUpQuestionAll=$db->getRows("select * from com_mock_up_question where mock_up_id='".$iMockDetail['id']."' and type='".$PopUpMockTypeList['type']."'");
					foreach($iPopUpQuestionAll as $PopUpQuesList)
					{	$PopQues=$PopQues+1;
					?>	
						<div class="row q-1">
							<div class="col-md-1">Q. <?php echo $PopQues; ?>)</div>
							<div class="col-md-11">
								<h5>Directions for Question <?php echo $PopQues; ?> to <?php echo $PopUpMockTypeList['no_of_que']; ?>:-<h5>
								<p><?php echo $PopUpQuesList['question']; ?></p>
								<p class="q-typee">
								Marks for correct answer: <span class="text-bold" style="color:green;"><?php echo floatval($PopUpQuesList['postive_mark']); ?></span>&nbsp;&nbsp;|&nbsp;
								Marks for wrong answer: <span class="text-bold" style="color:red;"><?php echo floatval($PopUpQuesList['negative_mark']); ?></span>
								</p>
								<?php if($PopUpQuesList['option1']!='') { ?>
								<p class="q-typee" style="color: red;">Question Type: &nbsp Multiple Choice Question</p>
								<?php } else { ?>
								<p class="q-typee" style="color: red;">Question Type: &nbsp Type In The Answer</p>
								<?php } ?>
							</div>
						</div>
					<?php } ?>	
					</div>
				<?php } ?>	
				</div>
			</div>
			</div>
		</div>
	</div>
</div>


<section class="sec-main-test">
<div class="navbar  navbar-twitch  sidenav"  id="mySidenav"  >
	<div id="border-side-sidebar">
		<div class="col-md-12 navbar-header">
			<div class="sec-r-side" ></div>


			
			
			
				<div class="row sec-not-two">
				<div class="col-md-12">
				<div class=" prof">
			<div class="col-sm-6 col-xs-6 text-center">
			<?php if($iUserProfile['profile_image']!='') { ?>
				<img src="<?php echo SITE_URL; ?>uploads/<?php echo $iUserProfile['profile_image']; ?>" class="img-circle" style="width: 100%;">
			<?php } else { ?>
				<img src="<?php echo SITE_URL; ?>img/download.png" class="img-circle" style="width: 100%;">
			<?php } ?>	
			</div>
			<div class="col-sm-6 col-xs-6 text-center">
				<h4><?php echo $iUserProfile['fullname']; ?></h4><!--<a href="#">Profile</a>--->
			</div>
		</div>
			</div>
				
					<div class="col-sm-6 col-xs-6 angled pd-r">
						<div class="ansd"><a class="ans-zero">0</a><label>Answered</label></div>
					</div>
					<div class="col-sm-6 col-xs-6 pd-l">
						<div class="ansd"><a class=" not-answ">1</a><label>Not Answered</label></div>
					</div>
					<div class="col-sm-6 col-xs-6 pd-r">
						<div class="ansd"><a class="not-visited">1</a><label>Not Visited</label></div>
					</div>
					<div class="col-sm-6 col-xs-6 pd-l">
						<div class="ansd"><a class="mar-f-r">1</a><label>Marked for Review</label></div>
					</div>
					<div class="col-sm-12 col-xs-12">
						<div class="ansd">
							<a class="mar-f-r mr-r12">5</a><label>Answered & Marked for Review
							<br><span>(will be Considered for evaluation)</span></label>
						</div>
					</div>
				</div>

				<div class="sb-tb-fi" id="cccc">
					<button onclick="closeNav()" class="">
						<i class="fa fa-caret-left nav-open" onclick="closeNav()"></i>	
						<i class="fa fa-caret-right nav-close  closebtn"></i>
						<a>A<br>N <br>S <br>W <br>E <br>R <br><br>S <br>H<br>E<br>E<br>T</a>
					</button>
				</div>
<?php if($CountTypeFromMockUpType==$CountTypeFromTest+1) { ?>
<form action="<?php echo SITE_URL; ?>full_length_test_analysis/<?php echo $_GET['mockid']; ?>" method="POST" id="submitmocktestform">
<?php } else { ?>
<form action="<?php echo SITE_URL; ?>full_length_test_sections/<?php echo $_GET['mockid']; ?>" method="POST" id="submitmocktestform">
<?php } ?>
				<!--Question Pallete--->
				<div class="row sec-not-three top-change">
					<h4>Question Palette</h4>
					<div class="col-sm-12 col-xs-12  ovr-f-hi">
						<h5>Choose a Question</h5>
						
							<div class="container122 fg">
						<div class="section">
						<div class="scrollable-content">
						
						<ul class="" id="subjectpannel">
						<?php  $N=0;
							$O=0;
						$aryListType=$db->getRows("select * from com_mock_up_type where mock_up_id='".$iMockDetail['id']."' and type='".$iComMockType['type']."'");
							foreach($aryListType as $iListType)
							{  $N=$N+1;
						?>
						<?php 
							$aryList=$db->getRows("select * from com_mock_up_question where mock_up_id='".$iMockDetail['id']."' and type = '".$iListType['type']."' order by id asc limit 0, ".$iListType['no_of_que']);
							$Oarr=count($aryList);	
								foreach($aryList as $iList)
								{ $O=$O+1;
								if($O>$Oarr) { $ONT=$N; } else { $ONT=$N; }
							$iAlreadyAnswered=$db->getRow("select * from com_mock_question_time where mock_up_id='".$iMockDetail['id']."' and userid='".$_SESSION['userid']."' and question_id='".$iList['id']."'");	
								?>
							<li onClick="getquestion('<?php echo $N; ?>','<?php echo $O; ?>','<?php echo $O; ?>','<?php echo $ONT; ?>');getanswer('2','<?php echo $O; ?>');get_question_time('<?php echo $iList['id']; ?>','<?php echo $iList['id']; ?>','<?php echo $O; ?>','0');">
								<div id="answer<?php echo $O; ?>" class="not-visited <?php if($iAlreadyAnswered['is_color']=='1') { echo "givenanswer"; } elseif($iAlreadyAnswered['is_color']=='2') { echo "notgivenanswer"; } elseif($iAlreadyAnswered['is_color']=='3') { echo "markforreview"; } elseif($iAlreadyAnswered['is_color']=='4') { echo "answeredandmarkforreview"; } ?>"><a><?php echo $O; ?></a></div>
							</li>
							<?php } ?>
						<?php } ?>
						</ul>
						
						</div>
						</div>
						</div>
						
					</div>
				</div>
			<!--Question Pallete-->
		</div>
	</div>
		
		<button type="button" onclick="openNav()"  id="mmm"  class="btn btn-default btn-xs navbar-twitch-toggle">
			<i class="fa fa-caret-left nav-open" onclick="closeNav()"  ></i>	
			<i class="fa fa-caret-right nav-close  closebtn"   ></i>
			<a>A<br>N <br>S <br>W <br>E <br>R <br><br>S <br>H<br>E<br>E<br>T</a>
		</button>
	</div>
</div>

<div class="container-fluid">
						
<div class="row timebox" id="main4" style="margin-right: 247px;">
	<div class="col-sm-10 group-button">
		<div class="sec-tst-tab sec-tst-two">
			<div class="col-md-12 col-sm-12 col-xs-12">
			
				<div class="col-md-12 cl-tmmm">
					<div class="col-md-7 col-sm-5"></div>
					<div class="col-md-5 col-sm-7">
					<div class="poko pull-right">
						<div class="tm-sett"> <div class="timer-mn">Time Left:<div id="time" class="timer"></div></div>	</div>	
						<div class="card sec-calcc" >
							<i class="fa fa-calculator" aria-hidden="true" onclick="myFunction()"></i>
						</div>
					</div>	
                      </div>					
				</div>
				
				<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="card">
					<ul class="nav nav-tabs" role="tablist" style="display:block;">
						<div class="changecolor"><a href="#"><i class="fa fa-caret-left" aria-hidden="true"></i></a></div>
						<?php $i=0;
						$aryListType=$db->getRows("select * from com_mock_up_type where mock_up_id='".$iMockDetail['id']."' ");
							foreach($aryListType as $iListType)
							{ $i=$i+1; 
							?>
						<style>.activequestion<?php echo $i; ?> { display:none; }</style>
						<?php //echo $iComMockType['type'].'====='.$iListType['type']; ?>	
						<li role="presentation" class="lipara <?php if($iComMockType['type']==$iListType['type']) { echo "active"; } ?>">
							<a><?php echo $db->getVal("select typename from com_type where id='".$iListType['type']."' "); ?><i class="fa fa-info-circle pull-right" aria-hidden="true"></i></a>
						</li>
						<?php } ?>
						<a href="#" class="pull-right changecolor"><i class="fa fa-caret-right" aria-hidden="true"></i></a>
					</ul>
				</div>
				
						</div>

			</div>
		</div>
	</div>
	<div class="col-md-2 col-sm-4 col-xs-12 ">


		
	</div>
</div>



<div class="row sec-tst-two" >





<div class="col-md-12 col-sm-12 col-xs-12 ">
<div class="sec-tst-tab">  
	<div class="card">
	

		<div class="tab-content">
		

			<?php $j=0;
			$L=0;
			$iTotalQuestion=$db->getVal("select sum(no_of_que) from com_mock_up_type where mock_up_id='".$iMockDetail['id']."' and type='".$iComMockType['type']."'");
			$aryListType=$db->getRows("select * from com_mock_up_type where mock_up_id='".$iMockDetail['id']."' and type='".$iComMockType['type']."'");
			foreach($aryListType as $iListType)
					{   $j = $j+1;	
					
					$TotalQue += $iListType['no_of_que'];
					if($j=='1')
					{
						$Qid='1';
					}
					else
					{
						$Qid=($TotalQue+1)-$iListType['no_of_que'];
					}
					?>
            <div role="tabpanel" class="tab-pane activesubject" id="subject<?php echo $j; ?>" style="display:<?php if($j=='1') { echo "block"; } ?>">
				<!--<p class="tp-q">Question Type : Multiple Choice Question</p>-->
				<input type="hidden" name="firstques" id="firstques<?php echo $j; ?>" value="<?php echo $Qid; ?>">
				<div class="row tab-inn-r" id="main">
				
	
				
				<?php $k=0;
					$aryList=$db->getRows("select * from com_mock_up_question where mock_up_id='".$iMockDetail['id']."' and type = '".$iListType['type']."' order by id asc limit 0, ".$iListType['no_of_que']);
					$Karr=count($aryList);
						foreach($aryList as $iList)
						{ 	$k=$k+1;
						$L=$L+1;
						$KL=$L+1;
						if($k==$Karr) { $KLNT=$j+1; } else { $KLNT=$j; }
						$PL=$L-1;
					
						if($k==1) { $PLNT=$j-1; } else { $PLNT=$j; }
						if($PLNT==0) { $PLNT=1; }
						
						if($L == '1') { $iQuestionIdNowToTime = $iList['id'];  }	
						
				$iAlreadyAnswered=$db->getRow("select * from com_mock_question_time where mock_up_id='".$iMockDetail['id']."' and userid='".$_SESSION['userid']."' and question_id='".$iList['id']."'");	
						?>
				<div class="activequestion<?php echo $j; ?>" id="question<?php echo $L; ?>" style="display:<?php if($k=='1') { echo "block"; } else{ echo "none"; }?>">		
					<div class="col-md-12 col-sm-12 col-xs-12 secmulti-ques">
						<div class="col-md-6 col-sm-6 col-xs-6 "> 
						<?php if($iList['option1']!='') { ?>
							<span class="f-spaann">Question Type: &nbsp Multiple Choice Question</span> 
						<?php } else { ?>
							<span class="f-spaann">Question Type: &nbsp Type In The Answer</span> 
						<?php } ?>	
						</div>
						<div class="col-md-6 col-sm-6 col-xs-6 "> <span class="f-spaann-2">Mark(s) for correct answer:<span class="positive-counttt"><?php echo floatval($iList['postive_mark']); ?></span>&nbsp  | wrong answer:<span class="negetive-counttt"><?php echo floatval($iList['negative_mark']); ?></span></span></div>
					</div>	
					
					<div class="col-md-12 col-sm-12 col-xs-12">
						
					<hr class="hr-intab">
					<?php if($iList['paragrah']!='') { ?>
					<div class="col-md-7 col-sm-7 col-xs-12 bdr-fht"> 
					
					<div class="container12"> 
					<div class="section"> 
						<div class="tab-inn-r-con-l">
							<h4>Direction for question <span><?php echo $L; ?></span></h4>
							<p class="ques-p"><?php echo stripslashes($iList['paragrah']); ?></p>
						</div>
						
						
					</div>
					</div>
					
					</div>
					<?php } ?>
					<div class="<?php if($iList['paragrah']!='') { ?>col-md-5 col-sm-5 col-xs-12<?php } else { ?> col-md-12 col-sm-12 col-xs-12<?php } ?>righ-head">
						<h3>Question No. <?php echo $L; ?> </h3>
						<div class="container122">
					<div class="section">
						<div class="q-option-d">
							<p><?php echo stripslashes($iList['question']); ?></p>
							<?php if($iList['option1']!='') { ?>
							<input type="hidden" name="ques_type<?php echo $L; ?>" id="ques_type<?php echo $L; ?>" value="1">
							<ul>
								<li><input type="radio" name="option<?php echo $L; ?>" value="1" onclick="getanswer('1','<?php echo $L; ?>');useranswer('<?php echo $L; ?>','1');" <?php if($iAlreadyAnswered['useranswer']=='1') { echo "checked"; } ?>/><?php echo stripslashes($iList['option1']); ?></li>
								<li><input type="radio" name="option<?php echo $L; ?>" value="2" onclick="getanswer('1','<?php echo $L; ?>');useranswer('<?php echo $L; ?>','2');" <?php if($iAlreadyAnswered['useranswer']=='2') { echo "checked"; } ?>/><?php echo stripslashes($iList['option2']); ?></li>
								<li><input type="radio" name="option<?php echo $L; ?>" value="3" onclick="getanswer('1','<?php echo $L; ?>');useranswer('<?php echo $L; ?>','3');" <?php if($iAlreadyAnswered['useranswer']=='3') { echo "checked"; } ?>/><?php echo stripslashes($iList['option3']); ?></li>
								<li><input type="radio" name="option<?php echo $L; ?>" value="4" onclick="getanswer('1','<?php echo $L; ?>');useranswer('<?php echo $L; ?>','4');" <?php if($iAlreadyAnswered['useranswer']=='4') { echo "checked"; } ?>/><?php echo stripslashes($iList['option4']); ?></li>
								<?php if($iList['option5']!='') { ?>
								<li><input type="radio" name="option<?php echo $L; ?>" value="5" onclick="getanswer('1','<?php echo $L; ?>');useranswer('<?php echo $L; ?>','5');" <?php if($iAlreadyAnswered['useranswer']=='5') { echo "checked"; } ?>/><?php echo stripslashes($iList['option5']); ?></li>
								<?php } ?>
							</ul>
							<?php } else { ?>
							<input type="hidden" name="ques_type<?php echo $L; ?>" id="ques_type<?php echo $L; ?>" value="2">
							<div id="keypad">
								<div>
									<input type="text" name="option<?php echo $L; ?>" id="calcfield<?php echo $L; ?>" value="<?php echo $iAlreadyAnswered['useranswer']; ?>" size="14" readonly>
								</div>
								<div>
									<input type="button" value="Backspace" onCLick="back('<?php echo $L; ?>')">
								</div>
								<div id="row1">
									<input type="button" name="number" id="number<?php echo $L; ?>" value="1" onClick="keypad('1','<?php echo $L; ?>');getanswer('1','<?php echo $L; ?>');">
									<input type="button" name="number" id="number<?php echo $L; ?>" value="2" onClick="keypad('2','<?php echo $L; ?>');getanswer('1','<?php echo $L; ?>');">
									<input type="button" name="number" id="number<?php echo $L; ?>" value="3" onClick="keypad('3','<?php echo $L; ?>');getanswer('1','<?php echo $L; ?>');">
								</div>
								<div id="row2">
									<input type="button" name="number" id="number<?php echo $L; ?>" value="4" onClick="keypad('4','<?php echo $L; ?>');getanswer('1','<?php echo $L; ?>');">
									<input type="button" name="number" id="number<?php echo $L; ?>" value="5" onClick="keypad('5','<?php echo $L; ?>');getanswer('1','<?php echo $L; ?>');">
									<input type="button" name="number" id="number<?php echo $L; ?>" value="6" onClick="keypad('6','<?php echo $L; ?>');getanswer('1','<?php echo $L; ?>');">
								</div>
								<div id="row3">
									<input type="button" name="number" id="number<?php echo $L; ?>" value="7" onClick="keypad('7','<?php echo $L; ?>');getanswer('1','<?php echo $L; ?>');">
									<input type="button" name="number" id="number<?php echo $L; ?>" value="8" onClick="keypad('8','<?php echo $L; ?>');getanswer('1','<?php echo $L; ?>');">
									<input type="button" name="number" id="number<?php echo $L; ?>" value="9" onClick="keypad('9','<?php echo $L; ?>');getanswer('1','<?php echo $L; ?>');">
								</div>
								<div id="row4">
									<input type="button" name="number" id="number<?php echo $L; ?>" value="0" onClick="keypad('0','<?php echo $L; ?>');getanswer('1','<?php echo $L; ?>');">
								</div>
								<div>
									<input type="button" value="Clear All" onCLick="clearall('<?php echo $L; ?>')">
								</div>
							</div>
							<?php } ?>
						</div>
					
					</div>
					</div>
					
					</div>
					</div>
					
					<footer class="row">
						<div class="col-md-12 ssasddd ">
							<div class="ssasddd-hdge foot-frre">
								<div class="col-md-7 col-sm-7 col-xs-12">
									<ul class="mk-f-rev-lft pull-left">
									<?php $iquwww=$db->getVal("select * from com_mock_up_question where mock_up_id='".$iMockDetail['id']."' and type = '".$iListType['type']."' and id>'".$iList['id']."' order by id asc limit 0,1 "); ?>
									<?php if($L!=$iTotalQuestion) { ?>
										<li class="mark" onClick="getanswer('3','<?php echo $L; ?>');getquestion('<?php echo $j; ?>','<?php echo $L; ?>','<?php echo $KL; ?>','<?php echo $KLNT; ?>');get_question_time('<?php echo $iquwww; ?>','<?php echo $iList['id']; ?>','<?php echo $L; ?>','3');"><a>Mark for Review & Next</a></li>
									<?php } else { ?>
										<li class="mark" onClick="getanswer('3','<?php echo $L; ?>');"><a>Mark for Review & Next</a></li>
									<?php } ?>	
										<li class="mark" onClick="getanswer('4','<?php echo $L; ?>');get_question_time('<?php echo $iList['id']; ?>','<?php echo $iList['id']; ?>','<?php echo $L; ?>','0');"><a>Clear Response</a></li>
									
									<!---<li class="mark" <?php if($L!=1) { ?> onClick="getquestion('<?php echo $j; ?>','<?php echo $L; ?>','<?php echo $PL; ?>','<?php echo $PLNT; ?>');getanswer('5','<?php echo $L; ?>');"<?php } ?>><a>Previous</a></li>--->
									</ul>
								</div>
								<div class="col-md-5 col-sm-5 col-xs-12"> 
									<?php if($L!=$iTotalQuestion) { ?>
									<div class="sv-nxt">
										<a onClick="getquestion('<?php echo $j; ?>','<?php echo $L; ?>','<?php echo $KL; ?>','<?php echo $KLNT; ?>');getanswer('5','<?php echo $L; ?>');get_question_time('<?php echo $iquwww; ?>','<?php echo $iList['id']; ?>','<?php echo $L; ?>','0');">Save & Next</a>
									</div>
									<?php } else { ?>
									<div class="sv-nxt">
										<a onClick="get_question_time('<?php echo $iquwww; ?>','<?php echo $iList['id']; ?>','<?php echo $L; ?>','0');">Save & Next</a>
									</div>
									<?php } ?>
									
									<ul class="mk-f-rev-rgt pull-right">
										<li class="mark"><a href="#" onClick="saveforleter('<?php echo SITE_URL; ?>full_length_instruction/<?php echo $iMockDetail['pageurl']; ?>')">Save For Later</a></li>
										<li><button type="button" onClick="submitform()" class="btn-primary">Submit</button></li>
									</ul>
								</div>
							</div>
						</div>
					</footer>	
				</div>	
				
					<input type="hidden" name="ques_id<?php echo $L; ?>" value="<?php echo $iList['id']; ?>" />
					<input type="hidden" name="useranswer<?php echo $L; ?>" id="useranswer<?php echo $L; ?>" value="<?php echo $iAlreadyAnswered['useranswer']; ?>"/>
					<input type="hidden" name="autoid<?php echo $L; ?>" value="<?php echo $iList['id']; ?>" />
					<?php } ?>
					
				</div>
			</div>
			<?php } ?>	
			<input type="hidden" name="count_ques" value="<?php echo $L; ?>"/>
			<input type="hidden" name="iThisRandomNumbernewAgain" value="<?php echo $iThisRandomNumber ; ?>"/>
			<input type="hidden" name="last_ques_id" id="last_ques_id" value="<?php echo $iQuestionIdNowToTime; ?>"/>
        </div>
	</div>
</div>
</div>
</div>
</div>
</form>
</section>
<?php
$iCategoryDetail=$db->getRow("select * from com_mock_up_question where id='".$iQuestionIdNowToTime."'");	

$iAlreadyMockQuesTime=$db->getRow("select * from com_mock_question_time where userid='".$_SESSION['userid']."' and mock_up_id='".$iMockDetail['id']."' and uniqueid='".$iThisRandomNumber."' order by id desc");
if($iAlreadyMockQuesTime['id']=='')
{	
	if($iCategoryDetail['option1']=='')
	{
		$UserAnswer='';
		$QuesType='2';
	} 
	else 
	{ 
		$UserAnswer='0';
		$QuesType='1'; 
	}
	
	$aryData=array(
					'uniqueid'						=>	$iThisRandomNumber,
					'userid'     	 	            =>	$_SESSION['userid'],
					'mock_up_id'     	 	        =>	$iMockDetail['id'],
					'question_id'     	 	     	=>	$iQuestionIdNowToTime,
					'useranswer'     	 	     	=>	$UserAnswer,
					'start_time'     	 	        =>	date("h:i:s"),
					'totalsecond'     	 	        =>	0,
					'create_at'     	 	        =>	date("Y-m-d H:i:s"),
					'is_result'     	 	        =>	0,
					'category'     	 	        	=>	$iCategoryDetail['category'],
					'type'     	 	        		=>	$iCategoryDetail['type'],
					'ques_type'     	 	        =>	$QuesType,
					'is_color'     	 	        	=>	2,
					);                      
		$flgIn1 = $db->insertAry("com_mock_question_time",$aryData);
}

$iAlreadyMockTestType=$db->getRow("select * from com_mock_test_type where uniqueid='".$iThisRandomNumber."' and userid='".$_SESSION['userid']."' and mock_up_id='".$iMockDetail['id']."' and status='0' order by id desc");

if($iAlreadyMockTestType['id']=='')
{
$aryData=array(
				'uniqueid'						=>	$iThisRandomNumber,
				'userid'     	 	            =>	$_SESSION['userid'],
				'mock_up_id'     	 	        =>	$iMockDetail['id'],
				'type_id'     	 	        	=>	$iComMockType['id'],
				'type'     	 	        		=>	$iComMockType['type'],
				'create_at'     	 	        =>	date("Y-m-d H:i:s"),
				'status'     	 	        	=>	0,
				'finaltotalsecond'				=>	0,
				'last_ques_id'     	 	        =>	$iQuestionIdNowToTime,
				'end_time'     	 	        	=>	date("Y-m-d h:i:s"),
				);                      
	$flgIn12 = $db->insertAry("com_mock_test_type",$aryData);
}
?>

<script type="text/javascript">
function keypad(getval,getid) 
{
	var the_field = document.getElementById('calcfield'+getid).value;
	var the_value = the_field+getval;
	document.getElementById("calcfield"+getid).value = the_value;
	
	document.getElementById("useranswer"+getid).value=the_value;
}
function back(getid) 
{
    var value = document.getElementById("calcfield"+getid).value;
    document.getElementById("calcfield"+getid).value = value.substr(0, value.length - 1);
	
	document.getElementById("useranswer"+getid).value = value.substr(0, value.length - 1);
}
function clearall(getid) 
{ 
    document.getElementById("calcfield"+getid).value = '';
	
	document.getElementById("useranswer"+getid).value = '';
}
</script>
<script>
function submitform()
{
	var submit = confirm("Are you sure you want to submit this test?");
    if (submit == true) 
	{
		document.getElementById("submitmocktestform").submit();
	}
	else 
	{
       return false;
    }
}
</script>
<script>
function saveforleter(link)
{
	var save = confirm("Are you sure you want to save this test?");
    if (save == true) 
	{
        var myWindow;
	
		myWindow = window.open(link,'newwindow','width=1000,height=600');
		
		myWindow.close();   // Closes the new window
    } 
	else 
	{
       return false;
    }
}
</script>
<script>
function useranswer(qid,ansid)
{
	document.getElementById("useranswer"+qid).value=ansid;
}
</script>
<script>
function get_question_time(nqid,qid,getid,color)
{	
	var useranswer = document.getElementById("useranswer"+getid).value;
	var ques_type = document.getElementById("ques_type"+getid).value;

	$.ajax({
			type: 'post',
			url: '<?php echo SITE_URL;?>ajax.php',
			data: {
					action			:	"Action_calculatetime",
					nqid			:	nqid,
					qid				:	qid,
					useranswer		:	useranswer,
					ques_type		:	ques_type,
					color			:	color,
					randomid 		: 	'<?php echo $iThisRandomNumber; ?>',
 				},
		success: function( data ) {	
		
			$("#showTimedataNew").html(data);
			document.getElementById("last_ques_id").value=qid;
		}
	});
}
</script>
<script>
setInterval(function() { updateMockTime(); }, 5000);

function updateMockTime()
{	
	var ques_id = document.getElementById("last_ques_id").value;
	
	$.ajax({
			type: 'post',
			url: '<?php echo SITE_URL;?>ajax.php',
			data: {
					action			: "Action_updatemocktesttetime",
					uniqueid 		: '<?php echo $iThisRandomNumber; ?>',
					mockid 			: '<?php echo $iMockDetail['id']; ?>',
					ques_id 		: ques_id,
					},
			success: function( data ) { 
				//$("#showmsg").html(data);
			}
	});
}
</script>
<script>
function startTimer(duration, display) {
    var timer = duration, minutes, seconds;
    setInterval(function () {
        minutes = parseInt(timer / 60, 10)
        seconds = parseInt(timer % 60, 10);
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        display.textContent = minutes + " : " + seconds;
 		if(minutes+":"+seconds=='00:00')
		{
			document.getElementById("submitmocktestform").submit();
		}
        if (--timer < 0) {
            timer = duration;
        }
    }, 1000);
}
window.onload = function () {
    var fiveMinutes = <?php echo $iTimer; ?> * 60 * 1,
    display = document.querySelector('#time');
    startTimer(fiveMinutes, display);
};
</script>	
<script>
function getsubject(sid)
{
	var arrayOfElements=document.getElementsByClassName('activesubject');
	var lengthOfArray=arrayOfElements.length;

	for (var i=0; i<lengthOfArray;i++){
		arrayOfElements[i].style.display='none';
	} 
	document.getElementById("subject"+sid).style.display="block";
	//document.getElementById("subjectpannel"+sid).style.display="block";
	var ques = document.getElementById("firstques"+sid).value;
	
	var arrayOfElementsTwo=document.getElementsByClassName('activequestion'+sid);
	var lengthOfArrayTwo=arrayOfElementsTwo.length;

	for (var i=0; i<lengthOfArrayTwo;i++){
		arrayOfElementsTwo[i].style.display='none';
	}
	
	document.getElementById("question"+ques).style.display="block";
}
</script>				
<script>
function getquestion(sid,qid,nid,ntid)
{ 
	var arrayOfElementsTwo=document.getElementsByClassName('activequestion'+sid);
	var lengthOfArrayTwo=arrayOfElementsTwo.length;

	for (var i=0; i<lengthOfArrayTwo;i++){
		arrayOfElementsTwo[i].style.display='none';
	}
	
	document.getElementById("question"+nid).style.display="block";
	
	
	var arrayOfElements=document.getElementsByClassName('activesubject');
	var lengthOfArray=arrayOfElements.length;
	for (var i=0; i<lengthOfArray;i++){
		arrayOfElements[i].style.display='none';
	}
	document.getElementById("subject"+ntid).style.display="block";
}				
</script>
<script>
function getanswer(tid,aid)
{ 
	if(tid==1)
	{
		document.getElementById("answer"+aid).classList.add("givenanswer");
		document.getElementById("answer"+aid).classList.remove("notgivenanswer");
		document.getElementById("answer"+aid).classList.remove("markforreview");
		document.getElementById("answer"+aid).classList.remove("answeredandmarkforreview");
	}
	else if(tid==2)
	{
		if ( document.getElementById('answer'+aid).classList.contains('givenanswer') )
		{
			document.getElementById("answer"+aid).classList.add("givenanswer");
			document.getElementById("answer"+aid).classList.remove("notgivenanswer");
			document.getElementById("answer"+aid).classList.remove("markforreview");
			document.getElementById("answer"+aid).classList.remove("answeredandmarkforreview");
		}
		else if ( document.getElementById('answer'+aid).classList.contains('answeredandmarkforreview') )
		{
			document.getElementById("answer"+aid).classList.add("answeredandmarkforreview");
			document.getElementById("answer"+aid).classList.remove("givenanswer");
			document.getElementById("answer"+aid).classList.remove("notgivenanswer");
			document.getElementById("answer"+aid).classList.remove("markforreview");
		}
		else
		{
			document.getElementById("answer"+aid).classList.add("notgivenanswer");
			document.getElementById("answer"+aid).classList.remove("givenanswer");
			document.getElementById("answer"+aid).classList.remove("markforreview");
			document.getElementById("answer"+aid).classList.remove("answeredandmarkforreview");
		}
	}
	else if(tid==3)
	{
		if ( document.getElementById('answer'+aid).classList.contains('givenanswer') )
		{
			document.getElementById("answer"+aid).classList.add("answeredandmarkforreview");
			document.getElementById("answer"+aid).classList.remove("givenanswer");
			document.getElementById("answer"+aid).classList.remove("notgivenanswer");
			document.getElementById("answer"+aid).classList.remove("markforreview");
		}
		else
		{
			document.getElementById("answer"+aid).classList.add("markforreview");
			document.getElementById("answer"+aid).classList.remove("givenanswer");
			document.getElementById("answer"+aid).classList.remove("notgivenanswer");
			document.getElementById("answer"+aid).classList.remove("answeredandmarkforreview");
		}
	}
	else if(tid==4)
	{
		document.getElementById("answer"+aid).classList.add("notgivenanswer");
		document.getElementById("answer"+aid).classList.remove("givenanswer");
		document.getElementById("answer"+aid).classList.remove("markforreview");
		document.getElementById("answer"+aid).classList.remove("answeredandmarkforreview");
		
		var ele = document.getElementsByName('option'+aid);
	    for(var i=0;i<ele.length;i++)
		{
			ele[i].checked = false;
		}
		
		if(ele.length>0)
		{
			document.getElementById("useranswer"+aid).value='';
		}
		else
		{
			document.getElementById("calcfield"+aid).value='';
		}
		
		if ( document.getElementById('answer'+aid).classList.contains('givenanswer') )
		{
			document.getElementById("answer"+aid).classList.add("answeredandmarkforreview");
		}
	}
	else if(tid==5)
	{
		if ( document.getElementById('answer'+aid).classList.contains('givenanswer') )
		{
			document.getElementById("answer"+aid).classList.add("givenanswer");
			document.getElementById("answer"+aid).classList.remove("notgivenanswer");
			document.getElementById("answer"+aid).classList.remove("markforreview");
			document.getElementById("answer"+aid).classList.remove("answeredandmarkforreview");
		}
		else
		{
			document.getElementById("answer"+aid).classList.add("notgivenanswer");
			document.getElementById("answer"+aid).classList.remove("givenanswer");
			document.getElementById("answer"+aid).classList.remove("markforreview");
			document.getElementById("answer"+aid).classList.remove("answeredandmarkforreview");
		}
	}
}				
</script>
<script>
function openNav() {
	document.getElementById("cccc").style.display = "block";
	document.getElementById("mmm").style.display = "none"; 
	document.getElementById("mySidenav").style.width = "265px";
    document.getElementById("main").style.marginRight = "269px";
		document.getElementById("main4").style.marginRight = "247px";
	document.getElementById("main1").style.marginRight = "287px";
	document.getElementById("main2").style.marginRight = "287px";
	document.getElementById("main3").style.marginRight = "287px";
}

function closeNav() {
	document.getElementById("mmm").style.display = "block";
	document.getElementById("cccc").style.display = "none";
    document.getElementById("mySidenav").style.width = "0";
    document.getElementById("main").style.marginRight= "0";
	document.getElementById("main4").style.marginRight= "-14px";
	document.getElementById("main1").style.marginRight= "0";
	document.getElementById("main2").style.marginRight= "0";
	document.getElementById("main3").style.marginRight= "0";
}
</script>
	<!-- Main Body Area End Here -->
    <!-- jquery-->
	<script src="<?php echo SITE_URL; ?>js/jquery-2.2.4.min.js" type="text/javascript"></script>
	<!-- Bootstrap js -->
	<script src="<?php echo SITE_URL; ?>js/bootstrap.min.js" type="text/javascript"></script>
	<script src="<?php echo SITE_URL; ?>js/calc-main.js" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/mathjs/3.17.0/math.min.js"></script>
	 
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script>
	$( function() {
	$( "#draggable" ).draggable();

	} );
	</script> 
</body>
</html>