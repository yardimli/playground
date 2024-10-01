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
				<div class="col-lg-7 mx-auto">
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
	<section class="py-4 py-sm-5">
		<div class="container">
			<div class="row justify-content-center">
				<!-- Title -->
				<div class="col-lg-12 text-center mb-4">
					<h2 class="h1">Craft Your Novel and Short Stories</h2>
					<p>Within a few steps create your story by choosing genre, reviewing book and character details.</p>
				</div>
			</div>
			<!-- Row START -->
			<div class="row g-4 g-lg-5">
				<!-- Feature START -->
				<div class="col-lg-12 text-center">
					<img class="h-200px mb-4" src="/assets/images/header1.jpg" alt="">
					<h4>A Tale of Untethered Fancy: The Chronicles of WRITE BFOOKS WITH AI</h4>
					<p class="mb-0">Embark on a splendid journey through the intricate domain that bridges the gaps twixt human
						ingenuity and artificial invention. WRITE BOOKS WITH AI doth cast anew the grand tapestry of storytelling,
						creating visionary realms heretofore unimagined, and forever altering the quill of the bard.</p>
				</div>
				<!-- Feature END -->
			</div>
			<!-- Row START -->
		</div>
	</section>
	<!-- Messaging feature END -->
	
	<!-- features START -->
	<section class="py-4 py-sm-5">
		<div class="container">
			<div class="row g-4 g-lg-5 align-items-top">
				<!-- Title -->
				<div class="col-lg-4">
					<h2 class="h1">The Fathomless Expanse: A Glimpse into WRITE BOOKS WITH AI's Multifarious Features</h2>
					<p class="mb-4">Embark with us upon a journey through the myriad possibilities proffered by v's vast
						constellation of attributes. Be beguiled by the wonders of our technological and creative assemblage, where
						the limitless potential of human ingenuity and AI innovation coalesce to sculpt unfathomable narratives
						amongst the celestials of storytelling. </p>
					<a class="btn btn-dark" href="{{route('register')}}">Start now</a>
				</div>
				<!-- Feature item START -->
				<div class="col-lg-8">
					<div class="card card-body bg-mode shadow-none border-0 p-4 p-sm-5 pb-sm-0 overflow-hidden">
						<div class="row g-4">
							<div class="col-md-6">
								<!-- Info -->
								<h4 class="mt-0 mb-3">The Synaptic Waltz: A Conversational Communion of Human and AI Minds</h4>
								<p class="mb-5">Delve into the enthralling realm where human exchange with AI transcends conventional
									boundaries, giving birth to narratives in a melding of minds. As the dance of dialogues intertwined,
									each interaction shapes and molds the story, creating a symbiotic tapestry of characters and plots
									that fascinate and captivate the boundless potential within the ever-expanding, collaborative universe
									of storytelling.</p>
							</div>
							<div class="col-md-6 text-end">
								<!-- image -->
								<!-- iphone-x mockup START -->
								<div class="iphone-x iphone-x-small iphone-x-half mb-n5 mt-0"
								     style="background: url(/assets/images/mobile_ui.jpg); background-size: 100%;">
									<i></i>
									<b></b>
								</div>
								<!-- iphone-x mockup END -->
							</div>
						</div>
					</div>
				</div>
				<!-- Feature item END -->
				
				<!-- Feature item START -->
				<div class="col-md-4">
					<div class="card card-body bg-mode shadow-none border-0 p-4 p-lg-5">
						<!-- Image -->
						<div>
							<img class="w-300px" src="/assets/images/header4.jpg" alt="">
						</div>
						<!-- Info -->
						<h4 class="mt-4">Cosmic Convergence: The Nexus of Human and AI Creation</h4>
						<p class="mb-0">As we continue to explore the vast cosmic ocean of knowledge, WRITE BOOKS WITH AI stands as a
							beacon bearing witness to the unification of human creativity and artificial intelligence. Dive into the
							remarkable world where the next generation of storytelling emerges, intrigued by the very essence of our
							cosmic identity.</p>
					</div>
				</div>
				<!-- Feature item START -->
				
				<div class="col-md-4">
					<div class="card card-body bg-mode shadow-none border-0 p-4 p-lg-5">
						<!-- Image -->
						<div>
							<img class="w-300px" src="/assets/images/header5.jpg" alt="">
						</div>
						<!-- Info -->
						<h4 class="mt-4">The Pale Blue Dot of Storytelling: A New Frontier in Narrative Creation</h4>
						<p class="mb-0">As pioneers in the grand expanse of possibility, WRITE BOOKS WITH AI offers an extraordinary
							glimpse into the evolving world of storytelling. By merging the once-separated realms of human imagination
							and AI innovation, we embark on a new era, finding ourselves as cosmic citizens of a shared literary
							universe.</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card card-body bg-mode shadow-none border-0 p-4 p-lg-5">
						<!-- Image -->
						<div>
							<img class="w-300px" src="/assets/images/header6.jpg" alt="">
						</div>
						<!-- Info -->
						<h4 class="mt-4">An AI Odyssey: Envisioning our Collective Future of Narratives</h4>
						<p class="mb-0">WRITE BOOKS WITH AI is a testament to the boundless potential of human progress and AI
							collaboration. As we weave our way through the vast cosmic dance of storytelling, remember that somewhere
							out there amidst the uncharted abyss of narrative possibility, our imaginations are enabled by the very
							technology we have fashioned, bringing forth stories that mirror the cosmos around us.</p>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- features END -->
	
	<!-- Get Discovered START -->
	<section class="py-4 py-sm-5">
		<div class="container">
			<div class="row">
				<div class="col-lg-10 ms-auto">
					<div class="row g-4 align-items-center">
						<div class="col-md-5 col-lg-5 position-relative">
							<!-- Image -->
							<img class="rounded-circle" src="/assets/images/581177845_A Journey into the Limelight.jpg" alt="">
							<!-- Chat START -->
							<div class="position-absolute top-50 start-0 translate-middle d-none d-lg-block">
								<!-- Chat item -->
								<div class="bg-mode border p-3 rounded-3 rounded-start-top-0 d-flex align-items-center mb-3">
									<!-- Avatar -->
									<div class="avatar avatar-xs me-3">
										<a href="{{route('user.showcase-library')}}"> <img class="avatar-img rounded-circle"
										                                          src="/assets/images/avatar/12.jpg" alt=""> </a>
									</div>
									<!-- Comment box  -->
									<div class="d-flex">
										<h6 class="mb-0 ">Inventive Storytelling Awaits!</h6>
									</div>
								</div>
								
								<!-- Chat item -->
								<div class="bg-mode border p-3 rounded-3 rounded-start-top-0 d-flex align-items-center mb-3">
									<!-- Avatar -->
									<div class="avatar avatar-xs me-3">
										<a href="#!"> <img class="avatar-img rounded-circle" src="/assets/images/avatar/10.jpg" alt=""> </a>
									</div>
									<!-- Comment box  -->
									<div class="d-flex">
										<h6 class="mb-0 ">AI-Assisted Literary Magic!</h6>
									</div>
								</div>
								
								<!-- Chat item -->
								<div class="bg-mode border p-3 rounded-3 rounded-start-top-0 d-flex align-items-center mb-3">
									<!-- Avatar -->
									<div class="avatar avatar-xs me-3">
										<a href="#!"> <img class="avatar-img rounded-circle"
										                   src="/assets/images/581177845_A Journey into the Limelight.jpg" alt=""> </a>
									</div>
									<!-- Comment box  -->
									<div class="d-flex">
										<h6 class="mb-0 ">Your Story, Our AI - Write Books Faster, Smarter, Better with AI</h6>
									</div>
								</div>
							</div>
							<!-- Chat END -->
						</div>
						<div class="col-md-6">
							<div class="ms-4">
								<!-- Info -->
								<h2 class="h1">A Journey into the Limelight</h2>
								<p class="lead mb-4">Traverse through the fabled lands wherein lays the resplendent opportunity for
									discovered fortune. As a weaver of tales in the grand tapestry of the mythic realm, thine own sagas
									shall be revealed to many an eager eye, their imaginations set alight by the enchanting flames of your
									prose, which finds new life within the hearts of those who seek the extraordinary.</p>
								<a class="btn btn-primary" href="{{route('register')}}"> Let's start </a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Get Discovered START -->
	
	
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
