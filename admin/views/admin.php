<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 */
?>

<?php
if ( !empty($_POST) ) {
	check_admin_referer('sya');
	@update_option("sya_dateformat", (string)$_POST['sya_dateformat']);
	@update_option("sya_datetitleseperator", (string)$_POST['sya_datetitleseperator']);
	@update_option("sya_linkyears", (bool)!empty($_POST['sya_linkyears']));
	@update_option("sya_collapseyears", (bool)!empty($_POST['sya_collapseyears']));
	@update_option("sya_postcount", (bool)!empty($_POST['sya_postcount']));
	@update_option("sya_commentcount", (bool)!empty($_POST['sya_commentcount']));
	@update_option("sya_linktoauthor", (bool)!empty($_POST['sya_linktoauthor']));
	@update_option("sya_reverseorder", (bool)!empty($_POST['sya_reverseorder']));
	@update_option("sya_prepend", (string)$_POST['sya_prepend']);
	@update_option("sya_append", (string)$_POST['sya_append']);
	@update_option("sya_excerpt", (bool)!empty($_POST['sya_excerpt']));
	@update_option("sya_excerpt_indent", $_POST['sya_excerpt_indent']);
	@update_option("sya_excerpt_maxchars", $_POST['sya_excerpt_maxchars']);
	@update_option("sya_show_categories", (bool)!empty($_POST['sya_show_categories']));
	@update_option("sya_showauthor", (bool)!empty($_POST['sya_showauthor']));
	@update_option("sya_showyearoverview", (bool)!empty($_POST['sya_showyearoverview']));
	@update_option("sya_dateformatchanged2012", 1);
	@update_option("sya_dateformatchanged2015", 1);
	@update_option("sya_showpostthumbnail", (bool)!empty($_POST['sya_showpostthumbnail']));
	@update_option("sya_postthumbnail_size", (string)$_POST['sya_postthumbnail_size']);

	$successmessage = __('Settings successfully updated!', $this->text_domain);

	echo '<div id="message" class="updated fade">
			<p>
				<strong>
					' . $successmessage . '
				</strong>
			</p>
		</div>';
}
?>

