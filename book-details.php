<?php
require_once 'action-session.php';
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title><?php echo __e('Playground - The Book'); ?></title>

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

<div class="ham-menu-items" id="ham-menu-items" style="height: 500px;">
	<form id="logoutForm" action="action-other-functions.php" method="POST" class="d-none">
		<input type="hidden" name="action" value="logout">
	</form>

	<div id="modeToggleBtn" class="mb-2 mt-1 btn btn-primary w-100">
		<i id="modeIcon" class="bi bi-sun"></i> <?php echo __e('Toggle Mode'); ?>
	</div>

	<br>
	<span style="font-size: 18px;">AI Engines:</span>
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


	<button class="btn btn-primary mb-1 mt-2 w-100" id="generateAllBeatsBtn" title="<?php echo __e('Write All Beats'); ?>"><i
			class="bi bi-lightning-charge"></i> <?php echo __e('Write All Beats'); ?>
	</button>

	<button class="btn btn-primary mb-3 mt-1 w-100" title="<?php echo __e('Cover Image'); ?>" id="createCoverBtn">
		<i class="bi bi-image"></i> <?php echo __e('Cover Image'); ?>
	</button>

	<button class="btn btn-success mb-1 mt-1 w-100" id="exportPdfBtn" title="<?php echo __e('Export as PDF'); ?>">
		<i class="bi bi-file-earmark-pdf"></i> <?php echo __e('Export as PDF'); ?>
	</button>

	<button class="btn btn-success mb-1 mt-1 w-100" id="exportTxtBtn" title="<?php echo __e('Export as DocX'); ?>">
		<i class="bi bi-file-earmark-word"></i> <?php echo __e('Export as DocX'); ?>
	</button>

	<button class="btn btn-primary mb-3 mt-1 w-100" id="showBookStructureBtn" title="<?php echo __e('View Book'); ?>">
		<i class="bi bi-book-half"></i> <?php echo __e('View Book'); ?>
	</button>

	<a href="index.php" class="mb-1 mt-1 btn btn-primary w-100"><i class="bi bi-bookshelf"></i> <?php echo __e('Back to Books'); ?></a>
	<a href="#" id="logoutBtn" onclick="document.getElementById('logoutForm').submit();"
	   class="mb-1 mt-1 btn btn-danger w-100"><i class="bi bi-door-open"></i> <?php echo __e('Log out'); ?></a>
	<a href="login.php" id="loginBtn" class="mb-1 mt-1 btn btn-primary w-100"><i class="bi bi-person"></i> <?php echo __e('Login/Sign up'); ?></a>

</div>
<div class="ham-menu" id="ham-menu">
	<span class="line line1"></span>
	<span class="line line2"></span>
	<span class="line line3"></span>
</div>

