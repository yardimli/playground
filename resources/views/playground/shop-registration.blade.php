@include('playground.header')

		<div class="page-content">
			<!-- inner page banner -->
			<div class="dz-bnr-inr overlay-secondary-dark dz-bnr-inr-sm" style="background-image:url(images/background/bg3.jpg);">
				<div class="container">
					<div class="dz-bnr-inr-entry">
						<h1>Registration</h1>
						<nav aria-label="breadcrumb" class="breadcrumb-row">
							<ul class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{route('index')}}"> Home</a></li>
								<li class="breadcrumb-item">Registration</li>
							</ul>
						</nav>
					</div>
				</div>
			</div>
			<!-- inner page banner End-->

			<!-- contact area -->
			<section class="content-inner shop-account">
				<!-- Product -->
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-lg-6 col-md-6 mb-4">
							<div class="login-area">
								<form>
									<h4 class="text-secondary">Registration</h4>
									<p class="font-weight-600">If you don't have an account with us, please Registration.</p>
									<div class="mb-4">
										<label class="label-title">Username *</label>
										<input name="dzName" required="" class="form-control" placeholder="Your Username" type="text">
									</div>
									<div class="mb-4">
										<label class="label-title">Email address *</label>
										<input name="dzName" required="" class="form-control" placeholder="Your Email address" type="email">
									</div>
									<div class="mb-4">
										<label class="label-title">Password *</label>
										<input name="dzName" required="" class="form-control " placeholder="Type Password" type="password">
									</div>
									<div class="mb-5">
										<small>Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our <a href="{{route('privacy-policy')}}">privacy policy</a>.</small>
									</div>
									<div class="text-left">
										<button class="btn btn-primary btnhover w-100 me-2">Register</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<!-- Product END -->
			</section>
			<!-- contact area End-->
		</div>

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
