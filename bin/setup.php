<?php
/**
 * Turns the boilerplate into a new plugin by replacing all placeholder tokens.
 *
 * Usage:
 *   php bin/setup.php                       Interactive.
 *   php bin/setup.php --name="My Plugin" --no-interaction [--dry-run] [--keep]
 *
 * Options (all optional except --name without interaction):
 *   --name, --slug, --namespace, --package, --repo, --prefix,
 *   --description, --author, --author-uri, --author-email
 *
 * @package plugin-boilerplate
 */

declare( strict_types=1 );

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "This script can only be run from the command line.\n" );
	exit( 1 );
}

$exit_code = ( new Plugin_Setup( dirname( __DIR__ ), $argv ) )->run();
exit( $exit_code );

/**
 * Replaces boilerplate tokens in file contents and file names.
 */
final class Plugin_Setup {
	/**
	 * Directories that are never touched.
	 */
	private const array SKIPPED_DIRECTORIES = array( '.git', 'vendor', 'node_modules', 'build', 'dist' );

	/**
	 * Files that are never touched (relative to the root).
	 */
	private const array SKIPPED_FILES = array( 'bin/setup.php', 'composer.lock', 'pnpm-lock.yaml' );

	/**
	 * Placeholder values in the boilerplate.
	 */
	private const string NAME        = 'Plugin Boilerplate';
	private const string SLUG        = 'plugin-boilerplate';
	private const string ROOT_NAMESPACE = 'Jswebforge\\PluginBoilerplate';
	private const string PACKAGE     = 'jswebforge/plugin-boilerplate';
	private const string REPO        = 'jswebschmiede/plugin-boilerplate';
	private const string PREFIX      = 'pb';
	private const string DESCRIPTION = 'Starting point for WordPress plugins';
	private const string AUTHOR      = 'jswebforge';
	private const string AUTHOR_URI  = 'https://jswebforge.de';

	/**
	 * Parsed --key=value options.
	 *
	 * @var array<string, string|true>
	 */
	private array $options;

	/**
	 * @param string   $root Absolute path to the plugin root.
	 * @param string[] $argv Command line arguments.
	 */
	public function __construct( private readonly string $root, array $argv ) {
		$this->options = $this->parse_options( array_slice( $argv, 1 ) );
	}

	/**
	 * Runs the setup.
	 *
	 * @return int Process exit code.
	 */
	public function run(): int {
		if ( isset( $this->options['help'] ) ) {
			fwrite( STDOUT, $this->get_help() );
			return 0;
		}

		if ( ! is_file( $this->root . '/' . self::SLUG . '.php' ) ) {
			fwrite( STDERR, "The boilerplate main file was not found. Setup has probably run already.\n" );
			return 1;
		}

		try {
			$config = $this->collect_config();
		} catch ( InvalidArgumentException $exception ) {
			fwrite( STDERR, 'Error: ' . $exception->getMessage() . "\n" );
			return 1;
		}

		$dry_run = isset( $this->options['dry-run'] );

		$this->print_summary( $config, $dry_run );

		if ( ! $dry_run && $this->is_interactive() && ! $this->confirm( 'Apply these changes?' ) ) {
			fwrite( STDOUT, "Aborted.\n" );
			return 1;
		}

		$changed = $this->replace_in_files( $config, $dry_run );
		$renamed = $this->rename_files( $config, $dry_run );

		if ( ! $dry_run ) {
			$this->update_composer_json( $config );
		}

		fwrite( STDOUT, sprintf( "\n%s %d file(s), renamed %d file(s).\n", $dry_run ? 'Would change' : 'Changed', $changed, $renamed ) );

		if ( $dry_run ) {
			return 0;
		}

		if ( ! isset( $this->options['keep'] ) ) {
			unlink( __FILE__ );
			fwrite( STDOUT, "Removed bin/setup.php.\n" );
		}

		fwrite(
			STDOUT,
			"\nNext steps:\n"
			. "  composer update --lock   # refresh the lock file hash after the composer.json changes\n"
			. "  composer install\n"
			. "  pnpm install && pnpm run build\n"
			. "  composer php:lint\n"
		);

		return 0;
	}

