<?php
/*
	Copyright (C) 2020 Papoo Software & Media GmbH

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License along
	with this program; if not, write to the Free Software Foundation, Inc.,
		51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

class Ccm19Integration {

	/** @var self $instance */
	private static $instance = null;

	/**
	 * @return self
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * @return void
	 */
	public static function staticInit()
	{
		$instance = static::getInstance();
		$instance->init();
	}

	/**
	 * @return void
	 */
	public function init()
	{
		// Insert the script on wp_head with extreme priority
		// so that it always runs before any other script.
		add_action('wp_head', [$this, 'on_wp_head'], -10);
		// Enqueue dummy script for dependency management
		wp_register_script('ccm19', false, [], false, false);
		wp_enqueue_script('ccm19');

		// Add settings form
		add_action('admin_menu', [$this, 'admin_menu']);
		add_action('admin_init', [$this, 'admin_init']);
		
		// Load translations
		load_plugin_textdomain('ccm19-integration', false, basename(__DIR__) . '/languages');
	}

	/**
	 * Hook: Initialize plugin settings
	 * @return void
	 */
	public function admin_init()
	{
		if ( is_admin() ){

			// Add settings
			add_settings_section(
				'ccm19-integration',
				__('General settings', 'ccm19-integration'),
				[$this, 'options_page_print_info'],
				'ccm19-integration'
			);

			register_setting('ccm19-integration', 'ccm19_code', [
				'type' => 'string',
				'description' => 'CCM19 Code snippet',
				'default' => ''
			]);

			add_settings_field(
				'ccm19_code', // ID
				__('CCM19 code snippet', 'ccm19-integration'),
				[$this, 'option_code_snippet_callback'],
				'ccm19-integration',
				'ccm19-integration'
			);

		}
	}

	/**
	 * Extract the ccm19.js url from the code snippet
	 *
	 * @return string|null
	 */
	private function get_integration_url()
	{
		$code = get_option('ccm19_code');
		if (!empty($code)) {
			$match = [];
			preg_match('/\bsrc=([\'"])((?>[^"\'?#]|(?!\1)["\'])*\/ccm19\.js\?(?>[^"\']|(?!\1).)*)\1/i', $code, $match);
			if ($match and $match[2]) {
				return html_entity_decode($match[2], ENT_HTML401|ENT_QUOTES, 'UTF-8');
			}
		}
		return null;
	}

	/**
	 * Hook: Inserts the script code
	 * @return void
	 */
	public function on_wp_head()
	{
		$integration_url = $this->get_integration_url();
		if ($integration_url) {
			echo '<script src="'.$integration_url.'" referrerpolicy="origin"></script>', "\n";
		}
	}

	/**
	 * Hook: Register plugin settings menu
	 * @return void
	 */
	public function admin_menu()
	{
		add_options_page(
			__('CCM19 Integration Options', 'ccm19-integration'),
			__('CCM19 Cookie Consent', 'ccm19-integration'),
			'manage_options',
			'ccm19-integration',
			[$this, 'options_page']
		);
	}

	/**
	 * Display options page
	 *
	 * @return void
	 */
	public function options_page()
	{
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		$integration_url = $this->get_integration_url();
		$admin_url = ($integration_url) ? preg_replace('%/ccm19\.js?.*$%i', '/', $integration_url) : null;
		include(__DIR__.'/options-page.php');
	}

	/**
	 * Callback: print settings section text
	 *
	 * @return void
	 */
	public function options_page_print_info()
	{
		_e('<p>Enter your code snippet from CCM19 below to integrate the cookie consent management with your website.</p>', 'ccm19-integration');
		_e('<p>If you don\'t yet have a CCM19 account or instance yet, buy or lease one on <a target="_blank" href="https://ccm19.de">ccm19.de</a>.</p>', 'ccm19-integration');
	}

	/**
	 * Callback: print input field for code snippet
	 *
	 * @return void
	 */
	public function option_code_snippet_callback()
	{
		printf(
			'<textarea id="ccm19-code" name="ccm19_code" cols="60" rows="4">%s</textarea>',
			esc_attr(get_option('ccm19_code'))
		);
	}
}