<div class="wrap">

	<h2><?php _e('Simple Yearly Archive Options', $this->plugin_slug); ?></h2>

	<form name="sya_form" action="" method="post">
		<?php if( function_exists('wp_nonce_field') ) wp_nonce_field('sya'); ?>
		<div id="poststuff" class="ui-sortable">
			<div id="sya_customize_box" class="postbox if-js-open">
				<h3>
					<?php _e('Customize the archive output', $this->text_domain); ?>
				</h3>
				<input type="hidden" name="action" value="edit" />
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Date format', $this->text_domain); ?></th>
					<td>
						<input type="text" name="sya_dateformat" class="text" value="<?php echo stripslashes(get_option('sya_dateformat')) ?>" />
						<label for="inputid"><br />
							<small>(<?php printf(__('Check <a href="%s" target="_blank">here</a> for date formatting', $this->text_domain), 'http://php.net/manual/function.date.php'); ?>)</small></label>
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Seperator between date and post title', $this->text_domain); ?></th>
					<td>
						<input type="text" name="sya_datetitleseperator" class="text" value="<?php echo stripslashes(get_option('sya_datetitleseperator')) ?>" />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Before / After (Year headline)', $this->text_domain); ?></th>
					<td>
						<input type="text" name="sya_prepend" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_prepend')) ?>" /> | <input type="text" name="sya_append" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_append')) ?>" />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Linked years?', $this->text_domain); ?></th>
					<td>
						<input type="checkbox" name="sya_linkyears" id="sya_linkyears" value="1" <?php echo (get_option('sya_linkyears')) ? ' checked="checked"' : '' ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top">
						<?php _e('Collapsible years?', $this->text_domain); ?><br />
						<small><em>(<?php _e('Disables the "Linked years?" option', $this->text_domain); ?>)</em></small>
					</th>
					<td>
						<input type="checkbox" name="sya_collapseyears" id="sya_collapseyears" value="1" <?php echo (get_option('sya_collapseyears')) ? ' checked="checked"' : '' ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Anchored overview at the top?', $this->text_domain); ?></th>
					<td>
						<input type="checkbox" name="sya_showyearoverview" id="sya_showyearoverview" value="1" <?php echo (get_option('sya_showyearoverview')) ? ' checked="checked"' : '' ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Show post count for each year?', $this->text_domain); ?></th>
					<td>
						<input type="checkbox" name="sya_postcount" id="sya_postcount" value="1" <?php echo (get_option('sya_postcount')) ? ' checked="checked"' : '' ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Show comments count for each post?', $this->text_domain); ?></th>
					<td>
						<input type="checkbox" name="sya_commentcount" id="sya_commentcount" value="1" <?php echo (get_option('sya_commentcount')) ? ' checked="checked"' : '' ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Show categories after each post?', $this->text_domain); ?></th>
					<td>
						<input type="checkbox" name="sya_show_categories" id="sya_show_categories" value="1" <?php echo (get_option('sya_show_categories')) ? ' checked="checked"' : '' ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Show post author after each post?', $this->text_domain); ?></th>
					<td>
						<input type="checkbox" name="sya_showauthor" id="sya_showauthor" value="1" <?php echo (get_option('sya_showauthor')) ? ' checked="checked"' : '' ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Show optional Excerpt (if available)?', $this->text_domain); ?></th>
					<td>
						<input type="checkbox" name="sya_excerpt" id="sya_excerpt" value="1" <?php echo (get_option('sya_excerpt')) ? ' checked="checked"' : '' ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><div style="padding-left:20px;">-- <?php _e('Max. chars of Excerpt (0 for default)', $this->text_domain); ?></div></th>
					<td>
						<input type="text" name="sya_excerpt_maxchars" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_excerpt_maxchars')) ?>" <?php echo (get_option('sya_excerpt') ? '' : 'readonly="readonly"') ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><div style="padding-left:20px;">-- <?php _e('Indentation of Excerpt (in px)', $this->text_domain); ?></div></th>
					<td>
						<input type="text" name="sya_excerpt_indent" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_excerpt_indent')) ?>" <?php echo (get_option('sya_excerpt') ? '' : 'readonly="readonly"') ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><?php _e('Show post thumbnail (if available)?', $this->text_domain); ?></th>
					<td>
						<input type="checkbox" name="sya_showpostthumbnail" id="sya_showpostthumbnail" value="1" <?php echo (get_option('sya_showpostthumbnail')) ? ' checked="checked"' : '' ?> />
					</td>
				</tr>
				</table>
				<table class="form-table">
				<tr>
					<th scope="row" valign="top"><div style="padding-left:20px;">-- <?php _e('Post thumbnail size', $this->text_domain); ?><br />(<?php printf(__('Configure <a href="%s" target="_blank">here</a>!', $this->text_domain), admin_url('options-media.php')); ?>)</div></th>
					<td>
						<select size="1" name="sya_postthumbnail_size" id="sya_postthumbnail_size" <?php echo (get_option('sya_showpostthumbnail') ? '' : 'disabled="disabled"') ?>>
						<?php
						$sya_image_sizes = get_intermediate_image_sizes();
						foreach( $sya_image_sizes as $sya_image_size ) {
							if( get_option( $sya_image_size . '_size_w' ) != '' ) {
								$_cur = ($sya_image_size == get_option('sya_postthumbnail_size') ? ' selected="selected"' : '');
								echo '<option' . $_cur . ' value="' . $sya_image_size . '">' . $sya_image_size . '</option>';
							}
						}
						?>
						</select>
					</td>
				</tr>
				</table>
				<p class="submit" style="margin-left:10px;">
					<input type="submit" name="submit" value="<?php _e('Update Options', $this->text_domain); ?> &raquo;" class="button button-primary" />
				</p>
			</div>
		</div>
		<div id="poststuff" class="ui-sortable">
			<div id="sya_misc_box" class="postbox if-js-open">
			<h3>
			<?php _e('Miscellaneous Options', $this->text_domain); ?>
			</h3>
			<table class="form-table">
			<tr>
				<th scope="row" valign="top"><?php _e('Link back to my website in plugin footer? :)', $this->text_domain); ?></th>
				<td>
					<input type="checkbox" name="sya_linktoauthor" id="sya_linktoauthor" value="1" <?php echo (get_option('sya_linktoauthor')) ? ' checked="checked"' : '' ?> />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><?php _e('Reverse order?', $this->text_domain); ?></th>
				<td>
					<input type="checkbox" name="sya_reverseorder" id="sya_reverseorder" value="1" <?php echo (get_option('sya_reverseorder')) ? ' checked="checked"' : '' ?> />
				</td>
			</tr>
			</table>
			<p class="submit" style="margin-left:10px;">
				<input type="submit" name="submit" value="<?php _e('Update Options', $this->text_domain); ?> &raquo;" class="button button-primary" />
			</p>
			</div>
		</div>
	</form>
  	<div id="poststuff" class="ui-sortable">
	  	<div id="sya_plugins_box" class="postbox if-js-open">
		  	<h3>
		    	<?php _e('More of my WordPress plugins', $this->text_domain); ?>
		  	</h3>
			<table class="form-table">
			<tr>
				<td>
					<?php _e('You may also be interested in some of my other plugins:', $this->text_domain); ?>
					<p id="authorplugins-wrap"><input id="authorplugins-start" value="<?php _e('Show other plugins by this author inline &raquo;', $this->text_domain); ?>" class="button-secondary" type="button"></p>
					<div id="authorplugins-wrap">
						<div id='authorplugins'>
							<div class='authorplugins-holder full' id='authorplugins_secondary'>
								<div class='authorplugins-content'>
									<ul id="authorpluginsul">

									</ul>
									<div class="clear"></div>
								</div>
							</div>
						</div>
					</div>
					<?php _e('More plugins at: <a class="button rbutton" href="http://www.schloebe.de/portfolio/" target="_blank">www.schloebe.de</a>', $this->text_domain); ?>
				</td>
			</tr>
			</table>
		</div>
	</div>
	<div id="poststuff" class="ui-sortable">
		<div id="sya_help_box" class="postbox if-js-open">
			<h3>
				<?php _e('Help', $this->text_domain); ?>
			</h3>
			<table class="form-table">
		 		<tr>
		 			<td>
		 				<?php _e('If you are new to using this plugin or cant understand what all these settings do, please read the documentation at <a href="http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/" target="_blank">http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/</a>', $this->text_domain); ?>
		 			</td>
		 		</tr>
			</table>
		</div>
	</div>
  	<div id="poststuff" class="ui-sortable">
	  	<div id="sya_about_box" class="postbox if-js-open">
	  	<?php
		$sya_plugindata = get_plugin_data( $this->plugin_path . 'simple-yearly-archive.php' );
		$sya_plugin =  sprintf(
			'%1$s | ' . __('Version'). ' %2$s | ' . __('Author') . ': %3$s',
			$sya_plugindata['Title'],
			$sya_plugindata['Version'],
			$sya_plugindata['Author']
		);
		?>
		<h3>
			<?php _e('About Simple Yearly Archive', $this->text_domain); ?>
		</h3>
		<table class="form-table">
		<tr>
			<td>
				<?php echo $sya_plugin; ?>
			</td>
		</tr>
		</table>
		</div>
	</div>
</div>