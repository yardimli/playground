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
		<button class="btn btn-info float-start me-2" id="showAllHistoryBtn" title="show all history"><i
				class="bi bi-clock-history"></i></button>
		<button class="btn btn-secondary float-start me-2" data-bs-toggle="modal" data-bs-target="#addUserModal"
		        title="add user modal"><i class="bi bi-person"></i></button>
		<button class="btn btn-info float-start me-2" id="toggleArchivedBtn"><i class="bi bi-archive"></i></button>
		<button class="btn btn-warning float-start me-2" id="generateAllBeatsBtn" title="Generate All Beats"><i
				class="bi bi-lightning-charge"></i></button>

		<button class="btn btn-primary float-start me-2" id="showBookStructureBtn" title="Show Book Structure">
			<i class="bi bi-book-half"></i>
		</button>

		<button class="btn btn-success float-start me-2" id="exportPdfBtn" title="Export as PDF">
			<i class="bi bi-file-earmark-pdf"></i>
		</button>

		<a href="#" class="btn btn-danger float-end ms-2" title="Log out"
		   onclick="document.getElementById('logoutForm').submit();"><i class="bi bi-door-open"></i></a>
		<button id="modeToggleBtn" class="btn btn-secondary float-end ms-2">
			<i id="modeIcon" class="bi bi-sun"></i>
		</button>
		<button class="btn btn-primary float-end ms-2" id="addChapterBtn" title="add new chapter"><i
				class="bi bi-plus-circle-fill"></i></button>
		<a href="index.php" class="btn btn-primary float-end ms-2" title="add new book"><i class="bi bi-book"></i></a>
	</div>
</header>

<main class="py-4">

	<div class="container mt-2">
		<h1 style="margin:10px;" class="text-center">Fiction Fusion Book</h1>
		<div>
			<div class="my-3 d-inline-block">
				Hello <span id="currentUser"></span>,
			</div>
		</div>

		<div class="card general-card">
			<div class="card-header modal-header">
				<span style="font-size: 22px; font-weight: normal;" class="p-2" id="bookTitle"></span>
			</div>
			<div class="card-body modal-content">
				<div id="bookBlurb" class="mb-4"></div>
				<div><em><span id="bookBackCoverText"></span></em></div>
			</div>
		</div>

		<div class="kanban-board" id="kanbanBoard">
		</div>

	</div>

	<!-- Modal for Adding/Editing Stories -->
	<div class="modal modal-lg fade" id="chapterModal" tabindex="-1" aria-labelledby="chapterModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog  modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="chapterModalLabel">Edit Chapter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
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
				<div class="modal-footer">
					<button class="btn btn-primary" id="saveChapter">Save Chapter</button>
					<button class="btn btn-secondary" id="showCommentModal">Add Comment</button>
					<button class="btn btn-primary" id="showBeatModal">View Beats</button>
					<button class="btn btn-info" id="showHistoryModal">View History</button>
					<button type="button" class="btn btn-secondary  me-auto" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-danger " id="deleteChapterBtn">Delete</button>
				</div>
			</div>
		</div>
	</div>


	<!-- Modal for Adding User -->
	<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addUserModalLabel">Add User</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="addUserForm">
						<div class="mb-3">
							<label for="userName" class="form-label">Username</label>
							<input type="text" class="form-control" id="userName" required>
						</div>
						<div class="mb-3">
							<label for="userPassword" class="form-label">Password</label>
							<input type="password" class="form-control" id="userPassword" required>
						</div>
						<button type="button" class="btn btn-primary" id="generateUser">Generate</button>
					</form>
					<div class="mt-3">
						<pre id="userJsonOutput"></pre>
						<button type="button" class="btn btn-secondary" id="copyUserJson">Copy</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Adding Comment -->
	<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="commentModalLabel">Add Comment</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
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
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					Are you sure you want to delete this chapter? This action cannot be undone.
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Viewing History -->
	<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="historyModalLabel">History</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" style="max-height: 400px; overflow: auto;">
					<div id="historyList"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Viewing All History -->
	<div class="modal fade" id="allHistoryModal" tabindex="-1" aria-labelledby="allHistoryModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-dialog-scrollable modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="allHistoryModalLabel">All History</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" style="max-height: 400px; overflow: auto;">
					<div id="allHistoryList"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Viewing Beats -->
	<div class="modal fade modal-lg" id="beatModal" tabindex="-1" aria-labelledby="beatModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="beatModalLabel">View Beats</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" style="min-height: 300px;">
					<div id="beatsList"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger me-auto" id="createBeats">Create Beats</button>
					<div class="spinner-border d-none" role="status" id="beat-spinner">
						<span class="visually-hidden">Loading...</span>
					</div>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" id="saveBeatsBtn">Save Beats</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Generating All Beats -->
	<div class="modal fade" id="generateAllBeatsModal" tabindex="-1" aria-labelledby="generateAllBeatsModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="generateAllBeatsModalLabel">Generating Beats for All Chapters</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
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

	<!-- Modal for Viewing Book Structure -->
	<div class="modal fade" id="bookStructureModal" tabindex="-1" aria-labelledby="bookStructureModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="bookStructureModalLabel">Book Structure</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div id="bookStructureContent"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Beat Details and Writing -->
	<div class="modal fade" id="beatDetailModal" tabindex="-1" aria-labelledby="beatDetailModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="beatDetailModalLabel">Beat Details</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p id="beatDescription"></p>
					<button id="writeBeatTextBtn" class="btn btn-primary mt-3">Write Beat Text</button>
					<div id="beatTextArea" class="mt-3" style="display: none;">
						<textarea id="beatText" class="form-control" rows="10"></textarea>
						<button id="saveBeatTextBtn" class="btn btn-success mt-3">Save Beat Text</button>
					</div>
				</div>
			</div>
		</div>
	</div>

</main>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="js/jquery-3.7.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/sortable.min.js"></script>

<script src="js/jspdf.umd.min.js"></script>



<!-- Your custom scripts -->
<script src="js/custom-ui.js"></script> <!-- If you have custom JS -->
<script src="js/custom.js"></script> <!-- If you have custom JS -->
</body>
</html>
