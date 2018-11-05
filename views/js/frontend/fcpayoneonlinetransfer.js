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

$(document).ready(function () {
    $('.js-payment-bankgroup').hide();
    $('.js-payment-ibanbic').hide();
    var oSelect = $('.js-ot-type');
    fcPayoneCheckOnlineTransferType(oSelect.find('option:selected'));
    oSelect.on('change', function () {
        var oSelectedOption = $(this).find('option:selected');
        fcPayoneCheckOnlineTransferType(oSelectedOption);
    });
});
function fcPayoneCheckOnlineTransferType(oSelectedOption) {
    var sPaymentId = oSelectedOption.data('payment-id');
    if (oSelectedOption.data('show-bankgroup') == true) {
        $('.js-payment-bankgroup').hide();
        $('.js-payment-bankgroup[data-payment-id="' + sPaymentId + '"]').show();
    } else {
        $('.js-payment-bankgroup').hide();
    }

    if (oSelectedOption.data('show-ibanbic') == true) {
        $('.js-payment-ibanbic').show();
    } else {
        $('.js-payment-ibanbic').hide();
    }
}