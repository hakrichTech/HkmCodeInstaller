
<li class="weight-500 col-md-6">
	<h4 class="text-blue h5 mb-20">Edit Your Personal Setting</h4>
    <div class="form-group">
        <label class="tw_form-label tw_inline-block tw_mb-2 tw_font-extrabold tw_text-gray-700 dark:tw_text-slate-100">Full Name</label>
        <input autocomplete="off" type="text" name="fullname" value="<?=$fullname?>"  class="tw_form-control my_input" >
    </div>
    <div class="form-group">
        <label>username</label>
        <input autocomplete="off" type="text"  name="username" disabled class="tw_form-control my_input" value="<?=$username?>" >
    </div>
    <div class="form-group">
        <label>Email</label>
        <input autocomplete="off" type="email"  name="email" class="tw_form-control my_input" value="<?=$email?>" >
    </div>
    <div class="form-group">
        <label>Date of birth</label>
        <input autocomplete="off" type="text" name="birthDate" value="<?=$birthDate?>" class="tw_form-control my_input date-picker" >
    </div>
    <div class="form-group">
        <label>Gender</label>
        <div class="d-flex">
        <?php
           if ($gender == "female") {
               echo '<div class="checkBox"> <input autocomplete="off" type="checkbox" name="gender" data-type="female" data-value="male" id="r3" class="gender_info_profile male_gender_input"> <label class="custom-control-label weight-400" for="r3">Male</label> </div> <div class="checkBox"> <input autocomplete="off" type="checkbox" name="gender" data-type="male" checked data-value="female" id="r4" class="gender_info_profile female_gender_input"> <label class="custom-control-label weight-400" for="r4">Female</label> </div>';
           }elseif ($gender == "male") {
            echo '<div class="checkBox"> <input autocomplete="off" type="checkbox" checked name="gender" data-type="female" data-value="male" id="r3" class="gender_info_profile male_gender_input"> <label class="custom-control-label weight-400" for="r3">Male</label> </div> <div class="checkBox"> <input autocomplete="off" type="checkbox" name="gender" data-type="male" data-value="female" id="r4" class="gender_info_profile female_gender_input"> <label class="custom-control-label weight-400" for="r4">Female</label> </div>';
           }else{
            echo '<div class="checkBox"> <input autocomplete="off" type="checkbox" name="gender" data-type="female" data-value="male" id="r3" class="gender_info_profile male_gender_input"> <label class="custom-control-label weight-400" for="r3">Male</label> </div> <div class="checkBox"> <input autocomplete="off" type="checkbox" name="gender" data-type="male" data-value="female" id="r4" class="gender_info_profile female_gender_input"> <label class="custom-control-label weight-400" for="r4">Female</label> </div>';

           }
        ?>
        </div>
    </div>

</li>

<script>

    $('.gender_info_profile').change(function(){

        $type = $(this).data('type');
        if ($(this).prop('checked')) {
            $('.'+$type+'_gender_input').prop('checked',false);
        }

    });
</script>
