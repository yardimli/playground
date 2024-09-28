@extends('layouts.app')

@section('title', 'All Books')

@section('content')
	
	<script>
		{!! $json_translations !!}
			let
		bookData = @json($book);
		let bookSlug = "{{$book_slug}}";
		let colorOptions = @json($colorOptions);
	</script>
	<main class="pt-5">
		
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
							<br>
							<a href="{{route('user.read-book',$book_slug)}}" class="btn btn-primary">{{__('Read Book')}}</a>
							@if ( (Auth::user() && (($book['owner'] ?? '') === Auth::user()->email)) || (Auth::user() && Auth::user()->isAdmin()) )
								<a href="{{route('user.edit-book',$book_slug)}}" class="btn btn-danger">{{__('Edit Book')}}</a>
							@endif
						</div>
						<div class="col-xl-8 col-lg-7 col-12">
							<div class="dz-box">
								<div class="dz-content">
									<div class="dz-header">
										<div class="d-inline-block">
											<i class="fas fa-book fa-fw m-r10"></i><a
												href="{{route('user.books-list-genre',[$book['genre'] ?? ''])}}"
												class="modal-body-color">{{$book['genre'] ?? ''}}</a>
											<h4 class="title mb-0"><a
													href="{{route('user.read-book',$book_slug)}}">{{$book['title'] ?? ''}}</a></h4>
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
													<a href="{{route('user.books-list-keyword',[$keyword])}}" class="badge">{{$keyword}}</a>
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
				
				</div>
			</section>
		
		
		</div>
	</main>
	
	@include('layouts.footer')

@endsection

@push('scripts')
	<!-- Inline JavaScript code -->
	<script>
		var current_page = 'read_stories';
		$(document).ready(function () {
		});
	</script>

@endpush
