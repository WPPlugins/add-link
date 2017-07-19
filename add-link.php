<?php
/*
Plugin Name: Add Link
Plugin URI: http://blogs.ubc.ca/support/plugins/add-links-widget/
Description: Adds a sidebar widget to submit links to blogroll
Author: OLT UBC
Version: 0.3
Author URI: http://olt.ubc.ca
Pl
*/

function vn_add_link_init()
{	
	// check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	// this prints the widget
	function vn_add_link($args)
	{
		// include WordPress "core" for "wp_insert_link" to work
		$root = preg_replace('/wp-content.*/', '', __FILE__);
		require_once($root . 'wp-config.php');
		require_once($root . 'wp-admin/includes/admin.php');
			
		extract($args);
			
		$options = get_option('vn_add_link_widget');
		$errors = array('passwordError'=>FALSE, 'addError'=>FALSE);
		
		if($_POST['addLinkSubmit'])
		{
			$nonce= $_REQUEST['addlink-nonce'];
			if (!wp_verify_nonce($nonce, 'addlink-nonce') )
				die('Security check failed. Please use the back button and try resubmitting the information.');
				
			if($options['useAddLinkPassword'] && ($_POST['addLinkPassword'] != $options['addLinkPassword']))
				$errors['passwordError'] = TRUE;
			else
			{
				if(class_exists("FeedWordPress"))
					$categoryID = FeedWordPress :: link_category_id();
				else
					$categoryID = get_option('default_link_category');
				
				$link = strip_tags(stripslashes($_POST['addLinkLink']));
				
				// add link; if link not added then 0 will be returned
				$linkID = wp_insert_link(array(
					"link_name" => $link,
					"link_url" => $link,
					"link_category" => array($categoryID)
					));
				
				if($linkID == 0)
					$errors['addError'] = TRUE;
				else
					$errors['addError'] = FALSE;
			}
		}
		
		$addLinkTitle = $options['addLinkTitle'];
		$addLinkMessage = $options['addLinkMessage'];
		$useAddLinkPassword = $options['useAddLinkPassword'];
		$addLinkAlign = $options['addLinkAlign'];
		
		echo $before_widget . $before_title . $addLinkTitle . $after_title;
		?>
			<div style="text-align: <?php print($addLinkAlign); ?>;">
			<p>
				<form method="post" action="<?php echo $_SERVER[PHP_SELF]; ?>">

					<?php
					// show error messages if necessary
					if($_POST['addLinkSubmit'])
					{
						if($errors['passwordError'])
							print("<p><strong>Password incorrect!</strong></p>");
						elseif($errors['addError'])
							print("<p><strong>Not added!</strong></p>");
						else
							print("<p><strong>Added successfully!</strong></p>");
					}
					?>
										
					<p>
						<?php print($addLinkMessage); ?><br />
						<input type="text" name="addLinkLink" id="addLinkLink" value="<?php if($errors['passwordError'] || $errors['addError']) echo $_POST['addLinkLink']; else echo 'http://'; ?>" style="width: 145px; border: #000000 1px solid;" />
					</p>
					
					<?php if($options['useAddLinkPassword']) { ?>
						<p>
							Password:<br />
							<input type="password" name="addLinkPassword" id="addLinkPassword" value="" style="width: 145px; border: #000000 1px solid;" />
						</p>
					<?php } ?>
					<input type="hidden" name="addlink-nonce" value="<?php echo wp_create_nonce('addlink-nonce'); ?>" />
					<input type="submit" id="addLinkSubmit" name="addLinkSubmit" value="<?php echo $options['addLinkButtonText']; ?>"/>	
				
				</form>
			</p>
			</div>
		<?php
		
		echo $after_widget;
	}
	
	// widget options
	function vn_add_link_control()
	{
		// get our options and see if we're handling a form submission.
		$options = get_option('vn_add_link_widget');
		
		if(!is_array($options)) // default values
			$options = array('addLinkTitle'=>'Add Link', 'addLinkMessage'=>'Link:', 'useAddLinkPassword'=>TRUE, 'addLinkPassword'=>'passw0rd', 'addLinkAlign'=>'left', 'addLinkButtonText'=>'Add Link');

		if($_POST['vn_add_link_submit'])
		{
			$nonce= $_REQUEST['addlink-nonce'];
			if (!wp_verify_nonce($nonce, 'addlink-nonce') )
				die('Security check failed. Please use the back button and try resubmitting the information.');
				
			// remember to sanitize and format user input appropriately.
			$options['addLinkTitle'] = strip_tags(stripslashes($_POST['addLinkTitle']));
			$options['addLinkMessage'] = strip_tags(stripslashes($_POST['addLinkMessage']));
			
			if($_POST['useAddLinkPassword'] == "yes")
				$options['useAddLinkPassword'] = TRUE;
			else
				$options['useAddLinkPassword'] = FALSE;
			
			$options['addLinkPassword'] = $_POST['addLinkPassword'];
			$options['addLinkAlign'] = $_POST['addLinkAlign'];
			$options['addLinkButtonText'] = $_POST['addLinkButtonText'];
			
			update_option('vn_add_link_widget', $options);
		}
		?>
		<p>
			Widget Alignment:<br />
			<select id="addLinkAlign" name="addLinkAlign">
					<option value="left" <?php if($options['addLinkAlign'] == 'left') print('selected="selected"'); ?>>Left</option>
					<option value="center" <?php if($options['addLinkAlign'] == 'center') print('selected="selected"'); ?>>Centre</option>
					<option value="right" <?php if($options['addLinkAlign'] == 'right') print('selected="selected"'); ?>>Right</option>
			</select>
		</p>
		
		<p>
			<label for="addLinkTitle">Title:</label><br /><input style="width: 255px;" id="addLinkTitle" name="addLinkTitle" type="text" value="<?php echo $options['addLinkTitle']; ?>" />
		</p>
		
		<p>Message (type "&lt;br /&gt;" without quotes for a new line):<br /><textarea id="addLinkMessage" name="addLinkMessage" style="width: 318px; height: 150px;"><?php echo $options['addLinkMessage']; ?></textarea></p>
		
		<p>
			<input type="checkbox" name="useAddLinkPassword" id="useAddLinkPassword" value="yes" onChange="jQuery('#addLinkPasswordTextBox').toggle(); return false;" <?php if($options['useAddLinkPassword']) echo 'checked'; ?> />
			<label for="useAddLinkPassword">Use Password</label>
		</p>
		
		<div id="addLinkPasswordTextBox" style="<?php if(!$options['useAddLinkPassword']) echo 'display: none;'; ?> margin-left: 25px;">
			<p><label for="addLinkPassword">Password:</label><br /><input id="addLinkPassword" style="width: 255px;" type="text" name="addLinkPassword" value="<?php echo $options['addLinkPassword']; ?>" /></p>
		</div>
		
		<p>
			<label for="addLinkButtonText">Button Text:</label><br /><input style="width: 255px;" id="addLinkButtonText" name="addLinkButtonText" type="text" value="<?php echo $options['addLinkButtonText']; ?>" />
		</p>
		<input type="hidden" name="addlink-nonce" value="<?php echo wp_create_nonce('addlink-nonce'); ?>" />
		<input type="hidden" id="vn_add_link_submit" name="vn_add_link_submit" value="yes" />
		<?php	
	}

	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget(array('Add Link', 'widgets'), 'vn_add_link');
	
	// this registers the widget control form
	register_widget_control(array('Add Link', 'widgets'), 'vn_add_link_control', 335, 700);
}

// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('widgets_init', 'vn_add_link_init');

?>
