<?php
/*
Plugin Name: Simple Yearly Archive
Version: 1.7.0
Plugin URI: http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/
Description: A simple, clean yearly list of your archives.
Author: Oliver Schl&ouml;be
Author URI: http://www.schloebe.de/
Text Domain: simple-yearly-archive
Domain Path: /languages

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

class SimpleYearlyArchive {
	private static $instance = null;
	private $plugin_path;
	private $plugin_url;
	private $gmt_offset;
	private $sort_order;
	private $post_status;
    public	$text_domain = 'simple-yearly-archive';
    private $slug = 'simple-yearly-archive';
    private $shortcode = 'SimpleYearlyArchive';
    private $plugin_version = '1.7.0';

	/**
	 * Creates or returns an instance of this class.
	 */
	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
	 */
	private function __construct() {
		$this->plugin_path	= plugin_dir_path( __FILE__ );
		$this->plugin_url	= plugin_dir_url( __FILE__ );
		$this->gmt_offset	= get_option('gmt_offset')*3600;
		$this->sort_order	= $this->get_archive_order();
		$this->post_status	= $this->get_archive_order();

		load_plugin_textdomain( $this->text_domain, false, $this->plugin_path . '/languages' );

		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );

		//add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		//add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
		
		add_action( 'wp_head', array( $this, 'html_head' ) );
		
		add_action( 'the_content', array( $this, 'parse_inline' ), 1 );
		
		add_shortcode( $this->shortcode, array( $this, 'register_shortcode' ) );

		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		$this->run();
	}
	
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
	function get( $format, $excludeCat='', $includeCat='', $posttype, $dateformat ) {
		global $wpdb, $PHP_SELF, $wp_version;
		
		$this->post_type = $posttype;
		
		$sya_locale = WPLANG;
		if( defined('ICL_LANGUAGE_CODE') )
			$sya_locale = $this->wpml_get_locale_from_code(ICL_LANGUAGE_CODE, $sya_locale);
		
		setlocale(LC_TIME, $sya_locale);
		
		$now = gmdate("Y-m-d H:i:s",(time()+$this->gmt_offset));
		$allcatids = get_all_category_ids();
		$sya_post_types = array_keys( get_post_types() );
		$yeararray = array();
		$output = '';
		
		if( !in_array($posttype, $sya_post_types) ) {
			$output .= "<p>" . sprintf(__('The post type "%s" does not seem to be registered or available.'), $posttype) . "</p>";
			$output = apply_filters('sya_archive_output', $output);
			return $output;
		}
		
		$output .= '<div class="sya_container" id="sya_container">';
		$syaargs_includecats = '';
		if ($excludeCat != '' || $includeCat != '') { // there are excluded or included categories
			$excludeCats = explode( ",", trim($excludeCat) );
			if( trim( $includeCat ) == '' )
				$includeCats = array_diff( $allcatids, $excludeCats );
			else
				$includeCats = explode( ",", trim( $includeCat ) );
		
			$syaargs_includecats = implode(",", $includeCats);
		}
		
		$syaargs = array(
			'no_found_rows'		=> 1,
			'post_type'			=> $posttype,
			'numberposts'		=> -1,
			'post_status'		=> $this->get_archive_post_statuses(),
			'orderby'			=> 'post_date',
			'order'				=> $this->sort_order,
			'suppress_filters'	=> false
		);
		
		($syaargs_includecats != '' ? $syaargs['category'] = $syaargs_includecats : '');
		
		$this->setup_args( $format, $syaargs );
		
		$posts = get_posts( $syaargs );
		
		#echo "<pre>";
		#echo print_r( $syaargs, true );
		#echo "</pre>";
		
		/*if( $format == 'yearly_past' ) {
			remove_filter( 'posts_where', array( $this, 'filter_posts_yearly_past' ) );
		}*/
		
		$jmb = array();
		foreach( $posts as $jahrMitBeitrag ) {
			$jmb[] = date('Y', strtotime( $jahrMitBeitrag->post_date ));
		}
		$years = array_unique( $jmb );
		
		#$wpdb->show_errors();
		
		#echo "<pre>";
		#echo print_r( $syaargs, true );
		#echo "</pre>";
		
		$allposts = $this->get_archive_posts( $posts );
		
		#echo "<pre>";
		#echo print_r( $allposts, true );
		#echo "</pre>";
		
		if( get_option('sya_showyearoverview') == TRUE ) {
			$output .= '<p class="sya_yearslist" id="sya_yearslist">' . implode( ' &bull; ', $this->get_overview( $yeararray ) ) . '</p>';
		}
		
		
		$before = get_option('sya_prepend');
		$after = get_option('sya_append');
		$indent = ((get_option('sya_excerpt_indent')=='') ? '0' : get_option('sya_excerpt_indent'));
		$excerpt_max_chars = ((get_option('sya_excerpt_maxchars')=='') ? '0' : get_option('sya_excerpt_maxchars'));
		$date_format = (($dateformat=='') ? get_option('sya_dateformat') : $dateformat);
		
		if( is_array( $allposts ) && count( $allposts ) > 0 ) {
			foreach( $years as $currentYear ) {
				#echo "<pre>";
				#echo print_r( $currentYear, true );
				#echo "</pre>";
					
				$year = $currentYear;
				
				if( get_option('sya_collapseyears')==TRUE ) {
					$linkyears_prepend = '<a href="#" onclick="this.parentNode.nextSibling.style.display=(this.parentNode.nextSibling.style.display!=\'none\'?\'none\':\'\');return false;">';
					$linkyears_append = '</a>';
				} elseif( get_option('sya_linkyears')==TRUE ) {
					$linkyears_prepend = '<a href="' . get_year_link( $year ) . '" rel="section">';
					$linkyears_append = '</a>';
				} else {
					$linkyears_prepend = '';
					$linkyears_append = '';
				}
				
				if( count( $allposts[$currentYear] ) > 0 ) {
					$listitems = '';
					foreach( $allposts[$currentYear] as $post ) {
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
						$listitems .= ('<span class="sya_date">' . utf8_encode(strftime($date_format, strtotime($post->post_date))) . ' ' . get_option('sya_datetitleseperator') . ' </span><a href="' . get_permalink($post->ID) . '" class="post-' . $post->ID . '" rel="bookmark" title="' . esc_attr( $post->post_title ) . '">' . $langtitle . '</a>');
						
						if( $post->comment_status != 'closed' && get_option('sya_commentcount') == TRUE ) {
							$listitems .= ' (' . $post->comment_count . ')';
						}
						if( get_option('sya_show_categories')==TRUE ) {
							$sya_categories = array();
							foreach( wp_get_post_categories( $post->ID ) as $cat_id ) {
								$sya_categories[] = get_cat_name( $cat_id );
							}
							if( count($sya_categories) > 0 )
								$listitems .= ' <span class="sya_categories">(' . implode(', ', $sya_categories) . ')</span>';
							$sya_categories = '';
						}
						if( get_option('sya_showauthor')==TRUE ) {
							$userinfo = get_userdata( $post->post_author );
							$listitems .= ' <span class="sya_author">(' . __('by') . ' ' . $userinfo->display_name . ')</span>';
						}
						$excerpt = '';
						if( get_option('sya_excerpt')==TRUE ) {
							if ( $excerpt_max_chars != '0' ) {
								if( !empty( $post->post_excerpt ) ) {
									$excerpt = substr($post->post_excerpt, 0, strrpos(substr($post->post_excerpt, 0, $excerpt_max_chars), ' ')) . '...';
								}
							} else {
								$excerpt = $post->post_excerpt;
							}
							$listitems .= '<br /><div style="padding-left:'.$indent.'px" class="robots-nocontent"><cite>' . wp_strip_all_tags( $excerpt ) . '</cite></div>';
						}
						$listitems .= '</li>';
					}
					
					$output .= $before . '<a id="year' . $year . '"></a>' . $linkyears_prepend . $year . $linkyears_append;
					if( get_option('sya_postcount')==TRUE ) {
						$postcount = count( $allposts[$currentYear] );
						$output .= ' <span class="sya_yearcount">(' . $postcount . ')</span>';
					}
					$additional_css = (get_option('sya_collapseyears')==TRUE ? ' style="display:none;"' : '');
					$output .= $after . '<ul' . $additional_css . '>' . $listitems . '</ul>';
				}
			}
		} else {
			$output .= __('No posts found.');
		}
		
		
		if( get_option('sya_linktoauthor') == TRUE ) {
			$linkvar = __('Plugin by', 'simple-yearly-archive') . ' <a href="http://www.schloebe.de" title="' . __('Plugin by', 'simple-yearly-archive') . ' Oliver Schl&ouml;be">Oliver Schl&ouml;be</a>';
			$output .= '<div style="text-align:right;font-size:90%;">' . $linkvar . '</div>';
		}
		
		$output .= "</div>";
		$output = apply_filters('sya_archive_output', $output);
		
		return $output;
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
	function display( $format='yearly', $excludeCat='', $includeCat='', $posttype='post', $dateformat='' ) {
		echo $this->get( $format, $excludeCat, $includeCat, $posttype, $dateformat );
	}
	
	
	/**
	 * Retrieve all posts for listing
	 *
	 * @since 1.7.0
	 * @author scripts@schloebe.de
	 *
	 * @param array|object
	 * @return array|object
	 */
	function get_archive_posts( $posts ) {
		global $wpdb;
		
		$_post_status = ( current_user_can('read_private_posts') ) ? "'private', 'publish'" : "'publish'";
		foreach( $posts as $post ) {
			/*
			 * $wpdb direct SQL queries are waaaay less memory consuming than qet_posts (with 1000+ posts)
			*/
			$_year = date('Y', strtotime($post->post_date));
				
			$_query = array();
			$_query[] = 'SELECT post.ID, post.post_title, post.post_date, YEAR(post.post_date) as post_year, post.post_status, post.comment_count, post.comment_status, post.post_author, post.post_excerpt, term_rel.term_taxonomy_id';
			if (defined('ICL_LANGUAGE_CODE'))
				$_query[] = ', icl_translations.*';
			$_query[] = 'FROM `' . $wpdb->posts . '` AS post';
			if( defined('ICL_LANGUAGE_CODE') )
				$_query[] = 'LEFT JOIN `' . $wpdb->prefix . 'icl_translations` icl_translations ON post.ID = icl_translations.element_id';
			$_query[] = 'LEFT JOIN `' . $wpdb->postmeta . '` meta ON post.ID = meta.post_id';
			$_query[] = 'LEFT JOIN `' . $wpdb->term_relationships . '` term_rel ON post.ID = term_rel.object_id';
			$_query[] = 'WHERE post.post_type IN ( "' . $this->post_type . '" )';
			$_query[] = 'AND post.post_status IN ( ' . $_post_status . ' )';
			$_query[] = 'AND post.ID = "' . $post->ID . '"';
			if( defined('ICL_LANGUAGE_CODE') )
				$_query[] = 'AND icl_translations.language_code = "' . ICL_LANGUAGE_CODE . '"';
			$_query[] = 'GROUP BY post.ID';
			$_query[] = 'ORDER BY post_date ' . $this->sort_order;
			$_query = implode(' ', $_query);
		
			$year_posts = $wpdb->get_row( $_query, OBJECT );
		
			$allposts[$_year][] = $year_posts;
		}
		
		return $allposts;
	}
	

	/**
	 * Return allowed post statuses
	 *
	 * @since 1.7.0
	 * @author scripts@schloebe.de
	 *
	 * @return array
	 */
	function get_archive_post_statuses() {
		return ( current_user_can('read_private_posts') ) ? array('private', 'publish') : array('publish');
	}
	

	/**
	 * Return the archive's sort order
	 *
	 * @since 1.7.0
	 * @author scripts@schloebe.de
	 *
	 * @return string
	 */
	function get_archive_order() {
		return ( get_option('sya_reverseorder') == true ) ? 'ASC' : 'DESC';
	}
	

	/**
	 * Set up args for get_posts()
	 *
	 * @since 1.7.0
	 * @author scripts@schloebe.de
	 *
	 * @param string
	 * @param int|string
	 */
	function setup_args( $format, &$syaargs ) {
		if( $format == 'yearly_act' ) {
			$syaargs['year'] = date('Y');
		} else if( $format == 'yearly_past' ) {
			$syaargs['date_query']['before'] = array(
				'year'  => date('Y')-1,
				'month' => 12,
				'day'   => 31
			);
			$syaargs['date_query']['inclusive'] = true;
		} else if( preg_match("/^([0-9]{1,})-([0-9]{1,})/", $format) ) {
			preg_match("/^([0-9]{1,})-([0-9]{1,})/", $format, $from_to_arr);
			$date_from = $from_to_arr[1]+$this->gmt_offset;
			$date_to = $from_to_arr[2]+$this->gmt_offset;
			#echo "<pre>";
			#echo print_r( $from_to_arr, true );
			#echo "</pre>";
			$syaargs['date_query']['after'] = array(
				'year'  => date('Y', $date_from),
				'month' => date('m', $date_from),
				'day'   => date('d', $date_from)
			);
			$syaargs['date_query']['before'] = array(
				'year'  => date('Y', $date_to),
				'month' => date('m', $date_to),
				'day'   => date('d', $date_to)
			);
			$syaargs['date_query']['inclusive'] = true;
		} else if( preg_match("/^[0-9]{4}$/", $format) ) {
			$syaargs['year'] = $format;
		}
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
	function get_overview( $yeararray ) {
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
	function html_head() {
		echo "\n" . '<!-- Using Simple Yearly Archive Plugin v' . $this->get_plugin_version() . ' | http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/ // -->' . "\n";
	}

	/**
	 * Get language locale for ISO code for WPML
	 *
	 * @since 1.6.2
	 * @author scripts@schloebe.de
	 *
	 * @param $code string
	 * @param $wplang string
	 * @return string
	 */
	function wpml_get_locale_from_code( $code, $wplang ) {
		global $wpdb;
		
		$_q = "SELECT * FROM `" . $wpdb->prefix . "icl_locale_map` WHERE code = %s";
		$locale_code = $wpdb->get_row($wpdb->prepare($_q, $code), ARRAY_A);
		
		return ($locale_code['locale'] == '' ? $wplang : $locale_code['locale']);
	}

	/**
	 * Setups the plugin's shortcode
	 *
	 * @since 1.1.0
	 * @author scripts@schloebe.de
	 *
	 * @param mixed
	 * @return string
	*/
	function register_shortcode( $atts ) {
		extract(shortcode_atts(array(
			'type' => 'yearly',
			'exclude' => '',
			'include' => '',
			'posttype' => 'post',
			'dateformat' => ''
		), $atts, $this->shortcode));
		
		return $this->get( $type, $exclude, $include, $posttype, $dateformat );
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
	function parse_inline( $post ) {
		if( substr_count($post, '<!--simple-yearly-archive-->') > 0 ) {
			$sya_archives = $this->get( $format, $excludeCat );
			$post = str_replace('<!--simple-yearly-archive-->', $sya_archives, $post);
		}
		return $post;
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
	
	

	public function get_plugin_url() {
		return $this->plugin_url;
	}

	public function get_plugin_path() {
		return $this->plugin_path;
	}

	public function get_plugin_version() {
		return $this->plugin_version;
	}

	public function get_plugin_slug() {
		return $this->slug;
	}

    /**
     * Place code that runs at plugin activation here.
     */
    public function activation() {
    	$this->set_default_options();
	}

    /**
     * Place code that runs at plugin deactivation here.
     */
    public function deactivation() {}

    /**
     * Enqueue and register JavaScript files here.
     */
    public function register_scripts() {}

    /**
     * Enqueue and register CSS files here.
     */
    public function register_styles() {}

    /**
     * Place code for your plugin's functionality here.
     */
    private function run() {}
}

add_action( 'plugins_loaded', array( 'SimpleYearlyArchive', 'get_instance' ) );

if( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/simple-yearly-archive-admin.php' );
	add_action( 'plugins_loaded', array( 'SimpleYearlyArchive_Admin', 'get_instance' ) );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/authorplugins.inc.php' );
}