<?php
/**
 * Print Invoice & Delivery Notes for WooCommerce.
 *
 * PDF Generator Service Class.
 *
 * @author      Tyche Softwares
 * @package     WCDN/Services
 * @category    Classes
 * @since       7.0
 */

namespace Tyche\WCDN\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Tyche\WCDN\Helpers\Templates;
use Tyche\WCDN\Helpers\Utils;
use Tyche\WCDN\Services\Template_Renderer;
use Tyche\WCDN\Services\Template_Engine;
use Tyche\WCDN\Helpers\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * PDF Generator Service Class.
 *
 * Handles PDF generation, caching, deletion
 * and URL retrieval for order documents.
 *
 * @since 7.0
 */
class Pdf {

	/**
	 * Constructor.
	 *
	 * Registers hooks.
	 *
	 * @since 7.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 100 );
		add_action( 'wcdn_delete_pdf_files', array( $this, 'delete_pdf_files' ), 100 );
	}

	/**
	 * Initialize scheduled tasks and ensure PDF directories exist.
	 *
	 * @since 7.0
	 */
	public function init() {

		$filesystem = Utils::get_filesystem();

		if ( ! $filesystem ) {
			return;
		}

		if ( ! as_has_scheduled_action( 'wcdn_delete_pdf_files' ) ) {
			as_schedule_recurring_action( time(), DAY_IN_SECONDS, 'wcdn_delete_pdf_files' );
		}

		$upload_path = wp_upload_dir()['basedir'];

		foreach ( Template_Engine::get_template_keys() as $template ) {
			$folder = trailingslashit( $upload_path ) . 'wcdn/' . $template;

			if ( ! $filesystem->exists( $folder ) ) {
				wp_mkdir_p( $folder );
			}
		}
	}

	/**
	 * Delete generated PDF files after configured retention period.
	 *
	 * @since 7.0
	 */
	public function delete_pdf_files() {

		$upload_path      = wp_upload_dir()['basedir'];
		$expire_x_days    = Settings::get( 'numberDaysPdfExpiration' );
		$expire_x_seconds = (int) $expire_x_days * DAY_IN_SECONDS;

		foreach ( Template_Engine::get_template_keys() as $template ) {

			$folder = trailingslashit( $upload_path ) . 'wcdn/' . $template;
			$files  = glob( $folder . '/*' );

			foreach ( $files as $file ) {

				if ( ! is_file( $file ) ) {
					continue;
				}

				if ( time() - filemtime( $file ) > $expire_x_seconds ) {
					wp_delete_file( $file );
				}
			}
		}
	}

	/**
	 * Generate PDF for order.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $template Template key.
	 * @param array  $data     Template data.
	 * @param bool   $force    Force regeneration.
	 * @return string|false File path or false on failure.
	 * @since 7.0
	 */
	public function generate( $order_id, $template, $data = array(), $force = true ) {

		$is_sample   = 'sample' === $order_id;
		$filesystem  = Utils::get_filesystem();
		$is_multiple = is_array( $order_id );

		if ( ! $filesystem || ! Settings::get( 'enablePDF' ) ) {
			return false;
		}

		$upload_dir = wp_upload_dir();
		$base_dir   = trailingslashit( $upload_dir['basedir'] ) . 'wcdn/' . $template . '/';

		wp_mkdir_p( $base_dir );

		// Protect directory from direct access.
		$this->protect_directory( $base_dir );

		if ( ! $is_multiple ) {
			Utils::maybe_generate_counter( $order_id );
		}

		$filename = $is_sample ? 'sample-' . $template . '-' . strtotime( 'now' ) . '.pdf' : ( $is_multiple ? 'merged-' . $template . '-' . md5( implode( '-', $order_id ) ) . '.pdf' : $this->get_filename( $order_id, $template ) );
		$file     = $base_dir . $filename;

		if ( ! $force && $filesystem->exists( $file ) ) {
			return apply_filters( 'wcdn_pdf_generated_file', $file, $order_id, $template );
		}

		if ( $is_multiple ) {

			if ( empty( $data ) || ! is_array( $data ) ) {
				return false;
			}

			$combined_html = '';

			foreach ( $data as $doc ) {

				if ( empty( $doc['data'] ) ) {
					continue;
				}

				$html = Template_Renderer::render(
					$template,
					$doc['data'],
					'pdf'
				);

				if ( ! empty( $html ) ) {
					$combined_html .= '<div style="page-break-after: always;">' . $html . '</div>';
				}
			}

			$html = $combined_html;

		} else {

			if ( empty( $data ) ) {
				return false;
			}

			$html = Template_Renderer::render(
				$template,
				$data,
				'pdf'
			);
		}

		if ( empty( $html ) ) {
			return false;
		}

		$options = new Options();
		$options->set( 'isRemoteEnabled', true );
		$options->set( 'isHtml5ParserEnabled', true );
		$options->set( 'isFontSubsettingEnabled', true );
		$options->set( 'dpi', apply_filters( 'wcdn_pdf_dpi', 150 ) );

		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $html, 'UTF-8' );

