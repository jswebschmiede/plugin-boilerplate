<?php
/**
 * Single checkbox settings field.
 *
 * An unchecked box is not submitted; SettingsModel::sanitize() stores false then.
 *
 * @var string $id          Checkbox id.
 * @var string $name        Checkbox name.
 * @var bool   $value       Whether the box is checked.
 * @var string $label       Field label, used as the fieldset legend.
 * @var string $description Text next to the box.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

\defined( 'ABSPATH' ) || exit;

$id          ??= '';
$name        ??= '';
$value       ??= false;
$label       ??= '';
$description ??= '';
?>
<fieldset>
	<legend class="screen-reader-text"><?php echo esc_html( $label ); ?></legend>
	<label for="<?php echo esc_attr( $id ); ?>">
		<input
			type="checkbox"
			id="<?php echo esc_attr( $id ); ?>"
			name="<?php echo esc_attr( $name ); ?>"
			value="1"
			<?php checked( (bool) $value ); ?>
		/>
		<?php echo esc_html( '' !== $description ? $description : $label ); ?>
	</label>
</fieldset>
