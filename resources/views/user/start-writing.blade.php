@extends('layouts.app')

@section('title', 'Start Writing')

@section('content')
	<style>
      #fullScreenOverlay {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.7);
          z-index: 9999;
          display: flex;
          justify-content: center;
          align-items: center;
      }

      .overlay-content {
          text-align: center;
      }


      #charCount {
          font-size: 0.9em;
      }
      #charCount.valid {
          color: green;
      }
      #charCount.invalid {
          color: red;
      }
	
	</style>
	
	<main class="pt-5">
		
		<!-- Content -->
		<div class="page-content site-theme-div">
			<!-- contact area -->
			<div class="content-block">
				<!-- Browse Jobs -->
				<section class="content-inner site-theme-div">
					<div class="container">
						<div class="row">
							<div class="col-xl-3 col-lg-4 mb-5">
								<img alt="" src="{{$coverFilename}}">
							</div>
							<div class="col-xl-9 col-lg-8 mb-5">
								<div class="shop-bx shop-profile">
									<div class="shop-bx-title clearfix">
										<h5 class="text-uppercase">{{__('default.Add Book')}}</h5>
									</div>
									
									
									<div class="mb-3">
										<label for="user_blurb" class="form-label">{{__('default.Book Description')}}:</label>
										<textarea class="form-control" id="user_blurb" name="user_blurb" required
										          placeholder="{{__('default.describe your books story, people and events. While you can just say \'A Boy Meets World\' the longer and more detailed your blurb is the more creative and unique the writing will be.')}}"
										          rows="8"></textarea>
										<div id="charCount" class="mt-2">0/2000</div>
									</div>
									<div class="mb-3">
										<label for="language" class="form-label">{{__('default.Language')}}:</label>
										
										<select class="form-control" id="language" name="language" required>
											<option
												value="{{__('default.English')}}" <?php if (__('default.Default Language') === __('default.English')) {
												echo " SELECTED";
											} ?>>{{__('default.English')}}</option>
											<option
												value="{{__('default.Norwegian')}}" <?php if (__('default.Default Language') === __('default.Norwegian')) {
												echo " SELECTED";
											} ?>>{{__('default.Norwegian')}}</option>
											<option
												value="{{__('default.Turkish')}}" <?php if (__('default.Default Language') === __('default.Turkish')) {
												echo " SELECTED";
											} ?>>{{__('default.Turkish')}}</option>
										</select>
									</div>
									
									<div class="row">
										<div class="mb-3 col-12 col-xl-6">
											<label for="language" class="form-label">{{__('default.Book Structure')}}:</label>
											
											<select class="form-control" id="bookStructure" name="bookStructure" required>
												<option
													value="{{__('default.the_1_act_story.txt')}}">{{__('default.The 1 Act Story (1 Act, 3 Chapters)')}}</option>
												<option
													value="{{__('default.abcde_short_story.txt')}}">{{__('default.ABCDE (1 Acts, 6 Chapters)')}}</option>
												<option
													value="{{__('default.fichtean_curve.txt')}}">{{__('default.Fichtean Curve (3 Acts, 8 Chapters)')}}</option>
												<option
													value="{{__('default.freytags_pyramid.txt')}}">{{__('default.Freytag\'s Pyramid (5 Acts, 9 Chapters)')}}</option>
												<option
													value="{{__('default.heros_journey.txt')}}">{{__('default.Hero\'s Journey (3 Acts, 12 Chapters)')}}</option>
												<option
													value="{{__('default.story_clock.txt')}}">{{__('default.Story Clock (4 Acts, 12 Chapters)')}}</option>
												<option
													value="{{__('default.save_the_cat.txt')}}">{{__('default.Save The Cat (4 Acts, 15 Chapters)')}}</option>
												<option
													value="{{__('default.dan_harmons_story_circle.txt')}}">{{__('default.Dan Harmon\'s Story Circle (8 Acts, 15 Chapters)')}}</option>
											</select>
										</div>
										
										<div class="mb-3 col-12 col-xl-6">
											<label for="llmSelect" class="form-label">{{__('default.AI Engines:')}}</label>
											<select id="llmSelect" class="form-select mx-auto mb-3">
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
									</div>
									
									<div class="row">
										<div class="mb-3 col-12 col-xl-6">
											<label for="adultContent" class="form-label">{{__('default.Content Type')}}:</label>
											<select class="form-control" id="adultContent" name="adultContent" required>
												<option value="non-adult">{{__('default.Non-Adult')}}</option>
												<option value="adult">{{__('default.Adult')}}</option>
											</select>
										</div>
										
										<div class="mb-3 col-12 col-xl-6">
											<label for="genre" class="form-label">{{__('default.Genre')}}:</label>
											<select class="form-control" id="genre" name="genre" required>
												<!-- Options will be populated dynamically -->
											</select>
										</div>
									</div>
									<div class="row">
										<div class="mb-3 col-12 col-xl-6">
											<label for="authorName" class="form-label">{{__('default.Author Name')}}:</label>
											<input type="text" class="form-control" id="authorName" name="authorName" required
											       value="{{ Auth::user()->name ?? 'Pen Name' }}">
										</div>
										<div class="mb-3 col-12 col-xl-6">
											<label for="publisherName" class="form-label">{{__('default.Publisher Name')}}:</label>
											<input type="text" class="form-control" id="publisherName" name="publisherName" required
											       value="WBWAI Publishing">
										</div>
									</div>
									<div class="row">
										<div class="mb-3 col-12 col-xl-6">
											<label for="writingStyle" class="form-label">{{__('default.Writing Style')}}:</label>
											<select class="form-control" id="writingStyle" name="writingStyle" required>
												@foreach($writingStyles as $style)
													<option value="{{ $style['value'] }}">{{ $style['label'] }}</option>
												@endforeach
											</select>
										</div>
										
										<div class="mb-3 col-12 col-xl-6">
											<label for="narrativeStyle" class="form-label">{{__('default.Narrative Style')}}:</label>
											<select class="form-control" id="narrativeStyle" name="narrativeStyle" required>
												@foreach($narrativeStyles as $style)
													<option value="{{ $style['value'] }}">{{ $style['value'] }}</option>
												@endforeach
											</select>
										</div>
									</div>
									
									<div class="mb-3" style="font-size: 14px;" id="hint_1">
										{{__('default.After clicking the submit button, the AI will first write the book\'s title and blurb and characters. You\'ll need to confirm the characters before the AI writes the book.')}}
									</div>
									<div class="mb-3 d-none alert alert-primary" style="font-size: 16px;" id="hint_2">
										{{__('default.Please verify the title, blurb the back cover text of the book and the characters of the story.')}}
										<br>
										{{__('default.After clicking the submit button, The AI will start creating all the chapters for the book. This process may take a few minutes.')}}
									</div>
									
									<div id="book_details" class="d-none">
										<div class="mb-3">
											<label for="book_title" class="form-label">{{__('default.Book Title')}}:</label>
											<input type="text" class="form-control" id="book_title" name="book_title" required>
										</div>
										
										<div class="mb-3">
											<label for="book_blurb" class="form-label">{{__('default.Book Blurb')}}:</label>
											<textarea class="form-control" id="book_blurb" name="book_blurb" required rows="8"></textarea>
										</div>
										
										<div class="mb-3">
											<label for="back_cover_text" class="form-label">{{__('default.Back Cover Text')}}:</label>
											<textarea class="form-control" id="back_cover_text" name="back_cover_text" required
											          rows="9"></textarea>
										</div>
										
										<div class="mb-3">
											<label for="character_profiles" class="form-label">{{__('default.Character Profiles')}}</label>
											<textarea class="form-control" id="character_profiles" name="character_profiles" required
											          rows="9"></textarea>
										</div>
									
									
									</div>
									<button id="addBookStepOneBtn" class="btn btn-primary btnhover"
									        style="min-width: 180px;">{{__('default.Submit')}}</button>
									<button id="addBookStepTwoBtn" class="btn btn-primary btnhover d-none"
									        style="min-width: 180px;">{{__('default.Submit')}}</button>
								
								
								</div>
							</div>
						</div>
					</div>
				</section>
				<!-- Browse Jobs END -->
			</div>
		</div>
	</main>
	<!-- Content END-->
	
	@include('layouts.footer')
	
	<div id="fullScreenOverlay" class="d-none">
		<div class="overlay-content">
			<div class="spinner-border text-light" role="status">
				<span class="visually-hidden">{{__('Loading...')}}</span>
			</div>
			<p class="mt-3 text-light">{{__('default.Processing your request. This may take a few minutes...')}}</p>
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

