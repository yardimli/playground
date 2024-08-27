<?php
	require_once 'vendor/autoload.php';
	require_once 'llm-call.php';
	require_once 'action-session.php';
	require_once 'action-init.php';

	$llm = $_POST['llm'] ?? 'anthropic/claude-3-haiku:beta';

	switch ($action) {

		//-----------------------------//
		case 'write_book_character_profiles':
			if ($current_user === __e('Visitor')) {
				echo json_encode(['success' => false, 'message' => __e('You must be logged in to create a book.')]);
				break;
			}

			$language = $_POST['language'] ?? __e('Default Language');
			$user_blurb = $_POST['user_blurb'] ?? '';
			$book_structure = $_POST['bookStructure'] ?? 'fichtean_curve.txt';


			if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
				if ($use_llm === 'anthropic-haiku') {
					$model = $_ENV['ANTHROPIC_HAIKU_MODEL'];
				} else {
					$model = $_ENV['ANTHROPIC_SONET_MODEL'];
				}

				$schema = file_get_contents('./prompts/book_schema_anthropic_1.json');
				$schema = json_decode($schema, true);

				$prompt = file_get_contents('./prompts/book_prompt_anthropic_1.txt');
				$prompt = str_replace('##user_blurb##', $user_blurb, $prompt);
				$prompt = str_replace('##language##', $language, $prompt);

				$results = $llmApp->function_call($prompt, $schema, $language);

			} else if ($use_llm === 'open-ai-gpt-4o' || $use_llm === 'open-ai-gpt-4o-mini') {
				if ($use_llm === 'open-ai-gpt-4o') {
					$model = $_ENV['OPEN_AI_GPT4_MODEL'] ?? 'open-router';
				} else {
					$model = $_ENV['OPEN_AI_GPT4_MINI_MODEL'] ?? 'open-router';
				}

				$schema = file_get_contents('./prompts/book_schema_openai_1.json');
				$schema = json_decode($schema, true);

				$prompt = file_get_contents('./prompts/book_prompt_openai_1.txt');
				$prompt = str_replace('##user_blurb##', $user_blurb, $prompt);
				$prompt = str_replace('##language##', $language, $prompt);

				$results = $llmApp->function_call($prompt, $schema, $language);
			} else {
				$model = $_ENV['OPEN_ROUTER_MODEL'] ?? 'anthropic/claude-3-haiku:beta';
				$model = $llm;
				$prompt = file_get_contents('./prompts/book_prompt_no_function_calling_1.txt');
				$prompt = str_replace('##user_blurb##', $user_blurb, $prompt);
				$prompt = str_replace('##language##', $language, $prompt);
				$schema = [];

				$results = $llmApp->llm_no_tool_call(false, $llm, $prompt, true, $language);
			}

			if ($results['title'] !== '' && $results['blurb'] !== '' && $results['back_cover_text'] !== '' && $results['character_profiles'] !== []) {
				echo json_encode(['success' => true, 'message' => __e('Book created successfully'), 'data' => $results]);
			} else {
				echo json_encode(['success' => false, 'message' => __e('Failed to generate book')]);
			}
			break;

		//-----------------------------//
		case 'write_book':
			if ($current_user === __e('Visitor')) {
				echo json_encode(['success' => false, 'message' => __e('You must be logged in to create a book.')]);
				break;
			}

			$language = $_POST['language'] ?? __e('Default Language');
			$book_structure = $_POST['bookStructure'] ?? __e('Default Structure');

			$user_blurb = $_POST['user_blurb'] ?? '';
			$book_title = $_POST['book_title'] ?? '';
			$book_blurb = $_POST['book_blurb'] ?? '';
			$back_cover_text = $_POST['back_cover_text'] ?? '';
			$character_profiles = $_POST['character_profiles'] ?? '';

			if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
				if ($use_llm === 'anthropic-haiku') {
					$model = $_ENV['ANTHROPIC_HAIKU_MODEL'];
				} else {
					$model = $_ENV['ANTHROPIC_SONET_MODEL'];
				}

				$schema = file_get_contents('./prompts/book_schema_anthropic_1.json');
				$schema = json_decode($schema, true);

				$prompt = file_get_contents('./prompts/book_prompt_anthropic_1.txt');
				$prompt = str_replace('##user_blurb##', $user_blurb, $prompt);
				$prompt = str_replace('##language##', $language, $prompt);
				$prompt = str_replace('##book_title##', $book_title, $prompt);
				$prompt = str_replace('##book_blurb##', $book_blurb, $prompt);
				$prompt = str_replace('##back_cover_text##', $back_cover_text, $prompt);
				$prompt = str_replace('##character_profiles##', $character_profiles, $prompt);

				$results = $llmApp->function_call($prompt, $schema, $language);

			} else if ($use_llm === 'open-ai-gpt-4o' || $use_llm === 'open-ai-gpt-4o-mini') {
				if ($use_llm === 'open-ai-gpt-4o') {
					$model = $_ENV['OPEN_AI_GPT4_MODEL'] ?? 'open-router';
				} else {
					$model = $_ENV['OPEN_AI_GPT4_MINI_MODEL'] ?? 'open-router';
				}

				$schema = file_get_contents('./prompts/book_schema_openai_1.json');
				$schema = json_decode($schema, true);

				$prompt = file_get_contents('./prompts/book_prompt_openai_1.txt');
				$prompt = str_replace('##user_blurb##', $user_blurb, $prompt);
				$prompt = str_replace('##language##', $language, $prompt);
				$prompt = str_replace('##book_title##', $book_title, $prompt);
				$prompt = str_replace('##book_blurb##', $book_blurb, $prompt);
				$prompt = str_replace('##back_cover_text##', $back_cover_text, $prompt);
				$prompt = str_replace('##character_profiles##', $character_profiles, $prompt);

				$results = $llmApp->function_call($prompt, $schema, $language);
			} else {
				$model = $_ENV['OPEN_ROUTER_MODEL'] ?? 'anthropic/claude-3-haiku:beta';
				$model = $llm;

				$prompt = file_get_contents('./prompts/' . $book_structure); //book_prompt_no_function_calling_2.txt');
				$prompt = str_replace('##user_blurb##', $user_blurb, $prompt);
				$prompt = str_replace('##language##', $language, $prompt);
				$prompt = str_replace('##book_title##', $book_title, $prompt);
				$prompt = str_replace('##book_blurb##', $book_blurb, $prompt);
				$prompt = str_replace('##back_cover_text##', $back_cover_text, $prompt);
				$prompt = str_replace('##character_profiles##', $character_profiles, $prompt);

				$results = $llmApp->llm_no_tool_call(false, $llm, $prompt, true, $language);
			}

			if (!empty($results['acts'])) {
				$book_header_data = [
					'title' => $book_title,
					'back_cover_text' => $back_cover_text,
					'blurb' => $book_blurb,
					'character_profiles' => $character_profiles,
					'prompt' => $user_blurb,
					'language' => $language,
				];

				$llmApp->createBookStructure($book_header_data, $results, $model, $current_user);
				echo json_encode(['success' => true, 'message' => __e('Book created successfully'), 'data' => $results]);
			} else {
				echo json_encode(['success' => false, 'message' => __e('Failed to generate book')]);
			}
			break;

		//-----------------------------//
		case 'get_book_structure':

			$bookData = json_decode(file_get_contents($bookJsonPath), true);
			$chaptersFile = $chaptersDirName . "/acts.json";
			$chaptersData = json_decode(file_get_contents($chaptersFile), true);

			$acts = [];
			foreach ($chaptersData as $act) {
				$actChapters = [];
				$chapterFiles = glob($chaptersDirName . "/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(file_get_contents($chapterFile), true);
					if (!isset($chapterData['row'])) {
						continue;
					}

					if ($chapterData['row'] === $act['id']) {
						$actChapters[] = $chapterData;
					}
				}
				usort($actChapters, function ($a, $b) {
					return $a['order'] - $b['order'];
				});
				$acts[] = [
					'name' => $act['title'],
					'chapters' => $actChapters
				];
			}

			echo json_encode([
				'success' => true,
				'bookTitle' => $bookData['title'],
				'bookBlurb' => $bookData['blurb'],
				'backCoverText' => $bookData['back_cover_text'],
				'prompt' => $bookData['prompt'],
				'language' => $bookData['language'],
				'acts' => $acts
			]);

			break;

		//-----------------------------//
		case 'fetch_initial_data':
			echo json_encode([
				'colorOptions' => $colorOptions,
				'chaptersDirName' => $chaptersDirName,
				'users' => array_column($users, 'username'),
				'currentUser' => $current_user,
				'defaultRow' => $defaultRow,
				'rows' => $rows,
				'bookData' => $bookData,
			]);
			break;

		//-----------------------------//
		case 'load_chapters':
			$chaptersFile = $chaptersDirName . "/acts.json";
			$chaptersData = json_decode(file_get_contents($chaptersFile), true);

			$chapters = [];
			$acts = [];
			foreach ($chaptersData as $act) {
				$actChapters = [];
				$chapterFiles = glob($chaptersDirName . "/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(file_get_contents($chapterFile), true);
					// Check if json_decode succeeded
					if (json_last_error() === JSON_ERROR_NONE) {

						if (!isset($chapterData['row'])) {
							continue;
						}

						$chapterData['chapterFilename'] = basename($chapterFile);

						if ($chapterData['row'] === $act['id']) {
							$actChapters[] = $chapterData;
						}
					}
				}

				usort($actChapters, function ($a, $b) {
					return $a['order'] - $b['order'];
				});

				foreach ($actChapters as $chapter) {
					$chapters[] = $chapter;
				}
			}

			echo json_encode($chapters);
			break;

		//-----------------------------//
		case 'save_chapter':
			if ($current_user === __e('Visitor')) {
				echo json_encode(['success' => false, 'message' => __e('You must be logged in to create a book.')]);
				break;
			}

			if ($bookData['owner'] !== $current_user) {
				echo json_encode(['success' => false, 'message' => __e('You are not the owner of this book.')]);
				break;
			}

			$name = $_POST['name'];
			$short_description = $_POST['short_description'];
			$events = $_POST['events'];
			$people = $_POST['people'];
			$places = $_POST['places'];
			$from_prev_chapter = $_POST['from_prev_chapter'];
			$to_next_chapter = $_POST['to_next_chapter'];
			$backgroundColor = $_POST['backgroundColor'];
			$textColor = $_POST['textColor'];
			$created = $lastUpdated = date('Y-m-d H:i:s');
			$row = 'to-do';
			$order = $_POST['order'] ?? 0;
			$new_chapter = true;

			if (empty($chapterFilename)) {
				$chapterFilename = create_slug($name) . '_' . time() . '.json';
			} else {
				$new_chapter = false;
				if (file_exists($chapterFilePath)) {
					$existingChapter = json_decode(file_get_contents($chapterFilePath), true);
					$created = $existingChapter['created'];
					$row = $existingChapter['row'];
				}
			}

			$chapter = [
				'row' => $row,
				'order' => $order,
				'name' => $name,
				'short_description' => $short_description,
				'events' => $events,
				'people' => $people,
				'places' => $places,
				'from_prev_chapter' => $from_prev_chapter,
				'to_next_chapter' => $to_next_chapter,
				'backgroundColor' => $backgroundColor,
				'textColor' => $textColor,
				'created' => $created,
				'lastUpdated' => $lastUpdated,
			];

			file_put_contents($chaptersDir . '/' . $chapterFilename, json_encode($chapter, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

			$chapter['chapterFilename'] = $chapterFilename;
			$chapter['success'] = true;
			echo json_encode($chapter);
			break;

		//-----------------------------//
		case 'save_cover':
			if ($current_user === 'Visitor') {
				echo json_encode(['success' => false, 'message' => __e('You must be logged in to create a book.')]);
				break;
			}

			if ($bookData['owner'] !== $current_user && $current_user !== 'admin') {
				echo json_encode(['success' => false, 'message' => __e('You are not the owner of this book.')]);
				break;
			}

			$cover_filename = $_POST['cover_filename'];

			$bookJsonPath = $chaptersDirName . '/book.json';
			if (file_exists($bookJsonPath)) {
				$bookJson = file_get_contents($bookJsonPath);
				$bookData = json_decode($bookJson, true);
				if (json_last_error() !== JSON_ERROR_NONE) {
					echo json_encode(['success' => false, 'message' => __e('Error decoding JSON:') . json_last_error_msg()]);
					break;
				}

				$bookData['cover_filename'] = $cover_filename;
				file_put_contents($bookJsonPath, json_encode($bookData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
				echo json_encode(['success' => true, 'message' => __e('Cover saved successfully')]);
			} else {
				echo json_encode(['success' => false, 'message' => __e('Book JSON file not found.')]);
			}
			break;

	}

