<!DOCTYPE html>
<html lang="en">
<head>
	
	<!-- Meta -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="keywords" content=""/>
	<meta name="author" content="playground-computer"/>
	<meta name="robots" content=""/>
	<meta name="description" content="Playground-Computer Ecommerce Website"/>
	<meta property="og:title" content="Playground-Computer Ecommerce Website"/>
	<meta property="og:description" content="Playground-Computer Ecommerce Website"/>
	<meta property="og:image" content="https://makaanlelo.com/tf_products_007/playground/xhtml/social-image.png"/>
	<meta name="format-detection" content="telephone=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<!-- FAVICONS ICON -->
	<link rel="icon" type="image/x-icon" href="/images/favicon.png"/>
	
	<!-- PAGE TITLE HERE -->
	<title>Playground Book Store Ecommerce Website</title>
	
	<!-- MOBILE SPECIFIC -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<!-- STYLESHEETS -->
	<link href="/css/bootstrap.min.css" rel="stylesheet">

	<link rel="stylesheet" type="text/css" href="/css/bootstrap-select.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/icons/fontawesome/css/all.min.css">
	<link rel="stylesheet" type="text/css" href="/css/swiper-bundle.min.css">
	<link rel="stylesheet" type="text/css" href="/css/animate.css">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	
	<link href="/css/bootstrap-icons.min.css" rel="stylesheet">
	
	<link rel="stylesheet" type="text/css" href="/css/custom.css">
	
	<!-- GOOGLE FONTS-->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link
		href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
		rel="stylesheet">
	
	<!-- JAVASCRIPT FILES ========================================= -->
	<script src="/js/jquery.min.js"></script><!-- JQUERY MIN JS -->
	<script src="/js/bootstrap.bundle.min.js"></script><!-- BOOTSTRAP MIN JS -->
	<script src="/js/bootstrap-select.min.js"></script><!-- BOOTSTRAP SELECT MIN JS -->
	<script src="/js/custom.js"></script><!-- CUSTOM JS -->
	
	<script>
		function applyTheme(theme) {
			if (theme === 'dark') {
				$('body').addClass('dark-mode');
				$('#modeIcon').removeClass('bi-sun').addClass('bi-moon');
				$('#modeToggleBtnFloat').attr('aria-label', 'Switch to Light Mode');
			} else {
				$('body').removeClass('dark-mode');
				$('#modeIcon').removeClass('bi-moon').addClass('bi-sun');
				$('#modeToggleBtnFloat').attr('aria-label', 'Switch to Dark Mode');
			}
		}
		
		
		$(document).ready(function () {
			const theme = localStorage.getItem('theme');
			if (theme) {
				applyTheme(theme);
			}
			
			
			$('#modeToggleBtnFloat').on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				const currentTheme = $('body').hasClass('dark-mode') ? 'dark' : 'light';
				const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
				localStorage.setItem('theme', newTheme);
				applyTheme(newTheme);
			})
		});
		
		
		// Manage z-index for multiple modals
		$('.modal').on('show.bs.modal', function () {
			const zIndex = 1040 + (10 * $('.modal:visible').length);
			$(this).css('z-index', zIndex);
			setTimeout(function () {
				$('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
			}, 0);
		});
		
		$('.modal').on('hidden.bs.modal', function () {
			if ($('.modal:visible').length) {
				// Adjust the backdrop z-index when closing a modal
				$('body').addClass('modal-open');
			}
		});
	
	</script>

</head>
<body>

<div class="page-wraper">
	<div id="loading-area" class="preloader-wrapper-1 dropdown-menu-color">
		<div class="preloader-inner">
			<div class="preloader-shade"></div>
			<div class="preloader-wrap"></div>
			<div class="preloader-wrap wrap2"></div>
			<div class="preloader-wrap wrap3"></div>
			<div class="preloader-wrap wrap4"></div>
			<div class="preloader-wrap wrap5"></div>
		</div>
	</div>
	
	<!-- Header -->
	<header class="site-header mo-left header style-1 modal-content-color">
		<!-- Main Header -->
		<div class="header-info-bar">
			<div class="container clearfix">
				<!-- Website Logo -->
				<div class="logo-header logo-dark">
					<a href="{{route('index')}}"><img src="/images/logo.png" alt="logo"></a>
				</div>
				
				<!-- EXTRA NAV -->
				<div class="extra-nav">
					<div class="extra-cell">
						<ul class="navbar-nav header-right">
							
							@if (Auth::user())
								<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
									@csrf
								</form>
								<li class="nav-item dropdown profile-dropdown  ms-4">
									<a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown"
									   aria-expanded="false">
										<img src="/images/profile1.jpg" alt="/">
										<div class="profile-info">
											<h6 class="title">{{ Auth::user()->username }}</h6>
										</div>
									</a>
									<div class="dropdown-menu py-0 dropdown-menu-end dropdown-menu-color">
										<div class="dropdown-header dropdown-menu-color">
											<h6 class="m-0">{{ Auth::user()->username }}</h6>
											<span>{{ Auth::user()->email }}</span>
										</div>
										<div class="dropdown-body dropdown-menu-color">
											<a href="{{route('my-profile')}}"
											   class="dropdown-item d-flex justify-content-between align-items-center ai-icon dropdown-item-color">
												<div>
													<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 24 24" width="20px"
													     fill="#000000">
														<path d="M0 0h24v24H0V0z" fill="none"/>
														<path
															d="M12 6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2m0 10c2.7 0 5.8 1.29 6 2H6c.23-.72 3.31-2 6-2m0-12C9.79 4 8 5.79 8 8s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 10c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
													</svg>
													<span class="ms-2">Profile</span>
												</div>
											</a>
											
											<div id="modeToggleBtnFloat" class="dropdown-item dropdown-item-color" style="padding: 10px 20px; ">
												<i id="modeIcon" class="bi bi-sun" style="margin-left:2px;"></i> <span class="ms-2">{{__('default.Toggle Mode Text')}}</span>
											</div>
											
										</div>
										<div class="dropdown-footer dropdown-menu-color">
											<a class="btn btn-primary w-100 btnhover btn-sm" href="#"  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log Out</a>
										</div>
									</div>
								</li>
							
							@else
								<li class="nav-item">
									<a href="{{route('register')}}" class="btn btn-secondary btnhover me-2">Register</a>
									<a href="{{route('login')}}" class="btn btn-primary btnhover">Login</a>
								</li>
							
							@endif
						</ul>
					</div>
				</div>
			
			
			</div>
		</div>
		<!-- Main Header End -->
		
		<!-- Main Header -->
		<div class="sticky-header modal-header-color main-bar-wraper navbar-expand-lg">
			<div class="main-bar clearfix modal-header-color">
				<div class="container clearfix">
					<!-- Website Logo -->
					<div class="logo-header logo-dark">
						<a href="{{route('index')}}"><img src="/images/logo.png" alt="logo"></a>
					</div>
					
					<!-- Nav Toggle Button -->
					<button class="navbar-toggler collapsed navicon justify-content-end" type="button" data-bs-toggle="collapse"
					        data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
					        aria-label="Toggle navigation">
						<span></span>
						<span></span>
						<span></span>
					</button>
					
					<!-- EXTRA NAV -->
					<div class="extra-nav">
						<div class="extra-cell">
							<a href="{{route('start-writing')}}" class="btn btn-primary btnhover">Start Writing</a>
						</div>
					</div>
					
					<!-- Main Nav -->
					<div class="header-nav navbar-collapse collapse justify-content-start" id="navbarNavDropdown">
						<div class="logo-header logo-dark">
							<a href="{{route('index')}}"><img src="/images/logo.png" alt=""></a>
						</div>
						
						<ul class="nav navbar-nav">
							<li><a href="{{route('index')}}"><span>Home</span></a></li>
							<li><a href="{{route('about-us')}}"><span>About Us</span></a></li>
							<li><a href="{{route('faq')}}">FAQ's</a></li>
							<li><a href="{{route('playground.books-list')}}">Library</a></li>
							<li><a href="{{route('blog-grid')}}"><span>Blog</span></a></li>
							<li><a href="{{route('contact-us')}}"><span>Contact Us</span></a></li>
						</ul>
						<div class="dz-social-icon">
							<ul>
								<li><a class="fab fa-facebook-f" target="_blank"
								       href="https://www.facebook.com/playground-computer"></a></li>
								<li><a class="fab fa-twitter" target="_blank" href="https://twitter.com/playground-computers"></a></li>
								<li><a class="fab fa-linkedin-in" target="_blank"
								       href="https://www.linkedin.com/showcase/playground-computer/admin/"></a></li>
								<li><a class="fab fa-instagram" target="_blank"
								       href="https://www.instagram.com/playground-computer/"></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Main Header End -->
	
	</header>
	<!-- Header End -->
