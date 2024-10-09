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
<script src="/js/chapter.js"></script>

</body>
</html>
