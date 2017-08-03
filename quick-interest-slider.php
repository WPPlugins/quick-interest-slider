<?php
/*
Plugin Name: Quick Interest Slider
Plugin URI: http://loanpaymentplugin.com/
Description: Interest calculator with slider and multiple display options.
Version: 1.1
Author: aerin
Author URI: http://quick-plugins.com/
*/

require_once( plugin_dir_path( __FILE__ ) . '/quick-interest-register.php' );

add_shortcode('qis', 'qis_loop');
add_action('wp_enqueue_scripts', 'qis_scripts');
add_filter('plugin_action_links', 'qis_plugin_action_links', 10, 2 );
add_action('wp_head', 'qis_head_css');

if (is_admin()) require_once( plugin_dir_path( __FILE__ ) . '/settings.php' );

function qis_loop($atts) {
    global $post;
    if (!empty($_POST['qissubmit'])) {
        $formvalues = $_POST;
        $formerrors = array();
        if (!qis_verify_form($formvalues, $formerrors)) {
            return qis_display($atts,$formvalues, $formerrors,null);
        } else {
            qis_process_form($formvalues);
            return qis_display($atts,$formvalues, null,'checked');
        }
    } else {
        $values = qis_get_stored_register();
        $digit1 = mt_rand(1,10);
        $digit2 = mt_rand(1,10);
        if( $digit2 >= $digit1 ) {
            $values['thesum'] = "$digit1 + $digit2";
            $values['answer'] = $digit1 + $digit2;
        } else {
            $values['thesum'] = "$digit1 - $digit2";
            $values['answer'] = $digit1 - $digit2;
        }
        return qis_display($atts,$values ,null,null);
    }
}

