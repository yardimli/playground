<?php
	require_once 'vendor/autoload.php';
	require_once 'llm-call.php';

	require_once 'action-session.php';
	require_once 'action-init.php';

	$llm = $_POST['llm'] ?? 'anthropic/claude-3-haiku:beta';

	switch ($action) {

		//-----------------------------//
		case 'login':
			$username = $_POST['username'] ?? '';
			$password = $_POST['password'] ?? '';

			if (empty($username) || empty($password)) {
				$error = __e('Username and password are required');
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
				$error = __e('Invalid username or password');
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
