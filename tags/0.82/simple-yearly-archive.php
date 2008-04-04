<?php
/*
Plugin Name: Simple Yearly Archive
Version: 0.82
Plugin URI: http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/
Description: A simple, clean yearly list of your archives.
Author: Oliver Schl&ouml;be
Author URI: http://www.schloebe.de/
*/

function simpleYearlyArchive($format='yearly', $excludeCat='') {

    global $wpdb, $PHP_SELF;
    setlocale(LC_ALL,WPLANG);
    $now = gmdate("Y-m-d H:i:s",(time()+((get_settings('gmt_offset'))*3600)));
	
	if (($format == 'yearly') || ($format == '')) {
		$modus = "";
	} else if($format == 'yearly_act') {
		$modus = " AND year(post_date) = ".date('Y');
	} else if($format == 'yearly_past') {
		$modus = " AND year(post_date) < ".date('Y');
	} else if(preg_match("/^[0-9]{4}$/", $format)) {
		$modus = " AND year(post_date) = '".$format."'";
	}
	
	$jahreMitBeitrag = $wpdb->get_results("SELECT DISTINCT post_date, year(post_date) AS `year`, COUNT(ID) as posts FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish'".$modus." GROUP BY year(post_date) ORDER BY post_date DESC");
	
	foreach ($jahreMitBeitrag as $aktuellesJahr) {
		for ($aktuellerMonat = 1; $aktuellerMonat <= 12; $aktuellerMonat++) {
			
			$monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat] = $wpdb->get_results("SELECT ID, post_date, post_title FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND year(post_date) = '$aktuellesJahr->year' ORDER BY post_date desc");
		}
	}
	
	if (($format == 'yearly') || ($format == 'yearly_act') || ($format == 'yearly_past') || ($format == '') || (preg_match("/^[0-9]{4}$/", $format))) {
	$before = get_option('sya_prepend');
	$after = get_option('sya_append');
	
	if ($jahreMitBeitrag) {
		if ($excludeCat != '') { // es gibt auszuschlie&szlig;ende Kategorien
		$excludeCats = explode(",", $excludeCat);
		foreach($jahreMitBeitrag as $aktuellesJahr) {
  			
  			$aktuellerMonat = 1;
    		while ($aktuellerMonat >= 1) {
		
					if(get_option('sya_linkyears')=='1') {
						$linkyears_prepend = '<a href="'.get_year_link($aktuellesJahr->year).'">';
						$linkyears_append = '</a>';
					} else {
						$linkyears_prepend = '';
						$linkyears_append = '';
					}

    				if ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat]) {
    					$ausgabe .= (''.$before.$linkyears_prepend.$aktuellesJahr->year.$linkyears_append.$after.'');
						$ausgabe .= '<ul>';
    						
    					foreach ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat] as $post) {
						if ($post->post_date <= $now) {
    						$cats = $wpdb->get_col("SELECT category_id FROM $wpdb->post2cat WHERE post_id = $post->ID");
							$match = false;
							//$aktdatum = the_time();
							//$wp_dateformat = get_option('date_format');
        						    
							foreach ($cats as $cat) if (in_array($cat, $excludeCats))
								$match = true;
        	                        
							if (!$match) {
    							$langtitle = $post->post_title;
    							$langtitle = apply_filters("the_title", $post->post_title);
								$ausgabe .= ('<li>'.date(get_option('sya_dateformat'),strtotime($post->post_date)).' '.get_option('sya_datetitleseperator').' <a href="'.get_permalink($post->ID).'" title="'.$post->post_title.'">'.$langtitle.'</a></li>');
							}
						}
						}
						
    					$ausgabe .= '</ul>';
				}
    			$aktuellerMonat--;
    		}
    		}
    		
    		} else { // es gibt keine auszuschlie&szlig;enden Kategorien
    		
			foreach($jahreMitBeitrag as $aktuellesJahr) {
    				
    				$aktuellerMonat = 1;
    				while ($aktuellerMonat >= 1) {
		
						if(get_option('sya_linkyears')==TRUE) {
							$linkyears_prepend = '<a href="'.get_year_link($aktuellesJahr->year).'">';
							$linkyears_append = '</a>';
						} else {
							$linkyears_prepend = '';
							$linkyears_append = '';
						}
					
    					if ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat]) {
    						//$ausgabe .= get_archives_link(get_month_link($aktuellesJahr->year, $aktuellerMonat), $monthNames[$aktuellerMonat] . ' ' . $aktuellesJahr->year, '', $before, $after);
							$ausgabe .= (''.$before.$linkyears_prepend.$aktuellesJahr->year.$linkyears_append.$after.'');
    						$ausgabe .= '<ul>';
    						
    						foreach ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat] as $post) {
    							$langtitle = $post->post_title;
    							$langtitle = apply_filters("the_title", $post->post_title);
								$ausgabe .= ('<li>'.date(get_option('sya_dateformat'),strtotime($post->post_date)).' '.get_option('sya_datetitleseperator').' <a href="'.get_permalink($post->ID).'" title="'.$post->post_title.'">'.$langtitle.'</a></li>');
							}
							$ausgabe .= '</ul>';
    					}
    					$aktuellerMonat--;
    				}
			}
		}
	}
	}
	
	echo $ausgabe;
}

