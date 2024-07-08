<?php
	require_once 'vendor/autoload.php';
	require_once 'gpt-call.php';

	session_start();

	$chaptersDirName = 'books/book_demo';

	if (isset($_POST['book']) && !empty($_POST['book'])) {
		$bookDirName = htmlspecialchars($_POST['book']);
		if (is_dir("./books/$bookDirName")) {
			$chaptersDirName = "books/$bookDirName";
		}
	}


	//json string for users, Admin password is 123456
	$users =
		[
			['username' => 'Admin', 'password' => '$2y$10$kMdhKRcawdXC9JhayVRhS.mZ/T5Va7K1wfck7FcM6uff1BGfd1qym'],
			['username' => 'Ekim', 'password' => '$2y$10$DIbIGXf43w/583AeGtCtMuiGFJZvNn6CNqatLrYYqOzzDdgeu62Kq'],
		];

	$autoLoginUser = 'Admin'; //leave this empty if you want to allow all users to login


	$jsonFilePath = $chaptersDirName . '/chapters.json';
	if (file_exists($jsonFilePath)) {
		$jsonContent = file_get_contents($jsonFilePath);
		$rows = json_decode($jsonContent, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			die('Error decoding JSON: ' . json_last_error_msg());
		}
	} else {
		die('Chapters JSON file not found.');
	}
	$defaultRow = $rows[0]['id'];

	$bookJsonPath = $chaptersDirName . '/book.json';
	if (file_exists($bookJsonPath)) {
		$bookJson = file_get_contents($bookJsonPath);
		$bookData = json_decode($bookJson, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			die('Error decoding JSON: ' . json_last_error_msg());
		}
	} else {
		die('Book JSON file not found.');
	}

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

	//------------------DO NOT MODIFY BELOW THIS LINE------------------
	$chaptersDir = __DIR__ . '/' . $chaptersDirName;

	if (!file_exists($chaptersDir)) {
		mkdir($chaptersDir, 0777, true);
	}

	if (!file_exists($chaptersDir . '/uploads')) {
		mkdir($chaptersDir . '/uploads', 0777, true);
	}


	if ($autoLoginUser !== '') {
		$_SESSION['user'] = $autoLoginUser;
		//check the current page to prevent redirect loop
		$current_page = basename($_SERVER['PHP_SELF']);
		if ($current_page === 'login.php') {
			header('Location: index.php');
			exit();
		}
	} else

		if (empty($_SESSION['user'])) {
			$post_action = $_POST['action'] ?? '';
			if ($post_action !== 'login') {
				header('Location: login.php');
				exit();
			}
		}

	function log_history($chapter, $action, $user)
	{
		$chapter['history'][] = [
			'action' => $action,
			'user' => $user,
			'timestamp' => date('Y-m-d H:i:s')
		];
		return $chapter;
	}

	function create_slug($string)
	{
		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
		return $slug;
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$action = $_POST['action'] ?? '';

		$chapterFilename = $_POST['chapterFilename'] ?? null;
		$chapterFilePath = $chaptersDir . '/' . $chapterFilename;
		$user = $_SESSION['user'] ?? '';
		$id = $_POST['id'] ?? null;
		$uploadFilename = $_POST['uploadFilename'] ?? null;
		$use_gpt = $_ENV['USE_GPT'] ?? 'openai';

		$gptApp = new GptFunctions();

		switch ($action) {

			//-----------------------------//
			case 'write_book':
				$language = $_POST['language'] ?? 'English';
				$blurb = $_POST['blurb'] ?? '';


				if ($use_gpt === 'anthropic') {
					$model = $_ENV['ANTHROPIC_MODEL'] ?? 'anthropic';
					$schema = file_get_contents('book_schema_anthropic_1.json');
					$schema = json_decode($schema, true);

					$prompt = file_get_contents('book_prompt_anthropic_1.txt');
					$prompt = str_replace('#subject#', $blurb, $prompt);
					$prompt = str_replace('#language#', $language, $prompt);

					$results = $gptApp->function_call($prompt, $schema, $language);
				} else if ($use_gpt === 'openai') {
					$model = $_ENV['OPENAI_MODEL'] ?? 'openai';
					$schema = file_get_contents('book_schema_openai_1.json');
					$schema = json_decode($schema, true);

					$prompt = file_get_contents('book_prompt_openai_1.txt');
					$prompt = str_replace('#subject#', $blurb, $prompt);
					$prompt = str_replace('#language#', $language, $prompt);

					$results = $gptApp->function_call($prompt, $schema, $language);
				} else {
					$model = $_ENV['OPEN_ROUTER_MODEL'] ?? 'open-router';
					$prompt = file_get_contents('book_prompt_no_function_calling_1.txt');
					$prompt = str_replace('#subject#', $blurb, $prompt);
					$prompt = str_replace('#language#', $language, $prompt);
					$schema = [];

					$results = $gptApp->gpt_no_stream($prompt, true, $language);
				}

				// Process the JSON and create the folder structure
				$gptApp->createBookStructure($results, $blurb, $model, $language);

				echo json_encode(['success' => true, 'message' => 'Book created successfully', 'data' => $results]);
				break;

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

					if ($use_gpt === 'anthropic') {
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

						$resultData = $gptApp->function_call($prompt, $schema);

					} else if ($use_gpt === 'openai') {
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

						$resultData = $gptApp->function_call($prompt, $schema);
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

						$resultData = $gptApp->gpt_no_stream($prompt, true);
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

				$resultData = $gptApp->gpt_no_stream($beatPrompt, false);

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

				echo json_encode( ['success' => true]);
				break;

			//-----------------------------//
			case 'get_book_structure':

				$bookData = json_decode(file_get_contents($bookJsonPath), true);
				$chaptersFile = $chaptersDirName . "/chapters.json";
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
					'bookBackCoverText' => $bookData['back_cover_text'],
					'acts' => $acts
				]);

				break;

			//-----------------------------//
			case 'delete_book':
				$book_id = $_POST['book_id'];
				$book_dir = "./books/$book_id";

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
			case 'get_all_chapters':
				$chapters = [];
				$files = glob($chaptersDirName . '/*.json');
				foreach ($files as $file) {
					$chapterData = json_decode(file_get_contents($file), true);
					//check if $chapterData has "row" key
					if (isset($chapterData['row'])) {
						if (!isset($chapterData['archived'])) {
							$chapterData['archived'] = false;
						}
						if ($chapterData['archived'] === false) {
							$chapterData['chapterFilename'] = basename($file);
							$chapters[] = $chapterData;
						}
					}
				}
				echo json_encode($chapters);
				break;

			//-----------------------------//
			case 'archive_chapter':
				$archived = filter_var($_POST['archived'], FILTER_VALIDATE_BOOLEAN);

				if (file_exists($chapterFilePath)) {
					$chapter = json_decode(file_get_contents($chapterFilePath), true);
					$chapter['archived'] = $archived;

					if ($archived) {
						$chapter = log_history($chapter, 'Archived the chapter', $user);
					} else {
						$chapter = log_history($chapter, 'Unarchived the chapter', $user);
					}

					file_put_contents($chapterFilePath, json_encode($chapter, JSON_PRETTY_PRINT));
					echo json_encode(['success' => true]);
				} else {
					echo json_encode(['success' => false, 'message' => 'Chapter not found']);
				}
				break;

			//-----------------------------//
			case 'delete_comment':

				if (file_exists($chapterFilePath)) {
					$chapter = json_decode(file_get_contents($chapterFilePath), true);
					if (isset($chapter['comments'])) {
						$chapter['comments'] = array_values(array_filter($chapter['comments'], function ($comment) use ($id) {
							return $comment['id'] !== $id;
						}));
						$chapter = log_history($chapter, 'Deleted comment', $user);

						file_put_contents($chapterFilePath, json_encode($chapter, JSON_PRETTY_PRINT));
						echo json_encode(['success' => true]);
					}
				}
				break;

			//-----------------------------//
			case 'delete_file':
				$delete_filename = $_POST['uploadFilename'];
				$delete_filePath = $chaptersDir . '/uploads/' . $delete_filename;

				if (file_exists($delete_filePath)) {
					unlink($delete_filePath); // Delete the file
				}

				$chapterfile_path = $chaptersDir . '/' . $chapterFilename;
				if (file_exists($chapterfile_path)) {
					$chapter = json_decode(file_get_contents($chapterfile_path), true);
					$chapter['files'] = array_values(array_filter($chapter['files'], function ($file) use ($delete_filename) {
						return $file['uploadFilename'] !== $delete_filename;
					}));
					$chapter = log_history($chapter, 'Deleted file ' . $delete_filename, $user);
					file_put_contents($chapterfile_path, json_encode($chapter, JSON_PRETTY_PRINT));
				}

				echo json_encode(['success' => true]);
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
			case 'fetch_all_history':
				$allHistories = [];

				foreach (glob($chaptersDir . '/*.json') as $chapterFilePaths) {
					$chapter = json_decode(file_get_contents($chapterFilePaths), true);
					if (isset($chapter['history'])) {
						foreach ($chapter['history'] as $history) {
							$history['title'] = $chapter['title'];
							$allHistories[] = $history;
						}
					}
				}

				// Sort histories by most recent first
				usort($allHistories, function ($a, $b) {
					return strtotime($b['timestamp']) - strtotime($a['timestamp']);
				});

				echo json_encode($allHistories);
				break;

			//-----------------------------//
			case 'generate_user':
				$username = preg_replace('/\s+/', '', $_POST['username']);
				$username = preg_replace('/[^\w\-]/', '', $username);
				$password = $_POST['password'];

				$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

				$response = [
					'username' => $username,
					'password' => $hashedPassword
				];

				echo "['username' => '" . $username . "', 'password' => '" . $hashedPassword . "'],";
				break;

			//-----------------------------//
			case 'load_stories':
				$stories = [];
				$showArchived = isset($_GET['showArchived']) && $_GET['showArchived'] == 'true';

				if (is_dir($chaptersDir)) {
					$files = scandir($chaptersDir);

					foreach ($files as $chapterFile) {
						if ($chapterFile !== '.' && $chapterFile !== '..' && is_file($chaptersDir . '/' . $chapterFile)) {
							$chapterFilePaths = $chaptersDir . '/' . $chapterFile;
							$chapter = json_decode(file_get_contents($chapterFilePaths), true);

							// Check if json_decode succeeded
							if (json_last_error() === JSON_ERROR_NONE) {

								if (isset($chapter['row'])) {

									$chapter['chapterFilename'] = $chapterFile;
									if (!isset($chapter['archived'])) {
										$chapter['archived'] = false;
									}

									// Check if row is in the rows array; if not, change it to the first row in the array
									$rowExists = false;
									foreach ($rows as $row) {
										if ($row['id'] === $chapter['row']) {
											$rowExists = true;
											break;
										}
									}
									if (!$rowExists) {
										$chapter['row'] = $rows[0]['id'];
									}

									if ($showArchived || !$chapter['archived']) {
										$stories[] = $chapter;
									}
								}
							}
						}
					}
				}

				echo json_encode($stories);
				break;

			//-----------------------------//
			case 'save_comment':
				$text = $_POST['text'];
				$timestamp = date('Y-m-d H:i:s');

				if (file_exists($chapterFilePath)) {
					$chapter = json_decode(file_get_contents($chapterFilePath), true);
					if (!isset($chapter['comments'])) {
						$chapter['comments'] = [];
					}

					if (empty($id)) {
						$id = uniqid();
						$chapter['comments'][] = [
							'id' => $id,
							'text' => $text,
							'user' => $user,
							'timestamp' => $timestamp,
						];
						$chapter = log_history($chapter, 'Added comment', $user);
						$isNew = true;
					} else {
						foreach ($chapter['comments'] as &$comment) {
							if ($comment['id'] === $id) {
								$comment['text'] = $text;
								$comment['timestamp'] = $timestamp;
								$chapter = log_history($chapter, 'Edited comment', $user);
								break;
							}
						}
						$isNew = false;
					}

					file_put_contents($chapterFilePath, json_encode($chapter, JSON_PRETTY_PRINT));
					echo json_encode([
						'id' => $id,
						'text' => $text,
						'user' => $user,
						'timestamp' => $timestamp,
						'chapterFilename' => $chapterFilename,
						'isNew' => $isNew,
					]);
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
				$archived = false;

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
						$archived = $existingChapter['archived'] ?? false;
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
					'history' => $history ?? [],
					'archived' => $archived
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

				file_put_contents($chaptersDir . '/' . $chapterFilename, json_encode($chapter, JSON_PRETTY_PRINT));

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
					file_put_contents($chapterFilePath, json_encode($chapter, JSON_PRETTY_PRINT));
					echo json_encode($chapter);
				}
				break;

			case 'save_beats':
				$beats = json_decode($_POST['beats'], true);

				if (file_exists($chapterFilePath)) {
					$chapterData = json_decode(file_get_contents($chapterFilePath), true);
					$chapterData['beats'] = $beats;

					if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT))) {
						echo json_encode(['success' => true]);
					} else {
						echo json_encode(['success' => false, 'message' => 'Failed to write to file']);
					}
				} else {
					echo json_encode(['success' => false, 'message' => 'Chapter file not found']);
				}
				break;


			//-----------------------------//
			case 'login':
				$username = $_POST['username'] ?? '';
				$password = $_POST['password'] ?? '';

				$userFound = false;

				foreach ($users as $user) {
					if ($user['username'] === $username && password_verify($password, $user['password'])) {
						$_SESSION['user'] = $username;
						$userFound = true;
						header('Location: index.php');
						exit();
					}
				}

				if (!$userFound) {
					$error = "Invalid username or password";
					header("Location: login.php?error=" . urlencode($error));
					exit();
				}
				break;

			//-----------------------------//
			case 'logout':
				session_destroy();
				header('Location: login.php');
				exit();
				break;
		}
	}
