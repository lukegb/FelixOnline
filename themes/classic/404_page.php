<?php
$timing->log('404 error');

$header = array(
	'title' => 'Felix Online - The student voice of Imperial College London',
	'meta' => '<meta property="og:image" content="http://felixonline.co.uk/img/title.jpg"/>'
);

$theme->render('header', $header); 
$timing->log('after header');

?>
	<!-- Article wrapper -->
	<div class="container_12">
		<div class="grid_12 error">
			<h2>Lost Cat</h2>
			<img src="/img/felix_cat-300.jpg" id="lostcat" width="300px"/>
			<div class="grid_8 push_2">
				<p>Medium sized black and white cat, responds to "Felix". Likes to party and find entertaining gifs online. Mostly house trained but can bite without warning.</p>
				<p>If found then please <a href="/contact/">contact us</a> or you can try searching for it here: </p>
				<form action="/search/" id="cse-search-box">
					<input type="text" name="q" size="31" id="searchBox" class="faded" autocomplete="off" onclick="if(this.value == 'Search Felix Online...') {this.value=''; this.style.color='#222';}" onblur="if(this.value.length == 0){ this.value='Search Felix Online...'; this.style.color='#999';};" value="Search Felix Online..."/>
					<input type="submit" name="sa" value="" id="searchButton"/>
				</form>
				<p>Or just return to the <a href="/">homepage</a> to begin again.</p>
			</div>
		</div>
		<div class="clear"></div>
		<div class="grid_12 error">
			<?php if(LOCAL || ($e->getUser() instanceof CurrentUser && $e->getUser()->getRole() == 100)) { ?>
			<p id="techdetails_show" style="display: none;"><a href="javascript:void();" onClick="document.getElementById('techdetails').style.display = 'block'; document.getElementById('techdetails_show').style.display = 'none';">View some technical details</a></p>
			<div id="techdetails" class="technical_details" style="display: block;">
				<p id="techdetails_hide"><a href="javascript:void();" onClick="document.getElementById('techdetails').style.display = 'none'; document.getElementById('techdetails_show').style.display = 'block';">Hide the technical details</a></p>
				<?php
					$exceptions = array($prior_exception, $e);
					
					foreach($exceptions as $exception) {
						if($exception == null) {
							continue;
						}
						
						$data = array();
						if($exception->getUser() instanceof CurrentUser && $exception->getUser()->getUser() instanceof User) {
							$username = $exception->getUser()->getUser()->getName();
						} else {
							$username = '<i>Unauthenticated</i>';
						}
						switch ($exception->getCode()) {
							case EXCEPTION_ERRORHANDLER:
								$header = 'Internal error';
								$data['Details'] = $exception->getMessage();
								$data['File'] = $exception->getErrorFile();
								$data['Line'] = $exception->getErrorLine();
								break;
							case EXCEPTION_GLUE:
								$header = 'Misconfigured glue';
								$data['URL'] = $exception->getUrl();
								$data['Class requested'] = $exception->getClass();
								$data['Method requested'] = $exception->getMethod();
								break;
							case EXCEPTION_GLUE_URL:
								$header = 'URL is not valid';
								$data['URL'] = $exception->getUrl();
								break;
							case EXCEPTION_IMAGE_NOTFOUND:
								$dimensions = $exception->getImageDimensions();
								$header = 'Image could not be found';
								$data['Containing page'] = $exception->getPage();
								$data['Image URL'] = $exception->getImageUrl();
								$data['Requested dimensions'] = $dimensions['width'].'x'.$dimensions['height'];
								break;
							case EXCEPTION_MODEL:
								$header = 'Misconfigured model';
								$data['Item type'] = $exception->getClass();
								$data['Item identifier'] = $exception->getItem();
								$data['Action'] = $exception->getVerb();
								$data['Property'] = $exception->getProperty();
								break;
							case EXCEPTION_MODEL_NOTFOUND:
								$header = 'Item is not in database';
								$data['Item type'] = $exception->getClass();
								$data['Item identifier'] = $exception->getItem();
								break;
							case EXCEPTION_VIEW_NOTFOUND:
								$header = 'Template does not exist';
								$data['Template'] = $exception->getView();
								break;
							default:
								$header = 'Internal exception';
								$data['Details'] = $exception->getMessage();
								$data['File'] = $exception->getFile();
								$data['Line'] = $exception->getLine();
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
					echo '<h3>Backtrace</h3>';
					echo '<pre>'.$exception->getTraceAsString().'</pre>';
				}
			}
			?>
		</div>
	</div>
	<!-- End of article wrapper -->

<?php $theme->render('footer'); ?>
