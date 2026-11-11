<?php
/**
 * Builds a distributable plugin zip in dist/.
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "This script can only be run from the command line.\n" );
	exit( 1 );
}

if ( ! class_exists( ZipArchive::class ) ) {
	fwrite( STDERR, "The PHP zip extension is required to package the plugin.\n" );
	exit( 1 );
}

$exit_code = ( new Plugin_Package( dirname( __DIR__ ) ) )->run();
exit( $exit_code );

/**
 * Packages the plugin folder into a versioned zip archive.
 */
final readonly class Plugin_Package {
	/**
	 * Directory name inside the zip (WordPress plugin slug).
	 */
	private const string SLUG = 'plugin-boilerplate';

	/**
	 * Files at the plugin root that stay out of the zip.
	 */
	private const array EXACT_FILES = array(
		'.editorconfig',
		'.gitattributes',
		'.gitignore',
		'.prettierrc',
		'.stylelintrc.json',
		'CLAUDE.md',
		'blueprint.json',
		'package.json',
		'phpcs.xml',
		'pnpm-lock.yaml',
		'postcss.config.js',
		'rector.php',
		'release.md',
		'webpack.config.js',
	);

	/**
	 * Directory prefixes that stay out of the zip.
	 */
	private const array EXCLUDED_PREFIXES = array(
		'.git/',
		'.idea/',
		'.vscode/',
		'assets/',
		'bin/',
		'dist/',
		'docs/',
		'node_modules/',
		'tests/',
		'vendor/bin/',
	);

	/**
	 * @param string $root Absolute path to the plugin root.
	 */
	public function __construct(
        /**
         * Absolute path to the plugin root.
         */
        private string $root
    )
    {
    }

	/**
	 * Creates dist/<slug>-<version>.zip and prints its path.
	 *
	 * @return int Process exit code.
	 */
	public function run(): int {
		$autoload = $this->root . '/vendor/autoload.php';
		if ( ! is_file( $autoload ) ) {
			fwrite( STDERR, "vendor/autoload.php is missing. Run composer install --no-dev first.\n" );
			return 1;
		}

		if ( ! is_file( $this->root . '/build/frontend.asset.php' ) || ! is_file( $this->root . '/build/admin.asset.php' ) ) {
			fwrite( STDERR, "build/ is incomplete. Run pnpm run build first.\n" );
			return 1;
		}

		$version  = $this->read_version();
		$dist_dir = $this->root . '/dist';
		$zip_path = $dist_dir . '/' . self::SLUG . '-' . $version . '.zip';

		if ( ! is_dir( $dist_dir ) && ! mkdir( $dist_dir, 0755, true ) && ! is_dir( $dist_dir ) ) {
			fwrite( STDERR, "Unable to create the dist directory.\n" );
			return 1;
		}

		if ( is_file( $zip_path ) && ! unlink( $zip_path ) ) {
			fwrite( STDERR, "Unable to replace the existing zip file.\n" );
			return 1;
		}

		$zip = new ZipArchive();
		if ( true !== $zip->open( $zip_path, ZipArchive::CREATE ) ) {
			fwrite( STDERR, "Unable to create the zip archive.\n" );
			return 1;
		}

		$file_count = $this->add_files( $zip );
		$zip->close();

		if ( 0 === $file_count ) {
			fwrite( STDERR, "The zip archive would be empty.\n" );
			return 1;
		}

		fwrite( STDOUT, $zip_path . "\n" );
		return 0;
	}

	/**
	 * Reads the plugin version from the main file header.
	 *
	 * @return string Plugin version.
	 */
	private function read_version(): string {
		$plugin_file = $this->root . '/' . self::SLUG . '.php';
		$contents    = file_get_contents( $plugin_file );

		if ( false === $contents ) {
			throw new RuntimeException( 'Unable to read the main plugin file.' );
		}

		if ( 1 !== preg_match( '/^\s*\*\s*Version:\s*(.+)$/m', $contents, $matches ) ) {
			throw new RuntimeException( 'Plugin version header is missing.' );
		}

		return trim( $matches[1] );
	}

	/**
	 * Adds included plugin files to the archive.
	 *
	 * @param ZipArchive $zip Open zip archive.
	 * @return int Number of files added.
	 */
	private function add_files( ZipArchive $zip ): int {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$this->root,
				FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
			),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		$count = 0;

		foreach ( $iterator as $file ) {
			if ( ! $file instanceof SplFileInfo || ! $file->isFile() ) {
				continue;
			}

			$relative = $this->relative_path( $file->getPathname() );
			if ( $this->should_exclude( $relative ) ) {
				continue;
			}

			$zip->addFile( $file->getPathname(), self::SLUG . '/' . $relative );
			++$count;
		}

		return $count;
	}

	/**
	 * Converts an absolute path to a root-relative path with forward slashes.
	 *
	 * @param string $absolute_path Absolute filesystem path.
	 * @return string Relative path.
	 */
	private function relative_path( string $absolute_path ): string {
		$relative = substr( $absolute_path, strlen( $this->root ) + 1 );

		return str_replace( '\\', '/', (string) $relative );
	}

	/**
	 * Returns whether a relative path belongs in the zip.
	 *
	 * @param string $relative_path Path relative to the plugin root.
	 * @return bool True when the file should be omitted.
	 */
	private function should_exclude( string $relative_path ): bool {
		if ( in_array( $relative_path, self::EXACT_FILES, true ) ) {
			return true;
		}

		foreach ( self::EXCLUDED_PREFIXES as $prefix ) {
			if ( str_starts_with( $relative_path, $prefix ) ) {
				return true;
			}
		}

		$basename = basename( $relative_path );

		if ( in_array( $basename, array( '.DS_Store', 'Thumbs.db', '.env', '.env.local', '.phpunit.result.cache' ), true ) ) {
			return true;
		}

		return str_ends_with( $relative_path, '.zip' ) || str_ends_with( $basename, '~' );
	}
}