@endsection

@push('scripts')
	<!-- Inline JavaScript code -->
	<script>
		let savedLlm = localStorage.getItem('llm') || 'anthropic/claude-3-haiku:beta';
		
		let exampleQuestion = '';
		let exampleAnswer = '';
		let bookKeywords = '';
		let bookEditUrl = '';
		
		// Function to update genre dropdown
		function updateGenreDropdown(genres) {
			const genreDropdown = $('#genre');
			genreDropdown.empty();
			genres.forEach(genre => {
				genreDropdown.append($('<option></option>').val(genre).text(genre));
			});
		}
		
		$(document).ready(function () {
			
			$('#alertModal').on('hidden.bs.modal', function (e) {
				if (bookEditUrl) {
					window.location.href = bookEditUrl;
				}
			});
			
			const maxChars = 2000;
			const userBlurb = $('#user_blurb');
			const charCount = $('#charCount');
			const addBookStepOneBtn = $('#addBookStepOneBtn');
			
			function updateCharCount() {
				const remaining = maxChars - userBlurb.val().length;
				charCount.text(userBlurb.val().length + '/' + maxChars);
				
				if (remaining < 0) {
					charCount.removeClass('valid').addClass('invalid');
					addBookStepOneBtn.prop('disabled', true);
				} else {
					charCount.removeClass('invalid').addClass('valid');
					addBookStepOneBtn.prop('disabled', false);
				}
			}
			
			userBlurb.on('input', updateCharCount);
			
			// Initial call to set the correct state
			updateCharCount();
			
			// Define genre arrays
			const adultGenres = {!! json_encode($adult_genres_array) !!};
			const nonAdultGenres = {!! json_encode($genres_array) !!};
			
			// Initial genre dropdown population
			updateGenreDropdown(nonAdultGenres);
			
			// Handle adult content dropdown change
			$('#adultContent').on('change', function () {
				const selectedValue = $(this).val();
				if (selectedValue === 'adult') {
					updateGenreDropdown(adultGenres);
				} else {
					updateGenreDropdown(nonAdultGenres);
				}
			});
			
			
			$("#llmSelect").on('change', function () {
				localStorage.setItem('llm', $(this).val());
				savedLlm = $(this).val();
			});
			
			// change $llmSelect to savedLlm
			console.log('set llmSelect to ' + savedLlm);
			var dropdown = document.getElementById('llmSelect');
			var options = dropdown.getElementsByTagName('option');
			
			
			for (var i = 0; i < options.length; i++) {
				if (options[i].value === savedLlm) {
					dropdown.selectedIndex = i;
				}
			}
			
			$("#addBookStepOneBtn").on('click', function (event) {
				event.preventDefault();
				$('#fullScreenOverlay').removeClass('d-none');
				
				console.log("user_blurb value:", $('#user_blurb').val());
				
				$.ajax({
					url: '{{ route("write-book-character-profiles") }}',
					type: 'POST',
					data: {
						user_blurb: $('#user_blurb').val(),
						language: $('#language').val(),
						book_structure: $('#bookStructure').val(),
						author_name: $('#authorName').val(),
						publisher_name: $('#publisherName').val(),
						llm: savedLlm,
						adultContent: $('#adultContent').val(),
						genre: $('#genre').val(),
						writingStyle: $('#writingStyle').val(),
						narrativeStyle: $('#narrativeStyle').val(),
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function (data) {
						console.log(data);
						$('#fullScreenOverlay').addClass('d-none');
						if (data.success) {
							$('#addBookStepOneBtn').addClass('d-none');
							$('#hint_1').addClass('d-none');
							$('#hint_2').removeClass('d-none');
							
							$('#book_details').removeClass('d-none');
							$('#addBookStepTwoBtn').removeClass('d-none');
							$('#book_title').val(data.data.title);
							$('#book_blurb').val(data.data.blurb);
							$('#back_cover_text').val(data.data.back_cover_text);
							
							exampleQuestion = data.data.example_question;
							exampleAnswer = data.data.example_answer;
							bookKeywords = data.data.keywords;
							
							let characterProfiles = '';
							data.data.character_profiles.forEach(function (profile) {
								characterProfiles += (profile.name || '') + '\n' + (profile.description || '') + '\n\n';
							});
							
							$('#character_profiles').val(characterProfiles);
						} else {
							$("#alertModalContent").html("Error: " + data.message);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
					},
					error: function (xhr, status, error) {
						$('#fullScreenOverlay').addClass('d-none');
						
						$("#alertModalContent").html("Error: " + error);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				});
			});
			
			$("#addBookStepTwoBtn").on('click', function (event) {
				event.preventDefault();
				$('#fullScreenOverlay').removeClass('d-none');
				
				$.ajax({
					url: '{{ route("write-book") }}',
					type: 'POST',
					data: {
						user_blurb: $('#user_blurb').val(),
						language: $('#language').val(),
						book_structure: $('#bookStructure').val(),
						author_name: $('#authorName').val(),
						publisher_name: $('#publisherName').val(),
						book_title: $('#book_title').val(),
						book_blurb: $('#book_blurb').val(),
						back_cover_text: $('#back_cover_text').val(),
						character_profiles: $('#character_profiles').val(),
						example_question: exampleQuestion,
						example_answer: exampleAnswer,
						book_keywords: bookKeywords,
						llm: savedLlm,
						adult_content: $('#adultContent').val(),
						genre: $('#genre').val(),
						writing_style: $('#writingStyle').val(),
						narrative_style: $('#narrativeStyle').val(),
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function (data) {
						$('#fullScreenOverlay').addClass('d-none');
						if (data.success) {
							bookEditUrl = '{{ route("edit-book", "") }}/' + data.bookSlug;
							$("#alertModalContent").html("{{ __('default.Book created successfully.') }} <a href='" + bookEditUrl + "'>{{ __('default.Click here to edit the book.') }}</a>");
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						} else {
							$("#alertModalContent").html("Error: " + data.message);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
					},
					error: function (xhr, status, error) {
						$('#fullScreenOverlay').addClass('d-none');
						
						$("#alertModalContent").html("Error: " + error);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				});
			});
			
		});
	
	</script>
@endpush



