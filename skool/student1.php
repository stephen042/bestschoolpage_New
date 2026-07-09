<?php 

include('../config.php');
include('inc.session-create.php');

if(isset($_POST['importSubmit'])){
        
    // Allowed mime type
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 
    'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv',
     'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
    
    // Validate whether selected file is a CSV file
    if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $csvMimes)){
        
        // If the file is uploaded
        if(is_uploaded_file($_FILES['file']['tmp_name'])){
            
            // Open uploaded CSV file with read-only mode
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
            
            // Skip the first line
            fgetcsv($csvFile);
            
            // Parse data from CSV file line by line
            while(($line = fgetcsv($csvFile)) !== FALSE){
                // Get row data
                // $fname   = $line[0];
                // $email  = $line[1];
                // $pnumber  = $line[2];
                // $sstatus = $line[3];
                // print "<pre>";
                // print_r($line);
                // print "<pre>";
                $value = "'".implode("','", $line)."'";
                // echo $value;
                    // Insert member data in the database
                  $res=$db->query("INSERT INTO manage_student (`student_id`,`session`, 
            `term_id`,`class`,
            `last_name`, `date_of_admission`,`first_name`,`state_of_origin`, 
            `other_name`,`lga_of_origin`,`gender`,`religion`,`nationality`,`date_of_birth`,
            `number_of_sibling`,`percentage`,`order_of_birth`, 
            `boarding`,`address_1`,`address_2`,`city`,`state`,`p_o_box`,`email`,`phone`,`mobile`
            ) VALUES (".$value.")");
                //   echo $res;
                }
                 if(!$res){
                    header('Location: student.php?submit=error');
                }else{
                    header('Location: student.php?submit=success');
                    
                }
            }else{
                
            }
        }else{
            header('Location: student.php?submit=invalid file');
            exit();
        }
        // Close opened CSV file
        fclose($csvFile); 
    }


