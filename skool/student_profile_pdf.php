<?php 
include('../config.php');
$iStudentBio=$db->getRow("select * from manage_student where randomid='".$_GET['randomid']."'");
$aryDetail=$db->getRow("select * from  local_government where id='".$iStudentBio['lga_of_origin']."'");
$sessionstu = $db->getRow("select * from  school_session where id='".$iStudentBio['session']."'");
$statename = $db->getRow("select * from  state where id='".$iStudentBio['state_of_origin']."' ");
$relig = $db->getRow("select * from religion where id='".$iStudentBio['religion']."'");


$firstname=$iStudentBio['first_name'];
$lastname=$iStudentBio['last_name'];
$othername=$iStudentBio['other_name'];
$gender=$iStudentBio['gender'];
$dob=$iStudentBio['date_of_birth'];
$sessionss=$sessionstu['session'];
$statenames=$statename['title'];
$lga=$aryDetail['title'];
$religion=$relig['title'];
$picture=$iStudentBio['picture'];
$Address=$iStudentBio['address_1'].','.$iStudentBio['address_2'];
$phone_no=$iStudentBio['phone']; 
$email=$iStudentBio['email'];

$title=$staffDetail['title'];
$staffid=$staffDetail['staff_id'];


$iupdatedetails = $db->getRow("select * from  school_register where id='".$_SESSION['userid']."'"); 
$stae = $db->getRow("select * from state where id='".$iupdatedetails['state']."'");
$skoolnm=$iupdatedetails['name']; 
$logo=$iupdatedetails['logo'];
$location=$iupdatedetails['location']; 
$statename=$stae['title']; 
$website=$iupdatedetails['website'];

$html ="
<html>
<head><title>Staff Profile</title>
</head>
<body>
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

</table>

<table border='1' width='100%' style='border-collapse: collapse;'>
        <tr>
            <td style='width:30%'>First Name</td>
            <td style='width:70%'>$firstname </td>
        </tr> 
		<tr>
            <td >Last Name </td>
            <td > $lastname</td>
        </tr>
		
		<tr>
            <td >Other Names</td>
            <td >$othername </td>
        </tr>
		
		
		<tr>
            <td >Gender</td>
            <td > $gender</td>
        </tr>
		
		<tr>
            <td >Date of Birth</td>
            <td > $dob</td>
        </tr>
		
		<tr>
            <td >State of Origin</td>
            <td > $statenames</td>
        </tr>
		
		<tr>
            <td >Session of Admission </td>
            <td > $sessionss</td>
        </tr>
		
		<tr>
            <td >L. G. A. </td>
            <td > $lga</td>
        </tr>
		
		<tr>
            <td >Religion</td>
            <td >$religion </td>
        </tr>
		
		<tr>
            <td >Address</td>
            <td > $Address</td>
        </tr>
		
		<tr>
            <td >Phone</td>
            <td > $phone_no</td>
        </tr>
		<tr>
            <td >Email</td>
            <td >$email</td>
        </tr>
       
        </table>


</body>
</html>";

$filename = 'student_profile';

// include autoloader
require_once 'dompdf/autoload.inc.php';

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();

$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream($filename);

?>