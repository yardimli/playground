<?php
	require_once 'vendor/autoload.php';
	require_once 'llm-call.php';
	require_once 'action-session.php';
	require_once 'action-init.php';


	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$action = $_POST['action'] ?? '';

		$chapterFilename = $_POST['chapterFilename'] ?? null;
		$chapterFilePath = $chaptersDir . '/' . $chapterFilename;
		$user = $_SESSION['user'] ?? '';
		$id = $_POST['id'] ?? null;
		$uploadFilename = $_POST['uploadFilename'] ?? null;
		$USE_LLM = $_ENV['USE_LLM'] ?? 'open-router';

		$llmApp = new LlmFunctions();

		switch ($action) {

			//-----------------------------//
			case 'write_beats':
				$chapterName = $_POST['chapterName'];
				$chapterText = $_POST['chapterText'];
				$chapterEvents = $_POST['chapterEvents'];
				$chapterPeople = $_POST['chapterPeople'];
				$chapterPlaces = $_POST['chapterPlaces'];
				$chapterFromPrevChapter = $_POST['chapterFromPrevChapter'];
				$chapterToNextChapter = $_POST['chapterToNextChapter'];
				$simulated = ($_POST['simulated'] ?? 'false') === 'true';

				// Load the book data
				$bookData = json_decode(file_get_contents($bookJsonPath), true);

				// Load the chapter data
				$chapterData = json_decode(file_get_contents($chapterFilePath), true);

				if ($simulated) {
					$resultData = [
						'beats' => [
							[
								'description' => 'This is the description for Beat 1',
							],
							[
								'description' => 'This is the description for Beat 2',
							],
							[
								'description' => 'This is the description for Beat 3',
							]
						]
					];

					echo json_encode(['success' => true, 'beats' => $resultData['beats']]);

				} else {

					if ($use_llm === 'anthropic-haiku' || $use_llm === 'anthropic-sonet') {
						$schema = file_get_contents('beat_schema_anthropic_1.json');
						$schema = json_decode($schema, true);

						// Prepare the prompt
						$prompt = file_get_contents('beat_prompt_anthropic_1.txt');
						$prompt = str_replace('##title##', $bookData['title'], $prompt);
						$prompt = str_replace('##book_description##', $bookData['description'] ?? '', $prompt);
						$prompt = str_replace('##book_blurb##', $bookData['blurb'], $prompt);
						$prompt = str_replace('##language##', $bookData['language'], $prompt);
						$prompt = str_replace('##act##', $chapterData['row'], $prompt);
						$prompt = str_replace('##chapter##', $chapterName, $prompt);
						$prompt = str_replace('##description##', $chapterText, $prompt);
						$prompt = str_replace('##events##', $chapterEvents, $prompt);
						$prompt = str_replace('##people##', $chapterPeople, $prompt);
						$prompt = str_replace('##places##', $chapterPlaces, $prompt);
						$prompt = str_replace('##prev_chapter##', $chapterFromPrevChapter, $prompt);
						$prompt = str_replace('##next_chapter##', $chapterToNextChapter, $prompt);

						$resultData = $llmApp->function_call($prompt, $schema);

					} else if ($use_llm === 'open-ai-gpt4o' || $use_llm === 'open-ai-gpt4o-mini') {
						$schema = file_get_contents('book_schema_openai_1.json');
						$schema = json_decode($schema, true);

						// Prepare the prompt
						$prompt = file_get_contents('beat_prompt_openai_1.txt');
						$prompt = str_replace('##title##', $bookData['title'], $prompt);
						$prompt = str_replace('##book_description##', $bookData['description'] ?? '', $prompt);
						$prompt = str_replace('##book_blurb##', $bookData['blurb'], $prompt);
						$prompt = str_replace('##language##', $bookData['language'], $prompt);
						$prompt = str_replace('##act##', $chapterData['row'], $prompt);
						$prompt = str_replace('##chapter##', $chapterName, $prompt);
						$prompt = str_replace('##description##', $chapterText, $prompt);
						$prompt = str_replace('##events##', $chapterEvents, $prompt);
						$prompt = str_replace('##people##', $chapterPeople, $prompt);
						$prompt = str_replace('##places##', $chapterPlaces, $prompt);
						$prompt = str_replace('##prev_chapter##', $chapterFromPrevChapter, $prompt);
						$prompt = str_replace('##next_chapter##', $chapterToNextChapter, $prompt);

						$resultData = $llmApp->function_call($prompt, $schema);
					} else {
						// Prepare the prompt
						$prompt = file_get_contents('beat_prompt_no_function_calling_1.txt');
						$prompt = str_replace('##title##', $bookData['title'], $prompt);
						$prompt = str_replace('##book_description##', $bookData['description'] ?? '', $prompt);
						$prompt = str_replace('##book_blurb##', $bookData['blurb'], $prompt);
						$prompt = str_replace('##language##', $bookData['language'], $prompt);
						$prompt = str_replace('##act##', $chapterData['row'], $prompt);
						$prompt = str_replace('##chapter##', $chapterName, $prompt);
						$prompt = str_replace('##description##', $chapterText, $prompt);
						$prompt = str_replace('##events##', $chapterEvents, $prompt);
						$prompt = str_replace('##people##', $chapterPeople, $prompt);
						$prompt = str_replace('##places##', $chapterPlaces, $prompt);
						$prompt = str_replace('##prev_chapter##', $chapterFromPrevChapter, $prompt);
						$prompt = str_replace('##next_chapter##', $chapterToNextChapter, $prompt);

						$resultData = $llmApp->llm_no_stream($prompt, true);
					}

					if (isset($resultData['beats'])) {
						$beats = $resultData['beats'];
					} elseif (is_array($resultData)) {
						$beats = $resultData;
					} else {
						echo json_encode(['success' => false, 'message' => 'Failed to generate beats']);
						break;
					}

					echo json_encode(['success' => true, 'beats' => $beats]);
				}

				break;

			//-----------------------------//
			case 'get_beat_prompt':
				$beatIndex = $_POST['beatIndex'];

				// Load the book data
				$bookData = json_decode(file_get_contents($bookJsonPath), true);

				// Load the chapter data
				$chapterData = json_decode(file_get_contents($chapterFilePath), true);

				// Load the beat prompt template
				$beatPromptTemplate = file_get_contents('beat_text_prompt.txt');
				// Prepare the data for the prompt
				$prevBeat = $beatIndex > 0 ? $chapterData['beats'][$beatIndex - 1] : null;
				$currentBeat = $chapterData['beats'][$beatIndex];
				$nextBeat = $beatIndex < count($chapterData['beats']) - 1 ? $chapterData['beats'][$beatIndex + 1] : null;

				// Replace placeholders in the prompt template
				$beatPrompt = str_replace(
					['##book_title##', '##book_description##', '##chapter_name##', '##chapter_description##', '##prev_beat##', '##current_beat##', '##next_beat##'],
					[$bookData['title'], $bookData['blurb'], $chapterData['name'], $chapterData['short_description'],
						$prevBeat ? json_encode($prevBeat) : 'N/A',
						json_encode($currentBeat),
						$nextBeat ? json_encode($nextBeat) : 'N/A'],
					$beatPromptTemplate
				);

				$resultData = $llmApp->llm_no_stream($beatPrompt, false);

				echo json_encode(['success' => true, 'prompt' => $resultData]);

				break;

			//-----------------------------//
			case 'save_beat_text':

				// Load the book data
				$bookData = json_decode(file_get_contents($bookJsonPath), true);

				// Load the chapter data
				$chapterData = json_decode(file_get_contents($chapterFilePath), true);

				$beatIndex = $_POST['beatIndex'];
				$beatText = $_POST['beatText'];

				$chapterData['beats'][$beatIndex]['beat_text'] = $beatText;

				file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT));

				echo json_encode(['success' => true]);
				break;

			//-----------------------------//
			case 'save_beats':
				$beats = json_decode($_POST['beats'], true);

				if (file_exists($chapterFilePath)) {
					$chapterData = json_decode(file_get_contents($chapterFilePath), true);
					$chapterData['beats'] = $beats;

					if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
						echo json_encode(['success' => true]);
					} else {
						echo json_encode(['success' => false, 'message' => 'Failed to write to file']);
					}
				} else {
					echo json_encode(['success' => false, 'message' => 'Chapter file not found']);
				}
				break;
		}
	}
