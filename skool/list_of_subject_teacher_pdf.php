<?php
namespace Dompdf;
require_once ('dompdf_New/autoload.inc.php');
ob_start();
include('../config.php');
include('inc.session-create.php');

$iupdatedetails = $db->getRow("select * from  school_register where id='".$_SESSION['userid']."'"); 
$stae = $db->getRow("select * from state where id='".$iupdatedetails['state']."'");

$statename=$stae['title'];

$seession=$db->getRow("select * from school_session where create_by_userid='".$create_by_userid."' and id='".$_GET['pdf_session']."'");
?>

<html>
<head>
<title>
</title>
</head>
<body>
<table cellspacing="0;">
<table style='width:100%;'>
<tr style="border: none;">
<th>
<img src="../uploads/<?php echo $iupdatedetails['logo'];?>"style="width: 100px;">
</th>
<th><p style=" font-weight: 500; font-size: 19px;font-family: sans-serif;"><?php echo $iupdatedetails['name']; ?></p>
<p style="padding-top:0px;font-size: 15px;font-weight: 500;  font-family: sans-serif;  margin-bottom: 0;">Website:<?php echo $iupdatedetails['website']; ?></p>
<p style="margin-top: 0;font-size: 15px;font-weight: 500;font-family: sans-serif; "><?php echo $iupdatedetails['location']; ?>, <?php echo $statename; ?>.</p>
<p style="font-size: 19px;padding-bottom: 14px;font-family: sans-serif;font-weight: 500;">LIST OF SUBJECT TEACHERS
</p>
<p>
<span>Session:<?php echo $seession['session'];?></span>

</p>
</th>
</tr>

</table>
  </div>
</td>
</tr>

</table>

<?php 
$i=0;
$aryList=$db->getRows("select * from school_class where create_by_userid='".$create_by_userid."' ");

foreach($aryList as $iList)
{	

?>

<table cellspacing="0"    width="100%">
<tr>
<td>
</td>
<td>
</td>
<td style="  font-family: roboto;   padding-bottom: 8px; "><span style=" border-bottom:1px solid gray; "><?php echo $iList['name']; ?></span>

</td>

</tr>

<tr>
<td style="border: 1px solid gray;height: 30px;    width: 10%;font-family: roboto;font-size: 14px;"><b>S/No</b>
</td>

<td style="border: 1px solid gray;height: 30px; width: 30%;font-family: roboto;font-size: 14px;"><b>Subject</b>
</td>
<td colspan="2" style="border:1px solid gray; height: 30px;font-family: roboto;width: 60%;font-size: 14px;">
<b>Name</b>
</td>
</tr>
<?php 
$i=0;
//$aaa=$db->getRows("select * from subject_teacher where  school_class='".$iList['id']."' and school_session='".$_GET['pdf_session']."' and create_by_userid='".$create_by_userid."'");
$aaa=$db->getRows("select * from subject_teacher where  school_class='".$iList['id']."'  and create_by_userid='".$create_by_userid."'");
foreach($aaa as $vv)
{	
$i=$i+1;
?>
<tr>
<td style="    border: 1px solid gray;
  
       height: 30px;    width: 10%;
    font-family: roboto;
    font-size: 14px;"><?php echo $i; ?>
</td>

<td style="    border: 1px solid gray;
    
       height: 30px;    width: 30%;
    font-family: roboto;
    font-size: 14px;"><?php echo $db->getVal("select subject from school_subject where id='".$vv['school_subject']."'"); ?>
</td>
<td colspan="2" style="border:1px solid gray;     height: 30px;
    font-family: roboto;    width: 60%;
    font-size: 14px;">

<?php echo $db->getVal("select first_name  from staff_manage where id='".$vv['staff_id']."'"); ?>
</td>
</tr>
<?php } ?>

</table>

<br>

<?php } ?>

</table>
</body>
</html>

<?php
$html = ob_get_clean();

$dompdf = new Dompdf();

$dompdf->setPaper('A4', 'portrait');
$dompdf->load_html($html);
$dompdf->render();
//For view
//$dompdf->stream("",array("Attachment" => false));
// for download
$dompdf->stream("list_of_subject_teacher");
?>