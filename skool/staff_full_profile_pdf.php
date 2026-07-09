<?php 
include('../config.php');
$staffDetail=$db->getRow("select* from staff_manage where randomid='".$_GET['randomid']."'");
$aryDetail=$db->getRow("select * from  local_government where id='".$staffDetail['lga_of_origin']."'");
$nationality=$db->getRow("select * from  nationality where id='".$staffDetail['nationality']."' ");
$religion=$db->getRow("select * from religion where id='".$staffDetail['religion']."'");

$firstname=$staffDetail['first_name'];
$lastname=$staffDetail['last_name'];
$othername=$staffDetail['other_name'];
$marrital_status=$staffDetail['marrital_status'];
$reliogn=$religion['title'];
$nationality=$nationality['country_name'];
$title=$staffDetail['title'];
$staffid=$staffDetail['staff_id'];
$gender=$staffDetail['gender'];
$dob=$staffDetail['date_of_birth'];
$state_of_origin=$staffDetail['state_of_origin'];
$lga=$aryDetail['title'];
$doa=$staffDetail['date_of_appointment'];
$address=$staffDetail['address_1'].','.$staffDetail['address_2'];
$phone=$staffDetail['phone'];
$email= $staffDetail['email'];
$bloodgroup=$staffDetail['blood_group'];
$pic=$staffDetail['picture'];
$branch=$staffDetail['branch'];
$denomination=$staffDetail['denomination'];
$genotype=$staffDetail['genotype'];

$iupdatedetails = $db->getRow("select * from  school_register where id='".$_SESSION['userid']."'"); 
$stae = $db->getRow("select * from state where id='".$iupdatedetails['state']."'");
$skoolnm=$iupdatedetails['name']; 
$logo=$iupdatedetails['logo'];
$location=$iupdatedetails['location']; 
$statename=$stae['title']; 
$website=$iupdatedetails['website'];

//next of kin
$nextDetail=$db->getRow("select * from staff_manage_kin_details where staff_manage_id='".$staffDetail['id']."'");

$kinfname=$nextDetail['first_name'];
$kinlast=$nextDetail['last_name'];
$kinothername=$nextDetail['other_name'];
$kinRelation=$nextDetail['relationship'];
$kinemail=$nextDetail['email'];
$kinphone=$nextDetail['phone'];
$kinaddress=$nextDetail['address_1'].','.$nextDetail['address_2'];
//refree details
$refreeDetail=$db->getRow("select * from staff_refree where staff_manage_id='".$staffDetail['id']."'"); 
$ref_name=$refreeDetail['name']; 
$ref_occ=$refreeDetail['occupation'];
$ref_home_add=$refreeDetail['home_address'];
$ref_office=$refreeDetail['office_address'];
$ref_phone=$refreeDetail['phone'];
if($refreeDetail['any_aligment']=='1') {  $ref_aligment="Yes";  } else { $ref_aligment="No"; }
$html ="
<html>
<head><title> table</title>
</head>
<body>
<table style='width:100%; font-size:15px;'>
<tr>
<td colspan='2'> 
  <div> 
    <img style='width:90px; height:90px;' src='../uploads/$logo'>
  </div>
</td>

<td colspan='3'> 
       <p style='font-size: 17px; text-align:center; margin-top: 15px;'>$skoolnm</p> 
       <p style='font-size: 13px; text-align:center; margin-bottom: -10px;'>Website:$website  </p>     
       <p style='font-size: 10px; text-align:center;'>$location,$statename</p>
	   <p style='font-size: 19px; text-align:center;'>STAFF INFORMATION</p>
</td>

<td colspan='2'>             
<div class='box'> 
  <img style='width:90px; height:90px;' src='../uploads/$pic'>
</div>
</td>
</tr>

</table>

<table style='width:100%;  font-family:sans-serif;'>

<tr>
<td  colspan='3'>
<h4 style='border-bottom:3px solid black; margin-bottom: 2px;'>BASIC INFO</h4>
</td>
</tr>



<tr> 
<td style='width:35%;'>STAFF ID: $staffid <br> FIRST NAME:$firstname <br> MARITAL STATUS:$marrital_status <br> NATIONALITY:$nationality <br> RELIGION: $reliogn</td>
<td style='width:32%;'>TITLE:$title <br> LAST NAME: $lastname <br> GENDER:$gender<br>STATE:$state_of_origin<br>DENOMINATION:$denomination</td>
<td style='width:33%;'>DATE OF APPOINTMENT:$doa<br>OTHER NAMES:$othername<br>DOB:$dob<br>LGA:$lga</br>BRANCH:$branch</td>
</tr>

<tr>
<td  colspan='3'>
<h4 style='border-bottom:3px solid black; margin-bottom: 2px;'>MEDICAL INFO</h4>
</td>
</tr>

<tr>
<td>BLOODGROUP:$bloodgroup</td>
<td>GENOTYPE:$genotype</td>
</tr>


<tr>
<td  colspan='3'>
<h4 style='border-bottom:3px solid black; margin-bottom: 2px;'>CONTACT INFO</h4>
</td>
</tr>

<tr>
<td>ADDRESS:$address</td><td style='text-align:center;'>PHONE:$phone</td></tr>
<tr><td>EMAIL:$email</td>
</tr>


<tr>
<td  colspan='3'>
<h4 style='border-bottom:3px solid black; margin-bottom: 2px;'>NEXT OF KIN INFO
</h4>
</td>
</tr>

<tr><td colspan=''>FIRST NAME:$kinfname</td><td>LAST NAME:$kinlast</td><td>OTHER NAME:$kinothername</td></tr>
<tr><td colspan=''>RELATIONSHIP:$kinRelation</td><td>EMAIL:$kinemail</td><td>PHONE:$kinphone</td></tr>
<tr><td colspan=''>ADDRESS:$kinaddress</td></tr>



<tr>
<td  colspan='3'>
<h4 style='border-bottom:3px solid black; margin-bottom: 2px;'>REFEREE INFO</h4>
</td>
</tr>


<tr><td colspan=''>NAME:$ref_name</td><td style='text-align:center'>OCCUPATION:$ref_occ</td></tr>
<tr><td colspan=''>HOME ADDRESS:$ref_home_add</td></tr>
<tr><td colspan=''>OFFICE ADDRESS:$ref_office</td></tr>
<tr><td colspan=''>PHONE:$ref_phone</td><td style='text-align:center'>DOES EMPLOYEE HAVE ANY KNOWN AILMENT?</td><td> $ref_aligment</td></tr>
</table>

</body>
</html>";

$filename = 'staff_full_profile';

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