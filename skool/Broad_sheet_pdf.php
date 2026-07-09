<?php

$html ="
<html>
<head>
  <title>BroadSheet</title>
  </head>
<body>
   <table style='width:100%;'>
<tbody>
<tr>
<td colspan='3' style='width:20%;'> 
 
    <img style='width:60%;' src='images/logo.jpg'>
  
</td>
<td> 
       <p style='font-size: 17px; text-align:center;font-family: roboto;margin: 8px;font-weight: 500;'>FLEXISAF ACADEMY</p> 
       <p style='font-size: 15px; text-align:center;font-family: roboto;margin: 0;    padding: 3px;'>Motto: MANAGING SCHOOLS THE SMARTER WAY   </p>     
       <p style='font-size: 17px; text-align:center;font-family: roboto;margin: 0;'>SUITE 1, TARABA HALL, INTERNATIONAL CONFERENCE CENTER, ABUJA</p>
	   <p style='font-size: 17px; text-align:center;font-family: roboto;margin: 8px'>ROLES REPORT</p>
	  
</td>


</tr>
 

</tbody>
</table>

  <table style='width:100%; text-align:center;'>
<tbody>
<tr>


<td>Class:A </td>
<td>Term:A </td>
<td>Session:A </td>


</tr>
</tbody>
</table>
	<table cellspacing='0'style='padding: 5px;    width: 100%;'>
 <tbody style='
    font-family: sans-serif;'>
  <tr style='height: 50px;'>
    <td style='  border: 1px solid #000000;'>Student Id</td>
    <td style='  border: 1px solid #000000;'>First Name</td>
    <td style='  border: 1px solid #000000;'>Last Name</td>
    <td style='  border: 1px solid #000000;'>LAST NAME</td>
    <td style='  border: 1px solid #000000;'>OTHER NAMES</td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER </td>
    <td style='  border: 1px solid #000000;'>OTHER</td>
  </tr>
  <tr style='height: 35px;'>
    <td style='  border: 1px solid #000000;'>1</td>
    <td style='  border: 1px solid #000000;'>0001</td>
    <td style='  border: 1px solid #000000;'>abdul</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>
    <td style='  border: 1px solid #000000;'>sakam</td>

  </tr>


  </tbody>
  </table>
</body></html>";

$filename = 'BroadSheet';

// include autoloader
require_once 'dompdf/autoload.inc.php';

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();

$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream($filename);

?>