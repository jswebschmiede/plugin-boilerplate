<?php
/**
 * Text settings field.
 *
 * @var string $id          Input id, matches the row label.
 * @var string $name        Input name.
 * @var string $value       Current value.
 * @var string $description Field description.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

\defined( 'ABSPATH' ) || exit;

$id          ??= '';
$name        ??= '';
$value       ??= '';
$description ??= '';
?>
<input
	type="text"
	class="regular-text"
	id="<?php echo esc_attr( $id ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	value="<?php echo esc_attr( (string) $value ); ?>"
	<?php echo '' !== $description ? 'aria-describedby="' . esc_attr( $id . '-description' ) . '"' : ''; ?>
/>
<?php if ( '' !== $description ) : ?>
	<p class="description" id="<?php echo esc_attr( $id . '-description' ); ?>"><?php echo esc_html( $description ); ?></p>
<?php endif; ?>
