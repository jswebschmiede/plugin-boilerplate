<?php
/**
 * Select settings field.
 *
 * @var string               $id          Select id, matches the row label.
 * @var string               $name        Select name.
 * @var string               $value       Selected value.
 * @var array<string,string> $choices     Value => label.
 * @var string               $description Field description.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

\defined( 'ABSPATH' ) || exit;

$id          ??= '';
$name        ??= '';
$value       ??= '';
$choices     ??= array();
$description ??= '';
?>
<select
	id="<?php echo esc_attr( $id ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	<?php echo '' !== $description ? 'aria-describedby="' . esc_attr( $id . '-description' ) . '"' : ''; ?>
>
	<?php foreach ( $choices as $choice_value => $choice_label ) : ?>
		<option value="<?php echo esc_attr( (string) $choice_value ); ?>" <?php selected( (string) $choice_value, (string) $value ); ?>>
			<?php echo esc_html( (string) $choice_label ); ?>
		</option>
	<?php endforeach; ?>
</select>
<?php if ( '' !== $description ) : ?>
	<p class="description" id="<?php echo esc_attr( $id . '-description' ); ?>"><?php echo esc_html( $description ); ?></p>
<?php endif; ?>
