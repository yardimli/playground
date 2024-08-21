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
<header style="min-height: 30px;">
	<form id="logoutForm" action="action-other-functions.php" method="POST" class="d-none">
		<input type="hidden" name="action" value="logout">
	</form>
	<div class="container-fluid mt-2">
		<a href="#" class="btn btn-danger float-end ms-2" title="Log out" id="logoutBtn"
		   onclick="document.getElementById('logoutForm').submit();"><i class="bi bi-door-open"></i></a>
		<button id="modeToggleBtn" class="btn btn-secondary float-end ms-2">
			<i id="modeIcon" class="bi bi-sun"></i>
		</button>
		<a href="index.php" class="btn btn-primary float-end ms-2" title="add new book"><i class="bi bi-book"></i></a>
	</div>
</header>

<main class="py-4">

	<div class="container mt-2">
		<h1 style="margin:10px;" class="text-center" id="bookTitle">Playground Book</h1>
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
			<button class="btn btn-info  me-2" id="showAllHistoryBtn" title="show all history"><i
					class="bi bi-clock-history"></i></button>
			<button class="btn btn-warning me-2" title="Create Cover" id="createCoverBtn">
				<i class="bi bi-image"></i>
			</button>
			<button class="btn btn-warning  me-2" id="generateAllBeatsBtn" title="Generate All Beats"><i
					class="bi bi-lightning-charge"></i></button>

			<button class="btn btn-primary me-2" id="showBookStructureBtn" title="Show Book Structure">
				<i class="bi bi-book-half"></i>
			</button>

			<button class="btn btn-success me-2" id="exportPdfBtn" title="Export as PDF">
				<i class="bi bi-file-earmark-pdf"></i>
			</button>
		</div>

		<div>
			<div class="my-3 d-inline-block">
				Hello <span id="currentUser"></span>,
			</div>
		</div>

		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
				<span style="font-size: 22px; font-weight: normal;" class="p-2" id="bookBlurb">About Book</span>
			</div>
			<div class="card-body modal-content modal-content-color d-flex flex-row">
				<!-- Image Div -->
				<div class="flex-shrink-0 d-none" id="bookCoverContainer">
					<img src="" alt="Book Cover" style="width: 100%; height: 100%; max-width: 300px; min-height: 300px; object-fit: cover;" id="bookCover">
				</div>
				<!-- Text Blocks Div -->
				<div class="flex-grow-1 ms-3">
					<div><em><span id="backCoverText"></span></em></div>
					<div><span id="bookPrompt"></span></div>
				</div>
			</div>
		</div>

		<div class="book-chapter-board" id="bookBoard">
		</div>

	</div>

	<!-- Modal for Adding/Editing Stories -->
	<div class="modal modal-lg fade" id="chapterModal" tabindex="-1" aria-labelledby="chapterModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog  modal-dialog-scrollable">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="chapterModalLabel">Edit Chapter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body modal-body-color">
					<form id="chapterForm" enctype="multipart/form-data">
						<input type="hidden" id="chapterFilename">
						<div class="mb-3">
							<label for="chapterName" class="form-label">Name</label>
							<input type="text" class="form-control" id="chapterName" required>
						</div>
						<div class="mb-3">
							<label for="chapterText" class="form-label">Text</label>
							<textarea class="form-control" id="chapterText" rows="3" required></textarea>
						</div>

						<div class="mb-3">
							<label for="chapterEvents" class="form-label">Events</label>
							<input type="text" class="form-control" id="chapterEvents">
						</div>
						<div class="mb-3">
							<label for="chapterPeople" class="form-label">People</label>
							<input type="text" class="form-control" id="chapterPeople">
						</div>
						<div class="mb-3">
							<label for="chapterPlaces" class="form-label">Places</label>
							<input type="text" class="form-control" id="chapterPlaces">
						</div>
						<div class="mb-3">
							<label for="chapterFromPrevChapter" class="form-label">Prev Chapter</label>
							<input type="text" class="form-control" id="chapterFromPrevChapter">
						</div>
						<div class="mb-3">
							<label for="chapterToNextChapter" class="form-label">Next Chapter</label>
							<input type="text" class="form-control" id="chapterToNextChapter">
						</div>

						<div class="mb-3">
							<label class="form-label">Background Color</label>
							<div id="colorPalette" class="d-flex flex-wrap">
								<!-- Color buttons will be inserted here dynamically -->
							</div>
						</div>
						<input type="hidden" id="chapterBackgroundColor">
						<input type="hidden" id="chapterTextColor">
						<div class="mb-3">
							<label for="chapterFiles" class="form-label">Upload Files</label>
							<input type="file" class="form-control" id="chapterFiles" name="chapterFiles[]" multiple>
						</div>
						<div id="save_result"></div>
					</form>
					<div class="comments-section">
						<hr>
						<h5>Comments</h5>
						<div id="commentsList"></div>
					</div>
					<div class="upload-files-section">
						<hr>
						<h5>Files</h5>
						<div id="UploadFilesList" class="row"></div>
					</div>
				</div>
				<div class="modal-footer modal-footer-color">
					<button class="btn btn-primary" id="saveChapter">Save Chapter</button>
					<button class="btn btn-secondary" id="showCommentModal">Add Comment</button>
					<button class="btn btn-info" id="showHistoryModal">View History</button>
					<button type="button" class="btn btn-secondary  me-auto" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-danger " id="deleteChapterBtn">Delete</button>
				</div>
			</div>
		</div>
	</div>


	<!-- Modal for Creating Book Cover -->
	<div class="modal fade" id="createCoverModal" tabindex="-1" aria-labelledby="createCoverModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="createCoverModalLabel">Create Cover</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body modal-body-color">
					<div class="row">
						<div class="col-md-8">
							<textarea class="form-control" id="coverPrompt" rows="5" placeholder="Enter cover description"></textarea>
							<input type="text" id="coverBookTitle" class="form-control mt-2" placeholder="Book Title">
							<input type="text" id="coverBookAuthor" class="form-control mt-2" placeholder="Book Author">
							<div class="mb-1 form-check mt-2">
								<input type="checkbox" class="form-check-input" id="enhancePrompt" checked>
								<label class="form-check-label" for="enhancePrompt">
									Enhance Prompt
								</label>
							</div>
							<span style="font-size: 14px; margin-left:24px;">AI will optimize for creative visuals</span>
						</div>
						<div class="col-md-4">
							<div id="coverImagePlaceholder"
							     style="width: 200px; height: 300px; background-color: #f0f0f0; display: flex; justify-content: center; align-items: center; margin: 0 auto;">
								<span>Cover Image</span>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer modal-footer-color">
					<button type="button" class="btn btn-primary" id="generateCoverBtn">Generate</button>
					<button type="button" class="btn btn-success" id="saveCoverBtn" disabled>Save</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Adding Comment -->
	<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="commentModalLabel">Add Comment</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body modal-body-color">
					<form id="commentForm">
						<input type="hidden" id="commentChapterFilename">
						<input type="hidden" id="commentId">
						<div class="mb-3">
							<label for="commentText" class="form-label">Comment</label>
							<textarea class="form-control" id="commentText" rows="3" required></textarea>
						</div>
						<button type="submit" class="btn btn-primary">Save Comment</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Confirmation Modal for Deleting Chapter -->
	<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body modal-body-color">
					Are you sure you want to delete this chapter? This action cannot be undone.
				</div>
				<div class="modal-footer modal-footer-color">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Viewing History -->
	<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="historyModalLabel">History</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body modal-body-color" style="max-height: 400px; overflow: auto;">
					<div id="historyList"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Viewing All History -->
	<div class="modal fade" id="allHistoryModal" tabindex="-1" aria-labelledby="allHistoryModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-dialog-scrollable modal-lg">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="allHistoryModalLabel">All History</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body modal-body-color" style="max-height: 400px; overflow: auto;">
					<div id="allHistoryList"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Viewing Book Structure -->
	<div class="modal fade" id="bookStructureModal" tabindex="-1" aria-labelledby="bookStructureModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-scrollable">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="bookStructureModalLabel">Book Structure</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body modal-body-color">
					<div id="bookStructureContent"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Generating All Beats -->
	<div class="modal fade" id="generateAllBeatsModal" tabindex="-1" aria-labelledby="generateAllBeatsModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-scrollable">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="generateAllBeatsModalLabel">Generating Beats for All Chapters</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body modal-body-color">
					<div class="progress mb-3">
						<div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
						     aria-valuemax="100">0%
						</div>
					</div>
					<div id="generateAllBeatsLog"
					     style="height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;"></div>
				</div>
			</div>
		</div>
	</div>
</main>

<script>
	window.currentUserName = "<?php echo htmlspecialchars($current_user ?? 'Visior'); ?>";
</script>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="js/jquery-3.7.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/sortable.min.js"></script>

<script src="js/jspdf.umd.min.js"></script>

<!-- Your custom scripts -->
<script src="js/custom-ui.js"></script>
<script src="js/utility.js"></script>
<script src="js/custom.js"></script>

<script src="js/chapter.js"></script>
<script src="js/comment.js"></script>

</body>
</html>
