<?php /** @noinspection PhpSignatureMismatchDuringInheritanceInspection */

/**
 * @author Bendik R. Brenne <bendik@konstant.no>
 *
 * Nextcloud - Two-factor Gateway
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

use OCA\TwoFactorGateway\Exception\SmsTransmissionException;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;

class ClxMbloxSms implements IProvider {

	const PROVIDER_ID = 'clx_mblox';

	/** @var IClient */
	private $client;

	/** @var ClxMbloxSmsConfig */
	private $config;

	public function __construct(
		IClientService $clientService,
		WebSmsConfig $config) {
		$this->client = $clientService->newClient();
		$this->config = $config;
	}

	/**
	 * @inheritdoc
	 *
	 * @throws SmsTransmissionException
	 * @throws \OCA\TwoFactorGateway\Exception\ConfigurationException
	 */
	public function send(string $identifier, string $message) {
		$config = $this->getConfig();

		try {
			$response = $this->client->get(
				$config->getConnectionURL(), [
				'query' => [
					'S'  => 'H',
					'UN' => $config->getUser(),
					'P'  => $config->getPassword(),
					'DA' => $identifier,
					'SA' => $config->getSender(),
					'M'  => self::GSMEncode($message),
				],
			]);
			preg_match("/(OK.*)\r$/", $response->getBody(), $matches);
			preg_match("/^OK((\s\-?\d+)+)(\sUR:.+)?/", $matches[1], $matches);
			$number = explode(" ", $matches[1]);
			array_shift($number);
		} catch (\OCA\TwoFactorGateway\Exception\ConfigurationException $e) {
			throw $e;
		} catch (\Exception $e) {
			throw new SmsTransmissionException();
		}
	}

	/**
	 * @inheritdoc
	 *
	 * @return ClxMbloxSmsConfig
	 */
	public function getConfig(): IProviderConfig {
		return $this->config;
	}

	/**
	 * @param string $to_encode
	 *
	 * @return string
	 *
	 * @see https://www.clxcommunications.com/docs/atlas/sms/httpsms.html
	 */
	private static function GSMEncode($to_encode) {

		$gsmchar = [
			"\x0A" => "\x0A",
			"\x0D" => "\x0D",
			"\x24" => "\x02",
			"\x40" => "\x00",
			"\x13" => "\x13",
			"\x10" => "\x10",
			"\x19" => "\x19",
			"\x14" => "\x14",
			"\x1A" => "\x1A",
			"\x16" => "\x16",
			"\x18" => "\x18",
			"\x12" => "\x12",
			"\x17" => "\x17",
			"\x15" => "\x15",
			"\x5B" => "\x1B\x3C",
			"\x5C" => "\x1B\x2F",
			"\x5D" => "\x1B\x3E",
			"\x5E" => "\x1B\x14",
			"\x5F" => "\x11",
			"\x7B" => "\x1B\x28",
			"\x7C" => "\x1B\x40",
			"\x7D" => "\x1B\x29",
			"\x7E" => "\x1B\x3D",
			"\x80" => "\x1B\x65",
			"\xA1" => "\x40",
			"\xA3" => "\x01",
			"\xA4" => "\x1B\x65",
			"\xA5" => "\x03",
			"\xA7" => "\x5F",
			"\xBF" => "\x60",
			"\xC0" => "\x41",
			"\xC1" => "\x41",
			"\xC2" => "\x41",
			"\xC3" => "\x41",
			"\xC4" => "\x5B",
			"\xC5" => "\x0E",
			"\xC6" => "\x1C",
			"\xC7" => "\x09",
			"\xC8" => "\x45",
			"\xC9" => "\x1F",
			"\xCA" => "\x45",
			"\xCB" => "\x45",
			"\xCC" => "\x49",
			"\xCD" => "\x49",
			"\xCE" => "\x49",
			"\xCF" => "\x49",
			"\xD0" => "\x44",
			"\xD1" => "\x5D",
			"\xD2" => "\x4F",
			"\xD3" => "\x4F",
			"\xD4" => "\x4F",
			"\xD5" => "\x4F",
			"\xD6" => "\x5C",
			"\xD8" => "\x0B",
			"\xD9" => "\x55",
			"\xDA" => "\x55",
			"\xDB" => "\x55",
			"\xDC" => "\x5E",
			"\xDD" => "\x59",
			"\xDF" => "\x1E",

			"\xE0" => "\x7F",
			"\xE1" => "\x61",
			"\xE2" => "\x61",
			"\xE3" => "\x61",
			"\xE4" => "\x7B",
			"\xE5" => "\x0F",
			"\xE6" => "\x1D",
			"\xE7" => "\x63",
			"\xE8" => "\x04",
			"\xE9" => "\x05",
			"\xEA" => "\x65",
			"\xEB" => "\x65",
			"\xEC" => "\x07",
			"\xED" => "\x69",
			"\xEE" => "\x69",
			"\xEF" => "\x69",
			"\xF0" => "\x64",
			"\xF1" => "\x7D",
			"\xF2" => "\x08",
			"\xF3" => "\x6F",
			"\xF4" => "\x6F",
			"\xF5" => "\x6F",
			"\xF6" => "\x7C",
			"\xF8" => "\x0C",
			"\xF9" => "\x06",
			"\xFA" => "\x75",
			"\xFB" => "\x75",
			"\xFC" => "\x7E",
			"\xFD" => "\x79",
		];

		# using the NO_EMPTY flag eliminates the need for the shift pop correction
		$chars = preg_split("//", $to_encode, -1, PREG_SPLIT_NO_EMPTY);

		$to_return = "";

		foreach ($chars as $char) {
			preg_match("/[A-Za-z0-9!\/#%&\"=\-'<>\?\(\)\*\+\,\.;:]/", $char, $matches);
			if (isset($matches[0])) {
				$to_return .= $char;
			}
			else {
				if (!isset($gsmchar[$char])) {
					$to_return .= "\x20";
				}
				else {
					$to_return .= $gsmchar[$char];
				}
			}
		}

		return $to_return;
	}
}
