<?php
use Hkm_services\HkmHtml\Hkm_Html;
?>
<!DOCTYPE html>
<html lang="en" class="tw_dark">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="theme-color" content="#5c68ff" />
       
        <link rel="stylesheet" href="/assets/css/icon-font.min.css" />


        <link rel="stylesheet" href="/assets/css/style_boot.css" />
        <link rel="stylesheet" href="/assets/css/style.css" />
        <link rel="stylesheet" href="/assets/css/app_style.css" />
        <link rel="stylesheet" href="/assets/css/phosphor_icons.css">	
       <script src='/assets/js/core.min.js'></script>
        <script src='/assets/js/alpine@3.min.js' defer></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script>
        function getBaseUrl() {
            var pathArray = location.href.split('/');
            var protocol = pathArray[0];
            var host = pathArray[2];
            var url = protocol + '//' + host + '/';

            return url;
        }
    </script>
        <?php
				print Hkm_Html::GET_HEADER();
        ?>
    
       
    </head>
    <body x-data="filestoreSelectorPlugin" >
        <div x-data="headerSecondFilestoreData">
        <div x-data="AddBlogButton">
        <div x-data="BlogsData">

    <!-- <div class="pre-loader">
		<div class="pre-loader-box">
			<div class="loader-logo"><img src="/assets/img/deskapp-logo.svg" alt=""></div>
			<div class='loader-progress' id="progress_div">
				<div class='bar' id='bar1'></div>
			</div>
			<div class='percent' id='percent1'>0%</div>
			<div class="loading-text">
				Loading....
			</div>
		</div>
	</div> -->


    <nav class='mobile-navbar'>
            <button id='mobileMenuBtn' class='tw_text-2xl tw_px-4'>
                <ion-icon style="transition: 0.25s ease;" class='tw_mt-2' name="menu-outline"></ion-icon>
            </button>
            <div class="tw_relative lg:tw_ml-[7.5rem] tw_hidden lg:tw_block"><button class="tw_text-xs tw_leading-5 tw_font-semibold tw_bg-slate-400/10 tw_rounded-full tw_py-1 tw_px-3 tw_flex tw_items-center tw_space-x-2 hover:tw_bg-slate-400/20 dark:tw_highlight-white/5" id="headlessui-menu-button-18" type="button" aria-haspopup="true" aria-expanded="false">v3.0.24<svg width="6" height="3" class="tw_ml-2 tw_overflow-visible" aria-hidden="true"><path d="M0 0L3 3L6 0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg></button></div>
            <a class="tw_ml-3 
            tw_button_hkm" 
            href="/blog/tailwindcss-v3">
              <strong class="tw_font-semibold">
                  AuthPost v3.0
              </strong>
              <svg width="2" height="2" fill="currentColor" aria-hidden="true" class="tw_ml-2 tw_text-sky-600 dark:tw_text-sky-400/70"><circle cx="1" cy="1" r="1"></circle></svg>
              <span class="tw_ml-2">
                Just-in-Time all the time
              </span>
              <svg width="3" height="6" class="tw_ml-3 tw_overflow-visible tw_text-sky-300 dark:tw_text-sky-400" aria-hidden="true"><path d="M0 0L3 3L0 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            </a>
            
            <div class="tw_flex">

                <div class="tw_relative tw_flex tw_items-center tw_ml-auto" x-data='{open: false}'>
                    <nav class=" tw_text-sm tw_leading-6 tw_font-semibold tw_text-slate-700 dark:tw_text-slate-200 tw_hidden lg:tw_inline-block"><ul class="tw_flex tw_space-x-8"><li><a class="hover:tw_text-sky-500 dark:hover:tw_text-sky-400" href="/docs/installation">Docs</a></li><li><a href="https://tailwindui.com" class="hover:tw_text-sky-500 dark:hover:tw_text-sky-400">Components</a></li><li><a class="hover:tw_text-sky-500 dark:hover:tw_text-sky-400" href="/blog">Blog</a></li></ul></nav>
                   
                    
                    <button  @click='open = !open' :class="open ? 'custom_icon_button_open' : '' " x-transition class='custom_icon_button' >
                        <ion-icon style="transition: 0.25s ease;"  name="notifications-outline"></ion-icon>
                    </button>
                    <div x-show="open" @click.outside="open = false" class="dropdown-content -tw_right-14 sm:-tw_right-8 md:tw_right-1 tw_text-center" x-transition:enter="tw_transition tw_ease-out tw_duration-200" x-transition:enter-start="tw_opacity-0 tw_scale-y-0 tw_transform" x-transition:enter-end="tw_opacity-100 tw_scale-y-100 tw_transform" x-transition:leave="tw_transition tw_ease-in tw_duration-100" x-transition:leave-start="tw_opacity-100" x-transition:leave-end="tw_opacity-0" style="display: none;">
                        
                        <div class='tw_p-3 tw_text-sm tw_text-gray-500'>
                            <span class="tw_text-theme-primary">2</span> Unread notification
                        </div>

                        <div class="tw_card custom_card">
                            <span class="tw_border tw_py-1 tw_px-2 tw_border-theme-primary tw_rounded-full tw_mr-3">
                                <ion-icon style="transition: 0.25s ease;" class='tw_mt-1 tw_text-theme-primary' name="dice-outline"></ion-icon>
                            </span>
                            <div>
                                <div class="tw_card-header">its just an notificationt</div>
                                <small class="tw_text-gray-500">5 Minutes ago</small>
                            </div>
                        </div>

                        <div class="tw_card custom_card">
                            <span class="tw_border tw_py-1 tw_px-2 tw_border-theme-primary tw_rounded-full tw_mr-3">
                                <ion-icon style="transition: 0.25s ease;" class='tw_mt-1 tw_text-theme-primary' name="dice-outline"></ion-icon>
                            </span>
                            <div>
                                <div class="tw_card-header">This notification title is longer than expection</div>
                                <small class="tw_text-gray-500">5 Minutes ago</small>
                            </div>
                        </div>

                        <div class="tw_card custom_card">
                            <span class="tw_border tw_py-1 tw_px-2 tw_border-gray-100 tw_rounded-full tw_mr-3">
                                <ion-icon style="transition: 0.25s ease;" class='tw_mt-1' name="dice-outline"></ion-icon>
                            </span>
                            <div>
                                <div class="tw_card-header">its just an notificationt</div>
                                <small class="tw_text-gray-500">5 Minutes ago</small>
                            </div>
                        </div>

                    </div>
                </div>
                
                <div class="tw_relative tw_inline-block"  x-data='{open: false}'>
                    <button  @click='open = !open' :class="open ? 'custom_icon_button_open' : '' "  class='custom_icon_button'>
                        <ion-icon style="transition: 0.25s ease;"  name="person-outline"></ion-icon>
                    </button>
                    <div x-show="open" @click.outside="open = false" class="dropdown-content -tw_right-6 sm:-tw_right-8 md:tw_right-1 tw_text-left" x-transition:enter="tw_transition tw_ease-out tw_duration-200" x-transition:enter-start="tw_opacity-0 tw_scale-y-0 tw_transform" x-transition:enter-end="tw_opacity-100 tw_scale-y-100 tw_transform" x-transition:leave="tw_transition tw_ease-in tw_duration-100" x-transition:leave-start="tw_opacity-100" x-transition:leave-end="tw_opacity-0" style="display: none;">
                        
                        <div class='tw_p-3 tw_text-sm tw_text-gray-500 tw_border-b dark:tw_border-gray-600/20 tw_border-gray-100'>
                            Signed in as
                            <span class='tw_block tw_text-theme-darked-primary'><?=$name?></span>
                        </div>

                        <a class='custom_c_card' href="#">
                            <ion-icon style="transition: 0.25s ease;" class='tw_mr-2' name="compass-outline"></ion-icon>
                            Visit site
                        </a>

                        <a class='custom_c_card' href="#">
                            <ion-icon style="transition: 0.25s ease;" class='tw_mr-2' name="wallet-outline"></ion-icon>
                            Manage wallet
                        </a>

                        <a class='custom_c_card' href="/admin/profile">
                            <ion-icon style="transition: 0.25s ease;" class='tw_mr-2' name="create-outline"></ion-icon>
                            Edit profile
                        </a>

                        <a class='tw_flex  dark:hover:tw_bg-slate-600/30 tw_items-center tw_w-full tw_p-3 tw_text-red-500 hover:tw_bg-red-50 tw_transition tw_duration-200 tw_border-t dark:tw_border-gray-600/20  tw_border-gray-100' href="#">
                            <ion-icon style="transition: 0.25s ease;" class='tw_mr-2' name="log-out-outline"></ion-icon>
                            Logout
                        </a>

                    </div>
                </div>
            </div>
    </nav>

<?php 
include __DIR__."/pages/components/selectOradd.image.modal.filestore.php";
 ?>
