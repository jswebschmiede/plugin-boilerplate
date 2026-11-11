<?php

/**
 * Checkbox list settings field.
 *
 * A hidden empty value is always submitted so an unchecked list is stored
 * as an empty array instead of keeping the previous option.
 *
 * @var string               $name        Option name.
 * @var array<string,string> $choices     Value => label.
 * @var string[]             $selected    Selected values.
 * @var string               $description Field description.
 *
 * @package plugin-boilerplate
 */

declare(strict_types=1);

\defined( 'ABSPATH' ) || exit;

$name        ??= '';
$choices     ??= array();
$selected    ??= array();
$description ??= '';
?>
<fieldset>
	<input type="hidden" name="<?php echo esc_attr( $name ); ?>[]" value="" />
	<?php foreach ( $choices as $choice_value => $choice_label ) : ?>
		<label>
			<input
				type="checkbox"
				name="<?php echo esc_attr( $name ); ?>[]"
				value="<?php echo esc_attr( (string) $choice_value ); ?>"
				<?php checked( in_array( (string) $choice_value, $selected, true ) ); ?>
			/>
			<?php echo esc_html( (string) $choice_label ); ?>
		</label><br />
	<?php endforeach; ?>
	<p class="description"><?php echo esc_html( $description ); ?></p>
</fieldset>
