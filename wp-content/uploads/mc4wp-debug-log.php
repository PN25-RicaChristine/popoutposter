<?php exit; ?>
[2020-10-20 15:50:18] ERROR: Form 7041 > Mailchimp API error: 400 Bad Request. Invalid Resource. test@te**.com looks fake or invalid, please enter a real email address.

Request: 
POST https://us2.api.mailchimp.com/3.0/lists/ec7835b04f/members

{"status":"pending","email_address":"test@te**.com","interests":{"6e27eb0b7d":true},"merge_fields":{},"email_type":"html","ip_signup":"125.99.176.141","tags":[]}

Response: 
400 Bad Request
{"type":"http://developer.mailchimp.com/documentation/mailchimp/guides/error-glossary/","title":"Invalid Resource","status":400,"detail":"test@te**.com looks fake or invalid, please enter a real email address.","instance":"329b1f99-07bf-4de2-a8d0-f508e880596c"}
[2020-11-07 08:59:07] ERROR: Form 7041 > Mailchimp API error: 400 Bad Request. Invalid Resource. nrwa****@gm***.com has signed up to a lot of lists very recently; we're not allowing more signups for now

Request: 
POST https://us2.api.mailchimp.com/3.0/lists/ec7835b04f/members

{"status":"pending","email_address":"nrwa****@gm***.com","interests":{"cf95ef6de1":true,"ec88869d1b":true,"342189640c":true,"78b6307fe7":true},"merge_fields":{},"email_type":"html","ip_signup":"45.154.255.71","tags":[]}

Response: 
400 Bad Request
{"type":"http://developer.mailchimp.com/documentation/mailchimp/guides/error-glossary/","title":"Invalid Resource","status":400,"detail":"nrwa****@gm***.com has signed up to a lot of lists very recently; we're not allowing more signups for now","instance":"a0b8fb56-0baa-467f-8f36-aec0d5a8fa8a"}
[2020-11-13 13:34:23] ERROR: Form 7041 > Mailchimp API error: 400 Bad Request. Invalid Resource. lean*******@hi********.com has signed up to a lot of lists very recently; we're not allowing more signups for now

Request: 
POST https://us2.api.mailchimp.com/3.0/lists/ec7835b04f/members

{"status":"pending","email_address":"lean*******@hi********.com","interests":{"cf95ef6de1":true,"ec88869d1b":true,"342189640c":true,"78b6307fe7":true},"merge_fields":{},"email_type":"html","ip_signup":"45.128.133.242","tags":[]}

Response: 
400 Bad Request
{"type":"http://developer.mailchimp.com/documentation/mailchimp/guides/error-glossary/","title":"Invalid Resource","status":400,"detail":"lean*******@hi********.com has signed up to a lot of lists very recently; we're not allowing more signups for now","instance":"408b040d-d03d-41d8-a94d-0fe9b4445ab6"}
[2020-11-21 19:17:57] ERROR: Form 7041 > Mailchimp API error: 400 Bad Request. Invalid Resource. wpat**@ao*.com has signed up to a lot of lists very recently; we're not allowing more signups for now

Request: 
POST https://us2.api.mailchimp.com/3.0/lists/ec7835b04f/members

{"status":"pending","email_address":"wpat**@ao*.com","interests":{"cf95ef6de1":true,"ec88869d1b":true,"342189640c":true,"78b6307fe7":true},"merge_fields":{},"email_type":"html","ip_signup":"104.244.72.94","tags":[]}

Response: 
400 Bad Request
{"type":"http://developer.mailchimp.com/documentation/mailchimp/guides/error-glossary/","title":"Invalid Resource","status":400,"detail":"wpat**@ao*.com has signed up to a lot of lists very recently; we're not allowing more signups for now","instance":"13c3ba1e-138f-41e2-b4a6-419e113077cd"}
