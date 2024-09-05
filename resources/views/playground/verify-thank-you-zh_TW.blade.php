@extends('layouts.app',['title'=>"welcome"])

@section('title', 'Welcome')

@section('content')
	
	<!-- Container START -->
	<div class="container" style="min-height: calc(88vh);">
		<div class="row g-4">
			<!-- Main content START -->
			<div class="col-lg-8 mx-auto">
				<!-- Card START -->
				<div class="card">
					<div class="card-header py-3 border-0 d-flex align-items-center justify-content-between">
						<h1 class="h5 mb-0">感謝您的加入！</h1>
					</div>
					<div class="card-body p-3">
						電子郵件已完成驗證。現在可以開始使用織音製作音樂了。
						<br>
						<br>
						未來可以利用會員中心頁面的選項或是加入官方LINE來查看並追蹤制定歌曲進度。
						<br>
						<br>
						LINE ID: @zhiyin
						<div style="text-align: center; ">
							<img src="{{ asset('assets/logos/zhiyin_logo.png') }}"
							     style="max-width: 300px; width: 300px;" alt="Thank You" class="img-fluid">
						</div>
					</div>
				</div>
				<!-- Card END -->
			</div>
		</div> <!-- Row END -->
	</div>
	<!-- Container END -->

@endsection

@push('scripts')
	<!-- Inline JavaScript code -->
	<script>
		var current_page = 'my.verify-thank-you-zh_TW';
		$(document).ready(function () {
		});
	</script>
@endpush
