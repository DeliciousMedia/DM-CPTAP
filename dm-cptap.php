<?php
/**
 * Plugin Name: DM Custom Post Type Archive Page
 * Plugin URI: https://github.com/DeliciousMedia/DM-CPTAP
 * Description: Provides a user interface to save content for use on a custom post type archive page.
 * Author:  Delicious Media Limited
 * Author URI: https://www.deliciousmedia.co.uk/
 * License: GPLv3 or later
 * Version: 1.0.1
 *
 * @package dmcptap
 */

/**
 * Provides the interface to edit page content.
 *
 * @return void
 */
function dmcptap_content_edit_page() {

	$current_screen = get_current_screen();
	$post_type = get_post_type_object( $current_screen->post_type );

	?>
	<div class="wrap">

		<h2>
		<?php
		// translators: This is the name of the custom post type.
		echo esc_html( sprintf( __( '%s Archive Page Content', 'dmcptap' ), $post_type->label ) );
		?>
		</h2>
		<?php
		if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) {
			echo '<div class="updated"><p>' . esc_html( 'Archive page content updated.', 'dmcptap' ) . '</p></div>';
		}
		?>

		<form method="post" action="<?php admin_url( 'edit.php?post_type=' . $post_type->name . '&page=dmcptap-' . $post_type->name . '-content' ); ?>">
			<?php wp_nonce_field( 'dmcptap_content_edit_page' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="title"><?php esc_html_e( 'Title', 'dmcptap' ); ?>:</label></th>
					<td><input type="text" name="data[title]" id="title" class="regular-text" value="<?php echo esc_html( dmcptap_get_content_item( $post_type->name, 'title' ) ); ?>"></td>
				</tr>

				<tr>
					<th><label for="content"><?php esc_html_e( 'Content', 'dmcptap' ); ?>:</label></th>
					<td><?php wp_editor( dmcptap_get_content_item( $post_type->name, 'content' ), 'content', [ 'textarea_name' => 'data[content]' ] ); ?></td>
				</tr>
			</table>

			<?php submit_button( __( 'Save Content', 'dmcptap' ), 'primary', 'submit', false ); ?>

		</form>

	</div><!--End .wrap-->

	<?php
}

/**
 * Saves the archive page content.
 *
 * @return void
 */
function dmcptap_update_options() {

	$current_screen = get_current_screen();
	$post_type = get_post_type_object( $current_screen->post_type );

	if ( ! isset( $_POST['submit'] ) ) {
		return;
	}

	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'dmcptap_content_edit_page' ) ) {
		wp_die( esc_html( __( 'Error processing request.', 'dmcptap' ) ) );
	}

	if ( ! empty( $_POST['data'] ) ) {

		$content = [];

		foreach ( $_POST['data'] as $key => $value ) {
			if ( ! empty( $_POST['data'][ $key ] ) ) {
				if ( 'content' === $key ) {
					$content[ $key ] = wp_kses_post( $_POST['data'][ $key ] );
				} else {
					$content[ $key ] = sanitize_text_field( $_POST['data'][ $key ] );
				}
			}
		}

		if ( ! empty( $content ) ) {
			update_option( 'dmcptap_' . $post_type->name, $content );
		}
	}

	wp_safe_redirect( admin_url( 'edit.php?post_type=' . $post_type->name . '&page=dmcptap-' . $post_type->name . '-content&updated=true' ) );
	exit;

}

/**
 * Adds a submenu item to any CPT which supports DMCPTAP and hook our function to save changes into the page load hook.
 *
 * @return void
 */
function dmcptap_add_submenus() {

	$cpts = dmcptap_get_supporting_cpts();

	foreach ( $cpts as $cpt ) {
		$page = add_submenu_page( 'edit.php?post_type=' . $cpt, __( 'Archive Page', 'dmcptap' ), __( 'Archive Page', 'dmcptap' ), 'edit_posts', 'dmcptap-' . $cpt . '-content', 'dmcptap_content_edit_page' );
		add_action( 'load-' . $page, 'dmcptap_update_options' );
	}

}
add_action( 'admin_menu', 'dmcptap_add_submenus' );


/**
 * Helper to return posts supporting custom post type archive pages.
 *
 * @return array
 */
function dmcptap_get_supporting_cpts() {
	return get_post_types_by_support( 'dmcptap_archive_page', 'and' );
}

/**
 * Return an item of content from the archive page.
 *
 * @param  string $post_type Name of the custom post type.
 * @param  string $item      Name of the content item (title|content).
 *
 * @return string|bool
 */
function dmcptap_get_content_item( $post_type, $item ) {
	$content = get_option( 'dmcptap_' . $post_type );
	if ( ! empty( $content[ $item ] ) ) {
		return stripslashes( $content[ $item ] );
	}
	return false;
}
