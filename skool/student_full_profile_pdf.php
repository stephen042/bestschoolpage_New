<?php 
include('../config.php');
$iStudentBio=$db->getRow("select * from manage_student where randomid='".$_GET['randomid']."'");
$gurdianInfo=$db->getRow("select * from student_guardian where student_id='".$iStudentBio['id']."' and type=3");
$faterInfo=$db->getRow("select * from student_guardian where student_id='".$iStudentBio['id']."' and type=1");
$motherInfo=$db->getRow("select * from student_guardian where student_id='".$iStudentBio['id']."' and type=2");
$MedicalInfo=$db->getRow("select * from student_medical_info where student_id='".$iStudentBio['id']."'");
$aryDetail=$db->getRow("select * from  local_government where id='".$iStudentBio['lga_of_origin']."'");
$sessionstu = $db->getRow("select * from  school_session where id='".$iStudentBio['session']."'");
$statename = $db->getRow("select * from  state where id='".$iStudentBio['state_of_origin']."' ");
$relig = $db->getRow("select * from religion where id='".$iStudentBio['religion']."'");

//student info
$firstname=$iStudentBio['first_name'];
$lastname=$iStudentBio['last_name'];
$othername=$iStudentBio['other_name'];
$gender=$iStudentBio['gender'];
$dob=$iStudentBio['date_of_birth'];
$sessionss=$sessionstu['session'];
$addmisson_sess=$iStudentBio['date_of_admission'];
$statenames=$statename['title'];
$lga=$aryDetail['title'];
$religion=$relig['title'];
$picture=$iStudentBio['picture'];
$Address=$iStudentBio['address_1'].','.$iStudentBio['address_2'];
$phone_no=$iStudentBio['phone']; 
$email=$iStudentBio['email'];

//gurdian info
$g_name=$gurdianInfo['first_name'].' '.$gurdianInfo['last_name'];
$g_address=$gurdianInfo['home_address_1'].','.$gurdianInfo['home_address_1'];
$g_phone=$gurdianInfo['phone'];
$g_email=$gurdianInfo['email'];
//
//fatherinfo
$f_name=$faterInfo['first_name'].' '.$faterInfo['last_name'];
$f_address=$faterInfo['home_address_1'].','.$faterInfo['home_address_1'];
$f_phone=$faterInfo['phone'];
$f_email=$faterInfo['email'];
$f_occ=$faterInfo['occupation'];
//

//mother
$m_name=$motherInfo['first_name'].' '.$motherInfo['last_name'];
$m_address=$motherInfo['home_address_1'].','.$motherInfo['home_address_1'];
$m_phone=$motherInfo['phone'];
$m_email=$motherInfo['email'];
$m_occ=$motherInfo['occupation'];
//
//medical info



//


$iupdatedetails = $db->getRow("select * from  school_register where id='".$_SESSION['userid']."'"); 
$stae = $db->getRow("select * from state where id='".$iupdatedetails['state']."'");
$skoolnm=$iupdatedetails['name']; 
$logo=$iupdatedetails['logo'];
$location=$iupdatedetails['location']; 
$statename=$stae['title']; 
$website=$iupdatedetails['website'];

$html ="<html>
<head>
<style>
 td {
    
  border-collapse: collapse;
}
</style>
</head>
<body>

<table style='width:100%;' cellspacing='0'>

<tr style='border: none;'>
<td>
<table style='width:100%;'>
<tr>
<td > 
  <div> 
    <img style='width:90px; height:90px;' src='../uploads/$logo'>
  </div>
</td>

<td colspan='2' style='font-family:sans-serif;'> 
       <p style='font-size: 21px; text-align:center; margin-top: 15px; margin-bottom: -5px;'>$skoolnm</p> 
       <p style='font-size: 14px; text-align:center; margin-bottom: -10px;'>Website: $website   </p>     
       <p style='font-size: 15px; text-align:center;'>$location,$statename</p>
	   <p style='font-size: 16px; text-align:center; font-weight:600;'>STUDENT INFORMATION</p>
</td>

<td> 
  <div> 
    <img style='width:90px; height:90px;' src='../uploads/$picture'>
  </div>
</td>
</tr>


<table style='width:100%;'>

  <tr>
<td style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;padding: 6px; font-weight: bold; border-bottom: 1px solid black;'>Basic Info

</td>
    <td colspan='2'  style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;padding: 6px;border-bottom: 1px solid black;'></td>

  </tr>
 


 <tr>
 <td style=' color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;padding: 6px;'><span style='font-weight: bold;'>First Name:</span><span>$firstname</span>
</td>
    <td colspan='2' style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;    padding: 6px;'>
<span style='    font-weight: bold;'>Last Name:</span><span>$lastname</span>
	
</td>
  
  </tr>
  <tr>
<td style=' color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;   padding: 6px;'>
<span style=' font-weight: bold;'>Other Names:</span> <span>$othername</span>
</td>
    <td colspan='2'style=' color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;   padding: 6px;'>
	<span style=' font-weight: bold;'>Dob:</span><span>$dob</span>
</td>
 
  </tr>
    <tr>
 <td style='color: black;
    padding:6px;
    font-size:17px;
    font-family:sans-serif;
    font-weight: 500 ;padding: 6px;'>
<span style='font-weight: bold;'>Session Info:</span><span></span>

</td>
    <td colspan='2' style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;  padding: 6px;'>
	
<span style='font-weight: bold;'>
Gender:
</span>
<span>$gender</span>
</td>
 
  </tr>
    <tr>
