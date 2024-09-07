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

		public function writeBookCharacterProfiles(Request $request)
		{
			if (Auth::guest()) {
				return response()->json(['success' => false, 'message' => __('You must be logged in to create a book.')]);
			}

			$language = $request->input('language', __('Default Language'));
			$userBlurb = $request->input('user_blurb', '');
			$bookStructure = $request->input('bookStructure', 'fichtean_curve.txt');
			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			$useLlm = env('USE_LLM', 'open-router');

			if ($useLlm === 'anthropic-haiku' || $useLlm === 'anthropic-sonet') {
				$model = $useLlm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
				$schema = json_decode(File::get(resource_path('prompts/book_schema_anthropic_1.json')), true);
				$prompt = File::get(resource_path('prompts/book_prompt_anthropic_1.txt'));
			} elseif ($useLlm === 'open-ai-gpt-4o' || $useLlm === 'open-ai-gpt-4o-mini') {
				$model = $useLlm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL', 'open-router') : env('OPEN_AI_GPT4_MINI_MODEL', 'open-router');
				$schema = json_decode(File::get(resource_path('prompts/book_schema_openai_1.json')), true);
				$prompt = File::get(resource_path('prompts/book_prompt_openai_1.txt'));
			} else {
				$model = $llm;
				$prompt = File::get(resource_path('prompts/book_prompt_no_function_calling_1.txt'));
				$schema = [];
			}

			$prompt = str_replace(['##user_blurb##', '##language##'], [$userBlurb, $language], $prompt);

			$results = $useLlm === 'open-router'
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
			if (Auth::guest()) {
				return response()->json(['success' => false, 'message' => __('You must be logged in to create a book.')]);
			}

			$language = $request->input('language', __('Default Language'));
			$bookStructure = $request->input('bookStructure', __('Default Structure'));
			$userBlurb = $request->input('user_blurb', '');
			$bookTitle = $request->input('book_title', '');
			$bookBlurb = $request->input('book_blurb', '');
			$backCoverText = $request->input('back_cover_text', '');
			$characterProfiles = $request->input('character_profiles', '');

			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			$useLlm = env('USE_LLM', 'open-router');

			if ($useLlm === 'anthropic-haiku' || $useLlm === 'anthropic-sonet') {
				$model = $useLlm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
				$schema = json_decode(File::get(resource_path('prompts/book_schema_anthropic_1.json')), true);
				$prompt = File::get(resource_path('prompts/book_prompt_anthropic_1.txt'));
			} elseif ($useLlm === 'open-ai-gpt-4o' || $useLlm === 'open-ai-gpt-4o-mini') {
				$model = $useLlm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL', 'open-router') : env('OPEN_AI_GPT4_MINI_MODEL', 'open-router');
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

			$results = $useLlm === 'open-router'
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
			if (Auth::guest()) {
				return response()->json(['success' => false, 'message' => __('You must be logged in to create a book.')]);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";

			if (!File::exists($bookJsonPath)) {
				return response()->json(['success' => false, 'message' => __('Book not found'), 'file' => $bookJsonPath], 404);
			}

			$bookData = json_decode(File::get($bookJsonPath), true);

			if (Auth::user()) {
				if ($bookData['owner'] !== Auth::user()->email && !Auth::user()->isAdmin()) {
					return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
				}
			} else {
				return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
			}

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
			if (Auth::guest()) {
				return response()->json(['success' => false, 'message' => __('You must be logged in to create a book.')]);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";

			if (!File::exists($bookJsonPath)) {
				return response()->json(['success' => false, 'message' => __('Book not found')], 404);
			}

			$bookData = json_decode(File::get($bookJsonPath), true);

			if (Auth::user() && ($bookData['owner'] !== Auth::user()->email) && !Auth::user()->isAdmin()) {
				return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
			}

			$coverFilename = $request->input('cover_filename');
			$bookData['cover_filename'] = $coverFilename;

			File::put($bookJsonPath, json_encode($bookData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

			return response()->json(['success' => true, 'message' => __('Cover saved successfully')]);
		}

		public function deleteBook($bookSlug)
		{
			if (Auth::guest()) {
				return response()->json(['success' => false, 'message' => __('You must be logged in to delete a book.')]);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";

			if (!File::exists($bookJsonPath)) {
				return response()->json(['success' => false, 'message' => __('Book not found')], 404);
			}

			$bookData = json_decode(File::get($bookJsonPath), true);

			if ($bookData['owner'] !== Auth::user()->email) {
				return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
			}

			File::deleteDirectory($bookPath);

			return response()->json(['success' => true, 'message' => __('Book deleted successfully')]);
		}

		public function fetch_initial_data()
		{

			echo json_encode([
//				'colorOptions' => $colorOptions,
//				'chaptersDirName' => $chaptersDirName,
//				'users' => array_column($users, 'username'),
//				'currentUser' => $current_user,
//				'defaultRow' => $defaultRow,
//				'rows' => $rows,
//				'bookData' => $bookData,
			]);
		}

		public function makeCoverImage(Request $request, $bookSlug)
		{
			if (Auth::guest()) {
				return response()->json(['success' => false, 'message' => __('You must be logged in to make the cover image of a book.')]);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";

			if (!File::exists($bookJsonPath)) {
				return response()->json(['success' => false, 'message' => __('Book not found')], 404);
			}

			$bookData = json_decode(File::get($bookJsonPath), true);

			if (Auth::user()) {
				if ($bookData['owner'] !== Auth::user()->email && !Auth::user()->isAdmin()) {
					return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
				}
			} else {
				return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
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
			if (Auth::guest()) {
				return response()->json(['success' => false, 'message' => __('You must be logged in to make the cover image of a book.')]);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsJsonPath = "{$bookPath}/acts.json";

			if (!File::exists($bookJsonPath)) {
				return response()->json(['success' => false, 'message' => __('Book not found')], 404);
			}

			if (!File::exists($actsJsonPath)) {
				return response()->json(['success' => false, 'message' => __('Acts not found')], 404);
			}

			$bookData = json_decode(File::get($bookJsonPath), true);

			if (Auth::user()) {
				if ($bookData['owner'] !== Auth::user()->email && !Auth::user()->isAdmin()) {
					return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
				}
			} else {
				return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
			}

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


			$save_beats = $request->input('save_beats', true);


			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			$useLlm = env('USE_LLM', 'open-router');

			if ($useLlm === 'anthropic-haiku' || $useLlm === 'anthropic-sonet') {
				$model = $useLlm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
				$schema = json_decode(File::get(resource_path('prompts/beat_schema_anthropic_1.json')), true);
				$prompt = File::get(resource_path('prompts/beat_prompt_anthropic_1.txt'));
			} elseif ($useLlm === 'open-ai-gpt-4o' || $useLlm === 'open-ai-gpt-4o-mini') {
				$model = $useLlm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL', 'open-router') : env('OPEN_AI_GPT4_MINI_MODEL', 'open-router');
				$schema = json_decode(File::get(resource_path('prompts/beat_schema_openai_1.json')), true);
				$prompt = File::get(resource_path('prompts/beat_prompt_openai_1.txt'));
			} else {
				$model = $llm;
				$prompt = File::get(resource_path('prompts/beat_prompt_no_function_calling_1.txt'));
				$schema = [];
			}

			$previous_chapter_beats = $current_chapter['from_previous_chapter'];
			if ($previous_chapter) {
				if ($previous_chapter['beats']) {
					$previous_chapter_beats = '';
					foreach ($previous_chapter['beats'] as $beat) {
						$previous_chapter_beats .= ($beat['description']??'') . "\n";
					}
				}
			}

			$replacements = [
				'##book_title##' => $bookData['title'],
				'##back_cover_text##' => $bookData['description'] ?? '',
				'##book_blurb##' => $bookData['blurb'],
				'##language##' => $bookData['language'],
				'##act##' => $current_chapter['row'],
				'##chapter##' => $current_chapter['name'],
				'##description##' => $current_chapter['short_description'],
				'##events##' => $current_chapter['events'],
				'##people##' => $current_chapter['people'],
				'##places##' => $current_chapter['places'],
				'##previous_chapter##' => $previous_chapter_beats,
				'##next_chapter##' => $current_chapter['to_next_chapter'],
			];

			$prompt = str_replace(array_keys($replacements), array_values($replacements), $prompt);

			$resultData = $useLlm === 'open-router'
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

				if ($save_beats) {
					if (file_exists($chapterFilePath)) {
						$chapterData = json_decode(file_get_contents($chapterFilePath), true);
						$chapterData['beats'] = $beats;

						if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
							return response()->json(['success' => true, 'beats' => $beats]);
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

			return response()->json(['success' => true, 'beats' => $beats]);
		}

		public function writeChapterBeatText(Request $request, $bookSlug, $chapter_name, $beat_text)
		{
			if (Auth::guest()) {
				return response()->json(['success' => false, 'message' => __('You must be logged in to make the cover image of a book.')]);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";

			if (!File::exists($bookJsonPath)) {
				return response()->json(['success' => false, 'message' => __('Book not found')], 404);
			}

			$bookData = json_decode(File::get($bookJsonPath), true);

			if (Auth::user()) {
				if ($bookData['owner'] !== Auth::user()->email && !Auth::user()->isAdmin()) {
					return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
				}
			} else {
				return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
			}

			$beatIndex = (int)$request->input('beatIndex');
			$currentBeatDescription = $request->input('currentBeatDescription') ?? '';


			// Load the beat prompt template
			$beatPromptTemplate = file_get_contents('./prompts/beat_text_prompt.txt');

			$book_title = $request->input('book_title');
			$book_blurb = $request->input('book_blurb');
			$back_cover_text = $request->input('back_cover_text');
			$language = $request->input('language');
			$act = $request->input('act');
			$chapter_title = $request->input('chapter_title');
			$chapter_description = $request->input('chapter_description');
			$chapter_events = $request->input('chapter_events');
			$chapter_people = $request->input('chapter_people');
			$chapter_places = $request->input('chapter_places');
			$previous_beat_summaries = $request->input('previous_beat_summaries');
			$last_beat = $request->input('last_beat');
			$current_beat = $request->input('current_beat');
			$next_beat = $request->input('next_beat');

			$beatPrompt = str_replace(
				['##book_title##', '##book_blurb##', '##back_cover_text##', '##language##', '##act##', '##chapter##', '##description##', '##events##', '##people##', '##places##', '##previous_beat_summaries##', '##last_beat##', '##current_beat##', '##next_beat##'],
				[
					$book_title,
					$book_blurb,
					$back_cover_text,
					$language,
					$act,
					$chapter_title,
					$chapter_description,
					$chapter_events,
					$chapter_people,
					$chapter_places,
					$previous_beat_summaries,
					$last_beat,
					$current_beat,
					$next_beat
				],
				$beatPromptTemplate
			);

			$resultData = $llmApp->llm_no_tool_call(false, $llm, $beatPrompt, false);

			echo json_encode(['success' => true, 'prompt' => $resultData]);

		}

		public function writeChapterBeatTextSummary(Request $request, $bookSlug, $chapter_name, $beat_text)
		{
			if (Auth::guest()) {
				return response()->json(['success' => false, 'message' => __('You must be logged in to make the cover image of a book.')]);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";

			if (!File::exists($bookJsonPath)) {
				return response()->json(['success' => false, 'message' => __('Book not found')], 404);
			}

			$bookData = json_decode(File::get($bookJsonPath), true);

			if (Auth::user()) {
				if ($bookData['owner'] !== Auth::user()->email && !Auth::user()->isAdmin()) {
					return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
				}
			} else {
				return response()->json(['success' => false, 'message' => __('You are not the owner of this book.')]);
			}

			$currentBeatDescription = $request->input('currentBeatDescription') ?? '';
			$currentBeatText = $request->input('currentBeatText') ?? '';

			// Load the beat prompt template
			$beatPromptTemplate = file_get_contents('./prompts/beat_text_summary.txt');

			$book_title = $request->input('book_title');
			$book_blurb = $request->input('book_blurb');
			$back_cover_text = $request->input('back_cover_text');
			$language = $request->input('language');
			$act = $request->input('act');
			$chapter_title = $request->input('chapter_title');
			$chapter_description = $request->input('chapter_description');
			$chapter_events = $request->input('chapter_events');
			$chapter_people = $request->input('chapter_people');
			$chapter_places = $request->input('chapter_places');

			// Replace placeholders in the prompt template

			$beatPrompt = str_replace(
				['##book_title##', '##book_blurb##', '##back_cover_text##', '##language##', '##act##', '##chapter##', '##description##', '##events##', '##people##', '##places##', '##beat_summary##', '##beat_text##'],
				[
					$book_title,
					$book_blurb,
					$back_cover_text,
					$language,
					$act,
					$chapter_title,
					$chapter_description,
					$chapter_events,
					$chapter_people,
					$chapter_places,
					$currentBeatDescription,
					$currentBeatText
				],
				$beatPromptTemplate
			);

			$resultData = $llmApp->llm_no_tool_call(false, $llm, $beatPrompt, false);

			echo json_encode(['success' => true, 'prompt' => $resultData]);

		}

//	$chapterData['beats'][$beatIndex]['description'] = $beatDescription;
//	$chapterData['beats'][$beatIndex]['beat_text'] = $beatText;
//	$chapterData['beats'][$beatIndex]['beat_text_summary'] = $beatTextSummary;
//
//	file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT));


	}
