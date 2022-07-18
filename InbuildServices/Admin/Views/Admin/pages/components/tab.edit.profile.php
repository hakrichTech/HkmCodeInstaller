<div class="tab-pane fade height-100-p" id="setting" role="tabpanel">
    <div class="profile-setting">
    <form class="edit_info_profile">                                                

            <ul class="profile-edit-list row">
            <?php
                include_once __DIR__."/edit.info.profile.php";
                include_once __DIR__."/edit.address.profile.php";
                include_once __DIR__."/edit.social.profile.php";
            ?>
            </ul>
        </form>
    </div>
</div>

<script>
    $('form.edit_info_profile').submit(function(e){
       $agree = $(this).find('#customCheck1-1');

        $form_inputs = $(this).find(':input');
        $formData = {}
        $form_inputs.each(function(){
            if($(this).attr('type')!="submit"){
                if($(this).attr('type') == "checkbox"){
                $c = $(this).is(':checked');
                if ($(this).attr('name') == "gender") {
                  $value = $(this).data('value');
                  if ($c) $formData[$(this).attr('name')] = $value;                  
                    
                }else{
                   $formData[$(this).attr('name')] = $c?"on":"off";
                }
                }else{
                $formData[$(this).attr('name')] = $(this).val();

                }

            }
        });

       if($agree.prop('checked')){
           
        
            $.ajax({
                url:'/ajax/auth/update',
                dataType:"json",
                type:"POST",
                data:$formData,
            }).done(function(data,status,xhr){
                console.log(data);
            }).fail(function(x,y,z){
             console.log(x);
           });
       }else{
           console.log("no");
       }
       e.preventDefault();
       return false;
    })
</script>