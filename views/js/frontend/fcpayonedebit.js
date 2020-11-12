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

$(document).ready(function () {
    var oBankCodeContainer = $('#bankaccountbankcode');
    if ( oBankCodeContainer.length > 0 ) {
        fcPayoneCheckDebitType();
        $('select[name="fcpayone_form[bankcountry]"]').on('change', function(){
            fcPayoneCheckDebitType();
        });
        $('input[name="fcpayone_form[bankdatatype]"]').on('change', function(){
            fcPayoneCheckDebitType();
        });
    }
});


function fcPayoneCheckDebitType() {
    var sLang = $('select[name="fcpayone_form[bankcountry]"]').find('option:selected').val();
    if ( sLang != 'DE' ) {
        $('#bankaccountbankcode').hide();
        $('#bankdatatype').hide();
        $('#ibanbic').show();
        $('#bankdatatype1').prop('checked', true);
        $('#bankdatatype1').parents('span').addClass('checked');
        $('#bankdatatype2').parents('span').removeClass('checked');
    } else {
        $('#bankdatatype').show();
        if ( $('input[name="fcpayone_form[bankdatatype]"]').prop('type') == 'radio' ) {
            var iBankType = $('input[name="fcpayone_form[bankdatatype]"]:checked').val();
        } else {
            var iBankType = $('input[name="fcpayone_form[bankdatatype]"]').val();
        }
        if (iBankType == 2) {
            $('#bankaccountbankcode').show();
            $('#ibanbic').hide();
        } else {
            $('#ibanbic').show();
            $('#bankaccountbankcode').hide();
        }
    }
    //console.log('active bank type: '+$('input[name="fcpayone_form[bankdatatype]"]:checked').val());
}
