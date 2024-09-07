@include('playground.header')

<div class="page-content">
	<!-- inner page banner -->
	<div class="dz-bnr-inr overlay-secondary-dark dz-bnr-inr-sm" style="background-image:url(images/background/bg3.jpg);">
		<div class="container">
			<div class="dz-bnr-inr-entry">
				<h1>Contact</h1>
				<nav aria-label="breadcrumb" class="breadcrumb-row">
					<ul class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{route('index')}}"> Home</a></li>
						<li class="breadcrumb-item">Contact</li>
					</ul>
				</nav>
			</div>
		</div>
	</div>
	<div class="content-inner-2 pt-0">
		<div class="map-iframe">
			<iframe
				src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d28921.2545341401!2d121.55171653025097!3d25.028751716234193!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3442abadec7543c5%3A0x408dbd7aa34838cd!2sXinyi%20District%2C%20Taipei%20City%2C%20110!5e0!3m2!1sen!2stw!4v1725603292833!5m2!1sen!2stw"
				style="border:0; width:100%; min-height:100%; margin-bottom: -8px;" allowfullscreen></iframe>
		</div>
	</div>
	
	<section class="contact-wraper1" style="background-image: url(images/background/bg2.jpg);">
		<div class="container">
			<div class="row">
				<div class="col-lg-5">
					<div class="contact-info">
						<div class="section-head text-white style-1">
							<h3 class="title text-white">Get In Touch</h3>
							<p>If you are interested in working with us, please get in touch.</p>
						</div>
						<ul class="no-margin">
							<li class="icon-bx-wraper text-white left m-b30">
								<div class="icon-md">
											<span class="icon-cell text-primary">
												<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
												     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
												     class="feather feather-map-pin"><path
														d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10"
												                                                                      r="3"></circle></svg>
											</span>
								</div>
								<div class="icon-content">
									<h5 class=" dz-tilte text-white">Our Address</h5>
									<p>Xinyi, Taipei City</p>
								</div>
							</li>
							<li class="icon-bx-wraper text-white left m-b30">
								<div class="icon-md">
											<span class="icon-cell text-primary">
												<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
												     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
												     class="feather feather-mail"><path
														d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline
														points="22,6 12,13 2,6"></polyline></svg>
											</span>
								</div>
								<div class="icon-content">
									<h5 class="dz-tilte text-white">Our Email</h5>
									<p>info@gmail<br>services@gmail.com</p>
								</div>
							</li>
						</ul>
					</div>
				</div>
				<div class="col-lg-7 m-b40">
					<div class="contact-area1 m-r20 m-md-r0">
						<div class="section-head style-1">
							<h6 class="sub-title text-primary">CONTACT US</h6>
							<h3 class="title m-b20">Get In Touch With Us</h3>
						</div>
						<form class="dz-form dzForm" method="POST" action="">
							<input type="hidden" class="form-control" name="dzToDo" value="Contact">
							<div class="dzFormMsg"></div>
							<div class="input-group">
								<input required type="text" class="form-control" name="dzName" placeholder="Full Name">
							</div>
							<div class="input-group">
								<input required type="text" class="form-control" name="dzEmail" placeholder="Email Adress">
							</div>
							<div class="input-group">
								<input required type="text" class="form-control" name="dzPhoneNumber" placeholder="Phone No.">
							</div>
							<div class="input-group">
								<textarea required name="dzMessage" rows="5" class="form-control">Message</textarea>
							</div>
							<div class="mb-3">
								<div class="g-recaptcha" data-sitekey="6LfqjjcqAAAAAL5QsaES2suRsJYYljDTqQta4mU0"
								     data-callback="verifyRecaptchaCallback" data-expired-callback="expiredRecaptchaCallback"></div>
								<input class="form-control d-none" style="display:none;" data-recaptcha="true" required
								       data-error="Please complete the Captcha">
							</div>
							<div>
								<button name="submit" type="submit" value="submit" class="btn w-100 btn-primary btnhover">SUBMIT
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
	
	<!-- Newsletter -->
	<section class="py-5 newsletter-wrapper"
	         style="background-image: url('images/background/bg1.jpg'); background-size: cover;">
		<div class="container">
			<div class="subscride-inner">
				<div
					class="row style-1 justify-content-xl-between justify-content-lg-center align-items-center text-xl-start text-center">
					<div class="col-xl-7 col-lg-12">
						<div class="section-head mb-0">
							<h2 class="title text-white my-lg-3 mt-0">Subscribe our newsletter for newest books updates</h2>
						</div>
					</div>
					<div class="col-xl-5 col-lg-6">
						<form class="dzSubscribe style-1" action="" method="post">
							<div class="dzSubscribeMsg"></div>
							<div class="form-group">
								<div class="input-group mb-0">
									<input name="dzEmail" required="required" type="email" class="form-control bg-transparent text-white"
									       placeholder="Your Email Address">
									<div class="input-group-addon">
										<button name="submit" value="Submit" type="submit" class="btn btn-primary btnhover">
											<span>SUBSCRIBE</span>
											<i class="fa-solid fa-paper-plane"></i>
										</button>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Newsletter End -->

</div>

<!-- Footer -->
<footer class="site-footer style-1">
	@include('playground.footer')
</footer>
<!-- Footer End -->

<button class="scroltop" type="button"><i class="fas fa-arrow-up"></i></button>
</div>

<!-- JAVASCRIPT FILES ========================================= -->

<script src='https://www.google.com/recaptcha/api.js'></script> <!-- Google API For Recaptcha  -->
<script src="/js/waypoints-min.js"></script><!-- WAYPOINTS JS -->
<script src="/js/counterup.min.js"></script><!-- COUNTERUP JS -->
<script src="/js/dz.ajax.js"></script><!-- AJAX -->

</body>
</html>
