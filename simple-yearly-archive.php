<?php
/*
Plugin Name: Simple Yearly Archive
Version: 0.97
Plugin URI: http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/
Description: A simple, clean yearly list of your archives.
Author: Oliver Schl&ouml;be
Author URI: http://www.schloebe.de/
*/

define("SYA_VERSION", "0.97");

function get_simpleYearlyArchive($format, $excludeCat) {

    global $wpdb, $PHP_SELF, $wp_version;
    setlocale(LC_ALL,WPLANG);
    $now = gmdate("Y-m-d H:i:s",(time()+((get_settings('gmt_offset'))*3600)));
    (!isset($wp_version)) ? $wp_version = get_bloginfo('version') : $wp_version = $wp_version;
	
	if (($format == 'yearly') || ($format == '')) {
		$modus = "";
	} else if($format == 'yearly_act') {
		$modus = " AND year($wpdb->posts.post_date) = " . date('Y');
	} else if($format == 'yearly_past') {
		$modus = " AND year($wpdb->posts.post_date) < " . date('Y');
	} else if(preg_match("/^[0-9]{4}$/", $format)) {
		$modus = " AND year($wpdb->posts.post_date) = '" . $format . "'";
	}
	
	$ausgabe .= "<div class=\"sya_container\" id=\"sya_container\">";
	
	$jahreMitBeitrag = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.post_date, year($wpdb->posts.post_date) AS `year`, COUNT(ID) as posts FROM $wpdb->posts,$wpdb->term_taxonomy,$wpdb->term_relationships WHERE $wpdb->posts.post_type = 'post' AND $wpdb->posts.post_status = 'publish'" . $modus . " GROUP BY year($wpdb->posts.post_date) ORDER BY $wpdb->posts.post_date DESC");

	foreach ($jahreMitBeitrag as $aktuellesJahr) {
		for ($aktuellerMonat = 1; $aktuellerMonat <= 12; $aktuellerMonat++) {
			
			$monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat] = $wpdb->get_results("SELECT ID, post_date, post_title, post_excerpt, comment_count FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND year(post_date) = '$aktuellesJahr->year' ORDER BY post_date desc");
		}
	}
	
	if (($format == 'yearly') || ($format == 'yearly_act') || ($format == 'yearly_past') || ($format == '') || (preg_match("/^[0-9]{4}$/", $format))) {
	$before = get_option('sya_prepend');
	$after = get_option('sya_append');
    ((get_option('sya_excerpt_indent')=='') ? $indent = '0' : $indent = get_option('sya_excerpt_indent'));
	((get_option('sya_excerpt_maxchars')=='') ? $maxzeichen = '0' : $maxzeichen = get_option('sya_excerpt_maxchars'));
	
	if ($jahreMitBeitrag) {
		if ($excludeCat != '') { // es gibt auszuschlie&szlig;ende Kategorien
		$excludeCats = explode(",", $excludeCat);
	
		foreach($jahreMitBeitrag as $aktuellesJahr) {
  			
  			$aktuellerMonat = 1;
    		while ($aktuellerMonat >= 1) {
		
					if(get_option('sya_linkyears')=='1') {
						$linkyears_prepend = '<a href="' . get_year_link($aktuellesJahr->year) . '">';
						$linkyears_append = '</a>';
					} else {
						$linkyears_prepend = '';
						$linkyears_append = '';
					}

    				if ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat]) {
						//$ausgabe .= $before;
    					//$ausgabe .= ('' . $linkyears_prepend . $aktuellesJahr->year . $linkyears_append . '');
						
						/*if(get_option('sya_postcount')==TRUE) {
							$postcount = count($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat]);
    						$ausgabe .= ' <span style="font-weight:200;">(' . $postcount . ')</span>';
    					}*/
						//$ausgabe .= $after;
						//$ausgabe .= '<ul>';
						$listitems = '';
    						
    					foreach ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat] as $post) {
						if ($post->post_date <= $now) {
	
							if($wp_version >= '2.3') {
								$wpcats_query = "SELECT $wpdb->term_taxonomy.term_id FROM $wpdb->term_taxonomy,$wpdb->term_relationships,$wpdb->posts WHERE $wpdb->term_relationships.object_id = ".$post->ID." AND $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id";
							} else {
								$wpcats_query = "SELECT category_id FROM $wpdb->post2cat WHERE post_id = ".$post->ID."";
							}
		
    						$cats = $wpdb->get_col($wpcats_query);
							$match = false;
							$aktdatum = get_the_time();
							$wp_dateformat = get_option('date_format');
        						    
							foreach ($cats as $cat) {
								if (in_array($cat, $excludeCats)) {
									$match = true;
								}
							}
        	                        
							if (!$match) {
    							//$sya_commentcount = $wpdb->get_row("SELECT COUNT(comment_ID) AS commentcount FROM " . $wpdb->comments . " WHERE `comment_post_ID` = " . $post->ID . "");
    							$langtitle = $post->post_title;
    							$langtitle = apply_filters("the_title", $post->post_title);
    							$listitems .= '<li>';
								$listitems .= ('' . date(get_option('sya_dateformat'),strtotime($post->post_date)) . ' ' . get_option('sya_datetitleseperator') . ' <a href="' . get_permalink($post->ID) . '" title="' . $post->post_title . '">' . $langtitle . '</a>');

								if(get_option('sya_commentcount')==TRUE) {
									//$listitems .= ' (' . $sya_commentcount->commentcount . ')';
									$listitems .= ' (' . $post->comment_count . ')';
								}
								if(get_option('sya_excerpt')==TRUE) {
									if ( $maxzeichen != '0' ) {
										if ( !empty($post->post_excerpt) ) {
											$excerpt = substr($post->post_excerpt, 0, strrpos(substr($post->post_excerpt, 0, $maxzeichen), ' ')) . '...';
										}
									} else {
										$excerpt = $post->post_excerpt;
									}
									$listitems .= '<br /><div style="padding-left:'.$indent.'px" class="robots-nocontent"><cite>' . strip_tags($excerpt) . '</cite></div>';
								}
								$listitems .= '</li>';
								$excerpt = '';
							}
						}
						}
						
    					//$ausgabe .= '</ul>';
						if (strlen($listitems) > 0) {
							$ausgabe .= $before. $linkyears_prepend . $aktuellesJahr->year . $linkyears_append;
							if(get_option('sya_postcount')==TRUE) {
								$postcount = count($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat]);
								$ausgabe .= ' <span style="font-weight:200;">(' . $postcount . ')</span>';
							}
							$ausgabe .= $after.'<ul>'.$listitems.'</ul>';
						}
				}
    			$aktuellerMonat--;
    		}
    		}
    		
    		} else { // es gibt keine auszuschlie&szlig;enden Kategorien
    		
			foreach($jahreMitBeitrag as $aktuellesJahr) {
    				
    				$aktuellerMonat = 1;
    				while ($aktuellerMonat >= 1) {
		
						if(get_option('sya_linkyears')==TRUE) {
							$linkyears_prepend = '<a href="' . get_year_link($aktuellesJahr->year) . '">';
							$linkyears_append = '</a>';
						} else {
							$linkyears_prepend = '';
							$linkyears_append = '';
						}
		
						if(get_option('sya_linkyears')==TRUE) {
							$linkyears_prepend = '<a href="' . get_year_link($aktuellesJahr->year) . '">';
							$linkyears_append = '</a>';
						} else {
							$linkyears_prepend = '';
							$linkyears_append = '';
						}
					
    					if ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat]) {
    						//$ausgabe .= get_archives_link(get_month_link($aktuellesJahr->year, $aktuellerMonat), $monthNames[$aktuellerMonat] . ' ' . $aktuellesJahr->year, '', $before, $after);
							//$ausgabe .= $before;
							//$ausgabe .= (''.$linkyears_prepend.$aktuellesJahr->year.$linkyears_append.'');
							
							if(get_option('sya_postcount')==TRUE) {
								$postcount = count($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat]);
    							//$ausgabe .= ' <span style="font-weight:200;">(' . $postcount . ')</span>';
    						}
    						//$ausgabe .= $after;
    						//$ausgabe .= '<ul>';
							$listitems = '';
    						
    						foreach ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat] as $post) {
    							
    							//$sya_commentcount = $wpdb->get_row("SELECT COUNT(comment_ID) AS commentcount FROM " . $wpdb->comments . " WHERE `comment_post_ID` = " . $post->ID . "");
    							$langtitle = $post->post_title;
    							$langtitle = apply_filters("the_title", $post->post_title);
    							$listitems .= '<li>';
    							$image = get_post_meta($post->ID, 'post_thumbnail', true);
								$listitems .= ('' . date(get_option('sya_dateformat'),strtotime($post->post_date)) . ' ' . get_option('sya_datetitleseperator') . ' <a href="' . get_permalink($post->ID) . '" title="' . $post->post_title . '">' . $langtitle . '</a>');

								if(get_option('sya_commentcount')==TRUE) {
									//$listitems .= ' (' . $sya_commentcount->commentcount . ')';
									$listitems .= ' (' . $post->comment_count . ')';
								}
								if(get_option('sya_excerpt')==TRUE) {
									if ( $maxzeichen != '0' ) {
										if ( !empty($post->post_excerpt) ) {
											$excerpt = substr($post->post_excerpt, 0, strrpos(substr($post->post_excerpt, 0, $maxzeichen), ' ')) . '...';
										}
									} else {
										$excerpt = $post->post_excerpt;
									}
									$listitems .= '<br /><div style="padding-left:'.$indent.'px" class="robots-nocontent"><cite>' . strip_tags($excerpt) . '</cite></div>';
								}
								$listitems .= '</li>';
								$excerpt = '';
							}
							//$ausgabe .= '</ul>';
							if (strlen($listitems) > 0) {
								$ausgabe .= $before.$linkyears_prepend.$aktuellesJahr->year.$linkyears_append;
								if(get_option('sya_postcount')==TRUE) {
									$postcount = count($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat]);
									$ausgabe .= ' <span style="font-weight:200;">(' . $postcount . ')</span>';
								}
								$ausgabe .= $after.'<ul>'.$listitems.'</ul>';
							}
    					}
    					$aktuellerMonat--;
    				}
			}
		}
	}
	}
	
	if(get_option('sya_linktoauthor')==TRUE) {
		$linkvar = __('Plugin by') . ' <a href="http://www.schloebe.de" target="_blank">Oliver Schl&ouml;be</a>';
		$ausgabe .= '<div align="right"><small>'.$linkvar.'</small></div>';
	}
	
	$ausgabe .= "</div>";
	
	return $ausgabe;
}

