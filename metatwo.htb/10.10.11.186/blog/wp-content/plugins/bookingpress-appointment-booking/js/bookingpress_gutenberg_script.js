!function(){var o=wp.element.createElement,e=wp.blocks.registerBlockType;source=wp.blocks.source;var t=wp.i18n.__;const n=o("img",{src:__BOOKINGPRESSIMAGEURL+"/bookingpress_menu_icon.png"});e("bookingpress/bookingpress-appointment-form",{title:t("BookingPress step-by-step wizard form"),icon:n,category:"bookingpress",keywords:[t("appointment"),t("bookingpress"),t("shortcode")],attributes:{short_code:{type:"string","default":"[bookingpress_form]"}},edit:function(e){return"bookingpress/bookingpress-appointment-form"==e.name?o("div",{},e.attributes.short_code):void 0},save:function(e){return o("div",{},e.attributes.short_code)}}),e("bookingpress/bookingpress-my-booking",{title:t("Customer panel - My Appointments"),icon:n,category:"bookingpress",keywords:[t("appointment"),t("bookingpress"),t("booking")],attributes:{short_code:{type:"string","default":"[bookingpress_my_appointments]"}},edit:function(e){return"bookingpress/bookingpress-my-booking"==e.name?o("div",{},e.attributes.short_code):void 0},save:function(e){return o("div",{},e.attributes.short_code)}})}(window.wp.blocks,window.wp.components,window.wp.i18n,window.wp.element,window.wp.editor);