	/**
	 * Collects and validates the new plugin identity.
	 *
	 * @return array<string, string> Config values.
	 * @throws InvalidArgumentException When a value is invalid.
	 */
	private function collect_config(): array {
		$name = $this->ask( 'name', 'Plugin name', null );
		if ( '' === $name || preg_match( '/[\r\n]/', $name ) ) {
			throw new InvalidArgumentException( 'The plugin name must not be empty.' );
		}

		$slug = $this->ask( 'slug', 'Slug', $this->slugify( $name ) );
		if ( 1 !== preg_match( '/^[a-z][a-z0-9]*(-[a-z0-9]+)*$/', $slug ) ) {
			throw new InvalidArgumentException( 'The slug may only contain lowercase letters, digits and single hyphens.' );
		}

		$vendor    = explode( '\\', self::ROOT_NAMESPACE )[0];
		$namespace = $this->ask( 'namespace', 'PHP namespace (Vendor\\Plugin)', $vendor . '\\' . $this->pascal_case( $slug ) );
		if ( 1 !== preg_match( '/^[A-Z][A-Za-z0-9]*\\\\[A-Z][A-Za-z0-9]*$/', $namespace ) ) {
			throw new InvalidArgumentException( 'The namespace must have the form Vendor\\Plugin in PascalCase.' );
		}

		$package = $this->ask( 'package', 'Composer package', explode( '/', self::PACKAGE )[0] . '/' . $slug );
		if ( 1 !== preg_match( '#^[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9]([_.-]?[a-z0-9]+)*$#', $package ) ) {
			throw new InvalidArgumentException( 'The Composer package must have the form vendor/name in lowercase.' );
		}

		$repo = $this->ask( 'repo', 'GitHub repository (owner/name)', explode( '/', self::REPO )[0] . '/' . $slug );
		if ( 1 !== preg_match( '#^[A-Za-z0-9-]+/[A-Za-z0-9._-]+$#', $repo ) ) {
			throw new InvalidArgumentException( 'The GitHub repository must have the form owner/name.' );
		}

		$prefix = $this->ask( 'prefix', 'Short prefix for post types and Tailwind classes (2-6 letters)', $this->initials( $slug ) );
		if ( 1 !== preg_match( '/^[a-z]{2,6}$/', $prefix ) ) {
			throw new InvalidArgumentException( 'The prefix must consist of 2 to 6 lowercase letters.' );
		}

		$description = $this->ask( 'description', 'Description', $name );

		$author = $this->ask( 'author', 'Author', self::AUTHOR );
		if ( '' === $author ) {
			throw new InvalidArgumentException( 'The author must not be empty.' );
		}

		$author_uri = $this->ask( 'author-uri', 'Author URI', self::AUTHOR_URI );
		if ( false === filter_var( $author_uri, FILTER_VALIDATE_URL ) ) {
			throw new InvalidArgumentException( 'The author URI must be a valid URL.' );
		}

		$author_email = $this->ask( 'author-email', 'Author e-mail for composer.json (optional)', '' );
		if ( '' !== $author_email && false === filter_var( $author_email, FILTER_VALIDATE_EMAIL ) ) {
			throw new InvalidArgumentException( 'The author e-mail is invalid.' );
		}

		return array(
			'name'         => $name,
			'slug'         => $slug,
			'namespace'    => $namespace,
			'package'      => $package,
			'repo'         => $repo,
			'prefix'       => $prefix,
			'description'  => $description,
			'author'       => $author,
			'author_uri'   => $author_uri,
			'author_email' => $author_email,
		);
	}

