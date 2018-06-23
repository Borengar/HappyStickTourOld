<?php

class DiscordApi {

	private $clientId;
	private $clientSecret;
	private $redirectUri;
	private $botToken;
	private $guildId;

	function __construct() {
		$config = parse_ini_file('config.ini');
		$this->clientId = $config['discordClientId'];
		$this->clientSecret = $config['discordClientSecret'];
		$this->redirectUri = $config['discordRedirectUri'];
		$this->botToken = $config['discordBotToken'];
		$this->guildId = $config['discordGuildId'];
		$this->channelId = $config['discordChannelId'];
		$this->webhook = $config['discordWebhook'];
	}

	public function getLoginUri() {
		return 'https://discordapp.com/oauth2/authorize?client_id=' . $this->clientId . '&scope=identify&redirect_uri=' . urlencode($this->redirectUri) . '&response_type=token';
	}

	public function getUser($token) {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => 'https://discordapp.com/api/users/@me',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $token
				)
			)
		);
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response;
	}

	public function removeUserRole($playerId, $roleId) {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'DELETE',
			CURLOPT_URL => 'https://discordapp.com/api/guilds/' . $this->guildId . '/members/' . $playerId . '/roles/' . $roleId,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bot ' . $this->botToken
				),
			CURLOPT_POSTFIELDS => ''
			)
		);
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response;
	}

	public function addUserRole($playerId, $roleId) {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'PUT',
			CURLOPT_URL => 'https://discordapp.com/api/guilds/' . $this->guildId . '/members/' . $playerId . '/roles/' . $roleId,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bot ' . $this->botToken
				),
			CURLOPT_POSTFIELDS => ''
			)
		);
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response;
	}

	public function getUserRoles($playerId) {
		$response = $this->getGuildMember($playerId);
		if ($response === false) {
			return false;
		}
		return $response->roles;
	}

	public function getGuildRoles() {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => 'https://discordapp.com/api/guilds/' . $this->guildId . '/roles',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bot ' . $this->botToken
				)
			)
		);
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response;
	}

	public function getGuildMember($playerId) {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => 'https://discordapp.com/api/guilds/' . $this->guildId . '/members/' . $playerId,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bot ' . $this->botToken
				)
			)
		);
		$response = json_decode(curl_exec($curl));
		if (!empty($response->code) && $response->code == 10007) {
			return false;
		}
		curl_close($curl);
		return $response;
	}

	public function getGuildMembers($after = null) {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => empty($after) ? 'https://discordapp.com/api/guilds/' . $this->guildId . '/members?limit=1000' : 'https://discordapp.com/api/guilds/' . $this->guildId . '/members?limit=1000&after=' . $after,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bot ' . $this->botToken
				)
			)
		);
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response;
	}

	public function sendMessage($message) {
		$messageObject = new stdClass;
		$messageObject->content = $message;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_URL => 'https://discordapp.com/api/channels/' . $this->channelId . '/messages',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bot ' . $this->botToken
				),
			CURLOPT_POSTFIELDS => json_encode($messageObject)
			)
		);
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response;
	}

	public function sendMatchResult($lobbyId, $matchId, $result, $roundName, $tierName) {
		var_dump($result);
		$messageObject = new stdClass;
		$messageObject->username = "RekindlingBot";
		$messageObject->avatar_url = "https://cdn.discordapp.com/app-icons/442726345869492254/4c5e5e08533eb9df9ba9c479b3ae23ce.png";
		$messageObject->embeds = [];
		$embed = new stdClass;
		$embed->color = 16711680;
		$embed->author = new stdClass;
		$embed->author->name = "Lobby " . $lobbyId . " (" . $roundName . " | " . $tierName . ")";
		$embed->author->icon_url = "https://images-ext-2.discordapp.net/external/zJZT3pGPZl6avCRuQOOnL1_1vktR3ZiN5KZTKKRmAvk/https/cdn0.iconfinder.com/data/icons/fighting-1/258/brawl003-512.png";
		$embed->description = "https://osu.ppy.sh/community/matches/" . $matchId;
		$embed->fields = [];
		$playerList = new stdClass;
		$playerList->name = "Player";
		$playerList->inline = true;
		$playerList->value = [];
		$scoreList = new stdClass;
		$scoreList->name = "Score";
		$scoreList->inline = true;
		$scoreList->value = [];
		foreach ($result as $score) {
			$playerList->value[] = $score->osu->username;
			$scoreList->value[] = $score->score;
		}
		$playerList->value = join($playerList->value, "\n");
		$scoreList->value = join($scoreList->value, "\n");
		$embed->fields[] = $playerList;
		$embed->fields[] = $scoreList;
		$messageObject->embeds[] = $embed;

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_URL => $this->webhook,
			CURLOPT_POSTFIELDS => json_encode($messageObject)
			)
		);
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		return $response;
	}
}

?>