<?php
/**
 * View Admin As - Class Update
 *
 * Update class used for version control and updates
 *
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package view-admin-as
 * @version 1.5.x
 */

! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

final class VAA_View_Admin_As_Update extends VAA_View_Admin_As_Class_Base
{
	/**
	 * The single instance of the class.
	 *
	 * @since   1.5.x
	 * @var     VAA_View_Admin_As_Update
	 */
	private static $_instance = null;

	/**
	 * Update settings
	 *
	 * @since   1.4
	 * @since   1.5.x  Moved to this class from main class
	 * @access  private
	 * @return  void
	 */
	private function db_update() {
		$defaults = array(
			'db_version' => VIEW_ADMIN_AS_DB_VERSION,
		);

		$current_db_version = strtolower( $this->store->get_optionData('db_version') );

		// Clear the user views for update to 1.5+
		if ( version_compare( $current_db_version, '1.5', '<' ) ) {
			// Reset user meta for all users
			global $wpdb;
			$all_users = $wpdb->get_results("SELECT ID FROM $wpdb->users");
			foreach ( $all_users as $user ) {
				$this->store->delete_user_meta( $user->ID, false, true ); // true for reset_only
			}
			// Reset currently loaded data
			$this->store->set_userMeta(false);
		}

		// Update version, append if needed
		$this->store->set_optionData( VIEW_ADMIN_AS_DB_VERSION, 'db_version', true );
		// Update option data
		$this->store->update_optionData( wp_parse_args( $this->store->get_optionData(), $defaults ) );

		// Main update finished, hook used to update modules
		do_action( 'vaa_view_admin_as_db_update', VIEW_ADMIN_AS_DB_VERSION );
	}

	/**
	 * Check the correct DB version in the DB
	 *
	 * @since   1.4
	 * @since   1.5.x  Moved to this class from main class
	 * @access  public
	 * @return  void
	 */
	public function maybe_db_update() {
		$db_version = strtolower( $this->store->get_optionData('db_version') );
		if ( version_compare( $db_version, $this->store->get_dbVersion(), '<' ) ) {
			$this->db_update();
		}
	}

	/**
	 * Main Instance.
	 *
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   1.5.x
	 * @access  public
	 * @static
	 * @param   object|bool  $caller  The referrer class
	 * @return  VAA_View_Admin_As_Update|bool
	 */
	public static function get_instance( $caller = false ) {
		if ( is_object( $caller ) && 'VAA_View_Admin_As' == get_class( $caller ) ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		return false;
	}
}