<?php
require_once 'action-session.php';
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title><?php echo __e('Playground - The Book - Index'); ?></title>

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

	<?php echo write_js_translations(); ?>
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
							'cover_filename' => $bookData['cover_filename'] ?? '',
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
		return $book['owner'] === $current_user || $book['owner'] === 'admin' || $current_user === 'admin';
	});


?>

	<div class="ham-menu-items" id="ham-menu-items">
		<form id="logoutForm" action="action-other-functions.php" method="POST" class="d-none">
			<input type="hidden" name="action" value="logout">
		</form>

		<div id="modeToggleBtn" class="mb-1 mt-1 btn btn-primary w-100">
			<i id="modeIcon" class="bi bi-sun"></i> <?php echo __e('Toggle Mode'); ?>
		</div>
		<a href="#" id="logoutBtn" onclick="document.getElementById('logoutForm').submit();" class="mb-1 mt-1 btn btn-primary w-100"><i class="bi bi-door-open"></i> <?php echo __e('Log out'); ?></a>

		<button id="addBookBtn"  class="mb-3 mt-1 btn btn-success w-100"><i class="bi bi-plus-circle-fill"></i> <?php echo __e('Add Book'); ?></button>

		<?php echo __e('AI Engines:'); ?>
		<select id="llmSelect" class="form-select mx-auto mb-1">
			<?php
				if ($current_user === 'admin' || $current_user === 'deniz') {
					?>
					<option value="anthropic/claude-3.5-sonnet:beta"><?php echo __e('Select an AI Engine'); ?></option>
					<option value="anthropic/claude-3.5-sonnet:beta">anthropic :: claude-3.5-sonnet</option>
					<option value="openai/gpt-4o">openai :: gpt-4o</option>
					<?php
				} else {
					?>
					<option value="anthropic/claude-3-haiku:beta"><?php echo __e('Select an AI Engine'); ?></option>
					<?php
				}
			?>
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

		<a href="login.php" id="loginBtn" class="mb-1 mt-1 btn btn-primary w-100"><i class="bi bi-person"></i> <?php echo __e('Login/Sign up'); ?></a>

	</div>
<div class="ham-menu" id="ham-menu">
	<span class="line line1"></span>
	<span class="line line2"></span>
	<span class="line line3"></span>
</div>

