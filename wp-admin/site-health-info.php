<?php
/**
 * Tools Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can( 'install_plugins' ) ) {
	wp_die( __( 'Sorry, you do not have permission to access the debug data.' ), '', array( 'reponse' => 401 ) );
}

wp_enqueue_style( 'site-health' );
wp_enqueue_script( 'site-health' );

if ( ! class_exists( 'WP_Debug_Data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-debug-data.php' );
}
if ( ! class_exists( 'WP_Site_Health' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-site-health.php' );
}

$health_check_site_status = new WP_Site_Health();

require_once( ABSPATH . 'wp-admin/admin-header.php' );
?>

	<div class="wrap health-check-header">
		<div class="title-section">
			<h1>
				<?php _ex( 'Site Health', 'Menu, Section and Page Title' ); ?>
			</h1>

			<div id="progressbar" class="loading" data-pct="0" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" aria-valuetext="<?php esc_attr_e( 'Site tests are running, please wait a moment.' ); ?>">
				<svg width="100%" height="100%" viewBox="0 0 200 200" version="1.1" xmlns="http://www.w3.org/2000/svg">
					<circle r="90" cx="100" cy="100" fill="transparent" stroke-dasharray="565.48" stroke-dashoffset="0"></circle>
					<circle id="bar" r="90" cx="100" cy="100" fill="transparent" stroke-dasharray="565.48" stroke-dashoffset="0"></circle>
				</svg>
			</div>
		</div>

		<nav class="tabs-wrapper" aria-label="<?php esc_attr_e( 'Secondary menu' ); ?>">
			<a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>" class="tab">
				<?php _e( 'Status' ); ?>
			</a>

			<a href="<?php echo esc_url( admin_url( 'site-health.php?tab=debug' ) ); ?>" class="tab active" aria-current="true">
				<?php _e( 'Info' ); ?>
			</a>
		</nav>

		<div class="wp-clearfix"></div>
	</div>

	<div class="wrap health-check-body">
		<?php
		WP_Debug_Data::check_for_updates();

		$info = WP_Debug_Data::debug_data();
		?>

		<h2>
			<?php _e( 'Site Info' ); ?>
		</h2>

		<p>
			<?php _e( 'You can export the information on this page so it can be easily copied and pasted in support requests such as on the WordPress.org forums, or shared with your website / theme / plugin developers.' ); ?>
		</p>

		<div class="system-information-copy-wrapper">
			<textarea id="system-information-default-copy-field" readonly><?php WP_Debug_Data::textarea_format( $info ); ?></textarea>

			<?php
			if ( 0 !== strpos( get_locale(), 'en' ) ) :

				$english_info = WP_Debug_Data::debug_data( 'en_US' );
				?>
				<textarea id="system-information-english-copy-field" readonly><?php WP_Debug_Data::textarea_format( $english_info ); ?></textarea>

			<?php endif; ?>

			<div class="copy-button-wrapper">
				<button type="button" class="button button-primary health-check-copy-field" data-copy-field="default"><?php _e( 'Copy to clipboard' ); ?></button>
				<span class="copy-field-success" aria-hidden="true">Copied!</span>
			</div>
			<?php if ( 0 !== strpos( get_locale(), 'en' ) ) : ?>
				<div class="copy-button-wrapper">
					<button type="button" class="button health-check-copy-field" data-copy-field="english"><?php _e( 'Copy to clipboard (English)' ); ?></button>
					<span class="copy-field-success" aria-hidden="true">Copied!</span>
				</div>
			<?php endif; ?>
		</div>

		<dl id="health-check-debug" role="presentation" class="health-check-accordion">

			<?php
			foreach ( $info as $section => $details ) {
				if ( ! isset( $details['fields'] ) || empty( $details['fields'] ) ) {
					continue;
				}
				?>
				<dt role="heading" aria-level="3">
					<button aria-expanded="false" class="health-check-accordion-trigger" aria-controls="health-check-accordion-block-<?php echo esc_attr( $section ); ?>" id="health-check-accordion-heading-<?php echo esc_attr( $section ); ?>" type="button">
			<span class="title">
				<?php echo esc_html( $details['label'] ); ?>

				<?php if ( isset( $details['show_count'] ) && $details['show_count'] ) : ?>
					<?php printf( '(%d)', count( $details['fields'] ) ); ?>
				<?php endif; ?>
			</span>
						<span class="icon"></span>
					</button>
				</dt>

				<dd id="health-check-accordion-block-<?php echo esc_attr( $section ); ?>" role="region" aria-labelledby="health-check-accordion-heading-<?php echo esc_attr( $section ); ?>" class="health-check-accordion-panel" hidden="hidden">
					<?php
					if ( isset( $details['description'] ) && ! empty( $details['description'] ) ) {
						printf(
							'<p>%s</p>',
							wp_kses(
								$details['description'],
								array(
									'a'      => array(
										'href' => true,
									),
									'strong' => true,
									'em'     => true,
								)
							)
						);
					}
					?>
					<table class="widefat striped health-check-table">
						<tbody>
						<?php
						foreach ( $details['fields'] as $field ) {
							if ( is_array( $field['value'] ) ) {
								$values = '';
								foreach ( $field['value'] as $name => $value ) {
									$values .= sprintf(
										'<li>%s: %s</li>',
										esc_html( $name ),
										esc_html( $value )
									);
								}
							} else {
								$values = esc_html( $field['value'] );
							}

							printf(
								'<tr><td>%s</td><td>%s</td></tr>',
								esc_html( $field['label'] ),
								$values
							);
						}
						?>
						</tbody>
					</table>
				</dd>
			<?php } ?>
		</dl>
	</div>

<?php
include( ABSPATH . 'wp-admin/admin-footer.php' );
