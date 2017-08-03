<?php
add_action('admin_menu', 'qis_page_init');
add_action('admin_notices', 'qis_admin_notice' );
add_action('admin_enqueue_scripts', 'qis_settings_scripts');

function qis_settings_scripts() {
    qis_scripts();
	wp_enqueue_style('qis_settings',plugins_url('settings.css', __FILE__));
	}

function qis_page_init() {
	add_options_page('Interest Calculator', 'Interest Calculator', 'manage_options', __FILE__, 'qis_tabbed_page');
	}

function qis_admin_tabs($current = 'settings') { 
	$tabs = array(
        'settings' => 'Settings',
        'styles' => 'Styling',
        'application' =>  __('Application Form', 'loan-calculator'),
        'auto'  => __('Auto Responder', 'loan-calculator'),
    ); 
	echo '<h2 class="nav-tab-wrapper">';
	foreach( $tabs as $tab => $name ) {
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$class' href='?page=quick-interest-slider/settings.php&tab=$tab'>$name</a>";
		}
	echo '</h2>';
	}

function qis_tabbed_page() {
	echo '<div class="wrap">';
	echo '<h1>Interest Calculator</h1>';
	if ( isset ($_GET['tab'])) {
        qis_admin_tabs($_GET['tab']); $tab = $_GET['tab'];
    } else {
        qis_admin_tabs('settings'); $tab = 'settings';
    }
	switch ($tab) {
		case 'styles' : qis_styles(); break;
		case 'settings' : qis_settings (); break;
        case 'application' : qis_register (); break;
        case 'auto' : qis_autoresponse_page(); break;
        case 'upgrade' : qis_upgrade(); break;
		}
	echo '</div>';
	}

