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
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Action" id="productRadio3" name="category">
						<label class="form-check-label" for="productRadio3">
							Action
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Biography & Autobiography" id="productRadio4" name="category">
						<label class="form-check-label" for="productRadio4">
							Biography & Autobiography
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Body, Mind & Spirit" id="productRadio5" name="category">
						<label class="form-check-label" for="productRadio5">
							Body, Mind & Spirit
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Business & Economics" id="productRadio6" name="category">
						<label class="form-check-label" for="productRadio6">
							Business & Economics
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Children Fiction" id="productRadio7" name="category">
						<label class="form-check-label" for="productRadio7">
							Children Fiction
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Children Non-Fiction" id="productRadio8" name="category">
						<label class="form-check-label" for="productRadio8">
							Children Non-Fiction
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Cooking" id="productRadio10" name="category">
						<label class="form-check-label" for="productRadio10">
							Cooking
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Crafts & Hobbies" id="productRadio11" name="category">
						<label class="form-check-label" for="productRadio11">
							Crafts & Hobbies
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Design" id="productRadio12" name="category">
						<label class="form-check-label" for="productRadio12">
							Design
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Drama" id="productRadio13" name="category">
						<label class="form-check-label" for="productRadio13">
							Drama
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Education" id="productRadio14" name="category">
						<label class="form-check-label" for="productRadio14">
							Education
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Family & Relationships" id="productRadio15" name="category">
						<label class="form-check-label" for="productRadio15">
							Family & Relationships
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Fiction" id="productRadio16" name="category">
						<label class="form-check-label" for="productRadio16">
							Fiction
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Foreign Language Study" id="productRadio17" name="category">
						<label class="form-check-label" for="productRadio17">
							Foreign Language Study
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Games" id="productRadio18" name="category">
						<label class="form-check-label" for="productRadio18">
							Games
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Gardening" id="productRadio19" name="category">
						<label class="form-check-label" for="productRadio19">
							Gardening
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Health & Fitness" id="productRadio20" name="category">
						<label class="form-check-label" for="productRadio20">
							Health & Fitness
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="History" id="productRadio21" name="category">
						<label class="form-check-label" for="productRadio21">
							History
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Humor" id="productRadio23" name="category">
						<label class="form-check-label" for="productRadio23">
							Humor
						</label>
					</div>
					<div class="form-check search-content">
						<input class="form-check-input" type="radio" value="Mathematics" id="productRadio25" name="category">
						<label class="form-check-label" for="productRadio25">
							Mathematics
						</label>
					</div>
				</div>
			</div>
			
			<div class="row">
				@foreach ($paginatedBooks as $book)
					<div class="col-md-12 col-sm-12">
						<div class="dz-shop-card book-list-small-container-color style-2">
							<div class="dz-media">
								<a
									href="{{route('playground.books-detail',$book['id'])}}"><img src="{{$book['cover_filename']}}" alt="book"></a>
							</div>
							<div class="dz-content" style="width: 100%">
								<div class="dz-header">
									<div>
										<ul class="dz-tags">
											<li><a href="{{route('playground.books-list')}}">ADVANTURE,</a></li>
											<li><a href="{{route('playground.books-list')}}">SCIENCE,</a></li>
											<li><a href="{{route('playground.books-list')}}">COMEDY</a></li>
										</ul>
										<h4 class="title mb-0"><a
												href="{{route('playground.books-detail',$book['id'])}}">{{$book['title']}}</a></h4>
									</div>
								</div>
								
								<div class="dz-body" style="width: 100%">
									<div class="dz-rating-box" style="width: 100%">
										<div style="width: 100%">
											<p class="dz-para">{{$book['blurb']}}</p>
											<div>
												<a href="" class="badge">Get 20% Discount for today</a>
												<a href="" class="badge">50% OFF Discount</a>
												<a href="" class="badge next-badge">See 2+ promos</a>
											</div>
										</div>
										<div class="review-num">
											<h4>4.0</h4>
											<ul class="dz-rating">
												<li><i class="flaticon-star text-yellow"></i></li>
												<li><i class="flaticon-star text-yellow"></i></li>
												<li><i class="flaticon-star text-yellow"></i></li>
												<li><i class="flaticon-star text-yellow"></i></li>
												<li><i class="flaticon-star text-muted"></i></li>
											</ul>
											<span><a href="javascript:void(0);"> 235 Reviews</a></span>
										</div>
									</div>
									<div class="rate">
										<ul class="book-info">
											<li><span>Writen by</span>{{$book['owner']}}</li>
											<li><span>Publisher</span>Printarea Studios</li>
											<li><span>Year</span>2019</li>
										</ul>
										<div class="d-flex">
											
											<div class="bookmark-btn style-1">
												<input class="form-check-input" type="checkbox" id="flexCheckDefault1">
												<label class="form-check-label" for="flexCheckDefault1">
													<i class="flaticon-heart"></i>
												</label>
											</div>
										</div>
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
