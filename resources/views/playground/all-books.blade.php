<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{{__('default.Playground - The Book - Index')}}</title>

	<!-- FAVICON AND TOUCH ICONS -->
	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
	<link rel="icon" href="images/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" sizes="152x152" href="images/apple-touch-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="120x120" href="images/apple-touch-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="76x76" href="images/apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
	<link rel="icon" href="images/apple-touch-icon.png" type="image/x-icon">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<!-- Bootstrap CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-icons.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="css/custom.css" rel="stylesheet"> <!-- If you have custom CSS -->

	<script>
		{!! $json_translations !!}
	</script>
</head>
<body>

	<div class="ham-menu-items" id="ham-menu-items" style="height: 250px;">

		
		{{__('default.AI Engines:')}}
		<select id="llmSelect" class="form-select mx-auto mb-3">
			<?php
				if (Auth::user() && Auth::user()->isAdmin()) {
					?>
					<option value="anthropic/claude-3.5-sonnet:beta">{{__('default.Select an AI Engine')}}</option>
					<option value="anthropic/claude-3.5-sonnet:beta">anthropic :: claude-3.5-sonnet</option>
					<option value="openai/gpt-4o">openai :: gpt-4o</option>
					<?php
				} else {
					?>
					<option value="anthropic/claude-3-haiku:beta">{{__('default.Select an AI Engine')}}</option>
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

		<button id="addBookBtn"  class="mb-4 mt-1 btn btn-success w-100"><i class="bi bi-plus-circle-fill"></i> {{__('default.Add Book')}}</button>

	</div>
<div class="ham-menu" id="ham-menu">
	<span class="line line1"></span>
	<span class="line line2"></span>
	<span class="line line3"></span>
</div>
<div id="modeToggleBtn">
	<i id="modeIcon" class="bi bi-sun"></i> {{__('default.Toggle Mode')}}
</div>

<main class="py-2">


	<div class="container mt-1">
		<h1 style="margin:10px;" class="text-center">{{__('default.Playground Book')}}</h1>

		<div class="container mt-1">

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
										<a href="{{route('playground.book-details',$book['id'])}}"><img
												src="{{$book['cover_filename']}}"
												alt="{{__('default.Book Cover')}}"
												style="width: 100%; object-fit: cover;"
												id="bookCover"></a>

										<div class="mt-3 mb-3"><em><span><?php echo htmlspecialchars($book['blurb']); ?></span></em></div>

										<a href="{{route('playground.book-details',$book['id'])}}"
										   class="btn btn-primary mt-3 d-inline-block">{{__('default.Read More')}}</a>
										<?php if (Auth::user() && (Auth::user()->isAdmin() || Auth::user()->email === $book['owner'])) : ?>
											<button class="btn btn-danger delete-book-btn mt-3 d-inline-block"
											        data-book-id="<?php echo urlencode($book['id']); ?>">{{__('default.Delete Book')}}
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
								<span style="font-size: 16px;"><em>{{__('default.Author:')}}</em> <?php echo $book['owner']; ?></span>
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
						<h5 class="modal-title" id="addBookStepOneModalLabel">{{__('default.Add Book')}}</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
						<div class="spinner-border d-none" role="status" id="spinner">
							<span class="visually-hidden">{{__('default.Loading...')}}</span>
						</div>
					</div>
					<div class="modal-body modal-body-color">
						<div class="mb-3">
							<textarea class="form-control" id="user_blurb" name="user_blurb" required
							          placeholder="{{__('default.describe your books story, people and events. While you can just say \'A Boy Meets World\' the longer and more detailed your blurb is the more creative and unique the writing will be.')}}"
							          rows="6"></textarea>
						</div>
						<div class="mb-3">
							<select class="form-control" id="language" name="language" required>
								<option value="{{__('default.English')}}" <?php if (__('default.Default Language') === __('default.English')) { echo " SELECTED"; } ?>>{{__('default.English')}}</option>
								<option value="{{__('default.Norwegian')}}" <?php if (__('default.Default Language') === __('default.Norwegian')) { echo " SELECTED"; } ?>>{{__('default.Norwegian')}}</option>
								<option value="{{__('default.Turkish')}}" <?php if (__('default.Default Language') === __('default.Turkish')) { echo " SELECTED"; } ?>>{{__('default.Turkish')}}</option>
							</select>
						</div>

						<div class="mb-3">
							<select class="form-control" id="bookStructure" name="bookStructure" required>
								<option value="{{__('default.fichtean_curve.txt')}}">{{__('default.Fichtean Curve (3 Acts, 8 Chapters)')}}</option>
								<option value="{{__('default.freytags_pyramid.txt')}}">{{__('default.Freytag\'s Pyramid (5 Acts, 9 Chapters)')}}</option>
								<option value="{{__('default.heros_journey.txt')}}">{{__('default.Hero\'s Journey (3 Acts, 12 Chapters)')}}</option>
								<option value="{{__('default.story_clock.txt')}}">{{__('default.Story Clock (4 Acts, 12 Chapters)')}}</option>
								<option value="{{__('default.save_the_cat.txt')}}">{{__('default.Save The Cat (4 Acts, 15 Chapters)')}}</option>
								<option value="{{__('default.dan_harmons_story_circle.txt')}}">{{__('default.Dan Harmon\'s Story Circle (8 Acts, 15 Chapters)')}}</option>
							</select>
						</div>

						<div class="mb-3" style="font-size: 14px;" id="hint_1">
							{{__('default.After clicking the submit button, the AI will first write the book\'s title and blurb and characters. You\'ll need to confirm the characters before the AI writes the book.')}}
						</div>
						<div class="mb-3 d-none alert alert-primary" style="font-size: 16px;" id="hint_2">
							{{__('default.Please verify the title, blurb the back cover text of the book and the characters of the story.')}}
							<br>
							{{__('default.After clicking the submit button, The AI will start creating all the chapters for the book. This process may take a few minutes.')}}
						</div>

						<div id="book_details" class="d-none">
							<div class="mb-3">
								<label for="book_title" class="form-label" style="font-size: 12px; margin-bottom:4px;">{{__('default.Book Title')}}</label>
								<input type="text" class="form-control" id="book_title" name="book_title" required>
							</div>

							<div class="mb-3">
								<label for="book_blurb" class="form-label" style="font-size: 12px; margin-bottom:4px;">{{__('default.Book Blurb')}}</label>
								<textarea class="form-control" id="book_blurb" name="book_blurb" required rows="6"></textarea>
							</div>

							<div class="mb-3">
								<label for="back_cover_text" class="form-label" style="font-size: 12px; margin-bottom:4px;">{{__('default.Back Cover Text')}}</label>
								<textarea class="form-control" id="back_cover_text" name="back_cover_text" required rows="6"></textarea>
							</div>

							<div class="mb-3">
								<label for="character_profiles" class="form-label" style="font-size: 12px; margin-bottom:4px;">{{__('default.Character Profiles')}}</label>
								<textarea class="form-control" id="character_profiles" name="character_profiles" required
								          rows="6"></textarea>
							</div>


						</div>
						<button id="addBookStepOneBtn" class="btn btn-primary">{{__('default.Submit')}}</button>
						<button id="addBookStepTwoBtn" class="btn btn-primary d-none">{{__('default.Submit')}}</button>
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
						<h5 class="modal-title">{{__('default.Confirm Deletion')}}</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
					</div>
					<div class="modal-body modal-body-color">
						{{__('default.Are you sure you want to delete this book? This action cannot be undone.')}}
					</div>
					<div class="modal-footer modal-footer-color">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Cancel')}}</button>
						<button type="button" class="btn btn-danger" id="confirmDeleteBook">{{__('default.Delete')}}</button>
					</div>
				</div>
			</div>
		</div>


		<!-- jQuery and Bootstrap Bundle (includes Popper) -->
		<script src="/js/jquery-3.7.0.min.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/moment.min.js"></script>

		<!-- Your custom scripts -->
		<script src="/js/custom-ui.js"></script> <!-- If you have custom JS -->

		<script>
			window.currentUserName = "<?php echo htmlspecialchars(Auth::user()->email ?? __('default.Visitor')); ?>";

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
									alert('{{__('default.Error deleting book:')}} ' + result.message);
								}
							},
							error: function () {
								alert('{{__('default.Error occurred while deleting the book.')}}');
							}
						});
					}
					$('#deleteBookModal').modal('hide');
				});

				$("#addBookBtn").click(function () {
					if (window.currentUserName === '{{__('default.Visitor')}}') {
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
								alert('{{__('default.Book created successfully. Please check the new fields before continuing.')}}');
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
									characterProfiles += (profile.name || '{{__('default.name')}}') + '\n' + (profile.description || '') + '\n\n';
								});

								$('#character_profiles').val(characterProfiles);
							} else {
								alert('{{__('default.Error:')}} ' + data.message);
							}
						},
						error: function(xhr, status, error) {
							spinner.addClass('d-none');
							alert('{{__('default.Error:')}} ' + error);
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
								alert('{{__('default.Book created successfully.')}}');
								location.reload();
							} else {
								alert('{{__('default.Error:')}} ' + data.message);
							}
						},
						error: function(xhr, status, error) {
							spinner.addClass('d-none');
							alert('{{__('default.Error:')}} ' + error);
						}
					});
				});

			});

		</script>

</body>
</html>
