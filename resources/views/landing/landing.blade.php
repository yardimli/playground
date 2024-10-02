<!DOCTYPE html>
<html lang="en">
<head>
	<title>Write Books with AI - Your Story, Our AI - Write Books Faster, Smarter, Better with AI</title>
	
	<!-- Meta Tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="Webestica.com">
	<meta name="description"
	      content="Write Books with AI - Your Story, Our AI - Write Books Faster, Smarter, Better with AI">
	
	<!-- Dark mode -->
	<script>
		const storedTheme = localStorage.getItem('theme')
		
		const getPreferredTheme = () => {
			if (storedTheme) {
				return storedTheme
			}
			return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
		}
		
		const setTheme = function (theme) {
			if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
				document.documentElement.setAttribute('data-bs-theme', 'dark')
			} else {
				document.documentElement.setAttribute('data-bs-theme', theme)
			}
		}
		
		setTheme(getPreferredTheme())
		
		window.addEventListener('DOMContentLoaded', () => {
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
		})
	
	</script>
	
	<!-- Favicon -->
	<link rel="shortcut icon" href="/assets/images/favicon.ico">
	
	<!-- Google Font -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
	
	<!-- Plugins CSS -->
	<link rel="stylesheet" type="text/css" href="/assets/vendor/font-awesome/css/all.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/vendor/bootstrap-icons/bootstrap-icons.css">
	<link rel="stylesheet" type="text/css" href="/assets/vendor/plyr/plyr.css">
	
	<!-- Theme CSS -->
	<link rel="stylesheet" type="text/css" href="/assets/css/style.css">

</head>
<body>

<!-- =======================
Header START -->

<header class="navbar-light header-static bg-transparent">
	<!-- Navbar START -->
	<nav class="navbar navbar-expand-lg">
		<div class="container">
			<!-- Logo START -->
			<a class="navbar-brand" href="{{ route('login') }}">
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
				<ul class="navbar-nav navbar-nav-scroll me-auto">
					<!-- Nav item -->
					<li class="nav-item">
						<a class="nav-link" href="{{route('login')}}">Log In</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{route('register')}}">Register</a>
					</li>
					<li class="nav-item">
						<a class="nav-link active" href="{{route('user.showcase-library')}}">Showcase</a>
					</li>
				</ul>
			</div>
			<!-- Main navbar END -->
			
			<!-- Nav right START -->
			<div class="ms-3 ms-lg-auto">
				{{--          <a class="btn btn-dark" href="app-download.html"> Download app </a>--}}
			</div>
			<!-- Nav right END -->
		</div>
	</nav>
	<!-- Navbar END -->
</header>

<!-- =======================
Header END -->

