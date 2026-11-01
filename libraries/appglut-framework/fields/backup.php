<?php
/**
 * Field: backup — export / import the whole options panel as JSON.
 * Only meaningful inside an options panel (uses its AJAX endpoints).
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Field_backup extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	public function render() {
		if ( 'options' !== $this->where ) {
			echo '<div class="agf-muted">' . esc_html__( 'The backup field is available in options panels only.', 'shopglut' ) . '</div>';
			return;
		}

		echo '<div class="agf-backup">';

		echo '<div class="agf-backup-card">';
		echo '<div class="agf-backup-icon">' . AppGlut::icon( 'dashicons-database-export' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div class="agf-backup-text"><strong>' . esc_html__( 'Export settings', 'shopglut' ) . '</strong><p>' . esc_html__( 'Download every saved option as a JSON file, or copy it to the clipboard.', 'shopglut' ) . '</p></div>';
		echo '<div class="agf-btn-row"><button type="button" class="agf-btn agf-btn-secondary agf-backup-download">' . AppGlut::icon( 'dashicons-download' ) . '<span>' . esc_html__( 'Download JSON', 'shopglut' ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<button type="button" class="agf-btn agf-btn-ghost agf-backup-copy">' . AppGlut::icon( 'dashicons-clipboard' ) . '<span>' . esc_html__( 'Copy', 'shopglut' ) . '</span></button></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';

		echo '<div class="agf-backup-card agf-backup-import">';
		echo '<div class="agf-backup-icon">' . AppGlut::icon( 'dashicons-database-import' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div class="agf-backup-text"><strong>' . esc_html__( 'Import settings', 'shopglut' ) . '</strong><p>' . esc_html__( 'Paste exported JSON or choose a file. Imported values are validated like a normal save.', 'shopglut' ) . '</p></div>';
		echo '<textarea class="agf-input agf-backup-data" rows="5" placeholder="{ ... }" spellcheck="false"></textarea>';
		echo '<div class="agf-btn-row"><label class="agf-btn agf-btn-ghost agf-backup-file">' . AppGlut::icon( 'dashicons-media-code' ) . '<span>' . esc_html__( 'Choose file', 'shopglut' ) . '</span><input type="file" accept="application/json,.json" hidden></label>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<button type="button" class="agf-btn agf-btn-primary agf-backup-run">' . AppGlut::icon( 'dashicons-upload' ) . '<span>' . esc_html__( 'Import', 'shopglut' ) . '</span></button></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';

		echo '</div>';
	}
}
