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

<?php
	$booksDir = './books';
	$books = [];
	if ($handle = opendir($booksDir)) {
		while (false !== ($subDir = readdir($handle))) {
			if ($subDir !== '.' && $subDir !== '..') {
				$bookJsonPath = "$booksDir/$subDir/book.json";
				if (file_exists($bookJsonPath)) {
					$bookJson = file_get_contents($bookJsonPath);
					$bookData = json_decode($bookJson, true);
					if ($bookData) {
						$books[] = [
							'id' => $subDir,
							'title' => $bookData['title'],
							'blurb' => $bookData['blurb'],
							'owner' => $bookData['owner'] ?? 'admin',
							'back_cover_text' => $bookData['back_cover_text'],
							'file_time' => filemtime($bookJsonPath)
						];
					}
				}
			}
		}
		closedir($handle);
	}

	usort($books, function ($a, $b) {
		return $b['file_time'] - $a['file_time'];
	});

	//remove books whose owner is not the current user or admin
	$books = array_filter($books, function ($book) {
		global $current_user;
		return $book['owner'] === $current_user || $book['owner'] === 'admin';
	});


?>


<main class="py-4">

	<div class="container mt-2">
		<h1 style="margin:10px;" class="text-center">Playground Book</h1>
		<select id="llmSelect" class="form-select w-50 mx-auto mb-4">
			<option value="anthropic/claude-3-haiku:beta">Select a LLM</option>
			<option value="anthropic/claude-3-haiku:beta">anthropic :: claude-3-haiku</option>
			<option value="openai/gpt-4o-mini">openai :: gpt-4o-mini</option>
			<!--			<option value="google/gemini-flash-1.5">google :: gemini-flash-1.5</option>-->
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

		<form id="logoutForm" action="action-other-functions.php" method="POST" class="d-none">
			<input type="hidden" name="action" value="logout">
		</form>
		<div class="text-center mb-4">
			<a href="#" class="btn btn-danger ms-2" title="Log out" id="logoutBtn"
			   onclick="document.getElementById('logoutForm').submit();"><i class="bi bi-door-open"></i></a>
			<a href="login.php" class="btn btn-secondary ms-2" title="login/sign up"><i class="bi bi-person"></i></a>
			<button id="modeToggleBtn" class="btn btn-secondary ms-2">
				<i id="modeIcon" class="bi bi-sun"></i>
			</button>
			<button type="button" class="btn btn-success ms-2" id="addBookBtn"><i class="bi bi-plus-circle-fill"></i>
			</button>
		</div>

		<div>
			<div class="my-3 d-inline-block">
				Hello <span id="currentUser"></span>,
			</div>
		</div>

		<div class="container mt-5">
			<h1>Books</h1>
			<ul class="list-group">
				<?php foreach ($books as $book): ?>

					<div class="card general-card">
						<div class="card-header modal-header modal-header-color" style="display: block">
							<div style="font-size: 22px; font-weight: normal;"
							     class="p-2"><?php echo htmlspecialchars($book['title']); ?>
								<div style="font-size: 16px;"><em>By</em> <?php echo $book['owner']; ?></div>
							</div>
						</div>
						<div class="card-body modal-content modal-content-color">
							<div class="mb-4"><?php echo htmlspecialchars($book['blurb']); ?></div>
							<div><em><span><?php echo htmlspecialchars($book['back_cover_text']); ?></span></em></div>

						</div>
						<div class="card-footer">
							<a href="book-details.php?book=<?php echo urlencode($book['id']); ?>"
							   class="btn btn-primary mt-3 d-inline-block" style="min-width:20vw;">Read More</a>
							<?php if ($current_user === 'admin' || $current_user === $book['owner']) : ?>
							<button class="btn btn-danger delete-book-btn mt-3 d-inline-block" style="min-width:20vw;"
							        data-book-id="<?php echo urlencode($book['id']); ?>">Delete Book
							</button>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</ul>
		</div>

		<!-- Add Book Modal -->
		<div class="modal modal-lg fade" id="addBookModal" tabindex="-1" role="dialog" aria-labelledby="addBookModalLabel"
		     aria-hidden="true">
			<div class="modal-dialog  modal-dialog-scrollable">
				<div class="modal-content modal-content-color">
					<div class="modal-header modal-header-color">
						<h5 class="modal-title" id="addBookModalLabel">Add Book</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="spinner-border d-none" role="status" id="spinner">
							<span class="visually-hidden">Loading...</span>
						</div>
					</div>
					<div class="modal-body modal-body-color">
						<!--						<div class="mb-3">-->
						<!--							<label for="subject" class="form-label">Subject</label>-->
						<!--							<input type="text" class="form-control" id="subject" name="subject" required>-->
						<!--						</div>-->
						<div class="mb-3">
							<textarea class="form-control" id="blurb" name="blurb" required
							          placeholder="describe your books story, people and events. While you can just say 'A Boy Meets World' the longer and more detailed your blurb is the more creative and unique the writing will be."
							          rows="6"></textarea>
						</div>
						<!--						<div class="mb-3">-->
						<!--							<label for="backCoverText" class="form-label">Back Cover Text</label>-->
						<!--							<textarea class="form-control" id="backCoverText" name="back_cover_text" required></textarea>-->
						<!--						</div>-->
						<div class="mb-3">
							<select class="form-control" id="language" name="language" required>
								<option value="English">English</option>
								<option value="Norwegian">Norwegian</option>
								<option value="Turkish">Turkish</option>
								<!-- Add more languages as needed -->
							</select>
						</div>

						<div class="mb-3">
							<select class="form-control" id="bookStructure" name="language" required>
								<option value="fichtean_curve.txt">Fichtean Curve (3 Acts, 8 Chapters)</option>
								<option value="freytags_pyramid.txt">Freytag's Pyramid (5 Acts, 9 Chapters)</option>
								<option value="heros_journey.txt">Hero's Journey (3 Acts, 12 Chapters)</option>
								<option value="story_clock.txt">Story Clock (4 Acts, 12 Chapters)</option>
								<option value="save_the_cat.txt">Save The Cat (4 Acts, 15 Chapters)</option>
								<option value="dan_harmons_story_circle.txt">Dan Harmon's Story Circle (8 Acts, 15 Chapters)</option>
							</select>
						</div>
						<div class="mb-3" style="font-size: 14px;">
							After clicking the submit button, the AI will first write the book's title and blurb. After that, it will
							start creating 15 chapters for the book. This process may take a few minutes.
						</div>
						<button id="addBookForm" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</div>
		</div>


		<!-- Delete Book Confirmation Modal -->
		<div class="modal fade" id="deleteBookModal" tabindex="-1" role="dialog" aria-labelledby="deleteBookModalLabel"
		     aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content modal-content-color">
					<div class="modal-header modal-header-color">
						<h5 class="modal-title">Confirm Deletion</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body modal-body-color">
						Are you sure you want to delete this book? This action cannot be undone.
					</div>
					<div class="modal-footer modal-footer-color">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-danger" id="confirmDeleteBook">Delete</button>
					</div>
				</div>
			</div>
		</div>


		<!-- jQuery and Bootstrap Bundle (includes Popper) -->
		<script src="js/jquery-3.7.0.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/moment.min.js"></script>
		<script src="js/sortable.min.js"></script>

		<!-- Your custom scripts -->
		<script src="js/custom-ui.js"></script> <!-- If you have custom JS -->

		<script>
			window.currentUserName = "<?php echo htmlspecialchars($current_user ?? 'Visior'); ?>";

				$(document).ready(function () {
				let bookToDelete = null;

				$('.delete-book-btn').click(function () {
					bookToDelete = $(this).data('book-id');
					$('#deleteBookModal').modal('show');
				});

				$('#confirmDeleteBook').click(function () {
					if (bookToDelete) {
						$.ajax({
							url: 'action-book.php',
							type: 'POST',
							data: {
								action: 'delete_book',
								llm: savedLlm,
								book: bookToDelete
							},
							success: function (response) {
								let result = JSON.parse(response);
								if (result.success) {
									location.reload(); // Reload the page to reflect the changes
								} else {
									alert('Error deleting book: ' + result.message);
								}
							},
							error: function () {
								alert('Error occurred while deleting the book.');
							}
						});
					}
					$('#deleteBookModal').modal('hide');
				});

				$("#addBookBtn").click(function () {
					if (window.currentUserName === 'Visitor') {
						//redirect to login page
						window.location.href = 'login.php';
						return;
					}
					$("#addBookModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				});


				$("#addBookForm").on('click', function (event) {
					event.preventDefault();
					let spinner = $('#spinner');
					spinner.removeClass('d-none');
					let formData = new FormData();
					// formData.append('subject', $('#subject').val());
					// formData.append('back_cover_text', $('#backCoverText').val());

					formData.append('blurb', $('#blurb').val());
					formData.append('language', $('#language').val());
					formData.append('action', 'write_book');
					formData.append('llm', savedLlm);
					formData.append('bookStructure', $('#bookStructure').val());

					fetch('action-book.php', {
						method: 'POST',
						body: formData
					})
						.then(response => response.json())
						.then(data => {
							spinner.addClass('d-none');
							if (data.success) {
								location.reload();
							} else {
								alert('Error: ' + data.message);
							}
						})
						.catch(error => {
							spinner.addClass('d-none');
							alert('Error: ' + error);
						});
				});
			});

		</script>

</body>
</html>
