<?php
	require_once 'vendor/autoload.php';
	require_once 'llm-call.php';
	require_once 'action-session.php';
	require_once 'action-init.php';

	function uuid()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
// 16 bits for "time_mid"
			mt_rand(0, 0xffff),
// 16 bits for "time_hi_and_version",
// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,
// 16 bits, 8 bits for "clk_seq_hi_res",
// 8 bits for "clk_seq_low",
// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,
// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	function openAI_question($messages, $temperature, $max_tokens, $model = 'gpt-4o-mini')
	{
		$data = array(
			'model' => $model,
			'messages' => $messages,
			'temperature' => $temperature,
			'max_tokens' => $max_tokens,
			'top_p' => 1,
			'frequency_penalty' => 0,
			'presence_penalty' => 0,
			'n' => 1,
			'stream' => false,
			'stop' => "" //"\n"
		);

		session_write_close();

		$post_json = json_encode($data);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);

		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = "Authorization: Bearer ".$_ENV['OPEN_AI_GPT4_KEY'];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		session_start();
		curl_close($ch);
		$result = json_decode($result, true);

		if (isset($result['choices'])) {
			$chat_response_text = $result['choices'][0]['message']['content'] . '';
		} else {
			$chat_response_text = '';
		}
		return $chat_response_text;

	}

	$theme = $_POST['theme'] ?? 'an ocean in space';
	$title1 = $_POST['title_1'] ?? 'Playground Computer';
	$author1 = $_POST['author_1'] ?? 'I, Robot';
	$model = $_POST['model'] ?? 'fast';
	$creative = $_POST['creative'] ?? 'no';


	$prompt = $theme. ' the book covers title is "'.$title1.'" and the author is "'.$author1.'" include the text lines on the cover.';

	if ($creative === 'more') {

		$gpt_prompt = "Write a prompt for an album cover 
The image in the background is :
 " . $theme . "
 the book title is " . $title1 . "
 the author is " . $author1 . "

With the above information, compose an album cover. Write it as a single paragraph. The instructions should focus on the text elements of the album cover. Generally the title should be on top part and the artist on the bottom of the image.

Prompt:";

		$single_request = [
			[
				"role" => "system",
				"content" => "You write prompts for making music album covers. Follow the format of the examples."
			],
			[
				"role" => "user",
				"content" => $gpt_prompt
			]
		];

		$prompt = openAI_question($single_request, 1, 256, 'gpt-4o');
		$gpt_results = $prompt;
	}

	$filename = uuid() . '.jpg';
	$outputFile = 'ai-images/' . $filename;

//make sure the folder exists
	if (!file_exists('ai-images')) {
		mkdir('ai-images', 0777, true);
	}

	$falApiKey = $_ENV['FAL_API_KEY'];
	if (empty($falApiKey)) {
		echo json_encode(['error' => 'FAL_API_KEY environment variable is not set']);
	}

	$client = new \GuzzleHttp\Client();

	$url = 'https://fal.run/fal-ai/flux/schnell';
	if ($model === 'fast') {
		$url = 'https://fal.run/fal-ai/flux/schnell';
	}
	if ($model === 'balanced') {
		$url = 'https://fal.run/fal-ai/flux/dev';
	}
	if ($model === 'detailed') {
		$url = 'https://fal.run/fal-ai/flux-pro';
	}

	$response = $client->post($url, [
		'headers' => [
			'Authorization' => 'Key ' . $falApiKey,
			'Content-Type' => 'application/json',
		],
		'json' => [
			'prompt' => $prompt,
			'image_size' => 'square_hd',
			'safety_tolerance' => '5',
		]
	]);

	$body = $response->getBody();
	$data = json_decode($body, true);

//add to render log file
	$renderLog = [
		'prompt' => $prompt,
		'image_size' => 'portrait_16_9',
		'safety_tolerance' => '5',
		'output_file' => $filename,
		'status_code' => $response->getStatusCode(),
		'body' => $body,
		'data' => $data
	];

	$renderLogPath = 'ai-images/' . 'render-log.json';
	$renderLogs = [];
	if (file_exists($renderLogPath)) {
		$renderLogs = json_decode(file_get_contents($renderLogPath), true);
	}
	$renderLogs[] = $renderLog;
	file_put_contents($renderLogPath, json_encode($renderLogs, JSON_PRETTY_PRINT));

	if ($response->getStatusCode() == 200) {


		if (isset($data['images'][0]['url'])) {
			$image_url = $data['images'][0]['url'];

			//save image_url to folder both .png and _1024.png
			$image = file_get_contents($image_url);
			file_put_contents($outputFile, $image);

			echo json_encode(['success' => true, 'message' => 'Image generated successfully', 'output_filename' => $filename, 'output_path' => $outputFile, 'data' => json_encode($data), 'seed' => $data['seed'], 'status_code' => $response->getStatusCode()]);
		} else {
			echo json_encode(['success' => false, 'message' => 'Error (2) generating image', 'status_code' => $response->getStatusCode()]);
		}
	} else {
		echo json_encode(['success' => false, 'message' => 'Error (1) generating image', 'status_code' => $response->getStatusCode()]);
	}
