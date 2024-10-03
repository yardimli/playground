<?php

	namespace App\Http\Controllers;

	use App\Models\SentencesTable;
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

			$chaptersToRewrite = json_decode($request->input('chapters_to_rewrite', '[]'), true);

			$bookSlug = $request->input('book_slug');
			$userPrompt = $request->input('user_prompt');

			$rewriteChapterFilename = $request->input('rewrite_chapter_filename');
			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookData = json_decode(File::get("{$bookPath}/book.json"), true);

			$rewrittenChapters = [];

			if (!empty($userPrompt)) {
				$prompt = $userPrompt;
			} else {
				$prompt = File::get(resource_path('prompts/rewrite_chapter.txt'));
			}

			$lastChapter = $chaptersToRewrite[count($chaptersToRewrite) - 1];
			//remove last chapter from chaptersToRewrite
			array_pop($chaptersToRewrite);

			$chapters_string = '';
			for ($i = 0; $i < count($chaptersToRewrite); $i++) {
				$chapters_string .= 'name: ' . ($chaptersToRewrite[$i]['name'] ?? '') . "\n" .
					'short description: ' . ($chaptersToRewrite[$i]['short_description'] ?? '') . "\n" .
					'events: ' . ($chaptersToRewrite[$i]['events'] ?? '') . "\n" .
					'people: ' . ($chaptersToRewrite[$i]['people'] ?? '') . "\n" .
					'places: ' . ($chaptersToRewrite[$i]['places'] ?? '') . "\n" .
					'from previous chapter: ' . ($chaptersToRewrite[$i]['from_previous_chapter'] ?? '') . "\n" .
					'to next chapter: ' . ($chaptersToRewrite[$i]['to_next_chapter'] ?? '') . "\n\n";

				$chapters_string .= 'beats: ' . "\n";
				for ($j = 0; $j < count($chaptersToRewrite[$i]['beats']); $j++) {
					$chapters_string .= ($chaptersToRewrite[$i]['beats'][$j]['beat_summary'] ?? '') . "\n";
				}
			}

			$current_chapter_string = 'name: ' . ($lastChapter['name'] ?? '') . "\n" .
				'short description: ' . ($lastChapter['short_description'] ?? '') . "\n" .
				'events: ' . ($lastChapter['events'] ?? '') . "\n" .
				'people: ' . ($lastChapter['people'] ?? '') . "\n" .
				'places: ' . ($lastChapter['places'] ?? '') . "\n" .
				'from previous chapter: ' . ($lastChapter['from_previous_chapter'] ?? '') . "\n" .
				'to next chapter: ' . ($lastChapter['to_next_chapter'] ?? '') . "\n\n";


			$replacements = [
				'##user_blurb##' => $bookData['prompt'] ?? '',
				'##language##' => $bookData['language'] ?? 'English',
				'##book_title##' => $bookData['title'] ?? '',
				'##book_blurb##' => $bookData['blurb'] ?? '',
				'##book_keywords##' => implode(', ', $bookData['keywords'] ?? []),
				'##back_cover_text##' => $bookData['back_cover_text'] ?? '',
				'##character_profiles##' => $bookData['character_profiles'] ?? '',
				'##genre##' => $bookData['genre'] ?? 'fantasy',
				'##adult_content##' => $bookData['adult_content'] ?? 'non-adult',
				'##writing_style##' => $bookData['writing_style'] ?? 'Minimalist',
				'##narrative_style##' => $bookData['narrative_style'] ?? 'Third Person - The narrator has a godlike perspective',
				'##book_structure##' => $bookData['book_structure'] ?? 'the_1_act_story.txt',
				'##previous_chapters##' => $chapters_string,
				'##current_chapter##' => $current_chapter_string,
			];

			$prompt = str_replace(array_keys($replacements), array_values($replacements), $prompt);

			$rewrittenChapter = MyHelper::llm_no_tool_call($llm, $bookData['example_question'] ?? '', $bookData['example_answer'] ?? '', $prompt, true, $bookData['language']);

			Log::info('Rewritten Chapter');
			Log::info($rewrittenChapter);

			// Instead of saving, return the rewritten chapter
			return response()->json([
				'success' => true,
				'rewrittenChapter' => $rewrittenChapter,
			]);
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

			$chapterFilename = $request->input('chapterFilename');

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
With the above information, compose a book cover. Write it as a single paragraph. The instructions should focus on the text elements of the book cover. Generally the title should be on top part and the artist on the bottom of the image.

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

		public function writeChapterBeats(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
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
			if ($previous_chapter && array_key_exists('beats', $previous_chapter)) {
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
			$beats_per_chapter = (int)$request->input('beats_per_chapter', 3);

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}
			$prompt = File::get(resource_path('prompts/beat_prompt.txt'));

			$beats_per_chapter_list = '';
			for ($i = 0; $i < $beats_per_chapter; $i++) {
				$beats_per_chapter_list .= "{\"description\":\"write beat " . ($i + 1) . " for this chapter.\"}";
				if ($i < $beats_per_chapter - 1) {
					$beats_per_chapter_list .= ",\n";
				}
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
				'##beats_per_chapter##' => $beats_per_chapter,
				'##beats_per_chapter_list##' => $beats_per_chapter_list,
				'##character_profiles##' => $bookData['character_profiles'] ?? 'no character profiles',
				'##genre##' => $bookData['genre'] ?? 'fantasy',
				'##writing_style##' => $bookData['writing_style'] ?? 'Minimalist',
				'##narrative_style##' => $bookData['narrative_style'] ?? 'Third Person - The narrator has a godlike perspective',
			];

			$prompt = str_replace(array_keys($replacements), array_values($replacements), $prompt);

			$example_question = '';
			$example_answer = '';
			$similar_prompts = MyHelper::getEmbeddingSimilarity($current_chapter['short_description'], 0.1, 2, 5);
			if ($similar_prompts === []) {
				$similar_prompts = MyHelper::getEmbeddingSimilarity($current_chapter['short_description'], 0.1, 1, 5);
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

			$resultData = MyHelper::llm_no_tool_call($llm, $example_question, $example_answer, $prompt, true);

			//loop all data fields and replace <BR> with \n
			foreach ($resultData as $key => $value) {
				if (gettype($value) === 'string') {
					$resultData[$key] = str_replace('<BR>', "\n", $value);
				} else if (gettype($value) === 'array') {
					foreach ($value as $key2 => $value2) {
						if (gettype($value2) === 'string') {
							$resultData[$key][$key2] = str_replace('<BR>', "\n", $value2);
						}
					}
				}
			}

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
						$chapterData['example_question'] = $example_question;
						$chapterData['example_answer'] = $example_answer;

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

		public function writeChapterBeatDescription(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
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
			$last_beat_lore_book = '';
			$next_beat = '';

			// Get the current beat
			if ($current_beat === '') {
				if (isset($current_chapter['beats'][$beatIndex])) {
					$current_beat = $current_chapter['beats'][$beatIndex]['beat_text'] ?? '';
				}
			}

			// Process previous beats and last beat
			if ($beatIndex > 0) {
				for ($i = 0; $i < $beatIndex; $i++) {
					if ($i === $beatIndex - 1) {
						$last_beat_full_text = $current_chapter['beats'][$i]['beat_text'] ?? '';
						$last_beat_lore_book = $current_chapter['beats'][$i]['beat_lore_book'] ?? '';
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
							$last_beat_lore_book = $prev_chapter_beats[$i]['beat_lore_book'] ?? '';
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
			$beatPromptTemplate = File::get(resource_path('prompts/beat_description_prompt.txt'));

			$replacements = [
				'##book_title##' => $bookData['title'] ?? 'no title',
				'##back_cover_text##' => $bookData['back_cover_text'] ?? 'no back cover text',
				'##book_blurb##' => $bookData['blurb'] ?? 'no blurb',
				'##language##' => $bookData['language'] ?? 'English',
				'##character_profiles##' => $bookData['character_profiles'] ?? 'no character profiles',
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
				'##beat_lore_book##' => $last_beat_lore_book,
				'##current_beat##' => $current_beat,
				'##next_beat##' => $next_beat,
				'##genre##' => $bookData['genre'] ?? 'fantasy',
				'##writing_style##' => $bookData['writing_style'] ?? 'Minimalist',
				'##narrative_style##' => $bookData['narrative_style'] ?? 'Third Person - The narrator has a godlike perspective',
			];

			$beatPrompt = str_replace(array_keys($replacements), array_values($replacements), $beatPromptTemplate);

			$example_question = '';
			$example_answer = '';

			if (array_key_exists('example_question', $current_chapter) && array_key_exists('example_answer', $current_chapter)) {
				$example_question = $current_chapter['example_question'];
				$example_answer = $current_chapter['example_answer'];
			}

			$resultData = MyHelper::llm_no_tool_call($llm, $example_question, $example_answer, $beatPrompt, false);

			$resultData = str_replace('<BR>', "\n", $resultData);

			if ($save_results) {
				$chapterFilePath = "{$bookPath}/{$chapterFilename}";

				if (file_exists($chapterFilePath)) {
					$chapterData = json_decode(file_get_contents($chapterFilePath), true);
					$chapterData['beats'][$beatIndex]['description'] = $resultData;

					if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
						return response()->json(['success' => true, 'message' => 'Wrote beat description to file.', 'prompt' => $resultData]);
					} else {
						return response()->json(['success' => false, 'message' => __('Failed to write to file')]);
					}
				} else {
					return response()->json(['success' => false, 'message' => __('Chapter file not found')]);
				}
			}

			echo json_encode(['success' => true, 'prompt' => $resultData]);
		}

		public function writeChapterBeatText(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
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
			$last_beat_lore_book = '';
			$next_beat = '';

			// Get the current beat
			if ($current_beat === '') {
				if (isset($current_chapter['beats'][$beatIndex])) {
					$current_beat = $current_chapter['beats'][$beatIndex]['beat_text'] ?? '';
				}
			}

			// Process previous beats and last beat
			if ($beatIndex > 0) {
				for ($i = 0; $i < $beatIndex; $i++) {
					if ($i === $beatIndex - 1) {
						$last_beat_full_text = $current_chapter['beats'][$i]['beat_text'] ?? '';
						$last_beat_lore_book = $current_chapter['beats'][$i]['beat_lore_book'] ?? '';
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
							$last_beat_lore_book = $prev_chapter_beats[$i]['beat_lore_book'] ?? '';
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
				'##character_profiles##' => $bookData['character_profiles'] ?? 'no character profiles',
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
				'##beat_lore_book##' => $last_beat_lore_book,
				'##current_beat##' => $current_beat,
				'##next_beat##' => $next_beat,
				'##genre##' => $bookData['genre'] ?? 'fantasy',
				'##writing_style##' => $bookData['writing_style'] ?? 'Minimalist',
				'##narrative_style##' => $bookData['narrative_style'] ?? 'Third Person - The narrator has a godlike perspective',
			];

			$beatPrompt = str_replace(array_keys($replacements), array_values($replacements), $beatPromptTemplate);

			$example_question = '';
			$example_answer = '';

			if (array_key_exists('example_question', $current_chapter) && array_key_exists('example_answer', $current_chapter)) {
				$example_question = $current_chapter['example_question'];
				$example_answer = $current_chapter['example_answer'];
			} else {
				$similar_prompts = MyHelper::getEmbeddingSimilarity($current_chapter['short_description'], 0.1, 2, 5);
				if ($similar_prompts === []) {
					$similar_prompts = MyHelper::getEmbeddingSimilarity($current_chapter['short_description'], 0.1, 1, 5);
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
			}


			$resultData = MyHelper::llm_no_tool_call($llm, $example_question, $example_answer, $beatPrompt, false);

			$resultData = str_replace('<BR>', "\n", $resultData);

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
			$verified = MyHelper::verifyBookOwnership($bookSlug);
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

			$resultData = MyHelper::llm_no_tool_call($llm, '', '', $beatPrompt, false);

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

		public function updateBeatLoreBook(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsJsonPath = "{$bookPath}/acts.json";

			$bookData = json_decode(File::get($bookJsonPath), true);
			$actsData = json_decode(File::get($actsJsonPath), true);

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

			// Reconstruct the chapter structure
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

			// Find current, previous, and next chapters
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

			// Get previous chapter's lore book
			$previous_lore_book = '';
			if ($beatIndex > 0) {
				// If not the first beat, get the previous beat's lore book from the same chapter
				$previous_lore_book = $current_chapter['beats'][$beatIndex - 1]['beat_lore_book'] ?? '';
			} elseif ($previous_chapter !== null && isset($previous_chapter['beats'])) {
				// If it's the first beat of the chapter, get the last beat's lore book from the previous chapter
				$prev_chapter_beats = $previous_chapter['beats'];
				$prev_beats_count = count($prev_chapter_beats);
				if ($prev_beats_count > 0) {
					$previous_lore_book = $prev_chapter_beats[$prev_beats_count - 1]['beat_lore_book'] ?? '';
				}
			}

			// Load the beat prompt template
			$beatPromptTemplate = File::get(resource_path('prompts/beat_lore_book.txt'));

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
				'##previous_lore_book##' => $previous_lore_book,
			];

			$beatPrompt = str_replace(array_keys($replacements), array_values($replacements), $beatPromptTemplate);

			$resultData = MyHelper::llm_no_tool_call($llm, '', '', $beatPrompt, false);

			if ($save_results) {
				$chapterFilePath = "{$bookPath}/{$chapterFilename}";

				if (file_exists($chapterFilePath)) {
					$chapterData = json_decode(file_get_contents($chapterFilePath), true);
					$chapterData['beats'][$beatIndex]['beat_lore_book'] = $resultData;

					if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
						return response()->json(['success' => true, 'message' => 'Wrote beat lore book to file.', 'prompt' => $resultData]);
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
			$verified = MyHelper::verifyBookOwnership($bookSlug);
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
			$verified = MyHelper::verifyBookOwnership($bookSlug);
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
			$beatLoreBook = $request->input('beatLoreBook', '');

			$chapterData['beats'][$beatIndex]['description'] = $beatDescription;
			$chapterData['beats'][$beatIndex]['beat_text'] = $beatText;
			$chapterData['beats'][$beatIndex]['beat_summary'] = $beatSummary;
			$chapterData['beats'][$beatIndex]['beat_lore_book'] = $beatLoreBook;

			if (File::put($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
				return response()->json(['success' => true, 'message' => 'Beats saved.']);
			} else {
				return response()->json(['success' => false, 'message' => __('Failed to save beats')]);
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

			$userPrompt = $request->input('userPrompt');
			$llm = $request->input('llm');

			try {
				$resultData = MyHelper::llm_no_tool_call($llm, '', '', $userPrompt, false);
				return response()->json(['success' => true, 'result' => $resultData]);
			} catch (\Exception $e) {
				return response()->json(['success' => false, 'message' => $e->getMessage()]);
			}
		}

	}
