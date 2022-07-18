<?php


include_once __DIR__."/modal.edit_profile_photo.profile.php";

?>

<div class="col-xl-4 col-lg-5 col-md-4 col-sm-12 tw_mb-[30px]">
                       <div class="tw_p-[20px]  tw_bg-slate-100 dark:tw_bg-slate-800  tw_text-slate-800 dark:tw_text-slate-300 tw_rounded-[10px] !tw_h-[100%]">
							<div class="profile-photo tw_w-[160px] tw_h-[160] tw_mt-0 tw_mx-auto tw_relative tw_mb-[15px]">
								<a href="modal" data-toggle="modal" data-target="#modal" class="edit-avatar"><i class="fa fa-pencil"></i></a>
								<img src="<?=$avatar?>" alt=""  id="profPictu" class="avatar-photo tw_max-w-[100%] tw_z-0 tw_align-top tw_h-auto tw_rounded-full">
							</div>
							<h5 class="tw_leading-[1.35] tw_text-center tw_mb-0 dark:tw_text-slate-300"><?=$name?></h5>
							<p class="tw_text-center tw_text-[14px] tw_leading-[1.71] tw_mb-[15px]">Lorem ipsum dolor sit amet</p>
							<div class="profile-info">
								<h5 class="tw_mb-[20px] tw_font-extrabold tw_text-sky-700 tw_text-[18px]">Contact Information</h5>
								<ul>
									<li>
										<span>Email Address:</span>
										<?=$email?>
									</li>
									<li>
										<span>Phone Number:</span>
										<?=$phone?>
									</li>
									<li>
										<span>Country:</span>
										<?=$country?>
									</li>
									<li>
										<span>Address:</span>
										<?=$address?>
									</li>
								</ul>
							</div>
							<div class="profile-social">
								<h5 class="tw_mb-[20px] tw_font-extrabold tw_text-sky-700 tw_text-[18px]">Social Links</h5>
								<ul class="clearfix">
									<li><a href="#" class="custom_btn" style="background: #3b5998; color:white;"><i class="fa fa-facebook"></i></a></li>
									<li><a href="#" class="custom_btn" style="background: #1da1f2; color:white;"><i class="fa fa-twitter"></i></a></li>
									<li><a href="#" class="custom_btn" style="background: #007bb5; color:white;"><i class="fa fa-linkedin"></i></a></li>
									<li><a href="#" class="custom_btn" style="background: #f46f30; color:white;"><i class="fa fa-instagram"></i></a></li>
									<li><a href="#" class="custom_btn" style="background: #c32361; color:white;"><i class="fa fa-dribbble"></i></a></li>
									<li><a href="#" class="custom_btn" style="background: #3d464d; color:white;"><i class="fa fa-dropbox"></i></a></li>
									<li><a href="#" class="custom_btn" style="background: #db4437; color:white;"><i class="fa fa-google-plus"></i></a></li>
									<li><a href="#" class="custom_btn" style="background: #bd081c; color:white;"><i class="fa fa-pinterest-p"></i></a></li>
									<li><a href="#" class="custom_btn" style="background: #00aff0; color:white;"><i class="fa fa-skype"></i></a></li>
									<li><a href="#" class="custom_btn" style="background: #00b489; color:white;"><i class="fa fa-vine"></i></a></li>
								</ul>
							</div>
							<div class="profile-skills">
								<h5 class="tw_mb-[20px] tw_font-extrabold tw_text-sky-700 tw_text-[18px]">Key Skills</h5>
								<h6 class="tw_mb-[5px] tw_text-[14px] dark:tw_text-slate-300">HTML</h6>
								<div class="progress tw_mb-[20px]" style="height: 6px;">
									<div class="progress-bar" role="progressbar" style="width: 90%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
								<h6 class="tw_mb-[5px] tw_text-[14px] dark:tw_text-slate-300">Css</h6>
								<div class="progress tw_mb-[20px]" style="height: 6px;">
									<div class="progress-bar" role="progressbar" style="width: 70%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
								<h6 class="tw_mb-[5px] tw_text-[14px] dark:tw_text-slate-300">jQuery</h6>
								<div class="progress tw_mb-[20px]" style="height: 6px;">
									<div class="progress-bar" role="progressbar" style="width: 60%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
								<h6 class="tw_mb-[5px] tw_text-[14px] dark:tw_text-slate-300">Bootstrap</h6>
								<div class="progress tw_mb-[20px]" style="height: 6px;">
									<div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
							</div>
						</div>
					</div>