<main class="py-5">

	<div class="container mt-2">
		<h1 style="margin:10px;" class="text-center" id="bookTitle"><?php echo __e('Playground Book'); ?></h1>


		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
				<span style="font-size: 22px; font-weight: normal;" class="p-2" id="bookBlurb"> <?php echo __e('About Book'); ?></span>
			</div>
			<div class="card-body modal-content modal-content-color d-flex flex-row">
				<!-- Image Div -->
				<div class="row">
					<div class="col-lg-5 col-12 mb-3">
						<img
							src="images/placeholder-cover.jpg"
							alt="Book Cover"
							style="width: 100%; object-fit: cover;"
							id="bookCover">
					</div>
					<!-- Text Blocks Div -->
					<div class="col-lg-7 col-12">
						<div><span id="backCoverText"></span></div>
						<div class="mt-3 mb-3"><span id="bookPrompt"></span></div>
						<div class="mt-3 mb-3"><span id="bookCharacters"></span></div>
					</div>
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
					<h5 class="modal-title" id="chapterModalLabel"><?php echo __e('Edit Chapter'); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __e('Close'); ?>"></button>
				</div>
				<div class="modal-body modal-body-color">
					<form id="chapterForm" enctype="multipart/form-data">
						<input type="hidden" id="chapterFilename">
						<div class="mb-3">
							<label for="chapterName" class="form-label"><?php echo __e('Name'); ?></label>
							<input type="text" class="form-control" id="chapterName" required>
						</div>
						<div class="mb-3">
							<label for="chapterText" class="form-label"><?php echo __e('Text'); ?></label>
							<textarea class="form-control" id="chapterText" rows="3" required></textarea>
						</div>

						<div class="mb-3">
							<label for="chapterEvents" class="form-label"> <?php echo __e('Events'); ?></label>
							<input type="text" class="form-control" id="chapterEvents">
						</div>
						<div class="mb-3">
							<label for="chapterPeople" class="form-label"><?php echo __e('People'); ?></label>
							<input type="text" class="form-control" id="chapterPeople">
						</div>
						<div class="mb-3">
							<label for="chapterPlaces" class="form-label"> <?php echo __e('Places'); ?></label>
							<input type="text" class="form-control" id="chapterPlaces">
						</div>
						<div class="mb-3">
							<label for="chapterFromPrevChapter" class="form-label"><?php echo __e('Prev Chapter'); ?></label>
							<input type="text" class="form-control" id="chapterFromPrevChapter">
						</div>
						<div class="mb-3">
							<label for="chapterToNextChapter" class="form-label"> <?php echo __e('Next Chapter'); ?></label>
							<input type="text" class="form-control" id="chapterToNextChapter">
						</div>

						<div class="mb-3">
							<label class="form-label"><?php echo __e('Background Color'); ?></label>
							<div id="colorPalette" class="d-flex flex-wrap">
								<!-- Color buttons will be inserted here dynamically -->
							</div>
						</div>
						<input type="hidden" id="chapterBackgroundColor">
						<input type="hidden" id="chapterTextColor">

						<div id="save_result"></div>
					</form>
				</div>
				<div class="modal-footer modal-footer-color">
					<button class="btn btn-primary" id="saveChapter"><?php echo __e('Save Chapter'); ?></button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> <?php echo __e('Close'); ?></button>
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
					<h5 class="modal-title" id="createCoverModalLabel"><?php echo __e('Create Cover'); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __e('Close'); ?>"></button>
				</div>
				<div class="modal-body modal-body-color">
					<div class="row">
						<div class="col-md-8">
							<textarea class="form-control" id="coverPrompt" rows="5" placeholder="<?php echo __e('Enter cover description'); ?>"></textarea>
							<input type="text" id="coverBookTitle" class="form-control mt-2" placeholder="<?php echo __e('Book Title'); ?>">
							<input type="text" id="coverBookAuthor" class="form-control mt-2" placeholder="<?php echo __e('Book Author'); ?>">
							<div class="mb-1 form-check mt-2">
								<input type="checkbox" class="form-check-input" id="enhancePrompt" checked>
								<label class="form-check-label" for="enhancePrompt">
									<?php echo __e('Enhance Prompt'); ?>
								</label>
							</div>
							<span style="font-size: 14px; margin-left:24px;"><?php echo __e('AI will optimize for creative visuals'); ?></span>
						</div>
						<div class="col-md-4">
							<img src="images/placeholder-cover.jpg" alt="<?php echo __e('Generated Cover'); ?>" style="width: 100%; height: auto;"
							     id="generatedCover">
						</div>
					</div>
				</div>
				<div class="modal-footer modal-footer-color">
					<button type="button" class="btn btn-primary" id="generateCoverBtn"> <?php echo __e('Generate'); ?></button>
					<button type="button" class="btn btn-success" id="saveCoverBtn" disabled><?php echo __e('Save'); ?></button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> <?php echo __e('Close'); ?></button>
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
					<h5 class="modal-title" id="bookStructureModalLabel"><?php echo __e('Book Structure'); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __e('Close'); ?>"></button>
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
					<h5 class="modal-title" id="generateAllBeatsModalLabel"><?php echo __e('Generating Beats for All Chapters'); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __e('Close'); ?>"></button>
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
				<div class="modal-footer modal-footer-color">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> <?php echo __e('Close'); ?></button>
				</div>
			</div>
		</div>
</main>

<script>
	window.currentUserName = "<?php echo htmlspecialchars($current_user ?? __e('Visitor')); ?>";
</script>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="js/jquery-3.7.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/moment.min.js"></script>

<script src="js/jspdf.umd.min.js"></script>
<script src="js/docx.js"></script>


<!-- Your custom scripts -->
<script src="js/custom-ui.js"></script>
<script src="js/chapter.js"></script>

</body>
</html>
