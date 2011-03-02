<?php
/**
 * @package PostRating
 * @version 0.1
 */

if ( ! class_exists( "PostRatingAdmin" ) ) {
  
  /**
   * The admin class. This class contains all the functionality related to the admin panel, and settings.
   *
   * @package PostRating
   * @since 0.1
   */
  class PostRatingAdmin {
    public static $settings_group = "post_rating_options";
    public static $settings_page = "post-rating-options";
    
    /**
     * The constructor function.
     *
     * @package PostRating
     * @since 0.1
     */
    function PostRatingAdmin() {
      global $post_rating;
      
      // Actions
      add_action( 'admin_menu',       array( $this, 'register_admin_menu_items' ) );
      add_action( 'admin_init',       array( $this, 'register_settings_and_fields' ) );
      add_action( 'add_meta_boxes',   array( $this, 'add_post_meta_box' ) );
      add_action( 'save_post',        array( $this, 'save_post_meta' ) );
      
      // Filters
      add_filter( 'plugin_row_meta',  array( $this, 'add_settings_link_to_plugin_page' ), 10, 2 );
      
    }
    
    /**
     * Adds a link in the admin menu under the Options menu item.
     * 
     * @package PostRating
     * @since   0.1
     */
    function register_admin_menu_items() {
      $options_page = add_options_page( "PostRating", "Post rating", "manage_options", "post-rating", array( $this, "options_page_html" ) );
    }
    
    /**
     * Prints out the html
     *
     * @package PostRating
     * @since   0.1
     */
    function options_page_html() {
      if (!current_user_can('manage_options')) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'post-rating' ) );
      } ?>

      <div>
        <h2><?php echo __( "Post rating", 'post-rating' ); ?></h2>
        <form action="options.php" method="post">
          <?php settings_fields( PostRatingAdmin::$settings_group ); ?>
          <?php do_settings_sections( PostRatingAdmin::$settings_page ); ?>

          <input name="Submit" type="submit" value="<?php echo __( 'Save Changes', 'post-rating' ); ?>" />
        </form>
      </div>

    <?php }
    
    /**
     * Registers settings, sections and fields.
     *
     * @package PostRating
     * @since   0.1
     */
    function register_settings_and_fields() {
      register_setting( PostRatingAdmin::$settings_group, 'post_rating_options', array( $this, 'validate_options' ) );
      add_settings_section( 'post_rating_options_section', __( 'Options', 'post-rating' ), array( $this, 'section_text'), PostRatingAdmin::$settings_page, 'post_rating_options' );
      add_settings_field( 'post_rating_maximum_rating', __( 'Maximum rating', 'post-rating' ), array( $this, 'maximum_rating_input' ), PostRatingAdmin::$settings_page, 'post_rating_options_section' );
    }
    
    
    /**
     * Displays the informational text under the options section.
     *
     * @package PostRating
     * @since   0.1
     */
    function section_text() {
      echo __( 'Post rating options.', 'post-rating' );
    }
    
    /**
     * Displays the input field for the maximum rating.
     *
     * @package PostRating
     * @since   0.1
     */
    function maximum_rating_input() {
      $options = get_option( 'post_rating_options' );

      $maximum_rating = floatval( $options['maximum_rating'] );
      if ( $maximum_rating == 0 )
        $maximum_rating = 5;
      echo "<input id='post_rating_maximum_rating' name='post_rating_options[maximum_rating]' type='number' step='0.1' min='0.1' value='{$maximum_rating}'>";
    }
    
    /**
     * Validates the options prior to saving.
     *
     * @package PostRating
     * @since   0.1
     * @param   array   $input  An array containing the new values.
     * @return  array           An array containing the final data pending insertion.
     */
    function validate_options( $input ) {
      $options = get_option( 'post_rating_options' );

      $options['maximum_rating'] = floatval( $input['maximum_rating'] );
      
      return $options;
    }
    
    /**
     * Adds the meta box to the post editing page.
     *
     * @package PostRating
     * @since   0.1
     */
    function add_post_meta_box() {
      add_meta_box( 'post-rating', 'Post rating', array( $this, 'rating_input' ), "post", 'side', 'low' );
    }
    
    /**
     * Displays the input for the post rating.
     *
     * @package PostRating
     * @since   0,1
     * @param   stdobj  $post   The post object.
     */
    function rating_input( $post ) {
      global $post_rating;
      $rating = $post_rating->get_rating( $post->ID );
      echo "<input id='post_rating' name='post_rating' type='number' min='0' max='{$rating[1]}' step='0.1' value='{$rating[0]}'>";
    }
    
    /**
     * Gets called after a post has been created or edited. Saves the post rating..
     *
     * @package PostRating
     * @since   0.1
     * @param   integer   $post_id   The post ID.
     */
    function save_post_meta( $post_id ) {
      if ( $parent_id = wp_is_post_revision( $post_id ) )
        $post_id = $parent_id;
        
      if ( isset( $_POST['post_rating'] ) )
        update_post_meta( $post_id, PostRating::$rating_meta_key, floatval( $_POST['post_rating'] ) );
    }
    
    /**
     * Adds a link to the settings page from the plugin list on the plugin page.
     *
     * @package PostRating
     * @since   0.1
     */
    function add_settings_link_to_plugin_page( $links, $file ) {
      if ( $file == "post-rating/post-rating.php" )
        array_push( $links, '<a href="options-general.php?page=post-rating">' . __( 'Settings', 'post-rating' ) . '</a>' );
      
      return $links;
    }
    
  } # class PostRatingAdmin
  
}

?>