@include('playground.header')


<!-- **************** MAIN CONTENT START **************** -->
	<main>
			
			<!-- Container START -->
		<div class="container" style="min-height: calc(50vh);">
			<div class="row">
				<!-- Main content START -->
				<div class="col-lg-8 mx-auto">
					<!-- Card START -->
					<div class="card">
						<div class="card-header py-3 border-0 d-flex align-items-center justify-content-between">
							<h1 class="h5 mb-0">Thank You!</h1>
						</div>
						<div class="card-body p-3">
							Thank you for verifying your email address. You man now start creating your stories.
							<br>
							<br>
							You can start writing your story by clicking the "Compose" link above.
							<br>
							<br>
							To view your stories, click the "My Stories" link above.
							<br>
							<br>
							<div style="text-align: center; ">
								<img src="{{ asset('/images/logo.png') }}"
								     style="max-width: 300px;" alt="Thank You" class="img-fluid">
							</div>
						</div>
					</div>
					<!-- Card END -->
				</div>
			</div> <!-- Row END -->
		</div>
		<!-- Container END -->
	
	</main>


@include('playground.footer')
<button class="scroltop" type="button"><i class="fas fa-arrow-up"></i></button>
</div>


<!-- JAVASCRIPT FILES ========================================= -->
<script src="/js/jquery.min.js"></script><!-- JQUERY MIN JS -->
<script src="/js/bootstrap.bundle.min.js"></script><!-- BOOTSTRAP MIN JS -->
<script src="/js/bootstrap-select.min.js"></script><!-- BOOTSTRAP SELECT MIN JS -->
<script src="/js/custom.js"></script><!-- CUSTOM JS -->

</body>
</html>
