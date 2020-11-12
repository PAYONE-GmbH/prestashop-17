/*
 * PAYONE Prestashop Connector is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PAYONE Prestashop Connector is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with PAYONE Prestashop Connector. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * @author    patworx multimedia GmbH <service@patworx.de>
 * @copyright 2003 - 2020 BS PAYONE GmbH
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      http://www.payone.de
 */

$(document).ready(function(){
    /**
     * Disable submit button
     */
    function fcPayoneDisableSubmit() {
        $('#submitOrder').css('opacity',0.2).attr('disabled',true);
    }

    /**
     * Enable submit button
     */
    function fcPayoneEnableSubmit() {
        $('#submitOrder').css('opacity',1).attr('disabled',false);
    }

    /**
     * Check term checkbox
     * triggers submit button enable/disable
     * @param oTermCheckbox
     */
    function fcPayoneCheckTerms(oTermCheckbox) {
        var sState = oTermCheckbox.prop('checked');
        if ( sState == true || sState == 1 || sState == 'checked' ) {
            fcPayoneEnableSubmit();
        } else {
            fcPayoneDisableSubmit();
        }
    }
    if( $('#cgv').length > 0 ) {
        fcPayoneCheckTerms($('#cgv'));
        $('#cgv').on('change', function(){
            fcPayoneCheckTerms($(this));
        });

        //terms popup
        if (!!$.prototype.fancybox) {
            $("a.iframe").fancybox({
                'type': 'iframe',
                'width': 600,
                'height': 600
            });
        }
    }
});
