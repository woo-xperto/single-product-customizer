function opensppcfw(evt, cityName) {
  if (evt && evt.preventDefault) {
    evt.preventDefault();
  }

  var i, tabcontent, menuItems;

  // Hide all tab content
  tabcontent = document.getElementsByClassName("tabcontent-sppcfw");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].classList.remove("active");
  }

  // Remove active-item class from all sidebar menu items
  menuItems = document.querySelectorAll(".sppcfw_sidebar_menu .menu-item");
  for (i = 0; i < menuItems.length; i++) {
    menuItems[i].classList.remove("active-item");
  }

  // Show the current tab content
  var targetContent = document.getElementById(cityName);
  if (targetContent) {
    targetContent.classList.add("active");
  }

  // Set active class on active sidebar item
  var activeMenuItem = document.querySelector('.sppcfw_sidebar_menu .menu-item[data-tab="' + cityName + '"]');
  if (activeMenuItem) {
    activeMenuItem.classList.add("active-item");
  }
}

document.addEventListener("DOMContentLoaded", function () {
  var sidebar = document.querySelector(".sppcfw_panel_sidebar");
  if (sidebar) {
    var collapseBtn = sidebar.querySelector(".collapse-item .item-link");

    // Restore sidebar collapsed state
    if (localStorage.getItem("sppcfw_sidebarCollapsed") === "true") {
      sidebar.classList.add("collapsed");
      if (collapseBtn) {
        var icon = collapseBtn.querySelector(".dashicons");
        if (icon) icon.style.transform = "rotate(180deg)";
      }
    }

    if (collapseBtn) {
      collapseBtn.addEventListener("click", function (e) {
        e.preventDefault();
        sidebar.classList.toggle("collapsed");
        var isCollapsed = sidebar.classList.contains("collapsed");
        localStorage.setItem("sppcfw_sidebarCollapsed", isCollapsed ? "true" : "false");
        var icon = collapseBtn.querySelector(".dashicons");
        if (icon) {
          icon.style.transform = isCollapsed ? "rotate(180deg)" : "rotate(0deg)";
        }
      });
    }

    // Handle submenu toggle if present
    var menuItems = sidebar.querySelectorAll(".menu-item");
    menuItems.forEach(function (menuItem) {
      var submenu = menuItem.querySelector(".sppcfw_panel_submenu");
      if (submenu) {
        var menuLink = menuItem.querySelector(".item-link");
        if (menuLink) {
          menuLink.addEventListener("click", function (e) {
            e.preventDefault();
            submenu.classList.toggle("open");
          });
        }
      }
    });
  }
});

// ============ AJAX Form Submission Handlers ============
jQuery(document).ready(function(){


    // Quick Checkout AJAX Handler
    jQuery("#sppcfw_quick_checkout form").on("submit", function (e) {
        e.preventDefault();
        sppcfw_submit_quick_checkout_ajax(jQuery(this));
    });

    // Toggle Quick Checkout template selector visibility
    jQuery('#sppcfw_enable_quick_checkout').on('change', function() {
    jQuery('.sppcfw_quick_checkout_template_row').slideToggle();
    jQuery('.sppcfw_product_options_row').slideToggle();
});


    // Preview picker: clicking an image updates selected template input, label, and right-side preview.
    jQuery(document).on('click', '#sppcfw_quick_checkout .sppcfw-template-preview-picker .sppcfw-template-item', function() {
        var templateVal = jQuery(this).attr('data-template');
        var selectedTemplateImg = jQuery(this).find('.sppcfw-template-thumb img');
        var previewImg = jQuery('#sppcfw-selected-template-preview-img');
        var titleText = jQuery(this).find('.sppcfw-template-title').text();

        jQuery(this)
            .closest('.sppcfw-template-preview-picker')
            .find('.sppcfw-template-item')
            .removeClass('is-active');
        jQuery(this).addClass('is-active');

        if (templateVal) {
            jQuery('#sppcfw_enable_qc').val(templateVal);
        }

        if (titleText) {
            jQuery('.sppcfw-fixed-template span').text(titleText);
        }

        if (selectedTemplateImg.length && previewImg.length) {
            previewImg.attr('src', selectedTemplateImg.attr('src'));
            previewImg.attr('alt', selectedTemplateImg.attr('alt'));
        }
    });

    // Keyboard accessibility for the preview picker.
    jQuery(document).on('keydown', '#sppcfw_quick_checkout .sppcfw-template-preview-picker .sppcfw-template-item', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            jQuery(this).trigger('click');
        }
    });
});


// Submit Quick Checkout form via AJAX
function sppcfw_submit_quick_checkout_ajax(form) {
    var submitBtn = form.find('button[type="submit"], input[type="submit"]').first();
    var originalText = submitBtn.is('input') ? submitBtn.val() : submitBtn.text();

    submitBtn.prop('disabled', true);
    if (submitBtn.is('input')) {
        submitBtn.val('Saving...');
    } else {
        submitBtn.text('Saving...');
    }
    
    jQuery.ajax({
        url: sppcfw_settings.ajaxurl,
        type: 'POST',
        data: form.serialize() + '&action=sppcfw_save_quick_checkout',
        success: function(response) {
            // admin-ajax.php can return plain "0" (string) when action handler isn't registered.
            // Also guard against unexpected response shapes.
            var isValidObject = response && typeof response === 'object';
            var isSuccess = isValidObject && response.success === true;
            var message =
                (isValidObject && response.data && response.data.message)
                    ? response.data.message
                    : 'An error occurred. Please try again.';

            if (isSuccess) {
                if (isValidObject && response.data && response.data.is_pro_template) {
                    sppcfw_show_notice('This is a Pro Template', 'danger', form);
                    // Reset template selection back to template-1 in UI since template-1 remains saved
                    jQuery('#sppcfw_enable_qc').val('template-1');
                    jQuery('.sppcfw-template-item').removeClass('is-active');
                    jQuery('.sppcfw-template-item[data-template="template-1"]').addClass('is-active');
                    var t1Title = jQuery('.sppcfw-template-item[data-template="template-1"] .sppcfw-template-title').text();
                    if (t1Title) {
                        jQuery('.sppcfw-fixed-template span').text(t1Title);
                    }
                    var t1Img = jQuery('.sppcfw-template-item[data-template="template-1"] .sppcfw-template-thumb img');
                    if (t1Img.length && jQuery('#sppcfw-selected-template-preview-img').length) {
                        jQuery('#sppcfw-selected-template-preview-img').attr('src', t1Img.attr('src'));
                    }
                } else {
                    sppcfw_show_notice(message, 'success', form);
                }
            } else {
                sppcfw_show_notice(message, 'error', form);
            }

            submitBtn.prop('disabled', false);
            if (submitBtn.is('input')) {
                submitBtn.val(originalText);
            } else {
                submitBtn.text(originalText);
            }
        },
        error: function() {
            sppcfw_show_notice('An error occurred. Please try again.', 'error', form);
            submitBtn.prop('disabled', false).text(originalText);
        }
    });
}

