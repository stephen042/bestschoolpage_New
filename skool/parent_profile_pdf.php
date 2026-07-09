<?php 
include('../config.php');
$iParents=$db->getRow("select * from student_guardian where randomid='".$_GET['randomid']."'");
$iupdatedetails = $db->getRow("select * from  school_register where id='".$_SESSION['userid']."'"); 
$stae = $db->getRow("select * from state where id='".$iupdatedetails['state']."'"); 

//skool information
$skoolnm=$iupdatedetails['name']; 
$website=$iupdatedetails['website'];
$logo=$iupdatedetails['logo'];
$location=$iupdatedetails['location'];
$statename=$stae['title']; 
//

// parent information
$p_title=$iParents['title'];
$p_id=$iParents['parent_id']; 
$p_lastname=$iParents['last_name'];
$p_fname=$iParents['first_name'];
$p_oname=$iParents['other_name'];
$p_email= $iParents['email']; 
$p_phone=$iParents['phone'];
$p_add1=$iParents['home_address_1'];
$p_add2=$iParents['home_address_2'];

$html ="
<html>
<head><title>Parent Profile</title>
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
	   <p style='font-size: 16px; text-align:center; font-weight:600;'>PARENT INFORMATION</p>
</td>

<td> 
  <div class='box' style='width:90px; height:110px; border:2px solid white; background-color:white;'> 
   </div>
</td>
</tr>

</table>

<table border='1' width='100%' style='border-collapse: collapse;'>
        <tr>
            <td style='width:30%'>Title</td>
            <td style='width:70%'>$p_title </td>
        </tr> 
		<tr>
            <td >Parent ID </td>
            <td > $p_id</td>
        </tr>
		
		<tr>
            <td >First Name </td>
            <td >$p_fname </td>
        </tr>
		
		<tr>
            <td >Last Name </td>
            <td >$p_lastname </td>
        </tr>
		
		<tr>
            <td >Other Names</td>
            <td >$p_oname </td>
        </tr>
		
		<tr>
            <td >Phone Number</td>
            <td > $p_phone</td>
        </tr>
		
		<tr>
            <td >Email</td>
            <td > $p_email</td>
        </tr>
		
		<tr>
            <td >Address 1 </td>
            <td > $p_add1</td>
        </tr>
		
		<tr>
            <td >Address 2</td>
            <td > $p_add2</td>
        </tr>
		
		
       
        </table>


</body>
</html>";

$filename = 'parent_profile';

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