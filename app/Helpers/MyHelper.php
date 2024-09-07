<?php

	namespace App\Helpers;

	use BinshopsBlog\Models\BinshopsCategory;
	use BinshopsBlog\Models\BinshopsCategoryTranslation;
	use BinshopsBlog\Models\BinshopsLanguage;
	use BinshopsBlog\Models\BinshopsPostTranslation;
	use Carbon\Carbon;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Session;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Facades\Validator;
	use Intervention\Image\ImageManagerStatic as Image;
	use Ahc\Json\Fixer;

	class MyHelper
	{


		public static function moderation($message)
		{
			function isValidUtf8($string)
			{
				return mb_check_encoding($string, 'UTF-8');
			}

			$openai_api_key = env('OPEN_AI_API_KEY');
			//make sure $message can be json encoded
			if (!isValidUtf8($message)) {
				$message = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $message);
			}


			$response = Http::withHeaders([
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $openai_api_key,
			])->post(env('OPEN_AI_API_BASE_MODERATIONS'), [
				'input' => $message,
			]);

			return $response->json();
			// {"id":"modr-7Km1oF0aR5KEwtX09UjbshKivO4B5","model":"text-moderation-004","results":[{"flagged":false,"categories":{"sexual":false,"hate":false,"violence":false,"self-harm":false,"sexual\/minors":false,"hate\/threatening":false,"violence\/graphic":false},"category_scores":{"sexual":1.2816758e-6,"hate":1.2005827e-7,"violence":4.2831335e-8,"self-harm":5.430266e-11,"sexual\/minors":1.4781849e-9,"hate\/threatening":1.0553468e-11,"violence\/graphic":9.761698e-9}}]}

		}

		public static function validateJson($str)
		{
			Log::info('Starting JSON validation.');

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

		public static function getContentsInBackticksOrOriginal($input)
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

		//------------------------------------------------------------
		public static function function_call($llm, $prompt, $schema, $language = 'english')
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

			Log::info('==================openAI_question=====================');
			Log::info($data);

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
				Log::info('CURL Error:');
				Log::info(curl_getinfo($ch));
			}
			curl_close($ch);
//			session_start();

			Log::info('==================Log complete 1 =====================');
			$complete = trim($complete, " \n\r\t\v\0");
			Log::info($complete);

			$validateJson = self::validateJson($complete);
			if ($validateJson == "Valid JSON") {
				Log::info('==================Log JSON complete=====================');
				$complete_rst = json_decode($complete, true);
				Log::info($complete_rst);
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
					$validateJson = self::validateJson($arguments);
					if ($validateJson == "Valid JSON") {
						Log::info('==================Log JSON arguments=====================');
						$arguments_rst = json_decode($arguments, true);
						Log::info($arguments_rst);
					}
				}


				return $arguments_rst;
			} else {
				Log::info('==================Log JSON error=====================');
				Log::info($validateJson);
			}
		}

		public static function extractJsonString($input)
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

		public static function mergeStringsWithoutRepetition($string1, $string2, $maxRepetitionLength = 100)
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

		public static function llm_no_tool_call($stream, $llm, $prompt, $return_json = true, $language = 'english')
		{
			set_time_limit(300);
			session_write_close();

			$use_llm = $_ENV['USE_LLM'] ?? 'open-router';


//			if ($llm === 'openai/gpt-4o-mini') {
//				$use_llm = 'open-ai-gpt-4o-mini';
//			}

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

			Log::info('GPT NO TOOL USE: ' . $llm_base_url);
			Log::info($data);

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
//					Log::info('==============');
//					Log::info($data);

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
				Log::info('CURL Error:');
				Log::info(curl_getinfo($ch));
			}
			curl_close($ch);

			if (!$stream) {
//			Log::info('==================Log complete 2 =====================');
				$complete = trim($complete, " \n\r\t\v\0");
//			Log::info($complete);

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
					Log::info("GPT NO STREAM RESPONSE:");
					Log::info($complete_rst);
				} else {
					Log::info("GPT STREAM RESPONSE:");
					Log::info($content);
				}

				Log::info('Return is NOT JSON. Will return content presuming it is text.');
				return $content;
			}

