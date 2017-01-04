<?php
/**
 * Plugin Name:Virtual Developers Projects creator
 * Description: To Create Project	
 * Version: 1.0.0
 * Author: Tarun Kumar
 * Author URI: http://www.tarun.pro
 * */ 
 
 
 function vd_project_post_type() {
  $labels = array(
    'name'               => _x( 'Project', 'post type general name' ),
    'singular_name'      => _x( 'Project', 'post type singular name' ),
    'add_new'            => _x( 'Add New', 'project' ),
    'add_new_item'       => __( 'Add New Project' ),
    'edit_item'          => __( 'Edit Project' ),
    'new_item'           => __( 'New Project' ),
    'all_items'          => __( 'All Projects' ),
    'view_item'          => __( 'View Project' ),
    'search_items'       => __( 'Search Projects' ),
    'not_found'          => __( 'No Projects found' ),
    'not_found_in_trash' => __( 'No Projects found in the Trash' ), 
    'parent_item_colon'  => '',
    'menu_name'          => 'Projects'
  );
  $args = array(
    'labels'        => $labels,
    'description'   => 'Holds our projects and project\'s specific data',
    'public'        => true,
    'menu_position' => 5,
    'menu_icon'     => 'dashicons-networking',
    'supports'      => array( 'title', 'editor','author'),
    'has_archive'   => true,
  );
  register_post_type( 'project', $args ); 
}
add_action( 'init', 'vd_project_post_type' );
 
 
 function vd_project_messages( $messages ) {
  global $post, $post_ID;
  $messages['project'] = array(
    0 => '', 
    1 => sprintf( __('Project updated. <a href="%s">View project</a>'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Project updated.'),
    5 => isset($_GET['revision']) ? sprintf( __('Project restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Project published. <a href="%s">View project</a>'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Project saved.'),
    8 => sprintf( __('Project submitted. <a target="_blank" href="%s">Preview project</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Project scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview project</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Project draft updated. <a target="_blank" href="%s">Preview project</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );
  return $messages;
}
add_filter( 'post_updated_messages', 'vd_project_messages' );


function vd_project_contextual_help( $contextual_help, $screen_id, $screen ) { 
  if ( 'product' == $screen->id ) {

    $contextual_help = '<h2>Projects</h2>
    <p>Products show the details of the items that we sell on the website. You can see a list of them on this page in reverse chronological order - the latest one we added is first.</p> 
    <p>You can view/edit the details of each product by clicking on its name, or you can perform bulk actions using the dropdown menu and selecting multiple items.</p>';

  } elseif ( 'edit-product' == $screen->id ) {

    $contextual_help = '<h2>Editing projects</h2>
    <p>This page allows you to view/modify product details. Please make sure to fill out the available boxes with the appropriate details (product image, price, brand) and <strong>not</strong> add these details to the product description.</p>';

  }
  return $contextual_help;
}
add_action( 'contextual_help', 'vd_project_contextual_help', 10, 3 );


function vd_skills() {
  $labels = array(
    'name'              => _x( 'skills', 'taxonomy general name' ),
    'singular_name'     => _x( 'skill', 'taxonomy singular name' ),
    'search_items'      => __( 'Search Skills' ),
    'all_items'         => __( 'All Skills' ),
    'parent_item'       => __( 'Parent Skill' ),
    'parent_item_colon' => __( 'Parent Skill:' ),
    'edit_item'         => __( 'Edit Skill' ), 
    'update_item'       => __( 'Update Skill' ),
    'add_new_item'      => __( 'Add New Skill' ),
    'new_item_name'     => __( 'New Skill' ),
    'menu_name'         => __( 'Skills' ),
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => true,
  );
  register_taxonomy( 'skill', 'project', $args );
}

add_action( 'init', 'vd_skills', 0 );


add_action( 'add_meta_boxes', 'project_price_box' );
function project_price_box() {
    add_meta_box( 
        'project_price_box',
        __( 'Budget', 'budget' ),
        'project_price_box_content',
        'project',
        'side',
        'high'
    );
}

function project_price_box_content( $post ) {
  $project_budget_currency = get_post_meta($post->ID, 'project_budget_currency', true);
  $project_budget_min = get_post_meta($post->ID, 'project_min_budget', true);
  $project_budget_max = get_post_meta($post->ID, 'project_max_budget', true);
  
  wp_nonce_field( plugin_basename( __FILE__ ), 'project_price_box_content_nonce' );
  echo '<label for="project_currency"><b>Currency</b></label>&nbsp;&nbsp;';
  echo '<select id="project_currency" name="project_budget_currency">
            <option value="inr" '.selected( $project_budget_currency, "inr", false ).'>INR</option>
            <option value="usd" '.selected( $project_budget_currency, "usd", false ).'>USD</option>
        </select><br><br>';
  echo '<label for="project_min_budget"><b>Min</b></label>&nbsp;&nbsp;';
  echo '<input type="text" id="project_min_budget" name="project_budget_min" placeholder="enter mimimum budget" value="'.$project_budget_min.'" /><br><br>';
  echo '<label for="project_max_budget"><b>Max</b></label>&nbsp;&nbsp;';
  echo '<input type="text" id="project_max_budget" name="project_budget_max" placeholder="enter maximum budget" value="'.$project_budget_max.'" />';
}


add_action( 'save_post', 'product_price_box_save' );
function product_price_box_save( $post_id ) {

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
  return;

  if ( !wp_verify_nonce( $_POST['project_price_box_content_nonce'], plugin_basename( __FILE__ ) ) )
  return;

  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
    return;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
    return;
  }
  $project_budget_currency = $_POST['project_budget_currency'];
  $project_min_price = $_POST['project_budget_min'];
  $project_max_price = $_POST['project_budget_max'];
  update_post_meta( $post_id, 'project_budget_currency', $project_budget_currency);
  update_post_meta( $post_id, 'project_min_budget', $project_min_price);
  update_post_meta( $post_id, 'project_max_budget', $project_max_price);
}

include_once plugin_dir_path(__FILE__) . "vd_list.php";

?>
