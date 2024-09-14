<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{{__('default.Playground - The Book - Beats')}}</title>
	
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
	<link href="/css/bootstrap.min.css" rel="stylesheet">
	<link href="/css/bootstrap-icons.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="/css/custom.css" rel="stylesheet"> <!-- If you have custom CSS -->
	
	<script>
		{!! $json_translations !!}
	</script>
	
	<script>
		let bookData = @json($book);
		let bookSlug = "{{$book_slug}}";
		let chapterFilename = "{{$chapter_file . '.json'}}";
		let previousChapter =  @json($previous_chapter);
		let currentChapter =  @json($current_chapter);
		let nextChapter =  @json($next_chapter);
	</script>

</head>
<body>

<main class="py-1">
	
	<div class="container mt-2">
		<h2 class="text-center m-4" id="bookTitle">{{$book['title']}}</h2>
		
		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
				<span style="font-size: 18px; font-weight: normal;" class="p-3" id="bookBlurb">{{$book['blurb']}}</span>
			</div>
			<div class="card-body  modal-content-color">
				<div><em><span id="backCoverText">{{$book['back_cover_text']}}</span></em></div>
				<hr>
				<em>{{__('default.Name')}}</em>: <span id="chapterName">{{$current_chapter['name'] ?? 'noname'}}</span><br>
				<em>{{__('default.Description')}}</em>: <span
					id="chapterDescription">{{$current_chapter['short_description'] ?? 'no description'}}</span><br>
				<em>{{__('default.Events')}}</em>: <span id="chapterEvents">{{$current_chapter['events'] ?? 'no events'}}</span><br>
				<em>{{__('default.People')}}</em>: <span id="chapterPeople">{{$current_chapter['people'] ?? 'no people'}}</span><br>
				<em>{{__('default.Places')}}</em>: <span id="chapterPlaces">{{$current_chapter['places'] ?? 'no places'}}</span><br>
				<em>{{__('default.Previous Chapter')}}</em>: <span
					id="chapterFromPreviousChapter">{{$previous_chapter_text}}</span><br>
				<em>{{__('default.Next Chapter')}}</em>: <span id="chapterToNextChapter">{{$next_chapter_text}}</span><br>
			</div>
		</div>
		
		
		<div class="card general-card">
			<div class="card-body  modal-content-color">
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
				
				<button type="button" class="btn btn-primary mt-2 mb-1 w-100" id="writeAllBeatsBtn"
				        title="{{__('default.Write All Beats')}}"><i
						class="bi bi-lightning-charge"></i> {{__('default.Write All Beat Contents')}}
				</button>
				
				<button type="button" class="btn btn-success mt-1 mb-1 w-100" id="recreateBeats"><i
						class="bi bi-pencil"></i> {{__('default.Recreate Beats')}}</button>
				
				<button type="button" class="btn btn-primary mt-2 mb-1 w-100"
				        id="saveBeatsBtn"><i
						class="bi bi-file-earmark-text-fill"></i> {{__('default.Save Beats')}}</button>
				
				
				<button class="btn btn-primary mb-3 mt-1 w-100" id="showchapterBeatsBtn"
				        title="{{__('default.Read all beats')}}">
					<i class="bi bi-book-half"></i> {{__('default.Read all beats')}}
				</button>
				
				<a href="{{route('playground.book-details', $book_slug)}}" class="btn btn-primary mb-1 mt-1 w-100"
				   title="{{__('default.Back to Chapters')}}"><i class="bi bi-book"></i> {{__('default.Back to Chapters')}}</a>
				<a href="{{route('playground.books-list')}}" class="mb-1 mt-1 btn btn-primary w-100"><i
						class="bi bi-bookshelf"></i> {{__('default.Back to Books')}}</a>
			</div>
		</div>
		
		
		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
				<h5 class="modal-title p-2" id="beatModalLabel">{{__('default.Beats')}}</h5>
			</div>
			<div class="card-body modal-content-color">
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
				<h5 class="modal-title" id="writeAllBeatsModalLabel">{{__('default.Writing All Beats')}}</h5>
				<div class="spinner-border float-start me-2 ms-2 d-none" style="width:20px; height: 20px;" role="status"
				     id="beatSpinner">
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
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
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-secondary closeAndRefreshButton">{{__('default.Close')}}</button>
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
				<h5 class="modal-title" id="chapterBeatsModalLabel">{{__('default.Read Chapter Beats')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div id="chapterBeatsContent"></div>
			</div>
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Close')}}</button>
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


<div id="fullScreenOverlay" class="d-none">
	<div class="overlay-content">
		<div class="spinner-border text-light" role="status">
			<span class="visually-hidden">{{__('Loading...')}}</span>
		</div>
		<p class="mt-3 text-light">{{__('default.Processing your request. This may take a few minutes...')}}</p>
	</div>
</div>


<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="/js/jquery-3.7.0.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/moment.min.js"></script>

<!-- Your custom scripts -->
<script src="/js/custom-ui.js"></script>
<script src="/js/beat.js"></script>

</body>
</html>