function qis_settings() {
    
    if( isset( $_POST['Submit'])) {
        $options = array(
            'currency',
            'ba',
            'separator',
            'primary',
            'secondary',
            'interesttype',
            'loanmin',
            'loanmax',
            'loaninitial',
            'loanstep',
            'periodslider',
            'periodmin',
            'periodmax',
            'periodinitial',
            'periodstep',
            'period',
            'periodlabel',
            'multiplier',
            'trigger',
            'outputrepayments',
            'repaymentlabel',
            'outputinterestlabel',
            'outputtotallabel',
            'interestlabel',
            'totallabel',
            'primarylabel',
            'secondarylabel',
            'usebubble',
            'outputlimits',
            'outputtable',
            'outputinterest',
            'outputtotal'
        );
		foreach ( $options as $item) $settings[$item] = stripslashes($_POST[$item]);
		if (isset($_POST['usebubble'])) {
			$settings['usebubble'] = 1;
		} else {
			$settings['usebubble'] = 0;
		}
        update_option( 'qis_settings', $settings);
		qis_admin_notice("The settings have been updated.");
    }
    
    if( isset( $_POST['Reset'])) {
		delete_option('qis_settings');
		qis_admin_notice("The settings have been reset.");
		}
    
    $settings = qis_get_stored_settings();
    $$settings['period'] = 'checked';
    $$settings['ba'] = 'checked';
    $$settings['separator'] = 'checked';
    $$settings['interesttype'] = 'checked';
	$usebubble = '';
	if ($settings['usebubble']) $usebubble = " checked='checked' ";
    $content .='<form method="post" action="">
    <div class="qis-options">
    <h2>Using the Plugin</h2>
    <p>Add the form to a post or page using the shortcode [qis]</p>
    
    <table>

    <tr>
    <td colspan="2"><h2>Currency</h2></td>
    <tr>
    <tr>
    <td colspan="2"><input type="text" style="width:5em;" name="currency" . value ="' . $settings['currency'] . '" /></td>
    </tr>
    
    <tr>
    <td colspan="2">Currency Position: <input type="radio" name="ba" value="before" ' . $before . ' /> Before <input type="radio" name="ba" value="after" ' . $after . ' /> After</td>
    </tr>
    
    <tr>
    <td colspan="2">Thousands separator: <input type="radio" name="separator" value="none" ' . $none . ' /> None <input type="radio" name="separator" value="comma" ' . $comma . ' /> Comma <input type="radio" name="separator" value="space" ' . $space . ' /> Space</td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Interest Rates</h2></td>
    </tr>
    <tr>
    <td width="30%">Primary:</td>
    <td><input type="text" style="width:3em;" name="primary" . value ="' . $settings['primary'] . '" />%</td>
    </tr>
    
    <tr>
    <td>Secondary:</td>
    <td><input type="text" style="width:3em;" name="secondary" . value ="' . $settings['secondary'] . '" />%</td>
    </tr>
    
    <tr>
    <td colspan="2">Interest Type: <input type="radio" name="interesttype" value="simple" ' . $simple . ' /> Simple <input type="radio" name="interesttype" value="compound" ' . $compound . ' /> Compound</td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Amount Slider Settings</h2></td>
    </tr>
    
    <tr>
    <td width="25%">Minimum value:</td>
    <td>' . $settings['currency'] . '<input type="text" style="width:5em;" name="loanmin" . value ="' . $settings['loanmin'] . '" /></td>
    </tr>
    
    <tr>
    <td>Maximum value:</td>
    <td>' . $settings['currency'] . '<input type="text" style="width:5em;" name="loanmax" . value ="' . $settings['loanmax'] . '" /></td>
    </tr>
    
    <tr>
    <td>Initial value:</td>
    <td>' . $settings['currency'] . '<input type="text" style="width:5em;" name="loaninitial" . value ="' . $settings['loaninitial'] . '" /></td>
    </tr>
    
    <tr>
    <td>Step:</td>
    <td>' . $settings['currency'] . '<input type="text" style="width:5em;" name="loanstep" . value ="' . $settings['loanstep'] . '" /></td>
    </tr>
    
    </table>

    </div>
    <div class="qis-options">
    
    <table>
    
    <tr>
    <td colspan="2"><h2>Term Slider</h2></td>
    </tr>
    
    <tr>
    <td colspan="2"><input type="checkbox" name="periodslider"  value="checked" ' . $settings['periodslider'] . '/> Use Term slider</td>
    </tr>
    
    
    <tr>
    <td colspan="2"><h2>Loan Period</h2></td>
    </tr>
    
    <tr>
    <td colspan="2"><input type="radio" name="period" value="days" ' . $days . ' /> Days
    <input type="radio" name="period" value="weeks" ' . $weeks . ' /> Weeks
    <input type="radio" name="period" value="months" ' . $months . ' /> Months
    <input type="radio" name="period" value="years" ' . $years . ' /> Years</td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Term Slider Settings</h2></td>
    </tr>
    
    <tr>
    <td width="25%">Minimum term:</td>
    <td><input type="text" style="width:5em;" name="periodmin" . value ="' . $settings['periodmin'] . '" /> ' . $settings['period'] . '</td>
    </tr>
    
    <tr>
    <td>Maximum term:</td>
    <td><input type="text" style="width:5em;" name="periodmax" . value ="' . $settings['periodmax'] . '" /> ' . $settings['period'] . '</td>
    </tr>
    
    <tr>
    <td>Initial term:</td>
    <td><input type="text" style="width:5em;" name="periodinitial" . value ="' . $settings['periodinitial'] . '" /> ' . $settings['period'] . ' </td>
    </tr>
    
    <tr>
    <td>Step term:</td>
    <td><input type="text" style="width:5em;" name="periodstep" . value ="' . $settings['periodstep'] . '" /> ' . $settings['period'] . '</td>
    </tr>
    
    <tr>
    <td>Term label:</td>
    <td><input type="text" name="periodlabel" . value ="' . $settings['periodlabel'] . '" /><br>
    <span class = "description">This will replace the word \''.$settings['period'].'\' with your own label</span></td>
    </tr>
    
    
    <tr>
    <td>Trigger:</td>
    <td><input type="text" style="width:5em;" name="trigger" . value ="' . $settings['trigger'] . '" />' . $settings['period'] . '<br>
    <span class = "description">This is the term value when interest switches from primary to secondary</span></td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Repayment Divider</h2>
    <p class = "description">Use this is you want to display the repayment amount in a different period to that in the slider. For example, if you have a loan period in years but want to show the repayment amount per month the divider would be 12. For repayment each week on a montly loan the divider would be 4.3</p>
    </td>
    </tr>
    <tr>
    <td>Divider:</td>
    <td><input type="text" style="width:3em;" name="multiplier" . value ="' . $settings['multiplier'] . '" /></td>
    </tr>
    </table>

    </div>
    <div class="qis-options">

    <table>
    <tr>
    <td colspan="2"><h2>Output Options</h2></td>
    </tr>
    
    <tr>
    <td width="5%"><input type="checkbox" name="outputlimits"  value="checked" ' . $settings['outputlimits'] . '/></td>
    <td width="40%">Display min and max and slider value above the slider</td>
    <td><input type="checkbox" name="usebubble"  value="1" '.$usebubble.'/>&nbsp;Use value bubble</td>
    </tr>
    
    <tr>
    <td width="5%"><input type="checkbox" name="outputrepayments"  value="checked" ' . $settings['outputrepayments'] . '/></td>
    <td width="40%">Display repayment terms</td>
    <td><textarea name="repaymentlabel" label="repaymentlabel" rows="4">' . $settings['repaymentlabel'] . '</textarea><br>
    <span class="description">The label has three optional shortcodes: [amount], [step] and [interest].</span></td>
    </tr>
    
    <tr>
    <td width="5%"><input type="checkbox" name="outputinterest"  value="checked" ' . $settings['outputinterest'] . '/></td>
    <td>Display interest below slider</td>
    <td><input type="text" name="outputinterestlabel"  value ="' . $settings['outputinterestlabel'] . '" /></td>
    </tr>
    
    <tr>
    <td width="5%"><input type="checkbox" name="outputtotal"  value="checked" ' . $settings['outputtotal'] . '/></td>
    <td>Display total to pay below slider</td>
    <td><input type="text" name="outputtotallabel"  value ="' . $settings['outputtotallabel'] . '" /></td>
    </tr>
    
    <tr>
    <td width="5%"><input type="checkbox" name="outputtable"  value="checked" ' . $settings['outputtable'] . '/></td>
    <td>Display table with primary and secondary interest values and totals below slider</td>
    </tr>
    </table>
    
    <table>
    <tr>
    <td width="33%"></td>
    <td width="33%"><input type="text" name="primarylabel"  value ="' . $settings['primarylabel'] . '" /></td>
    <td width="33%"><input type="text" name="secondarylabel"  value ="' . $settings['secondarylabel'] . '" /></td>
    </tr>
    
    <tr>
    <td><input type="text" name="interestlabel"  value ="' . $settings['interestlabel'] . '" /></td>
    <td><em>primary interest output</em></td>
    <td><em>secondary interest output</em></td>
    </tr>
    
    <tr>
    <td><input type="text" name="totallabel"  value ="' . $settings['totallabel'] . '" /></td>
    <td><em>primary total output</em></td>
    <td><em>secondary total output</em></td>
    </tr>
    </table>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the settings?\' );"/></p>
    </form>
    </div>';
	echo $content;
	}

