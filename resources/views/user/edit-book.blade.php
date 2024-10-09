<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{{__('default.Write Books With AI')}}</title>
	
	<!-- FAVICON AND TOUCH ICONS -->
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<!-- Bootstrap CSS -->
	<link href="/css/bootstrap.css" rel="stylesheet">
	<link href="/css/bootstrap-icons.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="/css/custom.css" rel="stylesheet">
	
	<script>
		let bookData = @json($book);
		let bookSlug = "{{$book_slug}}";
		let colorOptions = @json($colorOptions);
	</script>

</head>
<body>

<main class="py-1">
	
	<div class="container mt-2">
		<h1 style="margin:10px;" class="text-center" id="bookTitle">{{$book['title']}}</h1>
		<div class="mb-1 mt-1 w-100" style="text-align: right;">
			<a href="{{route('user.showcase-library')}}">{{__('default.Back to Books')}}</a>
		</div>
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
							<option value="anthropic-sonet">anthropic :: claude-3.5-sonnet (direct)</option>
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
							<option value="2" selected>2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
						</select>
						
						<div class="mb-3">
							<label for="writingStyle" class="form-label">{{__('default.Writing Style')}}:</label>
							<select class="form-control" id="writingStyle" name="writingStyle" required>
								@foreach($writingStyles as $style)
									@if ($style['value'] === $book['writing_style'])
										<option value="{{ $style['value'] }}" selected>{{ $style['label'] }}</option>
									@else
										<option value="{{ $style['value'] }}">{{ $style['label'] }}</option>
									@endif
								@endforeach
							</select>
						</div>
						
						<div class="mb-3">
							<label for="narrativeStyle" class="form-label">{{__('default.Narrative Style')}}:</label>
							<select class="form-control" id="narrativeStyle" name="narrativeStyle" required>
								@foreach($narrativeStyles as $style)
									@if ($style['value'] === $book['narrative_style'])
										<option value="{{ $style['value'] }}" selected>{{ $style['value'] }}</option>
									@else
										<option value="{{ $style['value'] }}">{{ $style['value'] }}</option>
									@endif
								@endforeach
							</select>
						</div>
						
						
						<button class="btn btn-primary mb-1 mt-2 w-100" id="generateAllBeatsBtn"
						        title="{{__('default.Write All Beats')}}"><i
								class="bi bi-lightning-charge"></i> {{__('default.Write All Beats')}}
						</button>
						
						<button class="btn btn-primary mb-3 mt-1 w-100" id="openLlmPromptModalBtn">
							<i class="bi bi-chat-dots"></i> {{__('default.Send Prompt to LLM')}}
						</button>
						
						<a href="{{route('book-beats',[$book_slug, 'all-chapters','2'])}}"
						   class="btn btn-primary mb-1 mt-1 w-100" title="{{__('default.Edit All Beats')}}">
							<i class="bi bi-pencil-square"></i> {{__('default.Edit All Beats')}}
						</a>
						
						<a href="{{route('book.codex',[$book_slug])}}" class="btn btn-primary mb-3 mt-1 w-100" id="openCodexBtn">
							<i class="bi bi-book"></i> {{__('default.Open Codex')}}
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
						
						<a href="{{route('user.showcase-library')}}" class="mb-1 mt-1 btn btn-secondary w-100"><i
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
								<button class="btn btn-primary mt-3" id="editBookDetailsBtn">
									<i class="bi bi-pencil-square"></i> {{__('default.Edit Book Details')}}
								</button>
								
								<button class="btn btn-danger delete-book-btn mt-3 d-inline-block"
								        data-book-id="<?php echo urlencode($book_slug); ?>"><i
										class="bi bi-trash-fill"></i> {{__('default.Delete Book')}}
								</button>
							@endif
						@endif
					
					</div>
				</div>
			
			</div>
		</div>
		
		<div class="book-chapter-board" id="bookBoard">
			@foreach ($book['acts'] as $act)
				<div class="card general-card">
					<div class="card-header modal-header modal-header-color">
						<h5 class="card-title">{{__('default.Act with Number', ['id' => $act['id']])}} — {{$act['title']}}</h5>
					</div>
					<div class="card-body modal-content modal-content-color">
						@foreach ($act['chapters'] as $chapter)
							<div class="card general-card">
								<div class="card-header modal-header modal-header-color">
									<h5 class="card-title">{{__('default.Chapter with Number', ['order' => $chapter['order']])}}
										— {{$chapter['name']}}</h5>
								</div>
								<div class="card-body modal-content modal-content-color">
									<div class="row">
										<div class="col-9">
											<div class="mb-3">
												<label for="chapterName" class="form-label">{{__('default.Name')}}</label>
												<input type="text" class="form-control chapterName" value="{{$chapter['name']}}">
											</div>
										</div>
										<div class="col-3">
											<div class="mb-3">
												<label for="chapterOrder" class="form-label">{{__('default.Order')}}</label>
												<input type="text" class="form-control chapterOrder" value="{{$chapter['order']}}">
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="chapterShortDescription"
												       class="form-label">{{__('default.Short Description')}}</label>
												<textarea class="form-control chapterShortDescription"
												          rows="3">{{$chapter['short_description']}}</textarea>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="chapterEvents" class="form-label"> {{__('default.Events')}}</label>
												<input type="text" class="form-control chapterEvents" value="{{$chapter['events']}}">
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="chapterPeople" class="form-label">{{__('default.People')}}</label>
												<input type="text" class="form-control chapterPeople" value="{{$chapter['people']}}">
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="chapterPlaces" class="form-label"> {{__('default.Places')}}</label>
												<input type="text" class="form-control chapterPlaces" value="{{$chapter['places']}}">
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="chapterFromPreviousChapter"
												       class="form-label">{{__('default.Previous Chapter')}}</label>
												<input type="text" class="form-control chapterFromPreviousChapter"
												       value="{{$chapter['from_previous_chapter']}}">
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="chapterToNextChapter" class="form-label"> {{__('default.Next Chapter')}}</label>
												<input type="text" class="form-control chapterToNextChapter"
												       value="{{$chapter['to_next_chapter']}}">
											</div>
										</div>
									</div>
									<div class="row" style="margin-left: -15px; margin-right: -15px;">
										<div class="col-12 col-xl-4 col-lg-4 mb-2 mt-1">
											<button class="btn bt-lg btn-secondary w-100 update-chapter-btn"
											        data-chapter-filename="{{$chapter['chapterFilename']}}">
												{{__('default.Update Chapter')}}
											</button>
										</div>
										<div class="col-12 col-xl-4 col-lg-4 mb-2 mt-1">
											<a class="btn bt-lg btn-primary w-100 editBeatsLink"
											   href="/book-beats/{{$book_slug}}/{{str_replace('.json','', $chapter['chapterFilename'])}}/2">{{__('default.Open Beats')}}</a>
										</div>
										<div class="col-12 col-xl-4 col-lg-4 mb-2 mt-1">
											<div class="btn bt-lg btn-warning w-100"
											     onclick="rewriteChapter('{{$chapter['chapterFilename']}}')">{{__('default.Rewrite Chapter')}}</div>
										</div>
									</div>
								</div>
							
							</div>
						@endforeach
					</div>
				</div>
			@endforeach
		
		</div>
	
	</div>