<td style=' color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 padding: 6px;border-bottom: 1px solid black;font-weight: bold;'>
</td>
    <td colspan='2' style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;padding: 6px;border-bottom: 1px solid black;'></td>

  </tr>
    <tr>
<td style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;    padding: 6px;'>
	<span style='    font-weight: bold;'>
Session of Admission:</span><span>$addmisson_sess</span>
</td>
    <td colspan='2' style=' color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;  padding: 6px;'>
</td>

  </tr>
    <tr>
<td style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;padding: 6px;border-bottom: 1px solid black;font-weight: bold;'>
Contact Info:


</td>
    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ; padding: 6px; border-bottom: 1px solid black;'></td>

  </tr>
    <tr>
<td style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;padding:6px;font-weight: bold;'>
Address:$Address



</td>
    <td colspan='2' style=' color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight:500 ;padding: 6px;'></td>

  </tr>
  <tr>
<td style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;  padding: 6px;   font-weight: bold;'>
Phone:$phone_no



</td>
    <td colspan='2' style=' color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;  padding: 6px;'></td>

  </tr>
 
</table>

<table style='width:100%;'>
 <tr>
<td style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500; padding: 6px;border-bottom: 1px solid black;font-weight: bold;'>
Other Info


</td>
    <td colspan='2' style=' color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;   padding: 6px;border-bottom: 1px solid black;'>
	</td>

	    <td colspan='2' style=' color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;   padding: 6px;border-bottom: 1px solid black;'></td>
  </tr>
    <tr>
<td style=' color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;  padding: 6px;'><span style='    font-weight: bold;'>
Religion: </span><span>$religion</span>


</td>
    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ; padding: 6px;'><span style='font-weight: bold;'> State:</span>
	
	
	<span  style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ; padding: 6px;'>$statenames</span>
	</td>
	<td>
		   <span style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;  font-weight: bold; padding:    6px;'>Lga:</span>
	   <span  style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500  padding: 6px;'>$lga</span>
	</td>

   

  </tr>


  
  <tr>
<td style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;  border-bottom: 1px solid black; padding: 6px;    font-weight: bold;'>
Guardian's Info:



</td>
    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500; border-bottom: 1px solid black; 6px;'></td>

	    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500; border-bottom: 1px solid black; 6px;'></td>
  </tr>
  <tr>
<td style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;   padding: 6px;    font-weight: bold;'>
Name:$g_name



</td>
    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500  padding: 6px;'></td>

	    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500  padding: 6px;'></td>
  </tr>
  <tr>
<td style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500    padding: 6px;    font-weight: bold;'>
Address:$g_address

</td>
    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500  padding: 6px;'></td>
	    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500  padding: 6px;'></td>

  </tr>
    <tr>
<td style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500    padding: 6px;   font-weight: bold;'>
Phone:$g_phone


</td>
    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500  padding: 6px;'></td>
	    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500  padding: 6px;'></td>

  </tr>
    <tr>
<td style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500    padding: 6px;   font-weight: bold;'>
Email:$g_email



</td>
    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500  padding: 6px;'></td>
	    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500  padding: 6px;'></td>

  </tr>
  
  </table>
  <table style='width:100%;'>
  
  <tr>
<td  style='color: black;

    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;border-bottom: 1px solid black;padding: 6px;font-weight: bold;'>
	Father's Info
	
	</td>
	
	<td colspan='2' style='color: black;

    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500;   border-bottom: 1px solid black;    padding: 6px;    font-weight: bold;   border-left: 1px solid;  '>Mother's Info
	
	</th>
	
	</tr>
	<tr>
	<td>
	<table style='width:100%;'><tr><td style='    font-weight: bold;'>Name:$f_name
</td></tr>
	<tr><td style='    font-weight: bold;'>Address:$f_address</td></tr>
	<tr><td style='    font-weight: bold;'>Phone:$f_phone
</td></tr>
	<tr><td style='    font-weight: bold;'>Email:$f_email</td></tr>
	<tr><td style='    font-weight: bold;'>Occupation:$f_occ </td></tr>
	</table>
	</td>
	
		<td colspan='2' style='    border-left: 1px solid;'>
	<table style='width:100%;'><tr><td style='    font-weight: bold;'>Name:$m_name
</td></tr>
	<tr><td style='    font-weight: bold;'>Address:$m_address</td></tr>
	<tr><td style='    font-weight: bold;'>Phone:$m_phone
</td></tr>
	<tr><td style='    font-weight: bold;'>Email:$m_email</td></tr>
	<tr><td style='    font-weight: bold;'>Occupation:$m_occ </td></tr>
	</table>

</td>
 
  </tr>
 </table>
<table style='width:100%;'>

<tr>
<td style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;     font-weight: bold;  padding: 6px;border-bottom: 1px solid black;'>
Medical Info


</td>
    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ; padding: 6px; border-bottom: 1px solid black;'></td>

  </tr>

 <tr>
<td style='color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500 ;     font-weight: bold;  padding: 6px;'>
Genotype: 



</td>
    <td colspan='2' style='  color: black;
    padding: 6px;
    font-size: 17px;
    font-family: sans-serif;
    font-weight: 500  padding: 6px;    font-weight: bold;'>Blood Group:</td>


  </tr> 
  
  </table>
 
 
 
 </table>

</body>
</html>
";

$filename = 'student_full_profile';

// include autoloader
require_once 'dompdf/autoload.inc.php';

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();

$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portraite');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream($filename);

?>