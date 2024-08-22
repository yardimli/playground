<?php
	$action = $_POST['action'] ?? '';
	$llm = $_POST['llm'] ?? 'anthropic/claude-3-haiku:beta';


	if ($action === 'write_book_character_profiles' || $action === 'write_book' || $action === 'login' || $action === 'register' || $action === 'logout' || $action === '') {

	} else if ($action === 'delete_book') {

		if (isset($_POST['book']) && !empty($_POST['book'])) {
			$bookDirName = htmlspecialchars($_POST['book']);
			if (is_dir("./books/$bookDirName")) {
				$chaptersDirName = "books/$bookDirName";
			}
		} else {
			echo json_encode(['success' => false, 'message' => 'Book not found.']);
			exit();
		}

		$bookJsonPath = $chaptersDirName . '/book.json';
		if (file_exists($bookJsonPath)) {
			$bookJson = file_get_contents($bookJsonPath);
			$bookData = json_decode($bookJson, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				die('Error decoding JSON: ' . json_last_error_msg());
			}
			$bookData['owner'] = $bookData['owner'] ?? 'admin';
		} else {
			echo json_encode(['success' => false, 'message' => 'Book JSON file not found.']);
			exit();
		}

		if ($bookData['owner'] !== $current_user) {
			echo json_encode(['success' => false, 'message' => 'You are not the owner of this book.']);
			exit();
		}

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

	} else {
		if (isset($_POST['book']) && !empty($_POST['book'])) {
			$bookDirName = htmlspecialchars($_POST['book']);
			if (is_dir("./books/$bookDirName")) {
				$chaptersDirName = "books/$bookDirName";
			}
		} else {
			echo json_encode(['success' => false, 'message' => 'Book not found.']);
			exit();
		}

		$jsonFilePath = $chaptersDirName . '/acts.json';
		if (file_exists($jsonFilePath)) {
			$jsonContent = file_get_contents($jsonFilePath);
			$rows = json_decode($jsonContent, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				echo json_encode(['success' => false, 'message' => 'Error decoding JSON: ' . json_last_error_msg()]);
				exit();
			}
		} else {
			echo json_encode(['success' => false, 'message' => 'Chapters JSON file not found.']);
			exit();
		}
		$defaultRow = $rows[0]['id'];

		$bookJsonPath = $chaptersDirName . '/book.json';
		if (file_exists($bookJsonPath)) {
			$bookJson = file_get_contents($bookJsonPath);
			$bookData = json_decode($bookJson, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				die('Error decoding JSON: ' . json_last_error_msg());
			}
			$bookData['owner'] = $bookData['owner'] ?? 'admin';
		} else {
			die('Book JSON file not found.');
		}


		$chaptersDir = __DIR__ . '/' . $chaptersDirName;

		if (!file_exists($chaptersDir)) {
			mkdir($chaptersDir, 0777, true);
		}

		if (!file_exists($chaptersDir . '/uploads')) {
			mkdir($chaptersDir . '/uploads', 0777, true);
		}


		$chaptersDir = __DIR__ . '/' . $chaptersDirName;

		if (!file_exists($chaptersDir)) {
			mkdir($chaptersDir, 0777, true);
		}

		if (!file_exists($chaptersDir . '/uploads')) {
			mkdir($chaptersDir . '/uploads', 0777, true);
		}

		$chapterFilename = $_POST['chapterFilename'] ?? null;
		$chapterFilePath = $chaptersDir . '/' . $chapterFilename;
	}


	$id = $_POST['id'] ?? null;
	$uploadFilename = $_POST['uploadFilename'] ?? null;
	$use_llm = $_ENV['USE_LLM'] ?? 'open-router';

	$llmApp = new LlmFunctions();

