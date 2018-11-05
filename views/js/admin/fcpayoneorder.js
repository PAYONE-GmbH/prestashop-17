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
    $('.submitPayoneForm').on('submit', function (e) {
        if ( !$(this).hasClass('payone-confirmed') ) {
            e.stopPropagation();
            var blConfirm = confirm($(this).data('payone-confirm'));
            if (blConfirm) {
                $(this).addClass('payone-confirmed');
                $(this).submit();
            }
        }
    });
    $('.js-payone-open-bankdata').on('click', function(e){
        e.preventDefault();
        var oBankData = $('.js-payone-bankdata');
        if ( oBankData.hasClass('js-open') ) {
            oBankData.removeClass('js-open').hide();
        } else {
            oBankData.addClass('js-open').show();
        }
    });
});

