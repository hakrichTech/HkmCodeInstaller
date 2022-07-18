

<div class="modal fade " id="exampleModal" aria-labelledby="exampleModalLabel" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content tw_bg-slate-100 dark:tw_bg-gray-900 dark:tw_border-[1px] dark:tw_border-gray-500">
     

      <div class="modal-body tw_border-0"> 
       <div>

          <div class="tw_text-center">
            <div class="svg-box ">
                    <svg class="circular green-stroke">
                        <circle class="path" cx="75" cy="75" r="50" fill="none" stroke-width="5" stroke-miterlimit="10"/>
                    </svg>
                    <svg class="checkmark green-stroke">
                        <g transform="matrix(0.79961,8.65821e-32,8.39584e-32,0.79961,-489.57,-205.679)">
                            <path class="checkmark__check" fill="none" d="M616.306,283.025L634.087,300.805L673.361,261.53"/>
                        </g>
                    </svg>
                </div>
            </div>
            <!-- <svg class="checkmark tw_block tw_text-center tw_mx-auto  tw_mt-8" xmlns="http://www.w3.org/2000/svg"
                width="80"
                height="80"
                viewBox="0 0 80 80">
            <circle class="circle"
                    cx="37.5"
                    cy="37.5"
                    r="37.5"
                    fill="#0c3"/>
            <path class="check"
                    d="M20 35l15 16 39-39"
                    fill="none"
                    stroke="#fff"
                    stroke-width="7"
                    stroke-linecap="round"/>
            </svg> -->

            <p class="tw_text-center tw_w-9/12 lg:tw_w-2/3 tw_mx-auto tw_my-8">Thank you for reporting the bug it's very helpfull for us</p>
            <div class="tw_flex tw_space-x-2 tw_justify-center tw_mb-8">
                <button
                    type="button"
                    data-mdb-ripple="true"
                    data-mdb-ripple-color="light"
                    class="tw_inline-block 
                    tw_px-6
                    tw_py-2.5
                    tw_bg-sky-600
                    tw_text-white
                    tw_font-medium
                    tw_text-xs
                    tw_leading-tight
                    tw_uppercase
                    tw_rounded
                    tw_shadow-md
                    hover:tw_bg-sky-700 hover:tw_shadow-lg
                    focus:tw_bg-sky-700 focus:tw_shadow-lg focus:tw_outline-none focus:tw_ring-0
                    active:tw_bg-sky-800 active:tw_shadow-lg
                    tw_transition
                    tw_duration-150
                    tw_ease-in-out"
                >Click me</button>
            </div>

            <p class="tw_text-center tw_w-9/12 lg:tw_w-2/3 tw_mx-auto tw_mb-8"><a href="#!"
                                class="tw_text-sky-600 hover:tw_text-sky-500 
                                focus:tw_text-sky-500 tw_transition tw_duration-200 tw_ease-in-out" >Submit another request</a></p>


       </div>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="accountVerifyModal" aria-labelledby="accountVerifyModalLabel" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content tw_bg-slate-100 dark:tw_bg-gray-900 dark:tw_border-[1px] dark:tw_border-gray-500">
     

      <div class="modal-body tw_border-0"> 
       <div>

            <div class="tw_text-left">
                <h2 class="tw_text-xl tw_text-slate-100 dark:tw_text-sky-500">Verify your email address!</h2>
            </div>
            

            <p class="tw_text-left tw_w-[90%] lg:tw_w-10/12 tw_mx-auto lg:tw_mx-0 tw_my-6">Use the link bellow to verify your email and get access to your account</p>
            <div class="tw_flex tw_space-x-2 tw_justify-end tw_items-end tw_mb-8">
              

                <button id="verifyingBtn" data-act ="genCode" class="btn 
                tw_px-6
                    tw_py-2.5
                    tw_bg-sky-600
                    tw_text-white
                    tw_font-medium
                    tw_text-xs
                    tw_leading-tight
                    tw_uppercase
                    tw_rounded
                    tw_shadow-md
                    dark:disabled:hover:tw_bg-sky-800
                    dark:disabled:focus:tw_bg-sky-800
                    dark:disabled:tw_bg-sky-800
                    hover:tw_bg-sky-700 hover:tw_shadow-lg
                    focus:tw_bg-sky-700 focus:tw_shadow-lg focus:tw_outline-none focus:tw_ring-0
                    active:tw_bg-sky-800 active:tw_shadow-lg
                    tw_transition
                    tw_duration-150
                    tw_ease-in-out
                    "> Verify email</button>
            </div>

            

       </div>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="accountOtpVerifyModal" aria-labelledby="accountOtpVerifyModalLabel" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content tw_bg-slate-100 dark:tw_bg-gray-900 dark:tw_border-[1px] dark:tw_border-gray-500">
     
      <div class="modal-body tw_border-0"> 
       <div>

            <div class="tw_text-left">
                <h2 class="tw_text-xl tw_text-slate-100 dark:tw_text-sky-500">Verification code!</h2>
            </div>
            

            <p class="tw_text-left tw_w-[90%] lg:tw_w-10/12 tw_mx-auto lg:tw_mx-0 tw_my-6">Please enter the verification code sent to <f id="emSet"></f></p>
            <form class="verCodeIn">
            <div class="tw_flex tw_space-x-2 tw_justify-start tw_items-start tw_my-6">
               <input maxlength="1" required autocomplete="off" id="codeInput" class="tw_w-[45px] tw_h-[45px] 
               tw_form-control
                tw_block
                tw_p-[15px]
                dark:tw_highlight-white/5 
                dark:tw_bg-slate-800 
                hover:tw_ring-slate-300
                dark:hover:tw_bg-slate-700
                dark:focus:tw_text-slate-100
                tw_text-base
                tw_font-normal
                tw_text-slate-400
                tw_bg-white tw_bg-clip-padding
                tw_border tw_border-solid tw_border-slate-300
                dark:tw_border-slate-300/10
                tw_rounded
                tw_transition
                tw_ease-in-out
                tw_mb-[20px]
                focus:tw_text-slate-700 focus:tw_bg-white focus:tw_border-cyan-600 
                dark:focus:tw_border-cyan-600 focus:tw_outline-none
               " type="text">
               <input maxlength="1" required autocomplete="off" id="codeInput" class="tw_w-[45px] tw_h-[45px]
               tw_form-control
                tw_block
                tw_p-[15px]
                dark:tw_highlight-white/5 
                dark:tw_bg-slate-800 
                hover:tw_ring-slate-300
                dark:hover:tw_bg-slate-700
                dark:focus:tw_text-slate-100
                tw_text-base
                tw_font-normal
                tw_text-slate-400
                tw_bg-white tw_bg-clip-padding
                tw_border tw_border-solid tw_border-slate-300
                dark:tw_border-slate-300/10
                tw_rounded
                tw_transition
                tw_ease-in-out
                tw_mb-[20px]
                focus:tw_text-slate-700 focus:tw_bg-white focus:tw_border-cyan-600 
                dark:focus:tw_border-cyan-600 focus:tw_outline-none
                " type="text">
               <input maxlength="1" required autocomplete="off" id="codeInput" class="tw_w-[45px] tw_h-[45px]
                tw_form-control
                tw_block
                tw_p-[15px]
                dark:tw_highlight-white/5 
                dark:tw_bg-slate-800 
                hover:tw_ring-slate-300
                dark:hover:tw_bg-slate-700
                dark:focus:tw_text-slate-100
                tw_text-base
                tw_font-normal
                tw_text-slate-400
                tw_bg-white tw_bg-clip-padding
                tw_border tw_border-solid tw_border-slate-300
                dark:tw_border-slate-300/10
                tw_rounded
                tw_transition
                tw_ease-in-out
                tw_mb-[20px]
                focus:tw_text-slate-700 focus:tw_bg-white focus:tw_border-cyan-600 
                dark:focus:tw_border-cyan-600 focus:tw_outline-none
                " type="text">
               <input maxlength="1" required autocomplete="off" id="codeInput" class="tw_w-[45px] tw_h-[45px]
                tw_form-control
                tw_block
                tw_p-[15px]
                dark:tw_highlight-white/5 
                dark:tw_bg-slate-800 
                hover:tw_ring-slate-300
                dark:hover:tw_bg-slate-700
                dark:focus:tw_text-slate-100
                tw_text-base
                tw_font-normal
                tw_text-slate-400
                tw_bg-white tw_bg-clip-padding
                tw_border tw_border-solid tw_border-slate-300
                dark:tw_border-slate-300/10
                tw_rounded
                tw_transition
                tw_ease-in-out
                tw_mb-[20px]
                focus:tw_text-slate-700 focus:tw_bg-white focus:tw_border-cyan-600 
                dark:focus:tw_border-cyan-600 focus:tw_outline-none
                " type="text">
               <input maxlength="1" required autocomplete="off" id="codeInput" class="tw_w-[45px] tw_h-[45px]
                tw_form-control
                tw_block
                tw_p-[15px]
                dark:tw_highlight-white/5 
                dark:tw_bg-slate-800 
                hover:tw_ring-slate-300
                dark:hover:tw_bg-slate-700
                dark:focus:tw_text-slate-100
                tw_text-base
                tw_font-normal
                tw_text-slate-400
                tw_bg-white tw_bg-clip-padding
                tw_border tw_border-solid tw_border-slate-300
                dark:tw_border-slate-300/10
                tw_rounded
                tw_transition
                tw_ease-in-out
                tw_mb-[20px]
                focus:tw_text-slate-700 focus:tw_bg-white focus:tw_border-cyan-600 
                dark:focus:tw_border-cyan-600 focus:tw_outline-none
                " type="text">
            </div>
            <div class="tw_flex tw_space-x-2 tw_justify-between tw_items-center tw_mb-8">
               <div class="tw_w-auto tw_text-sm">Didn't receive an OTP?<br>
               <a href="#" onclick="resend(this)" class="tw_text-xs tw_underline">Resend OTP</a></div>             

                <button type="submit" id="verifyingBtn" data-act ="sendCode" class="btn 
                tw_px-6
                    tw_py-2.5
                    tw_bg-sky-600
                    tw_text-white
                    tw_font-medium
                    tw_text-xs
                    tw_leading-tight
                    tw_uppercase
                    tw_rounded
                    tw_shadow-md
                    dark:disabled:hover:tw_bg-sky-800
                    dark:disabled:focus:tw_bg-sky-800
                    dark:disabled:tw_bg-sky-800
                    hover:tw_bg-sky-700 hover:tw_shadow-lg
                    focus:tw_bg-sky-700 focus:tw_shadow-lg focus:tw_outline-none focus:tw_ring-0
                    active:tw_bg-sky-800 active:tw_shadow-lg
                    tw_transition
                    tw_duration-150
                    tw_ease-in-out
                    "> Submit</button>
            </div>

            </form>

            

       </div>
      </div>
    </div>
  </div>