function simpleYearlyArchive($format='yearly', $excludeCat='') {
	echo get_simpleYearlyArchive($format, $excludeCat);
}

function sya_header() {
	echo "\n".'<!-- Using Simple Yearly Archive Plugin v'.SYA_VERSION.' | http://www.schloebe.de/wordpress/ // -->'."\n";
}

add_action('admin_menu', 'sya_add_optionpages');
add_action('wp_head', 'sya_header');

function set_default_options() {
	add_option('sya_dateformat', 'd.m.');
	add_option('sya_datetitleseperator', '-');
	add_option('sya_prepend', '<h2>');
	add_option('sya_append', '</h2>');
	add_option('sya_linkyears', 1);
	add_option('sya_postcount', 0);
	add_option('sya_commentcount', 0);
	add_option('sya_linktoauthor', 1);
	add_option('sya_excerpt', 0);
	add_option('sya_excerpt_indent', '0');
	add_option('sya_excerpt_maxchars', '0');
}

load_plugin_textdomain('simple-yearly-archive','wp-content/plugins');

function sya_add_optionpages() {
	set_default_options();

    add_options_page(__('Simple Yearly Archive Options', 'simple-yearly-archive'), __('Simple Yearly Archive', 'simple-yearly-archive'), 8, __FILE__, 'sya_options_page');
}

