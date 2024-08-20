<?php
require_once 'action-session.php';
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Playground - The Book</title>

	<!-- FAVICON AND TOUCH ICONS -->
	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
	<link rel="icon" href="images/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" sizes="152x152" href="images/apple-touch-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="120x120" href="images/apple-touch-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="76x76" href="images/apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
	<link rel="icon" href="images/apple-touch-icon.png" type="image/x-icon">

	<!-- Bootstrap CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-icons.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="css/custom.css" rel="stylesheet"> <!-- If you have custom CSS -->

</head>
<body>
<header>
	<form id="logoutForm" action="action-other-functions.php" method="POST" class="d-none">
		<input type="hidden" name="action" value="logout">
	</form>
	<div class="container-fluid mt-2">
		<a href="#" class="btn btn-danger float-end ms-2" title="Log out"
		   onclick="document.getElementById('logoutForm').submit();"><i class="bi bi-door-open"></i></a>
		<button id="modeToggleBtn" class="btn btn-secondary float-end ms-2">
			<i id="modeIcon" class="bi bi-sun"></i>
		</button>
		<a href="book-details.php?book=<?php echo $_GET['book']; ?>" class="btn btn-primary float-end ms-2"
		   title="add new book"><i class="bi bi-book"></i></a>
	</div>
</header>

<main class="py-4">

	<div class="container mt-2">
		<h2 class="text-center m-4" id="bookTitle">Playground Book</h2>
		<select id="llmSelect" class="form-select w-50 mx-auto mb-4">
			<option value="anthropic/claude-3-haiku:beta">Select a LLM</option>
			<option value="anthropic/claude-3-haiku:beta">anthropic :: claude-3-haiku</option>
			<option value="openai/gpt-4o-mini">openai :: gpt-4o-mini</option>
			<option value="google/gemini-flash-1.5">google :: gemini-flash-1.5</option>
			<option value="mistralai/mistral-nemo">mistralai :: mistral-nemo</option>
			<!--			<option value="mistralai/mixtral-8x22b-instruct">mistralai :: mixtral-8x22b</option>-->
			<!--			<option value="meta-llama/llama-3.1-70b-instruct">meta-llama :: llama-3.1</option>-->
			<!--			<option value="meta-llama/llama-3.1-8b-instruct">meta-llama :: llama-3.1-8b</option>-->
			<!--			<option value="microsoft/wizardlm-2-8x22b">microsoft :: wizardlm-2-8x22b</option>-->
			<option value="nousresearch/hermes-3-llama-3.1-405b">nousresearch :: hermes-3</option>
			<!--			<option value="perplexity/llama-3.1-sonar-large-128k-chat">perplexity :: llama-3.1-sonar-large</option>-->
			<!--			<option value="perplexity/llama-3.1-sonar-small-128k-chat">perplexity :: llama-3.1-sonar-small</option>-->
			<!--			<option value="cohere/command-r">cohere :: command-r</option>-->
		</select>

		<div class="text-center mb-4">
			<button class="btn btn-primary mb-2 me-2" id="showchapterBeatsBtn" title="Show Chapter Beats">
				<i class="bi bi-book-half"></i> Read all beats
			</button>
			<button type="button" class="btn btn-warning mb-2 me-2" id="writeAllBeatsBtn" title="Write All Beats"><i
					class="bi bi-lightning-charge"></i>Write All Beat Contents
			</button>

			<button type="button" class="btn btn-danger mb-2 me-2" id="recreateBeats">Recreate Beats</button>
			<div class="spinner-border d-none me-2 ms-2" style="width:20px; height: 20px;"
			     role="status" id="beat-spinner">
				<span class="visually-hidden">Loading...</span>
			</div>
			<button type="button" class="btn btn-primary d-none mb-2  me-2" id="saveBeatsBtn">Save Beats</button>
		</div>

		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
				<span style="font-size: 18px; font-weight: normal;" class="p-3" id="bookBlurb"></span>
			</div>
			<div class="card-body  modal-content-color">
				<div><em><span id="backCoverText"></span></em></div>
				<hr>
				<em>Name</em>: <span id="chapterName"></span><br>
				<em>Description</em>: <span id="chapterDescription"></span><br>
				<em>Events</em>: <span id="chapterEvents"></span><br>
				<em>People</em>: <span id="chapterPeople"></span><br>
				<em>Places</em>: <span id="chapterPlaces"></span><br>
				<em>Previous Chapter</em>: <span id="chapterFromPrevChapter"></span><br>
				<em>Next Chapter</em>: <span id="chapterToNextChapter"></span><br>
			</div>
		</div>


		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
				<h5 class="modal-title p-2" id="beatModalLabel">Beats</h5>
			</div>
			<div class="card-body  modal-content-color">
				<div id="beatsList"></div>
			</div>
		</div>
	</div>

</main>

<!-- Modal for Writing All Beats -->
<div class="modal fade" id="writeAllBeatsModal" tabindex="-1" aria-labelledby="writeAllBeatsModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="writeAllBeatsModalLabel">Writing All Beats</h5>
				<div class="spinner-border float-start me-2 ms-2" style="width:20px; height: 20px;" role="status"
				">
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
		<div class="modal-body modal-body-color">
			<div class="progress mb-3">
				<div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
				     aria-valuemax="100">0%
				</div>
			</div>
			<div id="writeAllBeatsLog"
			     style="height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;"></div>
		</div>
	</div>
</div>
</div>

<!-- Modal for Viewing Chapter Beats -->
<div class="modal fade" id="chapterBeatsModal" tabindex="-1" aria-labelledby="chapterBeatsModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="chapterBeatsModalLabel">Read Chapter Beats</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div id="chapterBeatsContent"></div>
			</div>
		</div>
	</div>
</div>


<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="js/jquery-3.7.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/moment.min.js"></script>

<!-- Your custom scripts -->
<script src="js/custom-ui.js"></script>
<script src="js/custom.js"></script>

<script src="js/beat.js"></script>

</body>
</html>