</div>
<script>
  function resend(el) {
    $(el).parent().next().prop('disabled',true);
    $(el).parent().next().html(`<i class="fa fa-spinner fa-spin"></i> Resend...`);
    $.ajax({
                url:"/ajax/login?act=verify_email",
                dataType:"json",
                type:"POST",
                data:{},
                
            }).done(function(data, textStatus, xhr){
                if(data.error){
                    console.log(data);
                }else{
                    $(el).parent().next().html(`Submit`);

                    console.log(data);
                    // location.assign(data.url);
                }
            }).fail(function(xhr, textStatus, errorThrown){
                console.log(errorThrown);
            }).always()
  }
  $('button#verifyingBtn').click(function(){
        $(this).prop('disabled', true);
        $(this).html(`<i class="fa fa-spinner fa-spin"></i> sending...`);
        
        $act = $(this).data('act');
        $btn = $(this);
        if ($act == "genCode" ) {
            $.ajax({
                url:"/ajax/login?act=verify_email",
                dataType:"json",
                type:"POST",
                data:{},
                
            }).done(function(data, textStatus, xhr){
                if(data.error){
                    console.log(data);
                }else{
                    $btn.prop('disabled', false);
                    $btn.html(`Verify email`);
                    $('#accountVerifyModal').modal('hide');
                    $('#accountOtpVerifyModal').modal('show');

                    console.log(data);
                    // location.assign(data.url);
                }
            }).fail(function(xhr, textStatus, errorThrown){
                console.log(errorThrown);
            }).always()
        }

        if ($act == "sendCode") {
          $form_inputs = $('form.verCodeIn').find(':input');
          $code = "";
          $form_inputs.each(function(){
              if($(this).attr('type')!="submit"){
                  $code += $(this).val();
              }
          });
          
          $.ajax({
                url:"/ajax/login?act=verify_email",
                dataType:"json",
                type:"POST",
                data:{
                  code:$code
                },
                
            }).done(function(data, textStatus, xhr){
                if(data.error){
                  switch (data.message) {
                    case "codeNot":
                      $btn.prop('disabled', false);
                      $btn.html(`Submit`);
                      console.log('code not match');
                      break;
                    case "none":
                       location.reload();
                      break;
                    case "notRequest":
                      location.reload();
                      break;
                    default:
                      location.reload();
                      break;
                  }
                    console.log(data);
                }else{
                    $btn.prop('disabled', false);
                    $btn.html(`Verify email`);
                    // $('#accountVerifyModal').modal('hide');
                    // $('#accountOtpVerifyModal').modal('show');

                    console.log(data);
                    location.assign(data.url);
                }
            }).fail(function(xhr, textStatus, errorThrown){
                console.log(errorThrown);
            }).always()
        }
        
  });


  $('input#codeInput').on('keyup',function(){
     
    $length = $(this).val().length;
    if ($length>0) {
      if($(this).next().is(':input')){
        $(this).next().focus();
      }
    }else{
      if($(this).prev().is(':input')){
        $(this).prev().focus();
      }
    }

      
  });


    // $('.checkmark').each(function () {
    //     var logoTimeline = anime.timeline({ autoplay: true, direction: 'alternate', loop: true });

    //     logoTimeline
    //     .add({
    //     targets: '.checkmark',
    //     scale: [
    //         { value: [0, 1], duration: 600, easing: 'easeOutQuad' }
    //     ]
    //     })
    //     .add({
    //     targets: '.check',
    //     strokeDashoffset: {
    //         value: [anime.setDashoffset, 0],
    //         duration: 700,
    //         delay: 200,
    //         easing: 'easeOutQuart'
    //     },
    //     translateX: {
    //         value: [6, 0],
    //         duration: 700,
    //         delay: 200,
    //         easing: 'easeOutQuart'
    //     },
    //     translateY: {
    //         value: [-2, 0],
    //         duration: 700,
    //         delay: 200,
    //         easing: 'easeOutQuart'
    //     },
    //     offset: 0
    //     });
    // });
    
</script>