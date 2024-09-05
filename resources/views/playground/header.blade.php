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
	
	<!-- FAVICONS ICON -->
	<link rel="icon" type="image/x-icon" href="/images/favicon.png"/>
	
	<!-- PAGE TITLE HERE -->
	<title>Playground Book Store Ecommerce Website</title>
	
	<!-- MOBILE SPECIFIC -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<!-- STYLESHEETS -->
	<link rel="stylesheet" type="text/css" href="/css/bootstrap-select.min.css">
	<link rel="stylesheet" type="text/css" href="/icons/fontawesome/css/all.min.css">
	<link rel="stylesheet" type="text/css" href="/css/swiper-bundle.min.css">
	<link rel="stylesheet" type="text/css" href="/css/animate.css">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	
	<!-- GOOGLE FONTS-->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link
		href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
		rel="stylesheet">

</head>
<body>

<div class="page-wraper">
	<div id="loading-area" class="preloader-wrapper-1">
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
	<header class="site-header mo-left header style-1">
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
									<div class="dropdown-menu py-0 dropdown-menu-end">
										<div class="dropdown-header">
											<h6 class="m-0">{{ Auth::user()->username }}</h6>
											<span>{{ Auth::user()->email }}</span>
										</div>
										<div class="dropdown-body">
											<a href="{{route('my-profile')}}"
											   class="dropdown-item d-flex justify-content-between align-items-center ai-icon">
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
										
										</div>
										<div class="dropdown-footer">
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
		<div class="sticky-header main-bar-wraper navbar-expand-lg">
			<div class="main-bar clearfix">
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