	/**
	 * Builds the token map. Longer tokens are matched first.
	 *
	 * @param array<string, string> $config Config values.
	 * @return array<string, string> Token => replacement.
	 */
	private function get_replacements( array $config ): array {
		$snake = str_replace( '-', '_', $config['slug'] );

		$replacements = array(
			// JSON-escaped namespace in composer.json.
			str_replace( '\\', '\\\\', self::ROOT_NAMESPACE ) => str_replace( '\\', '\\\\', $config['namespace'] ),
			self::ROOT_NAMESPACE                              => $config['namespace'],
			self::PACKAGE                                => $config['package'],
			self::REPO                                   => $config['repo'],
			self::DESCRIPTION                            => $config['description'],
			self::NAME                                   => $config['name'],
			self::SLUG                                   => $config['slug'],
			str_replace( '-', '_', self::SLUG )          => $snake,
			strtoupper( str_replace( '-', '_', self::SLUG ) ) => strtoupper( $snake ),
			// Author fields, e.g. in the .pot header. Namespace and package tokens are longer and match first.
			self::AUTHOR_URI                             => $config['author_uri'],
			self::AUTHOR                                 => $config['author'],
		);

		uksort( $replacements, static fn( string $a, string $b ): int => strlen( $b ) <=> strlen( $a ) );

		return $replacements;
	}

	/**
	 * Replaces tokens in all text files in a single pass, so replacements are never re-matched.
	 *
	 * @param array<string, string> $config  Config values.
	 * @param bool                  $dry_run Only report changes.
	 * @return int Number of changed files.
	 */
	private function replace_in_files( array $config, bool $dry_run ): int {
		$replacements = $this->get_replacements( $config );

		$alternatives = array_map( static fn( string $token ): string => preg_quote( $token, '/' ), array_keys( $replacements ) );
		// The short prefix only counts in front of ":" (Tailwind), "_" (post type, taxonomy) or ")" (prefix(pb)).
		$alternatives[] = '\b' . self::PREFIX . '(?=[:_)])';
		$pattern        = '/' . implode( '|', $alternatives ) . '/';

		$changed = 0;

		foreach ( $this->get_files() as $relative ) {
			$path     = $this->root . '/' . $relative;
			$contents = file_get_contents( $path );

			if ( false === $contents || str_contains( $contents, "\0" ) ) {
				continue;
			}

			$source = $contents;

			// The README section about creating a plugin from the template is obsolete afterwards.
			if ( 'README.md' === $relative ) {
				$source = (string) preg_replace( '/<!-- setup:start -->.*?<!-- setup:end -->\n*/s', '', $source );
			}

			$count   = 0;
			$updated = preg_replace_callback(
				$pattern,
				static fn( array $match ): string => $replacements[ $match[0] ] ?? $config['prefix'],
				$source,
				-1,
				$count
			);

			if ( null === $updated ) {
				throw new RuntimeException( 'Replacing failed in ' . $relative );
			}

			if ( 'plugin-boilerplate.php' === $relative ) {
				$updated = $this->update_plugin_header( $updated, $config );
			}

			if ( $updated === $contents ) {
				continue;
			}

			++$changed;
			fwrite( STDOUT, sprintf( "  %s %s (%d)\n", $dry_run ? 'would update' : 'updated', $relative, $count ) );

			if ( ! $dry_run ) {
				file_put_contents( $path, $updated );
			}
		}

		return $changed;
	}

	/**
	 * Sets author fields in the plugin header.
	 *
	 * @param string                $contents Main file contents.
	 * @param array<string, string> $config   Config values.
	 * @return string Updated contents.
	 */
	private function update_plugin_header( string $contents, array $config ): string {
		$fields = array(
			'Description' => $config['description'],
			'Author'     => $config['author'],
			'Author URI' => $config['author_uri'],
			'Version'    => '0.1.0',
		);

		foreach ( $fields as $field => $value ) {
			$contents = (string) preg_replace(
				'/^(\s*\*\s*' . preg_quote( $field, '/' ) . ':\s*).*$/m',
				'${1}' . addcslashes( $value, '\\$' ),
				$contents,
				1
			);
		}

		return $contents;
	}

