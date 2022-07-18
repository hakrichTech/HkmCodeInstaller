<li class="weight-500 col-md-6">
    <h4 class="text-blue h5 mb-20">Edit Your Address & Contact Setting</h4>
    <div class="form-group">
        <label>Country</label>
        <select class="selectpicker tw_block tw_form-control my_input" name="country" data-style="btn-outline-secondary btn-lg" title="Not Chosen">
            <option value="USA">United States</option>
            <option value="India">India</option>
            <option value="United Kingdom" >United Kingdom</option>
        </select>
    </div>
    <div class="form-group">
        <label>State/Province/Region</label>
        <input autocomplete="off" type="text" name="state"  value = "<?=$state?>" class="tw_form-control my_input" >
    </div>
    <div class="form-group">
        <label>Postal Code</label>
        <input autocomplete="off" type="text" name="postalCode" value="<?=$postalcode?>" class="tw_form-control my_input" >
    </div>
    <div class="form-group">
        <label>Phone Number</label>
        <input autocomplete="off" type="text" name="phone" value="<?=$phone?>"  class="tw_form-control my_input" >
    </div>
    <div class="form-group">
        <label>Address</label>
        <textarea  autocomplete="off" name="address" value = "<?=$address?>" class="ftw_orm-control my_input"><?=$address?></textarea>
    </div>
</li>