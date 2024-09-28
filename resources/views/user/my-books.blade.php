@extends('layouts.app')

@section('title', 'All Books')

@section('content')
	
	<!-- **************** MAIN CONTENT START **************** -->
	<main class="pt-5">
		
		<!-- Page header START -->
		<div class="py-5"
		     style="background-image:url(/assets/images/header/pexels-repuding-12064.jpg); background-position: center center; background-size: cover; background-repeat: no-repeat;">
			<div class="container">
				
				<div class="row justify-content-center py-5">
					<div class="col-md-6 text-center">
						<!-- Title -->
						<h1 class="text-white" style="background-color: rgba(0,0,0,0.5)">Welcome to <span>WBWAI</span></h1>
						<span class="mb-4 text-white" style="background-color: rgba(0,0,0,0.5)">Your Story, Our AI - Write Books Faster, Smarter, Better with AI</span>
					</div>
				</div>
			</div>
		</div>
		<!-- Page header END -->
		
		<!-- Container START -->
		<div class="py-1">
			<div class="container">
				
				<div class="tab-content mb-0 pb-0">
					<!-- For you tab START -->
					<div class="tab-pane fade show active" id="tab-1">
						
						<div class="row g-4 mt-2">
							<div class="col-12">
								<div class="card p-1">
									<h2 class="p-1" style="margin:0px;">Sample Books and Short Stories</h2>
									<div class="ps-1">
										Read a sample of our books and short stories. Click on the book cover to read the full book or
										story.
									</div>
								</div>
							</div>
							
							@foreach ($books as $book)
								<div class="col-sm-6 col-lg-4">
									<!-- Card feed item START -->
									<div class="card h-100">
										<a class="text-body" href="{{route('user.book-details',$book['id'] ?? '0')}}"><img
												class="card-img-top"
												src="{{$book['cover_filename'] ?? ''}}"
												alt="Book"></a>
										@if ($book['language'] !== 'English')
											<div class="badge bg-info text-white mt-2 ms-2 me-2 position-absolute top-0 start-0"
											     style="z-index: 9">
												<span class="badge text-bg-info">{{ $book['language'] }}</span>
											</div>
										@endif
										
										<!-- Card body START -->
										<div class="card-body">
											<!-- Info -->
											<a class="text-body"
											   href="{{route('user.book-details',$book['id'] ?? '0')}}">{{$book['title'] ?? ''}}</a>
											<!-- Feed react START -->
											<div class="d-flex justify-content-between">
												<h6 class="mb-0"><a
														href="{{route('user.books-list-genre',[$book['genre'] ?? ''])}}"
														class="modal-body-color">{{$book['genre'] ?? ''}}</a>
												</h6>
												<span class="small">{{ Illuminate\Support\Carbon::parse($book['file_time'])->diffForHumans() }}</span>
											</div>
											<p class="dz-para">{{$book['blurb'] ?? ''}}</p>
											
											<div>
												@if (isset($book['keywords']))
													@foreach ($book['keywords'] as $keyword)
														<a href="{{route('user.books-list-keyword',[$keyword])}}"
														   class="badge">{{$keyword}}</a>
													@endforeach
												@endif
											</div>
											
											<ul class="book-info">
												<li><span>Written by</span> {{$book['author_name'] ?? ''}}</li>
												<li><span>Publisher</span> {{$book['publisher_name'] ?? ''}}</li>
												<li><span>Year</span> {{date("Y", $book['file_time'] ?? 123456789)}}</li>
											</ul>
										</div>
										<!-- Card body END -->
									</div>
								</div>
							@endforeach
						</div>
						
						
					</div>
				</div>
			</div>
		</div>
		<!-- Container END -->
	
	</main>
	<!-- **************** MAIN CONTENT END **************** -->
	
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
