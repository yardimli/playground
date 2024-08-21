<?php

	require_once 'vendor/autoload.php';

	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();

	$host = $_ENV['DB_HOST'];
	$user = $_ENV['DB_USERNAME'];
	$password = $_ENV['DB_PASSWORD'];
	$database = $_ENV['DB_DATABASE'];

	use Ahc\Json\Fixer;


	class Log
	{
		private $filePath;

		public function __construct()
		{
			$date = new DateTime();
			$logFolder = __DIR__ . '/logs';
			if (!file_exists($logFolder)) {
				mkdir($logFolder, 0777, true);
			}
			$this->filePath = sprintf('%s/%s.txt', $logFolder, $date->format('Y-m-d'));
		}

		public function info($message)
		{
			$this->writeLog('INFO', $message);
		}

		private function writeLog($level, $message)
		{
			$date = new DateTime();

			if (is_array($message)) {
				$processedMessage = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			} else {
				$processedMessage = $message;
			}
			$processedMessage = str_replace('\r\n', "\n", $processedMessage);
			$processedMessage = str_replace('\r', "\n", $processedMessage);
			$processedMessage = str_replace('\n', "\n", $processedMessage);
//				$processedMessage = nl2br($processedMessage);

			$formattedMessage = sprintf(
				"[%s] [%s] %s \n",
				$date->format('Y-m-d H:i:s'),
				$level,
				$processedMessage
			);

			file_put_contents($this->filePath, $formattedMessage, FILE_APPEND);
		}
	}


	class LlmFunctions
	{
		private $logger;

		public function __construct($logger = null)
		{
			$this->logger = new Log();
		}

		public function helloWorld()
		{
			echo 'Hello, World!';
			$this->logger->info('Hello, World!');
		}

		public function validateJson($str)
		{
			$this->logger->info('Starting JSON validation.');

			$error = json_last_error();
			json_decode($str);
			$error = json_last_error();

			switch ($error) {
				case JSON_ERROR_NONE:
					return "Valid JSON";
				case JSON_ERROR_DEPTH:
					return "Maximum stack depth exceeded";
				case JSON_ERROR_STATE_MISMATCH:
					return "Underflow or the modes mismatch";
				case JSON_ERROR_CTRL_CHAR:
					return "Unexpected control character found";
				case JSON_ERROR_SYNTAX:
					return "Syntax error, malformed JSON";
				case JSON_ERROR_UTF8:
					return "Malformed UTF-8 characters, possibly incorrectly encoded";
				default:
					return "Unknown error";
			}
		}


		function getContentsInBackticksOrOriginal($input)
		{
			// Define a regular expression pattern to match content within backticks
			$pattern = '/`([^`]+)`/';

			// Initialize an array to hold matches
			$matches = array();

			// Perform a global regular expression match
			preg_match_all($pattern, $input, $matches);

			// Check if any matches were found
			if (empty($matches[1])) {
				return $input; // Return the original input if no matches found
			} else {
				return implode(' ', $matches[1]);
			}
		}

		public function moderation($message)
		{
			$openai_api_key = $_ENV['OPEN_AI_GPT4_KEY'];

			$response = Http::withHeaders([
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $openai_api_key,
			])->post($_ENV['OPEN_AI_GPT4_BASE'] . '/moderations', [
				'input' => $message,
			]);

			return $response->json();
		}

		//------------------------------------------------------------
		public function function_call($llm, $prompt, $schema, $language = 'english')
		{
			set_time_limit(300);
			session_write_close();

			$use_llm = $_ENV['USE_LLM'] ?? 'openai';

			if ($use_llm === 'anthropic-haiku') {
				$llm_base_url = $_ENV['ANTHROPIC_HAIKU_BASE'];
				$llm_api_key = $_ENV['ANTHROPIC_HAIKU_KEY'];
				$llm_model = $_ENV['ANTHROPIC_HAIKU_MODEL'];

			} else if ($use_llm === 'anthropic-sonet') {
				$llm_base_url = $_ENV['ANTHROPIC_SONET_BASE'];
				$llm_api_key = $_ENV['ANTHROPIC_SONET_KEY'];
				$llm_model = $_ENV['ANTHROPIC_SONET_MODEL'];

			} else if ($use_llm === 'open-router') {
				$llm_base_url = $_ENV['OPEN_ROUTER_BASE'];
				$llm_api_key = $_ENV['OPEN_ROUTER_KEY'];
				$llm_model = $_ENV['OPEN_ROUTER_MODEL'];
				if ($llm !== '' && $llm !== null) {
					$llm_model = $llm;
				}

			} else if ($use_llm === 'open-ai-gpt-4o') {
				$llm_base_url = $_ENV['OPEN_AI_GPT4_BASE'];
				$llm_api_key = $_ENV['OPEN_AI_GPT4_KEY'];
				$llm_model = $_ENV['OPEN_AI_GPT4_MODEL'];

			} else if ($use_llm === 'open-ai-gpt-4o-mini') {
				$llm_base_url = $_ENV['OPEN_AI_GPT4_MINI_BASE'];
				$llm_api_key = $_ENV['OPEN_AI_GPT4_MINI_KEY'];
				$llm_model = $_ENV['OPEN_AI_GPT4_MINI_MODEL'];
			}

			$chat_messages = [];
			if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {

				$chat_messages[] = [
					'role' => 'user',
					'content' => $prompt
				];
			} else {
//				$chat_messages[] = [
//					'role' => 'system',
//					'content' => 'You are an expert author advisor.'
//				];
				$chat_messages[] = [
					'role' => 'user',
					'content' => $prompt
				];
			}


			$temperature = 0.8;
			$max_tokens = 4000;

			$tool_name = 'auto';
//			if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
//				$tool_name = $schema['function']['name'];
//			}

			$data = array(
				'model' => $llm_model,
				'messages' => $chat_messages,
				'tools' => [$schema],
				'tool_choice' => $tool_name,
				'temperature' => $temperature,
				'max_tokens' => $max_tokens,
				'top_p' => 1,
				'frequency_penalty' => 0,
				'presence_penalty' => 0,
				'n' => 1,
				'stream' => false,
				'stop' => "" //"\n"
			);

			if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
				//remove tool_choice
				unset($data['tool_choice']);
				unset($data['frequency_penalty']);
				unset($data['presence_penalty']);
				unset($data['n']);
				unset($data['stop']);
			}

			$this->logger->info('==================openAI_question=====================');
			$this->logger->info($data);

			$post_json = json_encode($data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $llm_base_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);

			$headers = array();
			if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
				$headers[] = "x-api-key: " . $llm_api_key;
				$headers[] = 'anthropic-version: 2023-06-01';
				$headers[] = 'content-type: application/json';
			} else {
				$headers[] = 'Content-Type: application/json';
				$headers[] = "Authorization: Bearer " . $llm_api_key;
			}

			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$complete = curl_exec($ch);
			if (curl_errno($ch)) {
				$this->logger->info('CURL Error:');
				$this->logger->info(curl_getinfo($ch));
			}
			curl_close($ch);