// Display notice message
function sppcfw_show_notice(message, type, form) {
    var noticeClass = type === 'success' ? 'notice-success' : (type === 'danger' ? 'notice-danger notice-error' : 'notice-error');
    var noticeHtml = '<div class="notice ' + noticeClass + ' is-dismissible" style="margin: 15px 0;"><p>' + message + '</p><button type="button" class="notice-dismiss"></button></div>';
    
    form.find('.notice').remove();
    form.before(noticeHtml);
    
    if (type === 'success') {
        setTimeout(function() {
            form.prev('.notice').fadeOut(300);
        }, 2000);
    }
}
// ============ End AJAX Handlers ============

jQuery(document).ready(function ($) {
  $('select[name="sppcfw_advanced[custom_message_display_hook]"]').val(
    sppcfw_settings.custom_message_display_hook_dashboard
  );
});

jQuery(document).ready(function ($) {
  $('select[name="sppcfw_advanced[variation_table_display_hook]"]').val(
    sppcfw_settings.variation_table_display_hook_dashboard
  );
});

// document.addEventListener('DOMContentLoaded', function () {
//     var img = document.createElement('img');
//     img.src = sppcfw_settings.logoUrl;
//     img.alt = "Site Logo";
//     img.style.width = "100%";

//     // Append it wherever you want, e.g., inside a div with ID "logo-container"
//     if (document.getElementById('logo-container-spc')){
//         document.getElementById('logo-container-spc').appendChild(img);
//     }
// });

jQuery(document).ready(function () {
  // jQuery("#sppcfw_advanced_license form").on("submit", function (e) {
  //   e.preventDefault();
  //   alert(jQuery(this).serialize());
  //   return false;
  // });

  // Even better - create a mapping for cleaner code
  const checkboxHandlers = {
    enable_min_max_qty: sppcfw_min_max_enable_disable,
    enable_custom_message: sppcfw_custom_message_enable_disable,
    enable_varition_table: sppcfw_variation_table_enable_disable,
    enable_change_tab_default_label: sppcfw_tab_label_enable_disable,
  };

  // Function to check if checkbox ID matches any pattern
  function handleCheckbox(checkbox) {
    const id = jQuery(checkbox).attr("id");

    for (const [key, handler] of Object.entries(checkboxHandlers)) {
      if (id.includes(key)) {
        handler(checkbox);
        break;
      }
    }
  }

  // Apply to all checkboxes on load and on change
  jQuery("input.checkbox")
    .each(function () {
      handleCheckbox(this);
    })
    .on("change", function () {
      handleCheckbox(this);
    });
});

/**
 * Tabbable JavaScript codes & Initiate Color Picker
 *
 * This code uses localstorage for displaying active tabs
 */
jQuery(document).ready(function ($) {
  //Initiate Color Picker.
  $(".sppcfw-color-picker").iris();

  // Switches option sections
  $(".sppcfw-group").hide();
  var activetab = "";
  /*if ( 'undefined' != typeof localStorage ) {
        activetab = localStorage.getItem( 'activetab' );
    }
	console.log(activetab);
    if ( '' != activetab){
        if($( activetab ).length){
            $( activetab ).fadeIn();
        }
    } else {
        $( '.sppcfw-group:first' ).fadeIn();
    }*/
  $(".sppcfw-group .collapsed").each(function () {
    $(this)
      .find("input:checked")
      .parent()
      .parent()
      .parent()
      .nextAll()
      .each(function () {
        if ($(this).hasClass("last")) {
          $(this).removeClass("hidden");
          return false;
        }
        $(this).filter(".hidden").removeClass("hidden");
      });
  });

  /*if( '' != activetab){
        if($( activetab + '-tab' ).length){
            $( activetab + '-tab' ).addClass( 'nav-tab-active' );
        }
    }else {
        $( '.nav-tab-wrapper a:first' ).addClass( 'nav-tab-active' );
    }
    $( '.nav-tab-wrapper a' ).click( function( evt ) {
        $( '.nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );
        $( this )
            .addClass( 'nav-tab-active' )
            .blur();
        var clicked_group = $( this ).attr( 'href' );
        if ( 'undefined' != typeof localStorage ) {
            localStorage.setItem( 'activetab', $( this ).attr( 'href' ) );
        }
        $( '.group' ).hide();
        $( clicked_group ).fadeIn();
        evt.preventDefault();
    });*/

  $(".wpsa-browse").on("click", function (event) {
    event.preventDefault();

    var self = $(this);

    // Create the media frame.
    var file_frame = (wp.media.frames.file_frame = wp.media({
      title: self.data("uploader_title"),
      button: {
        text: self.data("uploader_button_text"),
      },
      multiple: false,
    }));

    file_frame.on("select", function () {
      attachment = file_frame.state().get("selection").first().toJSON();

      self.prev(".wpsa-url").val(attachment.url).change();
    });

    // Finally, open the modal
    file_frame.open();
  });

  $("input.wpsa-url")
    .on("change keyup paste input", function () {
      var self = $(this);
      self
        .next()
        .parent()
        .children(".wpsa-image-preview")
        .children("img")
        .attr("src", self.val());
    })
    .change();
});
// WP_OSA ended.

