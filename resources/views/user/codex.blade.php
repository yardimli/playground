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
		let bookData = @json($bookData);
		let bookSlug = "{{$bookSlug}}";
	</script>

</head>
<body>

<main class="py-1">
	
	<div class="container mt-2">
		<h1 style="margin:10px;" class="text-center" id="bookTitle">Codex for {{ $bookData['title'] }}</h1>
		<div class="mb-1 mt-1 w-100" style="text-align: right;">
			<a href="{{route('edit-book', $bookSlug)}}" class="btn btn-primary">Back to Book</a>
		</div>
		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
			</div>
			<div class="card-body modal-content modal-content-color">
				
				<div class="mb-3">
					<label for="characters" class="form-label">Characters</label>
					<div class="row">
						<div class="col-12" id="charactersCol">
            <textarea class="form-control" id="characters"
                      rows="5"
                      data-original="{{ $bookData['codex']['characters'] }}">{{ $bookData['codex']['characters'] }}</textarea>
						</div>
						<div class="col-6" id="charactersDiffCol" style="display: none;">
							<div class="mb-3 modal-header-color" id="charactersDiff"></div>
						</div>
					</div>
				</div>
				
				<div class="mb-3">
					<label for="locations" class="form-label">Locations</label>
					<div class="row">
						<div class="col-12" id="locationsCol">
            <textarea class="form-control" id="locations"
                      rows="5"
                      data-original="{{ $bookData['codex']['locations'] }}">{{ $bookData['codex']['locations'] }}</textarea>
						</div>
						<div class="col-6" id="locationsDiffCol" style="display: none;">
							<div class="mb-3 modal-header-color" id="locationsDiff"></div>
						</div>
					</div>
				</div>
				
				<div class="mb-3">
					<label for="objects" class="form-label">Objects/Items</label>
					<div class="row">
						<div class="col-12" id="objectsCol">
            <textarea class="form-control" id="objects"
                      rows="5"
                      data-original="{{ $bookData['codex']['objects'] }}">{{ $bookData['codex']['objects'] }}</textarea>
						</div>
						<div class="col-6" id="objectsDiffCol" style="display: none;">
							<div class="mb-3 modal-header-color" id="objectsDiff"></div>
						</div>
					</div>
				</div>
				
				<div class="mb-3">
					<label for="lore" class="form-label">Lore</label>
					<div class="row">
						<div class="col-12" id="loreCol">
            <textarea class="form-control" id="lore"
                      rows="5"
                      data-original="{{ $bookData['codex']['lore'] }}">{{ $bookData['codex']['lore'] }}</textarea>
						</div>
						<div class="col-6" id="loreDiffCol" style="display: none;">
							<div class="mb-3 modal-header-color" id="loreDiff"></div>
						</div>
					</div>
				</div>
				<button id="saveCodex" class="btn btn-primary">Save Codex</button>
			</div>
		
		</div>
		
		<hr>
		
		<div class="mb-3">
			<label for="llmSelect" class="form-label">{{__('default.AI Engines:')}}
				@if (Auth::user() && Auth::user()->isAdmin())
					<label class="badge bg-danger">Admin</label>
				@endif
			
			</label>
			<select id="llmSelect" class="form-select mx-auto">
				<option value="">{{__('default.Select an AI Engine')}}</option>
				@if (Auth::user() && Auth::user()->isAdmin())
					<option value="anthropic-sonet">anthropic :: claude-3.5-sonnet (direct)</option>
					<option value="anthropic-haiku">anthropic :: haiku (direct)</option>
					<option value="open-ai-gpt-4o">openai :: gpt-4o (direct)</option>
					<option value="open-ai-gpt-4o-mini">openai :: gpt-4o-mini (direct)</option>
				@endif
			</select>
		</div>
		<div class="mt-1 mb-5 small" style="border: 1px solid #ccc; border-radius: 5px; padding: 5px;">
			<div id="modelDescription"></div>
			<div id="modelPricing"></div>
		</div>
		
		
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
									       id="beat-{{ $chapter['chapterFilename'] }}-{{ $beatIndex }}"
									       name="beat-{{ $chapter['chapterFilename'] }}-{{ $beatIndex }}" {{$checkbox_checked}}>
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

<script src="/js/jspdf.umd.min.js"></script>
<script src="/js/docx.js"></script>
<script src="/js/diff.js"></script>

<!-- Your custom scripts -->
<script src="/js/custom-ui.js"></script>


