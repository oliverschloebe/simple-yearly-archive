<?php
/*
Plugin Name: Simple Yearly Archive
Version: 1.5.0
Plugin URI: http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/
Description: A simple, clean yearly list of your archives.
Author: Oliver Schl&ouml;be
Author URI: http://www.schloebe.de/

Copyright 2009-2014 Oliver Schlöbe (email : scripts@schloebe.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * The main plugin file
 *
 * @package WordPress_Plugins
 * @subpackage SimpleYearlyArchive
 */


/**
 * Define the plugin version
 */
define("SYA_VERSION", "1.5.0");

/**
 * Define the plugin path slug
 */
define("SYA_PLUGINPATH", "/" . plugin_basename( dirname(__FILE__) ) . "/");

/**
 * Define the plugin full url
 */
define("SYA_PLUGINFULLURL", WP_CONTENT_URL . '/plugins' . SYA_PLUGINPATH );

/**
 * Define the plugin full dir
 */
define("SYA_PLUGINFULLDIR", WP_CONTENT_DIR . '/plugins' . SYA_PLUGINPATH );


/**
 * Load plugin textdomain
 *
 * @since 1.5.0
 * @author scripts@schloebe.de
 */
function sya_load_plugin_textdomain() {
	load_plugin_textdomain('simple-yearly-archive', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
}
add_action('plugins_loaded', 'sya_load_plugin_textdomain');



/**
 * Notify users that date format has changed with version 1.2.6
 * 
 * @since 1.2.6
 * @author scripts@schloebe.de
 */
function sya_dateformat_changed_message() {
	echo "<div id='wpversionfailedmessage' class='error fade'><p>" . __('The date format changed in Simple Yearly Archive 1.2.6! Please <a href="options-general.php?page=simple-yearly-archive/simple-yearly-archive.php">save the options once</a> to assign the new date format to the system! <strong>Don\'t forget to change the date format string!</strong>', 'simple-yearly-archive') . "</p></div>";
}
if( !get_option('sya_dateformatchanged2012') || get_option('sya_dateformatchanged2012') == 0 ) {
	add_action('admin_notices', 'sya_dateformat_changed_message');
}


/**
 * Notify users that date format string is deprecated
 * 
 * @since 1.2.7
 * @author scripts@schloebe.de
 */
function sya_dateformat_deprecated_message() {
	echo "<div id='wpversionfailedmessage' class='error fade'><p>" . __('You still seem to use the old date format string in Simple Yearly Archive! Please <strong><a href="options-general.php?page=simple-yearly-archive/simple-yearly-archive.php">change the date format string</a></strong> to assign the new date format to the system! An example would be " %d/%m "!', 'simple-yearly-archive') . "</p></div>";
}

if( is_admin() ) {
	$sya_dateformat_deprecated = strpos(get_option('sya_dateformat'), "%");
	if( get_option('sya_dateformatchanged2012') == 1 && ($sya_dateformat_deprecated === false && get_option('sya_dateformat') != '') ) {
		add_action('admin_notices', 'sya_dateformat_deprecated_message');
	}
}


/**
 * Add action link(s) to plugins page
 * 
 * @since 1.1.7
 * @author scripts@schloebe.de
 * @copyright Dion Hulse, http://dd32.id.au/wordpress-plugins/?configure-link
 */
function sya_filter_plugin_actions($links, $file){
	static $this_plugin;

	if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if( $file == $this_plugin ) {
		$settings_link = '<a href="options-general.php?page=simple-yearly-archive/simple-yearly-archive.php">' . __('Settings') . '</a>';
		$links = array_merge( array($settings_link), $links); // before other links
	}
	return $links;
}

add_filter('plugin_action_links', 'sya_filter_plugin_actions', 10, 2);


/**
 * Returns the parsed archive contents
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string
 * @param int|string
 * @return int|string
 */
function get_simpleYearlyArchive($format, $excludeCat='', $includeCat='', $dateformat) {	
	global $wpdb, $PHP_SELF, $wp_version;
	setlocale(LC_TIME, WPLANG);
	$now = gmdate("Y-m-d H:i:s",(time()+((get_option('gmt_offset'))*3600)));
	(!isset($wp_version)) ? $wp_version = get_bloginfo('version') : $wp_version = $wp_version;
	$allcatids = get_all_category_ids();
	$yeararray = array();
	$ausgabe = '';
	
	$ausgabe .= "<div class=\"sya_container\" id=\"sya_container\">";
	
	$syaargs_includecats = '';
	if ($excludeCat != '' || $includeCat != '') { // there are excluded or included categories
		$excludeCats = explode( ",", trim($excludeCat) );
		if( trim($includeCat) == '' )
			$includeCats = array_diff( $allcatids, $excludeCats );
		else
			$includeCats = explode( ",", trim( $includeCat ) );
		
		$syaargs_includecats = implode(",", $includeCats);
	}
	
	$syaargs = array(
		'no_found_rows'			=> 1,
		'post_type'				=> 'post',
		'numberposts'			=> -1,
		'post_status'			=> ( current_user_can('read_private_posts') ) ? array('private', 'publish') : array('publish'),
		'orderby'				=> 'post_date',
		'order'					=> ( get_option('sya_reverseorder') == true ) ? 'ASC' : 'DESC',
		'suppress_filters'		=> false
	);
	
	($syaargs_includecats != '' ? $syaargs['category'] = $syaargs_includecats : '');
	
	if($format == 'yearly_act') {
		$syaargs['year'] = date('Y');
	} else if($format == 'yearly_past') {
		add_filter( 'posts_where', 'sya_filter_posts_yearly_past' );
	} else if(preg_match("/^[0-9]{4}$/", $format)) {
		$syaargs['year'] = $format;
	}
	
	$jahreMitBeitrag = get_posts( $syaargs );
	
	if($format == 'yearly_past') {
		remove_filter( 'posts_where', 'sya_filter_posts_yearly_past' );
	}
	
	$jmb = array();
	foreach( $jahreMitBeitrag as $jahrMitBeitrag ) {
		$jmb[] = date('Y', strtotime($jahrMitBeitrag->post_date));
	}
	$jahreMitBeitrag = array_unique($jmb);

	foreach ($jahreMitBeitrag as $aktuellesJahr) {
		for ($aktuellerMonat = 1; $aktuellerMonat <= 12; $aktuellerMonat++) {
			
			/*
			 * $wpdb direct SQL queries are waaaay less memory consuming than qet_posts (with 1000+ posts)
			 */
			$_post_status = ( current_user_can('read_private_posts') ) ? "'private', 'publish'" : "'publish'";
			$_query = "
				SELECT post.ID, post.post_title, post.post_date, post.post_status, post.comment_count, post.post_author, post.post_excerpt, term_rel.term_taxonomy_id FROM `$wpdb->posts` AS post
				LEFT JOIN `$wpdb->postmeta` AS meta ON post.ID = meta.post_id
				LEFT JOIN `$wpdb->term_relationships` AS term_rel ON post.ID = term_rel.object_id
				WHERE post.post_type IN ( 'post' )
				AND post.post_status IN ( $_post_status )
				AND YEAR(post.post_date) = '" . intval($aktuellesJahr) . "'
				GROUP BY post.ID
				ORDER BY post_date DESC;
			";
			$year_posts = $wpdb->get_results( $_query );
			
			$monateMitBeitrag[$aktuellesJahr][$aktuellerMonat] = $year_posts;
		}
		$yeararray[] = $aktuellesJahr;
	}
	
	if(get_option('sya_showyearoverview')==TRUE) {
		$ausgabe .= "<p class=\"sya_yearslist\" id=\"sya_yearslist\">" . implode( ' &bull; ', sya_yearoverview( $yeararray ) ) . "</p>";
	}
	
	if (($format == 'yearly') || ($format == 'yearly_act') || ($format == 'yearly_past') || ($format == '') || (preg_match("/^[0-9]{4}$/", $format))) {
		$before = get_option('sya_prepend');
		$after = get_option('sya_append');
	    ((get_option('sya_excerpt_indent')=='') ? $indent = '0' : $indent = get_option('sya_excerpt_indent'));
		((get_option('sya_excerpt_maxchars')=='') ? $maxzeichen = '0' : $maxzeichen = get_option('sya_excerpt_maxchars'));
		(($dateformat=='') ? $outputdateformat = get_option('sya_dateformat') : $outputdateformat = $dateformat);
	
		if ($jahreMitBeitrag) {
			if ($excludeCat != '' || $includeCat != '') { // there are excluded or included categories
			
				foreach($jahreMitBeitrag as $aktuellesJahr) {
		  			
		  			$aktuellerMonat = 1;
					while ($aktuellerMonat >= 1) {
						
						if(get_option('sya_collapseyears')==TRUE) {
							$linkyears_prepend = '<a href="#" onclick="this.parentNode.nextSibling.style.display=(this.parentNode.nextSibling.style.display!=\'none\'?\'none\':\'\');return false;">';
							$linkyears_append = '</a>';
						} elseif(get_option('sya_linkyears')==TRUE) {
							$linkyears_prepend = '<a href="' . get_year_link($aktuellesJahr) . '" rel="section">';
							$linkyears_append = '</a>';
						} else {
							$linkyears_prepend = '';
							$linkyears_append = '';
						}
	
	    				if ($monateMitBeitrag[$aktuellesJahr][$aktuellerMonat]) {
							$syaargs_status = ( current_user_can('read_private_posts') ) ? array('private', 'publish') : array('publish');
							
							$syaargs = array(
								'post_type' => 'post',
								'numberposts' => -1,
								'post_status' => $syaargs_status,
								'category' => $syaargs_includecats,
								'year' => $aktuellesJahr
							);
							
							$syaposts = get_posts( $syaargs );
							
							$wp_dateformat = get_option('date_format');
							if( $syaposts ) {
								$listitems = '';
								foreach( $syaposts as $post ) {
									setup_postdata( $post );
									$post->filter = 'sample';
									if ( date(('Y'), strtotime($post->post_date)) == $aktuellesJahr ) {
										$langtitle = $post->post_title;
		    							$langtitle = apply_filters("the_title", $post->post_title);
		    							if( $post->post_status == 'private' ) {
		    								$isprivate = ' class="sya_private"';
		    								$langtitle = sprintf(__('Private: %s'), $langtitle);
		    							} else {
		    								$isprivate = '';
		    							}
		    							$listitems .= '<li' . $isprivate . '>';
										$listitems .= ('<span class="sya_date">' . utf8_encode(strftime($outputdateformat, strtotime($post->post_date))) . ' ' . get_option('sya_datetitleseperator') . ' </span><a href="' . get_permalink($post->ID) . '" rel="bookmark" title="' . esc_attr( $post->post_title ) . '">' . $langtitle . '</a>');
										if(get_option('sya_commentcount')==TRUE) {
											$listitems .= ' (' . $post->comment_count . ')';
										}
										if(get_option('sya_show_categories')==TRUE) {
											$sya_categories = array();
											foreach (wp_get_post_categories( $post->ID ) as $cat_id) {
												$sya_categories[] = get_cat_name( $cat_id );
											}
											if( count($sya_categories) > 0 )
												$listitems .= ' <span class="sya_categories">(' . implode(', ', $sya_categories) . ')</span>';
											$sya_categories = '';
										}
										if(get_option('sya_showauthor')==TRUE) {
											$userinfo = get_userdata( $post->post_author );
											$listitems .= ' <span class="sya_author">(' . __('by') . ' ' . $userinfo->display_name . ')</span>';
										}
										$excerpt = '';
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
									}
								}
							}
							if (strlen($listitems) > 0) {
								$ausgabe .= $before . '<a id="year' . $aktuellesJahr . '"></a>' . $linkyears_prepend . $aktuellesJahr . $linkyears_append;
								if(get_option('sya_postcount')==TRUE) {
									$postcount = count( $syaposts );
									$ausgabe .= ' <span class="sya_yearcount">(' . $postcount . ')</span>';
								}
								$additionalulcss = (get_option('sya_collapseyears')==TRUE ? ' style="display:none;"' : '');
								$ausgabe .= $after.'<ul' . $additionalulcss . '>'.$listitems.'</ul>';
							}
						}
	    				$aktuellerMonat--;
		    		}
				}
	    		
	    	} else { // there are NO excluded or included categories
	    		
				foreach($jahreMitBeitrag as $aktuellesJahr) {
	    				
	    				$aktuellerMonat = 1;
	    				while ($aktuellerMonat >= 1) {
			
							if(get_option('sya_collapseyears')==TRUE) {
								$linkyears_prepend = '<a href="#" onclick="this.parentNode.nextSibling.style.display=(this.parentNode.nextSibling.style.display!=\'none\'?\'none\':\'\');return false;">';
								$linkyears_append = '</a>';
							} elseif(get_option('sya_linkyears')==TRUE) {
								$linkyears_prepend = '<a href="' . get_year_link($aktuellesJahr) . '" rel="section">';
								$linkyears_append = '</a>';
							} else {
								$linkyears_prepend = '';
								$linkyears_append = '';
							}
						
	    					if ($monateMitBeitrag[$aktuellesJahr][$aktuellerMonat]) {
								
								if(get_option('sya_postcount')==TRUE) {
									$postcount = count($monateMitBeitrag[$aktuellesJahr][$aktuellerMonat]);
	    						}
								$listitems = '';
	    						
	    						foreach ($monateMitBeitrag[$aktuellesJahr][$aktuellerMonat] as $post) {
									$post->filter = 'sample';
									
	    							$langtitle = $post->post_title;
	    							$langtitle = apply_filters("the_title", $post->post_title);
	    							if( $post->post_status == 'private' ) {
	    								$isprivate = ' class="sya_private"';
	    								$langtitle = sprintf(__('Private: %s'), $langtitle);
	    							} else {
	    								$isprivate = '';
	    							}
	    							$listitems .= '<li' . $isprivate . '>';
									$listitems .= ('<span class="sya_date">' . utf8_encode(strftime($outputdateformat, strtotime($post->post_date))) . ' ' . get_option('sya_datetitleseperator') . ' </span><a href="' . get_permalink($post->ID) . '" rel="bookmark" title="' . esc_attr( $post->post_title ) . '">' . $langtitle . '</a>');
	
									if(get_option('sya_commentcount')==TRUE) {
										$listitems .= ' (' . $post->comment_count . ')';
									}
									if(get_option('sya_show_categories')==TRUE) {
										$sya_categories = array();
										foreach (wp_get_post_categories( $post->ID ) as $cat_id) {
											$sya_categories[] = get_cat_name( $cat_id );
										}
										if( count($sya_categories) > 0 )
											$listitems .= ' <span class="sya_categories">(' . implode(', ', $sya_categories) . ')</span>';
										$sya_categories = '';
									}
									if(get_option('sya_showauthor')==TRUE) {
										$userinfo = get_userdata( $post->post_author );
										$listitems .= ' <span class="sya_author">(' . __('by') . ' ' . $userinfo->display_name . ')</span>';
									}
									$excerpt = '';
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
								}
								if (strlen($listitems) > 0) {
									$ausgabe .= $before . '<a id="year' . $aktuellesJahr . '"></a>' . $linkyears_prepend.$aktuellesJahr.$linkyears_append;
									if(get_option('sya_postcount')==TRUE) {
										$postcount = count($monateMitBeitrag[$aktuellesJahr][$aktuellerMonat]);
										$ausgabe .= ' <span class="sya_yearcount">(' . $postcount . ')</span>';
									}
									$additionalulcss = (get_option('sya_collapseyears')==TRUE ? ' style="display:none;"' : '');
									$ausgabe .= $after.'<ul' . $additionalulcss . '>'.$listitems.'</ul>';
								}
	    					}
	    					$aktuellerMonat--;
	    				}
				}
			}
		} else {
			$ausgabe .= __('No posts found.');
		}
	}
	
	if(get_option('sya_linktoauthor')==TRUE) {
		$linkvar = __('Plugin by', 'simple-yearly-archive') . ' <a href="http://www.schloebe.de">Oliver Schl&ouml;be</a>';
		$ausgabe .= '<div style="text-align:right;font-size:90%;">' . $linkvar . '</div>';
	}
	
	$ausgabe .= "</div>";
	$ausgabe = apply_filters('sya_archive_output', $ausgabe);
	
	return $ausgabe;
}

/**
 * Echoes the parsed archive contents
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string
 * @param int|string
 */
function simpleYearlyArchive($format='yearly', $excludeCat='', $includeCat='', $dateformat='') {
	echo get_simpleYearlyArchive($format, $excludeCat, $includeCat, $dateformat);
}

/**
 * Returns the year overview contents
 *
 * @since 1.2.5
 * @author scripts@schloebe.de
 *
 * @param array $yeararray
 * @return array
 */
function sya_yearoverview( $yeararray ) {
	$years = array();
	foreach( $yeararray as $year ) {
		$years[] = '<a href="#year' . $year . '">' . $year . '</a>';
	}
	return $years;
}

/**
 * Echoes the plugin version in the website header
 *
 * @since 0.8
 * @author scripts@schloebe.de
 */
function sya_header() {
	echo "\n" . '<!-- Using Simple Yearly Archive Plugin v' . SYA_VERSION . ' | http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/ // -->' . "\n";
}

if ( is_admin() ) {
	add_action('admin_menu', 'sya_add_option_menu');
}
add_action('wp_head', 'sya_header');


if( version_compare($GLOBALS['wp_version'], '2.4.999', '>') ) {
	/** 
	 * This file holds all the author plugins functions
	 */
	require_once( dirname(__FILE__) . '/' . 'authorplugins.inc.php' );
}


/**
 * Sets the default options after plugin activation
 *
 * @since 0.8
 * @author scripts@schloebe.de
 */
function set_default_options() {
	if ( get_option('sya_dateformat') == false ) update_option('sya_dateformat', '%d/%m');
	if ( get_option('sya_datetitleseperator') == false ) update_option('sya_datetitleseperator', '-');
	if ( get_option('sya_prepend') == false ) update_option('sya_prepend', '<h3>');
	if ( get_option('sya_append') == false ) update_option('sya_append', '</h3>');
	if ( get_option('sya_linkyears') == false ) update_option('sya_linkyears', 1);
	if ( get_option('sya_collapseyears') == false ) update_option('sya_collapseyears', 0);
	if ( get_option('sya_postcount') == false ) update_option('sya_postcount', 0);
	if ( get_option('sya_commentcount') == false ) update_option('sya_commentcount', 0);
	if ( get_option('sya_linktoauthor') == false ) update_option('sya_linktoauthor', 1);
	if ( get_option('sya_reverseorder') == false ) update_option('sya_reverseorder', 0);
	if ( get_option('sya_excerpt') == false ) update_option('sya_excerpt', 0);
	if ( get_option('sya_excerpt_indent') == false ) update_option('sya_excerpt_indent', '');
	if ( get_option('sya_excerpt_maxchars') == false ) update_option('sya_excerpt_maxchars', '');
	if ( get_option('sya_show_categories') == false ) update_option('sya_show_categories', 0);
	if ( get_option('sya_showauthor') == false ) update_option('sya_showauthor', 0);
	if ( get_option('sya_showyearoverview') == false ) update_option('sya_showyearoverview', 0);
	if ( get_option('sya_dateformatchanged2012') == false ) update_option("sya_dateformatchanged2012", 0);
}


/**
 * @since 1.1.7
 * @uses function sya_get_resource_url() to display
 */
if( isset($_GET['resource']) && !empty($_GET['resource'])) {
	$resources = array(
		'pulldown.gif' =>
		'R0lGODlhCgAKAKIAADMzM//M/97e3pCQkGZmZu/v7////wAAAC'.
		'H5BAEHAAEALAAAAAAKAAoAAAMkGLoc9PA5Ywa9z4ghujBPx41C'.
		'uIndU5xeoRZDIctEANz43ewJADs='.
		'');
 
	if(array_key_exists($_GET['resource'], $resources)) {
 
		$content = base64_decode($resources[ $_GET['resource'] ]);
 
		$lastMod = filemtime(__FILE__);
		$client = ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false );
		if (isset($client) && (strtotime($client) == $lastMod)) {
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 304);
			exit;
		} else {
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 200);
			header('Content-Length: '.strlen($content));
			header('Content-Type: image/' . substr(strrchr($_GET['resource'], '.'), 1) );
			echo $content;
			exit;
		}
	}
}
 