</main>

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
				<button type="button" class="btn-close alert-modal-close-button" data-bs-dismiss="modal"
				        aria-label="{{__('default.Close')}}"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div id="alertModalContent"></div>
			</div>
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-secondary alert-modal-close-button"
				        data-bs-dismiss="modal">{{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>


<!-- Modal for Editing Book Details -->
<div class="modal fade" id="editBookDetailsModal" tabindex="-1" aria-labelledby="editBookDetailsModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="editBookDetailsModalLabel">{{__('default.Edit Book Details')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<form id="editBookDetailsForm">
					<div class="mb-3">
						<label for="editBlurb" class="form-label">{{__('default.Blurb')}}</label>
						<textarea class="form-control" id="editBlurb" rows="3"></textarea>
					</div>
					<div class="mb-3">
						<label for="editBackCoverText" class="form-label">{{__('default.Back Cover Text')}}</label>
						<textarea class="form-control" id="editBackCoverText" rows="5"></textarea>
					</div>
					<div class="mb-3">
						<label for="editCharacterProfiles" class="form-label">{{__('default.Character Profiles')}}</label>
						<textarea class="form-control" id="editCharacterProfiles" rows="5"></textarea>
					</div>
					<div class="mb-3">
						<label for="editAuthorName" class="form-label">{{__('default.Author Name')}}</label>
						<input type="text" class="form-control" id="editAuthorName">
					</div>
					<div class="mb-3">
						<label for="editPublisherName" class="form-label">{{__('default.Publisher Name')}}</label>
						<input type="text" class="form-control" id="editPublisherName">
					</div>
				</form>
			</div>
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Close')}}</button>
				<button type="button" class="btn btn-primary" id="saveBookDetailsBtn">{{__('default.Save Changes')}}</button>
			</div>
		</div>
	</div>
</div>

<!-- LLM Prompt Modal -->
<div class="modal fade" id="llmPromptModal" tabindex="-1" aria-labelledby="llmPromptModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="llmPromptModalLabel">{{__('default.Send Prompt to LLM')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div class="mb-3">
					<label for="userPrompt" class="form-label">{{__('default.User Prompt')}}</label>
					<textarea class="form-control" id="userPrompt" rows="8"></textarea>
				</div>
				<div class="mb-3">
					<label for="llmResponse" class="form-label">{{__('default.LLM Response')}}</label>
					<textarea class="form-control" id="llmResponse" rows="10" readonly></textarea>
				</div>
			</div>
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Close')}}</button>
				<button type="button" class="btn btn-primary" id="sendPromptBtn">{{__('default.Send Prompt')}}</button>
			</div>
		</div>
	</div>
</div>

<!-- Rewrite Chapter Modal -->
<div class="modal fade" id="rewriteChapterModal" tabindex="-1" aria-labelledby="rewriteChapterModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="rewriteChapterModalLabel">{{__('default.Rewrite Chapter')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div class="mb-3">
					<label for="rewriteUserPrompt" class="form-label">{{__('default.User Prompt')}}</label>
					<textarea class="form-control" id="rewriteUserPrompt" rows="10"></textarea>
				</div>
				<div class="mb-3">
					<h6>{{__('default.Rewritten Chapter:')}}</h6>
					<textarea class="form-control" id="rewriteResult" rows="10"></textarea>
				</div>
			</div>
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Close')}}</button>
				<button type="button" class="btn btn-primary"
				        id="sendRewritePromptBtn">{{__('default.Rewrite Chapter')}}</button>
				<button type="button" class="btn btn-success" id="acceptRewriteBtn"
				        style="display: none;">{{__('default.Accept Rewrite')}}</button>
			</div>
		</div>
	</div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				Are you sure you want to delete this book?
			</div>
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
			</div>
		</div>
	</div>
</div>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="/js/jquery-3.7.0.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/moment.min.js"></script>

<script src="/js/jspdf.umd.min.js"></script>
<script src="/js/docx.js"></script>


<!-- Your custom scripts -->
<script src="/js/custom-ui.js"></script>
<script>
	
	let reload_window = false;
	
	function showBookStructureModal() {
		
		
		let structureHtml = '<h2>' + bookData.title + '</h2>';
		structureHtml += '<p><i>' + '{{__('default.Blurb')}}' + ':</i> ' + bookData.blurb + '</p>';
		structureHtml += '<p><i>' + '{{__('default.Back Cover Text')}}' + ':</i> ' + bookData.back_cover_text + '</p>';
		
		bookData.acts.forEach(function (act, actIndex) {
			structureHtml += '<h3>';
			structureHtml += "{{__('default.Act')}} " + (actIndex + 1) + ':' + act.title + ' </h3>';
			act.chapters.forEach(function (chapter) {
				
				var chapter_events = chapter.events;
				if (Array.isArray(chapter.events)) {
					chapter_events = chapter.events.join(', ');
				}
				var chapter_people = chapter.people;
				if (Array.isArray(chapter.people)) {
					chapter_people = chapter.people.join(', ');
				}
				var chapter_places = chapter.places;
				if (Array.isArray(chapter.places)) {
					chapter_places = chapter.places.join(', ');
				}
				
				structureHtml += '<h4>' + chapter.name + '</h4>';
				structureHtml += '<p>' + chapter.short_description + '</p>';
				structureHtml += '<ul>';
				structureHtml += '<li><i>' + '{{__('default.Events')}}' + '</i>: ' + chapter_events + '</li>';
				structureHtml += '<li><i>' + '{{__('default.People')}}' + '</i>: ' + chapter_people + '</li>';
				structureHtml += '<li><i>' + '{{__('default.Places')}}' + '</i>: ' + chapter_places + '</li>';
				structureHtml += '<li><i>' + '{{__('default.From Previous Chapter')}}' + '</i>: ' + chapter.from_previous_chapter + '</li>';
				structureHtml += '<li><i>' + '{{__('default.To Next Chapter')}}' + '</i>: ' + chapter.to_next_chapter + '</li>';
				structureHtml += '</ul>';
				if (chapter.beats && chapter.beats.length > 0) {
					structureHtml += '<h5>' + '{{__('default.Beats')}}' + ':</h5>';
					// structureHtml += '<ul>';
					chapter.beats.forEach(function (beat) {
						if (beat.beat_text) {
							let beatText = beat.beat_text;
							beatText = beatText.replace(/\n/g, '<br>');
							structureHtml += '<br>' + beatText + '<hr>';
						} else {
							structureHtml += '<br>-' + beat.description + '<hr>';
						}
					});
					// structureHtml += '</ul>';
				}
			});
		});
		
		$('#bookStructureContent').html(structureHtml);
		$('#bookStructureModal').modal({backdrop: 'static', keyboard: true}).modal('show');
	}
	
	function saveChapter(chapterData) {
		$.ajax({
			url: `/book/${bookSlug}/chapter`,
			type: 'POST',
			data: chapterData,
			dataType: 'json',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function (response) {
				if (response.success) {
					reload_window = true;
					
					$('#save_result').html('<div class="alert alert-success">{{__('default.Chapter saved successfully!')}}</div>');
					$("#alertModalContent").html('{{__('default.Chapter saved successfully!')}}');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				} else {
					console.log(response);
					$("#alertModalContent").html('{{__('default.Failed to save chapter: ')}}' + response.message);
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			},
			error: function (xhr, status, error) {
				console.error(xhr.responseText);
				$("#alertModalContent").html('{{__('default.An error occurred while saving the chapter.')}}');
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
			}
		});
	}
	
	function generateAllBeats(beatsPerChapter = 3, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
		const modal = $('#generateAllBeatsModal');
		const progressBar = modal.find('.progress-bar');
		const log = $('#generateAllBeatsLog');
		
		modal.modal({backdrop: 'static', keyboard: true}).modal('show');
		$('#generateAllBeatsLog').empty();
		progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
		
		$('#generateAllBeatsLog').append('<br>' + '{{__('default.This process will write 10 short beats for each chapter in the book. Later these beats will be turned into full book pages.')}}');
		$('#generateAllBeatsLog').append('<br>' + '{{__('default.Please wait...')}}');
		$('#generateAllBeatsLog').append('<br><br>{{__('default.If the progress bar is stuck for a long time, please refresh the page and try again.')}}<br><br>');
		
		chapters = bookData.acts.flatMap(act => act.chapters);
		
		console.log(chapters);
		generateSingleChapterBeats(chapters, beatsPerChapter, writingStyle, narrativeStyle, 0);
		
	}
	
	function generateSingleChapterBeats(chapters, beatsPerChapter, writingStyle, narrativeStyle, chapter_index = 0) {
		const modal = $('#generateAllBeatsModal');
		const log = $('#generateAllBeatsLog');
		
		const totalChapters = chapters.length;
		
		chapter_index++;
		
		const chapter = chapters[chapter_index - 1];
		$('#generateAllBeatsLog').append('<br><br>Processing chapter: ' + chapter.name);
		$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
		
		// Check if the chapter already has beats
		if (chapter.beats && chapter.beats.length > 0) {
			$('#generateAllBeatsLog').append('<br>Chapter "' + chapter.name + '" already has beats. Skipping...');
			
			const progressBar = modal.find('.progress-bar');
			const progress = Math.round((chapter_index / totalChapters) * 100);
			progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
			
			$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
			if (chapter_index < totalChapters) {
				generateSingleChapterBeats(chapters, beatsPerChapter, writingStyle, narrativeStyle, chapter_index);
			}
		} else {
			
			$.ajax({
				url: `/book/write-beats/${bookSlug}/${chapter.chapterFilename}`,
				method: 'POST',
				data: {
					llm: savedLlm,
					beats_per_chapter: beatsPerChapter,
					writing_style: writingStyle,
					narrative_style: narrativeStyle,
					save_results: true,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						// Save the generated beats back to the chapter
						
						const progressBar = modal.find('.progress-bar');
						const progress = Math.round((chapter_index / totalChapters) * 100);
						progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
						
						if (Array.isArray(response.beats)) {
							$('#generateAllBeatsLog').append('<br>Beats generated and saved for chapter: ' + chapter.name);
							
							response.beats.forEach((beat, index) => {
								$('#generateAllBeatsLog').append(`<br>${beat.description}`);
							});
						} else {
							$('#generateAllBeatsLog').append('<br>Beats failed for chapter: ' + chapter.name);
							$("#alertModalContent").html('Failed to generate beats: ' + response.beats);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
						if (chapter_index < totalChapters) {
							generateSingleChapterBeats(chapters, beatsPerChapter, writingStyle, narrativeStyle, chapter_index);
						} else {
							$('#generateAllBeatsLog').append('<br>' + '{{__('default.All chapters processed!')}}');
							$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
						}
					} else {
						$('#generateAllBeatsLog').append('<br>Beats failed for chapter: ' + chapter.name);
						$("#alertModalContent").html('Failed to generate beats: ' + response.beats);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				},
				
				error: function () {
					$('#generateAllBeatsLog').append('<p>Error generating beats for chapter: '+chapter.name + '</p>');
					$('#generateAllBeats').scrollTop(log[0].scrollHeight);
					//break loop
				}
			});
		}
	}
	
	function rewriteChapter(chapterFilename) {
		const modal = $('#rewriteChapterModal');
		
		let chaptersToInclude = [];
		let foundCurrentChapter = false;
		let foundCurrentChapterData = [];
		for (let act of bookData.acts) {
			for (let chapter of act.chapters) {
				if (chapter.chapterFilename === chapterFilename) {
					foundCurrentChapter = true;
					foundCurrentChapterData = chapter;
					break;
				}
				chaptersToInclude.push(chapter);
			}
			if (foundCurrentChapter) {
				break;
			}
		}
		
		// Fetch the rewrite_chapter.txt template
		$.get('/prompts/rewrite_chapter.txt', function (template) {
			// Replace placeholders in the template
			const replacements = {
				'##user_blurb##': bookData.prompt || '',
				'##language##': bookData.language || 'English',
				'##book_title##': bookData.title || '',
				'##book_blurb##': bookData.blurb || '',
				'##book_keywords##': bookData.keywords ? bookData.keywords.join(', ') : '',
				'##back_cover_text##': bookData.back_cover_text || '',
				'##character_profiles##': bookData.character_profiles || '',
				'##genre##': bookData.genre || 'fantasy',
				'##adult_content##': bookData.adult_content || 'non-adult',
				'##writing_style##': $("#writingStyle").val() || 'Minimalist',
				'##narrative_style##': $("#narrativeStyle").val() || 'Third Person - The narrator has a godlike perspective',
				'##book_structure##': bookData.book_structure || 'the_1_act_story.txt',
				'##previous_chapters##': chaptersToInclude.map(ch =>
					`name: ${ch.name}\nshort description: ${ch.short_description}\nevents: ${ch.events}\npeople: ${ch.people}\nplaces: ${ch.places}\nfrom previous chapter: ${ch.from_previous_chapter}\nto next chapter: ${ch.to_next_chapter}\n\nbeats:\n${ch.beats ? ch.beats.map(b => b.beat_summary || b.description).join('\n') : ''}`
				).join('\n\n'),
				'##current_chapter##': `name: ${foundCurrentChapterData.name}\nshort description: ${foundCurrentChapterData.short_description}\nevents: ${foundCurrentChapterData.events}\npeople: ${foundCurrentChapterData.people}\nplaces: ${foundCurrentChapterData.places}\nfrom previous chapter: ${foundCurrentChapterData.from_previous_chapter}\nto next chapter: ${foundCurrentChapterData.to_next_chapter}`
			};
			
			for (const [key, value] of Object.entries(replacements)) {
				template = template.replace(new RegExp(key, 'g'), value);
			}
			
			$('#rewriteUserPrompt').val(template.trim());
			
			// Show the modal
			modal.modal('show');
		});
		
		// Handle the rewrite button click
		$('#sendRewritePromptBtn').off('click').on('click', function () {
			const userPrompt = $('#rewriteUserPrompt').val();
			$('#sendRewritePromptBtn').prop('disabled', true).text('{{__('default.Rewriting...')}}');
			
			$.ajax({
				url: '/rewrite-chapter',
				method: 'POST',
				data: {
					book_slug: bookSlug,
					llm: savedLlm,
					user_prompt: userPrompt
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						// Display the rewritten chapter in the modal
						$('#rewriteResult').val(JSON.stringify(response.rewrittenChapter, null, 2));
						$('#acceptRewriteBtn').show();
					} else {
						$("#alertModalContent").html('{{__('default.Failed to rewrite chapter:')}}' + response.message);
						$("#alertModal").modal('show');
					}
					$('#sendRewritePromptBtn').prop('disabled', false).text('{{__('default.Rewrite Chapter')}}');
				},
				error: function () {
					$("#alertModalContent").html('{{__('default.Error rewriting chapter')}}');
					$("#alertModal").modal('show');
					$('#sendRewritePromptBtn').prop('disabled', false).text('{{__('default.Rewrite Chapter')}}');
				}
			});
		});
		
		$('#acceptRewriteBtn').off('click').on('click', function () {
			$.ajax({
				url: '/accept-rewrite',
				method: 'POST',
				data: {
					book_slug: bookSlug,
					chapter_filename: chapterFilename,
					rewritten_content: $('#rewriteResult').val()
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						reload_window = true;
						$("#alertModalContent").html('{{__('default.Chapter rewritten successfully!')}}');
						$("#alertModal").modal('show');
					} else {
						$("#alertModalContent").html('{{__('default.Failed to save rewritten chapter:')}}' + response.message);
						$("#alertModal").modal('show');
					}
				},
				error: function () {
					$("#alertModalContent").html('{{__('default.Error saving rewritten chapter')}}');
					$("#alertModal").modal('show');
				}
			});
		});
		
	}
	
	function exportAsPdf(bookStructure) {
		console.log(bookStructure);
		const {jsPDF} = window.jspdf;
		const doc = new jsPDF({
			unit: 'in',
			format: [6, 9]
		});
		
		// Load a Unicode font
		doc.addFont('/assets/fonts/NotoSans-Regular.ttf', 'NotoSans', 'normal');
		doc.addFont('/assets/fonts/NotoSans-Bold.ttf', 'NotoSans', 'bold');
		doc.addFont('/assets/fonts/NotoSans-Italic.ttf', 'NotoSans', 'italic');
		
		// Set default font to Roboto
		doc.setFont('NotoSans', 'normal');
		
		// Set font to a serif font
		// doc.setFont('times', 'normal');
		
		const lineHeight = 0.25; // Increased line height
		let yPosition = 0.75; // Increased top margin
		const pageHeight = 8.5;
		const pageWidth = 6;
		const margin = 0.75; // Increased side margins
		let pageNumber = 1;
		let currentFontSize = 12;
		let currentFontStyle = 'normal';
		
		function setFont(fontSize = 12, isBold = false) {
			currentFontSize = fontSize;
			currentFontStyle = isBold ? 'bold' : 'normal';
			doc.setFontSize(fontSize);
			// doc.setFont('times', currentFontStyle);
			doc.setFont('NotoSans', currentFontStyle);
		}
		
		function addText(text, fontSize = 12, isBold = false, align = 'left') {
			setFont(fontSize, isBold);
			const splitText = doc.splitTextToSize(text, pageWidth - 2 * margin);
			splitText.forEach(line => {
				if (yPosition > pageHeight - margin) {
					addPageNumber();
					doc.addPage();
					yPosition = margin;
					pageNumber++;
					setFont(currentFontSize, currentFontStyle === 'bold');
				}
				
				doc.text(line, align === 'center' ? pageWidth / 2 : margin, yPosition, {align: align});
				
				yPosition += lineHeight;
			});
			yPosition += 0.2; // Add a small gap after each text block
		}
		
		function addPageBreak() {
			addPageNumber();
			doc.addPage();
			yPosition = margin;
			pageNumber++;
			setFont(currentFontSize, currentFontStyle === 'bold');
		}
		
		function addPageNumber() {
			const currentFont = doc.getFont();
			const currentFontSize = doc.getFontSize();
			doc.setFontSize(10);
			doc.setFont('NotoSans', 'normal');
			doc.text(String(pageNumber), pageWidth - margin + 0.2, pageHeight - margin + 0.4, {align: 'right'});
			doc.setFontSize(currentFontSize);
			doc.setFont(currentFont.fontName, currentFont.fontStyle);
		}
		
		
		function addCenteredPage(text, fontSize = 18, isBold = true) {
			addPageBreak();
			setFont(fontSize, isBold);
			const textLines = doc.splitTextToSize(text, pageWidth - 2 * margin);
			const textHeight = textLines.length * lineHeight;
			const startY = (pageHeight - textHeight) / 2;
			doc.text(textLines, pageWidth / 2, startY, {align: 'center'});
		}
		
		// Title
		addCenteredPage(bookStructure.title, 18, true);
		
		// Blurb
		addCenteredPage(bookStructure.blurb, 14, true);
		addPageBreak();
		
		// Back Cover Text
		addText(bookStructure.back_cover_text, 14, false);
		addPageBreak();
		
		bookStructure.acts.forEach((act, actIndex) => {
			if (bookStructure.language === 'Turkish') {
				act.title = act.title.replace('Act', 'Perde');
			}
			
			addCenteredPage(`${act.title}`); //Act ${actIndex + 1}:
			act.chapters.forEach((chapter, chapterIndex) => {
				addPageBreak();
				
				if (bookStructure.language === 'Turkish') {
					chapter.name = chapter.name.replace('Chapter', 'Bölüm');
				}
				
				// Chapter title
				addText(chapter.name, 14, true);
				
				// Beats
				if (chapter.beats && chapter.beats.length > 0) {
					
					chapter.beats.forEach((beat, beatIndex) => {
						if (beat.beat_text) {
							addText(beat.beat_text);
							// addText('____________________');
							addText('');
						}
					});
				}
			});
		});
		
		addPageNumber(); // Add page number to the last page
		let simpleFilename = bookStructure.title.replace(/[^a-z0-9]/gi, '_').toLowerCase();
		doc.save(simpleFilename + '.pdf');
	}
	
	async function exportAsDocx(bookStructure) {
		console.log(bookStructure);
		
		const {Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType, PageBreak} = docx;
		
		let doc_children = [];
		
		function addText(text, size = 24, bold = false, alignment = AlignmentType.LEFT) {
			doc_children.push(new Paragraph({
				alignment: alignment,
				spacing: {
					line: 1.5 * 240
				},
				children: [
					new TextRun({
						text: text,
						bold: bold,
						size: size
					})
				]
			}));
		}
		
		function addPageBreak() {
			doc_children.push(new Paragraph({
				children: [new PageBreak()]
			}));
		}
		
		function addCenteredPage(text, size = 36, bold = true) {
			addText('');
			addText('');
			addText('');
			addText('');
			addText('');
			addText('');
			addText('');
			addText('');
			addText(text, size, bold, AlignmentType.CENTER);
		}
		
		// Title
		addCenteredPage(bookStructure.title);
		addPageBreak();
		
		// Blurb
		addText('');
		addText('');
		addText('');
		addText('');
		addText(bookStructure.blurb, 28, false, AlignmentType.JUSTIFIED);
		addPageBreak();
		
		// Back Cover Text
		addText(bookStructure.back_cover_text, 28, false, AlignmentType.JUSTIFIED);
		addPageBreak();
		
		bookStructure.acts.forEach((act, actIndex) => {
			if (bookStructure.language === 'Turkish') {
				act.title = act.title.replace('Act', 'Perde');
			}
			
			addCenteredPage(`${act.title}`);
			addPageBreak();
			
			act.chapters.forEach((chapter, chapterIndex) => {
				if (bookStructure.language === 'Turkish') {
					chapter.name = chapter.name.replace('Chapter', 'Bölüm');
				}
				
				// Chapter title
				addText('');
				addText(chapter.name, 32, true, AlignmentType.CENTER);
				addText('');
				addText('');
				
				// Beats
				if (chapter.beats && chapter.beats.length > 0) {
					chapter.beats.forEach((beat, beatIndex) => {
						if (beat.beat_text) {
							let beat_texts = beat.beat_text.split('\n');
							beat_texts.forEach((beat_text) => {
								addText(beat_text, 24, false, AlignmentType.JUSTIFIED);
								// addText('');
							});
						}
					});
				}
				
				addPageBreak();
				
				
			});
		});
		
		// Generate and save the document
		
		const doc = new Document({
			sections: [{
				properties: {
					page: {
						size: {
							width: 6 * 1440, // 6 inches in twips (1 inch = 1440 twips)
							height: 9 * 1440, // 9 inches in twips
						},
					},
				},
				children: doc_children
			}]
		});
		
		const blob = await Packer.toBlob(doc);
		const url = URL.createObjectURL(blob);
		const link = document.createElement('a');
		link.href = url;
		let simpleFilename = bookStructure.title.replace(/[^a-z0-9]/gi, '_').toLowerCase();
		link.download = simpleFilename + '.docx';
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		URL.revokeObjectURL(url);
	}
	
	
	let createCoverFileName = '';
	let bookToDelete = null;
	
	$(document).ready(function () {
		$('.closeAndRefreshButton').on('click', function () {
			location.reload();
		});
		
		$('.delete-book-btn').on('click', function (e) {
			e.preventDefault();
			bookToDelete = $(this).data('book-id');
			$('#deleteConfirmModal').modal('show');
		});
		
		$('#confirmDeleteBtn').on('click', function () {
			if (bookToDelete) {
				$.ajax({
					url: `/book/${bookToDelete}`,
					type: 'DELETE',
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					success: function (response) {
						if (response.success) {
							$('#deleteConfirmModal').modal('hide');
							window.location.href = '/my-books';
						} else {
							$("#alertModalContent").html(response.message);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
					},
					error: function () {
						$("#alertModalContent").html('{{__('default.An error occurred while deleting the book.')}}');
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				});
			}
		});
		
		$('#createCoverBtn').on('click', function (e) {
			e.preventDefault();
			$('#createCoverModal').modal({backdrop: 'static', keyboard: true}).modal('show');
			$("#coverBookTitle").val(bookData.title);
			$("#coverBookAuthor").val(bookData.author_name);
			$("#coverPrompt").val('{{__('default.An image describing: ')}}' + bookData.blurb);
		});
		
		$('#generateCoverBtn').on('click', function () {
			$('#generateCoverBtn').prop('disabled', true).text('{{__('default.Generating...')}}');
			
			$.ajax({
				url: '/make-cover-image/' + bookSlug,
				method: 'POST',
				data: {
					theme: $("#coverPrompt").val(),
					title_1: $("#coverBookTitle").val(),
					author_1: $("#coverBookAuthor").val(),
					creative: $("#enhancePrompt").is(':checked') ? 'more' : 'no',
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						$('#generatedCover').attr('src', "/storage/ai-images/" + data.output_filename);
						createCoverFileName = data.output_filename;
						$('#saveCoverBtn').prop('disabled', false);
					} else {
						$("#alertModalContent").html('{{__('default.Failed to generate cover: ')}}' + data.message);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
					$('#generateCoverBtn').prop('disabled', false).text('{{__('default.Generate')}}');
				}
			});
		});
		
		$('#saveCoverBtn').on('click', function () {
			$.ajax({
				url: '/book/' + bookSlug + '/cover',
				method: 'POST',
				data: {
					cover_filename: createCoverFileName
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						$("#alertModalContent").html('{{__('default.Cover saved successfully!')}}');
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						
						$('#bookCover').attr('src', '/storage/ai-images/' + createCoverFileName);
					} else {
						$("#alertModalContent").html('{{__('default.Failed to save cover: ')}}' + data.message);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				},
				error: function (xhr, status, error) {
					$("#alertModalContent").html('{{__('default.An error occurred while saving the cover.')}}');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			});
		});
		
		$('.update-chapter-btn').on('click', function () {
			var chapterFilename = $(this).data('chapter-filename');
			var chapterCard = $(this).closest('.card');
			
			var chapterData = {
				chapter_filename: chapterFilename,
				name: chapterCard.find('.chapterName').val(),
				order: chapterCard.find('.chapterOrder').val(),
				short_description: chapterCard.find('.chapterShortDescription').val(),
				events: chapterCard.find('.chapterEvents').val(),
				people: chapterCard.find('.chapterPeople').val(),
				places: chapterCard.find('.chapterPlaces').val(),
				from_previous_chapter: chapterCard.find('.chapterFromPreviousChapter').val(),
				to_next_chapter: chapterCard.find('.chapterToNextChapter').val()
			};
			saveChapter(chapterData);
		});
		
		$('#showBookStructureBtn').on('click', function (e) {
			e.preventDefault();
			showBookStructureModal();
		});
		
		$('#generateAllBeatsBtn').on('click', function (e) {
			e.preventDefault();
			generateAllBeats(parseInt($('#beatsPerChapter').val()), $("#writingStyle").val(), $("#narrativeStyle").val());
		});
		
		$('#rewriteChapterModal').on('shown.bs.modal', function () {
			$('#rewriteUserPrompt').focus();
		});
		
		$('#beatsPerChapter').on('change', function () {
			let selectedBeats = $(this).val();
			$('.editBeatsLink').each(function () {
				let currentHref = $(this).attr('href');
				console.log(currentHref);
				let newHref = currentHref.replace(/\/\d+$/, '/' + selectedBeats);
				$(this).attr('href', newHref);
			});
		});
		
		// Open the edit book details modal
		$('#editBookDetailsBtn').on('click', function () {
			$('#editBlurb').val(bookData.blurb);
			$('#editBackCoverText').val(bookData.back_cover_text);
			$('#editCharacterProfiles').val(bookData.character_profiles);
			$('#editAuthorName').val(bookData.author_name);
			$('#editPublisherName').val(bookData.publisher_name);
			$('#editBookDetailsModal').modal('show');
		});
		
		$('#exportPdfBtn').on('click', function (e) {
			e.preventDefault();
			exportAsPdf(bookData);
		});
		
		$('#exportTxtBtn').on('click', function (e) {
			e.preventDefault();
			exportAsDocx(bookData);
		});
		
		
		$(".alert-modal-close-button").on('click', function () {
			if (reload_window) {
				location.reload();
			}
		});
		
		// Save book details
		$('#saveBookDetailsBtn').on('click', function () {
			const updatedBookData = {
				blurb: $('#editBlurb').val(),
				back_cover_text: $('#editBackCoverText').val(),
				character_profiles: $('#editCharacterProfiles').val(),
				author_name: $('#editAuthorName').val(),
				publisher_name: $('#editPublisherName').val()
			};
			
			$.ajax({
				url: `/book/${bookSlug}/details`,
				type: 'POST',
				data: updatedBookData,
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function (response) {
					if (response.success) {
						// Update the bookData object
						Object.assign(bookData, updatedBookData);
						
						// Update the displayed information
						$('#bookBlurb').text(bookData.blurb);
						$('#backCoverText').html(bookData.back_cover_text.replace(/\n/g, '<br>'));
						$('#bookCharacters').html('<em>{{__("default.Character Profiles:")}}</em><br>' + bookData.character_profiles.replace(/\n/g, '<br>'));
						
						reload_window = true;
						$('#editBookDetailsModal').modal('hide');
						$("#alertModalContent").html('{{__("default.Book details updated successfully!")}}');
						$("#alertModal").modal('show');
					} else {
						$("#alertModalContent").html('{{__("default.Failed to update book details:")}}' + response.message);
						$("#alertModal").modal('show');
					}
				},
				error: function () {
					$("#alertModalContent").html('{{__("default.An error occurred while updating book details.")}}');
					$("#alertModal").modal('show');
				}
			});
		});
		
		
		// Open LLM Prompt Modal
		$('#openLlmPromptModalBtn').on('click', function () {
			$('#llmPromptModal').modal('show');
		});
		
		// Send Prompt to LLM
		$('#sendPromptBtn').on('click', function () {
			const userPrompt = $('#userPrompt').val();
			const llm = savedLlm; // Assuming you have a savedLlm variable
			
			// Disable buttons and show loading state
			$('#sendPromptBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
			$('#llmResponse').val('Processing...');
			
			$.ajax({
				url: '/send-llm-prompt/' + bookSlug,
				method: 'POST',
				data: {
					user_prompt: userPrompt,
					llm: llm
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						$('#llmResponse').val(response.result);
					} else {
						$('#llmResponse').val('Error: ' + response.message);
					}
				},
				error: function (xhr, status, error) {
					$('#llmResponse').val('An error occurred while processing the request.');
				},
				complete: function () {
					// Re-enable button and restore original text
					$('#sendPromptBtn').prop('disabled', false).text('Send Prompt');
				}
			});
		});
		
		
	});


</script>
</body>
</html>
