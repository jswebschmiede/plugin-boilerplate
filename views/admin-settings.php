<?php
/**
 * Settings page wrapper.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

use Jswebforge\PluginBoilerplate\Controllers\ShortcodeController;
use Jswebforge\PluginBoilerplate\Models\SettingsModel;

\defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">
		<?php
		settings_fields( SettingsModel::OPTION_NAME );
		do_settings_sections( SettingsModel::MENU_SLUG );
		submit_button();
		?>
	</form>

	<aside class="pb:mt-8 pb:max-w-2xl pb:rounded-md pb:border pb:border-slate-300 pb:bg-white pb:p-4">
		<h2 class="pb:mt-0"><?php esc_html_e( 'Usage', 'plugin-boilerplate' ); ?></h2>
		<p><?php esc_html_e( 'Add the shortcode to any post or page. The optional count attribute limits the number of entries (1–20).', 'plugin-boilerplate' ); ?></p>
		<code class="pb:inline-block pb:rounded pb:px-2 pb:py-1">[<?php echo esc_html( ShortcodeController::TAG ); ?> count="5"]</code>
	</aside>
</div>