//			session_start();

			$this->logger->info('==================Log complete 1 =====================');
			$complete = trim($complete, " \n\r\t\v\0");
			$this->logger->info($complete);

			$validateJson = $this->validateJson($complete);
			if ($validateJson == "Valid JSON") {
				$this->logger->info('==================Log JSON complete=====================');
				$complete_rst = json_decode($complete, true);
				$this->logger->info($complete_rst);
				$arguments_rst = [];

				if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
					$contents = $complete_rst['content'];
					foreach ($contents as $content) {
						if ($content['type'] === 'tool_use') {
							$arguments_rst = $content['input'];
						}
					}
				} else {
					$content = $complete_rst['choices'][0]['message']['tool_calls'][0]['function'];
					$arguments = $content['arguments'];
					$validateJson = $this->validateJson($arguments);
					if ($validateJson == "Valid JSON") {
						$this->logger->info('==================Log JSON arguments=====================');
						$arguments_rst = json_decode($arguments, true);
						$this->logger->info($arguments_rst);
					}
				}


				return $arguments_rst;
			} else {
				$this->logger->info('==================Log JSON error=====================');
				$this->logger->info($validateJson);
			}
		}

		public function extractJsonString($input)
		{

			//replace \n\n with <br><br>
			$input = str_replace("\",\n\n\"", "\",\n\"", $input);

			$input = str_replace("\n\n", "[NEW-PARA-0]", $input);

			// Find the first position of '{' or '['
			$startPos = strpos($input, '{');
			if ($startPos === false) {
				$startPos = strpos($input, '[');
			}

			// Find the last position of '}' or ']'
			$endPos = strrpos($input, '}');
			if ($endPos === false) {
				$endPos = strrpos($input, ']');
			}

			// If start or end positions are not found, return an empty string
			if ($startPos === false || $endPos === false) {
				return '';
			}

			// Extract the JSON substring
			$jsonString = substr($input, $startPos, $endPos - $startPos + 1);

			$jsonString = str_replace("[NEW-PARA-0]", "\\n\\n", $jsonString);

			return $jsonString;
		}

		public function mergeStringsWithoutRepetition($string1, $string2, $maxRepetitionLength = 100)
		{
			$len1 = strlen($string1);
			$len2 = strlen($string2);

			// Determine the maximum possible repetition length
			$maxPossibleRepetition = min($maxRepetitionLength, $len1, $len2);

			// Find the length of the actual repetition
			$repetitionLength = 0;
			for ($i = 1; $i <= $maxPossibleRepetition; $i++) {
				if (substr($string1, -$i) === substr($string2, 0, $i)) {
					$repetitionLength = $i;
				} else {
					break;
				}
			}

			// Remove the repetition from the beginning of the second string
			$string2 = substr($string2, $repetitionLength);

			// Merge the strings
			return $string1 . $string2;
		}

		public function llm_no_tool_call($stream, $llm, $prompt, $return_json = true, $language = 'english')
		{
			set_time_limit(300);
			session_write_close();

			$use_llm = $_ENV['USE_LLM'] ?? 'open-router';


			if ($llm === 'openai/gpt-4o-mini') {
				$use_llm = 'open-ai-gpt-4o-mini';
			}

			if ($use_llm === 'anthropic-haiku') {
				$llm_base_url = $_ENV['ANTHROPIC_HAIKU_BASE'];
				$llm_api_key = $_ENV['ANTHROPIC_HAIKU_KEY'];
				$llm_model = $_ENV['ANTHROPIC_HAIKU_MODEL'];

			} else if ($use_llm === 'anthropic-sonet') {
				$llm_base_url = $_ENV['ANTHROPIC_SONET_BASE'];
				$llm_api_key = $_ENV['ANTHROPIC_SONET_KEY'];
				$llm_model = $_ENV['ANTHROPIC_SONET_MODEL'];

			} else if ($use_llm === 'open-router') {
				$llm_base_url = $_ENV['OPEN_ROUTER_BASE'];
				$llm_api_key = $_ENV['OPEN_ROUTER_KEY'];
				$llm_model = $_ENV['OPEN_ROUTER_MODEL'];

				if ($llm !== '' && $llm !== null) {
					$llm_model = $llm;
				}

			} else if ($use_llm === 'open-ai-gpt-4o') {
				$llm_base_url = $_ENV['OPEN_AI_GPT4_BASE'];
				$llm_api_key = $_ENV['OPEN_AI_GPT4_KEY'];
				$llm_model = $_ENV['OPEN_AI_GPT4_MODEL'];

			} else if ($use_llm === 'open-ai-gpt-4o-mini') {
				$llm_base_url = $_ENV['OPEN_AI_GPT4_MINI_BASE'];
				$llm_api_key = $_ENV['OPEN_AI_GPT4_MINI_KEY'];
				$llm_model = $_ENV['OPEN_AI_GPT4_MINI_MODEL'];
			}

			$chat_messages = [];
			if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
				$chat_messages[] = [
					'role' => 'user',
					'content' => $prompt
				];
			} else {
//				$chat_messages[] = [
//					'role' => 'system',
//					'content' => 'You are an expert author advisor.'
//				];
				$chat_messages[] = [
					'role' => 'user',
					'content' => $prompt
				];
			}


			$temperature = 0.8;
			$max_tokens = 8096;

			$data = array(
				'model' => $llm_model,
				'messages' => $chat_messages,
				'temperature' => $temperature,
				'max_tokens' => $max_tokens,
				'top_p' => 1,
				'frequency_penalty' => 0,
				'presence_penalty' => 0,
				'n' => 1,
				'stream' => $stream,
				'stop' => "" //"\n"
			);

			if ($use_llm === 'open-ai-gpt-4o' || $use_llm === 'open-ai-gpt-4o-mini') {
				$data['max_tokens'] = 4096;
				$data['temperature'] = 1;
			} else if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
				if ($use_llm === 'anthropic-haiku') {
					$data['max_tokens'] = 4096;
				} else {
					$data['max_tokens'] = 4096;
				}
				unset($data['stop']);
			} else if ($use_llm === 'open-router') {

//				$data['provider'] = [
//					"order" => [
//						"OpenAI",
//						"Mistral",
//						"Google",
//						"Cohere",
//						"Novita",
//						"Together",
//						"Lambda",
//						"Perplexity",
//						"Fireworks"
//					],
//					"allow_fallbacks" => false
//				];

				$data['max_tokens'] = 8096;
				if (stripos($llm_model, 'anthropic') !== false) {
					unset($data['frequency_penalty']);
					unset($data['presence_penalty']);
					unset($data['n']);
					unset($data['stop']);
				}
				if (stripos($llm_model, 'openai') !== false) {
					$data['temperature'] = 1;
				}
				if (stripos($llm_model, 'google') !== false) {
					$data['stop'] = [];
				}
			}

			$this->logger->info('GPT NO TOOL USE: '.$llm_base_url);
			$this->logger->info($data);

			$post_json = json_encode($data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $llm_base_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);

			$headers = array();
			if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
				$headers[] = "x-api-key: " . $llm_api_key;
				$headers[] = 'anthropic-version: 2023-06-01';
				$headers[] = 'content-type: application/json';
			} else {
				$headers[] = 'Content-Type: application/json';
				$headers[] = "Authorization: Bearer " . $llm_api_key;
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$accumulatedData = '';
			$txt = '';
			$complete_rst = '';

			if ($stream) {

				curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) use (&$txt, &$accumulatedData) {
//					$this->logger->info('==============');
//					$this->logger->info($data);

					$data_lines = explode("\n", $data);
					for ($i = 0; $i < count($data_lines); $i++) {
						$data_line = $data_lines[$i];

						// Check if the data line contains [DONE]
						if (stripos($data_line, "[DONE]") !== false) {
							ob_flush();
							flush();
							return strlen($data_line);
						}

						// Append new data to the accumulated data
						if (substr($data_line, 0, 5) !== "data:") {
							$accumulatedData .= $data_line;
						} else {
							$accumulatedData = $data_line;
						}

						// Check if we have a complete JSON object
						$clean = str_replace("data: ", "", $accumulatedData);
						$decoded = json_decode($clean, true);
						if ($decoded && isset($decoded["choices"])) {
							$txt .= $decoded["choices"][0]["delta"]["content"] ?? '';
							$accumulatedData = ''; // Reset accumulated data
						}
					}

					return strlen($data);
				});
			}

			$complete = curl_exec($ch);
			if (curl_errno($ch)) {
				$this->logger->info('CURL Error:');
				$this->logger->info(curl_getinfo($ch));
			}
			curl_close($ch);

			if (!$stream) {
//			$this->logger->info('==================Log complete 2 =====================');
				$complete = trim($complete, " \n\r\t\v\0");
//			$this->logger->info($complete);

				$complete_rst = json_decode($complete, true);

				if ($use_llm === 'open-ai-gpt-4o' || $use_llm === 'open-ai-gpt-4o-mini') {
					$content = $complete_rst['choices'][0]['message']['content'];
				} else if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
					$content = $complete_rst['content'][0]['text'];
				} else if ($use_llm === 'open-router') {
					$content = $complete_rst['choices'][0]['message']['content'];
				}
			} else {
//				echo "--------------TXT: $txt\n";
				$content = $txt;
				$complete = $txt;
			}

			if (!$return_json) {
				if (!$stream) {
					$this->logger->info("GPT NO STREAM RESPONSE:");
					$this->logger->info($complete_rst);
				} else {
					$this->logger->info("GPT STREAM RESPONSE:");
					$this->logger->info($content);
				}

				$this->logger->info('Return is NOT JSON. Will return content presuming it is text.');
				return $content;
			}

