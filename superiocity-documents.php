<?php
/**
 * Plugin Name: Super Documents
 * Version: 1.0.0
 * Description: Allow the upload of documents as it's own post type.
 * Author: Superiocity
 * Author URI: http://www.superiocity.com
 * Plugin URI: http://www.superiocity.com
 * Text Domain: superiocity-documents
 * @package Superiocity Documents
 */

namespace Superiocity;

class Documents {

	public function __construct() 
	{
		add_action( 'plugins_loaded', array( $this, 'check_prerequisites' ) );
		add_action( 'init', array( $this, 'register_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'update_messages' ) );
		add_shortcode( 'list_super_docs', array( $this, 'list_docs' ) );
	}


	public function check_prerequisites()
	{
		if ( is_admin() && ! is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
			?>
			<div class="notice notice-error is-dismissible"><p>Super Documents Requires the <em><a href="https://wordpress.org/plugins/advanced-custom-fields/">Advanced
						Custom Fields</a></em> plugin.</p></div><?
		}
	}


	public function register_type() 
	{
		register_post_type( 'super-document', array(
			'labels'                => array(
				'name'               => __( 'Documents', 'superiocity-documents' ),
				'singular_name'      => __( 'Document', 'superiocity-documents' ),
				'all_items'          => __( 'All documents', 'superiocity-documents' ),
				'new_item'           => __( 'New document', 'superiocity-documents' ),
				'add_new'            => __( 'Add New', 'superiocity-documents' ),
				'add_new_item'       => __( 'Add new document', 'superiocity-documents' ),
				'edit_item'          => __( 'Edit document', 'superiocity-documents' ),
				'view_item'          => __( 'View document', 'superiocity-documents' ),
				'search_items'       => __( 'Search documents', 'superiocity-documents' ),
				'not_found'          => __( 'No documents found', 'superiocity-documents' ),
				'not_found_in_trash' => __( 'No documents found in trash', 'superiocity-documents' ),
				'parent_item_colon'  => __( 'Parent document', 'superiocity-documents' ),
				'menu_name'          => __( 'Documents', 'superiocity-documents' ),
			),
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title' ),
			'menu_position'         => 20,
			'has_archive'           => true,
			'rewrite'               => true,
			'query_var'             => 'super-document',
			'menu_icon'             => 'dashicons-format-aside',
			'show_in_rest'          => true,
			'rest_base'             => 'super-document',
			'rest_controller_class' => 'WP_REST_Posts_Controller',

		) );

	}

	
	public function update_messages( $messages ) 
	{
		global $post;

		$permalink = get_permalink( $post );

		$messages['superiocity_document'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( 'Superiocity document updated. <a target="_blank" href="%s">View superiocity document</a>',
				'superiocity-documents' ), esc_url( $permalink ) ),
			2  => __( 'Custom field updated.', 'superiocity-documents' ),
			3  => __( 'Custom field deleted.', 'superiocity-documents' ),
			4  => __( 'Superiocity document updated.',
				'superiocity-documents' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Superiocity document restored to revision from %s',
				'superiocity-documents' ),
				wp_post_revision_title( (int) $_GET['revision'],
					false ) ) : false,
			6  => sprintf( __( 'Superiocity document published. <a href="%s">View superiocity document</a>',
				'superiocity-documents' ), esc_url( $permalink ) ),
			7  => __( 'Superiocity document saved.', 'superiocity-documents' ),
			8  => sprintf( __( 'Superiocity document submitted. <a target="_blank" href="%s">Preview superiocity document</a>',
				'superiocity-documents' ),
				esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
			9  => sprintf( __( 'Superiocity document scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview superiocity document</a>',
				'superiocity-documents' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ),
					strtotime( $post->post_date ) ),
				esc_url( $permalink ) ),
			10 => sprintf( __( 'Superiocity document draft updated. <a target="_blank" href="%s">Preview superiocity document</a>',
				'superiocity-documents' ),
				esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		);

		return $messages;
	}


	public function list_docs( $atts )
	{
		$args = array(
			'post_type'   => array( 'super-document' ),
			'post_status' => array( 'publish' ),
			'order'       => 'DESC',
			'orderby'     => 'id',
		);

		if ( ! empty( $atts ) &&  is_numeric( $atts['limit'] ) ) {
			$args['posts_per_page'] = $atts['limit'];
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return false;
		}

		$list = '<ul class="super-documents">';
		
		while ( $query->have_posts() ) {
			$query->the_post();
			$file        = get_field( 'file' );
			$link        = $file['url'];
			$desc        = esc_attr( get_field( 'description', get_the_ID() ) );
			$title       = '<h4><a href="' . $link . '" target="document">' . 
			               esc_attr( get_the_title() ) . '</a></h4>';
			$description = '';
			$links       = '<div class="links"><a href="' . $link . '" target="document">view</a> | 
				<a href="' . $link . '" download>download</a></div>';
			
			if ( ! empty ( $desc ) ) {
				$description = '<div class="description">' . $desc. '</div>';
			}
			
			$list .= '<li>' . $title . $description . $links . '</li>';
		}

		$list .= '</ul>';
		
		wp_reset_postdata();
		return $list;
	}
}

new Documents();
