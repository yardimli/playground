<?php
	require_once 'vendor/autoload.php';
	require_once 'llm-call.php';

	require_once 'action-session.php';
	require_once 'action-init.php';

	switch ($action) {

		//-----------------------------//
		case 'delete_comment':

			if (file_exists($chapterFilePath)) {
				$chapter = json_decode(file_get_contents($chapterFilePath), true);
				if (isset($chapter['comments'])) {
					$chapter['comments'] = array_values(array_filter($chapter['comments'], function ($comment) use ($id) {
						return $comment['id'] !== $id;
					}));
					$chapter = log_history($chapter, 'Deleted comment', $user);

					file_put_contents($chapterFilePath, json_encode($chapter, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
				file_put_contents($chapterfile_path, json_encode($chapter, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
			}

			echo json_encode(['success' => true]);
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

				file_put_contents($chapterFilePath, json_encode($chapter, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
		case 'login':
			$username = $_POST['username'] ?? '';
			$password = $_POST['password'] ?? '';

			$userFound = false;

			foreach ($users as $user) {
				if ($user['username'] === $username && password_verify($password, $user['password'])) {
					$_SESSION['user'] = $username;
					$userFound = true;
					header('Location: book-details.php');
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