/*function sya_inline($content) {	
	if(!preg_match('|<!--simple-yearly-archive-->|', $content)) return $content;
	return str_replace('|<!--simple-yearly-archive-->|', simpleYearlyArchive(), $content);
}*/

function sya_inline($post) {	
	if (substr_count($post, '<!--simple-yearly-archive-->') > 0) {
		$sya_archives = get_simpleYearlyArchive($format, $excludeCat);
		$post = str_replace('<!--simple-yearly-archive-->', $sya_archives, $post);
	}
	return $post;
}

add_action('the_content', 'sya_inline', 1);

function sya_options_page() {

	if (isset($_POST['action']) === true) {
		update_option("sya_dateformat", (string)$_POST['sya_dateformat']);
		update_option("sya_datetitleseperator", (string)$_POST['sya_datetitleseperator']);
		update_option("sya_linkyears", (bool)$_POST['sya_linkyears']);
		update_option("sya_postcount", (bool)$_POST['sya_postcount']);
		update_option("sya_commentcount", (bool)$_POST['sya_commentcount']);
		update_option("sya_linktoauthor", (bool)$_POST['sya_linktoauthor']);
		update_option("sya_prepend", (string)$_POST['sya_prepend']);
		update_option("sya_append", (string)$_POST['sya_append']);
		update_option("sya_excerpt", (bool)$_POST['sya_excerpt']);
		update_option("sya_excerpt_indent", (string)$_POST['sya_excerpt_indent']);
		update_option("sya_excerpt_maxchars", (string)$_POST['sya_excerpt_maxchars']);

		$successmessage = __('Settings successfully updated!', 'simple-yearly-archive');

		echo '<div id="message" class="updated fade">
			<p>
				<strong>
					' . $successmessage . '
				</strong>
			</p>
		</div>';
	
	} ?>
	
	<? if(get_bloginfo('version') < '2.5') { ?>
	<style type="text/css">
    .form-table {
		border-collapse: collapse;
		margin-top: 1em;
		width: 100%;
		margin-bottom: -8px;
	}

	.form-table td {
		margin-bottom: 9px;
		padding: 10px;
		line-height: 20px;
		border-bottom-width: 1px;
		border-bottom-style: solid;
		border-bottom-color: #bbb;
	}

	.form-table th {
		text-align: left;
		padding: 10px;
		width: 150px;
		border-bottom-width: 1px;
		border-bottom-style: solid;
		border-bottom-color: #bbb;
	}

	.form-table input, .form-table textarea {
		border-width: 1px;
		border-style: solid;
	}
    </style>
	<? } ?>
	
	<div class="wrap">
      <h2>
        <?php _e('Simple Yearly Archive Options', 'simple-yearly-archive'); ?>
      </h2>
      <form name="sya_form" action="" method="post">
      <h3>
        <?php _e('Customize the archive output', 'simple-yearly-archive'); ?>
      </h3>
      <input type="hidden" name="action" value="edit" />
		<table class="form-table">
 		<tr>
 			<th scope="row" valign="top"><?php _e('Date format', 'simple-yearly-archive'); ?></th>
 			<td>
 				<input type="text" name="sya_dateformat" class="text" value="<?php echo stripslashes(get_option('sya_dateformat')) ?>" />
  	 			<label for="inputid"><br />
					<small><?php _e('(Check <a href="http://php.net/date" target="_blank">http://php.net/date</a> for date formatting)', 'simple-yearly-archive'); ?></small></label>
 			</td>
 		</tr>
		</table>
		<table class="form-table">
 		<tr>
 			<th scope="row" valign="top"><?php _e('Seperator between date and post title', 'simple-yearly-archive'); ?></th>
 			<td>
 				<input type="text" name="sya_datetitleseperator" class="text" value="<?php echo stripslashes(get_option('sya_datetitleseperator')) ?>" />
 			</td>
 		</tr>
		</table>
		<table class="form-table">
 		<tr>
 			<th scope="row" valign="top"><?php _e('Before / After (Year headline)', 'simple-yearly-archive'); ?></th>
 			<td>
 				<input type="text" name="sya_prepend" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_prepend')) ?>" /> | <input type="text" name="sya_append" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_append')) ?>" />
 			</td>
 		</tr>
		</table>
		<table class="form-table">
 		<tr>
 			<th scope="row" valign="top"><?php _e('Linked years?', 'simple-yearly-archive'); ?></th>
 			<td>
 				<input type="checkbox" name="sya_linkyears" id="sya_linkyears" value="1" <?php echo (get_option('sya_linkyears')) ? ' checked="checked"' : '' ?> />
 			</td>
 		</tr>
		</table>
		<table class="form-table">
 		<tr>
 			<th scope="row" valign="top"><?php _e('Show post count for each year?', 'simple-yearly-archive'); ?></th>
 			<td>
 				<input type="checkbox" name="sya_postcount" id="sya_postcount" value="1" <?php echo (get_option('sya_postcount')) ? ' checked="checked"' : '' ?> />
 			</td>
 		</tr>
		</table>
		<table class="form-table">
 		<tr>
 			<th scope="row" valign="top"><?php _e('Show comments count for each post?', 'simple-yearly-archive'); ?></th>
 			<td>
 				<input type="checkbox" name="sya_commentcount" id="sya_commentcount" value="1" <?php echo (get_option('sya_commentcount')) ? ' checked="checked"' : '' ?> />
 			</td>
 		</tr>
		</table>
		<table class="form-table">
 		<tr>
 			<th scope="row" valign="top"><?php _e('Show optional Excerpt (if available)?', 'simple-yearly-archive'); ?></th>
 			<td>
 				<input type="checkbox" name="sya_excerpt" id="sya_excerpt" value="1" <?php echo (get_option('sya_excerpt')) ? ' checked="checked"' : '' ?> />
 			</td>
 		</tr>
		</table>
		<table class="form-table">
 		<tr>
 			<th scope="row" valign="top"><div style="padding-left:20px;">-- <?php _e('Max. chars of Excerpt (0 for default)', 'simple-yearly-archive'); ?></div></th>
 			<td>
 				<input type="text" name="sya_excerpt_maxchars" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_excerpt_maxchars')) ?>" <?php echo (get_option('sya_excerpt') ? '' : 'disabled="disabled"') ?> />
 			</td>
 		</tr>
		</table>
		<table class="form-table">
 		<tr>
 			<th scope="row" valign="top"><div style="padding-left:20px;">-- <?php _e('Indentation of Excerpt (in px)', 'simple-yearly-archive'); ?></div></th>
 			<td>
 				<input type="text" name="sya_excerpt_indent" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_excerpt_indent')) ?>" <?php echo (get_option('sya_excerpt') ? '' : 'disabled="disabled"') ?> />
 			</td>
 		</tr>
		</table>
      <h3>
        <?php _e('Miscellaneous Options', 'simple-yearly-archive'); ?>
      </h3>
		<table class="form-table">
 		<tr>
 			<th scope="row" valign="top"><?php _e('Link back to my website in plugin footer? :)', 'simple-yearly-archive'); ?></th>
 			<td>
 				<input type="checkbox" name="sya_linktoauthor" id="sya_linktoauthor" value="1" <?php echo (get_option('sya_linktoauthor')) ? ' checked="checked"' : '' ?> />
 			</td>
 		</tr>
		</table>
		<p class="submit">
			<input type="submit" name="submit" value="<?php _e('Update Options', 'simple-yearly-archive'); ?> &raquo;" />
		</p>
		</form>
      <h3>
        <?php _e('Help', 'simple-yearly-archive'); ?>
      </h3>
		<table class="form-table">
 		<tr>
 			<td>
 				<?php _e('If you are new to using this plugin or cant understand what all these settings do, please read the documentation at <a href="http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/" target="_blank">http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/</a>', 'simple-yearly-archive'); ?>
 			</td>
 		</tr>
		</table>
 	</div>

<?php } ?>