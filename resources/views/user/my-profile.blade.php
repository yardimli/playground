@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
	
	<!-- **************** MAIN CONTENT START **************** -->
	<main>
		
		<!-- Container START -->
		<div class="container">
			<div class="row g-4">
				
				<!-- Main content START -->
				<div class="col-lg-8 vstack gap-4">
					<!-- My profile START -->
					<div class="card">
						<!-- Cover image -->
						<div class="h-200px rounded-top"
						     style="background-image:url({{ !empty($user->background_image) ? Storage::url($user->background_image) : '/assets/images/bg/01.jpg' }}); background-position: center; background-size: cover; background-repeat: no-repeat;"></div>
						<!-- Card body START -->
						<div class="card-body py-0">
							<div class="d-sm-flex align-items-start text-center text-sm-start">
								<div>
									<!-- Avatar -->
									<div class="avatar avatar-xxl mt-n5 mb-3">
										<img class="avatar-img rounded-circle border border-white border-3"
										     src="{{ !empty($user->avatar) ? Storage::url($user->avatar) : '/assets/images/avatar/01.jpg' }}"
										     alt="">
									</div>
								</div>
								<div class="ms-sm-4 mt-sm-3">
									<!-- Info -->
									<h1 class="mb-0 h5">{{ $user->username }} <i
											class="bi bi-patch-check-fill text-success small"></i></h1>
									<p>{{$story_count}} stories</p>
								</div>
								<!-- Button -->
								@if ($is_my_profile)
									<div class="d-flex mt-3 justify-content-center ms-sm-auto">
										<a href="{{route('my.settings')}}" class="btn btn-danger-soft me-2" type="button"> <i
												class="bi bi-pencil-fill pe-1"></i> Edit profile </a>
									</div>
								@endif
							</div>
							<!-- List profile -->
							<ul class="list-inline mb-0 text-center text-sm-start mt-3 mt-sm-0">
								<li class="list-inline-item"><i class="bi bi-calendar2-plus me-1"></i> Joined
									on {{ $user->created_at }}</li>
							</ul>
						</div>
						<!-- Card body END -->
						<div class="card-footer mt-3 pt-2 pb-0">
							<!-- Nav profile pages -->
							<ul
								class="nav nav-bottom-line align-items-center justify-content-center justify-content-md-start mb-0 border-0">
								<li class="nav-item"><a class="nav-link active" href="{{route('my.books')}}"> Fusions </a></li>
							</ul>
						</div>
					</div>
					<!-- My profile END -->
					
					<!-- Card feed item START -->
					
					@foreach($past_stories as $story)
						
						<div class="card">
							<!-- Card header START -->
							<div class="card-header border-0 pb-0">
								<div class="d-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center">
										<!-- Avatar -->
										<div class="avatar avatar-story me-2">
											<a href="{{ url('writer-profile/' . ($story->user->username ?? 'gone')) }}"> <img
													class="avatar-img rounded-circle"
													src="{{ !empty($story->user->avatar) ? Storage::url($story->user->avatar) : '/assets/images/avatar/01.jpg' }}"
													alt="">
											</a>
										</div>
										<!-- Info -->
										<div>
											<div class="nav nav-divider">
												<h6 class="nav-item card-title mb-0"><a
														href="{{ url('writer-profile/' . ($story->user->username ?? 'gone')) }}">{{ $story->user->name ?? 'anonymous' }}</a>
												</h6>
												<span class="nav-item small">{{ $story->non_auto_last_update->diffForHumans() }}</span>
											</div>
											<div class="nav nav-divider mt-1">
												<?php
												if ($story->nsfw===1) {
													$reasons = explode(',', $story->nsfw_reason);
													//distinct reasons
													$reasons = array_unique($reasons);
													?>
													<span class="badge d-inline me-2" style="color:white; background-color: #ff0000;">{{implode(', ', $reasons)}}</span>
												
												<?php
													}
												?>
												<a href="{{ url('stories/genre/' . Str::slug($story->genre)) }}"
												   class="badge d-inline" style="color:white; background-color: #999;">{{ $story->genre }}</a>
												<span class="ms-2 me-2">•</span>
												<span class="mb-0 small"><i class="bi bi-clock pe-1"></i> {{round($story->total_word_count/230)+1}} min
												read</span>
												<span class="ms-2 me-2">•</span>
												<span class="nav-item small"><i
														class="bi bi-hand-thumbs-up-fill pe-1"></i>({{$story->total_votes}})</span>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- Card header END -->
							<!-- Card body START -->
							<div class="card-body">
									<?php
									$story_image = asset('/assets/images/no-image-found-story_768.jpg');
									if ( Illuminate\Support\Facades\Storage::exists(str_replace('.png', '_768.jpg', "public/story_images/" . $story->story_image))) {
										$story_image = str_replace('.png', '_768.jpg', "storage/story_images/" . $story->story_image);
									}
									?>
								
								<h5><a class="text-body"
								       href="{{ url('read-story/' . $story->chat_header_guid) }}">{{ $story->title }}</a></h5>
								<a class="text-body mt-2" href="{{ url('read-story/' . $story->chat_header_guid) }}"><img
										class="card-img-top"
										src="/{{$story_image}}"
										alt="Post"></a>
							
							</div>
							<!-- Card body END -->
							<!-- Card footer START -->
							<div class="card-footer border-0 pt-0">
								@if (Auth::check())
									@if ($is_my_profile)
										<div class="clearfix">
											<div class="float-start">
												<div class="form-check form-switch">
													<input class="form-check-input private_checkbox" type="checkbox"
													       data-story_guid="{{$story->chat_header_guid}}"
													       id="privateSwitch_{{$story->chat_header_guid}}" {{$story->is_private  ? 'checked' : ''}}>
													<label class="form-check-label" for="privateSwitch{story_id}">Make Private</label>
												</div>
											</div>
											
											<div class="float-end">
												<div class="form-check form-switch">
													<input class="form-check-input contribution_checkbox" type="checkbox"
													       data-story_guid="{{$story->chat_header_guid}}"
													       id="contributionSwitch_{{$story->chat_header_guid}}" {{$story->allow_contribution  ? 'checked' : ''}}>
													<label class="form-check-label" for="contributionSwitch{story_id}">Allow others to continue
														writing</label>
												</div>
											</div>
										</div>
										<a href="{{ url('fusion/' . $story->chat_header_guid) }}"
										   class="btn btn-primary-soft btn-sm mt-4">CONTINUE WRITING</a>
										<a href="{{ url('edit_story/' . $story->chat_header_guid) }}"
										   class="btn btn-primary-soft btn-sm mt-4">EDIT SETTINGS</a>
										<a href="javascript:;"
										   onclick="if(confirm('Are you sure you want to delete this story?')) { location.href='{{ url('delete_story/' . $story->chat_header_guid) }}'; }"
										   class="btn btn-danger-soft btn-sm mt-4">DELETE STORY</a>
									@endif
									@if ($story->allow_contribution && !$is_my_profile)
										<a href="{{ url('fusion/' . $story->chat_header_guid) }}"
										   class="btn btn-primary-soft btn-sm mt-4">CONTRIBUTE TO THIS STORY</a>
									@endif
								@endif
							
							</div>
							<!-- Card footer END -->
						</div>
					@endforeach
					<!-- Card feed item END -->
				</div>
				<!-- Main content END -->
				
				<!-- Right sidebar START -->
				<div class="col-lg-4">
					
					
					<div class="row g-4">
						
						<!-- Card START -->
						<div class="col-md-6 col-lg-12">
							<div class="card">
								<div class="card-header border-0 pb-0">
									<h5 class="card-title">About</h5>
									<!-- Button modal -->
								</div>
								<!-- Card body START -->
								<div class="card-body position-relative pt-0">
									<p>{{ $user->about_me }}</p>
									<!-- Date time -->
									<ul class="list-unstyled mt-3 mb-0">
									</ul>
								</div>
								<!-- Card body END -->
							</div>
						</div>
						<!-- Card END -->
					</div>
				
				</div>
				<!-- Right sidebar END -->
			
			</div> <!-- Row END -->
		</div>
		<!-- Container END -->
	
	</main>
	<!-- **************** MAIN CONTENT END **************** -->
	
	@include('layouts.footer')

@endsection

@push('scripts')
	<!-- Inline JavaScript code -->
	<script>
      var current_page = 'my.books';
      $(document).ready(function () {
      });
	</script>
	
@endpush
