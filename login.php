<?php
require_once 'action-session.php';
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo __e('Playground Books - Login'); ?></title>
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<?php echo write_js_translations(); ?>
</head>

<body style="background: #e91e63 !important;">
<!-- Login Form -->
<div class="container">
	<div class="container mt-2">
		<div class="row justify-content-center mt-5">
			<div class="col-lg-4 col-md-6 col-sm-6">
				<div class="card shadow">
					<div class="card-title text-center border-bottom">
							<a href="index.php"><img src="images/android-chrome-192x192.png" style="width: 140px;"></a>
						<h4 class="p-1"><?php echo __e('Login / Register'); ?></h4>
					</div>
					<div class="card-body">
						<div id="error-message" class="alert alert-danger d-none" role="alert"></div>
						<form method="POST" action="action-other-functions.php">
							<input type="hidden" name="action" value="login">
							<div class="mb-2">
								<input type="text" class="form-control" id="username" name="username" placeholder="<?php echo __e('username'); ?>"/>
							</div>
							<div class="mb-2">
								<input type="password" class="form-control" id="password" name="password" placeholder="<?php echo __e('password'); ?>"/>
							</div>
							<div class="mb-2">
								<button type="submit" class="btn btn-primary text-light main-bg d-inline-block"><?php echo __e('Login / Register'); ?></button>
								<a href="index.php" class="btn btn-info text-dark float-end d-inline-block"><?php echo __e('Browse As Visitor'); ?></a>
							</div>
						</form>
						<div id="error-message" class="alert alert-info mt-3" role="alert">
							<?php echo __e('You can login with any username and password. If the username doesn\'t exist, it will be created. The password will be hashed. If the username exists, the password will be verified. If the password is incorrect, you will be told so.'); ?>
						</div>
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