function qis_display($atts,$formvalues,$formerrors,$registered) {
    $atts = shortcode_atts(array(
        'currency' => '',
        'ba' => 'before',
        'primary' => '',
        'secondary' => '',
        'loanmin' => '',
        'loanmax' => '',
        'loaninitial' => '',
        'loanstep' => '',
        'periodslider' => false,
        'periodmin' => '',
        'periodmax' => '',
        'periodinitial' => '',
        'periodstep' => '',
        'period' => '',
		'multiplier' => '',
        'trigger' => '',
        'outputinterestlabel' => '',
        'outputtotallabel' => '',
        'interestlabel' => '',
        'totallabel' => '',
        'primarylabel' => '',
        'secondarylabel' => '',
		'usebubble' => '',
        'outputlimits' => '',
        'outputtable' => '',
        'outputinterest' => '',
        'outputtotal' => '',
        'outputrepayments' => '',
        'application' => '',
        'repaymentlabel' => ''
    ),$atts,'qis');

	global $qis_count;
	$qis_count++;
    $style = qis_get_stored_style();
    $settings = qis_get_stored_settings();
    $register = qis_get_stored_register();
    $qppkey = get_option('qpp_key');

    
    foreach ($atts as $item => $key) {
        if ($key) {
            $settings[$item] = $key;
        }
    }

    $settings['repaymentlabel'] = preg_replace('/{(\w+)}/','[\1]',$settings['repaymentlabel']);
    
    if ($settings['ba'] == 'before') {
        $settings['cb'] = $settings['currency'];
        $settings['ca'] = ' ';
    }
    else {
        $settings['ca'] = $settings['currency'];
        $settings['cb'] = ' ';
    }
    
    if ($register['application']) $settings['application'] = true;
    if (!$formvalues['loan-amount']) $formvalues['loan-amount'] = $settings['loaninitial'];
    if (!$formvalues['loan-period']) $formvalues['loan-period'] = $settings['periodinitial'];
    if (!$formvalues['multiplier'] < 1) $formvalues['multiplier'] = 1;
    $settings['period'] = $settings['periodlabel'] ? $settings['periodlabel'] : $settings['period'];
	/*
		Normalize values
	*/
	$outputA = array();
	foreach ($settings as $k => $v) {
		$outputA[$k] = $v;
		
		if (strtolower($v) == 'checked') $outputA[$k] = true;
		
		if ($v == '') $outputA[$k] = false;
		
		if (preg_match('/[0-9.]+/',$v)) $outputA[$k] = (float) $v;
	}
    
	$output .= '<script type="text/javascript">';
	$output .= 'qis__rates["qis_'.$qis_count.'"] = '.json_encode($outputA).';';
	$output .= '</script><form action="" class="qis_form '.$style['border'].'" method="POST" id="qis_'.$qis_count.'">
	<input type="hidden" name="interesttype" value="'.$settings['interesttype'].'" />
	<div class="range qis-slider-principal">';
	
    
    if ($settings['outputlimits'])
        $output .= '<div class="qis-slideroutput">
        <span class="qis-sliderleft">'.$settings['cb'].qis_separator($settings['loanmin'],$outputA['separator']).$settings['ca'].'</span>
		<span class="qis-slidercenter"><output></output></span>
        <span class="qis-sliderright">'.$settings['cb'].qis_separator($settings['loanmax'],$outputA['separator']).$settings['ca'].'</span>
        </div>';
    
    $output .= '<input type="range" name="loan-amount" min="'.$settings['loanmin'].'" max="'.$settings['loanmax'].'" value="'.$formvalues['loan-amount'].'" step="'.$settings['loanstep'].'" data-qis>
    </div>';

    
    if ($settings['periodslider']) {
        $output .= '<div class="range qis-slider-term">';
        
        if ($settings['outputlimits'])
            $output .= '<div class="qis-slideroutput">
            <span class="qis-sliderleft">'.$settings['periodmin'].' '.$settings['period'].'</span>
			<span class="qis-slidercenter"><output></output></span>
            <span class="qis-sliderright">'.$settings['periodmax'].' '.$settings['period'].'</span>
            </div>';
    
        $output .= '<input type="range" name="loan-period" min="'.$settings['periodmin'].'" max="'.$settings['periodmax'].'" value="'.$formvalues['loan-period'].'" step="'.$settings['periodstep'].'" data-qis>
        </div>';
        }

    if ($settings['outputrepayments'] && $settings['periodslider']) {
        $settings['repaymentlabel'] = str_replace('[step]', $settings['periodstep'], $settings['repaymentlabel']);
        $settings['repaymentlabel'] = str_replace('[amount]', '<span class="repayment"></span>', $settings['repaymentlabel']);
		$settings['repaymentlabel'] = str_replace('[interest]', '<span class="interestrate"></span>', $settings['repaymentlabel']);
        $settings['repaymentlabel'] = str_replace('[total]', '<span class="final_total"></span>', $settings['repaymentlabel']);
        $output .= '<div class="qis-repayments">'.$settings['repaymentlabel'].'</div>';
    }
    
    if ($settings['outputinterest'])
        $output .= '<div class="qis-interest">'.$settings['outputinterestlabel'].':&nbsp;<span class="current_interest"></span></div>';
    
    if ($settings['outputtotal'])
        $output .= '<div class="qis-total">'.$settings['outputtotallabel'].':&nbsp;<span class="final_total"></span></div>';
    
    if ($settings['outputtable'])
        $output .= '<table class="qis-table">
        <tr>
        <th style="background:inherit"></th>
        <th>'.$settings['primarylabel'].'</th>
        <th>'.$settings['secondarylabel'].'</th>
        </tr>
        <tr>
        <td>'.$settings['interestlabel'].'</td>
        <td><span class="primary_interest"></span></td>
        <td><span class="secondary_interest"></span></td>
        </tr>
        <tr>
        <td>'.$settings['totallabel'].'</td>
        <td><span class="primary_total"></span></td>
        <td><span class="secondary_total"></span></td>
        </tr>
        </table>';
    if ($settings['application'] && $qppkey['authorised']) $output .= qis_display_form($formvalues,$formerrors,$registered);
    $output .= '</form>';
    return $output;
}

function qis_scripts() {
	wp_enqueue_style( 'qis_style',plugins_url('quick-interest-slider.css', __FILE__));
    wp_enqueue_script("jquery-effects-core");
	wp_enqueue_script('qis_script',plugins_url('quick-interest-slider.js', __FILE__ ), array( 'jquery' ), false, true );
}

function qis_plugin_action_links($links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$qis_links = '<a href="'.get_admin_url().'options-general.php?page=quick-interest-slider/settings.php">'.__('Settings').'</a>';
		array_unshift( $links, $qis_links );
		}
	return $links;
	}

