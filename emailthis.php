<?php
/*
Plugin Name: EmailThis
Plugin URI: http://emailthis.seoblog.cz/
Description: Provides a way for visitors of your blog to send your posts via email to their friends.
Author: Vaclav Papousek
Version: 1.0
Author URI: http://emailthis.seoblog.cz/
*/

//******************************************************************************

function special_letters($text)
  {
  // for multi-byte (f.e. UTF-8)
  $table = Array(
  'ä'=>'a',
  'Ä'=>'A',
  'á'=>'a',
  'Á'=>'A',
  'č'=>'c',
  'Č'=>'C',
  'ć'=>'c',
  'Ć'=>'C',
  'ď'=>'d',
  'Ď'=>'D',
  'ě'=>'e',
  'Ě'=>'E',
  'é'=>'e',
  'É'=>'E',
  'ë'=>'e',
  'Ë'=>'E',
  'í'=>'i',
  'Í'=>'I',
  'ľ'=>'l',
  'Ľ'=>'L',
  'ń'=>'n',
  'Ń'=>'N',
  'ň'=>'n',
  'Ň'=>'N',
  'ó'=>'o',
  'Ó'=>'O',
  'ö'=>'o',
  'Ö'=>'O',
  'ř'=>'r',
  'Ř'=>'R',
  'ŕ'=>'r',
  'Ŕ'=>'R',
  'š'=>'s',
  'Š'=>'S',
  'ś'=>'s',
  'Ś'=>'S',
  'ť'=>'t',
  'Ť'=>'T',
  'ú'=>'u',
  'Ú'=>'U',
  'ü'=>'u',
  'Ü'=>'U',
  'ý'=>'y',
  'Ý'=>'Y',
  'ž'=>'z',
  'Ž'=>'Z',
  'ź'=>'z',
  'Ź'=>'Z'
  );

  return strtr($text, $table);
  } 

//******************************************************************************

function report_errors($error)
  {
  $size = sizeof($error);
  for ($i=0; $i < $size; $i++)
    {
    echo '<p style="font-size: 9px; color:#FF0000;">ERROR: '.$error[$i].'</p>';
    }
  }

//******************************************************************************

function email_form($id)
  {
  $name = $_POST['f_name'];
  $mail1 = $_POST['f_mail1'];
  $mail2 = $_POST['f_mail2'];
  $message = $_POST['f_message'];
  
  echo '<form action="" method="post" enctype="multipart/form-data">';  
   
  echo '<div style="text-align:left;"><p>Send a copy of the article <i><a href="'.get_permalink($id).'" target="_blank" title="'.get_the_title($id).'">'.get_the_title($id).'</a></i> to your friends.</p>';
  echo '<p><strong>Your name: </strong><br/><input type="text" name="f_name" size="20" value="'.$name.'"> <span style="font-size:9px;">(required)</span></p>';
  echo '<p><strong>Your e-mail:</strong><br/><input type="text" name="f_mail1" size="20" value="'.$mail1.'"> <span style="font-size:9px;">(required)</span></p>';
  echo '<p><strong>E-mails of your friends (separate with commas):</strong><br/><input type="text" name="f_mail2" size="60" value="'.$mail2.'"> <span style="font-size:9px;">(required)</span></p>';
  
  echo '<p><strong>Add a personal message:</strong><br/><textarea name="f_message" rows="8" cols="50">'.$message.'</textarea></p>';
  echo '<input type="hidden" name="form_sent" value="1">';
  echo '<p><input type="submit" value="Email this post" title="Email this post"></p></div>';
     
  echo '</form>';
  }

//******************************************************************************

function email_send($id)
  {
  if(isset($_POST['form_sent']))//if the button was pressed
    {
    unset($error); //errasing error variable
  
    //**************finding errors in form*******************  
    if(strlen($_POST['f_name']) <= 0) {$error[] = "Your name is required.";}  
    if(!eregi("^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,6}$", stripslashes(trim($_POST['f_mail1'])))) {$error[] = "Your e-mail address is not valid.";}
    if(strlen($_POST['f_mail2']) <= 5) {$error[] = "You have to enter at least one e-mail where the post should be send.";}
  
    //************if there is an error**********
    if(sizeof($error) > 0)
      {
      report_errors($error);
      email_form($id);                   
      }
    //**********if there is no error, we can send email******  
    else
      {
      //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++      
      $name = substr($_POST['f_name'], 0, 30);
      
      
      $name = special_letters($name);
      
      $from = $_POST['f_mail1'];
      $mail2 = str_replace(' ', '', $_POST['f_mail2']);
      
      $to = explode(",", $mail2);
      $message = htmlspecialchars(substr($_POST['f_message'], 0, 1000));
            
      //control of validity of the email adress
      
      $mail_body = '<html><head><title>'.get_the_title($id).'</title></head><body><p>From server: 
        <a href="'.get_bloginfo('url').'">'.get_bloginfo('name').'</a><br/>URL: <a href="'.get_permalink($id).'">'.get_permalink($id).'</a><br/>From: 
        '.$name.' (<a href="mailto:'.$from.'">'.$from.'</a>)<br/>Comments: '.$message.'</p></body></html>';
      $subject = $name.' wants us to send you a post from '.get_bloginfo('name');
      
      $i = 0;
      $header .= "Bcc: ";
      while($i < count($to))
        {        
        if(eregi("^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,6}$", stripslashes(trim($to[$i]))))
          {
          $header .= $to[$i];
          if(($i+1) < count($to)) $header .= ", ";
          }
        $i++;
        }
      //erasing ', ' from the end of the header
      $header = preg_replace("/\, $/", "", $header);
      $header .= "\r\n";      
      $header .= "MIME-Version: 1.0\r\n";
      $header .= "Content-Type: text/html; charset=utf-8\r\n";
      $header .= "Content-Transfer-Encoding: 8bit\r\n";
      $header .= "From: ". $name . " <" . $from . ">\r\n";
      
      $main_to = "";
      //$main_to = bloginfo('admin_email');
      
      mail($main_to, $subject, $mail_body, $header);    
        
      //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      
      echo '<p>Done. Thank you for spreading the word.</p>';
      }
    }
  else
   {
   email_form($id);
   }
  }

//******************************************************************************

function email_this($data)
  {
  global $post;
  if(isset($_GET['id']))//if it is a form
    {
    email_send(htmlspecialchars($_GET['id']));    
    }
  else//if the button should show
    {
    $data_and_emailthis = $data.'<br/><p><a href="/email/?id='.get_the_ID().'" rel="nofollow" title="Email this post to your friend" style="font-weight: bold;"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/emailthis/email.gif" style="border: 0px; padding: 0px; margin: 0px;" alt="Email this post"> Email this post</a></p>';    
    }
  
  //return
  return $data_and_emailthis;
  }

add_filter('the_content', 'email_this');

?>
