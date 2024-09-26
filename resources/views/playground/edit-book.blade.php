<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{{__('default.Write Books With AI')}}</title>
	
	<!-- FAVICON AND TOUCH ICONS -->
	<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/images/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" sizes="152x152" href="/images/apple-touch-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/images/apple-touch-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/images/apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
	<link rel="icon" href="/images/apple-touch-icon.png" type="image/x-icon">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<!-- Bootstrap CSS -->
	<link href="/css/bootstrap.css" rel="stylesheet">
	<link href="/css/bootstrap-icons.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="/css/custom.css" rel="stylesheet"> <!-- If you have custom CSS -->
	
	<script>
		{!! $json_translations !!}
			let
		bookData = @json($book);
		let bookSlug = "{{$book_slug}}";
		let colorOptions = @json($colorOptions);
	</script>

</head>
<body>

<main class="py-1">
	
	<div class="container mt-2">
		<h1 style="margin:10px;" class="text-center" id="bookTitle">{{$book['title']}}</h1>
		
		
		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
			</div>
			<div class="card-body modal-content modal-content-color d-flex flex-row">
				<!-- Image Div -->
				<div class="row">
					<div class="col-lg-5 col-12 mb-3">
						<img
							src="{{$book['cover_filename']}}"
							alt="{{__('default.Book Cover')}}"
							style="width: 100%; object-fit: cover;"
							id="bookCover">
						<br>
						<button class="btn btn-primary mb-3 mt-1 w-100" title="{{__('default.Cover Image')}}" id="createCoverBtn">
							<i class="bi bi-image"></i> {{__('default.Cover Image')}}
						</button>
						
						
						<br>
						
						<span style="font-size: 18px;">{{__('default.AI Engines:')}}</span>
						<select id="llmSelect" class="form-select mx-auto mb-1">
							<?php
							if (Auth::user() && Auth::user()->isAdmin()) {
								?>
							<option value="anthropic/claude-3.5-sonnet:beta">{{__('default.Select an AI Engine')}}</option>
							<option value="anthropic/claude-3.5-sonnet:beta">anthropic :: claude-3.5-sonnet</option>
							<option value="openai/gpt-4o-2024-08-06">openai :: gpt-4o</option>
								<?php
							} else {
								?>
							<option value="anthropic/claude-3-haiku:beta">{{__('default.Select an AI Engine')}}</option>
								<?php
							}
							?>
							{{--					<option value="open-ai-gpt-4o">open-ai-gpt-4o</option>--}}
							{{--					<option value="open-ai-gpt-4o-mini">open-ai-gpt-4o-mini</option>--}}
							{{--					<option value="anthropic-haiku">anthropic-haiku</option>--}}
							{{--					<option value="anthropic-sonet">anthropic-sonet</option>--}}
							<option value="anthropic/claude-3-haiku:beta">anthropic :: claude-3-haiku</option>
							<option value="openai/gpt-4o-mini">openai :: gpt-4o-mini</option>
							<option value="google/gemini-flash-1.5">google :: gemini-flash-1.5</option>
							<option value="mistralai/mistral-nemo">mistralai :: mistral-nemo</option>
							{{--					<option value="mistralai/mixtral-8x22b-instruct">mistralai :: mixtral-8x22b</option>--}}
							{{--					<option value="meta-llama/llama-3.1-70b-instruct">meta-llama :: llama-3.1</option>--}}
							{{--					<option value="meta-llama/llama-3.1-8b-instruct">meta-llama :: llama-3.1-8b</option>--}}
							{{--					<option value="microsoft/wizardlm-2-8x22b">microsoft :: wizardlm-2-8x22b</option>--}}
							<option value="nousresearch/hermes-3-llama-3.1-405b">nousresearch :: hermes-3</option>
							{{--					<option value="perplexity/llama-3.1-sonar-large-128k-chat">perplexity :: llama-3.1-sonar-large</option>--}}
							{{--					<option value="perplexity/llama-3.1-sonar-small-128k-chat">perplexity :: llama-3.1-sonar-small</option>--}}
							{{--					<option value="cohere/command-r">cohere :: command-r</option>--}}
						
						</select>
						
						<span style="font-size: 18px;">{{__('default.Number of beats per chapter:')}}</span>
						<select id="beatsPerChapter" class="form-select mx-auto mb-1">
							<option value="2">2</option>
							<option value="3" selected>3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
						</select>
						
						
						<button class="btn btn-primary mb-1 mt-2 w-100" id="generateAllBeatsBtn"
						        title="{{__('default.Write All Beats')}}"><i
								class="bi bi-lightning-charge"></i> {{__('default.Write All Beats')}}
						</button>
						
						<a href="{{route('playground.book-beats',[$book_slug, 'all-chapters','3'])}}"
						   class="btn btn-primary mb-1 mt-1 w-100" title="{{__('default.Edit All Beats')}}">
							<i class="bi bi-pencil-square"></i> {{__('default.Edit All Beats')}}
						</a>
						
						<button class="btn btn-success mb-1 mt-1 w-100" id="exportPdfBtn" title="{{__('default.Export as PDF')}}">
							<i class="bi bi-file-earmark-pdf"></i> {{__('default.Export as PDF')}}
						</button>
						
						<button class="btn btn-success mb-1 mt-1 w-100" id="exportTxtBtn" title="{{__('default.Export as DocX')}}">
							<i class="bi bi-file-earmark-word"></i> {{__('default.Export as DocX')}}
						</button>
						
						<button class="btn btn-primary mb-3 mt-1 w-100" id="showBookStructureBtn"
						        title="{{__('default.Read Book')}}">
							<i class="bi bi-book-half"></i> {{__('default.Read Book')}}
						</button>
						
						<a href="{{route('playground.books-list')}}" class="mb-1 mt-1 btn btn-primary w-100"><i
								class="bi bi-bookshelf"></i> {{__('default.Back to Books')}}</a>
					
					</div>
					<!-- Text Blocks Div -->
					<div class="col-lg-7 col-12">
						<span style="font-size: 16px; font-weight: normal; font-style: italic;"
						      id="bookBlurb">{{$book['blurb']}}</span>
						<br><br>
						<div><span id="backCoverText">{!!str_replace("\n","<br>",$book['back_cover_text'])!!}</span></div>
						<div class="mt-3 mb-3"><span id="bookPrompt"><em>{{__('default.Prompt For Book:')}}</em><br>
								{{$book['prompt'] ?? 'no prompt'}}</span></div>
						<div class="mt-3 mb-3"><span id="bookCharacters"><em>{{__('default.Character Profiles:')}}</em><br>
								{!! str_replace("\n","<br>", $book['character_profiles'] ?? 'no characters')!!}</span></div>
						
						@if (Auth::user())
							@if (Auth::user()->email === $book['owner'] || Auth::user()->name === $book['owner'])
								<button class="btn btn-danger delete-book-btn mt-3 d-inline-block"
								        data-book-id="<?php echo urlencode($book_slug); ?>">{{__('default.Delete Book')}}
								</button>
							@endif
						@endif
					
					</div>
				</div>
			
			</div>
		</div>
		
		<div class="book-chapter-board" id="bookBoard">
		</div>
	
	</div>