function qis_generate_css() {
	$style = qis_get_stored_style();
    $background='';
    $handle = $style['slider-thickness'] + 1;
        
    if ($style['border']<>'none') {
        $border =".qis_form.".$style['border']." {border:".$style['form-border-thickness']."px solid ".$style['form-border-color'].";}";
    }
    if ($style['background'] == 'white') {
        $background = "form.qis_form {background:#FFF;}";
    }
    if ($style['background'] == 'color') {
        $background = ".qis_form {background:".$style['backgroundhex'].";}";
    }
    if ($style['backgroundimage']) {
        $background = ".qis_form {background: url('".$style['backgroundimage']."');}";
    }
        
    $formwidth = preg_split('#(?<=\d)(?=[a-z%])#i', $style['width']);
    if (!isset($formwidth[1])) $formwidth[1] = 'px';
    if ($style['widthtype'] == 'pixel') $width = $formwidth[0].$formwidth[1];
    else $width = '100%';
    
    $data = $border.
        $background.'
.qis_form {width:'.$width.';max-width:100%;color: '.$style['secondary-font-colour'].';}
.qis_form th {color:'.$style['primary-font-colour'].';}
.qis, .qis__fill {height: '.$style['slider-thickness'].'em;background: '.$style['slider-background'].';}
.qis__fill {background: '.$style['slider-revealed'].';}
.qis__handle {background: '.$style['handle-background'].';border: 1px solid '.$style['handle-border'].';width: '.$handle.'em;height: '.$handle.'em;position: absolute;top: -0.5em;-webkit-border-radius:'.$style['handle-colours'].'%;-moz-border-radius:'.$style['handle-corners'].'%;-ms-border-radius:'.$style['handle-corners'].'%;-o-border-radius:'.$style['handle-corners'].'%;border-radius:'.$style['handle-corners'].'%;}
.rangeslider__value-bubble { border-color: '.$style['primary-font-colour'].';}
.rangeslider__value-bubble::before { border-top-color: '.$style['primary-font-colour'].';}
.total {font-weight:bold;border-top:1px solid #FFF;margin-top:6px;text-align:left;font-size:'.$handle.'em;}
.qis, .qis__fill {font-size:1em;width: 100%;}
.loanoutput{text-align:left;margin-bottom:6px;}
.qis-slideroutput {margin-top:0.7em;color:'.$style['output-colour'].'}
.qis-slidercenter {color:'.$style['slideroutputcolour'].';font-size:'.$style['slideroutputfont'].'em;}
.qis-sliderleft, .qis-sliderright {color:'.$style['toplinecolour'].';font-size:'.$style['toplinefont'].'em;}
.qis-interest, .qis-repayments {color:'.$style['interestcolour'].';font-size:'.$style['interestfont'].'em;}
.qis-total {color:'.$style['totalcolour'].';font-size:'.$style['totalfont'].'em;}
.qis-table th {color:'.$style['headercolour'].';background:'.$style['headerbackground'].';font-size:'.$style['headerfont'].'em;}
.qis-table td {color:'.$style['rowcolour'].';background:'.$style['rowbackground'].';font-size:'.$style['rowfont'].'em;}
.qis-table td .primary_interest, .qis-table td .secondary_interest, .qis-table td .primary_total, .qis-table td .secondary_total {color:'.$style['outputcolour'].';font-size:'.$style['outputfont'].'em;}
.applicationform p, .applicationform span {color:#415063;margin:4px 0;}
.qis_form h2, .qis_form .submit {background:'.$style['submit-background'].';color:'.$style['submit-color'].';text-align:center;}
h2.toggle a:link, h2.toggle a:hover, h2.toggle a:visited, .applicationform .submit {color:'.$style['submit-color'].';}
.qis-error-message {color:red !important;font-weight:bold;}
.qis-error {border:1px solid red !important;}';
    return $data;
}

function qis_head_css () {
	$data = '<style type="text/css" media="screen">'.qis_generate_css().'</style><script type="text/javascript">qis__rates = [];</script>';
	echo $data;
}

function qis_registration_report() {
    $message = get_option('qis_messages');
    ob_start();
    $content ='<div id="qis-widget">
    <h2>Loan Applications</h2>';
    $content .= qis_build_registration_table ($message,'report');
    $content .='</div>';
    echo $content;
    $output_string=ob_get_contents();
    ob_end_clean();
    return $output_string;
}

function qis_build_registration_table ($message,$report) {
    $register = qis_get_stored_register();
    $span=$charles=$content='';
    $delete=array();$i=0;
    $dashboard = '<table cellspacing="0">
    <tr>';
    if ($register['usename']) $dashboard .= '<th>'.$register['yourname'].'</th>';
    if ($register['usemail']) $dashboard .= '<th>'.$register['youremail'].'</th>';
    if ($register['usetelephone']) $dashboard .= '<th>'.$register['yourtelephone'].'</th>';
    if ($register['usemessage']) $dashboard .= '<th>'.$register['yourmessage'].'</th>';
    $dashboard .= '<th>Amount</th><th>Period</th>';
    $dashboard .= '<th>Date Sent</th>';
    if (!$report) $dashboard .= '<th>Delete</th>';
    $dashboard .= '</tr>';
	
    foreach($message as $value) {
        $content .= '<tr'.$span.'>';
        if ($register['usename']) $content .= '<td>'.$value['yourname'].'</td>';
        if ($register['usemail']) $content .= '<td>'.$value['youremail'].'</td>';
        if ($register['usetelephone']) $content .= '<td>'.$value['yourtelephone'].'</td>';
        if ($register['usemessage']) $content .= '<td>'.$value['yourmessage'].'</td>';
        $content .= '<td>'.$value['loan-amount'].'</td>';
        $content .= '<td>'.$value['loan-period'].'</td>';
        if ($value['yourname']) $charles = 'messages';
        $content .= '<td>'.$value['sentdate'].'</td>';
        if (!$report)  $content .= '<td><input type="checkbox" name="'.$i.'" value="checked" /></td>';
        $content .= '</tr>';
        $i++;
    }	
    $dashboard .= $content.'</table>';
    if ($charles) return $dashboard;
}

function qis_get_stored_style() {
	$style = get_option('qis_style');
	if(!is_array($style)) $style = array();
    $default = array(
        'primary-font-colour' => '#3D9BE9',
        'secondary-font-colour' => '#465069',
        'border' => 'roundshadow',
        'form-border-thickness' => 5,
        'form-border-color' => '#007B9A',
        'width' => 350,
        'widthtype' => 'percent',
        'background' => 'white',
        'backgroundhex' => '#FFF',
        'corners' => 'corner',
        'slider-thickness' => 1,
        'slider-background' => '#CCC',
        'slider-revealed' => '#3D9BE9',
        'handle-background' => 'white',
        'handle-border' => '#007B9A',
        'handle-corners' => 20,
        'output-size' => '1.2em',
        'output-colour' => '#465069',
        'backgroundimage' => '',
        'toplinefont' => 1,
        'toplinecolour' => '#3D9BE9',
        'slideroutputfont' => 1,
        'slideroutputcolour' => '#465069',
        'interestfont' => 1,
        'interestcolour' => '#3D9BE9',
        'totalfont' => 1.5,
        'totalcolour' => '#465069',
        'headerfont' => 1,
        'headercolour' => '#FFF',
        'headerbackground' => '#888',
        'rowfont' => 1,
        'rowcolour' => '#FFF',
        'rowbackground' => '#465069',
        'outputfont' => 1,
        'outputcolour' => '#3D9BE9'
    );
	$style = array_merge($default, $style);
	return $style;
}

function qis_get_stored_settings() {
	$settings = get_option('qis_settings');
	if(!is_array($settings)) $settings = array();
    $default = array(
        'currency' => '$',
        'ba' => 'before',
        'separator' => 'none',
        'primary' => 2,
        'secondary' => 3,
        'interesttype' => 'simple',
        'loanmin' => 100000,
        'loanmax' => 3000000,
        'loaninitial' => 1500000,
        'loanstep' => 10000,
        'periodmin' => 7,
        'periodmax' => 56,
        'periodinitial' => 28,
        'periodstep' => 7,
        'period' => 'days',
        'periodlabel' => false,
        'multiplier' => 1,
        'trigger' => 35,
        'borrowlabel' => 'Loan Amount',
        'forlabel' => 'Loan Period',
        'outputinterestlabel' => 'Interest to pay',
        'outputtotallabel' => 'Total you will Pay',
        'interestlabel' => 'Interest',
        'totallabel' => 'Total to Pay',
        'primarylabel' => '2% Interest',
        'secondarylabel' => '3% Interest',
		'usebubble' => 0,
        'outputlimits' => 'checked',
        'outputtable' => 'checked',
        'outputinterest' => 'checked',
        'outputtotal' => 'checked',
        'outputrepayments' => 'checked',
		'periodslider' => '',
        'repaymentlabel' => 'Your repayments are [amount] every [step] days at [interest]'        
    );
    $settings = array_merge($default, $settings);
	return $settings;
}

function qis_get_stored_register () {
    $register = get_option('qis_register');
    if(!is_array($register)) $register = array();
    $default = array(
        'application' => '',
        'sort' => 'field1,field2,field3,field4,field5,field6,field7',
        'usename' => 'checked',
        'usemail' => 'checked',
        'useaddinfo' => '',
        'reqname' => 'checked',
        'reqmail' => 'checked',
        'notificationsubject' => __('New registration for', 'loan-calculator'),
        'title' => __('Apply for this Loan', 'loan-calculator'),
        'blurb' => __('Enter your details below. All fields are required', 'loan-calculator'),
        'replytitle' => __('Thank you for applying', 'loan-calculator'),
        'replyblurb' => __('We will be in contact soon', 'loan-calculator'),
        'yourname' => __('Your Name', 'loan-calculator'),
        'youremail' => __('Email Address', 'loan-calculator'),
        'yourtelephone' => __('Telephone Number', 'loan-calculator'),
        'yourmessage' => __('Message', 'loan-calculator'),
        'addinfo' => __('Fill in this field', 'loan-calculator'),
        'useterms' => '',
        'termslabel' => __('I agree to the Terms and Conditions', 'loan-calculator'),
        'termsurl' => '',
        'termstarget' => '',
        'notattend' => '',
        'errortitle' => __('There is an error with your Application', 'loan-calculator'),
        'errorblurb' => __('Please complete all fields', 'loan-calculator'),
        'qissubmit' => __('Apply now', 'loan-calculator'),
        'sendemail' => get_bloginfo('admin_email'),
        'sendcopy' => '',
        'usecopy' => '',
        'completed' => '',
        'copyblurb' => __('Send application details to your email address', 'loan-calculator')
    );
    $register = array_merge($default, $register);
    return $register;
}

function qis_get_stored_autoresponder () {
    $auto = get_option('qis_autoresponder');
    if(!is_array($auto)) $auto = array();
    $fromemail = get_bloginfo('admin_email');
    $title = get_bloginfo('name');
    $default = array(
        'enable' => '',
        'subject' => __('Loan Application', 'loan-calculator'),
        'message' => __('Thank you for your application, we will be in contact soon. If you have any questions please reply to this email.', 'loan-calculator'),
        'useregistrationdetails' => 'checked',
        'registrationdetailsblurb' => __('Your application details', 'loan-calculator'),
        'sendcopy' => 'checked',
        'fromname' => $title,
        'fromemail' => $fromemail,
        'permalink' => ''
    );
    $auto = array_merge($default, $auto);
    return $auto;
}

add_action( 'template_redirect', 'qis_upgrade_ipn' );

function qis_upgrade_ipn() {
    $qppkey = get_option('qpp_key');
    if (!$_POST['custom'] || $qppkey['authorised'])
        return;
    define("DEBUG", 0);
    define("LOG_FILE", "./ipn.log");
    $raw_post_data = file_get_contents('php://input');
    $raw_post_array = explode('&', $raw_post_data);
    $myPost = array();
    foreach ($raw_post_array as $keyval) {
        $keyval = explode ('=', $keyval);
        if (count($keyval) == 2)
            $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
    $req = 'cmd=_notify-validate';
    if(function_exists('get_magic_quotes_gpc')) {
        $get_magic_quotes_exists = true;
    }
    foreach ($myPost as $key => $value) {
        if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
            $value = urlencode(stripslashes($value));
        } else {
            $value = urlencode($value);
        }
        $req .= "&$key=$value";
    }

    $ch = curl_init("https://www.paypal.com/cgi-bin/webscr");
    if ($ch == FALSE) {
        return FALSE;
    }

    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

    if(DEBUG == true) {
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    }

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

    $res = curl_exec($ch);
    if (curl_errno($ch) != 0) // cURL error
    {
        if(DEBUG == true) {	
            error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
        }
        curl_close($ch);
        // exit;
    } else {
		if(DEBUG == true) {
			error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
			error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
        }
		curl_close($ch);
    }

    $tokens = explode("\r\n\r\n", trim($res));
    $res = trim(end($tokens));

    if (strcmp ($res, "VERIFIED") == 0 && $qppkey['key'] == $_POST['custom']) {
        $qppkey['authorised'] = 'true';
        update_option('qpp_key',$qppkey);
        $qpp_setup = qp_get_stored_setup();
        $email  = bloginfo('admin_email');
        $headers = "From: Quick Plugins <mail@quick-plugins.com>\r\n"
. "MIME-Version: 1.0\r\n"
. "Content-Type: text/html; charset=\"utf-8\"\r\n";	
        $message = '<html><p>Thank for upgrading. Your authorisation key is:</p><p>'.$qppkey['key'].'</p></html>';
        wp_mail($email,'Quick Plugins Authorisation Key',$message,$headers);
    }
    exit();
}

function qis_current_page_url() {
	$pageURL = 'http';
	if (!isset($_SERVER['HTTPS'])) $_SERVER['HTTPS'] = '';
	if (!empty($_SERVER["HTTPS"])) {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if (($_SERVER["SERVER_PORT"] != "80") && ($_SERVER['SERVER_PORT'] != '443'))
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	else 
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	return $pageURL;
}
function qis_separator($s,$separator) {
	if ($separator == 'none') return $s;
	$se = (($separator == 'comma')? ',':' ');
	return trim(preg_replace("/(\d)(?=(\d{3})+$)/",'$1'.$se,$s));
}