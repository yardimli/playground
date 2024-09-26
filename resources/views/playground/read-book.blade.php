@include('playground.header')
<script>
	{!! $json_translations !!}
		let
	bookData = @json($book);
	let bookSlug = "{{$book_slug}}";
	let colorOptions = @json($colorOptions);
</script>

<div class="page-content bg-grey-custom">
	<section class="content-inner-1">
		<div class="container">
			<div class="row book-grid-row style-4 m-b60 pt-3 pb-5 book-list-small-container-color">
				<div class="col-xl-4 col-lg-5 col-12">
					<div class="dz-box">
						<div class="dz-media">
							<img src="{{$book['cover_filename']}}" alt="book">
						</div>
					</div>
				</div>
				<div class="col-xl-8 col-lg-7 col-12">
					<div class="dz-box">
						<div class="dz-content">
							<div class="dz-header">
								<div class="d-inline-block">
									<i class="fas fa-book fa-fw m-r10"></i><a
										href="{{route('playground.books-list-genre',[$book['genre'] ?? ''])}}"
										class="modal-body-color">{{$book['genre'] ?? ''}}</a>
									<h4 class="title mb-0"><a
											href="{{route('playground.read-book',$book_slug)}}">{{$book['title'] ?? ''}}</a></h4>
								</div>
								
								<div class="d-inline-block float-end">
									
									<div class="bookmark-btn style-1">
										<input class="form-check-input" type="checkbox" id="flexCheckDefault1">
										<label class="form-check-label" for="flexCheckDefault1">
											<i class="flaticon-heart"></i>
										</label>
									</div>
								</div>
							
							</div>
							<div class="dz-body">
								<div class="book-detail" style="margin-bottom: 10px;">
									<ul class="book-info">
										<li>
											<div class="writer-info">
												<img src="/images/profile2.jpg" alt="book">
												<div>
													<span>Writen by</span>{{$book['author_name'] ?? ''}}
												</div>
											</div>
										</li>
										<li><span>Publisher</span>{{$book['publisher_name'] ?? ''}}</li>
										<li><span>Year</span>{{date("Y", $book['file_time'] ?? 123456789)}}</li>
									</ul>
								</div>
								<div class="mb-3">
									@if (isset($book['keywords']))
										@foreach ($book['keywords'] as $keyword)
											<a href="{{route('playground.books-list-keyword',[$keyword])}}" class="badge">{{$keyword}}</a>
										@endforeach
									@endif
								</div>
								
								<p class="text-1">{!! str_replace("\n","<br>", $book['back_cover_text'] ?? '')!!}</p>
								
								<div class="book-footer">
									<div style="width: 100%">
										<p class="dz-para">
											<span class="strong">Blurb:</span><br>
											{{$book['blurb'] ?? ''}}</p>
										
										<p class="dz-para">
											<span class="strong">Character Profiles:</span><br>
											{!! str_replace("\n","<br>", $book['character_profiles'] ?? ''  ) !!}</p>
									
									
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			
			</div>
			
			
			<div class="row book-grid-row style-4 m-b60 pt-3 pb-5 book-list-small-container-color" style="max-width: 640px; margin:0 auto;">
				@php
					$chapter_counter = 0;
				@endphp
				@foreach ($book['acts'] as $act)
					@php
						#chapter_counter++;
					@endphp
					<h3>{{$act['title'] ?? 'Act'}}</h3>
					@foreach ($act['chapters'] as $chapter)
						<h4>{{$chapter['name'] ?? 'Chapter '.$chapter_counter}}</h4>
						<p>{{$chapter['short_description'] ?? ''}}</p>
						<ul>
							<li><i>{{__('Events')}}</i>: {{$chapter['events'] ?? ''}}</li>
							<li><i>{{__('People')}}</i>: {{$chapter['people'] ?? ''}}</li>
							<li><i>{{__('Places')}}</i>: {{$chapter['places'] ?? ''}}</li>
						</ul>
						@if (isset($chapter['beats']))
							@foreach ($chapter['beats'] as $beat)
								<p>{!! str_replace("\n","<br>",$beat['beat_text'] ?? '') !!}</p>
							@endforeach
						@endif
					@endforeach
				@endforeach
			</div>
	
	</section>
	
	<!-- Newsletter -->
	<section class="py-5 newsletter-wrapper"
	         style="background-image: url('/images/background/bg1.jpg'); background-size: cover;">
		<div class="container">
			<div class="subscride-inner">
				<div
					class="row style-1 justify-content-xl-between justify-content-lg-center align-items-center text-xl-start text-center">
					<div class="col-xl-7 col-lg-12">
						<div class="section-head mb-0">
							<h2 class="title text-white my-lg-3 mt-0">Subscribe our newsletter for newest books updates</h2>
						</div>
					</div>
					<div class="col-xl-5 col-lg-6">
						<form class="dzSubscribe style-1" action="" method="post">
							<div class="dzSubscribeMsg"></div>
							<div class="form-group">
								<div class="input-group mb-0">
									<input name="dzEmail" required="required" type="email" class="form-control bg-transparent text-white"
									       placeholder="Your Email Address">
									<div class="input-group-addon">
										<button name="submit" value="Submit" type="submit" class="btn btn-primary btnhover">
											<span>SUBSCRIBE</span>
											<i class="fa-solid fa-paper-plane"></i>
										</button>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Newsletter End -->
</div>

<!-- Footer -->
<footer class="site-footer style-1">
	@include('playground.footer-categories')
	@include('playground.footer')
</footer>
<!-- Footer End -->

<button class="scroltop" type="button"><i class="fas fa-arrow-up"></i></button>
</div>

<!-- JAVASCRIPT FILES ========================================= -->

<script src="/js/swiper-bundle.min.js"></script><!-- SWIPER JS -->
<script src="/js/dz.carousel.js"></script><!-- DZ CAROUSEL JS -->
<script src="/js/waypoints-min.js"></script><!-- WAYPOINTS JS -->
<script src="/js/counterup.min.js"></script><!-- COUNTERUP JS -->
<script src="/js/dz.ajax.js"></script><!-- AJAX -->

</body>
</html>
