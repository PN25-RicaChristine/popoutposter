(function () { var require = undefined; var define = undefined; (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var checkoutForm = document.querySelector('form.woocommerce-checkout, form[name="checkout"]');
var previousEmailAddress = '';
var previousDataString;
var formSubmitted = false;
var ajaxurl = typeof mc4wp_ecommerce_cart !== "undefined" ? mc4wp_ecommerce_cart.ajax_url : woocommerce_params.ajax_url;

function isEmailAddressValid(emailAddress) {
  var regex = /\S+@\S+\.\S+/;
  return typeof emailAddress === "string" && emailAddress !== '' && regex.test(emailAddress);
}

function getFieldValue(fieldName) {
  var field = checkoutForm.querySelector("[name=\"".concat(fieldName, "\"]"));

  if (!field) {
    return '';
  }

  return field.value.trim();
}

function sendFormData(async) {
  var data = {
    previous_billing_email: previousEmailAddress,
    billing_email: getFieldValue('billing_email'),
    billing_first_name: getFieldValue('billing_first_name'),
    billing_last_name: getFieldValue('billing_last_name'),
    billing_address_1: getFieldValue('billing_address_1'),
    billing_address_2: getFieldValue('billing_address_2'),
    billing_city: getFieldValue('billing_city'),
    billing_state: getFieldValue('billing_state'),
    billing_postcode: getFieldValue('billing_postcode'),
    billing_country: getFieldValue('billing_country')
  };
  var dataString = JSON.stringify(data); // schedule cart update if email looks valid && data changed.

  if (isEmailAddressValid(data.billing_email) && dataString !== previousDataString) {
    var request = new XMLHttpRequest();
    request.open('POST', ajaxurl + "?action=mc4wp_ecommerce_schedule_cart", async);
    request.setRequestHeader('Content-Type', 'application/json');
    request.send(dataString);
    previousDataString = dataString;
    previousEmailAddress = data.billing_email;
  }
}

if (checkoutForm) {
  checkoutForm.addEventListener('change', function () {
    sendFormData(true);
  });
  checkoutForm.addEventListener('submit', function () {
    formSubmitted = true;
  }); // always send before unloading window, but not if form was submitted

  window.addEventListener('beforeunload', function () {
    if (!formSubmitted) {
      sendFormData(false);
    }
  });
}

},{}]},{},[1]);
 })();