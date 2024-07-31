<?php
require_once 'action-book.php';
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: #e91e63 !important;">
<!-- Login Form -->
<div class="container">
	<div class="container mt-2">
		<div class="row justify-content-center mt-5">
			<div class="col-lg-4 col-md-6 col-sm-6">
				<div class="card shadow">
					<div class="card-title text-center border-bottom">
						<h3 style="margin:10px;" class="text-center">
							<img src="images/android-chrome-192x192.png" style="height: 48px;"> Git Kanban Board
						</h3>
						<h4 class="p-3">Login</h4>
					</div>
					<div class="card-body">
						<div id="error-message" class="alert alert-danger d-none" role="alert"></div>
						<form method="POST" action="action-book.php">
							<input type="hidden" name="action" value="login">
							<div class="mb-4">
								<label for="username" class="form-label">Username</label>
								<input type="text" class="form-control" id="username" name="username"/>
							</div>
							<div class="mb-4">
								<label for="password" class="form-label">Password</label>
								<input type="password" class="form-control" id="password" name="password"/>
							</div>
							<div class="d-grid">
								<button type="submit" class="btn btn-primary text-light main-bg">Login</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="js/jquery-3.7.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
	$(document).ready(function () {
		const urlParams = new URLSearchParams(window.location.search);
		const errorMessage = urlParams.get('error');

		if (errorMessage) {
			$('#error-message').text(decodeURIComponent(errorMessage)).removeClass('d-none');
		}
	});
</script>
</body>
</html>