	/**
	 * Renames files whose name contains the slug.
	 *
	 * @param array<string, string> $config  Config values.
	 * @param bool                  $dry_run Only report changes.
	 * @return int Number of renamed files.
	 */
	private function rename_files( array $config, bool $dry_run ): int {
		$renamed = 0;

		foreach ( $this->get_files() as $relative ) {
			$basename = basename( $relative );

			if ( ! str_contains( $basename, self::SLUG ) ) {
				continue;
			}

			$target = dirname( $relative ) . '/' . str_replace( self::SLUG, $config['slug'], $basename );
			$target = ltrim( preg_replace( '#^\./#', '', $target ) ?? $target, '/' );

			++$renamed;
			fwrite( STDOUT, sprintf( "  %s %s -> %s\n", $dry_run ? 'would rename' : 'renamed', $relative, $target ) );

			if ( ! $dry_run ) {
				rename( $this->root . '/' . $relative, $this->root . '/' . $target );
			}
		}

		return $renamed;
	}

	/**
	 * Sets the authors in composer.json and drops the setup script entry.
	 *
	 * @param array<string, string> $config Config values.
	 * @return void
	 */
	private function update_composer_json( array $config ): void {
		$path = $this->root . '/composer.json';
		$data = json_decode( (string) file_get_contents( $path ), true, 512, JSON_THROW_ON_ERROR );

		$author = array( 'name' => $config['author'] );
		if ( '' !== $config['author_email'] ) {
			$author['email'] = $config['author_email'];
		}
		$author['homepage'] = $config['author_uri'];

		$data['authors'] = array( $author );

		if ( ! isset( $this->options['keep'] ) ) {
			unset( $data['scripts']['setup'] );
		}

		$json = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR );

		// Composer files in this project are indented with tabs.
		$json = (string) preg_replace_callback(
			'/^( {4})+/m',
			static fn( array $match ): string => str_repeat( "\t", intdiv( strlen( $match[0] ), 4 ) ),
			$json
		);

