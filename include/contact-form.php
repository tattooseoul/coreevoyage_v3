<?php

session_cache_limiter('nocache');
header('Expires: ' . gmdate('r', 0));
header('Content-type: application/json');

require_once('php-mailer/PHPMailerAutoload.php');
$mail = new PHPMailer();

// Enter your email address. If you need multiple email recipes simply add a comma: email@domain.com, email2@domain.com
$to = "contact@coreevoyage.com";

// Add your reCaptcha Secret key if you wish to activate google reCaptcha security
$recaptcha_secret_key = ''; 


// Form Fields
$name = isset($_POST["widget-contact-form-name"]) ? $_POST["widget-contact-form-name"] : null;
$email = $_POST["widget-contact-form-email"];
$phone = isset($_POST["widget-contact-form-phone"]) ? $_POST["widget-contact-form-phone"] : null;
$company = isset($_POST["widget-contact-form-company"]) ? $_POST["widget-contact-form-company"] : null;
$service = isset($_POST["widget-contact-form-service"]) ? $_POST["widget-contact-form-service"] : null;
$subject = isset($_POST["widget-contact-form-subject"]) ? $_POST["widget-contact-form-subject"] : 'New Message From Contact Form';
$message = isset($_POST["widget-contact-form-message"]) ? $_POST["widget-contact-form-message"] : null;

$recaptcha_response = isset($_POST["g-recaptcha-response"]) ? $_POST["g-recaptcha-response"] : null;


if( $_SERVER['REQUEST_METHOD'] == 'POST') {
	
    
 if($email != '') {
            
                //If you don't receive the email, enable and configure these parameters below: 
     
                //$mail->isSMTP();                                      // Set mailer to use SMTP
                //$mail->Host = 'mail.yourserver.com';                  // Specify main and backup SMTP servers, example: smtp1.example.com;smtp2.example.com
                //$mail->SMTPAuth = true;                               // Enable SMTP authentication
                //$mail->Username = 'SMTP username';                    // SMTP username
                //$mail->Password = 'SMTP password';                    // SMTP password
                //$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                //$mail->Port = 587;                                    // TCP port to connect to 
     
     	        $mail->IsHTML(true);                                    // Set email format to HTML
                $mail->CharSet = 'UTF-8';
     
                $mail->From = $email;
                $mail->FromName = $name;
     
                if(strpos($to, ',') !== false){
                    $email_addresses = explode(',', $to);
                    foreach($email_addresses as $email_address) {
                       $mail->AddAddress(trim($email_address));
                    }
                 }
                 else {
                     $mail->AddAddress($to);
                 }
							  
                $mail->AddReplyTo($email, $name);
                $mail->Subject = $subject;
                
                $name = isset($name) ? "Name: $name<br><br>" : '';
                $email = isset($email) ? "Email: $email<br><br>" : '';
                $phone = isset($phone) ? "Phone: $phone<br><br>" : '';
                $company = isset($company) ? "Company: $company<br><br>" : '';
                $service = isset($service) ? "Service: $service<br><br>" : '';
                $message = isset($message) ? "Message: $message<br><br>" : '';
                

                $mail->Body = $name . $email . $phone . $company . $service . $message . '<br><br><br>This email was sent from: ' . $_SERVER['HTTP_REFERER'];
     
	           // Check if google captch is present
                if(!empty($recaptcha_secret_key)) {
            
                    $ch = curl_init();

                    curl_setopt_array($ch,[CURLOPT_URL=>'https://www.google.com/recaptcha/api/siteverify',CURLOPT_POST =>true,CURLOPT_POSTFIELDS=>['secret'=> $recaptcha_secret_key,'response'=>$recaptcha_response,'remoteip'=>$_SERVER['REMOTE_ADDR']],CURLOPT_RETURNTRANSFER => true]);

                    $response = curl_exec($ch); 
                    curl_close($ch); 
                    $json = json_decode($response);

                    if ($json->success !== true ) {
                        $response = array ('response'=>'error', 'message'=> "Captcha is not Valid! Please Try Again.");
                    }else {
                       if(!$mail->Send()) {
                            $response = array ('response'=>'error', 'message'=> $mail->ErrorInfo);  
                        }else {
                            $response = array ('response'=>'success');  
                        } 
                    }
                }else {
                    if(!$mail->Send()) {
                        $response = array ('response'=>'error', 'message'=> $mail->ErrorInfo);  

                    }else {                  
                        $response = array ('response'=>'success');  
                    }
                }
     
            echo json_encode($response);
} else {
	$response = array ('response'=>'error');     
	echo json_encode($response);
}
    
}
?>



