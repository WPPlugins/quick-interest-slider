<?php

function qis_display_form( $values, $errors, $registered ) {
	$register = qis_get_stored_register();
    if (!$registered && count($errors) == 0) {
        $content = '<h2 class="toggle"><a href="#">' . $register['title'] . '</a></h2>
        <div class="apply" style="display: none;">';
    }
    $content .= '<div class="applicationform">';
    if ($registered) {
        if (!empty($register['replytitle'])) {
            $register['replytitle'] = '<h2>' . $register['replytitle'] . '</h2>';
        }
        if (!empty($register['replyblurb'])) {
            $register['replyblurb'] = '<p>' . $register['replyblurb'] . '</p>';
        }
        $content .= $register['replytitle'].$register['replyblurb'];
    
    } else {
        if (!empty($register['blurb'])) {
            $register['blurb'] = '<p>' . $register['blurb'] . '</p>';
        }
        if (count($errors) > 0) {
            $content .= '<h2>' . $register['errortitle'] . '</h2>';
            $content .= "<p class='qis-error-message'>" . $register['error'] . "</p>\r\t";
            $arr = array('yourname','youremail','yourtelephone','yourmessage','youranswer');
            foreach ($arr as $item) if ($errors[$item] == 'error') {
                $errors[$item] = ' class="qis-error"';
            }
            if ($errors['youranswer']) $errors['youranswer'] = 'border:1px solid red;';
        } else {
            $content .= $register['blurb'];
        }
		
        foreach (explode( ',',$register['sort']) as $name) {
            switch ( $name ) {
                case 'field1':
                if ($register['usename'])
                    $content .= '<input id="yourname" name="yourname" '.$errors['yourname'].' type="text" value="'.$values['yourname'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yourname'].'\';}" onfocus="if (this.value == \''.$values['yourname'].'\') {this.value = \'\';}" />'."\n";
                break;
                case 'field2':
                if ($register['usemail']) 
                    $content .= '<input id="email" name="youremail"'.$errors['youremail'].' type="text" value="'.$values['youremail'].'" onblur="if (this.value == \'\') {this.value = \''.$values['youremail'].'\';}" onfocus="if (this.value == \''.$values['youremail'].'\') {this.value = \'\';}" />';
                break;
                case 'field3':
                if ($register['usetelephone']) 
                    $content .= '<input id="email" name="yourtelephone"'.$errors['yourtelephone'].' type="text" value="'.$values['yourtelephone'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yourtelephone'].'\';}" onfocus="if (this.value == \''.$values['yourtelephone'].'\') {this.value = \'\';}" />';
                break;
                case 'field4':
                if ($register['usemessage']) 
                    $content .= '<textarea rows="4" label="message" name="yourmessage"'.$errors['yourmessage'].' onblur="if (this.value == \'\') {this.value = \''.$values['yourmessage'].'\';}" onfocus="if (this.value == \''.$values['yourmessage'].'\') {this.value = \'\';}" />' . stripslashes($values['yourmessage']) . '</textarea>';
                break;
                case 'field5':
                if ($register['usecaptcha']) 
                    $content .= '<span>'.$values['thesum'].' = </span><input id="youranswer" name="youranswer" type="text" style="width:3em;'.$errors['youranswer'].'"  value="'.$values['youranswer'].'" onblur="if (this.value == \'\') {this.value = \''.$values['youranswer'].'\';}" onfocus="if (this.value == \''.$values['youranswer'].'\') {this.value = \'\';}" /><input type="hidden" name="answer" value="' . strip_tags($values['answer']) . '" />
<input type="hidden" name="thesum" value="' . strip_tags($values['thesum']) . '" />';
                break;
                case 'field6':
                if ($register['usecopy']) {
                    if ($register['copychecked']) $copychecked = 'checked';
                    $content .= '<p><input type="checkbox" name="qis-copy" value="checked" '.$values['qis-copy'].' '.$copychecked.' /> '.$register['copyblurb'].'</p>';
                }
                break;
                case 'field7';
                if ($register['useaddinfo'])
                    $content .= '<p>'.$register['addinfo'].'</p>';
                break;
                }
            }
        if ($register['useterms']) {
            if ($errors['terms']) {
                $termstyle = ' style="border:1px solid red;"';
                $termslink = ' style="color:red;"';
            }
            if ($register['termstarget']) $target = ' target="_blank"';
            $content .= '<p><input type="checkbox" name="terms" value="checked" '.$termstyle.$values['terms'].' /> <a href="'.$register['termsurl'].'"'.$target.$termslink.'>'.$register['termslabel'].'</a></p>';
        }   
           
        $content .= '<input type="submit" value="'.$register['qissubmit'].'" class="submit" name="qissubmit" />
        </div>';
        if (!$registered && count($errors) == 0) {
            $content .= '</div>';
        }
    }
    return $content;
}

function qis_verify_form(&$values, &$errors) {
    $register = qis_get_stored_register();
    if ($register['usemail'] && !filter_var($values['youremail'], FILTER_VALIDATE_EMAIL))
        $errors['youremail'] = 'error';
    
    $values['yourname'] = filter_var($values['yourname'], FILTER_SANITIZE_STRING);
    if ($register['usename'] && (empty($values['yourname']) || $values['yourname'] == $register['yourname']))
        $errors['yourname'] = 'error';
    
    $values['youremail'] = filter_var($values['youremail'], FILTER_SANITIZE_STRING);
    if ($register['usemail'] && (empty($values['youremail']) || $values['youremail'] == $register['youremail']))
        $errors['youremail'] = 'error';
    
    $values['yourtelephone'] = filter_var($values['yourtelephone'], FILTER_SANITIZE_STRING);
    if ($register['usetelephone'] && (empty($values['yourtelephone']) || $values['yourtelephone'] == $register['yourtelephone'])) 
        $errors['yourtelephone'] = 'error';
        
    $values['yourmessage'] = filter_var($values['yourmessage'], FILTER_SANITIZE_STRING);
    if ($register['usemessage'] && (empty($values['yourmessage']) || $values['yourmessage'] == $register['yourmessage'])) 
        $errors['yourmessage'] = 'error';
        
    if ($register['useterms'] && (empty($values['terms']))) 
        $errors['terms'] = 'error';

    if ($register['usecaptcha'] && (empty($values['youranswer']) || $values['youranswer'] <> $values['answer'])) 
        $errors['youranswer'] = 'error';
    $values['youranswer'] = filter_var($values['youranswer'], FILTER_SANITIZE_STRING);
    return (count($errors) == 0);	
}

function qis_process_form($values) {
    global $post;
    $content='';
    $register = qis_get_stored_register();
    $settings = qis_get_stored_settings();
    $auto = qis_get_stored_autoresponder();
    $qis_messages = get_option('qis_messages');
    if(!is_array($qis_messages)) $qis_messages = array();
    $sentdate = date_i18n('d M Y');
    
    $values['loan-amount'] = $settings['currency'].$values['loan-amount']; 
    $values['loan-period'] = $values['loan-period'].' '.$settings['period']; 
    
    $newmessage = array();
    $arr = array(
        'yourname',
        'youremail',
        'yourtelephone',
        'yourmessage',
        'loan-amount',
        'loan-period',
    );
    
    foreach ($arr as $item) {
        if ($values[$item] != $register[$item]) $newmessage[$item] = $values[$item];
    }
    
    $newmessage['sentdate'] = $sentdate;
    $qis_messages[] = $newmessage;
    update_option('qis_messages',$qis_messages);
    if (empty($register['sendemail'])) {
        $qis_email = get_bloginfo('admin_email');
    } else {
        $qis_email = $register['sendemail'];
    }
    
    $subject = $auto['subject'];
    if (empty($subject)) $subject = 'New Loan Application';
    $notificationsubject = 'New Loan Application from '.$values['yourname'].' on '.$sentdate;
    $content = qis_build_event_message($values,$register);
    
    if (!$register['nonotifications']) {
        $headers = "From: ".$values['yourname']." <".$values['youremail'].">\r\n"
    . "MIME-Version: 1.0\r\n"
    . "Content-Type: text/html; charset=\"utf-8\"\r\n";	
        $message = '<html>'.$content.'</html>';
        wp_mail($qis_email, $notificationsubject, $message, $headers);
    }
    if ($auto['enable'] || $values['qis-copy']) {
        qis_send_confirmation ($auto,$values,$content,$register);
    }
}

function qis_send_confirmation ($auto,$values,$content,$register) {
    $date = date_i18n("d M Y");
    $subject = $auto['subject'];
    if (empty($subject)) $subject = 'Loan Application';
    
    if (!$auto['fromemail']) $auto['fromemail'] = get_bloginfo('admin_email');
    if (!$auto['fromname']) $auto['fromname'] = get_bloginfo('name');

    $msg = $auto['message'];
    $msg = str_replace('[name]', $values['yourname'], $msg);
    $msg = str_replace('[amount]', $values['loan-amount'], $msg);
    $msg = str_replace('[period]', $values['loan-period'], $msg);
    $msg = str_replace('[date]', $date, $msg);
    $copy .= '<html>' . $msg;
    if ($auto['useregistrationdetails'] || $values['qis-copy']) {
        if($auto['registrationdetailsblurb']) {
            $copy .= '<h2>'.$auto['registrationdetailsblurb'].'</h2>';
        }
        $copy .= qis_build_event_message($values,$register);
    }
    
    $message = $copy.'</html>';
    $headers = "From: ".$auto['fromname']." <{$auto['fromemail']}>\r\n"
. "MIME-Version: 1.0\r\n"
. "Content-Type: text/html; charset=\"utf-8\"\r\n";	
    wp_mail($values['youremail'], $subject, $message, $headers);
}

function qis_build_event_message($values,$register) {
    $settings = qis_get_stored_settings();
    $content = '';
    if ($register['usename']) $content .= '<p><b>' . $register['yourname'] . ': </b>' . strip_tags(stripslashes($values['yourname'])) . '</p>';
    if ($register['usemail']) $content .= '<p><b>' . $register['youremail'] . ': </b>' . strip_tags(stripslashes($values['youremail'])) . '</p>';
    if ($register['usetelephone']) $content .= '<p><b>' . $register['yourtelephone'] . ': </b>' . strip_tags(stripslashes($values['yourtelephone'])) . '</p>';
    $content .= '<p><b>' . $settings['borrowlabel'] . ': </b>' . strip_tags(stripslashes($values['loan-amount'])) . '</p>';
    if ($values['loan-period']) $content .= '<p><b>' . $settings['forlabel'] . ': </b>' . strip_tags(stripslashes($values['loan-period'])) . '</p>';
    if ($register['usemessage']) $content .= '<p><b>' . $register['yourmessage'] . ': </b>' . strip_tags(stripslashes($values['yourmessage'])) . '</p>';
    return $content;
}

function qis_messages() {
    if( isset( $_POST['qis_reset_message'])) {
        delete_option('qis_messages');
        qis_admin_notice('All applications have been deleted.');
    }
    
    if( isset($_POST['qis_delete_selected'])) {
        $event = $_POST["qis_download_form"];
        $message = get_option('qis_messages');
        for($i = 0; $i <= 1000; $i++) {
            if ($_POST[$i] == 'checked') {
                unset($message[$i]);
            }
        }
        $message = array_values($message);
        update_option('qis_messages', $message );
        qis_admin_notice('Selected applications have been deleted.');
    }

    if( isset($_POST['qis_emaillist'])) {
        $message = get_option('qis_messages');
        $content = qis_build_registration_table ($message,'report');
        $qis_email = get_bloginfo('admin_email');
        $headers = "From: {<{$qis_email}>\r\n"
. "MIME-Version: 1.0\r\n"
. "Content-Type: text/html; charset=\"utf-8\"\r\n";	
        wp_mail($qis_email, 'Loan Applications', $content, $headers);
        qis_admin_notice('Application list has been sent to '.$qis_email.'.');
    }

    $content=$current=$all='';
    $message = get_option('qis_messages');
    
    if(!is_array($message)) $message = array();
    $dashboard = '<div class="wrap">
    <h1>Loan Applications</h1>
    <div id="qis-widget">
    <form method="post" id="qis_download_form" action="">';
    $content = qis_build_registration_table ($message,'');
    if ($content) {
        $dashboard .= $content;
        $dashboard .='
        <input type="submit" name="qis_emaillist" class="button-primary" value="Email List" />
        <input type="submit" name="qis_reset_message" class="button-secondary" value="Delete All Registrants" onclick="return window.confirm( \'Are you sure you want to delete all the registrants for '.$title.'?\' );"/>
        <input type="submit" name="qis_delete_selected" class="button-secondary" value="Delete Selected" onclick="return window.confirm( \'Are you sure you want to delete the selected registrants?\' );"/>
        </form>';
    }
    else $dashboard .= '<p>There are Loan Applications</p>';
    $dashboard .= '</div></div>';		
    echo $dashboard;
}