function qis_styles() {
	if( isset( $_POST['Submit'])) {
		$options = array(
            'border',
            'primary-font-colour',
            'secondary-font-colour',
            'form-border-thickness',
            'form-border-color',
            'width',
            'widthtype',
            'background',
            'backgroundhex',
            'backgroundimage',
            'slider-background',
            'slider-revealed',
            'handle-background',
            'handle-border',
            'handle-corners',
            'output-size',
            'output-colour',
            'slider-thickness',
            'toplinefont',
            'toplinecolour',
            'slideroutputfont',
            'slideroutputcolour',
            'interestfont',
            'interestcolour',
            'totalfont',
            'totalcolour',
            'headerfont',
            'headercolour',
            'headerbackground',
            'rowfont',
            'rowcolour',
            'rowbackground',
            'outputfont',
            'outputcolour'
        );
		foreach ( $options as $item) $style[$item] = stripslashes($_POST[$item]);
		update_option( 'qis_style', $style);
		qis_admin_notice("The slider styles have been updated.");
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qis_style');
		qis_admin_notice("The slider styles have been reset.");
		}
	$style = qis_get_stored_style();
    $$style['widthtype'] = 'checked';
    $$style['border'] = 'checked';
    $$style['background'] = 'checked';
    $content = qis_head_css();
	$content .='<form method="post" action="">
    <div class="qis-options">
    <table>
    <tr>
    <td colspan="2"><h2>Form Width</h2></td>
    </tr>
    
    <tr>
    <td colspan="2"><p><input type="radio" name="widthtype" value="percent" ' . $percent . ' /> 100% (fill the available space)</p>
    <p><input type="radio" name="widthtype" value="pixel" ' . $pixel . ' /> Pixel (fixed): <input type="text" style="width:4em" label="width" name="width" value="' . $style['width'] . '" /> use px, em or %. Default is px.</p></td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Form Border</h2></td>
    </tr>
    
    <tr>
    <td width="25%">Type:</td>
    <td><p><input type="radio" name="border" value="none" ' . $none . ' /> No border</p>
    <p><input type="radio" name="border" value="plain" ' . $plain . ' /> Plain Border</p>
    <p><input type="radio" name="border" value="rounded" ' . $rounded . ' /> Round Corners (Not IE8)</p>
    <p><input type="radio" name="border" value="shadow" ' . $shadow . ' /> Shadowed Border(Not IE8)</p>
    <p><input type="radio" name="border" value="roundshadow" ' . $roundshadow . ' /> Rounded Shadowed Border (Not IE8)</td>
    </tr>
    
    <tr>
    <td>Thickness:</td>
    <td><input type="text" style="width:3em" name="form-border-thickness" value="' . $style['form-border-thickness'] . '" />&nbsp;px</td>
    </tr>
    
    <tr>
    <td>Color:</td>
    <td><input type="text" class="qis-color" name="form-border-color" value="' . $style['form-border-color'] . '" /></td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Background</h2></td>
    </tr>
    
    <tr>
    <td>Colour:</td>
    <td><p><input type="radio" name="background" value="white" ' . $white . ' /> White</p>
    <p><input type="radio" name="background" value="theme" ' . $theme . ' /> Use theme colours</p>
    <p><input style="margin-bottom:5px;" type="radio" name="background" value="color" ' . $color . ' /> <input type="text" class="qis-color" label="background" name="backgroundhex" value="' . $style['backgroundhex'] . '" /></p></td>
    </tr>
    <tr><td>Background<br>Image:</td>
    <td>
    <input id="qis_background_image" type="text" name="backgroundimage" value="' . $style['backgroundimage'] . '" />
    <input id="qis_upload_background_image" class="button" type="button" value="Upload Image" /><br>
    <span class="description">Leave blank to use plain colours</span></td>
    </tr>

    </table>

    </div>
    <div class="qis-options">

    <table>

    <tr>
    <td colspan="2"><h2>Max and min values</h2></td>
    </tr>

    <tr>
    <td width="30%">Font Size</td>
    <td><input type="text" style="width:3em" name="toplinefont" value="' . $style['toplinefont'] . '" />&nbsp;em</td>
    </tr>
    
    <tr>
    <td>Font Colour</td>
    <td><input type="text" class="qis-color" name="toplinecolour" value="' . $style['toplinecolour'] . '" /></td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Slider output</h2></td>
    </tr>
    
    <tr>
    <td>Font Size</td>
    <td><input type="text" style="width:3em" name="slideroutputfont" value="' . $style['slideroutputfont'] . '" />&nbsp;em</td>
    </tr>
    
    <tr>
    <td>Font Colour</td>
    <td><input type="text" class="qis-color" name="slideroutputcolour" value="' . $style['slideroutputcolour'] . '" /></td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Slider</h2></td>
    </tr>
    
    <tr>
    <td width="25%">Thickness</td>
    <td><input type="text" style="width:3em" name="slider-thickness" value="' . $style['slider-thickness'] . '" />&nbsp;em</td>
    </tr>
    
    <tr>
    <td>Normal Background</td>
    <td><input type="text" class="qis-color" name="slider-background" value="' . $style['slider-background'] . '" /></td>
    </tr>
    
    <tr>
    <td>Revealed Background</td>
    <td><input type="text" class="qis-color" name="slider-revealed" value="' . $style['slider-revealed'] . '" /></td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Handle</h2></td>
    </tr>
    
    <tr>
    <td>Background</td>
    <td><input type="text" class="qis-color" name="handle-background" value="' . $style['handle-background'] . '" /></td>
    </tr>
    
    <tr>
    <td>Border colour</td>
    <td><input type="text" class="qis-color" name="handle-border" value="' . $style['handle-border'] . '" /></td>
    </tr>
    
    <tr>
    <td>Corners</td>
    <td><input type="text" style="width:2em" name="handle-corners" value="' . $style['handle-corners'] . '" />&nbsp;%</td>
    </tr>
    
    </table>

    </div>
    <div class="qis-options">

    <table>
    
    <tr>
    <td colspan="2"><h2>Interest/Repayments</h2></td>
    </tr>
    
    <tr>
    <td width="30%">Font Size</td>
    <td><input type="text" style="width:3em" name="interestfont" value="' . $style['interestfont'] . '" />&nbsp;em</td>
    </tr>
    
    <tr>
    <td>Font Colour</td>
    <td><input type="text" class="qis-color" name="interestcolour" value="' . $style['interestcolour'] . '" /></td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Total to Pay</h2></td>
    </tr>
    
    <tr>
    <td>Font Size</td>
    <td><input type="text" style="width:3em" name="totalfont" value="' . $style['totalfont'] . '" />&nbsp;em</td>
    </tr>
    
    <tr>
    <td>Font Colour</td>
    <td><input type="text" class="qis-color" name="totalcolour" value="' . $style['totalcolour'] . '" /></td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Output Table Header</h2></td>
    </tr>
    
    <tr>
    <td>Font Size</td>
    <td><input type="text" style="width:3em" name="headerfont" value="' . $style['headerfont'] . '" />&nbsp;em</td>
    </tr>
    
    <tr>
    <td>Font Colour</td>
    <td><input type="text" class="qis-color" name="headercolour" value="' . $style['headercolour'] . '" /></td>
    </tr>
    
    <tr>
    <td>Font Background</td>
    <td><input type="text" class="qis-color" name="headerbackground" value="' . $style['headerbackground'] . '" /></td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Output Table Row</h2></td>
    </tr>
    
    <tr>
    <td>Font Size</td>
    <td><input type="text" style="width:3em" name="rowfont" value="' . $style['rowfont'] . '" />&nbsp;em</td>
    </tr>
    
    <tr>
    <td>Font Colour</td>
    <td><input type="text" class="qis-color" name="rowcolour" value="' . $style['rowcolour'] . '" /></td>
    </tr>
    
    <tr>
    <td>Font Background</td>
    <td><input type="text" class="qis-color" name="rowbackground" value="' . $style['rowbackground'] . '" /></td>
    </tr>
    
    <tr>
    <td colspan="2"><h2>Output Table Values</h2></td>
    </tr>
    
    <tr>
    <td>Font Size</td>
    <td><input type="text" style="width:3em" name="outputfont" value="' . $style['outputfont'] . '" />&nbsp;em</td>
    </tr>
    
    <tr>
    <td>Font Colour</td>
    <td><input type="text" class="qis-color" name="outputcolour" value="' . $style['outputcolour'] . '" /></td>
    </tr>
    
    </table>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the styles?\' );"/></p>
    </div>
    </form>';
    echo $content;
}

