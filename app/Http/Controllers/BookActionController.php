<?php

	namespace App\Http\Controllers;

	use App\Models\SentencesTable;
	use App\Models\User;
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
		public function startWriting(Request $request)
		{
			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';

			$genres_array = MyHelper::$genres_array;
			$adult_genres_array = MyHelper::$adult_genres_array;
			$writingStyles = MyHelper::$writingStyles;
			$narrativeStyles = MyHelper::$narrativeStyles;

			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("user.start-writing", compact('posts', 'coverFilename', 'adult_genres_array', 'genres_array', 'writingStyles', 'narrativeStyles'));

		}

		public function editBook(Request $request, $slug)
		{
			$verified = MyHelper::verifyBookOwnership($slug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$json_translations = MyHelper::writeJsTranslations();

			$bookPath = Storage::disk('public')->path("books/{$slug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsFile = "{$bookPath}/acts.json";

			if (!File::exists($bookJsonPath) || !File::exists($actsFile)) {
				return response()->json(['success' => false, 'message' => __('Book not found ' . $bookJsonPath)], 404);
			}

			$book = json_decode(File::get($bookJsonPath), true);

			//search $book['owner'] in users table name column
			$user = User::where('email', ($book['owner'] ?? 'admin'))->first();
			if ($user) {
				$book['owner_name'] = $user->name;
				if ($user->avatar) {
					$book['author_avatar'] = Storage::url($user->avatar);
				} else
				{
					$book['author_avatar'] = '/assets/images/avatar/03.jpg';
				}
			} else
			{
				$book['owner_name'] = 'admin';
				$book['author_name'] = $book['author_name']  . '(anonymous)';
				$book['author_avatar'] = '/assets/images/avatar/02.jpg';
			}


			$actsData = json_decode(File::get($actsFile), true);

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
					//if chapterData['events'] is an array, convert it to a string
					if (isset($chapterData['events']) && is_array($chapterData['events'])) {
						$chapterData['events'] = implode("\n", $chapterData['events']);
					}
					//places
					if (isset($chapterData['places']) && is_array($chapterData['places'])) {
						$chapterData['places'] = implode("\n", $chapterData['places']);
					}
					//people
					if (isset($chapterData['people']) && is_array($chapterData['people'])) {
						$chapterData['people'] = implode("\n", $chapterData['people']);
					}

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


			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
			$book['cover_filename'] = $book['cover_filename'] ?? '';

			$book_slug = $slug;

			if ($book['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $book['cover_filename']))) {
				$coverFilename = asset("storage/ai-images/" . $book['cover_filename']);
			}

			$book['cover_filename'] = $coverFilename;

			$book['acts'] = $acts;

			$colorOptions = [
				['background' => '#F28B82', 'text' => '#000000'],
				['background' => '#FBBC04', 'text' => '#000000'],
				['background' => '#FFF475', 'text' => '#000000'],
				['background' => '#CCFF90', 'text' => '#000000'],
				['background' => '#A7FFEB', 'text' => '#000000'],
				['background' => '#CBF0F8', 'text' => '#000000'],
				['background' => '#AECBFA', 'text' => '#000000'],
				['background' => '#D7AEFB', 'text' => '#000000'],
				['background' => '#FDCFE8', 'text' => '#000000'],
				['background' => '#E6C9A8', 'text' => '#000000'],
				['background' => '#E8EAED', 'text' => '#000000'],
				['background' => '#FFFFFF', 'text' => '#000000']
			];

			$genres_array = MyHelper::$genres_array;
			$adult_genres_array = MyHelper::$adult_genres_array;

			$writingStyles = MyHelper::$writingStyles;
			$narrativeStyles = MyHelper::$narrativeStyles;

			return view('user.edit-book', compact( 'book', 'json_translations', 'book_slug', 'colorOptions', 'genres_array', 'adult_genres_array', 'writingStyles', 'narrativeStyles'));
		}

		public function writeBookCharacterProfiles(Request $request)
		{
			$verified = MyHelper::verifyBookOwnership('');
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$language = $request->input('language', __('Default Language'));
			$userBlurb = $request->input('user_blurb', '');
			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');
			$adultContent = $request->input('adult_content', 'false');
			$genre = $request->input('genre', 'fantasy');
			$writingStyle = $request->input('writing_style', 'Minimalist');
			$narrativeStyle = $request->input('narrative_style', 'Third Person - The narrator has a godlike perspective');

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}
			$prompt = File::get(resource_path('prompts/book_prompt.txt'));

			$example_question = '';
			$example_answer = '';

			$blurb_vector = MyHelper::getEmbedding($userBlurb);

			$similar_prompts = MyHelper::getEmbeddingSimilarity($userBlurb, 0.1, 2, 5);
			if ($similar_prompts === []) {
				$similar_prompts = MyHelper::getEmbeddingSimilarity($userBlurb, 0.1, 1, 5);
			}

			if ($similar_prompts !== []) {
				$question_index = 0;
				$question = null;
				shuffle($similar_prompts);
				while ($question_index < count($similar_prompts)) {
					$question_id = $similar_prompts[$question_index]->questions_id;
					$question = SentencesTable::where('id', $question_id)->first();
					if ($question) {
						break;
					}
					$question_index++;
				}
				if ($question) {
					$example_question = $question['prompt'];
					$example_answer = $question['sentences'];
				}
			}

			$prompt = str_replace(['##user_blurb##', '##language##', '##adult_content##', '##genre##', '##writing_style##', '##narrative_style##'], [$userBlurb, $language, $adultContent, $genre, $writingStyle, $narrativeStyle], $prompt);
			$results = MyHelper::llm_no_tool_call($llm, $example_question, $example_answer, $prompt, true, $language);

			if (!empty($results['title']) && !empty($results['blurb']) && !empty($results['back_cover_text']) && !empty($results['character_profiles'])) {

				//loop all data fields and replace <BR> with \n
				foreach ($results as $key => $value) {
					if (gettype($value) === 'string') {
						$results[$key] = str_replace('<BR>', "\n", $value);
					} else if (gettype($value) === 'array') {
						foreach ($value as $key2 => $value2) {
							if (gettype($value2) === 'string') {
								$results[$key][$key2] = str_replace('<BR>', "\n", $value2);
							}
						}
					}
				}

				$results['example_question'] = $example_question;
				$results['example_answer'] = $example_answer;
				$results['similar_prompts'] = $similar_prompts;
				$results['blurb_vector'] = $blurb_vector['data'][0]['embedding'];

				return response()->json(['success' => true, 'message' => __('Book created successfully'), 'data' => $results]);
			} else {
				return response()->json(['success' => false, 'message' => __('Failed to generate book')]);
			}
		}

		public function rewriteChapter(Request $request)
		{
			$verified = MyHelper::verifyBookOwnership($request->input('book_slug'));
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}

			$bookSlug = $request->input('book_slug');
			$userPrompt = $request->input('user_prompt') ?? '';

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookData = json_decode(File::get("{$bookPath}/book.json"), true);

			if (!empty($userPrompt)) {
				$rewrittenChapter = MyHelper::llm_no_tool_call($llm, $bookData['example_question'] ?? '', $bookData['example_answer'] ?? '', $userPrompt, true, $bookData['language']);

				Log::info('Rewritten Chapter');
				Log::info($rewrittenChapter);

				// Instead of saving, return the rewritten chapter
				return response()->json([
					'success' => true,
					'rewrittenChapter' => $rewrittenChapter,
				]);
			} else
			{
				return response()->json([
					'success' => false,
					'message' => 'No user prompt provided',
				]);
			}
		}

		public function acceptRewrite(Request $request)
		{
			$verified = MyHelper::verifyBookOwnership($request->input('book_slug'));
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookSlug = $request->input('book_slug');
			$chapterFilename = $request->input('chapter_filename');
			$rewrittenContent = $request->input('rewritten_content');
			$rewrittenContent = json_decode($rewrittenContent, true);

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$chapterPath = "{$bookPath}/{$chapterFilename}";

			if (File::exists($chapterPath)) {
				$chapterData = json_decode(File::get($chapterPath), true);
				$chapterData['name'] = $rewrittenContent['name'];
				$chapterData['short_description'] = $rewrittenContent['short_description'];
				$chapterData['events'] = $rewrittenContent['events'];
				$chapterData['people'] = $rewrittenContent['people'];
				$chapterData['places'] = $rewrittenContent['places'];
				$chapterData['from_previous_chapter'] = $rewrittenContent['from_previous_chapter'];
				$chapterData['to_next_chapter'] = $rewrittenContent['to_next_chapter'];
				$chapterData['lastUpdated'] = now()->toDateTimeString();

				if (File::put($chapterPath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
					return response()->json(['success' => true, 'message' => 'Chapter rewritten successfully']);
				} else {
					return response()->json(['success' => false, 'message' => 'Failed to save rewritten chapter']);
				}
			} else {
				return response()->json(['success' => false, 'message' => 'Chapter file not found']);
			}
		}

		public function writeBook(Request $request)
		{
			$verified = MyHelper::verifyBookOwnership('');
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}


			$language = $request->input('language', __('Default Language'));
			$bookStructure = $request->input('book_structure', __('Default Structure'));
			$userBlurb = $request->input('user_blurb', '');
			$bookTitle = $request->input('book_title', '');
			$bookBlurb = $request->input('book_blurb', '');
			$bookKeywords = $request->input('book_keywords', '');
			$backCoverText = $request->input('back_cover_text', '');
			$characterProfiles = $request->input('character_profiles', '');
			$exampleQuestion = $request->input('example_question', '');
			$exampleAnswer = $request->input('example_answer', '');

			$authorName = $request->input('author_name', '');
			$publisherName = $request->input('publisher_name', '');

			$adultContent = $request->input('adult_content', 'false');
			$genre = $request->input('genre', 'fantasy');
			$writingStyle = $request->input('writing_style', 'Minimalist');
			$narrativeStyle = $request->input('narrative_style', 'Third Person - The narrator has a godlike perspective');


			$prompt = File::get(resource_path("prompts/{$bookStructure}"));

			$replacements = [
				'##user_blurb##' => $userBlurb,
				'##language##' => $language,
				'##book_title##' => $bookTitle,
				'##book_blurb##' => $bookBlurb,
				'##book_keywords##' => implode(', ', $bookKeywords),
				'##back_cover_text##' => $backCoverText,
				'##character_profiles##' => $characterProfiles,
				'##genre##' => $genre,
				'##adult_content##' => $adultContent,
				'##writing_style##' => $writingStyle,
				'##narrative_style##' => $narrativeStyle,
				'##book_structure##' => $bookStructure,
			];

			$prompt = str_replace(array_keys($replacements), array_values($replacements), $prompt);

			$results = MyHelper::llm_no_tool_call($llm, $exampleQuestion, $exampleAnswer, $prompt, true, $language);


			//loop all data fields and replace <BR> with \n
			foreach ($results as $key => $value) {
				if (gettype($value) === 'string') {
					$results[$key] = str_replace('<BR>', "\n", $value);
				} else if (gettype($value) === 'array') {
					foreach ($value as $key2 => $value2) {
						if (gettype($value2) === 'string') {
							$results[$key][$key2] = str_replace('<BR>', "\n", $value2);
						}
					}
				}
			}


			if (!empty($results['acts'])) {
				$bookHeaderData = [
					'title' => $bookTitle,
					'back_cover_text' => $backCoverText,
					'blurb' => $bookBlurb,
					'keywords' => $bookKeywords,
					'author_name' => $authorName,
					'publisher_name' => $publisherName,
					'character_profiles' => $characterProfiles,
					'prompt' => $userBlurb,
					'language' => $language,
					'adult_content' => $adultContent,
					'genre' => $genre,
					'writing_style' => $writingStyle,
					'narrative_style' => $narrativeStyle,
					'model' => $model,
					'example_question' => $exampleQuestion,
					'example_answer' => $exampleAnswer,
					'book_structure' => $bookStructure,
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
							'main_prompt_example_question' => $exampleQuestion ?? 'no example question',
							'main_prompt_example_answer' => $exampleAnswer ?? 'no example answer',
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
					'author_1' => $authorName,
					'creative' => 'more'
				]);

				$coverImageResponse = $this->makeCoverImage($coverImageRequest, $bookSlug);
				$coverImageData = json_decode($coverImageResponse, true);
				Log::info('coverImageData');
				Log::info($coverImageData);

				if ($coverImageData['success']) {
					// Update book.json with cover image information
					$bookData['cover_filename'] = $coverImageData['output_filename'];
					$bookData['cover_prompt'] = $coverImageData['prompt'];

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
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");

			$chapterFilename = $request->input('chapter_filename');

			$chapterPath = "{$bookPath}/{$chapterFilename}";

			$chapterData = [
				'row' => (File::exists($chapterPath) ? json_decode(File::get($chapterPath), true)['row'] : '0'),
				'order' => $request->input('order', 0),
				'name' => $request->input('name'),
				'short_description' => $request->input('short_description'),
				'events' => $request->input('events'),
				'people' => $request->input('people'),
				'places' => $request->input('places'),
				'from_previous_chapter' => $request->input('from_previous_chapter'),
				'to_next_chapter' => $request->input('to_next_chapter'),
				'created' => (File::exists($chapterPath) ? json_decode(File::get($chapterPath), true)['created'] : now()->toDateTimeString()),
				'lastUpdated' => now()->toDateTimeString(),
			];

			File::put($chapterPath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

			$chapterData['chapterFilename'] = $chapterFilename;
			$chapterData['success'] = true;

			return response()->json($chapterData);
		}

		public function saveCover(Request $request, $bookSlug)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
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
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$sourcePath = Storage::disk('public')->path("books/{$bookSlug}");
			$destinationPath = Storage::disk('public')->path("deleted_books/{$bookSlug}");

			// Create the deleted_books directory if it doesn't exist
			if (!File::isDirectory(dirname($destinationPath))) {
				File::makeDirectory(dirname($destinationPath), 0755, true);
			}

			// Move the book folder to the deleted_books directory
			if (File::moveDirectory($sourcePath, $destinationPath)) {
				return response()->json(['success' => true, 'message' => __('Book deleted successfully')]);
			} else {
				return response()->json(['success' => false, 'message' => __('Failed to delete the book')]);
			}
		}

		public function makeCoverImage(Request $request, $bookSlug)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$theme = $request->input('theme', 'an ocean in space');
			$title1 = $request->input('title_1', 'Write Books With AI');
			$author1 = $request->input('author_1', 'I, Robot');
			$model = 'fast'; //$request->input('model', 'fast');
			$creative = $request->input('creative', 'no');

			if (Auth::user() && Auth::user()->isAdmin()) {
//				$model = 'balanced';
			}

			$prompt = $theme . ' the book covers title is "' . $title1 . '" and the author is "' . $author1 . '" include the text lines on the cover.';

			Log::info('Cover Image Prompt');
			Log::info($prompt);

			if ($creative === 'more') {

				$gpt_prompt = "Write a prompt for an book cover
The image in the background is :
 " . $theme . "
 the book title is " . $title1 . "
 the author is " . $author1 . "
Write in English even if the background is written in another language.
With the above information, compose a book cover. Write it as a single paragraph. The instructions should focus on the text elements of the book cover. Generally the title should be on top part and the author on the bottom of the image. The texts should not repeat.

Prompt:";

				$prompt = MyHelper::llm_no_tool_call('open-ai-gpt-4o-mini', '', '', $gpt_prompt, false, 'english');
				Log::info('Enhanced Cover Image Prompt');
				Log::info($prompt);
			}

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
					'prompt' => $prompt,
					'image_size' => 'portrait_4_3',
					'safety_tolerance' => '5',
				]
			]);
			Log::info('FLUX image response');
			Log::info($response->getBody());

			$body = $response->getBody();
			$data = json_decode($body, true);

			if ($response->getStatusCode() == 200) {

				if (isset($data['images'][0]['url'])) {
					$image_url = $data['images'][0]['url'];

					//save image_url to folder both .png and _1024.png
					$image = file_get_contents($image_url);
					file_put_contents($outputFile, $image);

					return json_encode(['success' => true, 'message' => __('Image generated successfully'), 'output_filename' => $filename, 'output_path' => $outputFile, 'data' => json_encode($data), 'seed' => $data['seed'], 'status_code' => $response->getStatusCode(), 'prompt' => $prompt]);
				} else {
					return json_encode(['success' => false, 'message' => __('Error (2) generating image'), 'status_code' => $response->getStatusCode()]);
				}
			} else {
				return json_encode(['success' => false, 'message' => __('Error (1) generating image'), 'status_code' => $response->getStatusCode()]);
			}

		}

		public function saveBookDetails(Request $request, $bookSlug)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$bookData = json_decode(File::get($bookJsonPath), true);

			$updatedFields = [
				'blurb',
				'back_cover_text',
				'character_profiles',
				'author_name',
				'publisher_name'
			];

			foreach ($updatedFields as $field) {
				if ($request->has($field)) {
					$bookData[$field] = $request->input($field);
				}
			}

			File::put($bookJsonPath, json_encode($bookData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

			return response()->json(['success' => true, 'message' => __('Book details updated successfully')]);
		}

		public function sendLlmPrompt(Request $request, $bookSlug)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$userPrompt = $request->input('user_prompt');
			$llm = $request->input('llm');

			try {
				$resultData = MyHelper::llm_no_tool_call($llm, '', '', $userPrompt, false);
				return response()->json(['success' => true, 'result' => $resultData]);
			} catch (\Exception $e) {
				return response()->json(['success' => false, 'message' => $e->getMessage()]);
			}
		}

	}
