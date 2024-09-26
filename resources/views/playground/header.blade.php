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
	<title>{{__('default.Write Books With AI')}}</title>
	
	<!-- MOBILE SPECIFIC -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<!-- STYLESHEETS -->
	<link href="/css/bootstrap.css" rel="stylesheet">
	
	<link rel="stylesheet" type="text/css" href="/css/bootstrap-select.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/fontawesome/css/all.min.css">
	<link rel="stylesheet" type="text/css" href="/css/swiper-bundle.min.css">
	<link rel="stylesheet" type="text/css" href="/css/animate.css">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	
	<link href="/css/bootstrap-icons.min.css" rel="stylesheet">
	
	<link rel="stylesheet" type="text/css" href="/css/custom.css">
	<link rel="stylesheet" type="text/css" href="/css/navigation.css">
	
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
		
		// <!-- Dark mode -->
		const storedTheme = localStorage.getItem('theme')
		
		const getPreferredTheme = () => {
			if (storedTheme) {
				return storedTheme
			}
			return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
		}
		
		const setTheme = function (theme) {
			if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
				document.documentElement.setAttribute('data-bs-theme', 'dark');
				$('body').addClass('dark-mode');
			} else
			if (theme === 'dark') {
				document.documentElement.setAttribute('data-bs-theme', theme);
				$('body').addClass('dark-mode');
			} else
			{
				document.documentElement.setAttribute('data-bs-theme', theme);
				$('body').removeClass('dark-mode');
			}
		}
		
		$(document).ready(function () {
			
			setTheme(getPreferredTheme());
			
			var el = document.querySelector('.theme-icon-active');
			if (el != 'undefined' && el != null) {
				const showActiveTheme = theme => {
					const activeThemeIcon = document.querySelector('.theme-icon-active use')
					const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`)
					const svgOfActiveBtn = btnToActive.querySelector('.mode-switch use').getAttribute('href')
					
					document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
						element.classList.remove('active')
					})
					
					btnToActive.classList.add('active')
					activeThemeIcon.setAttribute('href', svgOfActiveBtn)
				}
				
				window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
					if (storedTheme !== 'light' || storedTheme !== 'dark') {
						setTheme(getPreferredTheme())
					}
				})
				
				showActiveTheme(getPreferredTheme())
				
				document.querySelectorAll('[data-bs-theme-value]')
					.forEach(toggle => {
						toggle.addEventListener('click', () => {
							const theme = toggle.getAttribute('data-bs-theme-value')
							localStorage.setItem('theme', theme)
							setTheme(theme)
							showActiveTheme(theme)
						})
					})
				
			}
			
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
	
	<!-- =======================
	Header START -->
	<header class="navbar-light fixed-top header-static bg-mode">
		
		<!-- Logo Nav START -->
		<nav class="navbar navbar-expand-lg">
			<div class="container">
				<!-- Logo START -->
				<a class="navbar-brand" href="{{route('index')}}">
					<img class="light-mode-item navbar-brand-item" src="/images/logo.png" alt="logo">
					<img class="dark-mode-item navbar-brand-item" src="/images/logo.png" alt="logo">
				</a>
				<!-- Logo END -->
				
				<!-- Responsive navbar toggler -->
				<button class="navbar-toggler ms-auto icon-md btn btn-light p-0" type="button" data-bs-toggle="collapse"
				        data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false"
				        aria-label="Toggle navigation">
        <span class="navbar-toggler-animation">
          <span></span>
          <span></span>
          <span></span>
        </span>
				</button>
				
				<!-- Main navbar START -->
				<div class="collapse navbar-collapse" id="navbarCollapse">
					
					<!-- Nav Search START -->
					<div class="nav mt-3 mt-lg-0 flex-nowrap align-items-center px-4 px-lg-0">
						<div class="nav-item w-100">
							<form class="rounded position-relative">
								<input class="form-control ps-5 bg-light" type="search" placeholder="Search..."
								       aria-label="Search">
								<button class="btn bg-transparent px-2 py-0 position-absolute top-50 start-0 translate-middle-y"
								        type="submit"><i class="bi bi-search fs-5"> </i></button>
							</form>
						</div>
					</div>
					<!-- Nav Search END -->
					
					<ul class="navbar-nav navbar-nav-scroll ms-auto">
						<li class="nav-item"><a class="nav-link active" href="{{route('index')}}"><span>Home</span></a></li>
						<li class="nav-item"><a class="nav-link" href="{{route('about-us')}}"><span>About Us</span></a></li>
						<li class="nav-item"><a class="nav-link" href="{{route('faq')}}">FAQ's</a></li>
						<li class="nav-item"><a class="nav-link" href="{{route('playground.books-list')}}">Library</a></li>
						<li class="nav-item"><a class="nav-link" href="{{route('blog-grid')}}"><span>Blog</span></a></li>
						<li class="nav-item"><a class="nav-link" href="{{route('contact-us')}}"><span>Contact Us</span></a></li>
					</ul>
				</div>
				<!-- Main navbar END -->
				
				<!-- Nav right START -->
				<ul class="nav flex-nowrap align-items-center ms-sm-3 list-unstyled">
					<li class="nav-item ms-2">
						<a class="nav-link icon-md btn btn-light p-0" href="{{route('start-writing')}}" title="Help">
							<i class="bi bi-pencil fs-6"> </i>
						</a>
					</li>
					
					<li class="nav-item ms-2 dropdown">
						<a class="nav-link btn icon-md p-0" href="#" id="profileDropdown" role="button"
						   data-bs-auto-close="outside"
						   data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
							@if (Auth::user())
								<img class="avatar-img rounded-circle"
								     src="{{ !empty(Auth::user()->avatar) ? Storage::url(Auth::user()->avatar) : '/assets/images/avatar/01.jpg' }}"
								     alt="avatar">
							@else
								<img class="avatar-img rounded-2" src="/assets/images/avatar/placeholder.jpg" alt="">
							@endif
						</a>
						<ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3"
						    aria-labelledby="profileDropdown">
							<!-- Profile info -->
							@if (Auth::user())
								<li class="px-3">
									<div class="d-flex align-items-center position-relative">
										<!-- Avatar -->
										<div class="avatar me-3">
											<img class="avatar-img rounded-circle"
											     src="{{ !empty(Auth::user()->avatar) ? Storage::url(Auth::user()->avatar) : '/assets/images/avatar/01.jpg' }}"
											     alt="avatar">
										</div>
										<div>
											<a class="h6 stretched-link"
											   href="{{route('index')}}">{{ Auth::user()->username }}</a>
											<p class="small m-0">Writer</p>
										</div>
									</div>
									<a class="dropdown-item btn btn-primary-soft btn-sm my-2 text-center"
									   href="{{route('index')}}">View profile</a>
								</li>
								<a class="dropdown-item" href="{{route('index')}}"><i
										class="bi bi-person fa-fw me-2"></i>Edit Profile</a>
							@endif
							<!-- Links -->
							{{--						<li class="dropdown-divider"></li>--}}
							<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
								@csrf
							</form>
							@if (Auth::user())
								<li><a class="dropdown-item bg-danger-soft-hover" href="#"
								       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
											class="bi bi-power fa-fw me-2"></i>Sign Out</a></li>
							@else
								<li><a class="dropdown-item bg-primary-soft-hover" href="{{ route('login') }}"><i
											class="bi bi-unlock fa-fw me-2"></i>Sign In</a></li>
								<li><a class="dropdown-item bg-primary-soft-hover" href="{{ route('register') }}"><i
											class="bi bi-person-circle fa-fw me-2"></i>Sign Up</a></li>
								<li><a class="dropdown-item" href="{{route('index')}}"><i
											class="bi bi-shop fa-fw me-2"></i>Buy Tokens</a></li>
							
							@endif
							<!-- Dark mode options START -->
							<hr class="dropdown-divider">
							<div
								class="modeswitch-item theme-icon-active d-flex justify-content-center gap-3 align-items-center p-2 pb-0">
								<span>Mode:</span>
								<button type="button" class="btn btn-modeswitch nav-link text-primary-hover mb-0"
								        data-bs-theme-value="light" data-bs-toggle="tooltip" data-bs-placement="top"
								        data-bs-title="Light">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
									     class="bi bi-sun fa-fw mode-switch" viewBox="0 0 16 16">
										<path
											d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
										<use href="#"></use>
									</svg>
								</button>
								<button type="button" class="btn btn-modeswitch nav-link text-primary-hover mb-0"
								        data-bs-theme-value="dark" data-bs-toggle="tooltip" data-bs-placement="top"
								        data-bs-title="Dark">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
									     class="bi bi-moon-stars fa-fw mode-switch" viewBox="0 0 16 16">
										<path
											d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z"/>
										<path
											d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
										<use href="#"></use>
									</svg>
								</button>
								<button type="button" class="btn btn-modeswitch nav-link text-primary-hover mb-0 active"
								        data-bs-theme-value="auto" data-bs-toggle="tooltip" data-bs-placement="top"
								        data-bs-title="Auto">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
									     class="bi bi-circle-half fa-fw mode-switch" viewBox="0 0 16 16">
										<path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
										<use href="#"></use>
									</svg>
								</button>
							</div>
					</li>
					<!-- Dark mode options END-->
				</ul>
				</li>
				<!-- Profile START -->
				
				</ul>
				<!-- Nav right END -->
			</div>
		</nav>
		<!-- Logo Nav END -->
	</header>
	<!-- =======================
	Header END -->