//			$content = str_replace("\\\"", "\"", $content);
			$content = $content ?? '';
			$content = self::getContentsInBackticksOrOriginal($content);

			//check if content is JSON
			$content_json_string = self::extractJsonString($content);
			$validate_result = self::validateJson($content_json_string);

			if ($validate_result !== "Valid JSON") {
				$content_json_string = (new Fixer)->silent(true)->missingValue('"truncated"')->fix($content_json_string);
				$validate_result = self::validateJson($content_json_string);
			}

			if (strlen($content ?? '') < 20) {
				Log::info('================== CONTENT IS EMPTY =====================');
				Log::info($complete);
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
				Log::info('======== SECOND CALL TO FINISH JSON =========');
				Log::info($data);
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
//						Log::info('==============');
//						Log::info($data);

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
					Log::info('CURL Error:');
					Log::info(curl_getinfo($ch));
				}
				curl_close($ch);
//				session_start();

				if (!$stream) {
					$complete2 = trim($complete2, " \n\r\t\v\0");

					Log::info("GPT NO STREAM RESPONSE FOR EXTENDED VERSION JSON CHECK:");
					Log::info($complete2);

					$complete2_rst = json_decode($complete2, true);
					$content2 = $complete2_rst['choices'][0]['message']['content'];

					//$content2 = str_replace("\\\"", "\"", $content2);
					$content2 = self::getContentsInBackticksOrOriginal($content2);

					if (!str_contains($content2, 'DONE')) {
						$content = self::mergeStringsWithoutRepetition($content, $content2, 255);
					}
				} else {
					$content2 = $txt;
					$content2 = trim($content2, " \n\r\t\v\0");

					Log::info("GPT STREAM RESPONSE FOR EXTENDED VERSION JSON CHECK:");
					Log::info($content2);

					//$content2 = str_replace("\\\"", "\"", $content2);
					$content2 = self::getContentsInBackticksOrOriginal($content2);

					if (!str_contains($content2, 'DONE')) {
						$content = self::mergeStringsWithoutRepetition($content, $content2, 255);
					}
				}

				//------------------------------------------------------------

				$content_json_string = self::extractJsonString($content);
				$validate_result = self::validateJson($content_json_string);

				if ($validate_result !== "Valid JSON") {
					$content_json_string = (new Fixer)->silent(true)->missingValue('"truncated"')->fix($content_json_string);
					$validate_result = self::validateJson($content_json_string);
				}

			} else {
				if (!$stream) {
					Log::info("GPT NO STREAM RESPONSE:");
					Log::info($complete_rst);
				} else {
					Log::info("GPT STREAM RESPONSE:");
					Log::info($content);
				}

			}

			if ($validate_result == "Valid JSON") {
				Log::info('================== VALID JSON =====================');
				$content_rst = json_decode($content_json_string, true);
				Log::info($content_rst);
				return $content_rst;
			} else {
				Log::info('================== INVALID JSON =====================');
				Log::info('JSON error : ' . $validate_result . ' -- ');
				Log::info($content);
			}
		}

		//-------------------------------------------------------------------------
		//-------------------------------------------------------------------------
		//-------------------------------------------------------------------------

		public static function getBlogData()
		{
			$locale = \App::getLocale() ?: config('app.fallback_locale', 'zh_TW');

			// the published_at + is_published are handled by BinshopsBlogPublishedScope, and don't take effect if the logged in user can manageb log posts

			//todo
			$title = 'Blog Page'; // default title...
			$category_slug = null;

			$categoryChain = null;
			$posts = array();
			if ($category_slug) {
				$category = BinshopsCategoryTranslation::where("slug", $category_slug)->with('category')->firstOrFail()->category;
				$categoryChain = $category->getAncestorsAndSelf();
				$posts = $category->posts()->where("binshops_post_categories.category_id", $category->id)->get(); //->where("lang_id", '=', 2)->get();

				$posts = BinshopsPostTranslation::join('binshops_posts', 'binshops_post_translations.post_id', '=', 'binshops_posts.id')
//					->where('lang_id', 2)
					->where("is_published", '=', true)
					->where('posted_at', '<', Carbon::now()->format('Y-m-d H:i:s'))
					->orderBy("posted_at", "desc")
					->whereIn('binshops_posts.id', $posts->pluck('id'))
					->paginate(config("binshopsblog.per_page", 10));

				// at the moment we handle this special case (viewing a category) by hard coding in the following two lines.
				// You can easily override this in the view files.
				\View::share('binshopsblog_category', $category); // so the view can say "You are viewing $CATEGORYNAME category posts"
				$title = 'Posts in ' . $category->category_name . " category"; // hardcode title here...
			} else {
				$posts = BinshopsPostTranslation::join('binshops_posts', 'binshops_post_translations.post_id', '=', 'binshops_posts.id')
//					->where('lang_id', 2)
					->where("is_published", '=', true)
					->where('posted_at', '<', Carbon::now()->format('Y-m-d H:i:s'))
					->orderBy("posted_at", "desc")
					->paginate(config("binshopsblog.per_page", 10));

				foreach ($posts as $post) {
					$post->category_name = '';
					//get post categories
					$categories = BinshopsCategory::join('binshops_post_categories', 'binshops_categories.id', '=', 'binshops_post_categories.category_id')
						->where('binshops_post_categories.post_id', $post->id)
						->get();
					//get category translations
					$categories = json_decode(json_encode($categories), true);
					foreach ($categories as $category) {
						if ($post->category_name == '' || $post->category_name == null) {
							$post->category_name = BinshopsCategoryTranslation::where('category_id', $category['category_id'])->first()->category_name ?? '';
						}
					}
				}
			}

			//load category hierarchy
			$rootList = BinshopsCategory::roots()->get();
			BinshopsCategory::loadSiblingsWithList($rootList);

			$blogData = [
				'lang_list' => BinshopsLanguage::all('locale', 'name'),
				'locale' => $locale, // $request->get("locale"),
				'category_chain' => $categoryChain,
				'categories' => $rootList,
				'posts' => $posts,
				'title' => $title,
			];

			return $blogData;

		}

		//-------------------------------------------------------------------------

		public static function openAI_function($messages, $functions, $temperature, $max_tokens, $llm_engine)
		{
			set_time_limit(300);

			$data = array(
				'model' => $llm_engine, // 'gpt-3.5-turbo', 'gpt-4',
				'messages' => $messages,
				'functions' => $functions,
				'function_call' => ['name' => 'get_content'],
				'temperature' => $temperature,
				'max_tokens' => $max_tokens,
				'top_p' => 1,
				'frequency_penalty' => 0,
				'presence_penalty' => 0,
				'n' => 1,
				'stream' => false,
				'stop' => "" //"\n"
			);

//			Log::info('==================openAI_question=====================');
//			Log::info($data);

			session_write_close();
			$txt = '';
			$completion_tokens = 0;

//			Log::info('openAI_question: ');
//			Log::info($data);

			$llm_base_url = env('OPEN_AI_API_BASE');
			$llm_api_key = env('OPEN_AI_API_KEY');

			//dont stream
			$post_json = json_encode($data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $llm_base_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);

			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = "Authorization: Bearer " . $llm_api_key;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$complete = curl_exec($ch);
//			Log::info('==================Log complete=====================');
//			Log::info($complete);
			if (curl_errno($ch)) {
				Log::info('CURL Error:');
				Log::info(curl_getinfo($ch));
			}
			curl_close($ch);
			session_start();

			return array('complete' => $complete);
		}

		//-------------------------------------------------------------------------
		// Send the message to the OpenAI API

		public static function openAI_question($messages, $temperature, $max_tokens, $llm_engine)
		{
			set_time_limit(300);

			$user_id = "1";  //  users id optional

			$disable_validation = true;
			if (!$disable_validation) {
				//parse the $chat_messages array select the last content belonging to the user
				$message = $messages[count($messages) - 1]['content'];
				Log::info('message: ' . $message);

				$mod_result = self::moderation($message);

				$flag_reason = '';

				Log::info('mod_result: ' . json_encode($mod_result));
				if ($mod_result['results'][0]['flagged'] == true ||
					$mod_result['results'][0]['category_scores']['hate'] > 0.4 ||
					$mod_result['results'][0]['category_scores']['sexual'] > 0.6 ||
					$mod_result['results'][0]['category_scores']['violence'] > 0.6 ||
					$mod_result['results'][0]['category_scores']['self-harm'] > 0.6 ||
					$mod_result['results'][0]['category_scores']['sexual/minors'] > 0.6 ||
					$mod_result['results'][0]['category_scores']['hate/threatening'] > 0.4 ||
					$mod_result['results'][0]['category_scores']['violence/graphic'] > 0.6
				) {
					//clear $messages array
//				$messages = [];
					if ($mod_result['results'][0]['category_scores']['hate'] > 0.4) {
						$flag_reason = 'hate';
					}
					if ($mod_result['results'][0]['category_scores']['sexual'] > 0.6) {
						$flag_reason = 'sexual';
					}
					if ($mod_result['results'][0]['category_scores']['violence'] > 0.6) {
						$flag_reason = 'violence';
					}
					if ($mod_result['results'][0]['category_scores']['self-harm'] > 0.6) {
						$flag_reason = 'self-harm';
					}
					if ($mod_result['results'][0]['category_scores']['sexual/minors'] > 0.6) {
						$flag_reason = 'sexual/minors';
					}
					if ($mod_result['results'][0]['category_scores']['hate/threatening'] > 0.4) {
						$flag_reason = 'hate/threatening';
					}
					if ($mod_result['results'][0]['category_scores']['violence/graphic'] > 0.6) {
						$flag_reason = 'violence/graphic';
					}
				}

				if ($flag_reason !== '') {
					Log::info($flag_reason);

					$messages[] = [
						'role' => 'system',
						'content' => 'Tell the user why the message the following request they made is flagged as inappropriate. Tell to Please write a request that doesnt break MySong Cloud guidelines. When telling them why they can\'t write use MySong Cloud instead of OpenAI. Reason for the problem is: ' . $flag_reason
					];
					$messages[] = [
						'role' => 'user',
						'content' => 'why can\'t i write about this topic?'
					];

				}
			}

			$prompt_tokens = 0;
			foreach ($messages as $message) {
				$prompt_tokens += round(str_word_count($message['content']) * 1.25);
			}
			$prompt_tokens = (int)$prompt_tokens;


			if (stripos($llm_engine, 'claude') !== false) {
				$llm_base_url = env('ANTHROPIC_SONET_BASE');
				$llm_api_key = env('ANTHROPIC_SONET_KEY');

				$system_message = '';
				foreach ($messages as $key => &$message) {
					if ($message['role'] === 'system') {
						$system_message = $message['content'];
						unset($messages[$key]);
					}
				}
				$messages = array_values($messages);


				$data = array(
					'model' => $llm_engine, // 'claude-3-opus-20240229', ...
					'messages' => $messages,
					'temperature' => $temperature,
					'max_tokens' => $max_tokens,
					'system' => $system_message,
					'stream' => true,
				);
			} else {
				$llm_base_url = env('OPEN_AI_GPT4_BASE');
				$llm_api_key = env('OPEN_AI_GPT4_KEY');

				$data = array(
					'model' => $llm_engine, // 'gpt-3.5-turbo', 'gpt-4',
					'messages' => $messages,
					'temperature' => $temperature,
					'max_tokens' => $max_tokens,
					'top_p' => 1,
					'frequency_penalty' => 0,
					'presence_penalty' => 0,
					'n' => 1,
					'stream' => true,
					'stop' => "" //"\n"
				);

			}

			session_write_close();
			$txt = '';
			$completion_tokens = 0;

			Log::info('LLM QUESTION: ');
			Log::info($data);

			$post_json = json_encode($data);
//			Log::info('openAI_question post_json: ');
//			Log::info($post_json);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $llm_base_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);

			$headers = array();
			if (stripos($llm_engine, 'claude') !== false) {
				$headers[] = 'Content-Type: application/json';
				$headers[] = "x-api-key: " . $llm_api_key;
				$headers[] = "anthropic-version: 2023-06-01";
				$headers[] = "anthropic-beta: messages-2023-12-15";

			} else
			{
				$headers[] = 'Content-Type: application/json';
				$headers[] = "Authorization: Bearer " . $llm_api_key;
			}

			Log::info('LLM headers: '.$llm_base_url);

			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$accumulatedData = '';

			curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl_info, $data) use (&$txt, &$completion_tokens, &$accumulatedData) {



				$data_lines = explode("\n", $data);
				for ($i = 0; $i < count($data_lines); $i++) {
					$data_line = $data_lines[$i];
//					Log::info($data_line);

					if (stripos( $data_line, 'event:') !== false) {
						continue;
					}

					// Check if the data line contains [DONE]
					if (stripos($data_line, "[DONE]") !== false || stripos($data_line, "message_stop") !== false) {
						Log::info('OpenAI [DONE]');
//						echo "data: [DONE]\n\n";
//						ob_flush();
//						flush();
						return strlen($data_line);
					}

					$completion_tokens++;

					// Append new data to the accumulated data
					if (stripos($data_line, "data:") === false) {
						$accumulatedData .= $data_line;
					} else {
						$accumulatedData = $data_line;
					}

					// Check if we have a complete JSON object
					$clean = str_replace("data: ", "", $accumulatedData);
					$decoded = json_decode($clean, true);

					if ($decoded && isset($decoded["delta"])) {
//						echo $accumulatedData . "\n";
//						echo PHP_EOL;
//						ob_flush();
//						flush();

						$txt .= $decoded["delta"]["text"] ?? '';
						$accumulatedData = ''; // Reset accumulated data
					}

					if ($decoded && isset($decoded["choices"])) {
//						echo $accumulatedData . "\n";
//						echo PHP_EOL;
//						ob_flush();
//						flush();

						$txt .= $decoded["choices"][0]["delta"]["content"] ?? '';
						$accumulatedData = ''; // Reset accumulated data
					}
				}

				return strlen($data);
			});

			$result = curl_exec($ch);

			Log::info('curl result:');
			Log::info($result);

			curl_close($ch);

			return array('message_text' => $txt, 'completion_tokens' => $completion_tokens, 'prompt_tokens' => $prompt_tokens);
		}

		//-------------------------------------------------------------------------

		public static function openAI_no_stream($messages, $temperature, $max_tokens, $llm_engine)
		{
			set_time_limit(300);

			$disable_validation = true;
			if (!$disable_validation) {
				//parse the $chat_messages array select the last content belonging to the user
				$message = $messages[count($messages) - 1]['content'];
				Log::info('openAI_no_stream message: ' . $message);

				$mod_result = self::moderation($message);

				$flag_reason = '';

				Log::info('mod_result: ' . json_encode($mod_result));
				if ($mod_result['results'][0]['flagged'] == true ||
					$mod_result['results'][0]['category_scores']['hate'] > 0.4 ||
					$mod_result['results'][0]['category_scores']['sexual'] > 0.6 ||
					$mod_result['results'][0]['category_scores']['violence'] > 0.6 ||
					$mod_result['results'][0]['category_scores']['self-harm'] > 0.6 ||
					$mod_result['results'][0]['category_scores']['sexual/minors'] > 0.6 ||
					$mod_result['results'][0]['category_scores']['hate/threatening'] > 0.4 ||
					$mod_result['results'][0]['category_scores']['violence/graphic'] > 0.6
				) {
					//clear $messages array
//				$messages = [];
					if ($mod_result['results'][0]['category_scores']['hate'] > 0.4) {
						$flag_reason = 'hate';
					}
					if ($mod_result['results'][0]['category_scores']['sexual'] > 0.6) {
						$flag_reason = 'sexual';
					}
					if ($mod_result['results'][0]['category_scores']['violence'] > 0.6) {
						$flag_reason = 'violence';
					}
					if ($mod_result['results'][0]['category_scores']['self-harm'] > 0.6) {
						$flag_reason = 'self-harm';
					}
					if ($mod_result['results'][0]['category_scores']['sexual/minors'] > 0.6) {
						$flag_reason = 'sexual/minors';
					}
					if ($mod_result['results'][0]['category_scores']['hate/threatening'] > 0.4) {
						$flag_reason = 'hate/threatening';
					}
					if ($mod_result['results'][0]['category_scores']['violence/graphic'] > 0.6) {
						$flag_reason = 'violence/graphic';
					}
				}

				if ($flag_reason !== '') {
					Log::info($flag_reason);

					$messages[] = [
						'role' => 'system',
						'content' => 'Tell the user why the message the following request they made is flagged as inappropriate. Tell to Please write a request that doesnt break MySong Cloud guidelines. When telling them why they can\'t write use MySong Cloud instead of OpenAI. Reason for the problem is: ' . $flag_reason
					];
					$messages[] = [
						'role' => 'user',
						'content' => 'why can\'t i write about this topic?'
					];

				}
			}

			$prompt_tokens = 0;
			foreach ($messages as $message) {
				$prompt_tokens += round(str_word_count($message['content']) * 1.25);
			}
			$prompt_tokens = (int)$prompt_tokens;

			$data = array(
				'model' => $llm_engine, // 'gpt-3.5-turbo', 'gpt-4',
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

			Log::info('openAI_no_stream: ');
			Log::info($data);

			$llm_base_url = env('OPEN_AI_API_BASE');
			$llm_api_key = env('OPEN_AI_API_KEY');

			$post_json = json_encode($data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $llm_base_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);

			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = "Authorization: Bearer " . $llm_api_key;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = curl_exec($ch);
			Log::info('curl result:');
			Log::info($result);
			curl_close($ch);

			return array('message_text' => $result, 'message_json' => json_decode($result, true));
		}

		//-------------------------------------------------------------------------

	}
