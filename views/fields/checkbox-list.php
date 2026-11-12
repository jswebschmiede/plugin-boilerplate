<?php
/**
 * Checkbox list settings field.
 *
 * Without any checked box nothing is submitted; SettingsModel::sanitize()
 * stores an empty list then.
 *
 * @var string               $id          Base id; each box gets "<id>-<value>".
 * @var string               $name        Input name; "[]" is appended.
 * @var string[]             $value       Selected values.
 * @var string               $label       Field label, used as the fieldset legend.
 * @var array<string,string> $choices     Value => label.
 * @var string               $description Field description.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

\defined( 'ABSPATH' ) || exit;

$id          ??= '';
$name        ??= '';
$value       ??= array();
$label       ??= '';
$choices     ??= array();
$description ??= '';
?>
<fieldset>
	<legend class="screen-reader-text"><?php echo esc_html( $label ); ?></legend>
	<?php foreach ( $choices as $choice_value => $choice_label ) : ?>
		<label for="<?php echo esc_attr( $id . '-' . $choice_value ); ?>">
			<input
				type="checkbox"
				id="<?php echo esc_attr( $id . '-' . $choice_value ); ?>"
				name="<?php echo esc_attr( $name ); ?>[]"
				value="<?php echo esc_attr( (string) $choice_value ); ?>"
				<?php checked( in_array( (string) $choice_value, (array) $value, true ) ); ?>
			/>
			<?php echo esc_html( (string) $choice_label ); ?>
		</label><br />
	<?php endforeach; ?>
	<?php if ( '' !== $description ) : ?>
		<p class="description"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>
</fieldset>