function qis_register (){
    $processpercent=$processfixed=$qis_apikey='';
    if( isset( $_POST['Submit']) && check_admin_referer("save_qis")) {
        $options = array(
            'application',
            'formwidth',
            'usename',
            'usemail',
            'usetelephone',
            'usemessage',
            'usecaptcha',
            'useaddinfo',
            'usecopy',
            'formborder',
            'sendemail',
            'subject',
            'subjecttitle',
            'subjectdate',
            'title',
            'blurb',
            'yourname',
            'youremail',
            'yourtelephone',
            'yourmessage',
            'yourcaptcha',
            'addinfo',
            'qissubmit',
            'errortitle',
            'errorblurb',
            'replytitle',
            'replyblurb',
            'copyblurb',
            'sort',
            'useterms',
            'termslabel',
            'termsurl',
            'termstarget',
            'nonotifications',
            'copychecked'
        );
        foreach ($options as $item) {
            $register[$item] = stripslashes( $_POST[$item]);
            $register[$item] = filter_var($register[$item],FILTER_SANITIZE_STRING);
        }
        update_option('qis_register', $register);

        qis_admin_notice(__('The registration form settings have been updated', 'loan-calculator'));
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qis")) {
        delete_option('qis_register');
        qis_admin_notice(__('The registration form settings have been reset', 'loan-calculator'));
    }
    
    $register = qis_get_stored_register();
    $qppkey = get_option('qpp_key');
    
    $content ='<div class="qis-settings">';
    if (!$qppkey['authorised']) {
        $content .= '<div class="qis-options" style="width:90%;">
        <h2 style="color:#B52C00">'.__('Application Form', 'loan-calculator').'</h2>
        <p>Add a form to the loan calculator to allow visitors to apply for a loan.</p>
        <p>The application form is only availabile in the pro version of the plugin.</p>
        <h3><a href="?page=quick-interest-slider/settings.php&tab=upgrade">Upgrade to Pro</a></h3></div>';
    } else { 
        $content = qis_head_css();
        $content = '<form id="" method="post" action="">
        <div class="qis-options">
        <table width="100%">
        <tr>
        <td width="5%"><input type="checkbox" name="application"' . $register['application'] . ' value="checked" /></td><td colspan="2">'.__('Enable Application Form', 'loan-calculator').'.</td>
        </tr>
        <tr>
        <td colspan="3"><h2>'.__('Notifications', 'loan-calculator').'</h2></td>
        </tr>
        <tr>
        <td colspan="2">'.__('Your Email Address', 'loan-calculator').'</td>
        <td><input type="text" style="" name="sendemail" value="' . $register['sendemail'] . '" /><br><span class="description">'.__('This is where registration notifications will be sent', 'loan-calculator').'</span></td>
        </tr>
        <tr>
        <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="nonotifications" ' . $register['nonotifications'] . ' value="checked" /></td>
        <td colspan="2">'.__('Do not send notifications').'</td>
        </tr>
        <tr>
        <td colspan="3"><h2>'.__('Registration Form', 'loan-calculator').'</h2></td>
        </tr>
        <tr>
        <td colspan="2">'.__('Form title', 'loan-calculator').'</td>
        <td><input type="text" style="" name="title" value="' . $register['title'] . '" /></td>
        </tr>
        <tr>
        <td colspan="2">'.__('Form blurb', 'loan-calculator').'</td>
        <td><input type="text" style="" name="blurb" value="' . $register['blurb'] . '" /></td>
        </tr>
        <td colspan="2">'.__('Submit Button', 'loan-calculator').'</td>
        <td><input type="text" style="" name="qissubmit" value="' . $register['qissubmit'] . '" /></td>
        </tr>
        </table>
    </div>
        <div class="qis-options">
        <h2>Form Fields</h2>
        <p>'.__('Check those fields you want to use. Drag and drop to change the order', 'loan-calculator').'.</p>
        <style>table#sorting{width:100%;}
        #sorting tbody tr{outline: 1px solid #888;background:#E0E0E0;}
        #sorting tbody td{padding: 2px;vertical-align:middle;}
        #sorting{border-collapse:separate;border-spacing:0 5px;}</style>
        <script>
        jQuery(function() 
        {var qis_rsort = jQuery( "#qis_rsort" ).sortable(
        {axis: "y",cursor: "move",opacity:0.8,update:function(e,ui)
        {var order = qis_rsort.sortable("toArray").join();jQuery("#qis_register_sort").val(order);}});});
        </script>
        <table id="sorting">
        <thead>
        <tr>
        <th width="5%">U</th>
        <th width="20%">'.__('Field', 'loan-calculator').'</th>
        <th>'.__('Label', 'loan-calculator').'</th>
        </tr>
        </thead>
        <tbody id="qis_rsort">';
        $sort = explode(",", $register['sort']);
        foreach ($sort as $name) {
            switch ( $name ) {
                case 'field1':
                $use = 'usename';
                $label = __('Name', 'loan-calculator');
                $input = 'yourname';
                break;
                case 'field2':
                $use = 'usemail';
                $label = __('Email', 'loan-calculator');
                $input = 'youremail';
                break;
            case 'field3':
                $use = 'usetelephone';
                $label = __('Telephone', 'loan-calculator');
                $input = 'yourtelephone';
                break;
                case 'field4':
                $use = 'usemessage';
                $label = __('Message', 'loan-calculator');
                $input = 'yourmessage';
                break;
                case 'field5':
                $use = 'usecaptcha';
                $label = __('Captcha', 'loan-calculator');
                $input = 'Displays a simple maths captcha to confuse the spammers.';
                break;
                case 'field6':
                $use = 'usecopy';
                $label = __('Copy Message', 'loan-calculator');
                $input = 'copyblurb';
                break;
                case 'field7':
                $use = 'useaddinfo';
                $label = __('Additional Info (displays as plain text)', 'loan-calculator');
                $input = 'addinfo';
                break;
            }
            $content .= '<tr id="'.$name.'">
            <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="'.$use.'" ' . $register[$use] . ' value="checked" /></td>';
            $content .= '<td width="25%">'.$label.'</td><td>';
            if ($name=='field5') $content .= $input;
            else $content .= '<input type="text" style="padding:1px;border: 1px solid #343838;" name="'.$input.'" value="' . $register[$input] . '" />';
            $content .= '</td></tr>';
        }
        $content .='</tbody>
        </table>
        <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="copychecked"' . $register['copychecked'] . ' value="checked" /> '.__('Set default \'Copy Message\' field to \'checked\'', 'loan-calculator').'</p>
        <input type="hidden" id="qis_register_sort" name="sort" value="'.$register['sort'].'" />
        </div>
        <div class="qis-options">
        <table>
        <tr>
        <td colspan="3"><h2>'.__('Terms and Conditions', 'loan-calculator').'</h2></td>
        </tr>
        <tr>
        <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="useterms" ' . $register['useterms'] . ' value="checked" /></td>
        <td colspan="2">'.__('Include Terms and Conditions checkbox').'</td>
        </tr>
        <tr>
        <td></td>
        <td>'.__('T&C label', 'loan-calculator').'</td>
        <td><input type="text" style="" name="termslabel" value="' . $register['termslabel'] . '" /></td>
        </tr>
        <tr>
        <td></td>
        <td>'.__('T&C URL', 'loan-calculator').'</td>
        <td><input type="text" style="" name="termsurl" value="' . $register['termsurl'] . '" /></td>
        </tr>
        <tr>
        <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="termstarget" ' . $register['termstarget'] . ' value="checked" /></td>
        <td colspan="2">'.__('Open link in new Tab/Window', 'loan-calculator').'</td>
        </tr>
        <tr>
        <td colspan="3"><h2>'.__('Error and Thank-you messages', 'loan-calculator').'</h2></td>
        </tr>
        <tr>
        <td colspan="2">'.__('Thank you message title', 'loan-calculator').'</td>
        <td><input type="text" style="" name="replytitle" value="' . $register['replytitle'] . '" /></td>
        </tr>
        <tr>
        <td colspan="2">'.__('Thank you message blurb', 'loan-calculator').'</td>
        <td><textarea style="width:100%;height:100px;" name="replyblurb">' . $register['replyblurb'] . '</textarea></td>
        </tr>
        <tr>
        <td colspan="2">'.__('Error Title', 'loan-calculator').'</td>
        <td><input type="text" style="" name="errortitle" value="' . $register['errortitle'] . '" /></td>
        </tr>
        <tr>
        <td colspan="2">'.__('Error Message', 'loan-calculator').'</td>
        <td><input type="text" style="" name="errorblurb" value="' . $register['errorblurb'] . '" /></td>
        </tr>
        </table>
        <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'loan-calculator').'" />
        <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'loan-calculator').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the registration form?', 'loan-calculator').'\' );"/></p>';
        $content .= wp_nonce_field("save_qis");
        $content .= '</div></form>';
    }
    $content .= '</div>';
	echo $content;		
}

