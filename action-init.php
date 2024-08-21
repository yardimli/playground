<?php
	$action = $_POST['action'] ?? '';
	$llm = $_POST['llm'] ?? 'anthropic/claude-3-haiku:beta';


	if ($action === 'write_book' || $action === 'login' || $action === 'register' || $action === 'logout' || $action === '') {

	} else {
		if (isset($_POST['book']) && !empty($_POST['book'])) {
			$bookDirName = htmlspecialchars($_POST['book']);
			if (is_dir("./books/$bookDirName")) {
				$chaptersDirName = "books/$bookDirName";
			}
		} else {
			die('Book not found.');
		}

		$jsonFilePath = $chaptersDirName . '/acts.json';
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

