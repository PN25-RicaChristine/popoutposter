<?php
/**
 * A representation of a WordPress admin notice.
 */

namespace Nexcess\MAPPS;

use Nexcess\MAPPS\Exceptions\ImmutableValueException;
use Nexcess\MAPPS\Integrations\Admin;

/**
 * @property string   $id             A unique ID for this notice.
 * @property bool     $inline         Whether or not the notice should render inline.
 * @property bool     $is_dismissible Whether or not the notice should be dismissible.
 * @property string   $message        The contents of the notice.
 * @property string   $type           The notice type.
 * @property string[] $valid_types    Valid admin notice types.
 */
class AdminNotice {

	/**
	 * @var string A capability check that must pass in order to render this notice.
	 */
	protected $capability;

	/**
	 * @var string A unique ID for this notice.
	 */
	protected $id;

	/**
	 * @var bool Whether or not the notice should be treated as being inline (e.g. not moved to the
	 *           top of the page).
	 */
	protected $inline = false;

	/**
	 * @var bool Whether or not the notice should be dismissible.
	 */
	protected $is_dismissible;

	/**
	 * @var string The contents of the notice.
	 */
	protected $message;

	/**
	 * @var string The notice type.
	 */
	protected $type;

	/**
	 * @var string[] Valid admin notice types.
	 */
	protected $valid_types = [
		'error',
		'info',
		'success',
		'warning',
	];

	/**
	 * User meta key for dismissed notifications.
	 */
	const USER_META_DISMISSED_NOTICES = '_nexcess_mapps_dismissed_notices';

	/**
	 * Create a new notification instance.
	 *
	 * @throws \DomainException if the given $type is not in $valid_types.
	 *
	 * @param string $message        The contents of the notice.
	 * @param string $type           Optional. The notice type, one of "success", "error",
	 *                               "warning", or "info". Default is "info."
	 * @param bool   $is_dismissible Optional. Whether or not the notice should be marked as
	 *                               dismissible. Default is true.
	 * @param string $id             Optional. A unique ID for the notification, which is used for
	 *                               tracking dismissed notifications. Default is a hash of $message.
	 */
	public function __construct( $message, $type = 'info', $is_dismissible = true, $id = '' ) {
		if ( ! in_array( $type, $this->valid_types, true ) ) {
			throw new \DomainException( sprintf(
				/* Translators: %1$s is the passed type, %2$s is an imploded list of permitted values. */
				__( 'Type "%1$s" is not defined, and must be one of: %2$s.', 'nexcess-mapps' ),
				$type,
				implode( ', ', $this->valid_types )
			) );
		}

		$this->message        = $message;
		$this->type           = $type;
		$this->is_dismissible = $is_dismissible;
		$this->id             = ! empty( $id ) ? $id : substr( md5( $type . ':' . $message ), 0, 10 );
	}

	/**
	 * Enable protected properties to be accessed easily.
	 *
	 * @param string $prop The property to retrieve.
	 *
	 * @return string|bool|null Either the string/bool value of the property, or null if the
	 *                          property is undefined.
	 */
	public function __get( $prop ) {
		return isset( $this->{$prop} ) ? $this->{$prop} : null;
	}

	/**
	 * AdminNotices should be treated as immutable.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\ImmutableValueException
	 *
	 * @param string $prop  The property name.
	 * @param mixed  $value The value that is being assigned.
	 */
	public function __set( $prop, $value ) {
		throw new ImmutableValueException( sprintf(
			/* Translators: %1$s is the current class name. */
			__( 'The %1$s object is immutable.', 'nexcess-mapps' ),
			__CLASS__
		) );
	}

	/**
	 * Automatically render the notice if it's cast to a string.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * Set the required capability in order to render this notice.
	 *
	 * @param string $cap The capability check.
	 *
	 * @return self
	 */
	public function setCapability( $cap ) {
		$this->capability = (string) $cap;

		return $this;
	}

	/**
	 * Set the value of $inline.
	 *
	 * @param bool $inline Whether or not the notice should be rendered inline.
	 *
	 * @return self
	 */
	public function setInline( $inline ) {
		$this->inline = (bool) $inline;

		return $this;
	}

	/**
	 * Generate the markup for the notification.
	 *
	 * @return string
	 */
	public function render() {
		if ( ! empty( $this->capability ) && ! current_user_can( $this->capability ) ) {
			return '';
		}

		return sprintf(
			'<div class="notice notice-%1$s mapps-notice%2$s%3$s" data-id="%4$s" data-nonce="%5$s">%6$s</div>',
			$this->type,
			$this->is_dismissible ? ' is-dismissible' : '',
			$this->inline ? ' inline' : '',
			$this->id,
			wp_create_nonce( Admin::HOOK_DISMISSED_NOTICE ),
			wpautop( $this->message )
		);
	}

	/**
	 * Print the rendered notice to the screen.
	 */
	public function output() {
		echo wp_kses_post( $this->render() );
	}

	/**
	 * Determine whether or not a particular notice should be shown based on the user's previously-
	 * dismissed notices.
	 *
	 * @return bool True if the user has dismissed the notice before or false if the user has
	 *              either not dismissed it or the notice is not dismissible.
	 */
	public function userHasDismissedNotice() {
		if ( ! $this->is_dismissible ) {
			return false;
		}

		$dismissed = (array) get_user_meta( get_current_user_id(), self::USER_META_DISMISSED_NOTICES, true ) ?: [];

		return isset( $dismissed[ $this->id ] );
	}
}
