<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Welcome to @HkmCode Framework V2.0.1!</title>
	<meta name="description" content="The small framework with powerful features">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<script src="https://kit.fontawesome.com/32d2ccdcca.js" crossorigin="anonymous"></script>
	<!-- STYLES -->
	<link rel="stylesheet" href="assets/css/welcome.css">

</head>

<body>
<style>
	.hero{
		background-image: url(bg.png);
		background-repeat: no-repeat;
		background-position: center;
		background-size: cover;
	}
</style> 
  <!-- HEADER: MENU + HERO SECTION -->

<section class="hero is-info is-fullheight"  >

  <div class="hero-head">
    <nav class="navbar">
      <div class="container">
        <div class="navbar-brand">
          <span class="navbar-item is-clickable">
			<svg width="15.5rem"  height="2.6rem"  version="1.1"
				xmlns="http://www.w3.org/2000/svg">
				<image x="0" y="0" width="100%" height="3rem" href="@hkmCodeTitle.svg" />
			</svg>
          </span>
          <span class="navbar-burger" data-target="navbarMenuHeroB">
            <span></span>
            <span></span>
            <span></span>
          </span>
        </div>
        <div id="navbarMenuHeroB" class="navbar-menu">
          <div class="navbar-end">
            <div class="navbar-item is-active">
             <a href="http://" class="button is-primary is-inverted is-outlined">Documentation</a>
            </div>
            <div class="navbar-item">
            <a href="http://" class="button is-primary is-inverted is-outlined">News</a>  
            </div>
            <div class="navbar-item">
            <a href="http://" class="button is-primary is-inverted is-outlined">Discuss</a>
            </div>
			<div class="navbar-item">
            <a href="http://" class="button is-primary is-inverted is-outlined">Partners</a>
            </div>
            <span class="navbar-item">
              <a class="button is-info is-inverted">
                <span class="icon">
                  <i class="fab fa-github"></i>
                </span>
                <span>Download</span>
              </a>
            </span>
          </div>
        </div>
      </div>
    </nav>
  </div>

  <div class="hero-body">
    <div class="container has-text-centered">
		<svg width="48rem"  height="200"  version="1.1"
		xmlns="http://www.w3.org/2000/svg">
			<image x="10" y="20" width="100%" height="87%" href="@hkmCode.svg" />
		</svg>


      <p class="title mt-6" style="max-width: 48rem !important; margin-right:auto;margin-left:auto;font-size: 2.3rem !important;line-height: 1 !important;">
        As easy as you've never worked up
      </p>
      <p class="subtitle pt-2" style="max-width: 48rem; ;font-size: 1.2rem !important;margin-right:auto;margin-left:auto;line-height: 1.75rem !important;">
	  Master your big projects with less time invest
	   and maximize your Efficiency 
	  to work on multiple project with delighted deadlines
      </p>
    </div>
  </div>

<!-- FOOTER: DEBUG INFO + COPYRIGHTS -->

  <div class="hero-foot">
	  

      <div class="copyrights p-4">
          <p>Page rendered in {elapsed_time} seconds</p>
		  <p>Environment: <?= ENVIRONMENT ?></p> 

		   <p>&copy; <?= date('Y') ?> HkmCode Team. @HkmCode is a PHP Framework released under the MIT licence.
		   </p>

	  </div>
  </div>
</section>
</body>
</html>
