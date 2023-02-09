<?php
/**
 * Generate reports of various system variables and configs, useful for debugging.
 * This only available to administrators.
 *
 * @package Themify
 */
class Themify_System_Status {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}

	public static function admin_menu() {
		$parent = is_plugin_active( 'themify-builder/themify-builder.php' ) ? 'themify-builder' : 'themify';
		add_submenu_page ( $parent, __( 'System Status', 'themify' ), __( 'System Status', 'themify' ), 'manage_options', 'tf-status', array( __CLASS__, 'admin' ) );
	}

	public static function admin() {
		$server_info = isset( $_SERVER['SERVER_SOFTWARE'] ) ? self::sanitize_deep( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		$r=false;
		foreach(array('/etc/lsb-release','/etc/os-release','/etc/redhat-release') as $dist){
			if(is_readable($dist)){
                            $r=@parse_ini_file($dist);
                            break;
			}
		}
		?>
<div class="wrap">
	<h1><?php _e( 'System Status', 'themify' ); ?></h1>
	<table class="tf_status_table widefat" cellspacing="0">
		<thead>
			<tr>
				<th colspan="3"><h2><?php esc_html_e( 'Server environment', 'themify' ); ?></h2></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th><?php esc_html_e( 'OS', 'themify' ); ?>:</th>
				<th>
					<?php if(is_array($r) && (isset($r['PRETTY_NAME']) || isset($r['NAME']))):?>
						<?php if(isset($r['PRETTY_NAME'])):?>
							<?php echo $r['PRETTY_NAME']?>
						<?php else:?>
							<?php echo $r['NAME']?>
						<?php endif;?>
					<?php else:?>
						<?php echo php_uname('s'); ?>
					<?php endif;?>
				</th>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Server info', 'themify' ); ?>:</th>
				<th>
					<?php echo esc_html( $server_info ); ?>
					<br>
					<strong><?php esc_html_e( 'Server IP', 'themify' ); ?></strong>: <?php echo $_SERVER['SERVER_ADDR']; ?>
				</th>
			</tr>
			<tr>
				<td><?php esc_html_e( 'PHP version', 'themify' ); ?>:</td>
				<td>
					<?php
					echo phpversion();
					if ( version_compare( phpversion(), '7.2', '>=' ) ) {
						//
					} else {
						echo '<span class="dashicons dashicons-warning"></span> ' . __( 'We recommend using PHP version 7.2 or above for greater performance and security. Please contact your web hosting provider.', 'themify' );
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Database', 'themify' ); ?>:</td>
				<td><?php echo $GLOBALS['wpdb']->db_server_info(); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Debug Mode', 'themify' ); ?>:</td>
				<td><?php echo ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? __( 'Enabled', 'themify' ) : __( 'Disabled', 'themify' ); ?></td>
			</tr>
			<?php if ( function_exists( 'ini_get' ) ) : ?>
				<tr>
					<td><?php esc_html_e( 'PHP post max size', 'themify' ); ?>:</td>
					<td><?php echo esc_html( size_format( self::let_to_num( ini_get( 'post_max_size' ) ) ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP time limit', 'themify' ); ?>:</td>
					<td><?php echo esc_html( (int) ini_get( 'max_execution_time' ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP memory limit', 'themify' ); ?>:</td>
					<td><?php echo esc_html( size_format( self::get_memory_limit() ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP max input vars', 'themify' ); ?>:</td>
					<td><?php echo esc_html( (int) ini_get( 'max_input_vars' ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'cURL version', 'themify' ); ?>:</td>
					<td><?php echo esc_html( self::get_curl_version() ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'SUHOSIN installed', 'themify' ); ?>:</td>
					<td><?php echo extension_loaded( 'suhosin' ) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
				</tr>
			<?php endif; ?>
			<tr>
				<td><?php esc_html_e( 'Max upload size', 'themify' ); ?>:</td>
				<td><?php echo esc_html( size_format( wp_max_upload_size() ) ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'fsockopen/cURL', 'themify' ); ?>:</td>
				<td>
					<?php
					if ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ) {
						echo '<span class="dashicons dashicons-yes"></span>';
					} else {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Your server does not have fsockopen or cURL enabled - some features that require connecting to external web services may not work. Contact your hosting provider.', 'themify' ) . '</mark>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Custom Tables Allowed', 'themify' ); ?>:</td>
				<td>
					<?php
					if ( Themify_Storage::init()!==false ) {
						echo '<span class="dashicons dashicons-yes"></span>';
					} else {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Your server does not have have permissions to create custom tables in DB', 'themify' ) . '</mark>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'GZip', 'themify' ); ?>:</td>
				<td>
					<?php
					$gzip = TFCache::get_available_gzip();
					if ( false !== $gzip ) {
						$gzip = current( $gzip );
						echo '<span class="dashicons dashicons-yes"></span> ' . $gzip['f'];
					} else {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '<a href="%s">GZIP</a> is recommended for better performance.', 'themify' ), 'https://php.net/manual/en/zlib.installation.php' ) . '</mark>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Multibyte string', 'themify' ); ?>:</td>
				<td>
					<?php
					if ( extension_loaded( 'mbstring' ) ) {
						echo '<span class="dashicons dashicons-yes"></span>';
					} else {
						/* Translators: %s: classname and link. */
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'themify' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' ) . '</mark>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Uploads folder', 'themify' ); ?>:</td>
				<td>
					<?php
					$dir = themify_upload_dir();
					echo '<strong>' . __( 'Base Dir ', 'themify' ) . '</strong>: ' . $dir['basedir'] . '<br>' . '<strong>' . __( 'Base URL ', 'themify' ) . '</strong>: ' . $dir['baseurl'];
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Concate CSS folder', 'themify' ); ?>:</td>
				<td>
					<?php
					$dir = Themify_Enqueue_Assets::getCurrentVersionFolder();
					echo $dir . ' - ';
					if ( Themify_Filesystem::is_writable( $dir ) ) {
						echo '<span class="dashicons dashicons-yes"></span>';
					} else {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Uploads folder is not writeable, your CSS may not display correctly.', 'themify' ) . '</mark>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Image Processing Library', 'themify' ); ?>:</td>
				<td>
					<?php
					$image_editor = _wp_image_editor_choose();
					if ( empty( $image_editor ) ) {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'No image processing library found. Please contact your web hosting to enable this.', 'themify' ) . '</mark>';
					} else {
						echo '<span class="dashicons dashicons-yes"></span>' . $image_editor;
					}
					?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
		<?php
	}

	public static function get_memory_limit() {
		$wp_memory_limit = self::let_to_num( WP_MEMORY_LIMIT );
		if ( function_exists( 'memory_get_usage' ) ) {
			$wp_memory_limit = max( $wp_memory_limit, self::let_to_num( @ini_get( 'memory_limit' ) ) );
		}

		return $wp_memory_limit;
	}

	public static function get_curl_version() {
		$curl_version = '';
		if ( function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();
			$curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
		} elseif ( extension_loaded( 'curl' ) ) {
			$curl_version = __( 'cURL installed but unable to retrieve version.', 'themify' );
		}
		return $curl_version;
	}


	/**
	 * Notation to numbers.
	 *
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
	 *
	 * @param  string $size Size value.
	 * @return int
	 */
	public static function let_to_num( $size ) {
		$l   = substr( $size, -1 );
		$ret = (int) substr( $size, 0, -1 );
		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= PB_IN_BYTES;
				// No break.
			case 'T':
				$ret *= TB_IN_BYTES;
				// No break.
			case 'G':
				$ret *= GB_IN_BYTES;
				// No break.
			case 'M':
				$ret *= MB_IN_BYTES;
				// No break.
			case 'K':
				$ret *= KB_IN_BYTES;
				// No break.
		}
		return $ret;
	}

	/**
	 * Applies sanitize_ function on multidimensional array
	 *
	 * @return mixed
	 */
	public static function sanitize_deep( $value ) {
		if ( is_array( $value ) ) { 
			return array_map( 'wc_clean', $value ); 
		} else { 
			return is_scalar( $value ) ? sanitize_text_field( $value ) : $value; 
		}
	}
}
Themify_System_Status::init();