<script>
	
	function setTextareaHeight(field) {
		var textarea = document.getElementById(field);
		var diffDiv = document.getElementById(field + 'Diff');
		textarea.style.height = diffDiv.offsetHeight + 'px';
	}
	
	function showDiff(field, newText) {
		var oldText = $('#' + field).data('original');
		var diff = Diff.diffLines(oldText, newText);
		var display = document.getElementById(field + 'Diff');
		display.innerHTML = '';
		
		diff.forEach((part) => {
			var color = part.added ? 'green' :
				part.removed ? 'red' : 'grey';
			var span = document.createElement('span');
			span.style.color = color;
			
			var lines = part.value.split('\n');
			lines.forEach((line, index) => {
				if (index > 0) {
					span.appendChild(document.createElement('br'));
				}
				span.appendChild(document.createTextNode(line));
			});
			
			display.appendChild(span);
		});
		
		$('#' + field).val(newText).data('original', newText);
		
		// Adjust layout
		$('#' + field + 'Col').removeClass('col-12').addClass('col-6');
		$('#' + field + 'DiffCol').show();
		
		setTextareaHeight(field);
		
	}
	
	function resetLayout() {
		['characters', 'locations', 'objects', 'lore'].forEach(field => {
			$('#' + field + 'Col').removeClass('col-6').addClass('col-12');
			$('#' + field + 'DiffCol').hide();
		});
	}
	
	function getLLMsData() {
		return new Promise((resolve, reject) => {
			$.ajax({
				url: '/check-llms-json',
				type: 'GET',
				success: function (data) {
					resolve(data);
				},
				error: function (xhr, status, error) {
					reject(error);
				}
			});
		});
	}
	
	function linkify(text) {
		const urlRegex = /(https?:\/\/[^\s]+)/g;
		return text.replace(urlRegex, function (url) {
			return '<a href="' + url + '" target="_blank" rel="noopener noreferrer">' + url + '</a>';
		});
	}
	
	
	$(document).ready(function () {
		
		$('#alertModal').on('hidden.bs.modal', function () {
			if ($('#alertModalContent').text().trim() === 'Codex saved successfully') {
				location.reload();
			}
		});
		
		getLLMsData().then(function (llmsData) {
			const llmSelect = $('#llmSelect');
			
			llmsData.forEach(function (model) {
				
				// Calculate and display pricing per million tokens
				let promptPricePerMillion = ((model.pricing.prompt || 0) * 1000000).toFixed(2);
				let completionPricePerMillion = ((model.pricing.completion || 0) * 1000000).toFixed(2);
				
				llmSelect.append($('<option>', {
					value: model.id,
					text: model.name + ' - $' + promptPricePerMillion + ' / $' + completionPricePerMillion,
					'data-description': model.description,
					'data-prompt-price': model.pricing.prompt || 0,
					'data-completion-price': model.pricing.completion || 0,
				}));
			});
			
			// Set the saved LLM if it exists
			if (savedLlm) {
				llmSelect.val(savedLlm);
			}
			
			// Show description on change
			llmSelect.change(function () {
				const selectedOption = $(this).find('option:selected');
				const description = selectedOption.data('description');
				const promptPrice = selectedOption.data('prompt-price');
				const completionPrice = selectedOption.data('completion-price');
				$('#modelDescription').html(linkify(description || ''));
				
				// Calculate and display pricing per million tokens
				const promptPricePerMillion = (promptPrice * 1000000).toFixed(2);
				const completionPricePerMillion = (completionPrice * 1000000).toFixed(2);
				
				$('#modelPricing').html(`
                <strong>Pricing (per million tokens):</strong> Prompt: $${promptPricePerMillion} - Completion: $${completionPricePerMillion}
            `);
			});
			
			// Trigger change to show initial description
			llmSelect.trigger('change');
		}).catch(function (error) {
			console.error('Error loading LLMs data:', error);
		});
		
		$('#saveCodex').on('click', function (e) {
			e.preventDefault();
			$('#fullScreenOverlay').removeClass('d-none');
			
			// Collect checked beats
			let checkedBeats = [];
			$('.form-check-input:checked').each(function() {
				let [chapterFilename, beatIndex] = $(this).val().split('-!-!-');
				checkedBeats.push({chapterFilename, beatIndex: parseInt(beatIndex)});
			});
			
			$.ajax({
				url: '/book/{{ $bookSlug }}/codex',
				method: 'POST',
				data: {
					characters: $('#characters').val(),
					locations: $('#locations').val(),
					objects: $('#objects').val(),
					lore: $('#lore').val(),
					beats: checkedBeats,
					_token: '{{ csrf_token() }}'
				},
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					$("#alertModalContent").html('Codex saved successfully');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
					$("#alertModalContent").html('Error saving codex');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			});
		});
		
		$('.updateCodexFromBeat').on('click', function () {
			
			let chapterFilename = $(this).data('chapterfilename');
			let beatIndex = $(this).data('beatindex');
			console.log(chapterFilename, beatIndex, $(this).data('id'));
			document.getElementById($(this).data('id')).checked = true;
			
			$('#fullScreenOverlay').removeClass('d-none');
			$.ajax({
				url: '/book/{{ $bookSlug }}/update-codex-from-beat',
				method: 'POST',
				data: {
					llm: savedLlm,
					chapterFilename: chapterFilename,
					beatIndex: beatIndex,
					
					_token: '{{ csrf_token() }}'
				},
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					if (response.success) {
						
						$('.updateCodexFromBeat').prop('disabled', true);
						
						showDiff('characters', response.codex_character_results);
						showDiff('locations', response.codex_location_results);
						showDiff('objects', response.codex_object_results);
						showDiff('lore', response.codex_lore_results);
						
						$("#diffView").show();
						
						$("#alertModalContent").html('Codex updated successfully');
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						// location.reload();
					} else {
						$('#fullScreenOverlay').addClass('d-none');
						$("#alertModalContent").html('Error updating codex (1)');
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
					$("#alertModalContent").html('Error updating codex (2)');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			});
		});
		
	});
</script>


</body>
</html>
