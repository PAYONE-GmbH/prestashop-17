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
 * @copyright 2003 - 2018 BS PAYONE GmbH
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      http://www.payone.de
 */
function fcPayoneSetErrorMessage(sMessage) {
    var oAlertBox = $('.js-payone-alert-box');
    oAlertBox.html(sMessage).show();
}

$(document).ready(function () {
    var oValidationContainer = $('.js-payone-validate');
    if ( oValidationContainer.length > 0 ) {
        var oValidationForm = oValidationContainer.parents('form');
        oValidationForm.find('.js-payone-payment-submit').on('click', function (e) {
            e.preventDefault();
            var sFormData = oValidationForm.serialize();
            sFormData += '&paymentid=' + oValidationContainer.data('payone-payment-id');
            $.ajax({
                method: "POST",
                url: oValidationContainer.data('payone-validation-url'),
                data: sFormData,
                success: function (data) {
                    var blHasError = false;
                    try{
                        var oResponse = $.parseJSON(data);
                    } catch(e) {
                        fcPayoneSetErrorMessage(e.message);
                        blHasError = true;
                    }
                    if ( !blHasError && oResponse ) {
                        if (oResponse.replacements) {
                            $.each(oResponse.replacements, function (sKey, sValue) {
                                oValidationForm.find('input[name="fcpayone_form[' + sKey + ']"]').val(sValue);
                            });
                        }
                        if (oResponse.errorMessages) {
                            var sMessage = '<ol>';
                            $.each(oResponse.errorMessages, function (key, sValue) {
                                sMessage += '<li>'+sValue+'</li>';
                            });
                            sMessage += '</ol>';
                            fcPayoneSetErrorMessage(sMessage);
                            blHasError = true;
                        }
                    }
                    
                    if ( !blHasError ) {
                        oValidationForm.submit();
                    }
                }
            });
        });
    }
});
