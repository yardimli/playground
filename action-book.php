<?php
	require_once 'vendor/autoload.php';
	require_once 'llm-call.php';
	require_once 'action-session.php';
	require_once 'action-init.php';

	$llm = $_POST['llm'] ?? 'anthropic/claude-3-haiku:beta';

	switch ($action) {

		//-----------------------------//
		case 'write_book':
			$language = $_POST['language'] ?? 'English';
			$blurb = $_POST['blurb'] ?? '';


			if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
				if ($use_llm === 'anthropic-haiku') {
					$model = $_ENV['ANTHROPIC_HAIKU_MODEL'];
				} else
				{
					$model = $_ENV['ANTHROPIC_SONET_MODEL'];
				}

				$schema = file_get_contents('./prompts/book_schema_anthropic_1.json');
				$schema = json_decode($schema, true);

				$prompt = file_get_contents('./prompts/book_prompt_anthropic_1.txt');
				$prompt = str_replace('#subject#', $blurb, $prompt);
				$prompt = str_replace('#language#', $language, $prompt);

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
				$prompt = str_replace('#subject#', $blurb, $prompt);
				$prompt = str_replace('#language#', $language, $prompt);

				$results = $llmApp->function_call($prompt, $schema, $language);
			} else {
				$model = $_ENV['OPEN_ROUTER_MODEL'] ?? 'anthropic/claude-3-haiku:beta';
				$model = $llm;
				$prompt = file_get_contents('./prompts/book_prompt_no_function_calling_1.txt');
				$prompt = str_replace('##subject##', $blurb, $prompt);
				$prompt = str_replace('##language##', $language, $prompt);
				$schema = [];

				$results = $llmApp->llm_no_tool_call(false, $llm, $prompt, true, $language);

				$book_title = $results['title'] ?? '';
				$book_blurb = $results['blurb'] ?? '';
				$back_cover_text = $results['back_cover_text'] ?? '';

				if (empty($book_title) || empty($book_blurb) || empty($back_cover_text)) {
					$results = [
						'title' => $book_title,
						'blurb' => $book_blurb,
						'back_cover_text' => $back_cover_text,
						'error' => 'Failed to generate book'
					];
				} else {

					$prompt = file_get_contents('./prompts/book_prompt_no_function_calling_2.txt');
					$prompt = str_replace('##subject##', $blurb, $prompt);
					$prompt = str_replace('##language##', $language, $prompt);
					$prompt = str_replace('##book_title##', $book_title, $prompt);
					$prompt = str_replace('##book_blurb##', $book_blurb, $prompt);
					$prompt = str_replace('##back_cover_text##', $back_cover_text, $prompt);

					$results = $llmApp->llm_no_tool_call(false, $llm, $prompt, true, $language);

					$results['title'] = $book_title;
					$results['blurb'] = $book_blurb;
					$results['back_cover_text'] = $back_cover_text;
				}
			}

			if ($results['title']!== '' && $results['blurb'] !== '' && $results['back_cover_text'] !== '') {
				$llmApp->createBookStructure($results, $blurb, $model, $language);
				echo json_encode(['success' => true, 'message' => 'Book created successfully', 'data' => $results]);
			} else
			{
				echo json_encode(['success' => false, 'message' => 'Failed to generate book']);
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
		case 'delete_book':
			$book = $_POST['book'];
			$book_dir = "./books/".$book;

			if (is_dir($book_dir)) {
				// Function to delete directory and its contents
				function deleteDir($dirPath)
				{
					if (!is_dir($dirPath)) {
						throw new InvalidArgumentException("$dirPath must be a directory");
					}
					if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
						$dirPath .= '/';
					}
					$files = glob($dirPath . '*', GLOB_MARK);
					foreach ($files as $file) {
						if (is_dir($file)) {
							deleteDir($file);
						} else {
							unlink($file);
						}
					}
					rmdir($dirPath);
				}

				// Delete the book directory
				deleteDir($book_dir);

				echo json_encode(['success' => true]);
			} else {
				echo json_encode(['success' => false, 'message' => 'Book directory not found']);
			}
			break;

		//-----------------------------//
		case 'fetch_initial_data':
			echo json_encode([
				'colorOptions' => $colorOptions,
				'chaptersDirName' => $chaptersDirName,
				'users' => array_column($users, 'username'),
				'currentUser' => $_SESSION['user'],
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
		case 'delete_chapter':

			if (file_exists($chapterFilePath)) {
				// Load the chapter to get the list of attachments
				$chapter = json_decode(file_get_contents($chapterFilePath), true);

				// Delete attachments if they exist
				if (!empty($chapter['files'])) {
					foreach ($chapter['files'] as $file) {
						$attachmentPath = $chaptersDir . '/uploads/' . $file['uploadFilename'];
						if (file_exists($attachmentPath)) {
							unlink($attachmentPath);
						}
					}
				}

				// Delete the chapter file itself
				unlink($chapterFilePath);
				echo json_encode(['success' => true]);
			} else {
				echo json_encode(['success' => false, 'message' => 'File not found']);
			}

			break;

		//-----------------------------//
		case 'save_chapter':
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
					$comments = $existingChapter['comments'] ?? [];
					$files = $existingChapter['files'] ?? [];
					$history = $existingChapter['history'] ?? [];
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
				'comments' => $comments ?? [],
				'files' => $files ?? [],
				'history' => $history ?? []
			];

			if (!empty($_FILES['files']['name'][0])) {
				foreach ($_FILES['files']['name'] as $key => $uploadFilename) {
					$file_tmp = $_FILES['files']['tmp_name'][$key];
					$file_dest = $chaptersDir . '/uploads/' . $uploadFilename;
					if (move_uploaded_file($file_tmp, $file_dest)) {
						$chapter['files'][] = [
							'uploadFilename' => $uploadFilename,
						];
					}
				}
			}

			if ($new_chapter) {
				$chapter = log_history($chapter, 'Created chapter', $_SESSION['user']);
			} else {
				$chapter = log_history($chapter, 'Edited chapter', $_SESSION['user']);
			}

			file_put_contents($chaptersDir . '/' . $chapterFilename, json_encode($chapter, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

			$chapter['chapterFilename'] = $chapterFilename;
			echo json_encode($chapter);
			break;

		//-----------------------------//
		case 'update_chapter_row':
			$row = $_POST['row'];
			$order = $_POST['order'];

			if (file_exists($chapterFilePath)) {
				$chapter = json_decode(file_get_contents($chapterFilePath), true);
				$dontUpdateTime = false;
				if ($chapter['row'] === $row) {
					$dontUpdateTime = true;
				}
				$chapter['row'] = $row;
				$chapter['order'] = $order;
				if (!$dontUpdateTime) {
					$chapter['lastUpdated'] = date('Y-m-d H:i:s');
					$chapter = log_history($chapter, 'Moved chapter to ' . $row, $user);
				}
				file_put_contents($chapterFilePath, json_encode($chapter, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
				echo json_encode($chapter);
			}
			break;
	}