</main>


<!-- Modal for Adding/Editing Stories -->
<div class="modal  fade" id="chapterModal" tabindex="-1" aria-labelledby="chapterModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="chapterModalLabel">{{__('default.Edit Chapter')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
			</div>
			<div class="modal-body modal-body-color">
				<form id="chapterForm" enctype="multipart/form-data">
					<input type="hidden" id="chapterFilename">
					<div class="mb-3">
						<label for="chapterName" class="form-label">{{__('default.Name')}}</label>
						<input type="text" class="form-control" id="chapterName" required>
					</div>
					<div class="mb-3">
						<label for="chapterText" class="form-label">{{__('default.Text')}}</label>
						<textarea class="form-control" id="chapterText" rows="3" required></textarea>
					</div>
					
					<div class="mb-3">
						<label for="chapterEvents" class="form-label"> {{__('default.Events')}}</label>
						<input type="text" class="form-control" id="chapterEvents">
					</div>
					<div class="mb-3">
						<label for="chapterPeople" class="form-label">{{__('default.People')}}</label>
						<input type="text" class="form-control" id="chapterPeople">
					</div>
					<div class="mb-3">
						<label for="chapterPlaces" class="form-label"> {{__('default.Places')}}</label>
						<input type="text" class="form-control" id="chapterPlaces">
					</div>
					<div class="mb-3">
						<label for="chapterFromPreviousChapter" class="form-label">{{__('default.Previous Chapter')}}</label>
						<input type="text" class="form-control" id="chapterFromPreviousChapter">
					</div>
					<div class="mb-3">
						<label for="chapterToNextChapter" class="form-label"> {{__('default.Next Chapter')}}</label>
						<input type="text" class="form-control" id="chapterToNextChapter">
					</div>
					
					<div class="mb-3">
						<label class="form-label">{{__('default.Background Color')}}</label>
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
				<button class="btn btn-primary" id="saveChapter">{{__('default.Save Chapter')}}</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> {{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>


<!-- Modal for Creating Book Cover -->
<div class="modal fade" id="createCoverModal" tabindex="-1" aria-labelledby="createCoverModalLabel"
     aria-hidden="true">
	<div class="modal-dialog ">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="createCoverModalLabel">{{__('default.Create Cover')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div class="row">
					<div class="col-md-8">
						<textarea class="form-control" id="coverPrompt" rows="5"
						          placeholder="{{__('default.Enter cover description')}}"></textarea>
						<input type="text" id="coverBookTitle" class="form-control mt-2" placeholder="{{__('default.Book Title')}}">
						<input type="text" id="coverBookAuthor" class="form-control mt-2"
						       placeholder="{{__('default.Book Author')}}">
						<div class="mb-1 form-check mt-2">
							<input type="checkbox" class="form-check-input" id="enhancePrompt" checked>
							<label class="form-check-label" for="enhancePrompt">
								{{__('default.Enhance Prompt')}}
							</label>
						</div>
						<span
							style="font-size: 14px; margin-left:24px;">{{__('default.AI will optimize for creative visuals')}}</span>
					</div>
					<div class="col-md-4">
						<img src="/images/placeholder-cover.jpg" alt="{{__('default.Generated Cover')}}"
						     style="width: 100%; height: auto;"
						     id="generatedCover">
					</div>
				</div>
			</div>
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-primary" id="generateCoverBtn"> {{__('default.Generate')}}</button>
				<button type="button" class="btn btn-success" id="saveCoverBtn" disabled>{{__('default.Save')}}</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> {{__('default.Close')}}</button>
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
				<h5 class="modal-title" id="bookStructureModalLabel">{{__('default.Book Structure')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div id="bookStructureContent"></div>
			</div>
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> {{__('default.Close')}}</button>
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
				<h5 class="modal-title" id="generateAllBeatsModalLabel">{{__('default.Generating Beats for All Chapters')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
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
				<button type="button" class="btn btn-secondary closeAndRefreshButton"> {{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="alertModalLabel">Alsert</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div id="alertModalContent"></div>
			</div>
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>


<script>
	window.currentUserName = "<?php echo htmlspecialchars(Auth::user()->email ?? __('Visitor')); ?>";
</script>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="/js/jquery-3.7.0.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/moment.min.js"></script>

<script src="/js/jspdf.umd.min.js"></script>
<script src="/js/docx.js"></script>


<!-- Your custom scripts -->
<script src="/js/custom-ui.js"></script>
<script src="/js/chapter.js"></script>

</body>
</html>
