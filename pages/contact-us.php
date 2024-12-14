<h1>Contact us</h1>

<div class="page-content-section">
	<form id="contact-us-form" method="post" action="<?php echo WEB_INDEX_FILE; ?>">
		<input type="hidden" name="page" value="contact_us" />
		<input type="hidden" name="contact-us-send-message" value="1" />
<?php
	foreach ($do['urlData'] as $key => $value) {
		echo '<input type="hidden" name="'.$key.'" value="'.$do['urlKeyValuePairs'][$key].'" />';
	}
?>
	<table>
		<tr><td colspan="2">If you have any questions or comments about the content presented on this site please write to us.</td></tr>
<?php
	$is_error = false;
	$user_name = $user_email = $user_message = $user_sendcopy = '';
	if (isset($do['contact-us']['error'])) {
		echo '<tr><td colspan="2"><p style="color:red;">'.$do['contact-us']['error'].'</p></td></tr>';
		$is_error = true;
		$user_name 		= $do['contact-us']['data']['name'];
		$user_email 	= $do['contact-us']['data']['email'];
		$user_message 	= $do['contact-us']['data']['message'];
		$user_sendcopy 	= $do['contact-us']['data']['sendcopy'];
	}
	if (isset($do['contact-us']['message'])) {
		echo '<tr><td colspan="2"><p style="color:green;">'.$do['contact-us']['message'].'</p></td></tr>';
	}
?>
		<tr><td>Name</td>
			<td><input type="text" id="contact-us-name" name="contact-us-name" maxlength="100" size="40"
						title="Enter your name here" value="<?php echo $user_name; ?>"></td>
		</tr>
		<tr><td>Email</td>
			<td><input type="text" id="contact-us-email" name="contact-us-email" maxlength="100" size="40"
						title="Enter your email address here" value="<?php echo $user_email; ?>"></td>
		</tr>
		<tr><td>Message</td>
			<td><textarea id="contact-us-message" name="contact-us-message" rows="20" cols="30"><?php echo $user_message; ?></textarea></td>
		</tr>
		<tr><td></td>
			<td><input type="checkbox" name="contact-us-sendcopy" id="contact-us-sendcopy" 
					<?php echo ($user_sendcopy === true) ? ' checked="checked"' : ''; ?> />
					Send a copy of this email to yourself</td>
		</tr>
		<tr><td></td>
			<td><a href="javascript:submit_form('contact-us-form')" class="button" style="float:left;">
					<span class="label" style="color:#000111;">Send</span>
				</a></td>
		</tr>
	</table>
	</form>
</div>