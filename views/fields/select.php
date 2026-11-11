<?php
/**
 * Select settings field.
 *
 * @var string               $name        Option name.
 * @var array<string,string> $choices     Value => label.
 * @var string               $selected    Selected value.
 * @var string               $description Field description.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

\defined( 'ABSPATH' ) || exit;

$name        ??= '';
$choices     ??= array();
$selected    ??= '';
$description ??= '';
?>
<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>">
	<?php foreach ( $choices as $choice_value => $choice_label ) : ?>
		<option value="<?php echo esc_attr( (string) $choice_value ); ?>" <?php selected( (string) $choice_value, $selected ); ?>>
			<?php echo esc_html( (string) $choice_label ); ?>
		</option>
	<?php endforeach; ?>
</select>
<p class="description"><?php echo esc_html( $description ); ?></p>
