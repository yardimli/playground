<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{{__('default.Write Books With AI - Book Beats')}}</title>
	
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
	<link href="/css/custom.css" rel="stylesheet"> <!-- If you have custom CSS -->
	
	<script>
		{!! $json_translations !!}
	</script>
	
	<script>
		let bookData = @json($book);
		let bookSlug = "{{$book_slug}}";
		let selectedChapter = "{{$selected_chapter}}";
		let selectedChapterIndex = "{{$selected_chapter_index}}";
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
						
						<span class="form-label">{{__('default.AI Engines:')}}</span>
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
						<span class="form-label">{{__('default.Number of beats per chapter:')}}</span>
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
						<button type="button" class="btn btn-success mt-1 mb-1 w-100" id="recreateBeats"><i
								class="bi bi-pencil"></i> {{__('default.Recreate Beats')}}</button>
						<button type="button" class="btn btn-primary mt-2 mb-1 w-100"
						        id="saveBeatsBtn"><i
								class="bi bi-file-earmark-text-fill"></i> {{__('default.Save Beats')}}</button>
						
						<button type="button" class="btn btn-primary mt-2 mb-1 w-100" id="writeAllBeatsBtn"
						        title="{{__('default.Write All Beats')}}"><i
								class="bi bi-lightning-charge"></i> {{__('default.Write All Beat Contents')}}
						</button>
						
						<a href="{{route('edit-book', $book_slug)}}" class="btn btn-primary mb-1 mt-1 w-100"
						   title="{{__('default.Back to Chapters')}}"><i
								class="bi bi-book"></i> {{__('default.Back to Chapters')}}</a>
						<a href="{{route('user.showcase-library')}}" class="mb-1 mt-1 btn btn-primary w-100"><i
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
					
					
					</div>
				</div>
			
			</div>
		</div>
		
		@php
			//dd($book);
			$chapter_index = 0;
			if ($selected_chapter_index !== 0) {
				$chapter_index = $selected_chapter_index -1;
			}
		@endphp
		
		<div class="card-header modal-header modal-header-color mb-4">
			<h5 class="modal-title p-2" id="beatModalLabel">{{__('default.Beats')}}</h5>
		</div>
		
		@foreach($book['acts'] as $act)
			
			@foreach($act['chapters'] as $chapter)
				@php
					$chapter_index++;
				@endphp
				<div class="card general-card">
					<div class="card-body modal-content-color">
						<h5>{{__('default.Chapter')}} #{{$chapter_index}} - {{$chapter['name'] ?? 'noname'}}</h5>
						<em>{{__('default.Description')}}</em>: <span
							id="chapterDescription">{{$chapter['short_description'] ?? 'no description'}}</span><br>
						<em>{{__('default.Events')}}</em>: <span
							id="chapterEvents">{{$chapter['events'] ?? 'no events'}}</span><br>
						<em>{{__('default.People')}}</em>: <span
							id="chapterPeople">{{$chapter['people'] ?? 'no people'}}</span><br>
						<em>{{__('default.Places')}}</em>: <span
							id="chapterPlaces">{{$chapter['places'] ?? 'no places'}}</span><br>
						<em>{{__('default.Previous Chapter')}}</em>: <span
							id="chapterFromPreviousChapter">{{$chapter['from_previous_chapter']}}</span><br>
						<em>{{__('default.Next Chapter')}}</em>: <span
							id="chapterToNextChapter">{{$chapter['to_next_chapter']}}</span><br>
					</div>
				</div>
				
				<div class="card general-card">
					<div class="card-body modal-content-color">
						<div id="beatsList">
							@php
								$index = -1;
							@endphp
							@foreach($chapter['beats'] as $beat)
								@php
									$index++;
								@endphp
								<div class="mb-3 beat-outer-container" data-chapter-index="{{$chapter_index}}"
								     data-chapter-filename="{{$chapter['chapterFilename']}}"
								     data-beat-index="{{$index}}">
									<h6>{{__('default.Beat')}} {{$index+1}}</h6>
									<div id="beatDescriptionContainer_{{$chapter_index}}_{{$index}}">
										<label for="beatDescription_{{$chapter_index}}_{{$index}}"
										       class="form-label">{{__('default.Beat Description')}}</label>
										<textarea id="beatDescription_{{$chapter_index}}_{{$index}}"
										          class="form-control beat-description-textarea"
										          rows="3">{{$beat['description'] ?? ''}}</textarea>
										<div id="beatDescriptionResult_{{$chapter_index}}_{{$index}}"></div>
										<button id="writeBeatDescriptionBtn_{{$chapter_index}}_{{$index}}"
										        data-chapter-index="{{$chapter_index}}"
										        data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}"
										        class="writeBeatDescriptionBtn btn btn-primary mt-3 me-2">{{__('default.Write Beat Description')}}</button>
									</div>
									<div id="beatTextArea_{{$chapter_index}}_{{$index}}" class="mt-3">
										<label for="beatText_{{$chapter_index}}_{{$index}}"
										       class="form-label">{{__('default.Beat Text')}}</label>
										<textarea id="beatText_{{$chapter_index}}_{{$index}}" class="form-control beat-text-textarea"
										          rows="10">{{$beat['beat_text'] ?? ''}}</textarea>
										<div id="beatTextResult_{{$chapter_index}}_{{$index}}"></div>
										<button id="writeBeatTextBtn_{{$chapter_index}}_{{$index}}" data-chapter-index="{{$chapter_index}}"
										        data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}"
										        class="writeBeatTextBtn btn btn-primary mt-3 me-2">{{__('default.Write Beat Text')}}</button>
									</div>
									<div id="beatSummaryArea_{{$chapter_index}}_{{$index}}" class="mt-3">
										<label for="beatSummary_{{$chapter_index}}_{{$index}}"
										       class="form-label">{{__('default.Beat Summary')}}</label>
										<textarea id="beatSummary_{{$chapter_index}}_{{$index}}" class="form-control beat-summary-textarea"
										          rows="3">{{$beat['beat_summary'] ?? ''}}</textarea>
										<div id="beatSummaryResult_{{$chapter_index}}_{{$index}}"></div>
										<button id="writeBeatSummaryBtn_{{$chapter_index}}_{{$index}}"
										        data-chapter-index="{{$chapter_index}}"
										        data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}"
										        class="writeBeatSummaryBtn btn btn-primary mt-3 me-2">{{__('default.Write Summary')}}</button>
									</div>
									
									<div id="loreBookArea_{{$chapter_index}}_{{$index}}" class="mt-3">
										<label for="loreBook_{{$chapter_index}}_{{$index}}"
										       class="form-label">{{__('default.Lore Book')}}</label>
										<textarea id="loreBook_{{$chapter_index}}_{{$index}}"
										          class="form-control lore-book-textarea"
										          rows="6">{{$beat['lore_book'] ?? ''}}</textarea>
										<div id="loreResult_{{$chapter_index}}_{{$index}}"></div>
										<button id="updateLoreBookBtn_{{$chapter_index}}_{{$index}}"
										        data-chapter-index="{{$chapter_index}}"
										        data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}"
										        class="updateLoreBookBtn btn btn-primary mt-3 me-2">{{__('default.Update Lore Book')}}</button>
									</div>
									
									<div>
										<button class="saveBeatBtn btn btn-success mt-3 me-2" data-chapter-index="{{$chapter_index}}"
										        data-chapter-filename="{{$chapter['chapterFilename']}}"
										        data-beat-index="{{$index}}">{{__('default.Save Beat')}}</button>
										@if($index == 0)
											<button class="addEmptyBeatBtn btn btn-primary mt-3 me-2" data-position="before"
											        data-chapter-index="f"
											        data-chapter-filename="{{$chapter['chapterFilename']}}"
											        data-beat-index="{{$index}}">{{__('default.Add Empty Beat Before')}}</button>
										@endif
										<button class="addEmptyBeatBtn btn btn-primary mt-3 me-2" data-position="after"
										        data-chapter-index="f"
										        data-chapter-filename="{{$chapter['chapterFilename']}}"
										        data-beat-index="{{$index}}">{{__('default.Add Empty Beat After')}}</button>
										<div class="me-auto d-inline-block" id="beatDetailModalResult_{{$chapter_index}}_{{$index}}"></div>
									</div>
								</div>
							@endforeach
						</div>
					</div>
				</div>
			@endforeach
		@endforeach
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
<script src="/js/all-beats.js"></script>

</body>
</html>
