<?php
namespace Ezrun\Core;

class MailIt extends BaseCore {
    
	function SendIt($from, $from_name = '', $to = array(), $subject = '', $message, $attachment_file = '', $attachment_name = '', $attachment_mime = '')
	{
		//ini_set ("SMTP", "netavps");
 		//ini_set('sendmail_from', 'cloudfactory@azure.bg');
 		
		//$to = implode( ',', $to );
		
		if($attachment_file == '' && $attachment_name == '')
		{
			/* message */
			$message = '
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<title></title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			</head>
			<body>
			'.$message.'
			</body>
			</html>
			';
			
			//send mail
			
			//mail($to, "=?UTF-8?B?".base64_encode($subject)."?=", $message, $headers);
			//foreach($to as $key => $value)
			//{
				$headers = "";
				/* Content-type header */
				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=utf-8\r\n";
				$headers .= "Content-Transfer-Encoding: 8bit\r\n";
				
				/* additional headers */
				$headers .= "Date: ".date("D, d M Y H:i:s O")."\r\n";
				$headers .= "To: <".implode( '>,<', $to).">\r\n";
				$headers .= "From: ".(!empty($from_name) ? "'" . $from_name . "'" : "")." <".$from.">\r\n";
				//$headers .= "Reply-To: ".$from."\r\n";
				//$headers .= "Return-Path: ".$from."\r\n";
				$headers .= "Subject:=?UTF-8?B?".base64_encode($subject)."?=\r\n";
				
				$SMTPIN = fsockopen (SMTP_SERVER, SMTP_PORT, $errno, $errstr, 30);
				if($SMTPIN)
				{
					fputs($SMTPIN, "EHLO ".getenv('MyHost')."\r\n");
					$talk["hello"] = fgets ( $SMTPIN, 1024 );
					fputs($SMTPIN, "MAIL FROM: <".$from.">\r\n");
					$talk["From"] = fgets ( $SMTPIN, 1024 );
					
					foreach($to as $key => $value)
					{
						fputs($SMTPIN, "RCPT TO: <".$value.">\r\n");
						$talk["To"] = fgets ($SMTPIN, 1024);
					}
					
					fputs($SMTPIN, "DATA\r\n");
					$talk["data"] = fgets( $SMTPIN, 1024 );
					//fputs($SMTPIN, "To: <".$to.">\r\nFrom: <".$from.">\r\nSubject:=?UTF-8?B?".base64_encode($subject)."?=\r\n\r\n\r\n".$message."\r\n.\r\n");
					fputs($SMTPIN, $headers . "\r\n\r\n" . $message . "\r\n.\r\n");
					$talk["send"] = fgets($SMTPIN, 256);
					
					//CLOSE CONNECTION AND EXIT ...
					fputs($SMTPIN, "QUIT\r\n");
					fclose($SMTPIN);
				}
			//}
		}
		else
		{
			/* message */
			$message = '
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<title></title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			</head>
			<body>
			'.$message.'
			</body>
			</html>
			';
			
			//process file
			$file = fopen( $attachment_file, "r" );
			$data = fread( $file, filesize( $attachment_file ) );
			fclose( $file );
			
			$data = chunk_split( base64_encode( $data ) );
			
			$semi_rand = md5(uniqid(time()));
			
			$headers = "";
			
			/* additional headers */
			$headers .= "Date: ".date("D, d M Y H:i:s O")."\r\n";
			$headers .= "To: <".implode( '>,<', $to).">\r\n";
			$headers .= "From: ".(!empty($from_name) ? "'" . $from_name . "'" : "")."<".$from.">\r\n";
			//$headers .= "Reply-To: ".$from."\n";
			//$headers .= "Return-Path: ".$from."\n";
			$headers .= "Subject:=?UTF-8?B?".base64_encode($subject)."?=\r\n";
			
			/* Content-type header */
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-Type: multipart/mixed; boundary=\"".$semi_rand."\"\r\n\r\n";
			$headers .= "--".$semi_rand."\r\n";
			$headers .= "Content-Type: text/html; charset=utf-8\r\n";
			$headers .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
			$headers .= $message . "\r\n\r\n";
			$headers .= "--" . $semi_rand . "\r\n";
			$headers .= "Content-Type: ".$attachment_mime."; name=\"".$attachment_name."\"\r\n";
			$headers .= "Content-Transfer-Encoding: base64\r\n";
			$headers .= "Content-Disposition: attachment; filename=\"".$attachment_name."\"\r\n\r\n";
			$headers .= $data . "\r\n\r\n";
			$headers .= "--" . $semi_rand . "--";
			
			//send mail
			//mail($to, "=?UTF-8?B?".base64_encode($subject)."?=", "", $headers);
			foreach($to as $key => $value)
			{
				$SMTPIN = fsockopen (SMTP_SERVER, SMTP_PORT, $errno, $errstr, 30);
				if($SMTPIN) 
				{
					fputs($SMTPIN, "EHLO ".getenv('MyHost')."\r\n"); 
					$talk["hello"] = fgets ( $SMTPIN, 1024 ); 
					fputs($SMTPIN, "MAIL FROM: <".$from.">\r\n"); 
					$talk["From"] = fgets ( $SMTPIN, 1024 ); 
					
					foreach($to as $key => $value)
					{
						fputs($SMTPIN, "RCPT TO: <".$value.">\r\n");
						$talk["To"] = fgets ($SMTPIN, 1024);
					}
					
					fputs($SMTPIN, "DATA\r\n");
					$talk["data"] = fgets( $SMTPIN, 1024 );
					//fputs($SMTPIN, "To: <".$to.">\r\nFrom: <".$from.">\r\nSubject:=?UTF-8?B?".base64_encode($subject)."?=\r\n\r\n\r\n".$message."\r\n.\r\n");
					fputs($SMTPIN, $headers . "\r\n.\r\n");
					$talk["send"] = fgets($SMTPIN, 256);
					//CLOSE CONNECTION AND EXIT ... 
					fputs($SMTPIN, "QUIT\r\n"); 
					fclose($SMTPIN);
				}
			}
		}
	}
	
	public function fixEmail($matches) {
		return preg_replace('/\./iu', '', $matches[1]) . '@' . $matches[2];
	}
}