add_action('admin_menu', 'sya_add_optionpages');

function set_default_options() {
	add_option('sya_dateformat', 'd.m.');
	add_option('sya_datetitleseperator', '-');
	add_option('sya_prepend', '<h2>');
	add_option('sya_append', '</h2>');
	add_option('sya_linkyears', 1);
}

load_plugin_textdomain('simple-yearly-archive','wp-content/plugins');

function sya_add_optionpages() {
	set_default_options();

    add_options_page(__('Simple Yearly Archive Options', 'simple-yearly-archive'), __('Simple Yearly Archive', 'simple-yearly-archive'), 8, __FILE__, 'sya_options_page');
}

function sya_inline($content) {	
	if(!preg_match('|<!--simple-yearly-archive-->|', $content)) return $content;
	return str_replace('|<!--simple-yearly-archive-->|', simpleYearlyArchive(), $content);
}

add_action('the_content', 'sya_inline', 8);

function sya_options_page() { 

	if (isset($_POST['action']) === true) {
		update_option("sya_dateformat", (string)$_POST['sya_dateformat']);
		update_option("sya_datetitleseperator", (string)$_POST['sya_datetitleseperator']);
		update_option("sya_linkyears", (bool)$_POST['sya_linkyears']);
		update_option("sya_prepend", (string)$_POST['sya_prepend']);
		update_option("sya_append", (string)$_POST['sya_append']);

		$successmessage = __('Settings successfully updated!', 'simple-yearly-archive');

		echo '<div id="message" class="updated fade">
			<p>
				<strong>
					'.$successmessage.'
				</strong>
			</p>
		</div>';
	
	} ?>
	
	<style type="text/css">
      .wrap ul {
        clear: both;
        margin: 0;
        padding: 10px 0 0 20px;
      }
      .wrap ul li {
        clear: both;
        list-style: none;
      }
      .wrap ul li div {
        float: left;
      }
      .wrap ul li .left {
        width: 250px;
        margin: 3px 0 0;
      }
      .wrap input.text {
        width: 200px;
      }
      .wrap select {
        width: 208px;
        height: 22px;
      }
      .wrap textarea {
        width: 200px;
        height: 50px;
      }
    </style>
	
	<div class="wrap">
      <h2>
        <?php _e('Simple Yearly Archive Options', 'simple-yearly-archive'); ?>
      </h2>
      <fieldset class="options">
      <legend>
      	<?php _e('Customize the archive output', 'simple-yearly-archive'); ?>
      </legend>
      <form name="sya_form" action="" method="post">
      <input type="hidden" name="action" value="edit" />
		<ul>
			<li>
				<div class="left">
					<?php _e('Date format', 'simple-yearly-archive'); ?>
				</div>
				<div>
					<input type="text" name="sya_dateformat" class="text" value="<?php echo stripslashes(get_option('sya_dateformat')) ?>" /><br />
					<small><?php _e('(Check <a href="http://php.net/date" target="_blank">http://php.net/date</a> for date formatting)', 'simple-yearly-archive'); ?></small>
				</div>
			</li>
		</ul>
		<ul>
			<li>
				<div class="left">
					<?php _e('Seperator between date and post title', 'simple-yearly-archive'); ?>
				</div>
				<div>
					<input type="text" name="sya_datetitleseperator" class="text" value="<?php echo stripslashes(get_option('sya_datetitleseperator')) ?>" />
				</div>
			</li>
		</ul>
		<ul>
			<li>
				<div class="left">
					<?php _e('Linked years?', 'simple-yearly-archive'); ?>
				</div>
				<div>
					<input type="checkbox" name="sya_linkyears" id="sya_linkyears" value="1" <?php echo (get_option('sya_linkyears')) ? ' checked="checked"' : '' ?> />
				</div>
			</li>
		</ul>
		<ul>
			<li>
				<div class="left">
					<?php _e('Before / After (Year headline)', 'simple-yearly-archive'); ?>
				</div>
				<div>
					<input type="text" name="sya_prepend" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_prepend')) ?>" /> | <input type="text" name="sya_append" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_append')) ?>" /><br />
				</div>
			</li>
		</ul>
		</fieldset>
		<p class="submit">
			<input type="submit" value="<?php _e('Update Options', 'simple-yearly-archive'); ?> &raquo;" />
		</p>
		</form>
		<fieldset class="options">
		<legend>
			<?php _e('Help', 'simple-yearly-archive'); ?>
		</legend>
		<?php _e('If you are new to using this plugin or cant understand what all these settings do, please read the documentation at <a href="http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/" target="_blank">http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/</a>', 'simple-yearly-archive'); ?>
		</fieldset>
 	</div>

<?php } ?>