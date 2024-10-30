<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link    https://kumaranup594.github.io/
 * @since   1.0.0
 *
 * @package Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/admin/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<!-- Our admin page content should all be inside .wrap -->
<div class="wrap">
	<!-- Print the page title -->
	<h1>ClickSend SMS Settings</h1>
	<!-- Here are our tabs -->
	<nav class="nav-tab-wrapper">
		<?php foreach ( $tabs as $key => $each_tab ) : ?>
			<a href="?page=clicksend-sms&tab=<?php echo esc_html( $key ); ?>" class="nav-tab <?php if ( $active_tab === $key ) : ?>
				nav-tab-active<?php endif; ?>">
				<?php
					echo esc_html( $each_tab['label'] );
				?>
			</a>
		<?php endforeach; ?>
	</nav>
	<div class="tab-content">
		<?php do_action( 'clicksend_admin_tab_content', $active_tab ); ?>
	</div>
</div>
