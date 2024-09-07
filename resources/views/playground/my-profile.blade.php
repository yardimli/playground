@include('playground.header')

<!-- Content -->
<div class="page-content site-theme-div">
	<!-- contact area -->
	<div class="content-block">
		<!-- Browse Jobs -->
		<section class="content-inner site-theme-div">
			<div class="container">
				<div class="row">
					<div class="col-xl-3 col-lg-4 m-b30">
						<div class="sticky-top">
							<div class="shop-account">
								<div class="account-detail text-center">
									<div class="my-image">
										<a href="javascript:void(0);">
											<img alt="" src="images/profile3.jpg">
										</a>
									</div>
									<div class="account-title">
										<div class="">
											<h4 class="m-b5"><a href="javascript:void(0);">David Matin</a></h4>
											<p class="m-b0"><a href="javascript:void(0);">Web developer</a></p>
										</div>
									</div>
								</div>
								<ul>
									<li>
										<a href="{{route('my-profile')}}" class="active"><i class="far fa-user" aria-hidden="true"></i>
											<span>Profile</span></a>
									</li>
									
									<li>
										<a href="{{route('playground.books-list')}}"><i class="fa fa-briefcase" aria-hidden="true"></i>
											<span>Shop</span></a>
									</li>
									<li>
										<a href="{{route('privacy-policy')}}"><i class="fa fa-key" aria-hidden="true"></i>
											<span>Privacy Policy</span></a>
									</li>
									<li>
										<a href="{{route('login')}}"><i class="fas fa-sign-out-alt" aria-hidden="true"></i>
											<span>Log Out</span></a>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="col-xl-9 col-lg-8 m-b30">
						<div class="shop-bx shop-profile">
							<div class="shop-bx-title clearfix">
								<h5 class="text-uppercase">Basic Information</h5>
							</div>
							<form>
								<div class="row m-b30">
									<div class="col-lg-6 col-md-6">
										<div class="mb-3">
											<label for="formcontrolinput1" class="form-label">Your Name:</label>
											<input type="text" class="form-control" id="formcontrolinput1" placeholder="Alexander Weir">
										</div>
									</div>
									<div class="col-lg-6 col-md-6">
										<div class="mb-3">
											<label for="formcontrolinput2" class="form-label">Professional title:</label>
											<input type="text" class="form-control" id="formcontrolinput2" placeholder="Web Designer">
										</div>
									</div>
									<div class="col-lg-6 col-md-6">
										<div class="mb-3">
											<label for="formcontrolinput3" class="form-label">Languages:</label>
											<input type="text" class="form-control" id="formcontrolinput3" placeholder="Language">
										</div>
									</div>
									<div class="col-lg-6 col-md-6">
										<div class="mb-3">
											<label for="formcontrolinput4" class="form-label">Age:</label>
											<input type="text" class="form-control" id="formcontrolinput4" placeholder="Age">
										</div>
									</div>
									<div class="col-lg-12 col-md-12">
										
										<div class="mb-3">
											<label for="exampleFormControlTextarea" class="form-label">Description:</label>
											<textarea class="form-control" id="exampleFormControlTextarea" rows="5">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s.</textarea>
										</div>
									</div>
								</div>
								<div class="shop-bx-title clearfix">
									<h5 class="text-uppercase">Contact Information</h5>
								</div>
								<div class="row">
									<div class="col-lg-6 col-md-6">
										<div class="mb-3">
											<label for="formcontrolinput5" class="form-label">Contact Number:</label>
											<input type="text" class="form-control" id="formcontrolinput5" placeholder="+1 123 456 7890">
										</div>
									</div>
									<div class="col-lg-6 col-md-6">
										<div class="mb-3">
											<label for="formcontrolinput6" class="form-label">Email Address:</label>
											<input type="text" class="form-control" id="formcontrolinput6" placeholder="info@example.com">
										</div>
									</div>
									<div class="col-lg-6 col-md-6">
										<div class="mb-3">
											<label for="formcontrolinput7" class="form-label">Country:</label>
											<input type="text" class="form-control" id="formcontrolinput7" placeholder="Country Name">
										</div>
									</div>
									<div class="col-lg-6 col-md-6">
										<div class="mb-3">
											<label for="formcontrolinput8" class="form-label">Postcode:</label>
											<input type="text" class="form-control" id="formcontrolinput8" placeholder="112233">
										</div>
									</div>
									<div class="col-lg-6 col-md-6">
										<div class="mb-3">
											<label for="formcontrolinput9" class="form-label">City:</label>
											<input type="text" class="form-control" id="formcontrolinput9" placeholder="City Name">
										</div>
									</div>
									<div class="col-lg-6 col-md-6">
										<div class="mb-4">
											<label for="formcontrolinput10" class="form-label">Full Address:</label>
											<input type="text" class="form-control" id="formcontrolinput10" placeholder="New york City">
										</div>
									</div>
								</div>
								<button class="btn btn-primary btnhover">Save Setting</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Browse Jobs END -->
	</div>
</div>
<!-- Content END-->

<!-- Footer -->
<footer class="site-footer style-1">
	@include('playground.footer-categories')
	@include('playground.footer')
</footer>
<!-- Footer End -->

<button class="scroltop" type="button"><i class="fas fa-arrow-up"></i></button>
</div>

</body>
</html>
