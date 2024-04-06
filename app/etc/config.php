<?php
return [
    'scopes' => [
        'websites' => [
            'admin' => [
                'website_id' => '0',
                'code' => 'admin',
                'name' => 'Admin',
                'sort_order' => '0',
                'default_group_id' => '0',
                'is_default' => '0'
            ],
            'base' => [
                'website_id' => '1',
                'code' => 'base',
                'name' => 'Main Website',
                'sort_order' => '0',
                'default_group_id' => '1',
                'is_default' => '1'
            ]
        ],
        'groups' => [
            [
                'group_id' => '0',
                'website_id' => '0',
                'name' => 'Default',
                'root_category_id' => '0',
                'default_store_id' => '0',
                'code' => 'default'
            ],
            [
                'group_id' => '1',
                'website_id' => '1',
                'name' => 'Main Website Store',
                'root_category_id' => '2',
                'default_store_id' => '1',
                'code' => 'main_website_store'
            ]
        ],
        'stores' => [
            'admin' => [
                'store_id' => '0',
                'code' => 'admin',
                'website_id' => '0',
                'group_id' => '0',
                'name' => 'Admin',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'en' => [
                'store_id' => '1',
                'code' => 'en',
                'website_id' => '1',
                'group_id' => '1',
                'name' => 'EN',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'th' => [
                'store_id' => '2',
                'code' => 'th',
                'website_id' => '1',
                'group_id' => '1',
                'name' => 'TH',
                'sort_order' => '0',
                'is_active' => '0'
            ]
        ]
    ],
    'system' => [
        'default' => [
            'general' => [
                'locale' => [
                    'code' => 'en_US'
                ]
            ],
            'web' => [
                'default' => [
                    'show_cms_breadcrumbs' => '1'
                ],
                'url' => [
                    'use_store' => '1'
                ]
            ],
            'dev' => [
                'static' => [
                    'sign' => '0'
                ],
                'front_end_development_workflow' => [
                    'type' => 'server_side_compilation'
                ],
                'template' => [
                    'minify_html' => '0'
                ],
                'js' => [
                    'merge_files' => '0',
                    'minify_files' => '0',
                    'minify_exclude' => [
                        'tiny_mce' => '/tiny_mce/',
                        'cardinal_commerce' => '/v1/songbird',
                        'authorizenet_acceptjs' => '\\.authorize\\.net/v1/Accept'
                    ],
                    'move_script_to_bottom' => '0',
                    'session_storage_logging' => '0',
                    'translate_strategy' => 'dictionary'
                ],
                'css' => [
                    'minify_files' => '0',
                    'minify_exclude' => [
                        'tiny_mce' => '/tiny_mce/'
                    ],
                    'use_css_critical_path' => '0'
                ]
            ],
            'catalog' => [
                'review' => [
                    'active' => '0'
                ]
            ],
            'cataloginventory' => [
                'options' => [
                    'display_product_stock_status' => '0'
                ]
            ],
            'checkout' => [
                'options' => [
                    'display_billing_address_on' => '1'
                ]
            ],
            'sales' => [
                'reorder' => [
                    'allow' => '0'
                ]
            ],
            'customer' => [
                'address_templates' => [
                    'text' => '{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}
{{depend company}}{{var company}}{{/depend}}
{{if street1}}{{var street1}}
{{/if}}
{{depend street2}}{{var street2}}{{/depend}}
{{depend street3}}{{var street3}}{{/depend}}
{{depend street4}}{{var street4}}{{/depend}}
{{if subdistrict}}{{var subdistrict}}, {{/if}}{{if city}}{{var city}}, {{/if}}{{if region}}{{var region}}, {{/if}}{{if postcode}}{{var postcode}}{{/if}}
{{var country}}
{{depend telephone}}T: {{var telephone}}{{/depend}}
{{depend fax}}F: {{var fax}}{{/depend}}
{{depend vat_id}}VAT: {{var vat_id}}{{/depend}}',
                    'oneline' => '{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}, {{var street}}, {{var subdistrict}}, {{var city}}, {{var region}} {{var postcode}}, {{var country}}',
                    'html' => '{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}{{depend firstname}}<br />{{/depend}}
{{depend company}}{{var company}}<br />{{/depend}}
{{if street1}}{{var street1}}<br />{{/if}}
{{depend street2}}{{var street2}}<br />{{/depend}}
{{depend street3}}{{var street3}}<br />{{/depend}}
{{depend street4}}{{var street4}}<br />{{/depend}}
{{if subdistrict}}{{var subdistrict}}, {{/if}}{{if city}}{{var city}},  {{/if}}{{if region}}{{var region}}, {{/if}}{{if postcode}}{{var postcode}}{{/if}}<br />
{{var country}}<br />
{{depend telephone}}T: <a href="tel:{{var telephone}}">{{var telephone}}</a>{{/depend}}
{{depend fax}}<br />F: {{var fax}}{{/depend}}
{{depend vat_id}}<br />VAT: {{var vat_id}}{{/depend}}',
                    'pdf' => '{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}|
{{depend company}}{{var company}}|{{/depend}}
{{if street1}}{{var street1}}|{{/if}}
{{depend street2}}{{var street2}}|{{/depend}}
{{depend street3}}{{var street3}}|{{/depend}}
{{depend street4}}{{var street4}}|{{/depend}}
{{if subdistrict}}{{var subdistrict}}, {{/if}}{{if city}}{{var city}}, {{/if}}{{if region}}{{var region}}, {{/if}}{{if postcode}}{{var postcode}}{{/if}}|
{{var country}}|
{{depend telephone}}T: {{var telephone}}|{{/depend}}
{{depend fax}}F: {{var fax}}|{{/depend}}|
{{depend vat_id}}VAT: {{var vat_id}}{{/depend}}|'
                ]
            ],
            'weltpixel_advancedwishlist' => [
                'general' => [
                    'enable_multiwishlist' => '1',
                    'enable_ajaxwishlist' => '0',
                    'enable_sharewishlist' => '0',
                    'enable_pricealert' => '0'
                ]
            ],
            'weltpixel_frontend_options' => [
                'breakpoints' => [
                    'screen__l' => '1024px',
                    'screen__m' => '769px',
                    'screen__s' => '640px',
                    'screen__xl' => '1440px',
                    'screen__xs' => '480px',
                    'screen__xxs' => '320px'
                ],
                'section_width' => [
                    'category_page' => '1440px',
                    'cms_page' => '100%',
                    'default_page' => '100%',
                    'footer' => '100%',
                    'page_main' => '1440px',
                    'page_main_padding' => '0 15px',
                    'product_page' => '1440px',
                    'row' => '1440px'
                ],
                'ratings' => [
                    'rating____icon__active__color' => '#000000',
                    'rating____icon__color' => '#B3B3B3',
                    'rating____icon__font____size' => '12px'
                ],
                'primary_buttons' => [
                    'button____primary__background' => '#000000',
                    'button____primary__border' => '#000000',
                    'button____primary__color' => '#FFFFFF',
                    'button____primary__hover__background' => '#73726F',
                    'button____primary__hover__border' => '#73726F',
                    'button____primary__hover__color' => '#FFFFFF',
                    'button____primary__line____height' => '16px'
                ],
                'default' => [
                    'font____family__base' => '0',
                    'font____family__base_characterset' => 'latin',
                    'font____size__base' => null,
                    'font____style__base' => 'inherit',
                    'font__letter____spacing' => '0',
                    'font____weight__regular' => '400',
                    'line____height__computed' => '1.57',
                    'text__color' => '#231F20'
                ],
                'navigation' => [
                    'navigation__background' => null,
                    'navigation____level0____item__active__color' => null
                ],
                'google_font_options' => [
                    'asyn_webfont_load' => '1'
                ],
                'main_options' => [
                    'active__color' => '#000000',
                    'error__color' => '#E75766',
                    'layout__max____width' => '1440px',
                    'page__background____color' => '#FFFFFF',
                    'primary__color' => '#231F20',
                    'secondary__color' => '#736B61'
                ],
                'links' => [
                    'link__color' => '#231F20',
                    'link__hover__color' => '#73726F',
                    'link__hover__text____decoration' => null,
                    'link__text____decoration' => null
                ],
                'h6' => [
                    'h6__font____color' => '#231F20',
                    'h6__font____family' => '0',
                    'h6__font____family_characterset' => 'latin',
                    'h6__font____size' => '16px',
                    'h6__font____style' => 'inherit',
                    'h6__letter____spacing' => '0',
                    'h6__font____weight' => '600',
                    'h6__line____height' => '1.25'
                ],
                'h5' => [
                    'h5__font____color' => '#231F20',
                    'h5__font____family' => '0',
                    'h5__font____family_characterset' => 'latin',
                    'h5__font____size' => '24px',
                    'h5__font____style' => 'inherit',
                    'h5__letter____spacing' => '0',
                    'h5__font____weight' => '600',
                    'h5__line____height' => '1.25'
                ],
                'h4' => [
                    'h4__font____color' => '#231F20',
                    'h4__font____family' => '0',
                    'h4__font____family_characterset' => 'latin',
                    'h4__font____size' => '28px',
                    'h4__font____style' => 'inherit',
                    'h4__letter____spacing' => '0',
                    'h4__font____weight' => '600',
                    'h4__line____height' => '34px'
                ],
                'h3' => [
                    'h3__font____color' => '#231F20',
                    'h3__font____family' => '0',
                    'h3__font____family_characterset' => 'latin',
                    'h3__font____size' => '32px',
                    'h3__font____style' => 'inherit',
                    'h3__letter____spacing' => '0',
                    'h3__font____weight' => '400',
                    'h3__line____height' => null
                ],
                'h2' => [
                    'h2__font____color' => '#231F20',
                    'h2__font____family' => '0',
                    'h2__font____family_characterset' => 'latin',
                    'h2__font____size' => '52px',
                    'h2__font____style' => 'inherit',
                    'h2__letter____spacing' => '0',
                    'h2__font____weight' => '400',
                    'h2__line____height' => '1.5'
                ],
                'h1' => [
                    'h1__font____color' => '#231F20',
                    'h1__font____family' => '0',
                    'h1__font____family_characterset' => 'latin',
                    'h1__font____size' => '62px',
                    'h1__font____style' => 'inherit',
                    'h1__letter____spacing' => '0',
                    'h1__font____weight' => '400',
                    'h1__line____height' => '1.5'
                ],
                'form_inputs' => [
                    'form____element____input__background' => null,
                    'form____element____input__border' => '1px solid',
                    'form____element____input__border____color' => '#73726F',
                    'form____element____input__border____radius' => '0',
                    'form____element____input__color' => null,
                    'form____element____input__font____family' => '0',
                    'form____element____input__font____size' => null,
                    'form____element____input__font____style' => null,
                    'form____element____input__font____weight' => 'normal',
                    'form____element____input__height' => '40px',
                    'form____element____input__line____height' => null,
                    'form____element____input____placeholder__color' => '#73726F'
                ],
                'dropdown' => [
                    'dropdown____list__background' => '#FFFFFF',
                    'dropdown____list__border' => '#CCCCCC',
                    'dropdown____list__shadow' => '0px 20px 50px -20px rgba(0, 0, 0, 0.5)',
                    'dropdown____list____item__hover' => '#F4F4F4',
                    'dropdown____list____pointer__border' => '#FFFFFF'
                ],
                'default_buttons' => [
                    'button__background' => '#000000',
                    'button__border' => '#000000',
                    'button__border____radius' => '0',
                    'button__color' => '#FFFFFF',
                    'button__font____family' => '0',
                    'button__font____family_characterset' => 'latin',
                    'button__font____size' => '14px',
                    'button__font____weight' => '400',
                    'button__hover__background' => '#73726F',
                    'button__hover__border' => '#73726F',
                    'button__hover__color' => '#FFFFFF',
                    'button__line____height' => '16px',
                    'button__letter____spacing' => '0'
                ],
                'breadcrumbs' => [
                    'breadcrumbs__font____size' => '13px',
                    'breadcrumbs__font____style' => 'normal',
                    'breadcrumbs__separator____color' => '#ACA899',
                    'breadcrumbs____background' => '#F7F5EE',
                    'breadcrumbs____current__color' => '#ACA899',
                    'breadcrumbs____link__color' => '#ACA899',
                    'breadcrumbs____link__hover__text____decoration' => '#ACA899'
                ],
                'icons' => [
                    'icon__font____size' => '30px',
                    'icon_label__font____size' => '16px'
                ],
                'price' => [
                    'old_price__line____through' => '1'
                ],
                'contact_options' => [
                    'contact_version' => 'v1',
                    'enable_block' => '0'
                ]
            ],
            'weltpixel_custom_footer' => [
                'prefooter' => [
                    'enable' => '1',
                    'background_color' => '#ADABA6',
                    'text_color' => '#231F20',
                    'icon_color' => '#231F20'
                ],
                'footer' => [
                    'enable' => '1',
                    'version' => 'v3',
                    'background_color' => '#73726F',
                    'text_color' => '#E6E4DD',
                    'icon_color' => '#E6E4DD'
                ],
                'copyright' => [
                    'enable' => '0'
                ]
            ],
            'weltpixel_custom_header' => [
                'general' => [
                    'header_style' => 'v3'
                ],
                'global_promo' => [
                    'text_color' => '#ffffff',
                    'background_color' => '#73726f'
                ],
                'top_header' => [
                    'active_link_color' => '#E6E4DD',
                    'background_color' => '#E6E4DD',
                    'border_bottom_color' => '#D6D6D6',
                    'hover_link_color' => '#73726F',
                    'link_color' => '#231F20',
                    'text_color' => '#231F20',
                    'visited_link_color' => '#030303',
                    'submenu_link_color' => '#231F20',
                    'submenu_hover_link_color' => '#73726F',
                    'width' => '1440px'
                ],
                'search_options' => [
                    'background' => null,
                    'border_color' => '#E6E4DD',
                    'border_style' => 'solid',
                    'border_width' => null,
                    'color' => '#5C5C5C',
                    'font_size' => '12px',
                    'height' => '30px',
                    'width' => '250px',
                    'background_v2' => '#000',
                    'background_opacity' => '0.6',
                    'background_input' => '#E6E4DD',
                    'main_elements_color' => '#73726F',
                    'display_magnifier' => '0',
                    'magnifier_background_color' => '##E6E4DD',
                    'input_borders' => '1',
                    'input_font_size' => '20',
                    'border_widthv2' => '1',
                    'placeholder_text' => 'Search PP Group',
                    'customHtml' => '',
                    'version' => '1',
                    'placeholder_color' => '#73726F'
                ],
                'middle_header' => [
                    'width' => '1440px',
                    'background' => null
                ],
                'bottom_header' => [
                    'background' => null,
                    'hover_link_color' => '#231F20',
                    'link_color' => '#231F20',
                    'shadow' => null,
                    'width' => '1440px',
                    'font_size_l0' => '14px',
                    'font_size_l1' => '15px',
                    'font_size_l2' => '14px'
                ],
                'icons' => [
                    'icon_size' => '20px',
                    'icon_color' => '#231F20',
                    'icon_color_hover' => '#231F20'
                ]
            ],
            'weltpixel_design_elements' => [
                'general' => [
                    'toggles_accordions_tabs' => '1',
                    'testimonials' => '1',
                    'smooth_scrolling' => '1',
                    'sections' => '1',
                    'responsive_helpers' => '1',
                    'pricing_boxes' => '1',
                    'icon_boxes' => '1',
                    'icons' => '1',
                    'headings_blockquotes' => '1',
                    'flexslider' => '1',
                    'dividers' => '1',
                    'buttons' => '1',
                    'brand_lists' => '1',
                    'bootstrap_tables' => '1',
                    'bootstrap_grids' => '1',
                    'animations' => '1',
                    'aos_animation' => '1',
                    'alert_boxes' => '1',
                    'btt_button' => '1',
                    'btt_offset' => '300',
                    'btt_offset_opacity' => '1200',
                    'btt_duration' => '700',
                    'collapsible_top_jump' => '0'
                ]
            ],
            'weltpixel_fullpagescroll' => [
                'general' => [
                    'enable' => '1'
                ]
            ],
            'weltpixel_lazy_loading' => [
                'general' => [
                    'enable' => '1'
                ],
                'advanced' => [
                    'loading_early' => 'No',
                    'negative_margin' => '300',
                    'effect_speed' => '1000',
                    'loading_placeholder' => 'No',
                    'placeholder_width' => '50',
                    'loading_icon' => null
                ]
            ],
            'weltpixel_owl_carousel_config' => [
                'general' => [
                    'breakpoint_1' => '0',
                    'breakpoint_2' => '768',
                    'breakpoint_3' => '1024',
                    'breakpoint_4' => '1280'
                ],
                'new_products' => [
                    'show_price' => '1',
                    'show_addto' => '1',
                    'show_wishlist' => '0',
                    'show_compare' => '0',
                    'show_reviews_ratings' => '0',
                    'random_sort' => '0',
                    'max_items' => '15',
                    'slide_by' => '1',
                    'loop' => '1',
                    'margin' => '0',
                    'lazyLoad' => '1',
                    'autoplay' => '1',
                    'autoplayTimeout' => '3000',
                    'autoplayHoverPause' => '1',
                    'nav_brk1' => '0',
                    'dots_brk1' => '0',
                    'items_brk1' => '2',
                    'center_brk1' => '0',
                    'stagePadding_brk1' => '0',
                    'nav_brk2' => '1',
                    'dots_brk2' => '0',
                    'items_brk2' => '3',
                    'center_brk2' => '0',
                    'stagePadding_brk2' => '0',
                    'nav_brk3' => '1',
                    'dots_brk3' => '0',
                    'items_brk3' => '4',
                    'center_brk3' => '0',
                    'stagePadding_brk3' => '0',
                    'nav_brk4' => '1',
                    'dots_brk4' => '0',
                    'items_brk4' => '6',
                    'center_brk4' => '0',
                    'stagePadding_brk4' => '0'
                ],
                'bestsell_products' => [
                    'period' => 'beginning',
                    'show_price' => '1',
                    'show_addto' => '1',
                    'show_wishlist' => '0',
                    'show_compare' => '0',
                    'show_reviews_ratings' => '0',
                    'random_sort' => '0',
                    'max_items' => '15',
                    'slide_by' => '1',
                    'loop' => '1',
                    'margin' => '0',
                    'lazyLoad' => '1',
                    'autoplay' => '1',
                    'autoplayTimeout' => '3000',
                    'autoplayHoverPause' => '1',
                    'nav_brk1' => '0',
                    'dots_brk1' => '1',
                    'items_brk1' => '2',
                    'center_brk1' => '0',
                    'stagePadding_brk1' => '0',
                    'nav_brk2' => '0',
                    'dots_brk2' => '1',
                    'items_brk2' => '3',
                    'center_brk2' => '0',
                    'stagePadding_brk2' => '0',
                    'nav_brk3' => '0',
                    'dots_brk3' => '1',
                    'items_brk3' => '4',
                    'center_brk3' => '0',
                    'stagePadding_brk3' => '0',
                    'nav_brk4' => '1',
                    'dots_brk4' => '1',
                    'items_brk4' => '5',
                    'center_brk4' => '0',
                    'stagePadding_brk4' => '0'
                ],
                'sell_products' => [
                    'show_price' => '1',
                    'show_addto' => '1',
                    'show_wishlist' => '0',
                    'show_compare' => '0',
                    'show_reviews_ratings' => '0',
                    'random_sort' => '0',
                    'max_items' => '15',
                    'slide_by' => '1',
                    'loop' => '1',
                    'margin' => '0',
                    'lazyLoad' => '1',
                    'autoplay' => '1',
                    'autoplayTimeout' => '3000',
                    'autoplayHoverPause' => '1',
                    'nav_brk1' => '0',
                    'items_brk1' => '2',
                    'dots_brk1' => '1',
                    'center_brk1' => '0',
                    'stagePadding_brk1' => '0',
                    'nav_brk2' => '0',
                    'items_brk2' => '3',
                    'dots_brk2' => '1',
                    'center_brk2' => '0',
                    'stagePadding_brk2' => '0',
                    'nav_brk3' => '0',
                    'dots_brk3' => '1',
                    'items_brk3' => '4',
                    'center_brk3' => '0',
                    'stagePadding_brk3' => '0',
                    'nav_brk4' => '1',
                    'items_brk4' => '5',
                    'dots_brk4' => '1',
                    'center_brk4' => '0',
                    'stagePadding_brk4' => '0'
                ],
                'recently_viewed' => [
                    'show_price' => '1',
                    'show_addto' => '1',
                    'show_wishlist' => '0',
                    'show_compare' => '0',
                    'show_reviews_ratings' => '0',
                    'random_sort' => '0',
                    'max_items' => '15',
                    'slide_by' => '1',
                    'loop' => '1',
                    'margin' => '0',
                    'lazyLoad' => '1',
                    'autoplay' => '1',
                    'autoplayTimeout' => '3000',
                    'autoplayHoverPause' => '1',
                    'nav_brk1' => '0',
                    'dots_brk1' => '1',
                    'items_brk1' => '2',
                    'center_brk1' => '0',
                    'stagePadding_brk1' => '0',
                    'nav_brk2' => '0',
                    'dots_brk2' => '1',
                    'items_brk2' => '3',
                    'center_brk2' => '0',
                    'stagePadding_brk2' => '0',
                    'nav_brk3' => '0',
                    'dots_brk3' => '1',
                    'items_brk3' => '4',
                    'center_brk3' => '0',
                    'stagePadding_brk3' => '0',
                    'nav_brk4' => '1',
                    'dots_brk4' => '1',
                    'items_brk4' => '5',
                    'center_brk4' => '0',
                    'stagePadding_brk4' => '0'
                ],
                'related_products' => [
                    'max_items' => '15',
                    'slide_by' => '1',
                    'show_addto' => '1',
                    'loop' => '1',
                    'margin' => '0',
                    'lazyLoad' => '1',
                    'autoplay' => '1',
                    'autoplayTimeout' => '3000',
                    'autoplayHoverPause' => '1',
                    'nav_brk1' => '0',
                    'dots_brk1' => '1',
                    'items_brk1' => '2',
                    'center_brk1' => '0',
                    'stagePadding_brk1' => '0',
                    'nav_brk2' => '0',
                    'items_brk2' => '3',
                    'dots_brk2' => '1',
                    'center_brk2' => '0',
                    'stagePadding_brk2' => '0',
                    'nav_brk3' => '0',
                    'dots_brk3' => '1',
                    'items_brk3' => '4',
                    'center_brk3' => '0',
                    'stagePadding_brk3' => '0',
                    'nav_brk4' => '1',
                    'dots_brk4' => '1',
                    'items_brk4' => '4',
                    'center_brk4' => '0',
                    'stagePadding_brk4' => '0'
                ],
                'upsell_products' => [
                    'max_items' => '15',
                    'slide_by' => '1',
                    'loop' => '1',
                    'margin' => '0',
                    'lazyLoad' => '1',
                    'autoplay' => '1',
                    'autoplayTimeout' => '3000',
                    'autoplayHoverPause' => '1',
                    'nav_brk1' => '0',
                    'dots_brk1' => '1',
                    'items_brk1' => '2',
                    'center_brk1' => '0',
                    'stagePadding_brk1' => '0',
                    'nav_brk2' => '0',
                    'dots_brk2' => '1',
                    'items_brk2' => '3',
                    'center_brk2' => '0',
                    'stagePadding_brk2' => '0',
                    'nav_brk3' => '0',
                    'dots_brk3' => '1',
                    'items_brk3' => '4',
                    'center_brk3' => '0',
                    'stagePadding_brk3' => '0',
                    'nav_brk4' => '1',
                    'dots_brk4' => '1',
                    'items_brk4' => '5',
                    'center_brk4' => '0',
                    'stagePadding_brk4' => '0'
                ],
                'crosssell_products' => [
                    'max_items' => '15',
                    'slide_by' => '1',
                    'loop' => '1',
                    'margin' => '0',
                    'lazyLoad' => '1',
                    'autoplay' => '1',
                    'autoplayTimeout' => '3000',
                    'autoplayHoverPause' => '1',
                    'nav_brk1' => '0',
                    'dots_brk1' => '1',
                    'items_brk1' => '2',
                    'center_brk1' => '0',
                    'stagePadding_brk1' => '0',
                    'nav_brk2' => '0',
                    'dots_brk2' => '1',
                    'items_brk2' => '3',
                    'center_brk2' => '0',
                    'stagePadding_brk2' => '0',
                    'nav_brk3' => '0',
                    'dots_brk3' => '1',
                    'items_brk3' => '4',
                    'center_brk3' => '0',
                    'stagePadding_brk3' => '0',
                    'nav_brk4' => '1',
                    'dots_brk4' => '1',
                    'items_brk4' => '5',
                    'center_brk4' => '0',
                    'stagePadding_brk4' => '0'
                ],
                'category_products' => [
                    'show_price' => '1',
                    'show_addto' => '1',
                    'show_wishlist' => '0',
                    'show_compare' => '0',
                    'show_reviews_ratings' => '0',
                    'random_sort' => '0',
                    'max_items' => '20',
                    'slide_by' => '1',
                    'loop' => '0',
                    'margin' => '0',
                    'lazyLoad' => '1',
                    'autoplay' => '0',
                    'autoplayTimeout' => '3000',
                    'autoplayHoverPause' => '1',
                    'nav_brk1' => '0',
                    'dots_brk1' => '1',
                    'items_brk1' => '1',
                    'center_brk1' => '0',
                    'stagePadding_brk1' => '100',
                    'nav_brk2' => '0',
                    'dots_brk2' => '1',
                    'items_brk2' => '3',
                    'center_brk2' => '0',
                    'stagePadding_brk2' => '0',
                    'nav_brk3' => '0',
                    'dots_brk3' => '1',
                    'items_brk3' => '4',
                    'center_brk3' => '0',
                    'stagePadding_brk3' => '0',
                    'nav_brk4' => '1',
                    'dots_brk4' => '1',
                    'items_brk4' => '4',
                    'center_brk4' => '0',
                    'stagePadding_brk4' => '20'
                ]
            ],
            'weltpixel_owl_slider_config' => [
                'general' => [
                    'enable_google_tracking' => '0',
                    'enable_hover_image
                    ' => '0',
                    'mobile_breakpoint' => '768',
                    'default_loader' => '1'
                ]
            ],
            'weltpixel_multistore' => [
                'general' => [
                    'enable' => '1',
                    'redirect_to_home_page' => '0',
                    'one_row' => '0',
                    'bar_bg_color' => '#000000',
                    'bar_width' => null,
                    'visibility' => '1',
                    'img_height' => null,
                    'img_width' => null,
                    'exclude_stores' => null
                ]
            ],
            'weltpixel_megamenu' => [
                'megamenu' => [
                    'enable' => '1'
                ]
            ],
            'weltpixel_newsletter' => [
                'general' => [
                    'enable' => '0',
                    'display_mode' => '1',
                    'display_mobile' => '1',
                    'display_after_pages' => '1',
                    'display_after_seconds' => '1',
                    'display_block' => 'weltpixel_newsletter',
                    'popup_cookie_lifetime' => '4',
                    'disable_popup' => '1',
                    'terms_conditions_consent' => '0',
                    'terms_conditions_text' => '<a href="https://pearl.weltpixel.com/privacy-policy-cookie-restriction-mode" target="_blank">I agree to the Privacy Policy. </a>'
                ]
            ],
            'weltpixel_category_page' => [
                'general' => [
                    'addtocart_align' => 'center',
                    'display_addtocart' => '5',
                    'display_compare' => '0',
                    'display_swatches' => '2',
                    'display_swatch_tooltip' => '0',
                    'display_wishlist' => '2',
                    'products_per_line' => '3',
                    'remove_breadcrumbs' => '0'
                ],
                'image' => [
                    'list_height' => null,
                    'list_width' => null
                ],
                'description' => [
                    'enable_show_more' => '0'
                ],
                'item' => [
                    'border_color' => '#FFFFFF',
                    'border_width' => '0px',
                    'box_shadow' => '0px 2px 9px rgba(0,0,0,0.25)'
                ],
                'name' => [
                    'color' => '#000000',
                    'font_size' => '14px',
                    'name_align' => 'center'
                ],
                'price' => [
                    'price_align' => 'center',
                    'price_color' => '#A7AAAF',
                    'price_font_size' => '14px',
                    'special_price_color' => '#FF0000',
                    'special_price_font_size' => '14px'
                ],
                'review' => [
                    'display_reviews' => '0',
                    'review_align' => 'center'
                ],
                'swatch_layerednavigation' => [
                    'font_size' => '12px',
                    'height' => '30px',
                    'radius' => '30px',
                    'swatch_align' => 'left',
                    'width' => '30px'
                ],
                'swatch_productlisting' => [
                    'font_size' => '12px',
                    'height' => '14px',
                    'radius' => '14px',
                    'show_on_hover' => '1',
                    'swatch_align' => 'center',
                    'width' => '14px'
                ],
                'toolbar' => [
                    'background_color' => null,
                    'border_color' => null,
                    'direction_font_color' => null,
                    'direction_font_hover_color' => null,
                    'direction_font_size' => '12px',
                    'grid_list_background_active_color' => '#FFFFFF',
                    'grid_list_background_color' => '#FFFFFF',
                    'grid_list_background_hover_color' => '#FFFFFF',
                    'grid_list_border_active_color' => '#FFFFFF',
                    'grid_list_border_color' => '#FFFFFF',
                    'grid_list_border_hover_color' => '#FFFFFF',
                    'grid_list_icon_active_color' => '#000000',
                    'grid_list_icon_color' => '#BABABA',
                    'grid_list_icon_hover_color' => '#2B2B2B',
                    'grid_list_icon_size' => '20px',
                    'label_font_color' => null,
                    'label_font_size' => '14px',
                    'padding' => '10px',
                    'pagination_background_active_color' => null,
                    'pagination_background_color' => null,
                    'pagination_background_hover_color' => null,
                    'pagination_border_active_color' => '#231F20',
                    'pagination_border_color' => null,
                    'pagination_border_hover_color' => null,
                    'pagination_border_radius' => '30px',
                    'pagination_font_active_color' => '#000000',
                    'pagination_font_color' => '#231F20',
                    'pagination_font_hover_color' => '#A8A8A8',
                    'pagination_font_size' => null,
                    'pagination_next_prev_border_color' => '#FFFFFF',
                    'pagination_next_prev_border_hover_color' => '#000000',
                    'pagination_next_prev_background_color' => '#FFFFFF',
                    'pagination_next_prev_background_hover_color' => '#000000',
                    'pagination_next_prev_font_color' => '#000000',
                    'pagination_next_prev_font_hover_color' => '#FFFFFF',
                    'pagination_next_prev_font_size' => '16px',
                    'select_arrow_color' => '#231F20',
                    'select_arrow_hover_color' => '#231F20',
                    'select_background_color' => '#FFFFFF',
                    'select_background_focus_color' => '#FFFFFF',
                    'select_border_color' => '#231F20',
                    'select_border_focus_color' => '#231F20',
                    'select_border_radius' => '0px',
                    'select_border_width' => null,
                    'select_font_color' => '#231F20',
                    'select_font_size' => '13px',
                    'select_option_background_hover_color' => null,
                    'select_option_font_color' => '#231F20'
                ]
            ],
            'weltpixel_productlabels' => [
                'general' => [
                    'enable_category_page' => '1',
                    'category_page_display_mode' => 'always',
                    'enable_product_page' => '1'
                ]
            ],
            'weltpixel_product_page' => [
                'general' => [
                    'display_swatch_tooltip' => '0',
                    'image_area_width' => '60%',
                    'move_description_tabs_under_info_area' => '0',
                    'position_product_info' => '1',
                    'product_info_area_width' => '35%',
                    'qty_select_maxvalue' => '10',
                    'qty_type' => 'select',
                    'remove_breadcrumbs' => '0',
                    'remove_compare' => '0',
                    'remove_email' => '1',
                    'remove_qty_box' => '0',
                    'remove_sku' => '0',
                    'remove_stock_availability' => '0',
                    'accordion_version' => '0',
                    'accordion_collapsible' => '0',
                    'tabs_version' => '0',
                    'remove_wishlist' => '0',
                    'tabs_layout' => 'tab'
                ],
                'gallery' => [
                    'arrows' => 'true',
                    'caption' => 'false',
                    'keyboard' => 'true',
                    'loop' => 'true',
                    'nav' => 'thumbs',
                    'navarrows' => 'true',
                    'navdir' => 'horizontal',
                    'navtype' => 'slides',
                    'transition_duration' => '500',
                    'transition_effect' => 'slide'
                ],
                'fullscreen' => [
                    'allowfullscreen' => 'true',
                    'arrows' => 'true',
                    'caption' => 'false',
                    'loop' => 'true',
                    'nav' => 'thumbs',
                    'navdir' => 'horizontal',
                    'transition_duration' => null,
                    'transition_effect' => 'slide'
                ],
                'magnifier' => [
                    'enabled' => 'false',
                    'eventtype' => 'hover',
                    'fullscreenzoom' => '5',
                    'height' => null,
                    'left' => null,
                    'top' => null,
                    'width' => null
                ],
                'swatch' => [
                    'font_size' => '11px',
                    'height' => '29px',
                    'line_height' => '29px',
                    'radius' => '29px',
                    'width' => '29px'
                ],
                'css' => [
                    'tab_active_background' => '#FFFFFF',
                    'tab_background' => '#FFFFFF',
                    'tab_container_padding' => '20px 0px 20px 0px',
                    'tab_text_active_color' => '#000000',
                    'tab_text_color' => '#000000',
                    'thumbnail_border' => '#000000',
                    'page_background_color' => '#FFFFFF',
                    'page_background_color_top_v3' => '#FFFFFF',
                    'page_background_color_bottom_v3' => '#FFFFFF'
                ],
                'images' => [
                    'main_image_height' => '1000',
                    'main_image_width' => '1250',
                    'thumb_image_height' => '95',
                    'thumb_image_width' => '84'
                ]
            ],
            'weltpixel_quick_cart' => [
                'general' => [
                    'enable' => '1',
                    'open_minicart' => '1'
                ],
                'header' => [
                    'header_height' => '50px',
                    'header_background' => '#000000',
                    'header_text_color' => '#FFFFFF'
                ],
                'footer' => [
                    'subtotal_background' => '#F6F6F6',
                    'subtotal_text_color' => '#000000'
                ],
                'minicart_message' => [
                    'enable' => '0',
                    'free_shipping_integration' => '0',
                    'text_color' => '#FF0000',
                    'font_size' => '16px',
                    'content' => null,
                    'free_shipping_content' => null,
                    'custom_css' => null
                ],
                'shoppingcart_message' => [
                    'enable' => '0',
                    'free_shipping_integration' => '0',
                    'text_color' => '#FF0000',
                    'font_size' => '30px',
                    'content' => null,
                    'free_shipping_content' => null,
                    'custom_css' => null
                ]
            ],
            'weltpixel_quickview' => [
                'general' => [
                    'button_style' => 'v2',
                    'close_quickview' => '5',
                    'custom_css' => null,
                    'enable_goto_product_button' => '1',
                    'enable_product_listing' => '1',
                    'enable_shopping_checkout_product_buttons' => '1',
                    'enable_zoom' => 'false',
                    'remove_availability' => '0',
                    'remove_goto_product_button' => '0',
                    'remove_product_image' => '0',
                    'remove_product_image_thumb' => '1',
                    'remove_qty_selector' => '1',
                    'remove_short_description' => '0',
                    'remove_sku' => '0',
                    'scroll_to_top' => '1'
                ]
            ],
            'weltpixel_searchautocomplete' => [
                'general' => [
                    'enable' => '1'
                ],
                'productSearch' => [
                    'enableAutoComplete' => '1',
                    'minimalChar' => '3',
                    'noResult' => 'Sorry, no results were found.',
                    'maxItems' => '3',
                    'showImg' => '1',
                    'widthImg' => '80',
                    'showDescr' => '1',
                    'maxWordsProdDescr' => '10',
                    'showPrice' => '1',
                    'resultHeader' => '',
                    'resultFooter' => ''
                ],
                'categorySearch' => [
                    'enableCategorySearch' => '0'
                ],
                'popularSuggestions' => [
                    'enablePopularSuggestions' => '1'
                ],
                'frontendSettings' => [
                    'widthResult' => '350',
                    'templateResultsContainer' => '0',
                    'enableProductDivider' => '1',
                    'colorProductDivider' => '#CCCCCC',
                    'containerBackgroundColor' => '#FFFFFF',
                    'containerTextColor' => '#000000',
                    'titleBackgroundColor' => '#F0EFF0',
                    'titleColor' => '#000000'
                ]
            ],
            'weltpixel_smartproducttabs' => [
                'general' => [
                    'enable_smartproducttabs' => '0',
                    'block_matching_logic' => '1',
                    'attribute_smartproducttabs_tab_1' => 'Smart Product Tab 1',
                    'attribute_smartproducttabs_tab_2' => 'Smart Product Tab 2',
                    'attribute_smartproducttabs_tab_3' => 'Smart Product Tab 3'
                ]
            ],
            'weltpixel_sociallogin' => [
                'general' => [
                    'enable' => '0',
                    'show_on_cart' => '1'
                ],
                'fb' => [
                    'enable_fb' => '0'
                ],
                'google' => [
                    'enable_google' => '0'
                ],
                'amazon' => [
                    'enable_amazon' => '0'
                ]
            ],
            'weltpixel_speedoptimization' => [
                'general' => [
                    'enable' => '0'
                ],
                'js_optimization' => [
                    'enable_advanced_bundling' => '0',
                    'move_js_to_bottom' => '0',
                    'move_js_to_bottom_ignored_blocks' => null
                ],
                'css_optimization' => [
                    'css_preload' => '0',
                    'css_preload_for_all' => '0'
                ],
                'html_optimization' => [
                    'minify_html' => '0'
                ]
            ],
            'weltpixel_thankyoupage' => [
                'general' => [
                    'enable' => '1'
                ],
                'order_details' => [
                    'enable' => '1',
                    'title' => 'Thank you for your purchase',
                    'subtitle' => 'Your order number is: %1.',
                    'description' => 'We\'ll email you an order confirmation with details and tracking info',
                    'continue_shopping' => '1',
                    'sort_order' => '1'
                ],
                'google_map' => [
                    'enable' => '0',
                    'map_dragging' => '1',
                    'map_zoom' => '10',
                    'map_width' => '100%',
                    'map_height' => '320px',
                    'sort_order' => '2'
                ],
                'wesupply_integration' => [
                    'enable' => '0',
                    'sort_order' => '3'
                ],
                'order_info' => [
                    'enable' => '0',
                    'customer_info' => '1',
                    'product_info' => '1',
                    'sort_order' => '4'
                ],
                'create_account' => [
                    'enable' => '0',
                    'description' => 'You can track your order status by creating an account.',
                    'email_label' => 'Email Address',
                    'after_creation_message' => 'A letter with further instructions will be sent to your email.',
                    'sort_order' => '5'
                ],
                'newsletter_subscribe' => [
                    'enable' => '0',
                    'description' => 'Subscribe to receive Important Product Updates and occasional discounts',
                    'sort_order' => '6'
                ],
                'custom_block' => [
                    'enable' => '0',
                    'sort_order' => '7'
                ]
            ],
            'advanced' => [
                'modules_disable_output' => [
                    'Magento_Banner' => '1'
                ]
            ]
        ],
        'stores' => [
            'th' => [
                'general' => [
                    'locale' => [
                        'code' => 'th_TH'
                    ]
                ]
            ]
        ],
        'websites' => [

        ]
    ],
    'modules' => [
        'Magento_Store' => 1,
        'Magento_Config' => 1,
        'Magento_AdminAnalytics' => 1,
        'Magento_AdminNotification' => 1,
        'Magento_AdminGwsConfigurableProduct' => 1,
        'Magento_AdminGwsStaging' => 1,
        'Magento_Directory' => 1,
        'Magento_AdobeIms' => 1,
        'Magento_AdobeIoEventsClient' => 1,
        'Magento_AdobeCommerceEventsClient' => 1,
        'Magento_AdobeImsApi' => 1,
        'Magento_AdobeCommerceEventsGenerator' => 1,
        'Magento_AdobeStockAdminUi' => 1,
        'Magento_MediaGallery' => 1,
        'Magento_AdobeStockAssetApi' => 1,
        'Magento_AdobeStockClient' => 1,
        'Magento_AdobeStockClientApi' => 1,
        'Magento_AdobeStockImage' => 1,
        'Magento_Theme' => 1,
        'Magento_AdobeStockImageApi' => 1,
        'Magento_Eav' => 1,
        'Magento_Customer' => 1,
        'Magento_AdvancedPricingImportExport' => 1,
        'Magento_Rule' => 1,
        'Magento_Indexer' => 1,
        'Magento_Backend' => 1,
        'Magento_Amqp' => 1,
        'Magento_Security' => 1,
        'Magento_Variable' => 1,
        'Magento_Authorization' => 1,
        'Magento_User' => 1,
        'Magento_Cms' => 1,
        'Magento_Catalog' => 1,
        'Magento_AwsS3CustomerCustomAttributes' => 1,
        'Magento_GiftCardImportExport' => 1,
        'Magento_Widget' => 1,
        'Magento_ImportExport' => 1,
        'Magento_AdminAdobeIms' => 1,
        'Magento_Backup' => 1,
        'Magento_CatalogRule' => 1,
        'Magento_Quote' => 1,
        'Magento_SalesSequence' => 1,
        'Magento_Payment' => 1,
        'Magento_Sales' => 1,
        'Magento_SalesRule' => 1,
        'Magento_Bundle' => 1,
        'Magento_GraphQl' => 1,
        'Magento_BundleImportExport' => 1,
        'Magento_BundleImportExportStaging' => 1,
        'Magento_CatalogInventory' => 1,
        'Magento_CacheInvalidate' => 1,
        'Magento_Checkout' => 1,
        'Magento_CardinalCommerce' => 1,
        'Magento_AdvancedCatalog' => 1,
        'Magento_Integration' => 1,
        'Magento_CmsGraphQl' => 1,
        'Magento_EavGraphQl' => 1,
        'Magento_Search' => 1,
        'Magento_SalesArchive' => 1,
        'Magento_CatalogImportExport' => 1,
        'Magento_CatalogImportExportStaging' => 1,
        'Magento_StoreGraphQl' => 1,
        'Magento_CatalogInventoryGraphQl' => 1,
        'Magento_CatalogSearch' => 1,
        'Magento_CatalogPageBuilderAnalytics' => 1,
        'Magento_CatalogPageBuilderAnalyticsStaging' => 1,
        'Magento_CatalogUrlRewrite' => 1,
        'Magento_Ui' => 1,
        'Magento_CustomerCustomAttributes' => 1,
        'Magento_Msrp' => 1,
        'Magento_CatalogRuleGraphQl' => 1,
        'Magento_Staging' => 1,
        'Magento_Captcha' => 1,
        'Magento_Downloadable' => 1,
        'Magento_StagingGraphQl' => 1,
        'Magento_GiftCard' => 1,
        'Magento_Wishlist' => 1,
        'Magento_CatalogGraphQl' => 1,
        'Magento_MediaStorage' => 1,
        'Magento_Robots' => 1,
        'Magento_ConfigurableProduct' => 1,
        'Magento_CheckoutAddressSearch' => 1,
        'Magento_GiftRegistry' => 1,
        'Magento_CheckoutAgreements' => 1,
        'Magento_CheckoutAgreementsGraphQl' => 1,
        'Magento_CheckoutStaging' => 1,
        'Magento_CloudComponents' => 1,
        'Magento_MediaGalleryUi' => 1,
        'Magento_CatalogCmsGraphQl' => 1,
        'Magento_CmsPageBuilderAnalytics' => 1,
        'Magento_CmsPageBuilderAnalyticsStaging' => 1,
        'Magento_VersionsCms' => 1,
        'Magento_CmsUrlRewrite' => 1,
        'Magento_CmsUrlRewriteGraphQl' => 1,
        'Magento_CompareListGraphQl' => 1,
        'Magento_TwoFactorAuth' => 0,
        'Magento_ConfigurableImportExport' => 1,
        'Magento_CatalogRuleConfigurable' => 1,
        'Magento_QuoteGraphQl' => 1,
        'Magento_ConfigurableProductSales' => 1,
        'Magento_PageCache' => 1,
        'Magento_Contact' => 1,
        'Magento_Cookie' => 1,
        'Magento_Cron' => 1,
        'Magento_Csp' => 1,
        'Magento_CurrencySymbol' => 1,
        'Magento_CustomAttributeManagement' => 1,
        'Magento_AdvancedCheckout' => 1,
        'Magento_Analytics' => 1,
        'Magento_CustomerBalance' => 1,
        'Magento_CustomerBalanceGraphQl' => 1,
        'Magento_CustomerSegment' => 1,
        'Magento_DownloadableGraphQl' => 1,
        'Magento_CustomerFinance' => 1,
        'Magento_CustomerGraphQl' => 1,
        'Magento_CustomerImportExport' => 1,
        'Magento_CatalogWidget' => 1,
        'Magento_DeferredTotalCalculating' => 1,
        'Magento_Deploy' => 1,
        'Magento_Developer' => 1,
        'Magento_Dhl' => 1,
        'Magento_BundleGraphQl' => 1,
        'Magento_DirectoryGraphQl' => 1,
        'Magento_ProductAlert' => 1,
        'Magento_CustomerDownloadableGraphQl' => 1,
        'Magento_DownloadableImportExport' => 1,
        'Magento_TargetRule' => 1,
        'Magento_AdvancedRule' => 1,
        'Magento_CatalogCustomerGraphQl' => 1,
        'Magento_AdvancedSearch' => 1,
        'Magento_Elasticsearch' => 1,
        'Magento_WebsiteRestriction' => 1,
        'Magento_ElasticsearchCatalogPermissionsGraphQl' => 1,
        'Magento_Email' => 1,
        'Magento_EncryptionKey' => 1,
        'Magento_Enterprise' => 1,
        'Magento_Fedex' => 1,
        'Magento_Tax' => 1,
        'Magento_GiftCardAccount' => 1,
        'Magento_GiftCardAccountGraphQl' => 1,
        'Magento_WishlistGraphQl' => 1,
        'Magento_Sitemap' => 1,
        'Magento_CatalogEvent' => 1,
        'Magento_GiftMessage' => 1,
        'Magento_GiftMessageGraphQl' => 1,
        'Magento_GiftMessageStaging' => 1,
        'Magento_UrlRewrite' => 1,
        'Magento_GiftRegistryGraphQl' => 1,
        'Magento_GiftWrapping' => 1,
        'Magento_GiftWrappingGraphQl' => 1,
        'Magento_GiftWrappingStaging' => 1,
        'Magento_GoogleAdwords' => 1,
        'Magento_GoogleAnalytics' => 1,
        'Magento_GoogleGtag' => 1,
        'Magento_GoogleOptimizer' => 1,
        'Magento_GoogleOptimizerStaging' => 1,
        'Magento_GoogleShoppingAds' => 1,
        'Magento_Banner' => 1,
        'Magento_AsyncOrder' => 1,
        'Magento_GraphQlCache' => 1,
        'Magento_GroupedProduct' => 1,
        'Magento_GroupedImportExport' => 1,
        'Magento_GroupedCatalogInventory' => 1,
        'Magento_GroupedProductGraphQl' => 1,
        'Magento_VisualMerchandiser' => 1,
        'Magento_ImportCsv' => 1,
        'Magento_ImportCsvApi' => 1,
        'Magento_RemoteStorage' => 1,
        'Magento_CatalogPermissions' => 1,
        'Magento_InstantPurchase' => 1,
        'Magento_CatalogAnalytics' => 1,
        'Magento_Inventory' => 0,
        'Magento_InventoryAdminUi' => 0,
        'Magento_InventoryAdvancedCheckout' => 0,
        'Magento_InventoryApi' => 0,
        'Magento_InventoryBundleImportExport' => 0,
        'Magento_InventoryBundleProduct' => 0,
        'Magento_InventoryBundleProductAdminUi' => 0,
        'Magento_InventoryBundleProductIndexer' => 0,
        'Magento_InventoryCatalog' => 0,
        'Magento_InventorySales' => 0,
        'Magento_InventoryCatalogAdminUi' => 0,
        'Magento_InventoryCatalogApi' => 0,
        'Magento_InventoryCatalogFrontendUi' => 0,
        'Magento_InventoryCatalogSearch' => 0,
        'Magento_InventoryCatalogSearchBundleProduct' => 0,
        'Magento_InventoryCatalogSearchConfigurableProduct' => 0,
        'Magento_ConfigurableProductGraphQl' => 1,
        'Magento_InventoryConfigurableProduct' => 0,
        'Magento_InventoryConfigurableProductFrontendUi' => 0,
        'Magento_InventoryConfigurableProductIndexer' => 0,
        'Magento_InventoryConfiguration' => 0,
        'Magento_InventoryConfigurationApi' => 0,
        'Magento_InventoryDistanceBasedSourceSelection' => 0,
        'Magento_InventoryDistanceBasedSourceSelectionAdminUi' => 0,
        'Magento_InventoryDistanceBasedSourceSelectionApi' => 0,
        'Magento_InventoryElasticsearch' => 0,
        'Magento_InventoryExportStockApi' => 0,
        'Magento_InventoryIndexer' => 0,
        'Magento_InventorySalesApi' => 0,
        'Magento_InventoryGroupedProduct' => 0,
        'Magento_InventoryGroupedProductAdminUi' => 0,
        'Magento_InventoryGroupedProductIndexer' => 0,
        'Magento_InventoryImportExport' => 0,
        'Magento_InventoryInStorePickupApi' => 0,
        'Magento_InventoryInStorePickupAdminUi' => 0,
        'Magento_InventorySourceSelectionApi' => 0,
        'Magento_InventoryInStorePickup' => 0,
        'Magento_InventoryInStorePickupGraphQl' => 0,
        'Magento_Shipping' => 1,
        'Magento_InventoryInStorePickupShippingApi' => 0,
        'Magento_InventoryInStorePickupQuoteGraphQl' => 0,
        'Magento_InventoryInStorePickupSales' => 0,
        'Magento_InventoryInStorePickupSalesApi' => 0,
        'Magento_InventoryInStorePickupQuote' => 0,
        'Magento_InventoryInStorePickupShipping' => 0,
        'Magento_InventoryInStorePickupShippingAdminUi' => 0,
        'Magento_Multishipping' => 1,
        'Magento_Webapi' => 1,
        'Magento_InventoryCache' => 0,
        'Magento_InventoryLowQuantityNotification' => 0,
        'Magento_Reports' => 1,
        'Magento_InventoryLowQuantityNotificationApi' => 0,
        'Magento_InventoryMultiDimensionalIndexerApi' => 0,
        'Magento_InventoryProductAlert' => 0,
        'Magento_InventoryQuoteGraphQl' => 0,
        'Magento_InventoryRequisitionList' => 0,
        'Magento_InventoryReservations' => 0,
        'Magento_InventoryReservationCli' => 0,
        'Magento_InventoryReservationsApi' => 0,
        'Magento_InventoryExportStock' => 0,
        'Magento_InventorySalesAdminUi' => 0,
        'Magento_InventoryGraphQl' => 0,
        'Magento_InventorySalesAsyncOrder' => 0,
        'Magento_InventorySalesFrontendUi' => 0,
        'Magento_InventorySetupFixtureGenerator' => 0,
        'Magento_InventoryShipping' => 0,
        'Magento_InventoryShippingAdminUi' => 0,
        'Magento_InventorySourceDeductionApi' => 0,
        'Magento_InventorySourceSelection' => 0,
        'Magento_InventoryInStorePickupFrontend' => 0,
        'Magento_InventorySwatchesFrontendUi' => 0,
        'Magento_InventoryVisualMerchandiser' => 0,
        'Magento_InventoryWishlist' => 0,
        'Magento_Invitation' => 1,
        'Magento_JwtFrameworkAdapter' => 1,
        'Magento_JwtUserToken' => 1,
        'Magento_LayeredNavigation' => 1,
        'Magento_LayeredNavigationStaging' => 1,
        'Magento_Logging' => 1,
        'Magento_LoginAsCustomer' => 1,
        'Magento_LoginAsCustomerAdminUi' => 1,
        'Magento_LoginAsCustomerApi' => 1,
        'Magento_LoginAsCustomerAssistance' => 1,
        'Magento_LoginAsCustomerFrontendUi' => 1,
        'Magento_LoginAsCustomerGraphQl' => 1,
        'Magento_LoginAsCustomerLog' => 1,
        'Magento_LoginAsCustomerLogging' => 1,
        'Magento_LoginAsCustomerPageCache' => 1,
        'Magento_LoginAsCustomerQuote' => 1,
        'Magento_LoginAsCustomerSales' => 1,
        'Magento_LoginAsCustomerWebsiteRestriction' => 1,
        'Magento_Marketplace' => 1,
        'Magento_MediaContent' => 1,
        'Magento_MediaContentApi' => 1,
        'Magento_MediaContentCatalog' => 1,
        'Magento_MediaContentCatalogStaging' => 1,
        'Magento_MediaContentCms' => 1,
        'Magento_MediaContentSynchronization' => 1,
        'Magento_MediaContentSynchronizationApi' => 1,
        'Magento_MediaContentSynchronizationCatalog' => 1,
        'Magento_MediaContentSynchronizationCms' => 1,
        'Magento_AdobeStockAsset' => 1,
        'Magento_MediaGalleryApi' => 1,
        'Magento_MediaGalleryCatalog' => 1,
        'Magento_MediaGalleryCatalogIntegration' => 1,
        'Magento_MediaGalleryCatalogUi' => 1,
        'Magento_MediaGalleryCmsUi' => 1,
        'Magento_MediaGalleryIntegration' => 1,
        'Magento_MediaGalleryMetadata' => 1,
        'Magento_MediaGalleryMetadataApi' => 1,
        'Magento_MediaGalleryRenditions' => 1,
        'Magento_MediaGalleryRenditionsApi' => 1,
        'Magento_MediaGallerySynchronization' => 1,
        'Magento_MediaGallerySynchronizationApi' => 1,
        'Magento_MediaGallerySynchronizationMetadata' => 1,
        'Magento_AdobeStockImageAdminUi' => 1,
        'Magento_MediaGalleryUiApi' => 1,
        'Magento_AwsS3' => 1,
        'Magento_MessageQueue' => 1,
        'Magento_Weee' => 1,
        'Magento_MsrpConfigurableProduct' => 1,
        'Magento_MsrpGroupedProduct' => 1,
        'Magento_MsrpStaging' => 1,
        'Magento_MultipleWishlist' => 1,
        'Magento_SalesGraphQl' => 1,
        'Magento_InventoryInStorePickupMultishipping' => 0,
        'Magento_MysqlMq' => 1,
        'Magento_NewRelicReporting' => 1,
        'Magento_Newsletter' => 1,
        'Magento_NewsletterGraphQl' => 1,
        'Magento_OfflinePayments' => 1,
        'Magento_OfflineShipping' => 1,
        'Magento_OpenSearch' => 1,
        'Magento_BannerCustomerSegment' => 1,
        'Magento_PageBuilder' => 1,
        'Magento_AdminGws' => 1,
        'Magento_PageBuilderAnalytics' => 1,
        'Magento_CatalogStaging' => 1,
        'Magento_PageBuilderAdminGwsAdminUi' => 1,
        'Magento_PaymentGraphQl' => 1,
        'Magento_PaymentStaging' => 1,
        'Magento_Vault' => 1,
        'Magento_Paypal' => 1,
        'Magento_PaypalGraphQl' => 1,
        'Magento_PaypalOnBoarding' => 1,
        'Magento_Persistent' => 1,
        'Magento_PersistentHistory' => 1,
        'Magento_PricePermissions' => 1,
        'Magento_DownloadableStaging' => 1,
        'Magento_ProductVideo' => 1,
        'Magento_ProductVideoStaging' => 1,
        'Magento_PromotionPermissions' => 1,
        'Magento_QuickCheckout' => 1,
        'Magento_QuickCheckoutAdminPanel' => 1,
        'Magento_BannerGraphQl' => 1,
        'Magento_QuoteAnalytics' => 1,
        'Magento_QuoteBundleOptions' => 1,
        'Magento_QuoteConfigurableOptions' => 1,
        'Magento_QuoteDownloadableLinks' => 1,
        'Magento_QuoteGiftCardOptions' => 1,
        'Magento_InventoryConfigurableProductAdminUi' => 0,
        'Magento_QuoteStaging' => 1,
        'Magento_ReCaptchaAdminUi' => 1,
        'Magento_ReCaptchaCheckout' => 1,
        'Magento_ReCaptchaCheckoutSalesRule' => 1,
        'Magento_ReCaptchaContact' => 1,
        'Magento_ReCaptchaCustomer' => 1,
        'Magento_ReCaptchaFrontendUi' => 1,
        'Magento_ReCaptchaGiftCard' => 1,
        'Magento_ReCaptchaInvitation' => 1,
        'Magento_ReCaptchaMigration' => 1,
        'Magento_ReCaptchaMultipleWishlist' => 1,
        'Magento_ReCaptchaNewsletter' => 1,
        'Magento_ReCaptchaPaypal' => 1,
        'Magento_ReCaptchaReview' => 1,
        'Magento_ReCaptchaSendFriend' => 1,
        'Magento_ReCaptchaStorePickup' => 1,
        'Magento_ReCaptchaUi' => 1,
        'Magento_ReCaptchaUser' => 1,
        'Magento_ReCaptchaValidation' => 1,
        'Magento_ReCaptchaValidationApi' => 1,
        'Magento_ReCaptchaVersion2Checkbox' => 1,
        'Magento_ReCaptchaVersion2Invisible' => 1,
        'Magento_ReCaptchaVersion3Invisible' => 1,
        'Magento_ReCaptchaWebapiApi' => 1,
        'Magento_ReCaptchaWebapiGraphQl' => 1,
        'Magento_ReCaptchaWebapiRest' => 1,
        'Magento_ReCaptchaWebapiUi' => 1,
        'Magento_RelatedProductGraphQl' => 1,
        'Magento_ReleaseNotification' => 1,
        'Magento_Reminder' => 1,
        'Magento_AwsS3GiftCardImportExport' => 1,
        'Magento_RemoteStorageCommerce' => 1,
        'Magento_InventoryLowQuantityNotificationAdminUi' => 0,
        'Magento_RequireJs' => 1,
        'Magento_ResourceConnections' => 1,
        'Magento_Review' => 1,
        'Magento_ReviewAnalytics' => 1,
        'Magento_ReviewGraphQl' => 1,
        'Magento_ReviewStaging' => 1,
        'Magento_Reward' => 1,
        'Magento_RewardGraphQl' => 1,
        'Magento_AdvancedSalesRule' => 1,
        'Magento_Rma' => 1,
        'Magento_RmaGraphQl' => 1,
        'Magento_RmaStaging' => 1,
        'Magento_ScheduledImportExport' => 1,
        'Magento_Rss' => 1,
        'Magento_SalesRuleStaging' => 1,
        'Magento_BannerPageBuilderAnalytics' => 1,
        'Magento_SalesAnalytics' => 1,
        'Magento_AsyncOrderGraphQl' => 1,
        'Magento_MultipleWishlistGraphQl' => 1,
        'Magento_SalesInventory' => 1,
        'Magento_BannerStaging' => 1,
        'Magento_RewardStaging' => 1,
        'Magento_BannerPageBuilder' => 1,
        'Magento_SampleData' => 1,
        'Magento_ScalableCheckout' => 1,
        'Magento_ScalableInventory' => 1,
        'Magento_ScalableOms' => 1,
        'Magento_AwsS3ScheduledImportExport' => 1,
        'Magento_Elasticsearch7' => 1,
        'Magento_SearchStaging' => 1,
        'Magento_CustomerAnalytics' => 1,
        'Magento_Securitytxt' => 1,
        'Magento_SendFriend' => 1,
        'Magento_SendFriendGraphQl' => 1,
        'Magento_InventoryInStorePickupSalesAdminUi' => 0,
        'Magento_AwsS3PageBuilder' => 1,
        'Magento_CatalogRuleStaging' => 1,
        'Magento_CatalogStagingGraphQl' => 1,
        'Magento_StagingPageBuilder' => 1,
        'Magento_CatalogPermissionsGraphQl' => 1,
        'Magento_UrlRewriteGraphQl' => 1,
        'Magento_Support' => 1,
        'Magento_Swagger' => 0,
        'Magento_SwaggerWebapi' => 0,
        'Magento_SwaggerWebapiAsync' => 0,
        'Magento_Swat' => 1,
        'Magento_Swatches' => 1,
        'Magento_SwatchesGraphQl' => 1,
        'Magento_SwatchesLayeredNavigation' => 1,
        'Magento_CatalogInventoryStaging' => 1,
        'Magento_TargetRuleGraphQl' => 1,
        'Magento_GiftCardStaging' => 1,
        'Magento_TaxGraphQl' => 1,
        'Magento_TaxImportExport' => 1,
        'Magento_GoogleTagManager' => 1,
        'Magento_ThemeGraphQl' => 1,
        'Magento_Translation' => 1,
        'Magento_AdminAdobeImsTwoFactorAuth' => 1,
        'Magento_ElasticsearchCatalogPermissions' => 1,
        'Magento_Ups' => 1,
        'Magento_CatalogUrlRewriteStaging' => 1,
        'Magento_CatalogUrlRewriteGraphQl' => 1,
        'Magento_AsynchronousOperations' => 1,
        'Magento_Usps' => 1,
        'Magento_GroupedProductStaging' => 1,
        'Magento_PaypalCaptcha' => 1,
        'Magento_VaultGraphQl' => 1,
        'Magento_Version' => 0,
        'Magento_CmsStaging' => 1,
        'Magento_VersionsCmsPageCache' => 1,
        'Magento_VersionsCmsUrlRewrite' => 1,
        'Magento_VersionsCmsUrlRewriteGraphQl' => 1,
        'Magento_BundleStaging' => 1,
        'Magento_InventoryInStorePickupWebapiExtension' => 0,
        'Magento_WebapiAsync' => 1,
        'Magento_WebapiSecurity' => 1,
        'Magento_ConfigurableProductStaging' => 1,
        'Magento_CatalogStagingPageBuilder' => 1,
        'Magento_WeeeGraphQl' => 1,
        'Magento_WeeeStaging' => 1,
        'Magento_PageBuilderAdminAnalytics' => 1,
        'Magento_CheckoutAddressSearchGiftRegistry' => 1,
        'Magento_WishlistAnalytics' => 1,
        'Magento_WishlistGiftCard' => 1,
        'Magento_WishlistGiftCardGraphQl' => 1,
        'Magento_GiftCardGraphQl' => 1,
        'Acommerce_All' => 1,
        'Acommerce_CurlProxy' => 1,
        'Acommerce_Sales' => 1,
        'Acommerce_Ccpp' => 1,
        'Amasty_Base' => 1,
        'Amasty_Geoip' => 1,
        'Amasty_BlogProSubscriptionPackage' => 1,
        'Amasty_LibSwiperJs' => 1,
        'Amasty_CronScheduleList' => 1,
        'Amasty_EmailUnsubscribe' => 1,
        'Amasty_Faq' => 1,
        'Amasty_FaqSubscriptionPackage' => 1,
        'Amasty_Feed' => 1,
        'Amasty_Gdpr' => 1,
        'Amasty_GdprCookie' => 1,
        'Amasty_GdprFrontendUi' => 1,
        'Amasty_GdprPro' => 1,
        'Amasty_Blog' => 1,
        'Amasty_ShopbyBase' => 1,
        'Amasty_ImprovedLayeredNavigationCloudPremium' => 1,
        'Amasty_InvisibleCaptcha' => 0,
        'Amasty_ShopbyBrand' => 1,
        'Amasty_Mage24Fix' => 1,
        'Amasty_MegaMenuLite' => 1,
        'Amasty_MegaMenu' => 1,
        'Amasty_MegaMenuPremium' => 1,
        'Amasty_MegaMenuPackagePremium' => 1,
        'Amasty_MegaMenuItemsTree' => 1,
        'Amasty_Scroll' => 1,
        'Amasty_ShopByQuickConfig' => 1,
        'Amasty_Shopby' => 1,
        'Amasty_ShopbySeo' => 1,
        'Amasty_CPS' => 1,
        'Amasty_ShopbyBrandGraphQl' => 1,
        'Amasty_ShopbyFilterAnalytics' => 1,
        'Amasty_ShopbyGraphQl' => 1,
        'Amasty_ShopbyPage' => 1,
        'Amasty_ShopbyPremium' => 1,
        'Amasty_ShopbyPro' => 0,
        'Amasty_GroupedOptions' => 1,
        'Amasty_SocialLogin' => 1,
        'Amasty_SocialLoginAppleId' => 1,
        'Amasty_SocialLoginGraphQl' => 1,
        'Amasty_SocialLoginLite' => 1,
        'Amasty_VisualMerchUi' => 1,
        'Amastyfixed_GDPR' => 1,
        'Bss_FacebookPixel' => 1,
        'Bss_Popup' => 1,
        'Fastly_Cdn' => 1,
        'Firebear_ImportExport' => 1,
        'Firebear_ImportExportMsi' => 1,
        'Firebear_PlatformFeeds' => 1,
        'Klaviyo_Reclaim' => 1,
        'Mageplaza_Core' => 1,
        'Mageplaza_Blog' => 0,
        'Mageplaza_OrderAttributes' => 1,
        'OlegKoval_RegenerateUrlRewrites' => 1,
        'OuterEdge_Hreflang' => 1,
        'OuterEdge_StructuredData' => 1,
        'PPGroup_AccessTrade' => 1,
        'WeltPixel_Backend' => 1,
        'PPGroup_Antispam' => 1,
        'PPGroup_Blog' => 0,
        'PPGroup_Catalog' => 1,
        'PPGroup_CatalogRule' => 1,
        'PPGroup_Ccpp' => 1,
        'WeltPixel_QuickCart' => 1,
        'PPGroup_CloudComponents' => 1,
        'PPGroup_Cms' => 1,
        'PPGroup_ConfigurableProduct' => 1,
        'PPGroup_Core' => 1,
        'PPGroup_Csp' => 1,
        'PPGroup_CurlProxy' => 1,
        'PPGroup_CustomTableData' => 1,
        'PPGroup_Gdpr' => 1,
        'PPGroup_ImportExport' => 1,
        'PPGroup_Integration' => 1,
        'PPGroup_Klaviyo' => 1,
        'WeltPixel_LayeredNavigation' => 0,
        'PPGroup_LogRotation' => 1,
        'WeltPixel_NavigationLinks' => 1,
        'PPGroup_Newsletter' => 1,
        'PPGroup_NewsletterPopup' => 1,
        'PPGroup_OrderAttributes' => 1,
        'PPGroup_Promocode' => 1,
        'PPGroup_Sales' => 1,
        'PPGroup_Shipping' => 1,
        'PPGroup_UrlRewrite' => 1,
        'PPGroup_ZipcodeSuggestion' => 1,
        'PPGroup_ZipcodeUpdate' => 1,
        'PayPal_Braintree' => 0,
        'PayPal_BraintreeGraphQl' => 1,
        'Temando_ShippingRemover' => 1,
        'Veriteworks_CookieFix' => 1,
        'WeSupply_Toolbox' => 1,
        'WeltPixelFixed_Backend' => 1,
        'WeltPixel_AdvanceCategorySorting' => 1,
        'WeltPixel_AdvancedWishlist' => 1,
        'WeltPixel_AjaxInfiniteScroll' => 0,
        'PPGroup_AdvancedWishlist' => 1,
        'WeltPixel_FrontendOptions' => 1,
        'WeltPixel_CmsBlockScheduler' => 1,
        'WeltPixel_Command' => 1,
        'WeltPixel_CustomFooter' => 1,
        'WeltPixel_CustomHeader' => 1,
        'WeltPixel_DesignElements' => 1,
        'WeltPixel_EnhancedEmail' => 1,
        'WeltPixel_LazyLoading' => 1,
        'WeltPixel_FullPageScroll' => 1,
        'WeltPixel_GA4' => 1,
        'WeltPixel_GoogleCards' => 1,
        'WeltPixel_GoogleTagManager' => 1,
        'WeltPixel_HyvaGA4' => 1,
        'WeltPixel_InstagramWidget' => 1,
        'PPGroup_LayeredNavigation' => 0,
        'WeltPixel_MobileDetect' => 1,
        'WeltPixel_OwlCarouselSlider' => 1,
        'WeltPixel_Multistore' => 1,
        'PPGroup_NavigationLinks' => 1,
        'WeltPixel_Newsletter' => 1,
        'WeltPixel_CategoryPage' => 1,
        'WeltPixel_ProductLabels' => 1,
        'WeltPixel_ProductPage' => 1,
        'PPGroup_Checkout' => 1,
        'WeltPixel_Quickview' => 1,
        'WeltPixel_RecentlyViewedBar' => 1,
        'WeltPixel_ReviewsWidget' => 1,
        'WeltPixel_SampleData' => 0,
        'WeltPixel_SearchAutoComplete' => 1,
        'WeltPixel_Sitemap' => 1,
        'WeltPixel_SmartProductTabs' => 1,
        'WeltPixel_SocialLogin' => 0,
        'WeltPixel_SpeedOptimization' => 0,
        'WeltPixel_ThankYouPage' => 1,
        'WeltPixel_TitleRewrite' => 1,
        'WeltPixel_UserProfile' => 1
    ],
    'admin_user' => [
        'locale' => [
            'code' => [
                'en_US'
            ]
        ]
    ]
];
