<?php

	$files_list = [
		'gpt-call.php',
		'index.php',
		'js/custom.js',
		'css/custom.css',
		'book_prompt_anthropic_1.txt',
		'book_schema_anthropic_1.json',
		'beat_prompt_anthropic_1.txt',
		'beat_schema_anthropic_1.json',
	];

	for ($i = 0; $i < count($files_list); $i++) {
		$source = file_get_contents($files_list[$i]);
		$source = preg_replace('/\r\n|\r|\n/', ' ', $source);
		echo $files_list[$i] . '<br>';
		echo htmlentities($source);
		echo '<br><br>';
	}


