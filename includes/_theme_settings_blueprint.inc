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

/**
 * The theme settings.
 */

$form['solo_settings']['settings_blueprint'] = [
  '#type' => 'details',
  '#title' => t("<strong class='purple'>Blueprint for The Theme Regions</strong> - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description' => t("

<strong>This is a graphical representation illustrating the positioning of each region on the page.</strong>

<table cellpadding='8' style='width: 100%;  color: LightSlateGray; font-size: 16px; font-family: Helvetica; border-collapse: collapse; border:3px solid LightBlue'>
  <tbody>
    <tr>
      <td style='text-align: center;'>
      <h1 class='error'>Page Wrapper</h1>
      <p>You can have the css class applied to one region or the whole site.</p>
      <table cellpadding='6' style='width: 100%;'>
        <tbody>

          <tr>
            <td>
            <table cellpadding='5' style='width: 100%; border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Sidebar Main Menu</h1>
                  <p>Visible on all pages</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>

          <tr>
            <td>
            <table cellpadding='5' style='width: 100%; border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Fixed Search Block</h1>
                  <p>Visible on all pages</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>


          <tr>
            <td>
            <table cellpadding='5' style='width: 100%; border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Fixed Login Block Form</h1>
                  <p>Visible on all pages</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>


          <tr>
            <td>
            <table cellpadding='5' style='width: 100%; border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Header</h1>
                  <p>Visible on all pages</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>



          <tr>
            <td>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Main Navigation - Horizontal</h1>
                  <p>Visible on all pages</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>
          <tr>
            <td>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Welcome Text</h1>
                  <p>Visible on front page only</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>
          <tr>
            <td>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Highlighted</h1>
                  <p>Visible on all pages</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>
          <tr>
            <td>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>System Messages</h1>
                  <p>Visible on all pages</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>
          <tr>
            <td style='text-align: center;'>
            <h1 class='status'>Top Container Wrapper</h1>
            <p>Optional</p>
            <p>Top Container (Contains three regions).</p>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Top First</h2>

                  </td>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Top Second</a></h2>

                  </td>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Top Third</a></h2>

                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>
          <tr>
            <td>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Page Title</h1>
                  <p>Visible on all pages except front page</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>

          <tr>
            <td>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Breadcrumb</h1>
                  <p>Visible on all pages except front page</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>

          <tr>
            <td style='text-align: center;'>
            <h1 class='status'>Main Container Wrapper</h1>
            <p>Visible on all pages</p>
            <p>Main Container (Contains three regions).</p>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Left SideBar</h2>
                  </td>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Solo Content</h2>
                  </td>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Right SideBar</h2>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>
          <tr>
            <td style='text-align: center;'>
            <h1 class='status'>Bottom Container Wrapper</h1>
            <p>Optional</p>
            <p>Bottom Container (Contain four regions).</p>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Bottom First</h2>

                  </td>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Bottom Second</h2>

                  </td>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Bottom Third</h2>

                  </td>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Bottom Fourth</h2>

                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>
          <tr>
            <td style='text-align: center;'>
            <h1 class='status'>Footer Container Wrapper</h1>
            <p>Optional</p>
            <p>Footer Container (Contains three regions).</p>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Footer First</h2>

                  </td>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Footer Second</h2>

                  </td>
                  <td style='text-align: center; border:1px solid LightBlue;'>
                  <h2 class='status'>Footer Third</h2>

                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>
          <tr>
            <td>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Footer Menu</h1>
                  <p>Visible on all pages</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>
          <tr>
            <td>
            <table cellpadding='5' style='width: 100%;border-collapse: collapse; border:3px solid LightBlue'>
              <tbody>
                <tr>
                  <td style='text-align: center;'>
                  <h1 class='info'>Copyright</h1>
                  <p>Visible on all pages</p>
                  </td>
                </tr>
              </tbody>
            </table>
            </td>
          </tr>
        </tbody>
      </table>
      </td>
    </tr>
  </tbody>
</table>"),
];
