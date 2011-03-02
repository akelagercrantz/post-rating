<?php
/**
 * @package PostRating
 * @version 0.1
 */
/*
Plugin Name: Post rating
Plugin URI: http://norbyit.se/blog/tag/post-rating
Description: A simple rating plugin.
Version: 0.1
Author: Åke Lagercrantz
Author URI: http://norbyit.se/
*/

/*  Copyright 2010  Åke Lagercrantz  (email : ake@norbyit.se)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( "PostRating" ) ) {
  
  /**
   * The main class. This class contains all the functionality of the plugin.
   *
   * @package PostRating
   * @since 0.1
   */
  class PostRating {
    public static $rating_meta_key = "rating";
    
    // Will hold the admin object.
    public $admin = null;
    
    /**
     * The constructor function. Load textdomain, add actions to some hooks etc.
     *
     * @package PostRating
     * @since   0.1
     */
    function PostRating() {
      // Load textdomain with language files.
      $path_to_translations = plugin_basename( dirname( __FILE__ ) . '/translations' );
      load_plugin_textdomain( 'post_rating', '', $path_to_translations );
      
      // Actions
      add_action( 'wp_print_styles', array( $this, 'enqueue_stylesheets' ) );

      // Filters

    }
    
    /**
     * Tells wordpress to load the stylesheet file.
     *
     * @package PostRating
     * @since   0.1
     */
    function enqueue_stylesheets() {
      wp_enqueue_style( 'post-rating', plugins_url( '/stylesheets/post-rating.css', __FILE__ ) );
    }
    
    /**
     * Returns the rating of a post.
     *
     * @package PostRating
     * @since   0.1
     * @param   integer   $post_id  The post ID.
     * @return  array               An array containing two floats, the rating and the maximum rating.
     */
    function get_rating( $post_id ) {
      $rating = floatval( get_post_meta( $post_id, PostRating::$rating_meta_key, true ) );
      $maximum_rating = $this->maximum_rating();

      return array( $rating, $maximum_rating );
    }
    
    /**
     * Returns the maximum rating as set in the admin panel, defaults to 5.
     *
     * @package PostRating
     * @since   0.1
     * @return  float       The maximum rating.
     */
    function maximum_rating() {
      $options = get_option( 'post_rating_options' );
      $maximum_rating = floatval( $options['maximum_rating'] );
      
      if ( empty( $maximum_rating ) or $maximum_rating == 0 )
        $maximum_rating = 5;
        
      return $maximum_rating;
    }
    
    /**
     * Returns the top {$num} number of posts sorted by rating.
     * Remember to use wp_reset_postdata() after finishing the have_posts() loop!
     *
     * @package PostRating
     * @since   0.1
     * @param   array   $args   An array of arguments to pass to the WP_Query object.
     * @return  stdObj          The query object.
     */
    function top_rated( $args = array() ) {
      $defaults = array(
        'post_type' => 'post',
        'meta_key' => PostRating::$rating_meta_key,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'posts_per_page' => '5',
        'offset' => 0
      );
      
      return new WP_Query( array_merge( $defaults, $args ) );
    }
    
    
    /**
     * Prints out the rating. Must be in the loop.
     *
     * @package PostRating
     * @since   0.1
     * @param   string    $format   The format to output. Either stars or text.
     */
    function print_rating( $format ) {
      global $post, $post_rating;

      $rating = $post_rating->get_rating( $post->ID );

      $width = 23 * $rating[0];  
      $width_bg = 23 * $rating[1];

      echo "<div class='post-rating'>";
      if ( $format == "stars" ) {
        echo "<span class='post-rating post-rating-max' title='" .join( ' / ', $rating ). "' style='width: {$width_bg}px;'></span>";
        echo "<span class='post-rating' title='" .join( ' / ', $rating ). "' style='width: {$width}px;'></span>";
      }
      else
        echo "Rating: " . join( ' / ', $rating );
      echo "</div>";
    }
  
  
  } # class PostRating
  
}

/**
 * We initialize the PostRating class, and reference it in a global variable.
 *
 * @package PostRating
 * @since   0.1
 * @global  object    $post_rating
 */
function post_rating_init() {
  global $post_rating;
  
  $post_rating = new PostRating();
  
  if ( is_admin() ) {
    $post_rating->admin = new PostRatingAdmin();
  }
}

/**
 * Wrapper for the print_rating function.
 *
 * @package PostRating
 * @since   0.1
 * @param   string    $format   The format to output. Either stars or text. Default is stars.
 */
function post_rating( $format = "stars" ) {
  global $post_rating;
  
  $post_rating->print_rating( $format );
}

// Load the admin class if an administration page is being displayed.
if ( is_admin() )
  require_once('post-rating-admin.php');

// Start the whole thing up by registering the init function.
add_action( 'init', 'post_rating_init' );

?>