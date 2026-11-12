<?php
/**
 * Textarea settings field.
 *
 * @var string $id          Textarea id, matches the row label.
 * @var string $name        Textarea name.
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
<textarea
	class="large-text"
	rows="4"
	id="<?php echo esc_attr( $id ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	<?php echo '' !== $description ? 'aria-describedby="' . esc_attr( $id . '-description' ) . '"' : ''; ?>
><?php echo esc_textarea( (string) $value ); ?></textarea>
<?php if ( '' !== $description ) : ?>
	<p class="description" id="<?php echo esc_attr( $id . '-description' ); ?>"><?php echo esc_html( $description ); ?></p>
<?php endif; ?>