//			$content = str_replace("\\\"", "\"", $content);
			$content = $content ?? '';
			$content = $this->getContentsInBackticksOrOriginal($content);

			//check if content is JSON
			$content_json_string = $this->extractJsonString($content);
			$validate_result = $this->validateJson($content_json_string);

			if ($validate_result !== "Valid JSON") {
				$content_json_string =  (new Fixer)->silent(true)->missingValue('"truncated"')->fix($content_json_string);
				$validate_result = $this->validateJson($content_json_string);
			}

			if (strlen($content ?? '') < 20) {
				$this->logger->info('================== CONTENT IS EMPTY =====================');
				$this->logger->info($complete);
				return '';
			}

			//if JSON failed make a second call to get the rest of the JSON
			if ($validate_result !== "Valid JSON") {

				//------ Check if JSON is complete or not with a prompt to continue ------------
				//-----------------------------------------------------------------------------
				$verify_completed_prompt = 'If the JSON is complete output DONE otherwise continue writing the JSON response. Only write the missing part of the JSON response, don\'t repeat the already written story JSON. Continue from exactly where the JSON response left off. Make sure the combined JSON response will be valid JSON.';

				$chat_messages[] = [
					'role' => 'assistant',
					'content' => $content
				];
				$chat_messages[] = [
					'role' => 'user',
					'content' => $verify_completed_prompt
				];

				$data['messages'] = $chat_messages;
				$this->logger->info('======== SECOND CALL TO FINISH JSON =========');
				$this->logger->info($data);
				$post_json = json_encode($data);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $llm_base_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$accumulatedData = '';
				$txt = '';

				if ($stream) {

					curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) use (&$txt, &$accumulatedData) {
//						$this->logger->info('==============');
//						$this->logger->info($data);

						$data_lines = explode("\n", $data);
						for ($i = 0; $i < count($data_lines); $i++) {
							$data_line = $data_lines[$i];

							// Check if the data line contains [DONE]
							if (stripos($data_line, "[DONE]") !== false) {
								ob_flush();
								flush();
								return strlen($data_line);
							}

							// Append new data to the accumulated data
							if (substr($data_line, 0, 5) !== "data:") {
								$accumulatedData .= $data_line;
							} else {
								$accumulatedData = $data_line;
							}

							// Check if we have a complete JSON object
							$clean = str_replace("data: ", "", $accumulatedData);
							$decoded = json_decode($clean, true);
							if ($decoded && isset($decoded["choices"])) {
								$txt .= $decoded["choices"][0]["delta"]["content"] ?? '';
								$accumulatedData = ''; // Reset accumulated data
							}
						}

						return strlen($data);
					});
				}

				$complete2 = curl_exec($ch);
				if (curl_errno($ch)) {
					$this->logger->info('CURL Error:');
					$this->logger->info(curl_getinfo($ch));
				}
				curl_close($ch);