// min max options hide show
function sppcfw_min_max_enable_disable(data) {
  if (jQuery(data).is(":checked")) {
    jQuery(".sppcfw_min_max").removeClass("sppcfw_admin_hidden");
  } else {
    jQuery(".sppcfw_min_max").addClass("sppcfw_admin_hidden");
  }
}

// custom message options hide show

function sppcfw_custom_message_enable_disable(data) {
  if (jQuery(data).is(":checked")) {
    jQuery(".sppcfw_custom_message").removeClass("sppcfw_admin_hidden");
  } else {
    jQuery(".sppcfw_custom_message").addClass("sppcfw_admin_hidden");
  }
}

// variation table options hide show

function sppcfw_variation_table_enable_disable(data) {
  if (jQuery(data).is(":checked")) {
    jQuery(".sppcfw_variation_table").removeClass("sppcfw_admin_hidden");
  } else {
    jQuery(".sppcfw_variation_table").addClass("sppcfw_admin_hidden");
  }
}

// tab label options hide show

function sppcfw_tab_label_enable_disable(data) {
  if (jQuery(data).is(":checked")) {
    jQuery(".sppcfw_tab_label").removeClass("sppcfw_admin_hidden");
  } else {
    jQuery(".sppcfw_tab_label").addClass("sppcfw_admin_hidden");
  }
}

function openSPPCFWTab(evt, Tab, wrapper) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = jQuery("#" + wrapper + " .tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = jQuery("#" + wrapper + " .tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(Tab).style.display = "block";
  evt.currentTarget.className += " active";
}

function sppcfwCustomTab(evt, Tab) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = jQuery("#sppcfw_content_area2").find("div.tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = jQuery("#sppcfw_content_area2").find("button.tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(Tab).style.display = "block";

  evt.currentTarget.className += " active";
}

function sppcfwCustomContent(evt, Tab) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = jQuery("#sppcfw_product_category_additonal_content").find(
    "div.tabcontent2"
  );
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = jQuery("#sppcfw_product_category_additonal_content").find(
    "button.tablinks2"
  );
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(Tab).style.display = "block";

  evt.currentTarget.className += " active";
}

function sppcfwCustomContentProduct(evt, Tab) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = jQuery("#sppcfw_aditional_content_area").find("div.tabcontent2");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = jQuery("#sppcfw_aditional_content_area").find("button.tablinks2");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(Tab).style.display = "block";

  evt.currentTarget.className += " active";
}

