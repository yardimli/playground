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
			<div class="card-body modal-content modal-content-color">
				<!-- Image Div -->
				<div class="row">
					<!-- Text Blocks Div -->
					<div class="col-12 col-lg-8">
						<span style="font-size: 16px; font-weight: normal; font-style: italic;"
						      id="bookBlurb">{{$book['blurb']}}</span>
						<br><br>
						
						<div><span id="backCoverText">{!!str_replace("\n","<br>",$book['back_cover_text'])!!}</span></div>
					</div>
					<div class="col-12 col-lg-4">
						<div class="mb-3"><span id="bookPrompt"><em>{{__('default.Prompt For Book:')}}</em><br>
								{{$book['prompt'] ?? 'no prompt'}}</span></div>
						<div class="mb-3"><span id="bookCharacters"><em>{{__('default.Character Profiles:')}}</em><br>
								{!! str_replace("\n","<br>", $book['character_profiles'] ?? 'no characters')!!}</span></div>
					</div>
					<div class="col-12 mt-3">
					</div>
					
					<div class="col-12 col-lg-6">
						<span class="form-label">{{__('default.AI Engines:')}}</span>
						<select id="llmSelect" class="form-select">
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
					</div>
					
					<div class="col-12 col-lg-6" id="beatsPerChapterLabel">
						
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
					</div>
					
					<div class="col-12 col-lg-6">
						<span for="writingStyle" class="form-label">{{__('default.Writing Style')}}:</span>
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
					
					<div class="col-12 col-lg-6">
						<span for="narrativeStyle" class="form-label">{{__('default.Narrative Style')}}:</span>
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
				</div>
				
				<div class="row">
					<div class="col-12 col-lg-6">
						<button type="button" class="btn btn-success mt-1 mb-1 w-100" id="recreateBeats"><i
								class="bi bi-pencil"></i> {{__('default.Recreate Beats')}}</button>
						<a href="{{route('book.codex',[$book_slug])}}" target="_blank" class="btn btn-primary mb-3 mt-1 w-100" id="openCodexBtn">
							<i class="bi bi-book"></i> {{__('default.Open Codex')}}
						</a>
						
						
						<a href="{{route('edit-book', $book_slug)}}" class="btn btn-primary mb-1 mt-1 w-100"
						   title="{{__('default.Back to Chapters')}}"><i
								class="bi bi-book"></i> {{__('default.Back to Chapters')}}</a>
						<a href="{{route('user.showcase-library')}}" class="mb-1 mt-1 btn btn-primary w-100"><i
								class="bi bi-bookshelf"></i> {{__('default.Back to Books')}}</a>
					
					
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
								     data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}">
									<h6>{{__('default.Beat')}} {{$index+1}}</h6>
									
									<ul class="nav nav-tabs" id="beatTabs_{{$chapter_index}}_{{$index}}" role="tablist">
										<li class="nav-item" role="presentation">
											<button class="nav-link active" id="description-tab-{{$chapter_index}}-{{$index}}"
											        data-bs-toggle="tab" data-bs-target="#description-{{$chapter_index}}-{{$index}}"
											        type="button" role="tab" aria-controls="description"
											        aria-selected="true">{{__('default.Description')}}</button>
										</li>
										<li class="nav-item" role="presentation">
											<button class="nav-link" id="text-tab-{{$chapter_index}}-{{$index}}" data-bs-toggle="tab"
											        data-bs-target="#text-{{$chapter_index}}-{{$index}}" type="button" role="tab"
											        aria-controls="text" aria-selected="false">{{__('default.Text')}}</button>
										</li>
										<li class="nav-item" role="presentation">
											<button class="nav-link" id="summary-tab-{{$chapter_index}}-{{$index}}" data-bs-toggle="tab"
											        data-bs-target="#summary-{{$chapter_index}}-{{$index}}" type="button" role="tab"
											        aria-controls="summary" aria-selected="false">{{__('default.Summary')}}</button>
										</li>
									</ul>
									
									<div class="tab-content" id="beatTabContent_{{$chapter_index}}_{{$index}}">
										<div class="tab-pane fade show active" id="description-{{$chapter_index}}-{{$index}}"
										     role="tabpanel" aria-labelledby="description-tab-{{$chapter_index}}-{{$index}}">
											<div id="beatDescriptionContainer_{{$chapter_index}}_{{$index}}">
											<textarea id="beatDescription_{{$chapter_index}}_{{$index}}"
											          class="form-control beat-description-textarea"
											          rows="3">{{$beat['description'] ?? ''}}</textarea>
												<div id="beatDescriptionResult_{{$chapter_index}}_{{$index}}"></div>
												<button id="writeBeatDescriptionBtn_{{$chapter_index}}_{{$index}}"
												        data-chapter-index="{{$chapter_index}}"
												        data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}"
												        class="writeBeatDescriptionBtn btn btn-primary mt-3 me-2">{{__('default.Write Beat Description')}}</button>
											</div>
										</div>
										
										<div class="tab-pane fade" id="text-{{$chapter_index}}-{{$index}}" role="tabpanel"
										     aria-labelledby="text-tab-{{$chapter_index}}-{{$index}}">
											<div id="beatTextArea_{{$chapter_index}}_{{$index}}" class="mt-3">
											<textarea id="beatText_{{$chapter_index}}_{{$index}}" class="form-control beat-text-textarea"
											          rows="10">{{$beat['beat_text'] ?? ''}}</textarea>
												<div id="beatTextResult_{{$chapter_index}}_{{$index}}"></div>
												<button id="writeBeatTextBtn_{{$chapter_index}}_{{$index}}"
												        data-chapter-index="{{$chapter_index}}"
												        data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}"
												        class="writeBeatTextBtn btn btn-primary mt-3 me-2">{{__('default.Write Beat Text')}}</button>
												<button class="saveBeatBtn btn btn-success mt-3 me-2" data-chapter-index="{{$chapter_index}}"
												        data-chapter-filename="{{$chapter['chapterFilename']}}"
												        data-beat-index="{{$index}}">{{__('default.Save Beat')}}</button>
												@if($index == 0)
													<button class="addEmptyBeatBtn btn btn-primary mt-3 me-2" data-position="before"
													        data-chapter-index="{{$chapter_index}}"
													        data-chapter-filename="{{$chapter['chapterFilename']}}"
													        data-beat-index="{{$index}}">{{__('default.Add Empty Beat Before')}}</button>
												@endif
												<button class="addEmptyBeatBtn btn btn-primary mt-3 me-2" data-position="after"
												        data-chapter-index="{{$chapter_index}}"
												        data-chapter-filename="{{$chapter['chapterFilename']}}"
												        data-beat-index="{{$index}}">{{__('default.Add Empty Beat After')}}</button>
												<div class="me-auto d-inline-block"
												     id="beatDetailModalResult_{{$chapter_index}}_{{$index}}"></div>
											</div>
										</div>
										
										<div class="tab-pane fade" id="summary-{{$chapter_index}}-{{$index}}" role="tabpanel"
										     aria-labelledby="summary-tab-{{$chapter_index}}-{{$index}}">
											<div id="beatSummaryArea_{{$chapter_index}}_{{$index}}" class="mt-3">
											<textarea id="beatSummary_{{$chapter_index}}_{{$index}}"
											          class="form-control beat-summary-textarea"
											          rows="3">{{$beat['beat_summary'] ?? ''}}</textarea>
												<div id="beatSummaryResult_{{$chapter_index}}_{{$index}}"></div>
												<button id="writeBeatSummaryBtn_{{$chapter_index}}_{{$index}}"
												        data-chapter-index="{{$chapter_index}}"
												        data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}"
												        class="writeBeatSummaryBtn btn btn-primary mt-3 me-2">{{__('default.Write Summary')}}</button>
											</div>
										</div>
									</div>
								</div>
							
							@endforeach
						</div>
					</div>
				</div>
			@endforeach
		@endforeach
		
		<button type="button" class="btn btn-primary mt-2 mb-1 w-100"
		        id="saveBeatsBtn"><i
				class="bi bi-file-earmark-text-fill"></i> {{__('default.Save Beats')}}</button>
	
	</div>
