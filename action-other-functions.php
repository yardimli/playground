<?php
	require_once 'vendor/autoload.php';
	require_once 'llm-call.php';

	require_once 'action-session.php';
	require_once 'action-init.php';

	$llm = $_POST['llm'] ?? 'anthropic/claude-3-haiku:beta';

	switch ($action) {

		//-----------------------------//
		case 'delete_comment':
			if ($bookData['owner'] !== $current_user) {
				echo json_encode(['success' => false, 'message' => 'You are not the owner of this book.']);
				break;
			}

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
			if ($bookData['owner'] !== $current_user) {
				echo json_encode(['success' => false, 'message' => 'You are not the owner of this book.']);
				break;
			}

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
				if (isset($chapter['row'])) {
					foreach ($chapter['history'] as $history) {
						$history['title'] = $chapter['name'];
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
		case 'save_comment':
			if ($bookData['owner'] !== $current_user) {
				echo json_encode(['success' => false, 'message' => 'You are not the owner of this book.']);
				break;
			}

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
					'success' => true,
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

			if (empty($username) || empty($password)) {
				$error = "Username and password are required";
				header("Location: login.php?error=" . urlencode($error));
				exit();
			}

			$userFound = false;

			// Load users from the JSON file
			$jsonFile = 'users_x.json';

			if (file_exists($jsonFile)) {
				$json = file_get_contents($jsonFile);
				$users = json_decode($json, true);
			} else {
				$users = [];
			}

			$userFound = false;
			foreach ($users as &$user) {
				if ($user['username'] === $username) {
					if (password_verify($password, $user['password'])) {
						$_SESSION['user'] = $username;
						$userFound = true;
						header('Location: index.php');
						exit();
					} else {
						$userFound = true; // Username exists but password is incorrect
						break;
					}
				}
			}
			unset($user); // Break the reference with the last element

			if (!$userFound) {
				// Add new user to the user array if username doesn't exist or password is incorrect
				$newUser = [
					'username' => $username,
					'password' => password_hash($password, PASSWORD_BCRYPT)
				];
				$users[] = $newUser;

				// Save the updated users list to the JSON file
				file_put_contents($jsonFile, json_encode($users));

				$_SESSION['user'] = $username;
				header('Location: index.php');
			} else {
				$error = "Invalid username or password";
				header("Location: login.php?error=" . urlencode($error));
			}
			break;

		//-----------------------------//
		case 'logout':
			session_destroy();
			header('Location: login.php');
			exit();
			break;
	}
