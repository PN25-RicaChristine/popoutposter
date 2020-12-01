<?php

namespace Nexcess\MAPPS\Concerns;

use Nexcess\MAPPS\Exceptions\MissingTemplateException;

trait HasAdminPages {

	/**
	 * Callback to render the page.
	 *
	 * By default, this will use the integration name (lowercased) and pass the Settings object.
	 */
	public function renderMenuPage() {
		$this->renderTemplate( strtolower( ( new \ReflectionClass( $this ) )->getShortName() ), [
			'settings' => $this->settings,
		] );
	}

	/**
	 * Get the contents of a template file, using the provided $data array.
	 *
	 * @param string  $template The template name, which should be relative to the
	 *                          nexcess-mapps/templates/ directory without a file extension.
	 * @param mixed[] $data     Optional. Data to pass to the template. Default is empty.
	 *
	 * @return string The output of the template.
	 */
	protected function getTemplateContent( $template, $data = [] ) {
		try {
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			extract( $data );

			// Get the filepath *after* extract() but before opening the output buffer.
			$filepath = $this->locateTemplateFile( $template );

			ob_start();
			include $filepath;
			$content = ob_get_clean();
		} catch ( MissingTemplateException $e ) {
			$content = defined( 'WP_DEBUG' ) && WP_DEBUG
				? sprintf( '<!-- %s -->', $e->getMessage() )
				: '';
		}

		return (string) $content;
	}

	/**
	 * Render a template from within the templates directory.
	 *
	 * @param string  $template The template name, which should be relative to the
	 *                          nexcess-mapps/templates/ directory without a file extension.
	 * @param mixed[] $data     Optional. Data to pass to the template. Default is empty.
	 */
	protected function renderTemplate( $template, $data = [] ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->getTemplateContent( $template, $data );
	}

	/**
	 * Retrieve the path to a template file.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\MissingTemplateException if the given template cannot
	 *                                                            be found.
	 *
	 * @param string  $template The template name, which should be relative to the
	 *                          nexcess-mapps/templates/ directory without a file extension.
	 *
	 * @return string Either the system path to the template or an empty string if the template
	 *                could not be found.
	 */
	protected function locateTemplateFile( $template ) {
		$file = sprintf( '%1$s/templates/%2$s.php', dirname( __DIR__ ), $template );

		/**
		 * Override the individual template location.
		 *
		 * @param string $file     The existing file path for the template file.
		 * @param string $template The template name that was used in the original call.
		 */
		$file = apply_filters( 'nexcess_mapps_branding_template_file', $file, $template );

		if ( ! file_exists( $file ) ) {
			throw new MissingTemplateException( sprintf(
				'The MAPPS template file at %1$s was not found.',
				$file
			) );
		}

		return $file;
	}
}
