<?php
/**
 * Output of the [plugin_boilerplate] shortcode.
 *
 * Utility classes use the Tailwind prefix "pb:" (see assets/css/frontend.css),
 * so they never collide with theme classes.
 *
 * @var string                                                                  $greeting Greeting text, may be empty.
 * @var string                                                                  $intro    Plain text with line breaks, may be empty.
 * @var string                                                                  $layout   "list" or "grid".
 * @var array<int, array{title: string, url: string, date: string, featured: bool}> $items    Entries.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

\defined( 'ABSPATH' ) || exit;

$greeting ??= '';
$intro    ??= '';
$layout   ??= 'list';
$items    ??= array();

$list_classes = 'grid' === $layout
	? 'pb:grid pb:gap-4 pb:sm:grid-cols-2 pb:lg:grid-cols-3'
	: 'pb:flex pb:flex-col pb:gap-3';
?>
<section class="plugin-boilerplate pb:my-6">
	<?php if ( '' !== $greeting ) : ?>
		<p class="pb:mb-4 pb:text-lg pb:font-semibold"><?php echo esc_html( $greeting ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== $intro ) : ?>
		<div class="pb:mb-4"><?php echo wp_kses_post( wpautop( esc_html( $intro ) ) ); ?></div>
	<?php endif; ?>

	<?php if ( array() === $items ) : ?>
		<p class="pb:text-sm pb:opacity-70"><?php esc_html_e( 'Nothing to show yet.', 'plugin-boilerplate' ); ?></p>
	<?php else : ?>
		<ul class="<?php echo esc_attr( $list_classes ); ?> pb:m-0 pb:list-none pb:p-0">
			<?php foreach ( $items as $item ) : ?>
				<li class="pb:rounded-lg pb:border pb:border-current/15 pb:p-4">
					<a class="pb:font-medium pb:underline-offset-4 pb:hover:underline" href="<?php echo esc_url( $item['url'] ); ?>">
						<?php echo esc_html( $item['title'] ); ?>
					</a>
					<?php if ( $item['featured'] ) : ?>
						<span class="pb:ml-2 pb:rounded-full pb:bg-amber-100 pb:px-2 pb:py-0.5 pb:text-xs pb:text-amber-900">
							<?php esc_html_e( 'Featured', 'plugin-boilerplate' ); ?>
						</span>
					<?php endif; ?>
					<?php if ( '' !== $item['date'] ) : ?>
						<p class="pb:mt-1 pb:mb-0 pb:text-xs pb:opacity-70"><?php echo esc_html( $item['date'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
