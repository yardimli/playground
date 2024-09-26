@include('playground.header')

<div class="page-content bg-grey-custom">
	<section class="content-inner border-bottom">
		<div class="container">
			<div class="d-flex justify-content-between align-items-center">
				<h4 class="title">Books</h4>
			</div>
			<div class="filter-area m-b30 book-list-small-container-color">
				<div class="grid-area">
					<div class="shop-tab">
					</div>
				</div>
				<div class="category">
					<div class="filter-category">
						<a data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false"
						   aria-controls="collapseExample">
							<i class="fas fa-list me-2"></i>
							Categories
						</a>
					</div>
				</div>
			</div>
			<div class="acod-content collapse book-list-small-container-color" id="collapseExample">
				<div class="widget widget_services">
					@foreach($genres_array as $genre)
						@php $genre_slug = Str::slug($genre); @endphp
						<div class="form-check search-content">
							<input class="form-check-input" type="radio" value="{{$genre}}" id="genre_{{$genre_slug}}" name="category">
							<label class="form-check-label" for="genre_{{$genre_slug}}">
								{{$genre}}
							</label>
						</div>
					@endforeach
					
				</div>
			</div>
			
			<div class="row">
				@foreach ($paginatedBooks as $book)
					<div class="col-md-12 col-sm-12">
						<div class="dz-shop-card book-list-small-container-color style-2">
							<div class="dz-media">
								<a
									href="{{route('playground.book-details',$book['id'] ?? '0')}}"><img src="{{$book['cover_filename'] ?? ''}}" alt="book"></a>
							</div>
							<div class="dz-content" style="width: 100%">
								<div class="dz-header">
									<div>
										<i class="fas fa-book fa-fw m-r10"></i><a href="{{route('playground.books-list-genre',[$book['genre'] ?? ''])}}" class="modal-body-color">{{$book['genre'] ?? ''}}</a>
										<h4 class="title mb-0"><a
												href="{{route('playground.book-details',$book['id'] ?? '0')}}">{{$book['title'] ?? ''}}</a></h4>
									</div>
									
									<div class="d-flex">
										
										<div class="bookmark-btn style-1">
											<input class="form-check-input" type="checkbox" id="flexCheckDefault1">
											<label class="form-check-label" for="flexCheckDefault1">
												<i class="flaticon-heart"></i>
											</label>
										</div>
									</div>
									
								</div>
								
								<div class="dz-body" style="width: 100%">
									<div class="dz-rating-box" style="width: 100%">
										<div style="width: 100%">
											<p class="dz-para">{{$book['blurb'] ?? ''}}</p>
											<div>
												@if (isset($book['keywords']))
													@foreach ($book['keywords'] as $keyword)
														<a href="{{route('playground.books-list-keyword',[$keyword])}}" class="badge">{{$keyword}}</a>
													@endforeach
												@endif
											</div>
										</div>
									</div>
									<div class="rate">
										<ul class="book-info">
											<li><span>Writen by</span>{{$book['author_name'] ?? ''}}</li>
											<li><span>Publisher</span>{{$book['publisher_name'] ?? ''}}</li>
											<li><span>Year</span>{{date("Y", $book['file_time'] ?? 123456789)}}</li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
				@endforeach
			</div>
			<div class="row page mt-0">
				<div class="col-md-6">
					<p class="page-text">Showing {{ $paginatedBooks->firstItem() }} to {{ $paginatedBooks->lastItem() }} from {{ $paginatedBooks->total() }} data</p>
				</div>
				<div class="col-md-6">
					<nav aria-label="Blog Pagination">
						<ul class="pagination style-1 p-t20">
							@if ($paginatedBooks->onFirstPage())
								<li class="page-item disabled"><span class="page-link modal-header-color prev">Prev</span></li>
							@else
								<li class="page-item"><a class="page-link modal-header-color prev" href="{{ $paginatedBooks->previousPageUrl() }}">Prev</a></li>
							@endif
							
							@php
								$currentPage = $paginatedBooks->currentPage();
								$lastPage = $paginatedBooks->lastPage();
								$desktopRange = 2; // Show 5 pages on desktop (current + 2 on each side)
								$mobileRange = 1;  // Show 3 pages on mobile (current + 1 on each side)
							@endphp
							
							@foreach (range(1, $lastPage) as $page)
								@php
									$isDesktopVisible = abs($page - $currentPage) <= $desktopRange;
									$isMobileVisible = abs($page - $currentPage) <= $mobileRange;
								@endphp
								
								@if ($isDesktopVisible || $isMobileVisible)
									<li class="page-item {{ $isMobileVisible ? '' : 'desktop-only' }}">
										<a class="page-link modal-header-color {{ $page == $currentPage ? 'active' : '' }}" href="{{ $paginatedBooks->url($page) }}">{{ $page }}</a>
									</li>
								@endif
							@endforeach
							
							@if ($paginatedBooks->hasMorePages())
								<li class="page-item"><a class="page-link modal-header-color next" href="{{ $paginatedBooks->nextPageUrl() }}">Next</a></li>
							@else
								<li class="page-item disabled"><span class="page-link modal-header-color next">Next</span></li>
							@endif
						</ul>
					</nav>
				</div>
			</div>
		</div>
	</section>
	
	
	<!-- Newsletter -->
	<section class="py-5 newsletter-wrapper"
	         style="background-image: url('images/background/bg1.jpg'); background-size: cover;">
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
	@include('playground.footer')
</footer>
<!-- Footer End -->

<button class="scroltop" type="button"><i class="fas fa-arrow-up"></i></button>
</div>

<!-- JAVASCRIPT FILES ========================================= -->

<script src="/js/swiper-bundle.min.js"></script><!-- SWIPER JS -->
<script src="/js/owl.carousel.js"></script><!-- OWL CAROUSEL JS -->
<script src="/js/counter.js"></script><!-- COUNTER JS -->
<script src="/js/waypoints-min.js"></script><!-- WAYPOINTS JS -->
<script src="/js/counterup.min.js"></script><!-- COUNTERUP JS -->
<script src="/js/nouislider.min.js"></script><!-- NOUSLIDER MIN JS-->
<script src="/js/dz.carousel.js"></script><!-- DZ CAROUSEL JS -->
<script src="/js/dz.ajax.js"></script><!-- AJAX -->

</body>
</html>
