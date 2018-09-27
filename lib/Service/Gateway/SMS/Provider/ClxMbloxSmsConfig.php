<?php

/**
 * @author Bendik R. Brenne <bendik@konstant.no>
 *
 * Nextcloud - Two-factor Gateway for Telegram
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\TwoFactorGateway\Service\Gateway\SMS\Provider;

use OCA\TwoFactorGateway\AppInfo\Application;
use OCA\TwoFactorGateway\Exception\ConfigurationException;
use OCP\IConfig;

class ClxMbloxSmsConfig implements IProviderConfig {

	const CLX_MBLOX_DEFAULT_URL = 'https://sms1.mblox.com:9444/HTTPSMS';

	const CLX_MBLOX_DEFAULT_SENDER = 'Nextcloud';

	/** @var IConfig */
	private $config;

	public function __construct(IConfig $config) {
		$this->config = $config;
	}

	/**
	 * @return string
	 */
	public function getConnectionURL(): string {
		return $this->config->getAppValue(
			Application::APP_NAME,
			'clx_mblox_connection_url',
			self::CLX_MBLOX_DEFAULT_URL
		);
	}

	/**
	 * @param string $url
	 */
	public function setConnectionURL(string $url) {
		$this->config->setAppValue(Application::APP_NAME, 'clx_mblox_connection_url', $url);
	}

	/**
	 * @return string
	 */
	public function getSender(): string {
		return $this->config->getAppValue(
			Application::APP_NAME,
			'clx_mblox_sender',
			self::CLX_MBLOX_DEFAULT_SENDER
		);
	}

	/**
	 * @param string $sender
	 */
	public function setSender(string $sender) {
		$this->config->setAppValue(Application::APP_NAME, 'clx_mblox_sender', $sender);
	}

	/**
	 * @return string
	 * @throws ConfigurationException
	 */
	public function getUser(): string {
		return $this->getOrFail('clx_mblox_user');
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 * @throws ConfigurationException
	 */
	private function getOrFail(string $key): string {
		$val = $this->config->getAppValue(Application::APP_NAME, $key, NULL);
		if (is_null($val)) {
			throw new ConfigurationException();
		}

		return $val;
	}

	public function setUser(string $user) {
		$this->config->setAppValue(Application::APP_NAME, 'clx_mblox_user', $user);
	}

	/**
	 * @return string
	 * @throws ConfigurationException
	 */
	public function getPassword(): string {
		return $this->getOrFail('clx_mblox_password');
	}

	public function setPassword(string $password) {
		$this->config->setAppValue(Application::APP_NAME, 'clx_mblox_password', $password);
	}

	public function isComplete(): bool {
		$set = $this->config->getAppKeys(Application::APP_NAME);
		$expected = [
			'clx_mblox_sender',
			'clx_mblox_connection_url',
			'clx_mblox_user',
			'clx_mblox_password',
		];

		return count(array_intersect($set, $expected)) === count($expected);
	}

}
