/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal, once) => {
    'use strict';


    Drupal.behaviors.soloFixedLoginBlock = {
        attach: function(settings) {

            const loginBlock = document.getElementById('popup-login-block');
            const loginBlockInner = document.querySelector('.popup-login-block-inner');
            const openBlock = document.querySelector('.login-block-button-open-inner');
            const closeBlock = document.querySelector('.login-button-close');

            const closeSearchHandler = () => {

                loginBlock.style.display = 'none';
                loginBlock.classList.remove('toggled');
            }

            const openSearchHandler = () => {
                loginBlock.style.display = "block";
                loginBlock.classList.add('toggled');

            }
            closeBlock?.addEventListener('click', closeSearchHandler);
            openBlock?.addEventListener('click', openSearchHandler);

            //click any where to close any submenu.
            document.addEventListener('click', (event) => {
                if (event.target == loginBlock) {
                    closeSearchHandler();
                }
            });

        }
    };

})(Drupal, once);