		file_put_contents( $path, $json . "\n" );
	}

	/**
	 * Lists all files to process, relative to the root.
	 *
	 * @return string[]
	 */
	private function get_files(): array {
		$directory = new RecursiveDirectoryIterator( $this->root, FilesystemIterator::SKIP_DOTS );
		$filter    = new RecursiveCallbackFilterIterator(
			$directory,
			fn( SplFileInfo $file ): bool => ! ( $file->isDir() && in_array( $file->getFilename(), self::SKIPPED_DIRECTORIES, true ) )
		);

		$files = array();

		foreach ( new RecursiveIteratorIterator( $filter ) as $file ) {
			if ( ! $file instanceof SplFileInfo || ! $file->isFile() ) {
				continue;
			}

			$relative = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $this->root ) + 1 ) );

			if ( ! in_array( $relative, self::SKIPPED_FILES, true ) ) {
				$files[] = $relative;
			}
		}

		sort( $files );

		return $files;
	}

	/**
	 * Prints the derived configuration.
	 *
	 * @param array<string, string> $config  Config values.
	 * @param bool                  $dry_run Whether this is a dry run.
	 * @return void
	 */
	private function print_summary( array $config, bool $dry_run ): void {
		fwrite( STDOUT, "\n" . ( $dry_run ? 'Dry run' : 'Setup' ) . " with:\n" );

		foreach ( $config as $key => $value ) {
			fwrite( STDOUT, sprintf( "  %-13s %s\n", $key, '' === $value ? '(none)' : $value ) );
		}

		fwrite( STDOUT, "\n" );
	}

	/**
	 * Reads a value from the options or, when interactive, from STDIN.
	 *
	 * @param string      $key           Option key.
	 * @param string      $label         Prompt label.
	 * @param string|null $default_value Default value, null when required.
	 * @return string Trimmed value.
	 * @throws InvalidArgumentException When a required value is missing.
	 */
	private function ask( string $key, string $label, ?string $default_value ): string {
		if ( isset( $this->options[ $key ] ) && is_string( $this->options[ $key ] ) ) {
			return trim( $this->options[ $key ] );
		}

		if ( ! $this->is_interactive() ) {
			if ( null === $default_value ) {
				throw new InvalidArgumentException( sprintf( 'Missing --%s.', $key ) );
			}

			return $default_value;
		}

		$prompt = null === $default_value || '' === $default_value ? $label . ': ' : sprintf( '%s [%s]: ', $label, $default_value );
		fwrite( STDOUT, $prompt );

		$line  = fgets( STDIN );
		$value = false === $line ? '' : trim( $line );

		return '' === $value ? (string) $default_value : $value;
	}

	/**
	 * Asks a yes/no question.
	 *
	 * @param string $question Question.
	 * @return bool
	 */
	private function confirm( string $question ): bool {
		fwrite( STDOUT, $question . ' [y/N]: ' );
		$line = fgets( STDIN );

		return false !== $line && in_array( strtolower( trim( $line ) ), array( 'y', 'yes', 'j', 'ja' ), true );
	}

	/**
	 * Whether prompts may be shown.
	 *
	 * @return bool
	 */
	private function is_interactive(): bool {
		return ! isset( $this->options['no-interaction'] ) && ! isset( $this->options['n'] ) && stream_isatty( STDIN );
	}

	/**
	 * Parses --key=value and --flag arguments.
	 *
	 * @param string[] $args Arguments without the script name.
	 * @return array<string, string|true>
	 */
	private function parse_options( array $args ): array {
		$options = array();

		foreach ( $args as $arg ) {
			if ( 1 !== preg_match( '/^--?([a-z-]+)(?:=(.*))?$/s', $arg, $matches ) ) {
				continue;
			}

			$options[ $matches[1] ] = $matches[2] ?? true;
		}

		return $options;
	}

	/**
	 * Converts a plugin name to a slug.
	 *
	 * @param string $name Plugin name.
	 * @return string Slug.
	 */
	private function slugify( string $name ): string {
		$ascii = strtr( $name, array( 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ß' => 'ss' ) );

		return trim( (string) preg_replace( '/[^a-z0-9]+/', '-', strtolower( $ascii ) ), '-' );
	}

	/**
	 * Converts a slug to PascalCase.
	 *
	 * @param string $slug Slug.
	 * @return string PascalCase name.
	 */
	private function pascal_case( string $slug ): string {
		$name = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $slug ) ) );

		// PHP identifiers must not start with a digit.
		return preg_match( '/^[0-9]/', $name ) ? 'Plugin' . $name : $name;
	}

	/**
	 * Builds a short prefix from the slug's initials.
	 *
	 * @param string $slug Slug.
	 * @return string Prefix with 2 to 6 letters.
	 */
	private function initials( string $slug ): string {
		$letters = '';

		foreach ( explode( '-', $slug ) as $part ) {
			if ( 1 === preg_match( '/[a-z]/', $part, $match ) ) {
				$letters .= $match[0];
			}
		}

		if ( strlen( $letters ) < 2 ) {
			$letters = substr( (string) preg_replace( '/[^a-z]/', '', $slug ), 0, 2 );
		}

		return substr( str_pad( $letters, 2, 'x' ), 0, 6 );
	}

	/**
	 * Returns the usage text.
	 *
	 * @return string
	 */
	private function get_help(): string {
		return <<<'TXT'
		Usage: php bin/setup.php [options]

		  --name=...           Plugin name (required without interaction)
		  --slug=...           Plugin slug, text domain and directory name
		  --namespace=...      PHP namespace, e.g. Acme\MyPlugin
		  --package=...        Composer package, e.g. acme/my-plugin
		  --repo=...           GitHub repository for updates, e.g. acme/my-plugin
		  --prefix=...         2-6 letters for post types and Tailwind classes
		  --description=...    Plugin description
		  --author=...         Author name
		  --author-uri=...     Author URL
		  --author-email=...   Author e-mail for composer.json
		  --dry-run            Only list the changes
		  --keep               Keep bin/setup.php afterwards
		  --no-interaction     Never prompt; use defaults

		TXT;
	}
}