/**
 * Display Images/Icons base64-encoded
 * 
 * @since 1.1.7
 * @author scripts@schloebe.de
 * @param $resourceID
 * @return $resourceURL
 */
function sya_get_resource_url( $resourceID ) {
	return trailingslashit( get_site_url() ) . '?resource=' . $resourceID;
}


/**
 * Adds the plugin's options page
 * 
 * @since 1.1.7
 * @author scripts@schloebe.de
 */
function sya_add_option_menu() {
	global $wp_version;
	if ( current_user_can('manage_options') && function_exists('add_submenu_page') ) {
		$menutitle = '';
		$menutitle .= '<img src="' . sya_get_resource_url('pulldown.gif') . '" alt="" />' . ' ';
		$menutitle .= __('Simple Yearly Archive', 'simple-yearly-archive');
 
		add_submenu_page('options-general.php', __('Simple Yearly Archive', 'simple-yearly-archive'), $menutitle, 'manage_options', __FILE__, 'sya_options_page');
	}
}


/**
 * Filters the shortcode from the post content and returns the filtered content
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string
 * @return string
 */
function sya_inline($post) {	
	if (substr_count($post, '<!--simple-yearly-archive-->') > 0) {
		$sya_archives = get_simpleYearlyArchive($format, $excludeCat);
		$post = str_replace('<!--simple-yearly-archive-->', $sya_archives, $post);
	}
	return $post;
}