<main>
	
	<!-- **************** MAIN CONTENT START **************** -->
	
	<!-- Main banner START -->
	<section class="pt-3 pb-0 position-relative">
		
		<!-- Container START -->
		<div class="container">
			<!-- Row START -->
			<div class="row text-center position-relative z-index-1">
				<div class="col-lg-7 col-12 mx-auto">
					<!-- Heading -->
					<h1 class="display-4">WRITE BOOKS WITH AI</h1>
					<p class="lead">"Your Story, Our AI - Write Books Faster, Smarter, Better with AI"</p>
					<div class="d-sm-flex justify-content-center">
						<!-- button -->
						<a href="{{route('register')}}" class="btn btn-primary">Sign up</a>
						<div class="mt-2 mt-sm-0 ms-sm-3">
							<!-- Rating START -->
							<div class="hstack justify-content-center justify-content-sm-start gap-1">
								<div><i class="fa-solid fa-star text-warning"></i></div>
								<div><i class="fa-solid fa-star text-warning"></i></div>
								<div><i class="fa-solid fa-star text-warning"></i></div>
								<div><i class="fa-solid fa-star text-warning"></i></div>
								<div><i class="fa-solid fa-star-half-stroke text-warning"></i></div>
							</div>
							<!-- Rating END -->
							<i>"I can't believe it's free!"</i>
						</div>
					</div>
					<br>
				</div>
			</div>
			<!-- Row END -->
		</div>
		<!-- Container END -->
		
		<!-- Svg decoration START -->
		<div class="position-absolute top-0 end-0 mt-5 pt-5">
			<img class="h-300px blur-9 mt-5 pt-5" src="/assets/images/elements/07.svg" alt="">
		</div>
		<div class="position-absolute top-0 start-0 mt-n5 pt-n5">
			<img class="h-300px blur-9" src="/assets/images/elements/01.svg" alt="">
		</div>
		<div class="position-absolute top-50 start-50 translate-middle">
			<img class="h-300px blur-9" src="/assets/images/elements/04.svg" alt="">
		</div>
		<!-- Svg decoration END -->
	
	</section>
	<!-- Main banner END -->
	
	<!-- Messaging feature START -->
	<section>
		<div class="container">
			<div class="row justify-content-center">
				<!-- Title -->
				<div class="col-lg-7 col-12 mx-auto  text-center mb-4">
					<h2 class="h1">Craft Your Novel and Short Stories</h2>
					<p>Within a few steps create your story by choosing genre, reviewing book and character details.</p>
				</div>
			</div>
			<!-- Row START -->
			<div class="row justify-content-center">
				<!-- Feature START -->
				<div class="col-lg-9 col-12 mx-auto  text-center mb-4">
					<div class="card card-body bg-mode shadow-none border-1">
						<!-- Info -->
						<h4 class="mt-0 mb-3">Start Your Book</h4>
						<p class="mb-3">Write your book description and choose the structure, AI model, and language. Set up the
							genre, writing style, and narrative. Fill in author details, then click Submit to start.</p>
					</div>
					<img class="mb-4 mt-4" src="/images/screenshot/add-book-dark.png" alt="">
				</div>
				<!-- Feature END -->
			</div>
			<!-- Row START -->
			
			
			<!-- Row START -->
			<div class="row justify-content-center">
				<!-- Feature START -->
				<div class="col-lg-9 col-12 mx-auto  text-center mb-4">
					<div class="card card-body bg-mode shadow-none border-1">
						<!-- Info -->
						<h4 class="mt-0 mb-3">Go over the AI's suggestions to your story.</h4>
						<p class="mb-3">After the first step now the AI has the book title, a blurb and a back cover text written for you. It also has character profiles for the book. Here you can edit these to your liking before moving to the next step that will start writing the content of your book.</p>
					</div>
					<img class="mb-4 mt-4" src="/images/screenshot/add-book-step-2-dark.png" alt="">
				</div>
				<!-- Feature END -->
			</div>
			
			
			<!-- Row START -->
			<div class="row justify-content-center">
				<!-- Feature START -->
				<div class="col-lg-9 col-12 mx-auto  text-center mb-4">
					<div class="card card-body bg-mode shadow-none border-1">
						<!-- Info -->
						<h4 class="mt-0 mb-3">Review the chapters.</h4>
						<p class="mb-3">Now the overview of each chapter is written. They all have name, description, event, people, places as well as how they link to the previous or next chapter. Your job is to review the texts, verify that the story follows a smooth path, that events, people and places are as they should be.</p>
					</div>
					<img class="mb-4 mt-4" src="/images/screenshot/book-chapters-dark.png" alt="">
				</div>
				<!-- Feature END -->
			</div>
			
			
			<!-- Row START -->
			<div class="row justify-content-center">
				<!-- Feature START -->
				<div class="col-lg-9 col-12 mx-auto  text-center mb-4">
					<div class="card card-body bg-mode shadow-none border-1">
						<!-- Info -->
						<h4 class="mt-0 mb-3">Time the beats.</h4>
						<p class="mb-3">Now the overview of each chapter is written. They all have name, description, event, people, places as well as how they link to the previous or next chapter. Your job is to review the texts, verify that the story follows a smooth path, that events, people and places are as they should be.</p>
					</div>
					<img class="mb-4 mt-4" src="/images/screenshot/chapter-beats-dark.png" alt="">
				</div>
				<!-- Feature END -->
			</div>
			
			
			<!-- Row START -->
			<div class="row justify-content-center">
				<!-- Feature START -->
				<div class="col-lg-9 col-12 mx-auto  text-center mb-4">
					<div class="card card-body bg-mode shadow-none border-1">
						<!-- Info -->
						<h4 class="mt-0 mb-3">Your book is ready to be read.</h4>
						<p class="mb-3">Everything is done, you have your chapters and beats, you have a good book cover. Ready to export and publish your book!<br>Good Job!</p>
					</div>
					<img class="mb-4 mt-4" src="/images/screenshot/edit-book-dark.png" alt="">
				</div>
				<!-- Feature END -->
			</div>
		</div>
	</section>
	<!-- Messaging feature END -->
	
	
	<!-- Main content END -->
</main>
<!-- **************** MAIN CONTENT END **************** -->
{{--<script src="https://everperfectassistant.com/chat/chat.js?id=Gy4nA4OB5o"></script>--}}

@include('layouts.footer')

<!-- =======================
JS libraries, plugins and custom scripts -->

<!-- Bootstrap JS -->
<script src="/assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<!-- Vendors -->
<script src="/assets/vendor/plyr/plyr.js"></script>

<!-- Theme Functions -->
<script src="/assets/js/functions.js"></script>

</body>
</html>