<main class="py-2">


	<div class="container mt-1">
		<h1 style="margin:10px;" class="text-center"><?php echo __e('Playground Book'); ?></h1>

		<div class="container mt-1">
			<div class="my-2 d-inline-block">
				<?php echo __e('Hello'); ?> <span id="currentUser"></span>,
			</div>

			<div class="row">
				<?php foreach ($books as $book):
					?>
					<div class="col-md-6 col-lg-6 col-12">

						<div class="card general-card">
							<div class="card-header modal-header modal-header-color" style="display: block;">
								<div style="font-size: 22px; font-weight: normal;" class="pt-2 ps-2 pe-2">
									<span id="bookBlurb"><?php echo htmlspecialchars($book['title']); ?></span>
								</div>
							</div>
							<div class="card-body modal-content modal-content-color d-flex flex-row">
								<!-- Image Div -->
								<div class="row">
									<div class="col-lg-5 col-12 mb-3">
										<a href="book-details.php?book=<?php echo urlencode($book['id']); ?>"><img
												src="<?php echo(($book['cover_filename'] !== '') ? 'ai-images/' . $book['cover_filename'] : 'images/placeholder-cover.jpg'); ?>"
												alt="<?php echo __e('Book Cover'); ?>"
												style="width: 100%; object-fit: cover;"
												id="bookCover"></a>

										<div class="mt-3 mb-3"><em><span><?php echo htmlspecialchars($book['blurb']); ?></span></em></div>

										<a href="book-details.php?book=<?php echo urlencode($book['id']); ?>"
										   class="btn btn-primary mt-3 d-inline-block"><?php echo __e('Read More'); ?></a>
										<?php if ($current_user === 'admin' || $current_user === $book['owner']) : ?>
											<button class="btn btn-danger delete-book-btn mt-3 d-inline-block"
											        data-book-id="<?php echo urlencode($book['id']); ?>"><?php echo __e('Delete Book'); ?>
											</button>
										<?php endif; ?>
									</div>
									<!-- Text Blocks Div -->
									<div class="col-lg-7 col-12">
										<div><span><?php echo htmlspecialchars($book['back_cover_text']); ?></span></div>
									</div>
								</div>
							</div>
							<div class="card-footer modal-footer-color">
								<span style="font-size: 16px;"><em><?php echo __e('Author:'); ?></em> <?php echo $book['owner']; ?></span>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Add Book Modal -->
		<div class="modal modal-lg fade" id="addBookStepOneModal" tabindex="-1" role="dialog"
		     aria-labelledby="addBookStepOneModalLabel"
		     aria-hidden="true">
			<div class="modal-dialog  modal-dialog-scrollable">
				<div class="modal-content modal-content-color">
					<div class="modal-header modal-header-color">
						<h5 class="modal-title" id="addBookStepOneModalLabel"><?php echo __e('Add Book'); ?></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __e('Close'); ?>"></button>
						<div class="spinner-border d-none" role="status" id="spinner">
							<span class="visually-hidden"><?php echo __e('Loading...'); ?></span>
						</div>
					</div>
					<div class="modal-body modal-body-color">
						<div class="mb-3">
							<textarea class="form-control" id="user_blurb" name="user_blurb" required
							          placeholder="<?php echo __e('describe your books story, people and events. While you can just say \'A Boy Meets World\' the longer and more detailed your blurb is the more creative and unique the writing will be.'); ?>"
							          rows="6"></textarea>
						</div>
						<div class="mb-3">
							<select class="form-control" id="language" name="language" required>
								<option value="English"><?php echo __e('English'); ?></option>
								<option value="Norwegian"><?php echo __e('Norwegian'); ?></option>
								<option value="Turkish"><?php echo __e('Turkish'); ?></option>
							</select>
						</div>

						<div class="mb-3">
							<select class="form-control" id="bookStructure" name="bookStructure" required>
								<option value="fichtean_curve.txt"><?php echo __e('Fichtean Curve (3 Acts, 8 Chapters)'); ?></option>
								<option value="freytags_pyramid.txt"><?php echo __e('Freytag\'s Pyramid (5 Acts, 9 Chapters)'); ?></option>
								<option value="heros_journey.txt"><?php echo __e('Hero\'s Journey (3 Acts, 12 Chapters)'); ?></option>
								<option value="story_clock.txt"><?php echo __e('Story Clock (4 Acts, 12 Chapters)'); ?></option>
								<option value="save_the_cat.txt"><?php echo __e('Save The Cat (4 Acts, 15 Chapters)'); ?></option>
								<option value="dan_harmons_story_circle.txt"><?php echo __e('Dan Harmon\'s Story Circle (8 Acts, 15 Chapters)'); ?></option>
							</select>
						</div>

						<div class="mb-3" style="font-size: 14px;" id="hint_1">
							<?php echo __e('After clicking the submit button, the AI will first write the book\'s title and blurb and characters. You\'ll need to confirm the characters before the AI writes the book.'); ?>
						</div>
						<div class="mb-3 d-none alert alert-primary" style="font-size: 16px;" id="hint_2">
							<?php echo __e('Please verify the title, blurb the back cover text of the book and the characters of the story.'); ?>
							<br>
							<?php echo __e('After clicking the submit button, The AI will start creating all the chapters for the book. This process may take a few minutes.'); ?>
						</div>

						<div id="book_details" class="d-none">
							<div class="mb-3">
								<label for="book_title" class="form-label" style="font-size: 12px; margin-bottom:4px;"><?php echo __e('Book Title'); ?></label>
								<input type="text" class="form-control" id="book_title" name="book_title" required>
							</div>

							<div class="mb-3">
								<label for="book_blurb" class="form-label" style="font-size: 12px; margin-bottom:4px;"><?php echo __e('Book Blurb'); ?></label>
								<textarea class="form-control" id="book_blurb" name="book_blurb" required rows="6"></textarea>
							</div>

							<div class="mb-3">
								<label for="back_cover_text" class="form-label" style="font-size: 12px; margin-bottom:4px;"><?php echo __e('Back Cover Text'); ?></label>
								<textarea class="form-control" id="back_cover_text" name="back_cover_text" required rows="6"></textarea>
							</div>

							<div class="mb-3">
								<label for="character_profiles" class="form-label" style="font-size: 12px; margin-bottom:4px;"><?php echo __e('Character Profiles'); ?></label>
								<textarea class="form-control" id="character_profiles" name="character_profiles" required
								          rows="6"></textarea>
							</div>


						</div>
						<button id="addBookStepOneBtn" class="btn btn-primary"><?php echo __e('Submit'); ?></button>
						<button id="addBookStepTwoBtn" class="btn btn-primary d-none"><?php echo __e('Submit'); ?></button>
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
						<h5 class="modal-title"><?php echo __e('Confirm Deletion'); ?></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __e('Close'); ?>"></button>
					</div>
					<div class="modal-body modal-body-color">
						<?php echo __e('Are you sure you want to delete this book? This action cannot be undone.'); ?>
					</div>
					<div class="modal-footer modal-footer-color">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __e('Cancel'); ?></button>
						<button type="button" class="btn btn-danger" id="confirmDeleteBook"><?php echo __e('Delete'); ?></button>
					</div>
				</div>
			</div>
		</div>


		<!-- jQuery and Bootstrap Bundle (includes Popper) -->
		<script src="js/jquery-3.7.0.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/moment.min.js"></script>

		<!-- Your custom scripts -->
		<script src="js/custom-ui.js"></script> <!-- If you have custom JS -->

		<script>
			window.currentUserName = "<?php echo htmlspecialchars($current_user ?? __e('Visitor')); ?>";

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
									alert('<?php echo __e('Error deleting book:'); ?> ' + result.message);
								}
							},
							error: function () {
								alert('<?php echo __e('Error occurred while deleting the book.'); ?>');
							}
						});
					}
					$('#deleteBookModal').modal('hide');
				});

				$("#addBookBtn").click(function () {
					if (window.currentUserName === '<?php echo __e('Visitor'); ?>') {
						//redirect to login page
						window.location.href = 'login.php';
						return;
					}
					$("#addBookStepOneModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				});

				$("#addBookStepOneBtn").on('click', function (event) {
					event.preventDefault();
					let spinner = $('#spinner');
					spinner.removeClass('d-none');

					console.log("user_blurb value:", $('#user_blurb').val());

					$.ajax({
						url: 'action-book.php',
						type: 'POST',
						data: {
							user_blurb: $('#user_blurb').val(),
							language: $('#language').val(),
							bookStructure: $('#bookStructure').val(),
							action: 'write_book_character_profiles',
							llm: savedLlm
						},
						dataType: 'json',
						success: function(data) {
							console.log(data);
							spinner.addClass('d-none');
							if (data.success) {
								alert('<?php echo __e('Book created successfully. Please check the new fields before continuing.'); ?>');
								$('#addBookStepOneBtn').addClass('d-none');
								$('#hint_1').addClass('d-none');
								$('#hint_2').removeClass('d-none');

								$('#book_details').removeClass('d-none');
								$('#addBookStepTwoBtn').removeClass('d-none');
								$('#book_title').val(data.data.title);
								$('#book_blurb').val(data.data.blurb);
								$('#back_cover_text').val(data.data.back_cover_text);

								let characterProfiles = '';
								data.data.character_profiles.forEach(function (profile) {
									characterProfiles += (profile.name || '<?php echo __e('name'); ?>') + '\n' + (profile.description || '') + '\n\n';
								});

								$('#character_profiles').val(characterProfiles);
							} else {
								alert('<?php echo __e('Error:'); ?> ' + data.message);
							}
						},
						error: function(xhr, status, error) {
							spinner.addClass('d-none');
							alert('<?php echo __e('Error:'); ?> ' + error);
						}
					});
				});

				$("#addBookStepTwoBtn").on('click', function (event) {
					event.preventDefault();
					let spinner = $('#spinner');
					spinner.removeClass('d-none');

					$.ajax({
						url: 'action-book.php',
						type: 'POST',
						data: {
							user_blurb: $('#user_blurb').val(),
							language: $('#language').val(),
							bookStructure: $('#bookStructure').val(),
							book_title: $('#book_title').val(),
							book_blurb: $('#book_blurb').val(),
							back_cover_text: $('#back_cover_text').val(),
							character_profiles: $('#character_profiles').val(),
							action: 'write_book',
							llm: savedLlm
						},
						dataType: 'json',
						success: function(data) {
							spinner.addClass('d-none');
							if (data.success) {
								alert('<?php echo __e('Book created successfully.'); ?>');
								location.reload();
							} else {
								alert('<?php echo __e('Error:'); ?> ' + data.message);
							}
						},
						error: function(xhr, status, error) {
							spinner.addClass('d-none');
							alert('<?php echo __e('Error:'); ?> ' + error);
						}
					});
				});

			});

		</script>

</body>
</html>
