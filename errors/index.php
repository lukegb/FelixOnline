<!DOCTYPE html>

<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js" xmlns:fb="http://ogp.me/ns/fb#"> <!--<![endif]-->
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# feliximperial: http://ogp.me/ns/fb/feliximperial#">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="keywords" content="felix, student news, student newspaper, felix online, imperial college union, imperial college, felixonline"/>
    <meta name="description" content="Felix Online is the online companion to Felix, the student newspaper of Imperial College London.">
    <meta name="author" content="Jonathan Kim">
    <meta name="google-site-verification" content="V5LPwqv0BzMHvfMOIZvSjjJ-8tJc4Mi1A-L2AEbby50" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <base href="<?php echo STANDARD_URL; ?>">

    <!-- Title -->
    <title>
       	Felix Online - The student voice of Imperial College London 
    </title>

    <!-- Facebook -->
    <meta property="og:site_name" content="Felix Online"/>
    <meta property="fb:app_id" content="200482590030408" />

    <!-- Place favicon.ico & apple-touch-icon.png in the root of your domain and delete these references -->
    <link rel="shortcut icon" href="favicon.ico">
    <!-- CSS files -->
    <link rel="stylesheet" href="<?php echo STANDARD_URL; ?>errors/styles.css">
</head>
<body>
	<div class="header">
		<img class="felix" alt="FELIX" src="<?php echo STANDARD_URL; ?>errors/felix.jpg" />
	</div>
	<div class="box">
		<img class="cat" alt="" src="<?php echo STANDARD_URL; ?>errors/cat.jpg" />
		<div class="text">
			<h1>He's Dead, Jim!</h1>
			<p>Felix Online is experiencing major technical difficulties at the moment. The cat has already been notified, and things should be back up and running soon.</p>
			<p>In the meantime, please enjoy this video:</p>
			<iframe width="480" height="360" src="http://www.youtube.com/embed/QgkGogPLacA?rel=0" frameborder="0" allowfullscreen></iframe>
			<p id="techdetails_show" <?php if(LOCAL): echo 'style="display: none;"'; else: echo 'style="display: block;"'; endif; ?>><a href="javascript:void();" onClick="document.getElementById('techdetails').style.display = 'block'; document.getElementById('techdetails_show').style.display = 'none';">View some technical details</a></p>
			<div id="techdetails" class="technical_details" <?php if(LOCAL): echo 'style="display: block;"'; else: echo 'style="display: none;"'; endif; ?>>
				<p id="techdetails_hide"><a href="javascript:void();" onClick="document.getElementById('techdetails').style.display = 'none'; document.getElementById('techdetails_show').style.display = 'block';">Hide the technical details</a></p>
				<?php
					$data = array();
					if($prior_exception->getUser()->getUser() instanceof User) {
						$username = $prior_exception->getUser()->getUser()->getName();
					} else {
						$username = '<i>Unauthenticated</i>';
					}
					switch ($prior_exception->getCode()) {
						case EXCEPTION_ERRORHANDLER:
							$header = 'Internal error';
							$data['Details'] = $prior_exception->getMessage();
							$data['File'] = $prior_exception->getErrorFile();
							$data['Line'] = $prior_exception->getErrorLine();
							break;
						case EXCEPTION_GLUE:
							$header = 'Misconfigured glue';
							$data['URL'] = $prior_exception->getClass();
							$data['Class requested'] = $prior_exception->getItem();
							$data['Method requested'] = $prior_exception->getVerb();
							break;
						case EXCEPTION_GLUE_URL:
							$header = 'URL is not valid';
							$data['URL'] = $prior_exception->getClass();
							break;
						case EXCEPTION_IMAGE_NOTFOUND:
							$dimensions = $prior_exception->getImageDimensions();
							$header = 'Image could not be found';
							$data['Containing page'] = $prior_exception->getPage();
							$data['Image URL'] = $prior_exception->getImageUrl();
							$data['Requested dimensions'] = $dimensions['width'].'x'.$dimensions['height'];
							break;
						case EXCEPTION_MODEL:
							$header = 'Misconfigured model';
							$data['Item type'] = $prior_exception->getClass();
							$data['Item identifier'] = $prior_exception->getItem();
							$data['Action'] = $prior_exception->getVerb();
							$data['Property'] = $prior_exception->getProperty();
							break;
						case EXCEPTION_MODEL_NOTFOUND:
							$header = 'Item is not in database';
							$data['Item type'] = $prior_exception->getClass();
							$data['Item identifier'] = $prior_exception->getItem();
							break;
						case EXCEPTION_VIEW_NOTFOUND:
							$header = 'Template does not exist';
							$data['Template'] = $prior_exception->getView();
							break;
						default:
							$header = 'Internal exception';
							$data['Details'] = $prior_exception->getMessage();
							$data['File'] = $prior_exception->getFile();
							$data['Line'] = $prior_exception->getLine();
							break;
					}
				?>
				<h2><?php echo $header; ?></h2>
				<ul>
					<li><b>Username:</b> <?php echo $username; ?></li>
					<?php
						foreach($data as $name => $value) {
							echo '<li><b>'.$name.':</b> '.$value.'</li>';
						}
					?>
				</ul>
				<?php
				if(LOCAL) {
					echo '<h3>Backtrace <i>(shown in local mode only)</i></h3>';
					echo '<pre>'.$prior_exception->getTraceAsString().'</pre>';
				}
				?>
			</div>
			&copy; Felix Imperial
		</div>
	</div>
</body>
</html>