//				session_start();

				if (!$stream) {
					$complete2 = trim($complete2, " \n\r\t\v\0");

					$this->logger->info("GPT NO STREAM RESPONSE FOR EXTENDED VERSION JSON CHECK:");
					$this->logger->info($complete2);

					$complete2_rst = json_decode($complete2, true);
					$content2 = $complete2_rst['choices'][0]['message']['content'];

					//$content2 = str_replace("\\\"", "\"", $content2);
					$content2 = $this->getContentsInBackticksOrOriginal($content2);

					if (!str_contains($content2, 'DONE')) {
						$content = $this->mergeStringsWithoutRepetition($content, $content2, 255);
					}
				} else {
					$content2 = $txt;
					$content2 = trim($content2, " \n\r\t\v\0");

					$this->logger->info("GPT STREAM RESPONSE FOR EXTENDED VERSION JSON CHECK:");
					$this->logger->info($content2);

					//$content2 = str_replace("\\\"", "\"", $content2);
					$content2 = $this->getContentsInBackticksOrOriginal($content2);

					if (!str_contains($content2, 'DONE')) {
						$content = $this->mergeStringsWithoutRepetition($content, $content2, 255);
					}
				}

				//------------------------------------------------------------

				$content_json_string = $this->extractJsonString($content);
				$validate_result = $this->validateJson($content_json_string);

				if ($validate_result !== "Valid JSON") {
					$content_json_string =  (new Fixer)->silent(true)->missingValue('"truncated"')->fix($content_json_string);
					$validate_result = $this->validateJson($content_json_string);
				}

			} else
			{
				if (!$stream) {
					$this->logger->info("GPT NO STREAM RESPONSE:");
					$this->logger->info($complete_rst);
				} else {
					$this->logger->info("GPT STREAM RESPONSE:");
					$this->logger->info($content);
				}

			}

			if ($validate_result == "Valid JSON") {
				$this->logger->info('================== VALID JSON =====================');
				$content_rst = json_decode($content_json_string, true);
				$this->logger->info($content_rst);
				return $content_rst;
			} else {
				$this->logger->info('================== INVALID JSON =====================');
				$this->logger->info('JSON error : ' . $validate_result . ' -- ');
				$this->logger->info($content);
			}
		}


		public function createBookStructure($book, $prompt, $model, $language = 'english', $current_user)
		{
			$timestamp = time();
			$book_folder = __DIR__ . "/books/book_$timestamp";
			if (!file_exists($book_folder)) {
				mkdir($book_folder, 0777, true);
			}

			// Create book.json
			$book_file = $book_folder . '/book.json';
			$book_header = [
				'title' => $book['title'] ?? 'book title',
				'blurb' => $book['blurb'] ?? 'book blurb',
				'prompt' => $prompt,
				'model' => $model,
				'back_cover_text' => $book['back_cover_text'] ?? 'back cover text',
				'folder' => $book_folder,
				'created' => (new DateTime())->format('Y-m-d H:i:s'),
				'lastUpdated' => (new DateTime())->format('Y-m-d H:i:s'),
				'language' => $language,
				'owner' => $current_user,
			];
			file_put_contents($book_file, json_encode($book_header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

			$acts = $book['acts'];
			$chapters_summary = [];

			foreach ($acts as $act_index => $act) {
				$act_id = 'act-' . ($act_index + 1);
				$act_name = $act['name'] ?? 'act ' . ($act_index + 1);

				$chapters = $act['chapters'];
				foreach ($chapters as $chapter_index => $chapter) {
					$chapter_name = $chapter['name'] ?? 'chapter ' . ($chapter_index + 1);
					$slug_name = $this->slugify($chapter_name);

					$chapter_data = [
						'row' => $act_id,
						'order' => $chapter_index + 1,
						'name' => $chapter_name,
						'short_description' => $chapter['short_description'] ?? 'no description',
						'events' => $chapter['events'] ?? 'no events',
						'people' => $chapter['people'] ?? 'no people',
						'places' => $chapter['places'] ?? 'no places',
						'from_prev_chapter' => $chapter['from_prev_chapter'] ?? 'N/A',
						'to_next_chapter' => $chapter['to_next_chapter'] ?? 'N/A',
						'backgroundColor' => '#AECBFA',
						'textColor' => '#000000',
						'created' => (new DateTime())->format('Y-m-d H:i:s'),
						'lastUpdated' => (new DateTime())->format('Y-m-d H:i:s'),
						'comments' => [],
						'history' => [
							['action' => 'Create chapter', 'user' => 'Admin', 'timestamp' => (new DateTime())->format('Y-m-d H:i:s')]
						]
					];

					$chapter_file = $book_folder . '/' . $slug_name . '.json';
					file_put_contents($chapter_file, json_encode($chapter_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

					//check if $chapters_summary is unique before adding
					if (!in_array($act_id, array_column($chapters_summary, 'id'))) {
						$chapters_summary[] = [
							'id' => $act_id,
							'title' => $act_name
						];
					}
				}
			}

			// Create acts.json
			$chapters_summary_file = $book_folder . '/acts.json';
			file_put_contents($chapters_summary_file, json_encode($chapters_summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		}

		private
		function slugify($text)
		{
			// replace non letter or digits by -
			$text = preg_replace('~[^\pL\d]+~u', '-', $text);
			// transliterate
			$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
			// remove unwanted characters
			$text = preg_replace('~[^-\w]+~', '', $text);
			// trim
			$text = trim($text, '-');
			// remove duplicate -
			$text = preg_replace('~-+~', '-', $text);
			// lowercase
			$text = strtolower($text);
			if (empty($text)) {
				return 'n-a';
			}
			return $text;
		}


	}



