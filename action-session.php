<?php

	session_start();

	//json string for users, Admin password is 123456
	$users =
		[
			['username' => 'Admin', 'password' => '$2y$10$kMdhKRcawdXC9JhayVRhS.mZ/T5Va7K1wfck7FcM6uff1BGfd1qym'],
			['username' => 'Ekim', 'password' => '$2y$10$DIbIGXf43w/583AeGtCtMuiGFJZvNn6CNqatLrYYqOzzDdgeu62Kq'],
		];

	$autoLoginUser = 'Admin'; //leave this empty if you want to allow all users to login

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

	if ($autoLoginUser !== '') {
		$_SESSION['user'] = $autoLoginUser;
		//check the current page to prevent redirect loop
		$current_page = basename($_SERVER['PHP_SELF']);
		if ($current_page === 'login.php') {
			header('Location: book-details.php');
			exit();
		}
	} else if (empty($_SESSION['user'])) {
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

