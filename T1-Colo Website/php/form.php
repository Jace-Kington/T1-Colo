<?php
$postData = $uploadedFile = $statusMsg = '';
$msgClass = 'errordiv';
if(isset($_POST['submit'])){

    $postData = $_POST;
    $email = $_POST['email'];
    $name = $_POST['name'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
     
    if(!empty($email) && !empty($name) && !empty($subject) && !empty($message)){
         
        if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
            $statusMsg = 'Please enter your valid email.';
        }else{
            $uploadStatus = 1;
             
            if(!empty($_FILES["attachment"]["name"])){
                 
                $targetDir = "uploads/";
                $fileName = basename($_FILES["attachment"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);
                 
                $allowTypes = array('pdf', 'doc', 'docx', 'jpg', 'png', 'jpeg');
                if(in_array($fileType, $allowTypes)){

                    if(move_uploaded_file($_FILES["attachment"]["tmp_name"], $targetFilePath)){
                        $uploadedFile = $targetFilePath;
                    }else{
                        $uploadStatus = 0;
                        $statusMsg = "Sorry, there was an error uploading your file.";
                    }
                }else{
                    $uploadStatus = 0;
                    $statusMsg = 'Sorry, only PDF, DOC, JPG, JPEG, & PNG files are allowed to upload.';
                }
            }
             
            if($uploadStatus == 1){
                 
                $toEmail = 'data@tecoda.com.au';
 
                $from = $email;
                $fromName = $name;
                 
                $emailSubject = 'Contact Request Submitted by '.$name;
                 
                $htmlContent = '<h2>Customer Contact Detail!</h2>
                    <p><b>Name:</b> '.$name.'</p>
                    <p><b>Email:</b> '.$email.'</p>
                    <p><b>Subject:</b> '.$subject.'</p>
                    <p><b>Message:</b> '.$message.'</p>';
                 
                $headers = "From:". $fromName." <".$from.">";
 
                if(!empty($uploadedFile) && file_exists($uploadedFile)){
                     
                    $semi_rand = md5(time()); 
                    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
                     
                    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 
                     
                    $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
                    "Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n"; 
                     
                    if(is_file($uploadedFile)){
                        $message .= "--{$mime_boundary}\n";
                        $fp =    @fopen($uploadedFile,"rb");
                        $data =  @fread($fp,filesize($uploadedFile));
                        @fclose($fp);
                        $data = chunk_split(base64_encode($data));
                        $message .= "Content-Type: application/octet-stream; name=\"".basename($uploadedFile)."\"\n" . 
                        "Content-Description: ".basename($uploadedFile)."\n" .
                        "Content-Disposition: attachment;\n" . " filename=\"".basename($uploadedFile)."\"; size=".filesize($uploadedFile).";\n" . 
                        "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
                    }
                     
                    $message .= "--{$mime_boundary}--";
                    $returnpath = "-f" . $email;
                     
                    $mail = mail($toEmail, $emailSubject, $message, $headers, $returnpath);
                     
                    @unlink($uploadedFile);
                }else{
                    $headers .= "\r\n". "MIME-Version: 1.0";
                    $headers .= "\r\n". "Content-type:text/html;charset=UTF-8";
                     
                    $mail = mail($toEmail, $emailSubject, $htmlContent, $headers); 
                }
                 
                if($mail){
                    $statusMsg = 'Your Message was submitted successfully!';
                    $msgClass = 'succdiv';
                     
                    $postData = '';
                }else{
                    $statusMsg = 'Your contact request submission failed, please try again.';
                }
            }
        }
    }else{
        $statusMsg = 'Please fill all the fields.';
    }
}