		$paper_size  = apply_filters( 'wcdn_pdf_paper_size', 'A4', $order_id );
		$orientation = apply_filters( 'wcdn_pdf_orientation', 'portrait', $order_id );

		$dompdf->setPaper( $paper_size, $orientation );
		$dompdf->render();

		$filesystem->put_contents(
			$file,
			$dompdf->output(),
			FS_CHMOD_FILE
		);

		do_action( 'wcdn_after_pdf_generated', $file, $order_id, $template );

		if ( ! $is_sample && ! $is_multiple ) {
			$order = wc_get_order( $order_id );
			$order->update_meta_data( '_wcdn_' . $template . '_pdf', $filename );
			$order->save();
		}

		return $file;
	}

	/**
	 * Generate filename.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $template Template key.
	 * @return string
	 * @since 7.0
	 */
	protected function get_filename( $order_id, $template ) {

		$order = wc_get_order( $order_id );

		$format = Templates::get(
			$template,
			'pdfFilename',
			$template . '-{order_number}.pdf'
		);

		$filename = Utils::replace_placeholders(
			$format,
			$order,
			true
		);

		return apply_filters( 'wcdn_pdf_filename', $filename, $order_id, $template );
	}

	/**
	 * Get PDF URL.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $template Template key.
	 * @return string
	 * @since 7.0
	 */
	public function get_url( $order_id, $template ) {
		$upload_dir = wp_upload_dir();
		$base_url   = trailingslashit( $upload_dir['baseurl'] ) . 'wcdn/' . $template . '/';
		$filename   = $this->get_filename( $order_id, $template );
		return esc_url( $base_url . $filename );
	}

	/**
	 * Delete cached PDF file.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $template Template key.
	 * @return void
	 * @since 7.0
	 */
	public function delete( $order_id, $template ) {

		$filesystem = Utils::get_filesystem();

		if ( ! $filesystem ) {
			return;
		}

		$upload_dir = wp_upload_dir();
		$base_dir   = trailingslashit( $upload_dir['basedir'] ) . 'wcdn/';

		$file = $base_dir . $this->get_filename( $order_id, $template );

		if ( $filesystem->exists( $file ) ) {
			$filesystem->delete( $file );
		}
	}

	/**
	 * Protect directory from direct access.
	 *
	 * Creates .htaccess and index.html if missing.
	 *
	 * @param string $dir Directory path.
	 * @return void
	 * @since 7.0
	 */
	protected function protect_directory( $dir ) {

		$filesystem = Utils::get_filesystem();

		if ( ! $filesystem ) {
			return;
		}

		// Prevent directory listing.
		$index_file = trailingslashit( $dir ) . 'index.html';

		if ( ! $filesystem->exists( $index_file ) ) {
			$filesystem->put_contents( $index_file, '', FS_CHMOD_FILE );
		}

		// Apache protection.
		$htaccess_file = trailingslashit( $dir ) . '.htaccess';

		if ( ! $filesystem->exists( $htaccess_file ) ) {

			$rules  = "Options -Indexes\n";
			$rules .= "<FilesMatch \"\\.pdf$\">\n";
			$rules .= "Require all denied\n";
			$rules .= '</FilesMatch>';

			$filesystem->put_contents( $htaccess_file, $rules, FS_CHMOD_FILE );
		}
	}
}
