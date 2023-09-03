    <?php
    $topCategoryQuery = mysql(brilliantDirectories::getDatabaseConfiguration("database"), "SELECT * FROM `list_services` WHERE `master_id` = '0'");

    $rows = mysql_fetch_assoc($topCategoryQuery);

    $allRows = array();

    // Push all rows into the $allRows array

    do {
        array_push($allRows, $rows);
    } while ($rows = mysql_fetch_assoc($topCategoryQuery));

    $tid_values = isset($_GET['tid']) ? explode(',', $_GET['tid']) : array();

    function cats($topCategory, $parent_id, $tid_values)
    {

        if ($parent_id == 94 || $parent_id == 152) {
            $subCategoryQuery = mysql(
                brilliantDirectories::getDatabaseConfiguration("database"),
                "SELECT * FROM `list_services` WHERE `master_id` = $parent_id ORDER BY `sort_order` ASC"
            );
        } else {
            $subCategoryQuery = mysql(
                brilliantDirectories::getDatabaseConfiguration("database"),
                "SELECT * FROM `list_services` WHERE `master_id` = $parent_id ORDER BY `name` ASC"
            );
        }

        $rows = mysql_fetch_assoc($subCategoryQuery);

        $allRows = array();

        // Push all rows into the $allRows array

        do {
            if (!array_key_exists($rows['service_id'], $allRows)) {
                $allRows[$rows['service_id']] = $rows;
            }
        } while ($rows = mysql_fetch_assoc($subCategoryQuery));

        $end = '';

        $openOrClosed = false;
        $isOpen = 'fa-minus';
        $isClosed = 'fa-plus';

        foreach ($allRows as $subCategory) {

            $isChecked = in_array($subCategory['service_id'], $tid_values) ? 'checked' : '';

            if ($isChecked) {
                $openOrClosed = true;
            }

            $end .= '<div class="custom-sub-cat-checkbox-container ">
            <label>
                <input type="checkbox" name="tid[]" value="' . $subCategory['service_id'] . '" class="custom-single-checkbox-filter" ' . $isChecked . '>
                <span class="custom-checkbox-name-filter-category">
                    ' . $subCategory['name'] . '
                </span>
            </label>
        </div>';
        };

        $icon = $openOrClosed ? $isOpen : $isClosed;

        $start = '<div class="category-group closed-mode-cat-group" data-pid=' . $topCategory['service_id'] . '>
        <label class="category-view-switch-button-icon">
            <span class="custom-group-cat-title">
                ' . $topCategory['name'] . '
            </span>
            <i class="fa ' . $icon . '" aria-hidden="true"></i>
        </label>';

        $end .= '</div>';

        return $start . $end;
    }

    $priorityOneHtml = '';
    $priorityTwoHtml = '';
    $normalHtml = '';

    foreach ($allRows as $topCategory) {
        if ($topCategory['service_id'] == "167") {
            $priorityOneHtml .= cats($topCategory, $topCategory['service_id'], $tid_values);
        } elseif ($topCategory['service_id'] == "94") {
            $priorityTwoHtml .= cats($topCategory, $topCategory['service_id'], $tid_values);
        } else {
            $normalHtml .= cats($topCategory, $topCategory['service_id'], $tid_values);
        }
    }

    ?>

    <div class="module custom-sidebar-search-filters">
        <h3>Filter by Category</h3>
        <div class="search-filter-element categories-search-filters">
            <?php echo $priorityOneHtml . $priorityTwoHtml . $normalHtml; ?>
        </div>
    </div>
    <div style="display:none;">
        <?php
        addonController::showWidget('dynamic_category_filtering', 'b45b5ae1411c41afb8ccb3246727fbb1', '');
        ?>
    </div>
    <script>


    document.addEventListener('DOMContentLoaded', function() {
        var customCategoryGroups = document.querySelectorAll('.category-group');

        customCategoryGroups.forEach(function(categoryGroup) {
            var checkBoxes = categoryGroup.querySelectorAll('input[type="checkbox"]');
            var isOpen = Array.from(checkBoxes).some(function(checkBox) {
                return checkBox.checked;
            });

            if (isOpen) {
                categoryGroup.setAttribute('isOpen', 'true');
                categoryGroup.classList.remove('closed-mode-cat-group');
            } else {
                categoryGroup.setAttribute('isOpen', 'false');
                categoryGroup.classList.add('closed-mode-cat-group');
            }

            var closedModeCatGroup = categoryGroup.classList.contains('closed-mode-cat-group');
            var customSubCatCheckboxContainers = categoryGroup.querySelectorAll('.custom-sub-cat-checkbox-container');

            if (closedModeCatGroup) {
                customSubCatCheckboxContainers.forEach(function(customSubCatCheckboxContainer) {
                    customSubCatCheckboxContainer.style.display = 'none';
                });
            } else {
                customSubCatCheckboxContainers.forEach(function(customSubCatCheckboxContainer) {
                    customSubCatCheckboxContainer.style.display = 'block';
                });
            }

            categoryGroup.addEventListener('click', function() {
                var isOpen = categoryGroup.getAttribute('isOpen') === 'true';
                isOpen = !isOpen;
                categoryGroup.setAttribute('isOpen', isOpen.toString());

                var icon = categoryGroup.querySelector('.category-view-switch-button-icon i');

                console.log(icon);


                if (isOpen) {
                    categoryGroup.classList.remove('closed-mode-cat-group');
                    customSubCatCheckboxContainers.forEach(function(customSubCatCheckboxContainer) {
                        customSubCatCheckboxContainer.style.display = 'block';
                    });
                    if (icon.classList.contains('fa-plus')) {
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-minus');
                } else {
                    icon.classList.remove('fa-minus');
                    icon.classList.add('fa-plus');
                }

                } else {
                    categoryGroup.classList.add('closed-mode-cat-group');
                    customSubCatCheckboxContainers.forEach(function(customSubCatCheckboxContainer) {
                        customSubCatCheckboxContainer.style.display = 'none';
                });
                if (icon.classList.contains('fa-plus')) {
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-minus');
                } else {
                    icon.classList.remove('fa-minus');
                    icon.classList.add('fa-plus');
                }
                
                }

                console.log(icon);


            });
        });

        // Add event listener for the custom checkboxes
        var customCheckboxes = document.querySelectorAll('.custom-single-checkbox-filter');
        customCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                // Get the current URL without parameters
                var baseUrl = window.location.origin + window.location.pathname;
                var urlParams = new URLSearchParams(window.location.search);
                var tidValues = [];

                // Get the checked custom checkboxes
                customCheckboxes.forEach(function(checkedCheckbox) {
                    if (checkedCheckbox.checked) {
                        tidValues.push(checkedCheckbox.value);
                    }
                });

                // Set or delete the 'tid' parameter
                if (tidValues.length > 0) {
                    urlParams.set('tid', tidValues.join(','));
                } else {
                    urlParams.delete('tid');
                }

                // Check if "dynamic" parameter is already present
                if (!urlParams.has('dynamic')) {
                    // If "dynamic" parameter is not present, set it to 1
                    urlParams.set('dynamic', 1);
    }         
    // Update the URL without reloading the page
            var newUrl = baseUrl + '?' + urlParams.toString();
            window.location.href = newUrl;
        });
    });
    });


    </script>

    <style>
        
    .category-view-switch-button-icon {
    width: 100%;
    cursor: pointer;
    overflow: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 7px 0px 5px 4px;
    transition: 0.3s ease 0s;
    border-radius: 3px;
    margin-bottom: 5px;
    position: relative;
    }

    .category-view-switch-button-icon span {

        margin: 0!important;

    }

    .category-view-switch-button-icon:hover {
        background-color: #f5f5f5;
    }

    .custom-sub-cat-checkbox-container {
        padding: 5px;
    } 

    .custom-single-checkbox-filter {
        margin-right: 5px !important;
    }



    </style>