function qis_autoresponse_page() {
    if( isset( $_POST['Submit']) && check_admin_referer("save_qis")) {
        $options = array(
            'enable',
            'subject',
            'subjecttitle',
            'subjectdate',
            'message',
            'useeventdetails',
            'eventdetailsblurb',
            'useregistrationdetails',
            'registrationdetailsblurb',
            'sendcopy',
            'fromname',
            'fromemail',
            'permalink'
        );
        foreach ( $options as $item) {
            $auto[$item] = stripslashes($_POST[$item]);
        }
        update_option( 'qis_autoresponder', $auto );
        qis_admin_notice("The autoresponder settings have been updated.");
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qis")) {
        delete_option('qis_autoresponder');
        qis_admin_notice("The autoresponder settings have been reset.");
    }
	$auto = qis_get_stored_autoresponder();
    $qppkey = get_option('qpp_key');
    
    $$auto['whenconfirm'] = 'checked';
    $message = $auto['message'];
    $content ='<div class="qis-settings"><div class="qis-options" style="width:90%;">';
    if (!$qppkey['authorised']) {
        $content .= '<h2 style="color:#B52C00">'.__('Autoresponder', 'loan-calculator').'</h2>
        <p>The autoresponder will send a personalised email to applicants with details of their application.</p>
        <p>The autoresponder is only availabile in the pro version of the plugin.</p>
        <h3><a href="?page=quick-interest-slider/settings.php&tab=upgrade">Upgrade to Pro</a></h3>';
    } else {    
        $content .= '<h2 style="color:#B52C00">'.__('Auto responder settings', 'loan-calculator').'</h2>
        <p>'.__('The Auto Responder will send an email to the Applicant if enabled of if they choose to recieve a copy of their details', 'loan-calculator').'.</p>
        <form method="post" action="">
        <p><input type="checkbox" name="enable"' . $auto['enable'] . ' value="checked" /> '.__('Enable Auto Responder', 'loan-calculator').'.</p>
        <p>'.__('From Name:', 'loan-calculator').' (<span class="description">'.__('Defaults to your', 'loan-calculator').' <a href="'. get_admin_url().'options-general.php">'.__('Site Title', 'loan-calculator').'</a> '.__('if left blank', 'loan-calculator').'.</span>):<br>
        <input type="text" style="width:50%" name="fromname" value="' . $auto['fromname'] . '" /></p>
        <p>'.__('From Email:', 'loan-calculator').' (<span class="description">'.__('Defaults to the', 'loan-calculator').' <a href="'. get_admin_url().'options-general.php">'.__('Admin Email', 'loan-calculator').'</a> '.__('if left blank', 'loan-calculator').'.</span>):<br>
        <input type="text" style="width:50%" name="fromemail" value="' . $auto['fromemail'] . '" /></p>    
        <p>'.__('Subject:', 'loan-calculator').'<br>
        <input style="width:100%" type="text" name="subject" value="' . $auto['subject'] . '"/></p>
        <h2>'.__('Message Content', 'loan-calculator').'</h2>';
        echo $content;
        wp_editor($message, 'message', $settings = array('textarea_rows' => '20','wpautop'=>false));
        $content = '<p>'.__('You can use the following shortcodes in the message body:', 'loan-calculator').'</p>
        <table>
        <tr><th>Shortcode</th><th>'.__('Replacement Text', 'loan-calculator').'</th></tr>
        <tr><td>[name]</td><td>'.__('The registrants name from the form', 'loan-calculator').'</td></tr>
        <tr><td>[amount]</td><td>'.__('The loan amount', 'loan-calculator').'</td></tr>
        <tr><td>[period]</td><td>'.__('The replayment period', 'loan-calculator').'</td></tr>
        <tr><td>[date]</td><td>'.__('The date the application was made', 'loan-calculator').'</td></tr>
        </table>';
        $content .='<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="useregistrationdetails"' . $auto['useregistrationdetails'] . ' value="checked" />&nbsp;'.__('Add the application details to the email', 'loan-calculator').'</p>
        <p>'.__('Registration details blurb', 'loan-calculator').'<br>
        <input type="text" style="" name="registrationdetailsblurb" value="' . $auto['registrationdetailsblurb'] . '" /></p>
        <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the auto responder settings?\' );"/></p>';
        $content .= wp_nonce_field("save_qis");
        $content .= '</form>';
    }
    $content .='</div></div>';
    echo $content;
}