</main>

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
				<h5 class="modal-title" id="alertModalLabel">Alert</h5>
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
<script>
	
	function recreateBeats(selectedChapter, beatsPerChapter = 3, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
		$('#fullScreenOverlay').removeClass('d-none');
		$("#recreateBeats").prop('disabled', true);
		
		// Clear existing beats
		$('#beatsList').empty();
		
		// Now proceed with creating beats
		$.ajax({
			url: `/book/write-beats/${bookSlug}/${selectedChapter}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beats_per_chapter: beatsPerChapter,
				writing_style: writingStyle,
				narrative_style: narrativeStyle,
				save_results: false,
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				$('#fullScreenOverlay').addClass('d-none');
				if (response.success) {
					response.beats.forEach((beat, beatIndex) => {
						let chapterIndex = selectedChapterIndex;
						
						const beatHtml = `
        <div class="mb-3 beat-outer-container" data-chapter-index="${chapterIndex}"
             data-chapter-filename="${selectedChapter}"
             data-beat-index="${beatIndex}">
            <h6>Beat ${beatIndex + 1}</h6>
            <div id="beatDescriptionContainer_${chapterIndex}_${beatIndex}">
                <label for="beatDescription_${chapterIndex}_${beatIndex}"
                       class="form-label">{{__('default.Beat Description')}}</label>
                <textarea id="beatDescription_${chapterIndex}_${beatIndex}"
                          class="form-control beat-description-textarea"
                          rows="3">${beat.description}</textarea>
            </div>
        </div>
    `;
						$('#beatsList').append(beatHtml);
						
					});
					
					$("#alertModalContent").html("{{__('default.All chapter Beat Descriptions generated successfully.')}}<br>{{__('default.Please review the beats and click "Save Beats" to save the changes.')}}<br>{{__('default.You will need to save the beats before proceeding to write the beat contents.')}}");
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					$("#saveBeatsBtn").show();
					$('#recreateBeats').prop('disabled', false);
					
				} else {
					$('#fullScreenOverlay').addClass('d-none');
					$("#alertModalContent").html("{{__('default.Failed to create beats: ')}}" + response.message);
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					
					$("#recreateBeats").prop('disabled', false);
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$("#alertModalContent").html("{{__('default.An error occurred while creating beats.')}}");
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
			}
		});
	}
	
	//------------------------------------------------------------
	function writeBeatDescription(beatDescription, beatIndex, chapterIndex, chapterFilename, showOverlay = true, save_results = false, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
		return new Promise((resolve, reject) => {
			if (showOverlay) {
				$('#fullScreenOverlay').removeClass('d-none');
			}
			
			$("#writeBeatDescriptionBtn_" + chapterIndex + '_' + beatIndex).prop('disabled', true);
			$("#beatDescriptionResult_" + chapterIndex + '_' + beatIndex).html("{{__('default.Writing beat description...')}}");
			
			$.ajax({
				url: `/book/write-beat-description/${bookSlug}/${chapterFilename}`,
				method: 'POST',
				data: {
					llm: savedLlm,
					writing_style: writingStyle,
					narrative_style: narrativeStyle,
					beat_index: beatIndex,
					current_beat: beatDescription,
					save_results: save_results,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					if (response.success) {
						$('#beatDescription_' + chapterIndex + '_' + beatIndex).val(response.prompt);
						$('#beatDescriptionResult_' + chapterIndex + '_' + beatIndex).html("{{__('default.Beat description generated successfully!')}}");
						$('#writeBeatDescriptionBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
						resolve(response.prompt);
					} else {
						$('#beatDescriptionResult_' + chapterIndex + '_' + beatIndex).html("{{__('default.Failed to write beat description:')}}" + response.message);
						reject("{{__('default.Failed to write beat description:')}}" + response.message);
					}
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
					$('#beatDescriptionResult_' + chapterIndex + '_' + beatIndex).html("{{__('default.Failed to write beat description:')}}");
					reject("{{__('default.Failed to write beat description:')}}");
				}
			});
		});
	}
	
	//------------------------------------------------------------
	function writeBeatText(beatDescription, beatIndex, chapterIndex, chapterFilename, showOverlay = true, save_results = false, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
		return new Promise((resolve, reject) => {
			if (showOverlay) {
				$('#fullScreenOverlay').removeClass('d-none');
			}
			
			$("#writeBeatTextBtn_" + chapterIndex + '_' + beatIndex).prop('disabled', true);
			$("#beatTextResult_" + chapterIndex + '_' + beatIndex).html("{{__('default.Writing beat text...')}}");
			
			$.ajax({
				url: `/book/write-beat-text/${bookSlug}/${chapterFilename}`,
				method: 'POST',
				data: {
					llm: savedLlm,
					writing_style: writingStyle,
					narrative_style: narrativeStyle,
					beat_index: beatIndex,
					current_beat: beatDescription,
					save_results: save_results,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					if (response.success) {
						$('#beatText_' + chapterIndex + '_' + beatIndex).val(response.prompt);
						$('#beatTextResult_' + chapterIndex + '_' + beatIndex).html("{{__('default.Beat text generated successfully!')}}");
						$('#writeBeatTextBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
						resolve(response.prompt);
					} else {
						$('#beatTextResult_' + chapterIndex + '_' + beatIndex).html("{{__('default.Failed to write beat text: ')}}" + response.message);
						reject("{{__('default.Failed to write beat text: ')}}" + response.message);
					}
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
					$('#beatTextResult_' + chapterIndex + '_' + beatIndex).html("{{__('default.Failed to write beat text.')}}");
					reject("{{__('default.Failed to write beat text.')}}");
				}
			});
		});
	}
	
	//------------------------------------------------------------
	function writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, showOverlay = true, save_results = false) {
		return new Promise((resolve, reject) => {
			if (showOverlay) {
				$('#fullScreenOverlay').removeClass('d-none');
			}
			$('#writeBeatSummaryBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', true);
			$('#beatSummaryResult_' + chapterIndex + '_' + beatIndex).html("{{__('default.Writing beat summary...')}}");
			
			$.ajax({
				url: `/book/write-beat-summary/${bookSlug}/${chapterFilename}`,
				method: 'POST',
				data: {
					llm: savedLlm,
					beat_index: beatIndex,
					current_beat_description: beatDescription,
					current_beat_text: beatText,
					save_results: save_results,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					if (response.success) {
						$('#beatSummary_' + chapterIndex + '_' + beatIndex).val(response.prompt);
						$('#beatSummaryResult_' + chapterIndex + '_' + beatIndex).html("{{__('default.Beat summary generated successfully!')}}");
						$('#writeBeatSummaryBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
						resolve(response.prompt);
					} else {
						$('#beatSummaryResult_' + chapterIndex + '_' + beatIndex).html("{{__('default.Failed to write summary: ')}}" + response.message);
						reject("{{__('default.Failed to write summary: ')}}" + response.message);
					}
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
					$('#beatSummaryResult_' + chapterIndex + '_' + beatIndex).html("{{__('default.Failed to write beat summary.')}}");
					reject("{{__('default.Failed to write beat summary.')}}");
				}
			});
		});
	}
	
	//------------------------------------------------------------
	function saveBeat(beatText, beatSummary, beatDescription, beatIndex, chapterIndex, chapterFilename) {
		$.ajax({
			url: `/book/save-single-beat/${bookSlug}/${chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beat_index: beatIndex,
				beat_description: beatDescription,
				beat_text: beatText,
				beat_summary: beatSummary,
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					$("#beatDetailModalResult_" + chapterIndex + "_" + beatIndex).html("{{__('default.Beat saved successfully!')}}");
				} else {
					$("#beatDetailModalResult_" + chapterIndex + "_" + beatIndex).html("{{__('default.Failed to save beat: ')}}" + response.message);
				}
			}
		});
	}
	
	//------------------------------------------------------------
	$(document).ready(function () {
		
		if (selectedChapter !== '') {
			$("#saveBeatsBtn").hide();
			$('#recreateBeats').show();
			$('#beatsPerChapter').show();
			$('#beatsPerChapterLabel').show();
		} else {
			$("#saveBeatsBtn").hide();
			$('#recreateBeats').hide();
			$('#beatsPerChapter').hide();
			$('#beatsPerChapterLabel').hide();
		}
		
		//check if the beat-description-textarea is empty
		let emptyBeatDescriptions = true;
		$('.beat-description-textarea').each(function (index, element) {
			if ($(element).val().trim() !== '') {
				emptyBeatDescriptions = false;
			}
		});
		
		if (emptyBeatDescriptions) {
			//show the alert modal
			$("#alertModalContent").html("{{__('default.This chapter has no beats written yet.')}}<br>{{__('default.Click "Recreate Beats" to generate beat descriptions.')}}<span style=\"font-size: 18px;\">{{__('default.Number of beats per chapter:')}}</span><br><br>\n" +
				"\t\t\t\t\t\t<select id=\"beatsPerChapter_modal\" class=\"form-select mx-auto mb-1\">\n" +
				"\t\t\t\t\t\t\t<option value=\"2\" selected>2</option>\n" +
				"\t\t\t\t\t\t\t<option value=\"3\">3</option>\n" +
				"\t\t\t\t\t\t\t<option value=\"4\">4</option>\n" +
				"\t\t\t\t\t\t\t<option value=\"5\">5</option>\n" +
				"\t\t\t\t\t\t\t<option value=\"6\">6</option>\n" +
				"\t\t\t\t\t\t\t<option value=\"7\">7</option>\n" +
				"\t\t\t\t\t\t\t<option value=\"8\">8</option>\n" +
				"\t\t\t\t\t\t\t<option value=\"9\">9</option>\n" +
				"\t\t\t\t\t\t\t<option value=\"10\">10</option>\n" +
				"\t\t\t\t\t\t</select>" + "<br><button type=\"button\" class=\"btn btn-success mt-1 mb-1 w-100\" id=\"recreateBeats_modal\"><i\n" +
				"\t\t\t\t\t\t\t\tclass=\"bi bi-pencil\"></i>{{__('default.Recreate Beats')}}</button>");
			$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
			$("#recreateBeats_modal").off('click').on('click', function () {
				$("#alertModal").modal('hide');
				recreateBeats(selectedChapter + '.json', parseInt($('#beatsPerChapter_modal').val()), $('#writingStyle').val(), $('#narrativeStyle').val());
			});
			
		}
		
		
		$('.addEmptyBeatBtn').off('click').on('click', function () {
			let chapterIndex = $(this).data('chapter-index');
			let chapterFilename = $(this).data('chapter-filename');
			let beatIndex = $(this).data('beat-index');
			let position = $(this).data('position');
			
			let newBeat = {
				description: '',
				beat_text: '',
				beat_summary: ''
			};
			
			$.ajax({
				url: `/book/add-empty-beat/${bookSlug}/${chapterFilename}`,
				method: 'POST',
				data: {
					beat_index: beatIndex,
					position: position,
					new_beat: JSON.stringify(newBeat)
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function (response) {
					if (response.success) {
						location.reload(); // Refresh the page
					} else {
						$("#alertModalContent").html('Failed to add empty beat: ' + response.message);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				},
				error: function () {
					$("#alertModalContent").html('An error occurred while adding the empty beat.');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			});
		});
		
		$('.closeAndRefreshButton').on('click', function () {
			location.reload();
		});
		
		$('.saveBeatBtn').off('click').on('click', function () {
			let beatIndex = Number($(this).attr('data-beat-index'));
			let chapterIndex = Number($(this).attr('data-chapter-index'));
			let chapterFilename = $(this).attr('data-chapter-filename');
			
			let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
			let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
			let beatSummary = $('#beatSummary_' + chapterIndex + '_' + beatIndex).val();
			
			saveBeat(beatText, beatSummary, beatDescription, beatIndex, chapterIndex, chapterFilename);
		});
		
		$('.writeBeatDescriptionBtn').off('click').on('click', function () {
			let beatIndex = Number($(this).attr('data-beat-index'));
			let chapterIndex = Number($(this).attr('data-chapter-index'));
			let chapterFilename = $(this).attr('data-chapter-filename');
			
			let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
			
			writeBeatDescription(beatDescription, beatIndex, chapterIndex, chapterFilename, true, false, $('#writingStyle').val(), $('#narrativeStyle').val());
		});
		
		$('.writeBeatTextBtn').off('click').on('click', function () {
			let beatIndex = Number($(this).attr('data-beat-index'));
			let chapterIndex = Number($(this).attr('data-chapter-index'));
			let chapterFilename = $(this).attr('data-chapter-filename');
			
			let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
			
			writeBeatText(beatDescription, beatIndex, chapterIndex, chapterFilename, true, false, $('#writingStyle').val(), $('#narrativeStyle').val());
		});
		
		$('.writeBeatSummaryBtn').off('click').on('click', function () {
			let beatIndex = Number($(this).attr('data-beat-index'));
			let chapterIndex = Number($(this).attr('data-chapter-index'));
			let chapterFilename = $(this).attr('data-chapter-filename');
			
			let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
			let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
			writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, true, false);
		});
		
		$("#recreateBeats").on('click', function (e) {
			e.preventDefault();
			recreateBeats(selectedChapter + '.json', parseInt($('#beatsPerChapter').val()), $('#writingStyle').val(), $('#narrativeStyle').val());
		});
		
		$('#saveBeatsBtn').on('click', function (e) {
			e.preventDefault();
			
			let beats = [];
			
			$('#beatsList').find('.beat-outer-container').each(function (index, element) {
				let beatDescription = $(element).find('.beat-description-textarea').val();
				let beatText = $(element).find('.beat-text-textarea').val() || '';
				let beatSummary = $(element).find('.beat-summary-textarea').val() || '';
				beats.push({description: beatDescription, beat_text: beatText, beat_summary: beatSummary});
			});
			
			$.ajax({
				url: `/book/save-beats/${bookSlug}/${selectedChapter}.json`,
				method: 'POST',
				data: {
					llm: savedLlm,
					beats: JSON.stringify(beats)
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						$("#alertModalContent").html("{{__('default.Beats saved successfully!')}}");
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						setTimeout(function () {
							location.reload();
						}, 2500);
						
					} else {
						$("#alertModalContent").html("{{__('default.Failed to save beats: ')}}" + response.message);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				}
			});
		});
		
	});


</script>

</body>
</html>
