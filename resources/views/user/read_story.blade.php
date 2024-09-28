@extends('layouts.app')

@section('title', $current_story->title)

@section('content')
	<!-- **************** MAIN CONTENT START **************** -->
	<main>
		
		<!-- Container START -->
		<div class="container">
			<div class="row g-4">
				<!-- Main content START -->
				<div class="col-lg-8 mx-auto">
					<div class="vstack gap-4">
						<!-- Blog single START -->
						<div class="card card-body">
							@if(Storage::exists(str_replace('.png','_768.jpg',"public/story_images/".$current_story->story_image)))
								<img class="rounded"
								     src="{{str_replace('.png','_768.jpg',Storage::url("public/story_images/".$current_story->story_image))}}"
								     alt="">
							@elseif(Storage::exists("public/story_images/".$current_story->story_image))
								<img class="rounded"
								     src="{{Storage::url("public/story_images/".$current_story->story_image)}}"
								     alt="">
							@else
								<img class="rounded" src="{{asset('/assets/images/no-image-found-story_768.jpg')}}" alt="">
							@endif
							<div class="mt-4">
								<!-- Tag -->
								<?php
								if ($current_story->nsfw === 1) {
									$reasons = explode(',', $current_story->nsfw_reason);
									//distinct reasons
									$reasons = array_unique($reasons);
									?>
								<span class="badge d-inline me-2"
								      style="color:white; background-color: #ff0000;">{{implode(', ', $reasons)}}</span>

									<?php
								}
								?>
								
								
								<a href="{{ url('stories/genre/' . Str::slug($current_story->genre)) }}"
								   class="badge bg-danger bg-opacity-10 text-danger mb-2 fw-bold">{{ $current_story->genre }}</a>
								
								<div class="d-flex align-items-center justify-content-between my-3">
									<div class="d-flex align-items-center">
										<!-- Avatar -->
										<div class="avatar avatar-story me-2">
											<a href="{{ url('writer-profile/' . $current_story->user->username) }}"> <img
													class="avatar-img rounded-circle"
													src="{{ !empty($current_story->user->avatar) ? Storage::url($current_story->user->avatar) : '/assets/images/avatar/01.jpg' }}"
													alt=""> </a>
										</div>
										<!-- Info -->
										<div>
											<div class="nav nav-divider">
												<h6 class="nav-item card-title mb-0"><a
														href="{{ url('writer-profile/' . $current_story->user->username) }}"> {{ $current_story->user->name }} </a>
												</h6>
												
												<span class="nav-item small">{{ '@'.$current_story->user->username ?? '@anonymous' }}</span>
												
												<span class="nav-item small"> <i class="bi bi-clock pe-1"></i>{{round($current_story->total_word_count/230)+1}} min read</span>
												<span class="nav-item small"><span class="nav-link" style="cursor:pointer;" id="upvote_link"> <i
															class="bi bi-hand-thumbs-up-fill pe-1"></i>(<span
															id="upvote_count">{{$current_story->total_votes}}</span>)</span></span>
											</div>
											<p class="mb-0 small">{{ $current_story->non_auto_last_update }}</p>
										</div>
									</div>
								</div>
								<h1 class="h4">{{$current_story->title}}</h1>

								<?php
									$first_message = '';
								?>
								<div class="card-body" id="read">
									@foreach($chat_histories as $message)
										@if (stripos($message->message, 'This is a summary of the story elements') !== false)
												<?php
												$first_message = $message->message; ?>
										@else
											
											@if($message->sender === 'User')
													<?php
													if (stripos($message->message, 'Help me out here.') !== false) {
														//do nothing
													} else {
														echo '											<p class="mt-4 bg-light rounded-start-top-0 p-3 rounded">';
														echo nl2br(e($message->message));
														echo '											</p>';
													}
													?>
											@else
												@if (stripos($message->message, 'Now, write how the story begins') !== false)
												@elseif ($first_message==='')
														<?php
														$first_message = $message->message; ?>
												@else
														<?php
														$json_string = \App\Helpers\MyHelper::extract_json_from_message($message->message);
														if (\App\Helpers\MyHelper::validateJson($json_string) === "Valid JSON") {
//														echo App\Helpers\MyHelper::createOptionButtonsFromInput($json_string);
														} else {
															echo '											<p class="mt-4">';
															echo nl2br(e($message->message));
															echo '											</p>';
														}
														?>
												@endif
											@endif
										@endif
									@endforeach
									
									
									@if ($is_my_profile)
										<div class="clearfix">
											<div class="">
												<div class="form-check form-switch">
													<input class="form-check-input private_checkbox" type="checkbox"
													       data-story_guid="{{$current_story->chat_header_guid}}"
													       id="privateSwitch_{{$current_story->chat_header_guid}}" {{$current_story->is_private  ? 'checked' : ''}}>
													<label class="form-check-label" for="privateSwitch{story_id}">Make Private</label>
												</div>
											</div>
											
											<div class="">
												<div class="form-check form-switch">
													<input class="form-check-input contribution_checkbox" type="checkbox"
													       data-story_guid="{{$current_story->chat_header_guid}}"
													       id="contributionSwitch_{{$current_story->chat_header_guid}}" {{$current_story->allow_contribution  ? 'checked' : ''}}>
													<label class="form-check-label" for="contributionSwitch{story_id}">Allow others to continue
														writing</label>
												</div>
											</div>
										</div>
									@endif
									
									@if (Auth::check())
										
										@if ($is_my_profile || Auth::user()->id === 1)
											<a href="{{ url('fusion/' . $current_story->chat_header_guid) }}"
											   class="btn btn-primary-soft btn-sm mt-4">CONTINUE WRITING</a>
											<a href="{{ url('edit_story/' . $current_story->chat_header_guid) }}"
											   class="btn btn-primary-soft btn-sm mt-4">EDIT SETTINGS</a>
											<a href="javascript:;"
											   onclick="if(confirm('Are you sure you want to delete this story?')) { location.href='{{ url('delete_story/' . $current_story->chat_header_guid) }}'; }"
											   class="btn btn-danger-soft btn-sm mt-4">DELETE STORY</a>
										@elseif ($current_story->allow_contribution && !$is_my_profile)
											<a href="{{ url('fusion/' . $current_story->chat_header_guid) }}"
											   class="btn btn-primary btn-sm mt-4">CONTRIBUTE TO THIS STORY</a>
										@endif
										
										@if (Auth::user()->id === 1)
											<!-- Checks if the logged-in user is an admin -->
											<div class="clearfix">
												<div class="">
													<!-- Flag as sexual Button -->
													<button class="btn btn-warning-soft btn-sm mt-4" id="flagSexual">Flag as Sexual</button>
													<!-- Flag as sexual Button -->
													<button class="btn btn-warning-soft btn-sm mt-4" id="flagAdultTheme">Flag as Adult Theme
													</button>
													<!-- Admin delete Button -->
													<button class="btn btn-danger-soft btn-sm mt-4" id="adminDelete">Admin Delete</button>
													<!-- Allow contribution Button -->
													<button class="btn btn-danger-soft btn-sm mt-4" id="adminAllowContributions">Admin Allow
														Contributions
													</button>
												</div>
											</div>
										@endif
									@endif
								</div>
								
								<!-- Row START -->
								
								<!-- Row END -->
								<!-- Blockquote START -->
								
								
								<figure class="bg-light rounded p-3 p-sm-4 my-4">
									<blockquote class="blockquote" style="font-size: 14px;">
										<p>{!! nl2br($first_message) !!}</p>
									</blockquote>
									<figcaption class="blockquote-footer mb-0">
										by <a href="{{ url('writer-profile/' . $current_story->user->username) }}"
										      class="text-reset btn-link">{{ $current_story->user->name }}</a>
									</figcaption>
								</figure>
								<!-- Blockquote END -->
							</div>
						</div>
						<!-- Card END -->
						<!-- Blog single END -->
					</div>
				</div>
				<!-- Main content END -->
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
	
	</script>

@endpush
