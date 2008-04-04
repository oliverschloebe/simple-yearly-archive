<?php
/*
Plugin Name: Simple Yearly Archive
Version: 0.5
Plugin URI: http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/
Description: A simple, clean yearly list of your archives.
Author: Oliver Schl&ouml;be
Author URI: http://www.schloebe.de/
*/

function simpleYearlyArchive($format='yearly', $excludeCat='', $before='<h1>', $after='</h1>') {

    global $wpdb, $PHP_SELF;
    setlocale(LC_ALL,WPLANG);
    $now = gmdate("Y-m-d H:i:s",(time()+((get_settings('gmt_offset'))*3600)));
	
	if (($format == 'yearly') || ($format == '')) {
		$modus = "";
	} else if($format == 'yearly_act') {
		$modus = " AND year(post_date) = ".date('Y');
	} else if($format == 'yearly_past') {
		$modus = " AND year(post_date) < ".date('Y');
	}
	
	$jahreMitBeitrag = $wpdb->get_results("SELECT DISTINCT post_date, year(post_date) AS `year`, COUNT(ID) as posts FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish'".$modus." GROUP BY year(post_date) ORDER BY post_date DESC");
	
	foreach ($jahreMitBeitrag as $aktuellesJahr) {
		for ($aktuellerMonat = 1; $aktuellerMonat <= 12; $aktuellerMonat++) {
			
			$monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat] = $wpdb->get_results("SELECT ID, post_date, post_title FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND year(post_date) = '$aktuellesJahr->year' ORDER BY post_date desc");
		}
	}
	
	if (($format == 'yearly') || ($format == 'yearly_act') || ($format == 'yearly_past') || ($format == '')) {
	($before == '') ? $before = '<h1>' : $before;
	($after == '') ? $after = '</h1>' : $after;
	
	if ($jahreMitBeitrag) {
		if ($excludeCat != '') { // es gibt auszuschlie&szlig;ende Kategorien
		$excludeCats = explode(",", $excludeCat);
		foreach($jahreMitBeitrag as $aktuellesJahr) {
  			for ($aktuellerMonat = 1; $aktuellerMonat >= 1; $aktuellerMonat--) {
			
    				if ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat]) {
    					echo (''.$before.'<a href="'.get_year_link($aktuellesJahr->year).'">'.$aktuellesJahr->year.'</a>'.$after.'');
					echo '<ul>';
    						
    					foreach ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat] as $post) {
						if ($post->post_date <= $now) {
    						$cats = $wpdb->get_col("SELECT category_id FROM $wpdb->post2cat WHERE post_id = $post->ID");
							$match = false;
							//$aktdatum = the_time();
							//$wp_dateformat = get_option('date_format');
        						    
							foreach ($cats as $cat) if (in_array($cat, $excludeCats))
								$match = true;
        	                        
							if (!$match)
								echo ('<li>'.date('d.m.',strtotime($post->post_date)).' - <a href="'.get_permalink($post->ID).'" title="'.$post->post_title.'">'.$post->post_title.'</a></li>');
						}
						}
    					echo '</ul>';
				}
    			}
    		}
    		
    		} else { // es gibt keine auszuschlie&szlig;enden Kategorien
			foreach($jahreMitBeitrag as $aktuellesJahr) {
    				for ($aktuellerMonat = 1; $aktuellerMonat >= 1; $aktuellerMonat--) {
    					if ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat]) {
    						echo get_archives_link(get_month_link($aktuellesJahr->year, $aktuellerMonat), $monthNames[$aktuellerMonat] . ' ' . $aktuellesJahr->year, '', $before, $after);
    						echo '<ul>';
    						
    						foreach ($monateMitBeitrag[$aktuellesJahr->year][$aktuellerMonat] as $post)
							echo ('<li>'.date('d.m.',strtotime($post->post_date)).' - <a href="'.get_permalink($post->ID).'" title="'.$post->post_title.'">'.$post->post_title.'</a></li>');
    							echo '</ul>';
    					}
    				}
    			}
		}
	}
	}
}
?>