@extends('layouts.app')

@section('title', 'All Books')

@section('content')
	
	<script>
		{!! $json_translations !!}
			let
		bookData = @json($book);
		let bookSlug = "{{$book_slug}}";
	</script>
	<main class="pt-5">
		
		<!-- Container START -->
		<div class="container pt-4">
			<div class="row g-4">
				<div class="col-xl-4 col-lg-4 col-12 vstack gap-4">
					<div class="card">
						<div class="card-body py-3">
							<img src="{{$book['cover_filename']}}" alt="book" class="pb-1">
						</div>
					</div>
				</div>
				
				<!-- Main content START -->
				<div class="col-xl-8 col-lg-8 col-12 vstack gap-4">
					<!-- My profile START -->
					<div class="card">
						<div class="card-body py-0">
							
							<h1 class="title mb-0"><a href="{{route('user.read-book',$book_slug)}}">{{$book['title'] ?? ''}}</a>
							</h1>
							
							<div class="d-flex align-items-center justify-content-between my-3">
								<div class="d-flex align-items-center">
									<!-- Avatar -->
									<div class="avatar avatar-story me-2">
										<a href=""> <img
												class="avatar-img rounded-circle"
												src="{{$book['author_avatar'] ?? ''}}"
												alt=""> </a>
									</div>
									<!-- Info -->
									<div>
										<div class="nav nav-divider">
											<h6 class="nav-item card-title mb-0"><a
													href=""> {{$book['author_name'] ?? ''}} </a>
											</h6>
											
											<span class="nav-item small">{{$book['publisher_name'] ?? ''}}</span>
											<span class="nav-item small"> <i class="bi bi-clock pe-1"></i>55 min read</span>
											<span class="nav-item small">{{date("Y-m-d", $book['file_time'] ?? 1923456789)}}</span>
											<a
												href="{{route('user.showcase-library-genre',[$book['genre'] ?? ''])}}"
												class="nav-item small">{{$book['genre'] ?? ''}}</a>
										
										</div>
									</div>
								</div>
							</div>
							
							
							
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
							
							<p class="mt-4">{!! str_replace("\n","<br>", $book['back_cover_text'] ?? '')!!}</p>
						</div>
					
					</div>
					
					<figure class="bg-light rounded p-3 p-sm-4 my-4">
						<blockquote class="blockquote" style="font-size: 14px;">
							<span class="strong">Blurb:</span><br>
							{{$book['blurb'] ?? ''}}
						</blockquote>
						<figcaption class="blockquote-footer mb-0">
							<span class="strong">Character Profiles:</span><br>
							{!! str_replace("\n","<br>", $book['character_profiles'] ?? ''  ) !!}
						</figcaption>
					</figure>
				</div>
			</div>
		</div>
	</main>
	
	
	@include('layouts.footer')

@endsection

@push('scripts')
	<!-- Inline JavaScript code -->
	<script>
		var current_page = 'read_book';
		$(document).ready(function () {
		});
	</script>

@endpush