add_action('the_content', 'sya_inline', 1);


/**
 * Setups the plugin's shortcode
 *
 * @since 1.1.0
 * @author scripts@schloebe.de
 *
 * @param mixed
 * @return string
*/
function syatag_func( $atts ) {
	extract(shortcode_atts(array(
		'type' => 'yearly',
		'exclude' => '',
		'include' => '',
		'dateformat' => ''
	), $atts));
	
	return get_simpleYearlyArchive($type, $exclude, $include, $dateformat);
}
if( function_exists('add_shortcode') ) {
	add_shortcode('SimpleYearlyArchive', 'syatag_func');
}


/**
 * Fills the options page with content
 *
 * @since 0.7
 * @author scripts@schloebe.de
 */
function sya_options_page() {
	global $wp_version;
	if ( !empty($_POST) ) {
		check_admin_referer('sya');
		update_option("sya_dateformat", (string)$_POST['sya_dateformat']);
		update_option("sya_datetitleseperator", (string)$_POST['sya_datetitleseperator']);
		update_option("sya_linkyears", (bool)!empty($_POST['sya_linkyears']));
		update_option("sya_collapseyears", (bool)!empty($_POST['sya_collapseyears']));
		update_option("sya_postcount", (bool)!empty($_POST['sya_postcount']));
		update_option("sya_commentcount", (bool)!empty($_POST['sya_commentcount']));
		update_option("sya_linktoauthor", (bool)!empty($_POST['sya_linktoauthor']));
		update_option("sya_reverseorder", (bool)!empty($_POST['sya_reverseorder']));
		update_option("sya_prepend", (string)$_POST['sya_prepend']);
		update_option("sya_append", (string)$_POST['sya_append']);
		update_option("sya_excerpt", (bool)!empty($_POST['sya_excerpt']));
		update_option("sya_excerpt_indent", $_POST['sya_excerpt_indent']);
		update_option("sya_excerpt_maxchars", $_POST['sya_excerpt_maxchars']);
		update_option("sya_show_categories", (bool)!empty($_POST['sya_show_categories']));
		update_option("sya_showauthor", (bool)!empty($_POST['sya_showauthor']));
		update_option("sya_showyearoverview", (bool)!empty($_POST['sya_showyearoverview']));
		update_option("sya_dateformatchanged2012", 1);

		$successmessage = __('Settings successfully updated!', 'simple-yearly-archive');

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
		<h2>
			<?php _e('Simple Yearly Archive Options', 'simple-yearly-archive'); ?>
		</h2>
		<form name="sya_form" action="" method="post">
		<?php if( function_exists('wp_nonce_field') ) wp_nonce_field('sya'); ?>
		<div id="poststuff" class="ui-sortable">
			<div id="sya_customize_box" class="postbox if-js-open">
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
						<small><?php _e('(Check <a href="http://php.net/manual/en/function.strftime.php" target="_blank">http://php.net/strftime</a> for date formatting)', 'simple-yearly-archive'); ?></small></label>
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
				<th scope="row" valign="top">
					<?php _e('Collapsible years?', 'simple-yearly-archive'); ?><br />
					<small><em>(<?php _e('Disables the "Linked years?" option', 'simple-yearly-archive'); ?>)</em></small>
				</th>
				<td>
					<input type="checkbox" name="sya_collapseyears" id="sya_collapseyears" value="1" <?php echo (get_option('sya_collapseyears')) ? ' checked="checked"' : '' ?> />
				</td>
			</tr>
			</table>
			<table class="form-table">
			<tr>
				<th scope="row" valign="top"><?php _e('Anchored overview at the top?', 'simple-yearly-archive'); ?></th>
				<td>
					<input type="checkbox" name="sya_showyearoverview" id="sya_showyearoverview" value="1" <?php echo (get_option('sya_showyearoverview')) ? ' checked="checked"' : '' ?> />
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
				<th scope="row" valign="top"><?php _e('Show categories after each post?', 'simple-yearly-archive'); ?></th>
				<td>
					<input type="checkbox" name="sya_show_categories" id="sya_show_categories" value="1" <?php echo (get_option('sya_show_categories')) ? ' checked="checked"' : '' ?> />
				</td>
			</tr>
			</table>
			<table class="form-table">
			<tr>
				<th scope="row" valign="top"><?php _e('Show post author after each post?', 'simple-yearly-archive'); ?></th>
				<td>
					<input type="checkbox" name="sya_showauthor" id="sya_showauthor" value="1" <?php echo (get_option('sya_showauthor')) ? ' checked="checked"' : '' ?> />
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
					<input type="text" name="sya_excerpt_maxchars" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_excerpt_maxchars')) ?>" <?php echo (get_option('sya_excerpt') ? '' : 'readonly="readonly"') ?> />
				</td>
			</tr>
			</table>
			<table class="form-table">
			<tr>
				<th scope="row" valign="top"><div style="padding-left:20px;">-- <?php _e('Indentation of Excerpt (in px)', 'simple-yearly-archive'); ?></div></th>
				<td>
					<input type="text" name="sya_excerpt_indent" class="text" style="width:89px;" value="<?php echo stripslashes(get_option('sya_excerpt_indent')) ?>" <?php echo (get_option('sya_excerpt') ? '' : 'readonly="readonly"') ?> />
				</td>
			</tr>
			</table>
			<p class="submit" style="margin-left:10px;">
				<input type="submit" name="submit" value="<?php _e('Update Options', 'simple-yearly-archive'); ?> &raquo;" class="button button-primary" />
			</p>
			</div>
		</div>
		<div id="poststuff" class="ui-sortable">
			<div id="sya_misc_box" class="postbox if-js-open">
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
			<tr>
				<th scope="row" valign="top"><?php _e('Reverse order?', 'simple-yearly-archive'); ?></th>
				<td>
					<input type="checkbox" name="sya_reverseorder" id="sya_reverseorder" value="1" <?php echo (get_option('sya_reverseorder')) ? ' checked="checked"' : '' ?> />
				</td>
			</tr>
			</table>
			<p class="submit" style="margin-left:10px;">
				<input type="submit" name="submit" value="<?php _e('Update Options', 'simple-yearly-archive'); ?> &raquo;" class="button button-primary" />
			</p>
			</div>
		</div>
		</form>
		<?php if( version_compare($wp_version, '2.5', '>=') ) { ?>
		  	<div id="poststuff" class="ui-sortable">
		  	<div id="sya_plugins_box" class="postbox if-js-open">
		  	<h3>
		    	<?php _e('More of my WordPress plugins', 'simple-yearly-archive'); ?>
		  	</h3>
			<table class="form-table">
			<tr>
				<td>
					<?php _e('You may also be interested in some of my other plugins:', 'simple-yearly-archive'); ?>
					<p id="authorplugins-wrap"><input id="authorplugins-start" value="<?php _e('Show other plugins by this author inline &raquo;', 'simple-yearly-archive'); ?>" class="button-secondary" type="button"></p>
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
					<?php _e('More plugins at: <a class="button rbutton" href="http://www.schloebe.de/portfolio/" target="_blank">www.schloebe.de</a>', 'simple-yearly-archive'); ?>
				</td>
			</tr>
			</table>
			</div>
			</div>
		<?php } ?>
		<div id="poststuff" class="ui-sortable">
			<div id="sya_help_box" class="postbox if-js-open">
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
		</div>
	  	<div id="poststuff" class="ui-sortable">
		  	<div id="sya_about_box" class="postbox if-js-open">
		  	<?php
			$sya_plugindata = get_plugin_data(__FILE__);
			$sya_plugin =  sprintf(
				'%1$s | ' . __('Version'). ' %2$s | ' . __('Author') . ': %3$s',
				$sya_plugindata['Title'],
				$sya_plugindata['Version'],
				$sya_plugindata['Author']
			);
			?>
			<h3>
				<?php _e('About Simple Yearly Archive', 'simple-yearly-archive'); ?>
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
<?php
}



/**
 * Filter for get_posts
 *
 * @since 1.4.2
 * @author scripts@schloebe.de
 *
 * @param string
 * @return string
 */
function sya_filter_posts_yearly_past( $where = '' ) {
	global $wpdb;
 
	$where .= $wpdb->prepare( " AND year(post_date) < %s", date('Y') );
 
	return $where;
}



/**
 * On plugin activation
 *
 * @since 1.3.2
 * @author scripts@schloebe.de
 */
function sya_activate() {
	#ob_start();
	set_default_options();
	#trigger_error(ob_get_contents(), E_USER_ERROR);
}
register_activation_hook( __FILE__, 'sya_activate' );
?>