@extends('layouts.app')

@section('title', 'All Stories')

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
						<h1 class="text-white" style="background-color: rgba(0,0,0,0.5)">Write Books with  <span>AI</span></h1>
						<span class="mb-4 text-white" style="background-color: rgba(0,0,0,0.5)">Your Story, Our AI - Write Books Faster, Smarter, Better with AI</span>
					</div>
				</div>
			</div>
		</div>
		<!-- Page header END -->
		
		<!-- Container START -->
		<div class="py-5">
			<div class="container">
				
				<div class="tab-content mb-0 pb-0">
					<!-- For you tab START -->
					<div class="tab-pane fade show active" id="tab-1">
						
						
						<div class="row g-4">
							<!-- Main content START -->
							
							<div class="col-12">
								<div class="card p-1">
									<h2 class="p-1" style="margin:0px;">{{ $genre_title }}</h2>
								</div>
							</div>
						
						
						@foreach($past_stories as $story)
								<div class="col-sm-6 col-lg-4">
									<!-- Card feed item START -->
									<div class="card h-100">
										<!-- Card header END -->
										<!-- Tag -->
											<?php
											$reasons=[];
										if ($story->nsfw===1) {
											$reasons = explode(',', $story->nsfw_reason);
											//distinct reasons
											$reasons = array_unique($reasons);
										}?>
										
										
										
										@if(Storage::exists(str_replace('.png','_380.jpg',"public/story_images/".$story->story_image)))
											<a class="text-body" href="{{ url('read-story/' . $story->chat_header_guid) }}"><img
													class="card-img-top"
													src="{{str_replace('.png','_380.jpg', Storage::url("public/story_images/".$story->story_image))}}"
													alt="Post"></a>
										@else
											<a class="text-body" href="{{ url('read-story/' . $story->chat_header_guid) }}"><img class="card-img-top" src="{{asset('/assets/images/no-image-found-story_380.jpg')}}" alt="Post"></a>
										@endif
										
										<div class="badge bg-danger text-white mt-2 me-2 position-absolute top-0 end-0">
											<!-- badge -->
											 {{implode(', ', $reasons)}}
										</div>
										@if ($story->story_language !== 'English')
											<div class="badge bg-info text-white mt-2 me-2 position-absolute top-0 start-0" style="z-index: 9">
												<span class="badge text-bg-info">{{ $story->story_language }}</span>
											</div>
										@endif
										
{{--									@if(Storage::exists(str_replace('.png','_380.jpg',"public/story_images/".$story->story_image)))--}}
{{--										<a class="text-body" href="{{ url('read-story/' . $story->chat_header_guid) }}"><img--}}
{{--												class="card-img-top"--}}
{{--												src="{{str_replace('.png','_380.jpg', Storage::url("public/story_images/".$story->story_image))}}"--}}
{{--												alt="Post"></a>--}}
{{--										@else--}}
{{--											<a class="text-body" href="{{ url('read-story/' . $story->chat_header_guid) }}"><img class="card-img-top" src="{{asset('/assets/images/no-image-found-story_380.jpg')}}" alt="Post"></a>--}}
{{--										@endif--}}
										
										<!-- Card body START -->
										<div class="card-body">
											<!-- Info -->
											<a class="text-body"
											   href="{{ url('read-story/' . $story->chat_header_guid) }}">{{ $story->title }}</a>
											<!-- Feed react START -->
											<div class="d-flex justify-content-between">
												<h6 class="mb-0"><a href="{{ url('writer-profile/' . ($story->user->username ?? 'gone')) }}">{{ '@'.$story->user->username ?? '@anonymous' }}</a></h6>
												<span class="small">{{ $story->non_auto_last_update->diffForHumans() }}</span>
											</div>
											<ul class="nav nav-stack flex-wrap small mt-3">
												<li class="nav-item">
													<span class="nav-link"> <i class="bi bi-hand-thumbs-up-fill pe-1"></i>({{$story->total_votes}})</span>
												</li>
												<li class="nav-item">
													<i class="bi bi-clock pe-1"></i> {{round($story->total_word_count/230)+1}} min read</s>
												</li>
												
												<!-- Card share action END -->
											</ul>
											<!-- Feed react END -->
										</div>
										<!-- Card body END -->
									
									</div>
									<!-- Card feed item END -->
								</div>
							@endforeach
							
							<!-- Main content END -->
						</div> <!-- Row END -->
						
						<div class="d-flex justify-content-start mt-5">
							@if ($past_stories->onFirstPage())
								<button class="btn btn-secondary mx-1" disabled>First</button>
							@else
								<a href="{{ $past_stories->url(1) }}" class="btn btn-primary mx-1">First</a>
							@endif
							
							@if ($past_stories->onFirstPage())
								<button class="btn btn-secondary mx-1" disabled>Previous</button>
							@else
								<a href="{{ $past_stories->previousPageUrl() }}" class="btn btn-primary mx-1">Previous</a>
							@endif
							
							@foreach(range(1, $past_stories->lastPage()) as $i)
								@if ($i >= $past_stories->currentPage() - 2 && $i <= $past_stories->currentPage() + 2)
									@if ($i == $past_stories->currentPage())
										<button class="btn btn-secondary mx-1">{{ $i }}</button>
									@else
										<a href="{{ $past_stories->url($i) }}" class="btn btn-primary mx-1">{{ $i }}</a>
									@endif
								@endif
							@endforeach
							
							@if ($past_stories->hasMorePages())
								<a href="{{ $past_stories->nextPageUrl() }}" class="btn btn-primary mx-1">Next</a>
							@else
								<button class="btn btn-secondary mx-1" disabled>Next</button>
							@endif
							
							@if ($past_stories->currentPage() === $past_stories->lastPage())
								<button class="btn btn-secondary mx-1" disabled>Last</button>
							@else
								<a href="{{ $past_stories->url($past_stories->lastPage()) }}" class="btn btn-primary mx-1">Last</a>
							@endif
						</div>
						
						<div class="mt-2">Viewing {{ $past_stories->firstItem() }} - {{ $past_stories->lastItem() }} out of {{ $past_stories->total() }}</div>
					
					</div>
					<!-- For you tab END -->
				
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
