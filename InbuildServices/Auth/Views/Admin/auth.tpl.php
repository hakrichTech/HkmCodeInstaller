<script src='/assets/js/validation.js'></script>
<?php
global $engine;
?>
<?php include __DIR__."/modals.tpl.php"; ?>

<section class="tw_flex tw_justify-center" style="height: 100vh;">
        <div class="tw_card tw_w-11/12 md:tw_w-9/12 lg:tw_w-[30%] tw_my-auto tw_mx-auto" style="padding: 1.25rem">
            <div class="tw_card-header tw_justify-between tw_border-b dark:tw_border-slate-300/10 tw_py-3 tw_mb-3">
                <h1 class="tw_text-3xl"><?=ucfirst($engine);?>Admin<span class="tw_text-sky-500">.</span></h1>
              
                <a href="../index.html">
                    <i class="fa-solid fa-house tw_transition 
                    tw_duration-150 tw_ease-in-out tw_ease-in-out hover:tw_text-sky-700 
                    
                    hover:tw_shadow-lg 
                    focus:tw_text-sky-700 tw_leading-normal tw_shadow-md "></i>
               </a>

            </div>
            <div class="tw_card-body">
                
                
                <div class="tw_block tw_py-6 tw_rounded-lg  tw_max-w-sm tw_mx-auto">
                    <form class="adminAuth">
                      <div class="tw_form-group tw_mb-6">
                        <input type="email" required  class="tw_form-control
                          tw_block
                          tw_w-full
                          tw_px-3
                          dark:tw_highlight-white/5 
                          dark:tw_bg-slate-800 
                          hover:tw_ring-slate-300
                          dark:hover:tw_bg-slate-700
                          dark:focus:tw_text-slate-100
                          tw_py-1.5
                          tw_text-base
                          tw_font-normal
                          tw_text-slate-400
                          tw_bg-white tw_bg-clip-padding
                          tw_border tw_border-solid tw_border-slate-300
                          dark:tw_border-slate-300/10
                          tw_rounded
                          tw_transition
                          tw_ease-in-out
                          tw_m-0
                          focus:tw_text-slate-700 focus:tw_bg-white focus:tw_border-cyan-600 
                          dark:focus:tw_border-cyan-600 focus:tw_outline-none" id="exampleInputEmail2" name = "username"
                          aria-describedby="emailHelp" placeholder="Enter username or email">
                      </div>
                      <div class="tw_form-group tw_mb-6">

                        <input type="password" required class="tw_form-control tw_block
                    
                        tw_w-full
                        tw_px-3
                        dark:tw_highlight-white/5 
                        dark:tw_bg-slate-800 
                        hover:tw_ring-slate-300
                        dark:hover:tw_bg-slate-700
                        dark:focus:tw_text-slate-100
                        tw_py-1.5
                        tw_text-base
                        tw_font-normal
                        tw_text-slate-400
                        tw_bg-white tw_bg-clip-padding
                        tw_border tw_border-solid tw_border-slate-300
                        dark:tw_border-slate-300/10
                        tw_rounded
                        tw_transition
                        tw_ease-in-out
                        tw_m-0
                        focus:tw_text-slate-700 focus:tw_bg-white focus:tw_border-cyan-600 
                        dark:focus:tw_border-cyan-600 focus:tw_outline-none" id="exampleInputPassword2"
                         name="password" placeholder="Password">
                      </div>
                      <div class="tw_flex tw_justify-between tw_items-center tw_mb-6">
                        <div class="tw_form-group tw_form-check">
                          <input type="checkbox" name="remember"
                            class="tw_form-check-input tw_appearance-none tw_h-4 tw_w-4 tw_border tw_border-gray-300 tw_rounded-sm tw_bg-white checked:tw_text-white checked:tw_bg-sky-600 checked:tw_border-sky-600 focus:tw_outline-none tw_transition tw_duration-200 tw_mt-1 tw_align-top tw_bg-no-repeat tw_bg-center tw_bg-contain tw_float-left tw_mr-2 tw_cursor-pointer"
                            id="exampleCheck2">
                          <label class="tw_form-check-label tw_inline-block tw_text-gray-800 dark:tw_text-slate-100" for="exampleCheck2">Remember me</label>
                        </div>
                        <a href="#!"
                          class="tw_text-sky-500 hover:tw_text-sky-500 focus:tw_text-sky-500 tw_transition tw_duration-200 tw_ease-in-out">Forgot
                          password?</a>
                      </div>
                      <input type="submit" class="
                      tw_w-full
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
                        tw_ease-in-out" value="Sign in">
                      <p class="tw_text-gray-800 tw_mt-6 dark:tw_text-slate-100 tw_text-center">Not a member? <a href="#!"
                          class="tw_text-sky-600 hover:tw_text-sky-500 focus:tw_text-sky-500 tw_transition tw_duration-200 tw_ease-in-out">Register</a>
                      </p>
                    </form>
                  </div>


            </div>
        </div>
    </section>

<script src="/assets/js/script.js"></script>

<script>

    $('form.verCodeIn').validin({
                    feedback_delay: 500,
                    custom_tests: {
                        'state_abbreviation': {
                            'regex': /[A-Z]{2}/,
                            'error_message': "States are abbreviated with two capital letters"
                        }
                    },
                    error_message_class: "is-danger",
                    onValidateInput: function(validation_info) {
                    }
    });

    $('form.verCodeIn').submit(function(e){
          e.preventDefault();
          e.stopPropagation();
    });
  
  $('form.adminAuth').validin({
                    feedback_delay: 500,
                    custom_tests: {
                        'state_abbreviation': {
                            'regex': /[A-Z]{2}/,
                            'error_message': "States are abbreviated with two capital letters"
                        }
                    },
                    error_message_class: "is-danger",
                    onValidateInput: function(validation_info) {
                    }
    });

    $('form.adminAuth').submit(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $form_inputs = $(this).find(':input');
                    $formData = {}
                    $form_inputs.each(function(){
                       if($(this).attr('type')!="submit"){
                          if($(this).attr('type') == "checkbox"){
                            $c = $(this).is(':checked');
                           $formData[$(this).attr('name')] = $c?"on":"off";
                          }else{
                            $formData[$(this).attr('name')] = $(this).val();

                          }

                       }
                    });
                   
                    $.ajax({
                        url:"/ajax/login?act=authentification",
                        dataType:"json",
                        type:"POST",
                        data:$formData,
                        
                    }).done(function(data, textStatus, xhr){
                        if(data.error){
                          if (data.message == "verif") {
                            $('#emSet').html(data.email);
                            $('#accountVerifyModal').modal('show');
                          }else{
                            alert('Username and password incorrect! try again');
                          }
                            
                        }else{
                            location.assign('/dashboard');
                        }
                    }).fail(function(xhr, textStatus, errorThrown){
                        console.log(errorThrown);
                    }).always()
                   


                });
</script>