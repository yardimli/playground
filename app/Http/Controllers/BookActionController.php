<?php

	namespace App\Http\Controllers;

	use GuzzleHttp\Client;
	use GuzzleHttp\Exception\ClientException;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\File;
	use App\Helpers\MyHelper;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;

	class BookActionController extends Controller
	{

		public static function verifyBookOwnership($bookSlug)
		{
			if (Auth::guest()) {
				return ['success' => false, 'message' => __('You must be logged in to verify book ownership.')];
			}

			if ($bookSlug !== '' && $bookSlug !== null) {
				$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
				$bookJsonPath = "{$bookPath}/book.json";
				$actsJsonPath = "{$bookPath}/acts.json";

				if (!File::exists($bookJsonPath)) {
					return ['success' => false, 'message' => __('Book not found')];
				}

				if (!File::exists($actsJsonPath)) {
					return ['success' => false, 'message' => __('Acts not found')];
				}

				$bookData = json_decode(File::get($bookJsonPath), true);

				if (Auth::user()) {
					if ($bookData['owner'] !== Auth::user()->email && !Auth::user()->isAdmin()) {
						return ['success' => false, 'message' => __('You are not the owner of this book.')];
					} else {
						return ['success' => true, 'message' => __('You are the owner of this book.')];
					}
				} else {
					return ['success' => false, 'message' => __('You are not the owner of this book.')];
				}
			} else {
				return ['success' => true, 'message' => __('Book not found')];
			}
		}

		public function writeBookCharacterProfiles(Request $request)
		{
			$verified = self::verifyBookOwnership('');
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$language = $request->input('language', __('Default Language'));
			$userBlurb = $request->input('user_blurb', '');
			$bookStructure = $request->input('bookStructure', 'fichtean_curve.txt');
			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
				$schema = json_decode(File::get(resource_path('prompts/book_schema_anthropic_1.json')), true);
				$prompt = File::get(resource_path('prompts/book_prompt_anthropic_1.txt'));
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
				$schema = json_decode(File::get(resource_path('prompts/book_schema_openai_1.json')), true);
				$prompt = File::get(resource_path('prompts/book_prompt_openai_1.txt'));
			} else {
				$model = $llm;
				$prompt = File::get(resource_path('prompts/book_prompt_no_function_calling_1.txt'));
				$schema = [];
			}

			$prompt = str_replace(['##user_blurb##', '##language##'], [$userBlurb, $language], $prompt);

			$results = $schema === []
				? MyHelper::llm_no_tool_call(false, $llm, $prompt, true, $language)
				: MyHelper::function_call($prompt, $schema, $language);

			if (!empty($results['title']) && !empty($results['blurb']) && !empty($results['back_cover_text']) && !empty($results['character_profiles'])) {
				return response()->json(['success' => true, 'message' => __('Book created successfully'), 'data' => $results]);
			} else {
				return response()->json(['success' => false, 'message' => __('Failed to generate book')]);
			}
		}

		public function writeBook(Request $request)
		{
			$verified = self::verifyBookOwnership('');
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$language = $request->input('language', __('Default Language'));
			$bookStructure = $request->input('bookStructure', __('Default Structure'));
			$userBlurb = $request->input('user_blurb', '');
			$bookTitle = $request->input('book_title', '');
			$bookBlurb = $request->input('book_blurb', '');
			$backCoverText = $request->input('back_cover_text', '');
			$characterProfiles = $request->input('character_profiles', '');

			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
				$schema = json_decode(File::get(resource_path('prompts/book_schema_anthropic_1.json')), true);
				$prompt = File::get(resource_path('prompts/book_prompt_anthropic_1.txt'));
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
				$schema = json_decode(File::get(resource_path('prompts/book_schema_openai_1.json')), true);
				$prompt = File::get(resource_path('prompts/book_prompt_openai_1.txt'));
			} else {
				$model = $llm;
				$prompt = File::get(resource_path("prompts/{$bookStructure}"));
				$schema = [];
			}

			$replacements = [
				'##user_blurb##' => $userBlurb,
				'##language##' => $language,
				'##book_title##' => $bookTitle,
				'##book_blurb##' => $bookBlurb,
				'##back_cover_text##' => $backCoverText,
				'##character_profiles##' => $characterProfiles,
			];

			$prompt = str_replace(array_keys($replacements), array_values($replacements), $prompt);

			$results = $schema === []
				? MyHelper::llm_no_tool_call(false, $llm, $prompt, true, $language)
				: MyHelper::function_call($prompt, $schema, $language);

			if (!empty($results['acts'])) {
				$bookHeaderData = [
					'title' => $bookTitle,
					'back_cover_text' => $backCoverText,
					'blurb' => $bookBlurb,
					'character_profiles' => $characterProfiles,
					'prompt' => $userBlurb,
					'language' => $language,
					'model' => $model,
				];

				$bookSlug = Str::slug($bookTitle) . '-' . Str::random(8);
				$bookPath = Storage::disk('public')->path("books/{$bookSlug}");

				if (!File::isDirectory($bookPath)) {
					File::makeDirectory($bookPath, 0755, true);
				}

				// Save book.json
				$bookData = array_merge($bookHeaderData, [
					'owner' => Auth::user()->email,
					'folder' => $bookSlug,
					'created_at' => now()->toDateTimeString(),
					'updated_at' => now()->toDateTimeString(),
				]);
				File::put("{$bookPath}/book.json", json_encode($bookData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

				// Save acts.json
				$acts = collect($results['acts'])->map(function ($act, $index) {
					return [
						'id' => $index + 1,
						'title' => $act['name'],
						'description' => $act['description'] ?? '',
					];
				})->toArray();
				File::put("{$bookPath}/acts.json", json_encode($acts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

				// Save individual chapters
				foreach ($results['acts'] as $actIndex => $act) {
					foreach ($act['chapters'] as $chapterIndex => $chapter) {
						$chapterFilename = Str::slug($chapter['name']) . '_' . time() . '.json';
						$chapter_name = $chapter['name'] ?? 'chapter ' . ($chapter_index + 1);
						$chapterData = [
							'row' => $actIndex + 1,
							'order' => $chapterIndex + 1,
							'name' => $chapter_name,
							'short_description' => $chapter['short_description'] ?? 'no description',
							'events' => $chapter['events'] ?? 'no events',
							'people' => $chapter['people'] ?? 'no people',
							'places' => $chapter['places'] ?? 'no places',
							'from_previous_chapter' => $chapter['from_previous_chapter'] ?? 'N/A',
							'to_next_chapter' => $chapter['to_next_chapter'] ?? 'N/A',
							'backgroundColor' => '#AECBFA',
							'textColor' => '#000000',
							'created' => now()->toDateTimeString(),
							'lastUpdated' => now()->toDateTimeString(),
						];
						File::put("{$bookPath}/{$chapterFilename}", json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
					}
				}

				// Call makeCoverImage
				$coverImageRequest = new Request([
					'theme' => __('An image describing: ') . $bookBlurb,
					'title_1' => $bookTitle,
					'author_1' => Auth::user()->name,
					'creative' => 'more'
				]);

				Log::info('coverImageRequest');
				Log::info($coverImageRequest);
				$coverImageResponse = $this->makeCoverImage($coverImageRequest, $bookSlug);
				Log::info('coverImageResponse');
				Log::info($coverImageResponse);
				$coverImageData = json_decode($coverImageResponse, true);
				Log::info('coverImageData');
				Log::info($coverImageData);

				if ($coverImageData['success']) {
					// Update book.json with cover image information
					$bookData['cover_filename'] = $coverImageData['output_filename'];

					File::put("{$bookPath}/book.json", json_encode($bookData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
				}

				return response()->json([
					'success' => true,
					'message' => __('Book created successfully'),
					'data' => $results,
					'bookSlug' => $bookSlug,
					'coverImage' => $coverImageData['success'] ? $coverImageData : null
				]);
			} else {
				return response()->json(['success' => false, 'message' => __('Failed to generate book')]);
			}
		}

		public function saveChapter(Request $request, $bookSlug)
		{
			$verified = self::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");

			$chapterFilename = $request->input('chapterFilename');
			$newChapter = empty($chapterFilename);

			if ($newChapter) {
				$chapterFilename = Str::slug($request->input('name')) . '_' . time() . '.json';
			}

			$chapterPath = "{$bookPath}/{$chapterFilename}";

			$chapterData = [
				'row' => $newChapter ? 'to-do' : (File::exists($chapterPath) ? json_decode(File::get($chapterPath), true)['row'] : 'to-do'),
				'order' => $request->input('order', 0),
				'name' => $request->input('name'),
				'short_description' => $request->input('short_description'),
				'events' => $request->input('events'),
				'people' => $request->input('people'),
				'places' => $request->input('places'),
				'from_previous_chapter' => $request->input('from_previous_chapter'),
				'to_next_chapter' => $request->input('to_next_chapter'),
				'backgroundColor' => $request->input('backgroundColor'),
				'textColor' => $request->input('textColor'),
				'created' => $newChapter ? now()->toDateTimeString() : (File::exists($chapterPath) ? json_decode(File::get($chapterPath), true)['created'] : now()->toDateTimeString()),
				'lastUpdated' => now()->toDateTimeString(),
			];

			File::put($chapterPath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

			$chapterData['chapterFilename'] = $chapterFilename;
			$chapterData['success'] = true;

			return response()->json($chapterData);
		}

		public function saveCover(Request $request, $bookSlug)
		{
			$verified = self::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$bookData = json_decode(File::get($bookJsonPath), true);

			$coverFilename = $request->input('cover_filename');
			$bookData['cover_filename'] = $coverFilename;

			File::put($bookJsonPath, json_encode($bookData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

			return response()->json(['success' => true, 'message' => __('Cover saved successfully')]);
		}

		public function deleteBook($bookSlug)
		{
			$verified = self::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}
			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");

			File::deleteDirectory($bookPath);

			return response()->json(['success' => true, 'message' => __('Book deleted successfully')]);
		}

		public function makeCoverImage(Request $request, $bookSlug)
		{
			$verified = self::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$theme = $request->input('theme', 'an ocean in space');
			$title1 = $request->input('title_1', 'Playground Computer');
			$author1 = $request->input('author_1', 'I, Robot');
			$model = 'fast'; //$request->input('model', 'fast');
			$creative = $request->input('creative', 'no');

			if (Auth::user() && Auth::user()->isAdmin()) {
//				$model = 'balanced';
			}

			$prompt = $theme . ' the book covers title is "' . $title1 . '" and the author is "' . $author1 . '" include the text lines on the cover.';

			if ($creative === 'more') {

				$gpt_prompt = "Write a prompt for an book cover
The image in the background is :
 " . $theme . "
 the book title is " . $title1 . "
 the author is " . $author1 . "
Write in English even if the background is written in another language.
With the above information, compose a book cover. Write it as a single paragraph. The instructions should focus on the text elements of the book cover. Generally the title should be on top part and the artist on the bottom of the image.

Prompt:";

				$single_request = [
					[
						"role" => "system",
						"content" => "You write prompts for making book covers. Follow the format of the examples."
					],
					[
						"role" => "user",
						"content" => $gpt_prompt
					]
				];

				$prompt = MyHelper::openAI_question($single_request, 1, 256, 'gpt-4o');
				$gpt_results = $prompt;
			}
			Log::info('prompt');
			Log::info($prompt);

			if (!Storage::disk('public')->exists('ai-images')) {
				Storage::disk('public')->makeDirectory('ai-images');
			}

			$filename = Str::uuid() . '.jpg';
			$outputFile = Storage::disk('public')->path('ai-images/' . $filename);

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
					'prompt' => $prompt['message_text'],
					'image_size' => 'portrait_4_3',
					'safety_tolerance' => '5',
				]
			]);
			Log::info('response');
			Log::info($response->getBody());

			$body = $response->getBody();
			$data = json_decode($body, true);

//add to render log file
			$renderLog = [
				'prompt' => $prompt,
				'image_size' => 'portrait_4_3',
				'safety_tolerance' => '5',
				'output_file' => $filename,
				'status_code' => $response->getStatusCode(),
				'body' => $body,
				'data' => $data
			];

			Log::info('renderLog');
			Log::info($renderLog);

			if ($response->getStatusCode() == 200) {


				if (isset($data['images'][0]['url'])) {
					$image_url = $data['images'][0]['url'];

					//save image_url to folder both .png and _1024.png
					$image = file_get_contents($image_url);
					file_put_contents($outputFile, $image);

					return json_encode(['success' => true, 'message' => __('Image generated successfully'), 'output_filename' => $filename, 'output_path' => $outputFile, 'data' => json_encode($data), 'seed' => $data['seed'], 'status_code' => $response->getStatusCode()]);
				} else {
					return json_encode(['success' => false, 'message' => __('Error (2) generating image'), 'status_code' => $response->getStatusCode()]);
				}
			} else {
				return json_encode(['success' => false, 'message' => __('Error (1) generating image'), 'status_code' => $response->getStatusCode()]);
			}

		}

		public function writeChapterBeats(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = self::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsJsonPath = "{$bookPath}/acts.json";

			$bookData = json_decode(File::get($bookJsonPath), true);
			$actsData = json_decode(File::get($actsJsonPath), true);

			$acts = [];
			foreach ($actsData as $act) {
				$actChapters = [];
				$chapterFiles = File::glob("{$bookPath}/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(File::get($chapterFile), true);
					if (!isset($chapterData['row'])) {
						continue;
					}
					$chapterData['chapterFilename'] = basename($chapterFile);

					if ($chapterData['row'] === $act['id']) {
						$actChapters[] = $chapterData;

					}
				}

				usort($actChapters, fn($a, $b) => $a['order'] - $b['order']);
				$acts[] = [
					'id' => $act['id'],
					'title' => $act['title'],
					'chapters' => $actChapters
				];
			}

			$current_chapter = null;
			$previous_chapter = null;
			$next_chapter = null;
			foreach ($acts as $act) {
				foreach ($act['chapters'] as $chapter) {
					if ($current_chapter && !$next_chapter) {
						$next_chapter = $chapter;
						break;
					}

					if ($chapter['chapterFilename'] === $chapterFilename) {
						$current_chapter = $chapter;
					}

					if (!$current_chapter) {
						$previous_chapter = $chapter;
					}

				}
			}

			$previous_chapter_beats = $current_chapter['from_previous_chapter'];
			if ($previous_chapter) {
				if ($previous_chapter['beats']) {
					$previous_chapter_beats = '';
					foreach ($previous_chapter['beats'] as $beat) {
						if (key_exists('beat_summary', $beat) && $beat['beat_summary'] !== '') {
							$previous_chapter_beats .= $beat['beat_summary'] . "\n";
						} else {
							$previous_chapter_beats .= ($beat['description'] ?? '') . "\n";
						}
					}
				}
			}

			$save_results = ($request->input('save_results', 'true') === 'true');

			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
				$schema = json_decode(File::get(resource_path('prompts/beat_schema_anthropic_1.json')), true);
				$prompt = File::get(resource_path('prompts/beat_prompt_anthropic_1.txt'));
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
				$schema = json_decode(File::get(resource_path('prompts/beat_schema_openai_1.json')), true);
				$prompt = File::get(resource_path('prompts/beat_prompt_openai_1.txt'));
			} else {
				$model = $llm;
				$prompt = File::get(resource_path('prompts/beat_prompt_no_function_calling_1.txt'));
				$schema = [];
			}

			$replacements = [
				'##book_title##' => $bookData['title'] ?? 'no title',
				'##back_cover_text##' => $bookData['back_cover_text'] ?? 'no back cover text',
				'##book_blurb##' => $bookData['blurb'] ?? 'no blurb',
				'##language##' => $bookData['language'] ?? 'English',
				'##act##' => $current_chapter['row'] ?? 'no act',
				'##chapter##' => $current_chapter['name'] ?? 'no name',
				'##description##' => $current_chapter['short_description'] ?? 'no description',
				'##events##' => $current_chapter['events'] ?? 'no events',
				'##people##' => $current_chapter['people'] ?? 'no people',
				'##places##' => $current_chapter['places'] ?? 'no places',
				'##previous_chapter##' => $previous_chapter_beats ?? 'Beginning of the book',
				'##next_chapter##' => $current_chapter['to_next_chapter'] ?? 'No more chapters',
			];

			$prompt = str_replace(array_keys($replacements), array_values($replacements), $prompt);

			$resultData = $schema === []
				? MyHelper::llm_no_tool_call(false, $llm, $prompt, true)
				: MyHelper::function_call($llm, $prompt, $schema);

			$beats = null;
			if (isset($resultData['beats'])) {
				$beats = $resultData['beats'];
			} elseif (is_array($resultData)) {
				$beats = $resultData;
			}

			if ($beats) {
				$chapterFilePath = "{$bookPath}/{$current_chapter['chapterFilename']}";

				if ($save_results) {
					if (file_exists($chapterFilePath)) {
						$chapterData = json_decode(file_get_contents($chapterFilePath), true);
						$chapterData['beats'] = $beats;

						if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
							return response()->json(['success' => true, 'message' => 'Wrote beats to file.', 'beats' => $beats]);
						} else {
							return response()->json(['success' => false, 'message' => __('Failed to write to file')]);
						}
					} else {
						return response()->json(['success' => false, 'message' => __('Chapter file not found')]);
					}
				}
			} else {
				return response()->json(['success' => false, 'message' => __('Failed to generate beats')]);
			}

			return response()->json(['success' => true, 'message' => 'Generated beats.', 'beats' => $beats]);
		}


		public function writeChapterBeatText(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = self::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsJsonPath = "{$bookPath}/acts.json";

			$bookData = json_decode(File::get($bookJsonPath), true);
			$actsData = json_decode(File::get($actsJsonPath), true);

			$acts = [];
			foreach ($actsData as $act) {
				$actChapters = [];
				$chapterFiles = File::glob("{$bookPath}/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(File::get($chapterFile), true);
					if (!isset($chapterData['row'])) {
						continue;
					}
					$chapterData['chapterFilename'] = basename($chapterFile);

					if ($chapterData['row'] === $act['id']) {
						$actChapters[] = $chapterData;

					}
				}

				usort($actChapters, fn($a, $b) => $a['order'] - $b['order']);
				$acts[] = [
					'id' => $act['id'],
					'title' => $act['title'],
					'chapters' => $actChapters
				];
			}


			$current_chapter = null;
			$previous_chapter = null;
			$next_chapter = null;
			foreach ($acts as $act) {
				foreach ($act['chapters'] as $chapter) {
					if ($current_chapter && !$next_chapter) {
						$next_chapter = $chapter;
						break;
					}

					if ($chapter['chapterFilename'] === $chapterFilename) {
						$current_chapter = $chapter;
					}

					if (!$current_chapter) {
						$previous_chapter = $chapter;
					}
				}
			}

			$save_results = ($request->input('save_results', 'true') === 'true');
			$beatIndex = (int)$request->input('beatIndex', 0);
			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');
			$current_beat = $request->input('current_beat', '');


			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}

			$previous_beat_summaries = '';
			$last_beat_full_text = '';
			$next_beat = '';

			// Get the current beat
			if ($current_beat==='') {
				if (isset($current_chapter['beats'][$beatIndex])) {
					$current_beat = $current_chapter['beats'][$beatIndex]['beat_text'] ?? '';
				}
			}

			// Process previous beats and last beat
			if ($beatIndex > 0) {
				for ($i = 0; $i < $beatIndex; $i++) {
					if ($i === $beatIndex - 1) {
						$last_beat_full_text = $current_chapter['beats'][$i]['beat_text'] ?? '';
					} else {
						$current_beat_summary = $current_chapter['beats'][$i]['beat_summary'] ?? $current_chapter['beats'][$i]['description'] ?? '';
						$previous_beat_summaries .= $current_beat_summary . "\n";
					}
				}
			} else {
				// If it's the first beat of the chapter, look at the previous chapter
				if ($previous_chapter !== null && isset($previous_chapter['beats'])) {
					$prev_chapter_beats = $previous_chapter['beats'];
					$prev_beats_count = count($prev_chapter_beats);

					for ($i = 0; $i < $prev_beats_count; $i++) {
						if ($i === $prev_beats_count - 1) {
							$last_beat_full_text = $prev_chapter_beats[$i]['beat_text'] ?? '';
						} else {
							$current_beat_summary = $prev_chapter_beats[$i]['beat_summary'] ?? $prev_chapter_beats[$i]['description'] ?? '';
							$previous_beat_summaries .= $current_beat_summary . "\n";
						}
					}
				}
			}

			// Process next beat
			if (isset($current_chapter['beats'][$beatIndex + 1])) {
				$next_beat = $current_chapter['beats'][$beatIndex + 1]['description'] ?? '';
			} else {
				// If it's the last beat of the chapter, look at the next chapter
				if ($next_chapter !== null && isset($next_chapter['beats'][0])) {
					$next_beat = $next_chapter['beats'][0]['description'] ?? '';
				}
			}

			// Trim any trailing newlines from previous_beat_summaries
			$previous_beat_summaries = rtrim($previous_beat_summaries);


			// Load the beat prompt template
			$beatPromptTemplate = File::get(resource_path('prompts/beat_text_prompt.txt'));

			$replacements = [
				'##book_title##' => $bookData['title'] ?? 'no title',
				'##back_cover_text##' => $bookData['back_cover_text'] ?? 'no back cover text',
				'##book_blurb##' => $bookData['blurb'] ?? 'no blurb',
				'##language##' => $bookData['language'] ?? 'English',
				'##act##' => $current_chapter['row'] ?? 'no act',
				'##chapter##' => $current_chapter['name'] ?? 'no name',
				'##description##' => $current_chapter['short_description'] ?? 'no description',
				'##events##' => $current_chapter['events'] ?? 'no events',
				'##people##' => $current_chapter['people'] ?? 'no people',
				'##places##' => $current_chapter['places'] ?? 'no places',
				'##previous_chapter##' => $previous_chapter_beats ?? 'Beginning of the book',
				'##next_chapter##' => $current_chapter['to_next_chapter'] ?? 'No more chapters',
				'##previous_beat_summaries##' => $previous_beat_summaries,
				'##last_beat_full_text##' => $last_beat_full_text,
				'##current_beat##' => $current_beat,
				'##next_beat##' => $next_beat,
			];

			$beatPrompt = str_replace(array_keys($replacements), array_values($replacements), $beatPromptTemplate);

			$resultData = MyHelper::llm_no_tool_call(false, $llm, $beatPrompt, false);

			if ($save_results) {
				$chapterFilePath = "{$bookPath}/{$chapterFilename}";

				if (file_exists($chapterFilePath)) {
					$chapterData = json_decode(file_get_contents($chapterFilePath), true);
					$chapterData['beats'][$beatIndex]['beat_text'] = $resultData;

					if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
						return response()->json(['success' => true, 'message' => 'Wrote beat text to file.', 'prompt' => $resultData]);
					} else {
						return response()->json(['success' => false, 'message' => __('Failed to write to file')]);
					}
				} else {
					return response()->json(['success' => false, 'message' => __('Chapter file not found')]);
				}
			}

			echo json_encode(['success' => true, 'prompt' => $resultData]);
		}

		public function writeChapterBeatSummary(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = self::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");

			$currentBeatDescription = $request->input('currentBeatDescription') ?? '';
			$currentBeatText = $request->input('currentBeatText') ?? '';
			$beatIndex = $request->input('beatIndex', 0);
			$save_results = ($request->input('save_results', 'true') === 'true');

			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}

			// Load the beat prompt template
			$beatPromptTemplate = File::get(resource_path('prompts/beat_summary.txt'));

			$replacements = [
				'##book_title##' => $bookData['title'] ?? 'no title',
				'##back_cover_text##' => $bookData['back_cover_text'] ?? 'no back cover text',
				'##book_blurb##' => $bookData['blurb'] ?? 'no blurb',
				'##language##' => $bookData['language'] ?? 'English',
				'##act##' => $current_chapter['row'] ?? 'no act',
				'##chapter##' => $current_chapter['name'] ?? 'no name',
				'##description##' => $current_chapter['short_description'] ?? 'no description',
				'##events##' => $current_chapter['events'] ?? 'no events',
				'##people##' => $current_chapter['people'] ?? 'no people',
				'##places##' => $current_chapter['places'] ?? 'no places',
				'##beat_summary##' => $currentBeatDescription ?? '',
				'##beat_text##' => $currentBeatText ?? '',
			];

			$beatPrompt = str_replace(array_keys($replacements), array_values($replacements), $beatPromptTemplate);

			$resultData = MyHelper::llm_no_tool_call(false, $llm, $beatPrompt, false);

			if ($save_results) {
				$chapterFilePath = "{$bookPath}/{$chapterFilename}";

				if (file_exists($chapterFilePath)) {
					$chapterData = json_decode(file_get_contents($chapterFilePath), true);
					$chapterData['beats'][$beatIndex]['beat_summary'] = $resultData;

					if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
						return response()->json(['success' => true, 'message' => 'Wrote beat summary to file.', 'prompt' => $resultData]);
					} else {
						return response()->json(['success' => false, 'message' => __('Failed to write to file')]);
					}
				} else {
					return response()->json(['success' => false, 'message' => __('Chapter file not found')]);
				}
			}

			echo json_encode(['success' => true, 'prompt' => $resultData]);
		}

		public function saveChapterBeats(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = self::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$chapterFilePath = "{$bookPath}/{$chapterFilename}";

			if (!File::exists($chapterFilePath)) {
				return response()->json(['success' => false, 'message' => __('Chapter file not found')], 404);
			}

			$posted_beats = $request->input('beats');
			$posted_beats = json_decode($posted_beats, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				return response()->json(['success' => false, 'message' => 'Invalid JSON', 'error' => json_last_error_msg(), 'beats' => $posted_beats]);
			}

			//check if beats are empty
			if (empty($posted_beats)) {
				return response()->json(['success' => false, 'message' => 'Beats are empty.', 'beats' => $posted_beats]);
			}

			foreach ($posted_beats as $beat) {
				if (!isset($beat['beat_text'])) {
					$beat['beat_text'] = '';
				}
				if (!isset($beat['beat_summary'])) {
					$beat['beat_summary'] = '';
				}
			}

			$chapterData = json_decode(File::get($chapterFilePath), true);
			$chapterData['beats'] = $posted_beats;

			if (File::put($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
				return response()->json(['success' => true, 'message' => 'Beats saved.']);
			} else {
				return response()->json(['success' => false, 'message' => __('Failed to save beats')]);
			}
		}

		public function saveChapterSingleBeat(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = self::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");

			$chapterFilePath = "{$bookPath}/{$chapterFilename}";
			if (!File::exists($chapterFilePath)) {
				return response()->json(['success' => false, 'message' => __('Chapter file not found')], 404);
			}

			$chapterData = json_decode(File::get($chapterFilePath), true);

			$beatIndex = (int)$request->input('beatIndex', 0);
			$beatDescription = $request->input('beatDescription', '');
			$beatText = $request->input('beatText', '');
			$beatSummary = $request->input('beatSummary', '');

			$chapterData['beats'][$beatIndex]['description'] = $beatDescription;
			$chapterData['beats'][$beatIndex]['beat_text'] = $beatText;
			$chapterData['beats'][$beatIndex]['beat_summary'] = $beatSummary;

			if (File::put($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
				return response()->json(['success' => true, 'message' => 'Beats saved.']);
			} else {
				return response()->json(['success' => false, 'message' => __('Failed to save beats')]);
			}
		}

	}
