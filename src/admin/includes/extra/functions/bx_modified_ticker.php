<?php
/**
 * BX Modified Ticker - Helper Functions
 * 
 * Central helper function library for Ticker module operations.
 * Provides reusable utilities for database operations, email notifications,
 * form field generation, and alert banner display.
 * 
 * @package    BX Modified Ticker
 * @subpackage Core Functions
 * @version    1.0.0
 * @author     benax
 * @copyright  2006-2026 benax
 * @license    GNU GPL v2.0
 * 
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

/**
 * Konfigurationseingabefeld für die Modulversion (read-only)
 */
if (!function_exists('bx_configuration_field_version')) {
  function bx_configuration_field_version(string $value, string $constant): string {
    return xtc_draw_input_field( 'configuration['.$constant.']', $value, 'readonly="true" style="opacity: 0.4;"');
  }
}