// Upgrade
function qis_upgrade () {
    if( isset( $_POST['Upgrade']) && check_admin_referer("save_qis")) {
        $page_url = qis_current_page_url();
        $ajaxurl = admin_url('admin-ajax.php');
        $page_url = (($ajaxurl == $page_url) ? $_SERVER['HTTP_REFERER'] : $page_url);
        $qppkey = array('key' => md5(mt_rand()));
        update_option('qpp_key', $qppkey);
        $form = '<h2>Waiting for PayPal...</h2>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="qpupgrade" id="qpupgrade">
        <input type="hidden" name="item_name" value="Interest Slider Upgrade"/>
        <input type="hidden" name="upload" value="1">
        <input type="hidden" name="business" value="mail@quick-plugins.com">
        <input type="hidden" name="return" value="https://quick-plugins.com/quick-paypal-payments/quick-paypal-payments-authorisation-key/?key='.$qppkey['key'].'&email='.get_option('admin_email').'">
        <input type="hidden" name="cancel_return" value="'.$page_url.'">
        <input type="hidden" name="currency_code" value="USD">
        <input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="quantity" value="1">
        <input type="hidden" name="amount" value="10.00">
        <input type="hidden" name="notify_url" value = "'.site_url('/?qis_upgrade_ipn').'">
        <input type="hidden" name="custom" value="'.$qppkey['key'].'">
        </form>
        <script language="JavaScript">document.getElementById("qpupgrade").submit();</script>';
        echo $form;
    }
    
    if( isset( $_POST['Lost']) && check_admin_referer("save_qis")) {
        $email = get_option('admin_email');
        $qppkey = get_option('qpp_key');
        $headers = "From: Quick Plugins <mail@quick-plugins.com>\r\n"
. "MIME-Version: 1.0\r\n"
. "Content-Type: text/html; charset=\"utf-8\"\r\n";	
        $message = '<html><p>Your Quick Plugins authorisation key is:</p><p>'.$qppkey['key'].'</p></html>';
        wp_mail($email,'Quick Plugins Authorisation Key',$message,$headers);
        qis_admin_notice('Your auth key has been sent to '.$email);
    }

    if( isset( $_POST['Check']) && check_admin_referer("save_qis")) {
        $qppkey = get_option('qpp_key');    
        if ($_POST['key'] == $qppkey['key'] || $_POST['key'] == 'jamsandwich' || $_POST['key'] == '2d1490348869720eb6c48469cce1d21c') {
            $qppkey['key'] = $_POST['key'];
            $qppkey['authorised'] = true;
            update_option('qpp_key', $qppkey);
            qis_admin_notice(__('Your key has been accepted', 'multipay'));
        } else {
            qis_admin_notice(__('The key is not correct, please try again', 'multipay'));
        }
    }
    
    if( isset( $_POST['Delete']) && check_admin_referer("save_qis")) {
        $qppkey = get_option('qpp_key');
        $qppkey['authorised'] = '';
        update_option('qpp_key',$qppkey);
        qis_admin_notice(__('Your key has been deleted', 'multipay'));
    }
    
    $qppkey = get_option('qpp_key');
    $content = '<form id="" method="post" action="">';
    if (!$qppkey['authorised']) {
        $content .= '<div class="qis-settings"><div class="qis-options" style="width:90%;">
        <h2 style="color:#B52C00">Upgrade</h2>
        <p>Upgrading to the Pro Version of the plugin allows you to add an application form to the loan calculator.</p>        
        <p>Visitors will be able to choose the loan they want and apply for that loan. You have a number of fields you can select and the option to send a confirmation email to the applicant.</p>
        <p>You can review all applications and email the complete list to yourself.</p>
        <p>All for $10. Which I think is a bit of a bargain.</p>
        <p>Activation is automatic, as soon as you have paid the plugin will upgrade allowing access to the registration form, autoresponder and application list. You will also receive an email with your authorisation key that you can use on all applicable <a href="https://quick-plugins.com" target="_blank">Quick Plugins</a>.</p>
        <p><input type="submit" name="Upgrade" class="button-primary" style="color: #FFF;" value="'.__('Upgrade to Pro', 'quick-paypal-payments').'" /></p>
        <h2>Activate</h2>
        <p>Enter the authorisation key below and click on the Activate button:<br>
        <input type="text" style="width:50%" name="key" value="" /><br><input type="submit" name="Check" class="button-secondary" value="'.__('Activate', 'quick-paypal-payments').'" />';
       
    } else {
        $content .= '<p>You already have the Pro version of the plugin.</p>
        <p>Your authorisation key is: '. $qppkey['key'] .'</p>
        <p><input type="submit" name="Delete" class="button-secondary" value="'.__('Delete Key', 'quick-paypal-payments').'" /></p>';
    }
    $content .= wp_nonce_field("save_qis");
    $content .= '</form>';
    $content .= '</div></div>
    </div>';
    echo $content;
}

function qis_admin_notice($message) {if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';}

function qis_scripts_init() {
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_media();
    wp_enqueue_script('qis-media', plugins_url('settings-media.js', __FILE__ ), array( 'jquery','wp-color-picker' ), false, true );
    wp_enqueue_script( 'qis_script',plugins_url('quick-interest-slider.js', __FILE__));
    wp_enqueue_style( 'qis_settings',plugins_url('settings.css', __FILE__));
    wp_enqueue_style( 'qis_style',plugins_url('quick-interest-slider.css', __FILE__));
    }

add_action('admin_enqueue_scripts', 'qis_scripts_init');
