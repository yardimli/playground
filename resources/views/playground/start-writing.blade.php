@include('playground.header')

<!-- Content -->
<div class="page-content site-theme-div">
	<!-- contact area -->
	<div class="content-block">
		<!-- Browse Jobs -->
		<section class="content-inner site-theme-div">
			<div class="container">
				<div class="row">
					<div class="col-xl-3 col-lg-4 m-b30">
						<div class="sticky-top">
							<div class="shop-account">
								<div class="account-detail text-center" style="padding-top:10px;">
									<img alt="" src="{{$coverFilename}}" style="width: 90%;">
								
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-9 col-lg-8 m-b30">
						<div class="shop-bx shop-profile">
							<div class="shop-bx-title clearfix">
								<h5 class="text-uppercase">{{__('default.Add Book')}}</h5>
							</div>
							
							
							<div class="mb-3">
								<label for="user_blurb" class="form-label">{{__('default.Book Description')}}:</label>
								<textarea class="form-control" id="user_blurb" name="user_blurb" required
								          placeholder="{{__('default.describe your books story, people and events. While you can just say \'A Boy Meets World\' the longer and more detailed your blurb is the more creative and unique the writing will be.')}}"
								          rows="8"></textarea>
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
							
							<div class="mb-3">
								<label for="language" class="form-label">{{__('default.Book Structure')}}:</label>
								
								<select class="form-control" id="bookStructure" name="bookStructure" required>
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
							
							<div class="mb-3">
								<label for="llmSelect" class="form-label">{{__('default.AI Engines:')}}</label>
								<select id="llmSelect" class="form-select mx-auto mb-3">
									<?php
									if (Auth::user() && Auth::user()->isAdmin()) {
										?>
									<option value="anthropic/claude-3.5-sonnet:beta">{{__('default.Select an AI Engine')}}</option>
									<option value="anthropic/claude-3.5-sonnet:beta">anthropic :: claude-3.5-sonnet</option>
									<option value="openai/gpt-4o">openai :: gpt-4o</option>
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
							<button id="addBookStepOneBtn" class="btn btn-primary btnhover">{{__('default.Submit')}}</button>
							<button id="addBookStepTwoBtn" class="btn btn-primary btnhover d-none">{{__('default.Submit')}}</button>
						
						
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Browse Jobs END -->
	</div>
</div>
<!-- Content END-->

<script>
	let savedTheme = localStorage.getItem('theme') || 'light';
	let savedLlm = localStorage.getItem('llm') || 'anthropic/claude-3-haiku:beta';
	
	$(document).ready(function () {
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
				url: '{{ route("book.write-book-character-profiles") }}',
				type: 'POST',
				data: {
					user_blurb: $('#user_blurb').val(),
					language: $('#language').val(),
					bookStructure: $('#bookStructure').val(),
					llm: savedLlm,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (data) {
					console.log(data);
					$('#fullScreenOverlay').addClass('d-none');
					if (data.success) {
						//alert('{{ __('default.Book created successfully. Please check the new fields before continuing.') }}');
						$('#addBookStepOneBtn').addClass('d-none');
						$('#hint_1').addClass('d-none');
						$('#hint_2').removeClass('d-none');
						
						$('#book_details').removeClass('d-none');
						$('#addBookStepTwoBtn').removeClass('d-none');
						$('#book_title').val(data.data.title);
						$('#book_blurb').val(data.data.blurb);
						$('#back_cover_text').val(data.data.back_cover_text);
						
						let characterProfiles = '';
						data.data.character_profiles.forEach(function (profile) {
							characterProfiles += (profile.name || '') + '\n' + (profile.description || '') + '\n\n';
						});
						
						$('#character_profiles').val(characterProfiles);
					} else {
						alert('Error: ' + data.message);
					}
				},
				error: function (xhr, status, error) {
					$('#fullScreenOverlay').addClass('d-none');
					alert('Error: ' + error);
				}
			});
		});
		
		$("#addBookStepTwoBtn").on('click', function (event) {
			event.preventDefault();
			$('#fullScreenOverlay').removeClass('d-none');
			
			$.ajax({
				url: '{{ route("book.write-book") }}',
				type: 'POST',
				data: {
					user_blurb: $('#user_blurb').val(),
					language: $('#language').val(),
					bookStructure: $('#bookStructure').val(),
					book_title: $('#book_title').val(),
					book_blurb: $('#book_blurb').val(),
					back_cover_text: $('#back_cover_text').val(),
					character_profiles: $('#character_profiles').val(),
					llm: savedLlm,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (data) {
					$('#fullScreenOverlay').addClass('d-none');
					if (data.success) {
						alert('{{ __('default.Book created successfully.') }}');
						//set url to book details
						window.location.href = '{{ route("playground.book-details", "") }}/' + data.bookSlug;
					} else {
						alert('Error: ' + data.message);
					}
				},
				error: function (xhr, status, error) {
					$('#fullScreenOverlay').addClass('d-none');
					alert('Error: ' + error);
				}
			});
		});
		
	});

</script>

<!-- Footer -->
<footer class="site-footer style-1">
	@include('playground.footer')
</footer>
<!-- Footer End -->

<button class="scroltop" type="button"><i class="fas fa-arrow-up"></i></button>
</div>


<div id="fullScreenOverlay" class="d-none">
	<div class="overlay-content">
		<div class="spinner-border text-light" role="status">
			<span class="visually-hidden">{{__('Loading...')}}</span>
		</div>
		<p class="mt-3 text-light">{{__('default.Processing your request. This may take a few minutes...')}}</p>
	</div>
</div>

</body>
</html>
