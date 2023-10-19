<?php

// Note: this script is expected to return a stringified JSON representation of the slack message
// avoid any echo or print commands other than the final one

define('CHANGELOG_JSON', getenv('CHANGELOG_JSON'));
define('CHANGELOG_STRING', getenv('CHANGELOG_STRING'));
define('PROJECT', getenv('PROJECT'));

$values = getopt(
	'',
	array(
		'version:',
		'repo:',
		'out:'
	)
);

if (!isset($values["version"])) {
	echo "version is empty!";
	exit(1);
}

if (!isset($values["repo"])) {
	echo "repo is empty!";
	exit(1);
}

if (!isset($values["out"])) {
	echo "out is empty! please specify output filename";
	exit(1);
}

$slack_payload_blocks = [
	[
		"type" => "divider"
	],
	[
		"type" => "header",
		"text" => [
			"type" => "plain_text",
			"text" => PROJECT." GitHub Release.\n"
		]
	],
	[
		"type" => "section",
		"text" => [
			"type" => "mrkdwn",
			"text" => "You can <https://github.com/{$values['repo']}/releases/tag/{$values['version']}|check the release here>.\n"
		]
	],
	[
		"type" => "section",
		"text" => [
			"type" => "mrkdwn",
			"text" => ":white_check_mark: 4.{$values['version']}\n:white_check_mark: 3.{$values['version']}\n:white_check_mark: 2.{$values['version']}\n:no_entry: 1.{$values['version']} (will be released separately)\n"
		]
	],
];

$pattern = '/PR:#(\d+)(.*)/';

try {
	foreach (json_decode(CHANGELOG_JSON) as $key => $array_of_prs) {
		$formatted_key = str_replace('##', '', $key);
		$slack_payload_blocks[] = [
			"type" => "header",
			"text" => [
				"type" => "plain_text",
				"text" => $formatted_key
			]
		];

		foreach ($array_of_prs as $pr) {
			preg_match($pattern, $pr, $matches);
			$pr_number = $matches[1];
			$pr_title = $matches[2];
			$slack_payload_blocks[] = [
				"type" => "section",
				"text" => [
					"type" => "mrkdwn",
					"text" => "<https://github.com/{$values['repo']}/pull/{$pr_number}|$pr_number> $pr_title"
				]
			];
		}
	}
} catch (\Throwable $th) {
	echo "Failed to parse CHANGELOG_JSON";
}

$slack_payload = [
	"text" => "\n\n\n".PROJECT." GitHub Release {$values['version']}\nChangelog:\n" . CHANGELOG_STRING,
	"blocks" => $slack_payload_blocks,
];

$json = json_encode($slack_payload, JSON_PRETTY_PRINT);
$fp = fopen($values["out"], 'w');
fwrite($fp, $json);
fclose($fp);