function sppcfw_vertical_tabs(evt, Tab, wrapper_id) {
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = jQuery("#" + wrapper_id + "").find("div.tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = jQuery("#" + wrapper_id + "").find("button.tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(Tab).style.display = "block";

  evt.currentTarget.className += " active";
}

jQuery(document).ajaxSuccess(function (e, request, settings) {
  var object = deparam(settings.data);
  if (object.action === "add-tag") {
    if (object.taxonomy === "product_cat") {
      let sppcfw_basic = sppcfw_settings.sppcfw_basic;
      jQuery("#sppcfw_cat_out_of_stock_text").val(
        sppcfw_basic.out_of_stock_text
      );
      jQuery("#sppcfw_cat_sale_badge_text").val(sppcfw_basic.sale_badge_text);
      jQuery("#sppcfw_cat_add_to_cart_button_text").val(
        sppcfw_basic.add_to_cart_button_text
      );

      jQuery("#sppcfw_cat_out_of_stock_text").val(
        sppcfw_basic.out_of_stock_text
      );
      jQuery("#sppcfw_cat_out_of_stock_text").val(
        sppcfw_basic.out_of_stock_text
      );
      jQuery("#sppcfw_cat_out_of_stock_text").val(
        sppcfw_basic.out_of_stock_text
      );

      let sppcfw_advanced = sppcfw_settings.sppcfw_advanced;
      jQuery("#sppcfw_cat_min_max_qty_global_min_value").val(
        sppcfw_advanced.min_max_qty_global_min_value
      );
      jQuery("#sppcfw_cat_min_qty_validation_text").val(
        sppcfw_advanced.min_qty_validation_text
      );
      jQuery("#sppcfw_cat_min_max_qty_global_max_value").val(
        sppcfw_advanced.min_max_qty_global_max_value
      );
      jQuery("#sppcfw_cat_max_qty_validation_text").val(
        sppcfw_advanced.max_qty_validation_text
      );

      jQuery("#sppcfw_cat_plus_minus_button_qty_change_global_setp").val(
        sppcfw_advanced.plus_minus_button_qty_change_global_setp
      );
      jQuery("#sppcfw_cat_custom_message_text").val(
        sppcfw_advanced.custom_message_text
      );
      jQuery("#sppcfw_cat_description_tab_label").val(
        sppcfw_advanced.description_tab_label
      );
      jQuery("#sppcfw_cat_additional_information_tab_label").val(
        sppcfw_advanced.additional_information_tab_label
      );
      jQuery("#sppcfw_cat_review_tab_label").val(
        sppcfw_advanced.review_tab_label
      );
      jQuery("#sppcfw_cat_related_products_title").val(
        sppcfw_advanced.related_products_title
      );
      jQuery("#sppcfw_cat_upsell_products_title").val(
        sppcfw_advanced.upsell_products_title
      );
      jQuery("#sppcfw_cat_change_clear_text").val(
        sppcfw_advanced.change_clear_text
      );
      jQuery("#sppcfw_cat_change_backorder_text").val(
        sppcfw_advanced.change_backorder_text
      );
    }
  }
});

jQuery(document).ready(function () {
  jQuery(
    "#sppcfw_import_settings_from_category,#sppcfw_import_global_settings"
  ).on("click", function () {
    let action = jQuery(this).attr("id");
    let category_id = 0;
    let category_name = "";
    if (action === "sppcfw_import_settings_from_category") {
      jQuery('input[name^="tax_input[product_cat]"]:checked').each(function () {
        category_id = jQuery(this).val();
        category_name = jQuery(this).parent().text();
        return false;
      });

      data = { action: action, category_id: category_id };
    } else {
      category_id = 1;
      data = { action: action, category_id: category_id };
    }

    if (category_id > 0) {
      let element = jQuery("#sppcfw_add_product_meta_box_id");
      element.block({
        message: "Please wait... it's importing settings",
        overlayCSS: { background: "#fff", opacity: 0.6 },
      });
      jQuery.post(sppcfw_settings.ajaxurl, data, function (obj) {
        if (obj.hasOwnProperty("enable_plus_minus_button")) {
          console.log(obj.hasOwnProperty("enable_plus_minus_button"));
          if (obj.enable_plus_minus_button === "on") {
            jQuery("#sppcfw_product_enable_plus_minus_button").prop(
              "checked",
              true
            );
          } else {
            jQuery("#sppcfw_product_enable_plus_minus_button").prop(
              "checked",
              false
            );
          }
        } else {
          jQuery("#sppcfw_product_enable_plus_minus_button").prop(
            "checked",
            false
          );
        }

        if (obj.hasOwnProperty("out_of_stock_text")) {
          jQuery("#sppcfw_product_out_of_stock_text").val(
            obj.out_of_stock_text
          );
        } else {
          jQuery("#sppcfw_product_out_of_stock_text").val("");
        }

        if (obj.hasOwnProperty("sale_badge_text")) {
          jQuery("#sppcfw_product_sale_badge_text").val(obj.sale_badge_text);
        } else {
          jQuery("#sppcfw_product_sale_badge_text").val("");
        }

        if (obj.hasOwnProperty("sale_badge_text")) {
          jQuery("#sppcfw_product_add_to_cart_button_text").val(
            obj.add_to_cart_button_text
          );
        } else {
          jQuery("#sppcfw_product_add_to_cart_button_text").val("");
        }

        if (obj.hasOwnProperty("remove_product_meta")) {
          if (obj.remove_product_meta === "on") {
            jQuery("#sppcfw_product_remove_product_meta").prop("checked", true);
          } else {
            jQuery("#sppcfw_product_remove_product_meta").prop(
              "checked",
              false
            );
          }
        } else {
          jQuery("#sppcfw_product_remove_product_meta").prop("checked", false);
        }

        if (obj.hasOwnProperty("remove_related_product_section")) {
          if (obj.remove_related_product_section === "on") {
            jQuery("#sppcfw_product_remove_related_product_section").prop(
              "checked",
              true
            );
          } else {
            jQuery("#sppcfw_product_remove_related_product_section").prop(
              "checked",
              false
            );
          }
        } else {
          jQuery("#sppcfw_product_remove_related_product_section").prop(
            "checked",
            false
          );
        }

        if (obj.hasOwnProperty("remove_product_rating")) {
          if (obj.remove_product_rating === "on") {
            jQuery("#sppcfw_product_remove_product_rating").prop(
              "checked",
              true
            );
          } else {
            jQuery("#sppcfw_product_remove_product_rating").prop(
              "checked",
              false
            );
          }
        } else {
          jQuery("#sppcfw_product_remove_product_rating").prop(
            "checked",
            false
          );
        }

        if (obj.hasOwnProperty("hide_product_price")) {
          if (obj.hide_product_price === "on") {
            jQuery("#sppcfw_product_hide_product_price").prop("checked", true);
          } else {
            jQuery("#sppcfw_product_hide_product_price").prop("checked", false);
          }
        } else {
          jQuery("#sppcfw_product_hide_product_price").prop("checked", false);
        }

        if (obj.hasOwnProperty("hide_add_to_cart_button")) {
          if (obj.hide_add_to_cart_button === "on") {
            jQuery("#sppcfw_product_hide_add_to_cart_button").prop(
              "checked",
              true
            );
          } else {
            jQuery("#sppcfw_product_hide_add_to_cart_button").prop(
              "checked",
              false
            );
          }
        } else {
          jQuery("#sppcfw_product_hide_add_to_cart_button").prop(
            "checked",
            false
          );
        }

        if (obj.hasOwnProperty("hide_short_description")) {
          if (obj.hide_short_description === "on") {
            jQuery("#sppcfw_product_hide_short_description").prop(
              "checked",
              true
            );
          } else {
            jQuery("#sppcfw_product_hide_short_description").prop(
              "checked",
              false
            );
          }
        } else {
          jQuery("#sppcfw_product_hide_short_description").prop(
            "checked",
            false
          );
        }

        // advanced settings
        if (obj.hasOwnProperty("enable_ajax_add_to_cart")) {
          if (obj.enable_ajax_add_to_cart === "on") {
            jQuery("#sppcfw_product\\[enable_ajax_add_to_cart\\]").prop(
              "checked",
              true
            );
          } else {
            jQuery("#sppcfw_product\\[enable_ajax_add_to_cart\\]").prop(
              "checked",
              false
            );
          }
        } else {
          jQuery("#sppcfw_product\\[enable_ajax_add_to_cart\\]").prop(
            "checked",
            false
          );
        }

        if (obj.hasOwnProperty("enable_min_max_qty")) {
          if (obj.enable_min_max_qty === "on") {
            jQuery("#sppcfw_product\\[enable_min_max_qty\\]")
              .prop("checked", true)
              .trigger("change");
            jQuery("#sppcfw_product_min_max_qty_global_min_value").val(
              obj.min_max_qty_global_min_value
            );
            jQuery("#sppcfw_product_min_qty_validation_text").val(
              obj.min_qty_validation_text
            );
            jQuery("#sppcfw_product_min_max_qty_global_max_value").val(
              obj.min_max_qty_global_max_value
            );
            jQuery("#sppcfw_product_max_qty_validation_text").val(
              obj.max_qty_validation_text
            );
            jQuery(
              "#sppcfw_product_plus_minus_button_qty_change_global_setp"
            ).val(obj.plus_minus_button_qty_change_global_setp);
          } else {
            jQuery("#sppcfw_product\\[enable_min_max_qty\\]")
              .prop("checked", false)
              .trigger("change");
            jQuery("#sppcfw_product_min_max_qty_global_min_value").val("");
            jQuery("#sppcfw_product_min_qty_validation_text").val("");
            jQuery("#sppcfw_product_min_max_qty_global_max_value").val("");
            jQuery("#sppcfw_product_max_qty_validation_text").val("");
            jQuery(
              "#sppcfw_product_plus_minus_button_qty_change_global_setp"
            ).val("");
          }
        } else {
          jQuery("#sppcfw_product\\[enable_min_max_qty\\]")
            .prop("checked", false)
            .trigger("change");
          jQuery("#sppcfw_product_min_max_qty_global_min_value").val("");
          jQuery("#sppcfw_product_min_qty_validation_text").val("");
          jQuery("#sppcfw_product_min_max_qty_global_max_value").val("");
          jQuery("#sppcfw_product_max_qty_validation_text").val("");
          jQuery(
            "#sppcfw_product_plus_minus_button_qty_change_global_setp"
          ).val("");
        }

        if (obj.hasOwnProperty("enable_custom_message")) {
          if (obj.enable_custom_message === "on") {
            jQuery("#sppcfw_product\\[enable_custom_message\\]")
              .prop("checked", true)
              .trigger("change");
            jQuery("#sppcfw_product_custom_message_text").val(
              obj.custom_message_text
            );
            jQuery("#sppcfw_product\\[custom_message_display_hook\\]").val(
              obj.custom_message_display_hook
            );
          } else {
            jQuery("#sppcfw_product\\[enable_custom_message\\]")
              .prop("checked", false)
              .trigger("change");
            jQuery("#sppcfw_product_custom_message_text").val("");
            jQuery("#sppcfw_product\\[custom_message_display_hook\\]").val("");
          }
        } else {
          jQuery("#sppcfw_product\\[enable_custom_message\\]")
            .prop("checked", false)
            .trigger("change");
          jQuery("#sppcfw_product_custom_message_text").val("");
          jQuery("#sppcfw_product\\[custom_message_display_hook\\]").val("");
        }

        if (obj.hasOwnProperty("enable_varition_table")) {
          if (obj.enable_varition_table === "on") {
            jQuery("#sppcfw_product\\[enable_varition_table\\]")
              .prop("checked", true)
              .trigger("change");
            jQuery("#sppcfw_product\\[variation_table_display_hook\\]").val(
              obj.variation_table_display_hook
            );
          } else {
            jQuery("#sppcfw_product\\[enable_varition_table\\]")
              .prop("checked", false)
              .trigger("change");
            jQuery("#sppcfw_product\\[variation_table_display_hook\\]").val("");
          }
        } else {
          jQuery("#sppcfw_product\\[enable_varition_table\\]")
            .prop("checked", false)
            .trigger("change");
          jQuery("#sppcfw_product\\[variation_table_display_hook\\]").val("");
        }

        if (obj.hasOwnProperty("enable_change_tab_default_label")) {
          if (obj.enable_change_tab_default_label === "on") {
            jQuery("#sppcfw_product\\[enable_change_tab_default_label\\]")
              .prop("checked", true)
              .trigger("change");
            jQuery("#sppcfw_product_description_tab_label").val(
              obj.description_tab_label
            );
            jQuery("#sppcfw_product_additional_information_tab_label").val(
              obj.additional_information_tab_label
            );
            jQuery("#sppcfw_product_review_tab_label").val(
              obj.review_tab_label
            );
          } else {
            jQuery("#sppcfw_product\\[enable_change_tab_default_label\\]")
              .prop("checked", false)
              .trigger("change");
            jQuery("#sppcfw_product_description_tab_label").val("");
            jQuery("#sppcfw_product_additional_information_tab_label").val("");
            jQuery("#sppcfw_product_review_tab_label").val("");
          }
        } else {
          jQuery("#sppcfw_product\\[enable_change_tab_default_label\\]")
            .prop("checked", false)
            .trigger("change");
          jQuery("#sppcfw_product_description_tab_label").val("");
          jQuery("#sppcfw_product_additional_information_tab_label").val("");
          jQuery("#sppcfw_product_review_tab_label").val("");
        }

        if (obj.hasOwnProperty("enable_layout_switcher")) {
          if (obj.enable_layout_switcher === "on") {
            jQuery("#sppcfw_product\\[enable_layout_switcher\\]").prop(
              "checked",
              true
            );
          } else {
            jQuery("#sppcfw_product\\[enable_layout_switcher\\]").prop(
              "checked",
              false
            );
          }
        } else {
          jQuery("#sppcfw_product\\[enable_layout_switcher\\]").prop(
            "checked",
            false
          );
        }

        if (obj.hasOwnProperty("related_products_title")) {
          jQuery("#sppcfw_product_related_products_title").val(
            obj.related_products_title
          );
        } else {
          jQuery("#sppcfw_product_related_products_title").val("");
        }

        if (obj.hasOwnProperty("upsell_products_title")) {
          jQuery("#sppcfw_product_upsell_products_title").val(
            obj.upsell_products_title
          );
        } else {
          jQuery("#sppcfw_product_upsell_products_title").val("");
        }

        if (obj.hasOwnProperty("related_products_title")) {
          jQuery("#sppcfw_product_related_products_title").val(
            obj.related_products_title
          );
        } else {
          jQuery("#sppcfw_product_related_products_title").val("");
        }

        if (obj.hasOwnProperty("change_clear_text")) {
          jQuery("#sppcfw_product_change_clear_text").val(
            obj.change_clear_text
          );
        } else {
          jQuery("#sppcfw_product_change_clear_text").val("");
        }

        if (obj.hasOwnProperty("change_backorder_text")) {
          jQuery("#sppcfw_product_change_backorder_text").val(
            obj.change_backorder_text
          );
        } else {
          jQuery("#sppcfw_product_change_backorder_text").val("");
        }

        if (obj.hasOwnProperty("enable_quick_cart")) {
          if (obj.enable_quick_cart === "on") {
            jQuery("#sppcfw_product\\[enable_quick_cart\\]").prop(
              "checked",
              true
            );
          } else {
            jQuery("#sppcfw_product\\[enable_quick_cart\\]").prop(
              "checked",
              false
            );
          }
        } else {
          jQuery("#sppcfw_product\\[enable_quick_cart\\]").prop(
            "checked",
            false
          );
        }

        element.unblock();
      });
    } else {
      alert("There is no product category checked yet!");
    }
  });
});

/* Add new div on single product tab sesction */

jQuery(document).ready(function ($) {});

function sppcfw_add_new_additional_content_tab_item(data) {
  let i = parseInt(jQuery(data).attr("data-next"));

  let tab_button_html = `<button type="button" id="sppcfw_custom_tab_button_text_${i}" data-index="${i}" class="tablinks2 active" onclick="sppcfwCustomContent(event, 'sppcfw_custom_tab_content_${i}')">Content</button>`;
  let additional_content_display_hooks = `<select name="sppcfw_cat[sppcfw_custom_additional_content_display_hook][]">`;
  let hooks = sppcfw_settings.sppcfw_wc_action_hooks;
  //console.log(hooks);
  for (let x in hooks) {
    additional_content_display_hooks += `<option value="${x}">${hooks[x]}</option>`;
    //console.log(hooks[x]);
  }
  additional_content_display_hooks += `</select>`;
  let tab_content_html =
    `<div id="sppcfw_custom_tab_content_${i}" class="tabcontent2" style="display:block;">
        <div class="sppcfw_tab_title_input_area"><input type="text" value="Content" id="sppcfw_custom_tab_title_input_${i}" name="sppcfw_cat[sppcfw_custom_additional_content_title][]" data-index="${i}" onkeyup="sppcfw_update_content_title_text(this)" placeholder="Content name"><button class="button button-remove" onclick="sppcfw_backend_remove_content(this)" data-index="${i}" type="button">Remove</button></div>
        <br/><br/>
        ` +
    additional_content_display_hooks +
    `<br/><br/>
        <div style="clear:both" class="wp-media-buttons"><button type="button" id="custom_tab_media_button_${i}" class="button insert-media add_media" data-editor="sppcfw_custom_tab_title_textarea_${i}"><span class="wp-media-buttons-icon"></span> Add Media</button></div>
        <textarea id="sppcfw_custom_tab_title_textarea_${i}" class="wp-editor-area" name="sppcfw_cat[sppcfw_custom_additional_content][]"></textarea>
        
    </div>`;

  jQuery(
    "#sppcfw_cat_additional_tab_items button.tablinks2.active"
  ).removeClass("active");
  jQuery("#sppcfw_single_product_tabcontent_main div.tabcontent2").hide();

  jQuery("#sppcfw_cat_additional_tab_items").append(tab_button_html);
  jQuery("#sppcfw_single_product_tabcontent_main").append(tab_content_html);
  if (tinymce !== "undefined") {
    var settings = {
      tinymce: {
        branding: false,
        theme: "modern",
        skin: "lightgray",
        language: "en",
        formats: {
          alignleft: [
            {
              selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
              styles: { textAlign: "left" },
            },
            { selector: "img,table,dl.wp-caption", classes: "alignleft" },
          ],
          aligncenter: [
            {
              selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
              styles: { textAlign: "center" },
            },
            { selector: "img,table,dl.wp-caption", classes: "aligncenter" },
          ],
          alignright: [
            {
              selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
              styles: { textAlign: "right" },
            },
            { selector: "img,table,dl.wp-caption", classes: "alignright" },
          ],
          strikethrough: { inline: "del" },
        },
        relative_urls: false,
        remove_script_host: false,
        convert_urls: false,
        browser_spellcheck: true,
        fix_list_elements: true,
        entities: "38,amp,60,lt,62,gt",
        entity_encoding: "raw",
        keep_styles: false,
        paste_webkit_styles: "font-weight font-style color",
        preview_styles:
          "font-family font-size font-weight font-style text-decoration text-transform",
        end_container_on_empty_block: true,
        wpeditimage_disable_captions: false,
        wpeditimage_html5_captions: true,
        plugins:
          "charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",
        menubar: false,
        wpautop: true,
        indent: false,
        resize: true,
        theme_advanced_resizing: true,
        theme_advanced_resize_horizontal: false,
        statusbar: true,
        toolbar1:
          "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_adv",
        toolbar2:
          "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
        toolbar3: "",
        toolbar4: "",
        tabfocus_elements: ":prev,:next",
        height: 400,
        width: "100%",
        // body_class: 'id post-type-post post-status-publish post-format-standard',
        setup: function (editor) {
          editor.on("init", function () {
            this.getBody().style.fontFamily =
              'Georgia, "Times New Roman", "Bitstream Charter", Times, serif';
            this.getBody().style.fontSize = "16px";
            this.getBody().style.color = "#333";
          });
        },
      },
      quicktags: {
        buttons: "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close",
      },
    };
    wp.editor.initialize(
      "sppcfw_custom_tab_title_textarea_" + i + "",
      settings
    );
  }
  //jQuery("button#sppcfw_custom_tab_button_text_"+i+"").click();
  jQuery(data).attr("data-next", i + 1);
}

function sppcfw_add_new_additional_content_tab_item_for_product(data) {
  let i = parseInt(jQuery(data).attr("data-next"));

  let tab_button_html = `<button type="button" id="sppcfw_custom_addi_content_button_text_${i}" data-index="${i}" class="tablinks2 active" onclick="sppcfwCustomContentProduct(event, 'sppcfw_custom_addi_content_${i}')">Content</button>`;
  let additional_content_display_hooks = `<select name="sppcfw_custom_additional_content_display_hook[]">`;
  let hooks = sppcfw_settings.sppcfw_wc_action_hooks;
  //console.log(hooks);
  for (let x in hooks) {
    additional_content_display_hooks += `<option value="${x}">${hooks[x]}</option>`;
    //console.log(hooks[x]);
  }
  additional_content_display_hooks += `</select>`;
  let tab_content_html =
    `<div id="sppcfw_custom_addi_content_${i}" class="tabcontent2" style="display:block;">
        <div class="sppcfw_tab_title_input_area"><input type="text" value="Content" id="sppcfw_custom_addi_content_title_input_${i}" name="sppcfw_custom_additional_content_title[]" data-index="${i}" onkeyup="sppcfw_update_product_addi_content_title_text(this)" placeholder="Content name"><button class="button button-remove" onclick="sppcfw_backend_remove_content_product_addi_content(this)" data-index="${i}" type="button">Remove</button></div>
        <br/><br/>
        ` +
    additional_content_display_hooks +
    `<br/><br/>
        <div style="clear:both" class="wp-media-buttons"><button type="button" id="custom_addi_content_media_button_${i}" class="button insert-media add_media" data-editor="sppcfw_custom_addi_content_textarea_${i}"><span class="wp-media-buttons-icon"></span> Add Media</button></div>
        <textarea id="sppcfw_custom_addi_content_textarea_${i}" class="wp-editor-area" name="sppcfw_custom_additional_content[]"></textarea>
        
    </div>`;

  jQuery(
    "#sppcfw_custom_additional_content_tab_items button.tablinks2.active"
  ).removeClass("active");
  jQuery(
    "#sppcfw_single_product_additional_content_main div.tabcontent2"
  ).hide();

  jQuery("#sppcfw_custom_additional_content_tab_items").append(tab_button_html);
  jQuery("#sppcfw_single_product_additional_content_main").append(
    tab_content_html
  );
  if (tinymce !== "undefined") {
    var settings = {
      tinymce: {
        branding: false,
        theme: "modern",
        skin: "lightgray",
        language: "en",
        formats: {
          alignleft: [
            {
              selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
              styles: { textAlign: "left" },
            },
            { selector: "img,table,dl.wp-caption", classes: "alignleft" },
          ],
          aligncenter: [
            {
              selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
              styles: { textAlign: "center" },
            },
            { selector: "img,table,dl.wp-caption", classes: "aligncenter" },
          ],
          alignright: [
            {
              selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
              styles: { textAlign: "right" },
            },
            { selector: "img,table,dl.wp-caption", classes: "alignright" },
          ],
          strikethrough: { inline: "del" },
        },
        relative_urls: false,
        remove_script_host: false,
        convert_urls: false,
        browser_spellcheck: true,
        fix_list_elements: true,
        entities: "38,amp,60,lt,62,gt",
        entity_encoding: "raw",
        keep_styles: false,
        paste_webkit_styles: "font-weight font-style color",
        preview_styles:
          "font-family font-size font-weight font-style text-decoration text-transform",
        end_container_on_empty_block: true,
        wpeditimage_disable_captions: false,
        wpeditimage_html5_captions: true,
        plugins:
          "charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",
        menubar: false,
        wpautop: true,
        indent: false,
        resize: true,
        theme_advanced_resizing: true,
        theme_advanced_resize_horizontal: false,
        statusbar: true,
        toolbar1:
          "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_adv",
        toolbar2:
          "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
        toolbar3: "",
        toolbar4: "",
        tabfocus_elements: ":prev,:next",
        height: 400,
        width: "100%",
        // body_class: 'id post-type-post post-status-publish post-format-standard',
        setup: function (editor) {
          editor.on("init", function () {
            this.getBody().style.fontFamily =
              'Georgia, "Times New Roman", "Bitstream Charter", Times, serif';
            this.getBody().style.fontSize = "16px";
            this.getBody().style.color = "#333";
          });
        },
      },
      quicktags: {
        buttons: "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close",
      },
    };
    wp.editor.initialize(
      "sppcfw_custom_addi_content_textarea_" + i + "",
      settings
    );
  }
  //jQuery("button#sppcfw_custom_tab_button_text_"+i+"").click();
  jQuery(data).attr("data-next", i + 1);
}

function sppcfw_add_new_custom_tab_item(data) {
  let i = parseInt(jQuery(data).attr("data-next"));

  let tab_button_html = `<button type="button" id="sppcfw_custom_tab_button_text_${i}" data-index="${i}" class="tablinks active" onclick="sppcfwCustomTab(event, 'sppcfw_custom_tab_content_${i}')">Tab title</button>`;
  let tab_content_html = `<div id="sppcfw_custom_tab_content_${i}" class="tabcontent" style="display:block;">
        <div class="sppcfw_tab_title_input_area"><input type="text" value="Tab title" id="sppcfw_custom_tab_title_input_${i}" name="sppcfw_custom_tab_title[]" data-index="${i}" onkeyup="sppcfw_update_tab_title_text(this)" placeholder="Tab title"><button class="button button-remove" onclick="sppcfw_backend_remove_tab(this)" data-index="${i}" type="button">Remove this tab</button></div>
        <br/><br/>
        <div style="clear:both" class="wp-media-buttons"><button type="button" id="custom_tab_media_button_${i}" class="button insert-media add_media" data-editor="sppcfw_custom_tab_title_textarea_${i}"><span class="wp-media-buttons-icon"></span> Add Media</button></div>
        <textarea id="sppcfw_custom_tab_title_textarea_${i}" class="wp-editor-area" name="sppcfw_custom_tab_content[]"></textarea>
        
    </div>`;

  jQuery("#sppcfw_content_area2 button.tablinks.active").removeClass("active");
  jQuery("#sppcfw_single_product_tabcontent_main div.tabcontent").hide();

  jQuery("#sppcfw_single_product_custom_tab_items").append(tab_button_html);
  jQuery("#sppcfw_single_product_tabcontent_main").append(tab_content_html);
  if (tinymce !== "undefined") {
    //console.log(tinymce);
    var settings = {
      tinymce: {
        branding: false,
        theme: "modern",
        skin: "lightgray",
        language: "en",
        formats: {
          alignleft: [
            {
              selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
              styles: { textAlign: "left" },
            },
            { selector: "img,table,dl.wp-caption", classes: "alignleft" },
          ],
          aligncenter: [
            {
              selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
              styles: { textAlign: "center" },
            },
            { selector: "img,table,dl.wp-caption", classes: "aligncenter" },
          ],
          alignright: [
            {
              selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
              styles: { textAlign: "right" },
            },
            { selector: "img,table,dl.wp-caption", classes: "alignright" },
          ],
          strikethrough: { inline: "del" },
        },
        relative_urls: false,
        remove_script_host: false,
        convert_urls: false,
        browser_spellcheck: true,
        fix_list_elements: true,
        entities: "38,amp,60,lt,62,gt",
        entity_encoding: "raw",
        keep_styles: false,
        paste_webkit_styles: "font-weight font-style color",
        preview_styles:
          "font-family font-size font-weight font-style text-decoration text-transform",
        end_container_on_empty_block: true,
        wpeditimage_disable_captions: false,
        wpeditimage_html5_captions: true,
        plugins:
          "charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",
        menubar: false,
        wpautop: true,
        indent: false,
        resize: true,
        theme_advanced_resizing: true,
        theme_advanced_resize_horizontal: false,
        statusbar: true,
        toolbar1:
          "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_adv",
        toolbar2:
          "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
        toolbar3: "",
        toolbar4: "",
        tabfocus_elements: ":prev,:next",
        height: 400,
        width: "100%",
        // body_class: 'id post-type-post post-status-publish post-format-standard',
        setup: function (editor) {
          editor.on("init", function () {
            this.getBody().style.fontFamily =
              'Georgia, "Times New Roman", "Bitstream Charter", Times, serif';
            this.getBody().style.fontSize = "16px";
            this.getBody().style.color = "#333";
          });
        },
      },
      quicktags: {
        buttons: "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close",
      },
    };
    wp.editor.initialize(
      "sppcfw_custom_tab_title_textarea_" + i + "",
      settings
    );
  }
  //jQuery("button#sppcfw_custom_tab_button_text_"+i+"").click();
  jQuery(data).attr("data-next", i + 1);
}

function sppcfw_update_tab_title_text(data) {
  let value = jQuery(data).val().trim();
  let index = jQuery(data).attr("data-index");
  let tab_title = "Tab " + index;
  if (value) tab_title = value;
  jQuery("#sppcfw_custom_tab_button_text_" + index + "").text(tab_title);
}

function sppcfw_update_content_title_text(data) {
  let value = jQuery(data).val().trim();
  let index = jQuery(data).attr("data-index");
  let tab_title = "Content " + index;
  if (value) tab_title = value;
  jQuery("#sppcfw_custom_tab_button_text_" + index + "").text(tab_title);
}

function sppcfw_update_product_addi_content_title_text(data) {
  let value = jQuery(data).val().trim();
  let index = jQuery(data).attr("data-index");
  let tab_title = "Content " + index;
  if (value) tab_title = value;
  jQuery("#sppcfw_custom_addi_content_button_text_" + index + "").text(
    tab_title
  );
}

function sppcfw_backend_remove_tab(data) {
  if (confirm("Are you sure?")) {
    let index = jQuery(data).attr("data-index");
    tinymce.get("sppcfw_custom_tab_title_textarea_" + index + "").remove();
    jQuery("#sppcfw_single_product_custom_tab_items")
      .find("button#sppcfw_custom_tab_button_text_" + index + "")
      .remove();
    jQuery("#sppcfw_single_product_tabcontent_main")
      .find("div#sppcfw_custom_tab_content_" + index + "")
      .remove();

    let first_tab = jQuery(
      "#sppcfw_single_product_custom_tab_items button:first"
    );
    if (first_tab.length > 0) {
      jQuery(first_tab).addClass("active");
      let target_index = jQuery(first_tab).attr("data-index");
      jQuery("div#sppcfw_custom_tab_content_" + target_index + "").show();
    }
  }
}

function sppcfw_backend_remove_content(data) {
  if (confirm("Are you sure?")) {
    let index = jQuery(data).attr("data-index");
    tinymce.get("sppcfw_custom_tab_title_textarea_" + index + "").remove();
    jQuery("#sppcfw_product_category_additonal_content")
      .find("button#sppcfw_custom_tab_button_text_" + index + "")
      .remove();
    jQuery("#sppcfw_product_category_additonal_content")
      .find("div#sppcfw_custom_tab_content_" + index + "")
      .remove();

    let first_tab = jQuery("#sppcfw_cat_additional_tab_items button:first");
    if (first_tab.length > 0) {
      jQuery(first_tab).addClass("active");
      let target_index = jQuery(first_tab).attr("data-index");
      jQuery("div#sppcfw_custom_tab_content_" + target_index + "").show();
    }
  }
}

function sppcfw_backend_remove_content_product_addi_content(data) {
  if (confirm("Are you sure?")) {
    let index = jQuery(data).attr("data-index");
    tinymce.get("sppcfw_custom_addi_content_textarea_" + index + "").remove();
    jQuery("#sppcfw_product_additonal_content")
      .find("button#sppcfw_custom_addi_content_button_text_" + index + "")
      .remove();
    jQuery("#sppcfw_product_additonal_content")
      .find("div#sppcfw_custom_addi_content_" + index + "")
      .remove();

    let first_tab = jQuery(
      "#sppcfw_custom_additional_content_tab_items button:first"
    );
    if (first_tab.length > 0) {
      jQuery(first_tab).addClass("active");
      let target_index = jQuery(first_tab).attr("data-index");
      jQuery("div#sppcfw_custom_addi_content_" + target_index + "").show();
    }
  }
}

function sppcfw_tab_re_indexing() {}

function generate_custom_link(element) {
  const targetUrl = element.dataset.url;

  if (targetUrl) {
    window.open(targetUrl, "_blank");
  }
}
