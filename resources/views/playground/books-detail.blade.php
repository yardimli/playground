@include('playground.header')
<script>
	{!! $json_translations !!}
		let
	bookData = @json($book);
	let bookSlug = "{{$book_slug}}";
	let colorOptions = @json($colorOptions);
</script>

<div class="page-content bg-grey">
	<section class="content-inner-1">
		<div class="container">
			<div class="row book-grid-row style-4 m-b60">
				<div class="col">
					<div class="dz-box">
						<div class="dz-media">
							<img src="{{$book['cover_filename']}}" alt="book">
						</div>
						<div class="dz-content">
							<div class="dz-header">
								<h3 class="title">{{$book['title']}}</h3>
								<div class="shop-item-rating">
									<div class="d-lg-flex d-sm-inline-flex d-flex align-items-center">
										<ul class="dz-rating">
											<li><i class="flaticon-star text-yellow"></i></li>
											<li><i class="flaticon-star text-yellow"></i></li>
											<li><i class="flaticon-star text-yellow"></i></li>
											<li><i class="flaticon-star text-yellow"></i></li>
											<li><i class="flaticon-star text-muted"></i></li>
										</ul>
										<h6 class="m-b0">4.0</h6>
									</div>
								</div>
							</div>
							<div class="dz-body">
								<div class="book-detail">
									<ul class="book-info">
										<li>
											<div class="writer-info">
												<img src="/images/profile2.jpg" alt="book">
												<div>
													<span>Writen by</span>{{$book['owner']}}
												</div>
											</div>
										</li>
										<li><span>Publisher</span>Playground Computer</li>
										<li><span>Year</span>2019</li>
									</ul>
								</div>
								<p class="text-1">{!! str_replace("\n","<br>", $book['back_cover_text'] ?? '')!!}</p>
								
								<div class="book-footer">
									
									<a href="{{route('playground.book-details',$book_slug)}}"
									   class="btn btn-primary mt-3 d-inline-block">{{__('default.Read More')}}</a>
									@if (Auth::user())
										@if (Auth::user()->email === $book['owner'])
											<button class="btn btn-danger delete-book-btn mt-3 d-inline-block"
											        data-book-id="<?php echo urlencode($book_slug); ?>">{{__('default.Delete Book')}}
											</button>
										@endif
									@endif
									
									<div class="product-num">
										<div class="bookmark-btn style-1 d-none d-sm-block">
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
			</div>
			
			<div class="row">
				<div class="col-xl-8">
					<div class="product-description tabs-site-button">
						<ul class="nav nav-tabs">
							<li><a data-bs-toggle="tab" href="#graphic-design-1" class="active">Details Product</a></li>
							<li><a data-bs-toggle="tab" href="#developement-1">Customer Reviews</a></li>
						</ul>
						<div class="tab-content">
							<div id="graphic-design-1" class="tab-pane show active">
								<table class="table border book-overview">
									<tr>
										<th>Book Title</th>
										<td>{{$book['title']}}</td>
									</tr>
									<tr>
										<th>Author</th>
										<td>{{$book['owner']}}</td>
									</tr>
									<tr>
										<th>Ediiton Language</th>
										<td>English</td>
									</tr>
									<tr>
										<th>Book Format</th>
										<td>Paperback, 450 Pages</td>
									</tr>
									<tr>
										<th>Date Published</th>
										<td>August 10th 2019</td>
									</tr>
									<tr>
										<th>Publisher</th>
										<td>Playground Computer</td>
									</tr>
									<tr>
										<th>Pages</th>
										<td>520</td>
									</tr>
									<tr class="tags">
										<th>Tags</th>
										<td>
											<a href="javascript:void(0);" class="badge">Drama</a>
											<a href="javascript:void(0);" class="badge">Advanture</a>
											<a href="javascript:void(0);" class="badge">Survival</a>
											<a href="javascript:void(0);" class="badge">Biography</a>
											<a href="javascript:void(0);" class="badge">Trending2022</a>
											<a href="javascript:void(0);" class="badge">Bestseller</a>
										</td>
									</tr>
								</table>
							</div>
							<div id="developement-1" class="tab-pane">
								
								<div class="mt-3 mb-3"><span id="bookPrompt"><em>{{__('default.Prompt For Book:')}}</em><br>
								{{$book['prompt'] ?? 'no prompt'}}</span></div>
								<div class="mt-3 mb-3"><span id="bookCharacters"><em>{{__('default.Character Profiles:')}}</em><br>
								{!! str_replace("\n","<br>", $book['character_profiles'] ?? 'no characters')!!}</span></div>
								
								<div class="clear" id="comment-list">
									<div class="post-comments comments-area style-1 clearfix">
										<h4 class="comments-title">4 COMMENTS</h4>
										<div id="comment">
											<ol class="comment-list">
												<li class="comment even thread-even depth-1 comment" id="comment-2">
													<div class="comment-body">
														<div class="comment-author vcard">
															<img src="/images/profile4.jpg" alt="" class="avatar"/>
															<cite class="fn">Michel Poe</cite> <span class="says">says:</span>
															<div class="comment-meta">
																<a href="javascript:void(0);">December 28, 2022 at 6:14 am</a>
															</div>
														</div>
														<div class="comment-content dlab-page-text">
															<p>Donec suscipit porta lorem eget condimentum. Morbi vitae mauris in leo venenatis
																varius. Aliquam nunc enim, egestas ac dui in, aliquam vulputate erat.</p>
														</div>
														<div class="reply">
															<a rel="nofollow" class="comment-reply-link" href="javascript:void(0);"><i
																	class="fa fa-reply"></i> Reply</a>
														</div>
													</div>
													<ol class="children">
														<li
															class="comment byuser comment-author-w3itexpertsuser bypostauthor odd alt depth-2 comment"
															id="comment-3">
															<div class="comment-body" id="div-comment-3">
																<div class="comment-author vcard">
																	<img src="/images/profile3.jpg" alt="" class="avatar"/>
																	<cite class="fn">Celesto Anderson</cite> <span class="says">says:</span>
																	<div class="comment-meta">
																		<a href="javascript:void(0);">December 28, 2022 at 6:14 am</a>
																	</div>
																</div>
																<div class="comment-content dlab-page-text">
																	<p>Donec suscipit porta lorem eget condimentum. Morbi vitae mauris in leo venenatis
																		varius. Aliquam nunc enim, egestas ac dui in, aliquam vulputate erat.</p>
																</div>
																<div class="reply">
																	<a class="comment-reply-link" href="javascript:void(0);"><i class="fa fa-reply"></i>
																		Reply</a>
																</div>
															</div>
														</li>
													</ol>
												</li>
												<li class="comment even thread-odd thread-alt depth-1 comment" id="comment-4">
													<div class="comment-body" id="div-comment-4">
														<div class="comment-author vcard">
															<img src="/images/profile2.jpg" alt="" class="avatar"/>
															<cite class="fn">Ryan</cite> <span class="says">says:</span>
															<div class="comment-meta">
																<a href="javascript:void(0);">December 28, 2022 at 6:14 am</a>
															</div>
														</div>
														<div class="comment-content dlab-page-text">
															<p>Donec suscipit porta lorem eget condimentum. Morbi vitae mauris in leo venenatis
																varius. Aliquam nunc enim, egestas ac dui in, aliquam vulputate erat.</p>
														</div>
														<div class="reply">
															<a class="comment-reply-link" href="javascript:void(0);"><i class="fa fa-reply"></i> Reply</a>
														</div>
													</div>
												</li>
												<li class="comment odd alt thread-even depth-1 comment" id="comment-5">
													<div class="comment-body" id="div-comment-5">
														<div class="comment-author vcard">
															<img src="/images/profile1.jpg" alt="" class="avatar"/>
															<cite class="fn">Stuart</cite> <span class="says">says:</span>
															<div class="comment-meta">
																<a href="javascript:void(0);">December 28, 2022 at 6:14 am</a>
															</div>
														</div>
														<div class="comment-content dlab-page-text">
															<p>Donec suscipit porta lorem eget condimentum. Morbi vitae mauris in leo venenatis
																varius. Aliquam nunc enim, egestas ac dui in, aliquam vulputate erat.</p>
														</div>
														<div class="reply">
															<a rel="nofollow" class="comment-reply-link" href="javascript:void(0);"><i
																	class="fa fa-reply"></i> Reply</a>
														</div>
													</div>
												</li>
											</ol>
										</div>
										<div class="default-form comment-respond style-1" id="respond">
											<h4 class="comment-reply-title" id="reply-title">LEAVE A REPLY <small> <a rel="nofollow"
											                                                                          id="cancel-comment-reply-link"
											                                                                          href="javascript:void(0)"
											                                                                          style="display:none;">Cancel
														reply</a> </small></h4>
											<div class="clearfix">
												<form method="post" id="comments_form" class="comment-form" novalidate>
													<p class="comment-form-author"><input id="name" placeholder="Author" name="author" type="text"
													                                      value=""></p>
													<p class="comment-form-email"><input id="email" required="required" placeholder="Email"
													                                     name="email" type="email" value=""></p>
													<p class="comment-form-comment"><textarea id="comments" placeholder="Type Comment Here"
													                                          class="form-control4" name="comment" cols="45"
													                                          rows="3" required="required"></textarea></p>
													<p class="col-md-12 col-sm-12 col-xs-12 form-submit">
														<button id="submit" type="submit" class="submit btn btn-primary filled">
															Submit Now <i class="fa fa-angle-right m-l10"></i>
														</button>
													</p>
												</form>
											</div>
										</div>
									</div>
								</div>
							
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 mt-5 mt-xl-0">
					<div class="widget">
						<h4 class="widget-title">Related Books</h4>
						<div class="row">
							<div class="col-xl-12 col-lg-6">
								<div class="dz-shop-card style-5">
									<div class="dz-media">
										<img src="/images/books/grid/book15.jpg" alt="">
									</div>
									<div class="dz-content">
										<h5 class="subtitle">Terrible Madness</h5>
										<ul class="dz-tags">
											<li>THRILLE,</li>
											<li>DRAMA,</li>
											<li>HORROR</li>
										</ul>
										<div class="price">
											<span class="price-num">$45.4</span>
											<del>$98.4</del>
										</div>
									
									</div>
								</div>
							</div>
							<div class="col-xl-12 col-lg-6">
								<div class="dz-shop-card style-5">
									<div class="dz-media">
										<img src="/images/books/grid/book3.jpg" alt="">
									</div>
									<div class="dz-content">
										<h5 class="subtitle">Battle Drive</h5>
										<ul class="dz-tags">
											<li>THRILLE,</li>
											<li>DRAMA,</li>
											<li>HORROR</li>
										</ul>
										<div class="price">
											<span class="price-num">$45.4</span>
											<del>$98.4</del>
										</div>
									
									</div>
								</div>
							</div>
							<div class="col-xl-12 col-lg-6">
								<div class="dz-shop-card style-5">
									<div class="dz-media">
										<img src="/images/books/grid/book5.jpg" alt="">
									</div>
									<div class="dz-content">
										<h5 class="subtitle">Terrible Madness</h5>
										<ul class="dz-tags">
											<li>THRILLE,</li>
											<li>DRAMA,</li>
											<li>HORROR</li>
										</ul>
										<div class="price">
											<span class="price-num">$45.4</span>
											<del>$98.4</del>
										</div>
									
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
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

@include('playground.footer')
<button class="scroltop" type="button"><i class="fas fa-arrow-up"></i></button>
</div>

<!-- JAVASCRIPT FILES ========================================= -->
<script src="/js/jquery.min.js"></script><!-- JQUERY MIN JS -->
<script src="/js/bootstrap.bundle.min.js"></script><!-- BOOTSTRAP MIN JS -->
<script src="/js/bootstrap-select.min.js"></script><!-- BOOTSTRAP SELECT MIN JS -->
<script src="/js/swiper-bundle.min.js"></script><!-- SWIPER JS -->
<script src="/js/dz.carousel.js"></script><!-- DZ CAROUSEL JS -->
<script src="/js/waypoints-min.js"></script><!-- WAYPOINTS JS -->
<script src="/js/counterup.min.js"></script><!-- COUNTERUP JS -->
<script src="/js/dz.ajax.js"></script><!-- AJAX -->
<script src="/js/custom.js"></script><!-- CUSTOM JS -->

</body>
</html>
