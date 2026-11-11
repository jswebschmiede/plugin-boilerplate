<?php
/**
 * Text settings field.
 *
 * @var string $name        Option name.
 * @var string $value       Current value.
 * @var string $description Field description.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

\defined( 'ABSPATH' ) || exit;

$name        ??= '';
$value       ??= '';
$description ??= '';
?>
<input
	type="text"
	class="regular-text"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo esc_attr( $name ); ?>"
	value="<?php echo esc_attr( $value ); ?>"
/>
<p class="description"><?php echo esc_html( $description ); ?></p>
