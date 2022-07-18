<div class="filter_navbar_second   tw_absolute dark:tw_bg-slate-800  tw_rounded-tl-lg tw_rounded-tr-lg tw_w-[100%] tw_p-4 tw_z-20">
        <div class="tw_flex tw_space-x-2 tw_justify-between tw_items-center">
            <div class="">
                    <div class="tw_filter_nav">Filter:</div> 
                    <div class="tw_inline-block tw_mr-4"  @click.away="$store.headerSecondFilestoreDropdown.show = false">
                            <button  @click="$store.headerSecondFilestoreDropdown.toggle()" id="filterDButton" data-dropdown-toggle="filterD" class="tw_button_hkm tw_button_addon" type="button">
                            <span  x-text="$store.headerSecondFilestoreDropdown.current"> </span> <svg width="6" height="3" class="tw_ml-2 tw_overflow-visible" aria-hidden="true"><path d="M0 0L3 3L6 0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg></button>
                            <!-- Dropdown menu -->
                            <div id="filterD" x-data :class="$store.headerSecondFilestoreDropdown.show ?'tw_block':'tw_hidden'" class="tw_bropdown_body_hkm">
                                <ul  class="tw_py-1 tw_text-sm tw_text-gray-700 dark:tw_text-gray-200" aria-labelledby="filterDButton">
                                    <template x-for="(item, mtype) in mediaType" :key="mtype">
                                        <li  @click="$store.headerSecondFilestoreDropdown.toggle(), setDropdown1SelectedFilestore(item), $store.headerSecondFilestoreDropdown.current=mtype">
                                            <a href="#" class="tw_dropdown_elem_hkm" x-text="mtype"></a>
                                        </li>
                                    </template>
                                </ul>
                                <div class="tw_py-1">
                                <a href="#" @click="$store.headerSecondFilestoreDropdown.toggle(), setDropdown1SelectedFilestore('all'), $store.headerSecondFilestoreDropdown.current='All media items'" class="tw_dropdown_elem_hkm tw_dropdown_elem_separated_link">All media items</a>
                                </div>
                            </div>
                    </div>
                    <div class="tw_inline-block tw_mr-4" x-data @click.away="$store.headerSecondFilestoreDropdown2.show = false">
                            <button @click="$store.headerSecondFilestoreDropdown2.toggle()" id="filterDButton" data-dropdown-toggle="filterD" class="tw_button_hkm tw_button_addon" type="button">
                           <span x-text="$store.headerSecondFilestoreDropdown2.current"></span> <svg width="6" height="3" class="tw_ml-2 tw_overflow-visible" aria-hidden="true"><path d="M0 0L3 3L6 0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg></button>
                            <!-- Dropdown menu -->
                            <div id="filterD" :class="$store.headerSecondFilestoreDropdown2.show ?'tw_block':'tw_hidden'" class="tw_bropdown_body_hkm">
                                <ul x-data="headerSecondFilestoreData2" class="tw_py-1 tw_text-sm tw_text-gray-700 dark:tw_text-gray-200" aria-labelledby="filterDButton">
                                    <template x-for="(item, mtype) in dateData" :key="mtype">

                                            <li  @click="$store.headerSecondFilestoreDropdown2.toggle(), setDropdown2SelectedFilestore(item), $store.headerSecondFilestoreDropdown2.current=mtype">
                                                <a href="#" class="tw_dropdown_elem_hkm" x-text="mtype"></a>
                                            </li>
                                    </template>
                                </ul>
                                <div class="tw_py-1">
                                  <a href="#" @click="$store.headerSecondFilestoreDropdown2.toggle(),setDropdown2SelectedFilestore('all') ,$store.headerSecondFilestoreDropdown2.current='All dates'" class="tw_dropdown_elem_hkm tw_dropdown_elem_separated_link">All dates</a>
                                </div>
                            </div>
                    </div>
            </div>
            <div class="">
                <?php include __DIR__."/search.second.filestore.php"; ?>
            </div>
        </div>
    </div>