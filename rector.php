<?php
/**
 * Rector rules to automatically refactor code to modern syntax.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;

return RectorConfig::configure()
	->withPaths(
		array(
			__DIR__,
		)
	)
	->withSkip(
		array(
			__DIR__ . '/vendor',
			__DIR__ . '/node_modules',
			__DIR__ . '/build',
			__DIR__ . '/dist',
			__DIR__ . '/tests',
			__DIR__ . '/languages',
			// WPCS requires long array syntax.
			LongArrayToShortArrayRector::class,
			ChangeSwitchToMatchRector::class,
			// Hook callbacks stay array( $this, 'method' ) so they can be removed again.
			ArrayToFirstClassCallableRector::class,
		)
	)
	->withPhpSets( php83: true )
	->withPreparedSets(
		deadCode: false,
		codeQuality: false,
		codingStyle: false,
		typeDeclarations: false,
		privatization: false,
		naming: false,
		instanceOf: false,
		earlyReturn: false,
	);
