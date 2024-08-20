<?php
	require_once 'vendor/autoload.php';
	require_once 'llm-call.php';
	require_once 'action-session.php';
	require_once 'action-init.php';


	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$action = $_POST['action'] ?? '';
		$llm = $_POST['llm'] ?? 'anthropic/claude-3-haiku:beta';



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
						$schema = file_get_contents('./prompts/beat_schema_anthropic_1.json');
						$schema = json_decode($schema, true);

						// Prepare the prompt
						$prompt = file_get_contents('./prompts/beat_prompt_anthropic_1.txt');
						$prompt = str_replace('##book_title##', $bookData['title'], $prompt);
						$prompt = str_replace('##back_cover_text##', $bookData['description'] ?? '', $prompt);
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

						$resultData = $llmApp->function_call($llm, $prompt, $schema);

					} else if ($use_llm === 'open-ai-gpt-4o' || $use_llm === 'open-ai-gpt-4o-mini') {
						$schema = file_get_contents('./prompts/book_schema_openai_1.json');
						$schema = json_decode($schema, true);

						// Prepare the prompt
						$prompt = file_get_contents('./prompts/beat_prompt_openai_1.txt');
						$prompt = str_replace('##book_title##', $bookData['title'], $prompt);
						$prompt = str_replace('##back_cover_text##', $bookData['description'] ?? '', $prompt);
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

						$resultData = $llmApp->function_call($llm, $prompt, $schema);
					} else {
						// Prepare the prompt
						$prompt = file_get_contents('./prompts/beat_prompt_no_function_calling_1.txt');
						$prompt = str_replace('##book_title##', $bookData['title'], $prompt);
						$prompt = str_replace('##back_cover_text##', $bookData['description'] ?? '', $prompt);
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

						$resultData = $llmApp->llm_no_tool_call(false, $llm, $prompt, true);
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
			case 'write_beat_text':
				$beatIndex = (int)$_POST['beatIndex'];
				$currentBeatDescription = $_POST['currentBeatDescription'] ?? '';


				// Load the beat prompt template
				$beatPromptTemplate = file_get_contents('./prompts/beat_text_prompt.txt');

				$book_title = $_POST['book_title'];
				$book_blurb = $_POST['book_blurb'];
				$back_cover_text = $_POST['back_cover_text'];
				$language = $_POST['language'];
				$act = $_POST['act'];
				$chapter_title = $_POST['chapter_title'];
				$chapter_description = $_POST['chapter_description'];
				$chapter_events = $_POST['chapter_events'];
				$chapter_people = $_POST['chapter_people'];
				$chapter_places = $_POST['chapter_places'];
				$prev_beat_summaries = $_POST['prev_beat_summaries'];
				$last_beat = $_POST['last_beat'];
				$current_beat = $_POST['current_beat'];
				$next_beat = $_POST['next_beat'];

				$beatPrompt = str_replace(
					['##book_title##', '##book_blurb##', '##back_cover_text##', '##language##', '##act##', '##chapter##', '##description##', '##events##', '##people##', '##places##', '##prev_beat_summaries##', '##last_beat##', '##current_beat##', '##next_beat##'],
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
						$prev_beat_summaries,
						$last_beat,
						$current_beat,
						$next_beat
					],
					$beatPromptTemplate
				);

				$resultData = $llmApp->llm_no_tool_call(false, $llm, $beatPrompt, false);

				echo json_encode(['success' => true, 'prompt' => $resultData]);

				break;

			//-----------------------------//
			case 'write_beat_text_summary':
				$currentBeatDescription = $_POST['currentBeatDescription'] ?? '';
				$currentBeatText = $_POST['currentBeatText'] ?? '';

				// Load the beat prompt template
				$beatPromptTemplate = file_get_contents('./prompts/beat_text_summary.txt');

				$book_title = $_POST['book_title'];
				$book_blurb = $_POST['book_blurb'];
				$back_cover_text = $_POST['back_cover_text'];
				$language = $_POST['language'];
				$act = $_POST['act'];
				$chapter_title = $_POST['chapter_title'];
				$chapter_description = $_POST['chapter_description'];
				$chapter_events = $_POST['chapter_events'];
				$chapter_people = $_POST['chapter_people'];
				$chapter_places = $_POST['chapter_places'];

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

				break;

			//-----------------------------//
			case 'save_beat_text':

				// Load the book data
				$bookData = json_decode(file_get_contents($bookJsonPath), true);

				// Load the chapter data
				$chapterData = json_decode(file_get_contents($chapterFilePath), true);

				$beatIndex = $_POST['beatIndex'];
				$beatDescription = $_POST['beatDescription'];
				$beatText = $_POST['beatText'];
				$beatTextSummary = $_POST['beatTextSummary'];

				$chapterData['beats'][$beatIndex]['description'] = $beatDescription;
				$chapterData['beats'][$beatIndex]['beat_text'] = $beatText;
				$chapterData['beats'][$beatIndex]['beat_text_summary'] = $beatTextSummary;

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
