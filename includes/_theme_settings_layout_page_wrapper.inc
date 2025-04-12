<?php

/**
 * @file
 * Solo Theme.
 *
 * Filename:     solo.theme
 * Website:      http://www.flashwebcenter.com
 * Description:  template
 * Author:       Alaa Haddad http://www.alaahaddad.com.
 */

// Page wrapper settings.
$form['solo_settings']['settings_page_wrapper'] = [
  '#type' => 'details',
  '#title' => t("<strong class='status'>Page Wrapper</strong> Settings - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description'   => t("The Page Wrapper serves as the encompassing div for all regions within the site. Any settings applied to it will have a global effect. It's important to note that the CSS should probably be provided by a trusted user, such as an admin or developer."),
];

$form['solo_settings']['settings_page_wrapper']['classes_page_wrapper'] = [
  '#type' => 'textfield',
  '#title' => t('CSS Classes Only'),
  '#description' => t("Here are the predefined CSS classes you can use:<br>
- dis-box-shadow: Removes the shadow effect from elements, creating a flat appearance.<br>
- dis-border: Eliminates borders from elements, which can be useful for a minimalist design.<br>
- dis-border-radius: Sets the border-radius to 0, removing rounded corners for a sharp-edged look.<br>
- dis-margin: Resets margins to 0, helping elements to align tightly without external spacing.<br>
- dis-padding: Resets padding to 0, ensuring elements have no internal spacing.<br>
- image-auto: Sets images within links to display at their original size.<br>
- dis-text-transform: Prevents text transformations, maintaining the original text case and style.<br>
- dis-anim-link: Disables bottom border hover animations on menu links.<br>
- dis-anim-submit: Stops the wiggle animations on the submit button.<br>
- solo-text-left: Forces text alignment to the left, overriding existing styles.<br>
- solo-text-center: Forces text alignment to the center, overriding existing styles.<br>
- solo-text-right: Forces text alignment to the right, overriding existing styles.<br>
- solo-display-none: Completely hides the element from view.<br>
- solo-visually-hidden: Hides content visually but remains accessible for screen readers.<br>
- solo-no-wrap: Prevents text from wrapping to the next line.<br>
- solo-break-word: Allows long words to break and wrap within the element.<br>
- solo-truncate: Truncates text with ellipsis when it exceeds the element's width.<br>
- solo-align-left: Aligns block-level elements to the left using auto margins.<br>
- solo-align-center: Centers block-level elements horizontally using auto margins.<br>
- solo-align-right: Aligns block-level elements to the right using auto margins.<br>
- solo-overflow-hidden: Hides overflow content within the element.<br>
- solo-overflow-auto: Adds a scroll bar if content overflows the element.<br>
- solo-mobile-hide: Hides the element on smaller screen sizes (mobile).<br>
- solo-desktop-hide: Hides the element on larger screen sizes (desktop).<br><br>

<b>Usage Notes:</b><br>
These utility classes provide flexible control over element styles without writing custom CSS. They are designed for isolated adjustments, block classes, or specific content overrides.<br><br>

<b>Important:</b><br>
- Alignment classes like 'solo-text-left', 'solo-text-center', and 'solo-text-right' apply <i>text-align</i> to all nested content within the target element.<br>
- Classes like 'solo-align-left', 'solo-align-center', and 'solo-align-right' use auto margins for block-level alignment.<br>
- Many classes apply <i>!important</i> to enforce the style, which may override other theme or module styles.<br><br>

Use these classes thoughtfully to maintain visual consistency and avoid unintended layout issues."),
  '#default_value' => theme_get_setting('classes_page_wrapper'),
];

$form['solo_settings']['settings_page_wrapper']['opacity_page_wrapper'] = [
  '#type' => 'checkbox',
  '#title' => t('Enable Smooth Page Load Animation'),
  '#description' => t('Activates a smooth transition effect for the entire page when it is loaded, enhancing the user experience by gradually increasing the page’s visibility from transparent to fully opaque. Ideal for creating a more engaging and visually appealing website entrance.'),

  '#default_value' => theme_get_setting('opacity_page_wrapper'),
];

foreach ($attributes as $attribute_key => $attribute_label) {
  $region = 'page_wrapper';
  $label = 'Page Wrapper';
  $form['solo_settings']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
}
