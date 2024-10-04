@extends('layouts.app')

@section('title', 'Change Log')

@section('content')
	
	<!-- **************** MAIN CONTENT START **************** -->
	<main>
			<!-- Container START -->
		<div class="container" style="min-height: calc(88vh);">
			<div class="row g-4">
				<!-- Main content START -->
				<div class="col-lg-8 mx-auto">
					<!-- Card START -->
					<div class="card">
						<div class="card-header py-3 border-0 d-flex align-items-center justify-content-between">
							<h1 class="h5 mb-0">Change Log</h1>
						</div>
						<div class="card-body p-3 mb-3">
							<div style="text-align: center; ">
								<img src="{{ asset('/images/logo.png') }}"
								     style="max-width: 300px; width: 300px;" alt="Thank You" class="img-fluid">
							</div>
							
							<br>
							<strong>v0.1.0 - 09/28/2024</strong>
							<br>
							- Change the Write Books with AI's UI, so except the showcase all books are private. <br>There is no need for users to publish their stories on the site.
							<br>
							- Add Blog code.
							<br><br>
							<strong>v0.1.1 - 09/29/2024</strong>
							<br>
							- Allow users to insert beats in between existing beats or at the beginning or end of the chapter.
							
							<br><br>
								<strong>v0.1.2 - 09/30/2024</strong>
							<br>
							- Improve the UI for viewing the library and reading the books.
							
						</div>
					</div>
					<!-- Card END -->
				</div>
			</div> <!-- Row END -->
		</div>
		<!-- Container END -->
	
	</main>
	
	
	
	@include('layouts.footer')

@endsection

@push('scripts')
	<!-- Inline JavaScript code -->
	<script>
      var current_page = 'my.change-log';
      $(document).ready(function () {
      });
	</script>
	
@endpush
