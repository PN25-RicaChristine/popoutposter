!function(e){var r={};function t(o){if(r[o])return r[o].exports;var n=r[o]={i:o,l:!1,exports:{}};return e[o].call(n.exports,n,n.exports,t),n.l=!0,n.exports}t.m=e,t.c=r,t.d=function(e,r,o){t.o(e,r)||Object.defineProperty(e,r,{enumerable:!0,get:o})},t.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},t.t=function(e,r){if(1&r&&(e=t(e)),8&r)return e;if(4&r&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(t.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&r&&"string"!=typeof e)for(var n in e)t.d(o,n,function(r){return e[r]}.bind(null,n));return o},t.n=function(e){var r=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(r,"a",r),r},t.o=function(e,r){return Object.prototype.hasOwnProperty.call(e,r)},t.p="",t(t.s=21)}({0:function(e,r,t){},21:function(e,r,t){"use strict";t.r(r);t(0);!function(){var e=document.querySelectorAll(".wc-block-grid");if(e)for(var r=0;r<e.length;r++){e[r].classList.add("block-row","block-row-separator");var t=e[r].querySelectorAll(".wc-block-grid__product-image img");if(t)for(var o=0;o<t.length;o++)t[o].classList.add("border-frontend");var n=e[r].querySelector(".wc-block-grid__products");n&&n.classList.add("container");var l=e[r].querySelectorAll(".wp-block-button a");if(l)for(var c=0;c<l.length;c++)l[c].classList.add("button"),l[c].classList.remove("wp-block-button__link")}}()}});