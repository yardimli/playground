<?php
require_once 'action.php';
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Fiction Fusion - The Book</title>

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
	<form id="logoutForm" action="action.php" method="POST" class="d-none">
		<input type="hidden" name="action" value="logout">
	</form>
	<div class="container-fluid mt-2">
		<a href="#" class="btn btn-danger float-end ms-2" title="Log out"
		   onclick="document.getElementById('logoutForm').submit();"><i class="bi bi-door-open"></i></a>
		<button id="modeToggleBtn" class="btn btn-secondary float-end ms-2">
			<i id="modeIcon" class="bi bi-sun"></i>
		</button>
		<button type="button" class="btn btn-success float-end ms-2" id="addBookBtn"><i class="bi bi-plus-circle-fill"></i>
		</button>
	</div>
</header>

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


?>


<main class="py-4">

	<div class="container mt-2">
		<h1 style="margin:10px;" class="text-center">Fiction Fusion Book</h1>
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
						<div class="card-header modal-header">
							<span style="font-size: 22px; font-weight: normal;" class="p-2"><?php echo htmlspecialchars($book['title']); ?></span>
						</div>
						<div class="card-body modal-content">
							<div class="mb-4"><?php echo htmlspecialchars($book['blurb']); ?></div>
							<div><em><span><?php echo htmlspecialchars($book['back_cover_text']); ?></span></em></div>
						</div>
						<div class="card-footer">
							<a href="index.php?book=<?php echo urlencode($book['id']); ?>" class="btn btn-primary w-25 mt-3 d-inline-block">Read More</a>
							<button class="btn btn-danger delete-book-btn mt-3 w-25 d-inline-block" data-book-id="<?php echo urlencode($book['id']); ?>">Delete Book</button>
						</div>
					</div>
				<?php endforeach; ?>
			</ul>
		</div>

		<!-- Add Book Modal -->
		<div class="modal modal-lg fade" id="addBookModal" tabindex="-1" role="dialog" aria-labelledby="addBookModalLabel"
		     aria-hidden="true">
			<div class="modal-dialog  modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addBookModalLabel">Add Book</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<!--						<div class="mb-3">-->
						<!--							<label for="subject" class="form-label">Subject</label>-->
						<!--							<input type="text" class="form-control" id="subject" name="subject" required>-->
						<!--						</div>-->
						<div class="mb-3">
							<label for="blurb" class="form-label">Blurb</label>
							<textarea class="form-control" id="blurb" name="blurb" required></textarea>
						</div>
						<!--						<div class="mb-3">-->
						<!--							<label for="backCoverText" class="form-label">Back Cover Text</label>-->
						<!--							<textarea class="form-control" id="backCoverText" name="back_cover_text" required></textarea>-->
						<!--						</div>-->
						<div class="mb-3">
							<label for="language" class="form-label">Language</label>
							<select class="form-control" id="language" name="language" required>
								<option value="English">English</option>
								<option value="Turkish">Turkish</option>
								<option value="Spanish">Spanish</option>
								<option value="French">French</option>
								<option value="German">German</option>
								<option value="Traditional Chinese">Chinese</option>
								<!-- Add more languages as needed -->
							</select>
						</div>
						<button id="addBookForm" class="btn btn-primary">Submit</button>
						<div class="spinner-border d-none" role="status" id="spinner">
							<span class="visually-hidden">Loading...</span>
						</div>
					</div>
				</div>
			</div>
		</div>


		<!-- Delete Book Confirmation Modal -->
		<div class="modal fade" id="deleteBookModal" tabindex="-1" role="dialog" aria-labelledby="deleteBookModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="deleteBookModalLabel">Confirm Deletion</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Are you sure you want to delete this book? This action cannot be undone.
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
			$(document).ready(function () {
				let bookToDelete = null;

				$('.delete-book-btn').click(function() {
					bookToDelete = $(this).data('book-id');
					$('#deleteBookModal').modal('show');
				});

				$('#confirmDeleteBook').click(function() {
					if (bookToDelete) {
						$.ajax({
							url: 'action.php',
							type: 'POST',
							data: {
								action: 'delete_book',
								book_id: bookToDelete
							},
							success: function(response) {
								let result = JSON.parse(response);
								if (result.success) {
									location.reload(); // Reload the page to reflect the changes
								} else {
									alert('Error deleting book: ' + result.message);
								}
							},
							error: function() {
								alert('Error occurred while deleting the book.');
							}
						});
					}
					$('#deleteBookModal').modal('hide');
				});

				$("#addBookBtn").click(function () {
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

					fetch('action.php', {
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
