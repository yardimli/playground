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
		bookData = @json($bookData);
		let bookSlug = "{{$bookSlug}}";
	</script>

</head>
<body>

<main class="py-1">
	
	<div class="container mt-2">
		<h1 style="margin:10px;" class="text-center" id="bookTitle">Codex for {{ $bookData['title'] }}</h1>
		<div class="mb-1 mt-1 w-100" style="text-align: right;">
			<a href="{{route('user.book-details', $bookSlug)}}" class="btn btn-primary">Back to Book</a>
		</div>
		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
			</div>
			<div class="card-body modal-content modal-content-color">
					<div class="mb-3">
						<label for="characters" class="form-label">Characters</label>
						<textarea class="form-control" id="characters"
						          rows="5">{{ $bookData['codex']['characters'] }}</textarea>
					</div>
					<div class="mb-3">
						<label for="locations" class="form-label">Locations</label>
						<textarea class="form-control" id="locations"
						          rows="5">{{ $bookData['codex']['locations'] }}</textarea>
					</div>
					<div class="mb-3">
						<label for="objects" class="form-label">Objects/Items</label>
						<textarea class="form-control" id="objects"
						          rows="5">{{ $bookData['codex']['objects'] }}</textarea>
					</div>
					<div class="mb-3">
						<label for="lore" class="form-label">Lore</label>
						<textarea class="form-control" id="lore" rows="5">{{ $bookData['codex']['lore'] }}</textarea>
					</div>
					<button id="saveCodex" class="btn btn-primary">Save Codex</button>
			</div>
		
		</div>
		
		<hr>
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
		
		<h3>Chapters and Beats</h3>
		<div id="chaptersAndBeats">
			<?php
//				dd($bookData);
			?>
			@foreach($bookData['acts'] as $act)
				@foreach($act['chapters'] as $chapter)
					<h4>{{ $chapter['name'] }}</h4>
					@if(isset($chapter['beats']))
						@foreach($chapter['beats'] as $beatIndex => $beat)
							@if(!empty($beat['beat_text']))
								@php
									$checkbox_checked = '';
									if (!empty($bookData['codex']['beats'])) {
										for ($i = 0; $i < count($bookData['codex']['beats']); $i++) {
											if ($bookData['codex']['beats'][$i]['chapterFilename'] === $chapter['chapterFilename'] && $bookData['codex']['beats'][$i]['beatIndex'] == $beatIndex) {
												$checkbox_checked = 'checked';
												break;
											}
										}
									}
								@endphp
								
								<div class="form-check">
									<input class="form-check-input" type="checkbox"
									       value="{{ $chapter['chapterFilename'] }}-!-!-{{ $beatIndex }}"
									       id="beat-{{ $chapter['chapterFilename'] }}-{{ $beatIndex }}" {{$checkbox_checked}}>
									<label class="form-check-label" for="beat-{{ $chapter['chapterFilename'] }}-{{ $beatIndex }}">
										Beat {{ $beatIndex + 1 }} - {{ $beat['description'] ?? '' }}
									</label>
									<button data-id="beat-{{ $chapter['chapterFilename'] }}-{{ $beatIndex }}"
									        data-chapterfilename="{{ $chapter['chapterFilename'] }}" data-beatindex="{{ $beatIndex }}"
									        class="btn updateCodexFromBeat btn-secondary mt-1 mb-3">Update Codex from Beat
									</button>
								</div>
							@endif
						@endforeach
					@else
						<p>No beats found</p>
					@endif
				@endforeach
			@endforeach
		</div>
	
	</div>
</main>


<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="/js/jquery-3.7.0.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/moment.min.js"></script>

<script src="/js/jspdf.umd.min.js"></script>
<script src="/js/docx.js"></script>

<!-- Your custom scripts -->
<script src="/js/custom-ui.js"></script>


<script>
	$(document).ready(function () {
		$('#saveCodex').on('click', function (e) {
			e.preventDefault();
			
			$.ajax({
				url: '/book/{{ $bookSlug }}/codex',
				method: 'POST',
				data: {
					characters: $('#characters').val(),
					locations: $('#locations').val(),
					objects: $('#objects').val(),
					lore: $('#lore').val(),
					_token: '{{ csrf_token() }}'
				},
				success: function (response) {
					alert('Codex saved successfully');
				},
				error: function () {
					alert('Error saving codex');
				}
			});
		});
		
		$('.updateCodexFromBeat').on('click', function () {
			let chapterFilename = $(this).data('chapterfilename');
			let beatIndex = $(this).data('beatindex');
			let checkboxId = $(this).data('id');
			let checkboxChecked = $('#' + checkboxId).prop('checked');
			checkboxChecked = checkboxChecked ? 'true' : 'false';
			
			$.ajax({
				url: '/book/{{ $bookSlug }}/update-codex-from-beats',
				method: 'POST',
				data: {
					llm: savedLlm,
					chapterFilename: chapterFilename,
					beatIndex: beatIndex,
					checkboxChecked: checkboxChecked,
					
					_token: '{{ csrf_token() }}'
				},
				success: function (response) {
					if (response.success) {
						$("#characters").val(response.codex_character_results);
						$("#locations").val(response.codex_location_results);
						$("#objects").val(response.codex_object_results);
						$("#lore").val(response.codex_lore_results);
						
						alert('Codex updated successfully');
						// location.reload();
					} else {
						alert('Error updating codex');
					}
				},
				error: function () {
					alert('Error updating codex');
				}
			});
		});
		
	});
</script>


</body>
</html>
