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
 * @category  Payone
 * @package   fcpayone
 * @author    patworx multimedia GmbH <service@patworx.de>
 * @copyright 2003 - 2018 BS PAYONE GmbH
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      http://www.payone.de
 */
var request = {};
var config = {
    fields: {
        cardpan: {
            selector: "cardpan", // put name of your div-container here
            type: "text", // text (default), password, tel
            style: "font-size: 1em; border: 1px solid #d6d4d4;",
            iframe: {
                width: "100%"
            }
        },
        cardexpiremonth: {
            selector: "cardexpiremonth", // put name of your div-container here
            type: "select", // select(default), text, password, tel
            size: "2",
            maxlength: "2",
            iframe: {
                width: "100%"
            }
        },
        cardexpireyear: {
            selector: "cardexpireyear", // put name of your div-container here
            type: "select", // select(default), text, password, tel
            iframe: {
                width: "100%"
            }
        }
    },
    defaultStyle: {
        input: "font-size: 1em; border: 1px solid #d6d4d4; width: 175px;",
        select: "font-size: 1em; border: 1px solid #d6d4d4;",
        iframe: {
            height: "33px",
            width: "180px"
        }
    }
};
var hasCvC = false;

/**
 * Adds pseudocard pan and truncatedcardpan to form
 * or show error message from response
 * @param oResponse
 */
function fcPayoneCheckCallback(oResponse) {
    if (oResponse.status === "VALID" && fcPayoneValidateCardExpireDate(oResponse)) {
        var oForm = $('.js-payone-payment-submit').parents('form');
        oForm.find('input[name="fcpayone_form[pseudocardpan]"]').val(oResponse.pseudocardpan);
        oForm.find('input[name="fcpayone_form[truncatedcardpan]"]').val(oResponse.truncatedcardpan);
        oForm.submit();
    } else if( oResponse.status === "ERROR" ) {
        var sMessage = oResponse.errorcode+': '+oResponse.errormessage;
        fcPayoneSetErrorMessage(sMessage);
    }
}

/**
 * Set sub payment to form
 *
 * @param oCardTypeSelect
 */
function fcPayoneSetSubPayment(oCardTypeSelect) {
    var sSubPaymentId = oCardTypeSelect.find('option:selected').data('payone-subpayment-id');
    $('input[name="payone_payment_sub"]').val(sSubPaymentId);
}

/**
 * validates the expiredate given in response
 *
 * @param object response
 * @returns bool
 */
function fcPayoneValidateCardExpireDate(response) {

    var oCurrentDate = new Date();

    // current year month string has to be set into format YYMM
    var aMonths = new Array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
    var sMonth = aMonths[oCurrentDate.getMonth()];

    var sYear = oCurrentDate.getFullYear().toString(); // need to use full year because getYear() is broken due to Y2K-Bug
    sYear = sYear.substr(2,4);

    var sCurrentYearMonth = sYear + sMonth;
    var sResponseYearMonth = response.cardexpiredate.toString();

    var blValid = false;
    if (sResponseYearMonth > sCurrentYearMonth) {
        blValid = true;
    } else {
        var oConfigObject = $('.js-payone-request-config');
        fcPayoneSetErrorMessage(oConfigObject.data('validation-expiredate-error'));
    }

    return blValid;
}

/**
 * Check if csv should be shown
 */
function fcPayoneCheckForCvC() {
    if ( $('#cardcvc2').length > 0 ) {
        hasCvC = true;
        var cardcvc2 =  {
            selector: "cardcvc2", // put name of your div-container here
            type: "password", // select(default), text, password, tel
            style: "font-size: 1em; border: 1px solid #d6d4d4;",
            size: "4",
            length: { "V": 3, "M": 3, "A": 4, "D": 3, "J": 3, "O": 3, "P": 3, "U": 3 },
            maxlength: "4",
            iframe: {
                width: "100%"
            }
        };

        config.fields['cardcvc2'] = cardcvc2;
    }
}

/**
 * Check if there's specific CSS
 */
function fcPayoneCheckForSpecificCSS() {
    var possibleFormFields = [ 'cardcvc2', 'cardpan', 'cardexpiremonth', 'cardexpireyear' ];
    $.each( possibleFormFields, function( index, formfield ){
        fcPayoneSetCSSConfig(formfield);
    });
}

function fcPayoneSetCSSConfig(formfield) {
    if ( $('#' + formfield).length > 0 ) {
        if (typeof $('#' + formfield).data('css') !== "undefined") {
            config.fields[formfield]['style'] = $('#' + formfield).data('css');
        }
        if (typeof $('#' + formfield).data('iframe-height') !== "undefined") {
            config.fields[formfield]['iframe']['height'] = $('#' + formfield).data('iframe-height');
        }
    }
}

$(document).ready(function () {

    fcPayoneCheckForCvC();
    fcPayoneCheckForSpecificCSS();

    var oForm = $('.js-payone-payment-submit').parents('form');
    oForm.append('<input type="hidden" name="fcpayone_form[pseudocardpan]" value="">');
    oForm.append('<input type="hidden" name="fcpayone_form[truncatedcardpan]" value="">');

    var oConfigObject = $('.js-payone-request-config');
    var sLang = oConfigObject.data('request-lang');
    config.language = Payone.ClientApi.Language[sLang]; // Language to display error-messages

    var sRequest = oConfigObject.data('request');
    var aRequest = sRequest.split('|');
    $.each(aRequest, function (iKey, sParamValue) {
        if ( sParamValue ) {
            var aParam = sParamValue.split('=');
            request[aParam[0]] = aParam[1];
        }
    });
    //console.log(request);
    var oIframe = new Payone.ClientApi.HostedIFrames(config, request);
    var oCardType = $('#cardtype');
    oIframe.setCardType(oCardType.val());
    fcPayoneSetSubPayment(oCardType);
    oCardType.on('change', function () {
        oIframe.setCardType($(this).val());
        fcPayoneSetSubPayment($(this));
    });
    setTimeout(function() { $('#cardtype').trigger('change'); }, 1000);
    $('.js-payone-payment-submit').on('click', function (e) {
        e.preventDefault();
        if (oIframe.isComplete()) {
            if ((hasCvC && oIframe.isCvcComplete()) || !hasCvC) {
                oIframe.creditCardCheck('fcPayoneCheckCallback'); // Perform "CreditCardCheck" to create and get a
                // PseudoCardPan; then call your function "checkCallback"
            }
        } else {
            fcPayoneSetErrorMessage(oConfigObject.data('validation-fields-error'));
        }
    });
});
