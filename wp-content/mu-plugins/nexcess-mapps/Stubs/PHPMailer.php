<?php
/**
 * Extend the PHPMailer class used by WordPress to prevent mail from being sent.
 *
 * This is loosely based on the MockPHPMailer class included in the WordPress core test suite.
 */

namespace Nexcess\MAPPS\Stubs;

use PHPMailer as BaseMailer;

class PHPMailer extends BaseMailer {

	/**
	 * Messages that have been sent.
	 *
	 * @var array[]
	 */
	public $messages = [];

	/**
	 * Prevent messages from actually being sent.
	 */
	public function postSend() {
		$this->messages[] = [
			'to'      => $this->to,
			'cc'      => $this->cc,
			'bcc'     => $this->bcc,
			'header'  => $this->MIMEHeader . $this->mailHeader,
			'subject' => $this->Subject,
			'body'    => $this->MIMEBody,
		];

		return true;
	}
}
