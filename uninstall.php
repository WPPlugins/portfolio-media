<?php
/* delete options */
delete_option( 'pm_pm_general_basic' );
delete_option( 'pm_media_image' );
delete_option( 'pm_media_video' );
delete_option( 'pm_single_layout' );
delete_option( 'pm_single_display' );

/* delete portfolio meta => portfolio media */
$posts_array = get_posts( array( 'post_type' => 'portfolio_media' ) );

foreach( $posts_array as $post ) {
	
	/* delete all media objects that are portfolio media */
	if ( $portfolio_media = get_post_meta( $post->ID, 'pm_portfolio_media', true ) ) {
		
		delete_post_meta( $post->ID, 'pm_portfolio_media' );
		wp_delete_post( $portfolio_media['media_id'] , true );	
	}
	
	wp_delete_post( $post->ID, true );
}
?>