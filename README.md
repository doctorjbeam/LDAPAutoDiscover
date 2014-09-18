LDAPAutoDiscover
================

Outlook-compatible autodiscover.xml generator for AD domain-connected users.

Requirements
-----

* PHP5, including PHP5-LDAP
* Active Directory environment
* An AD user to bind with for searching
* A web server (Works well with IIS)
* A HTTPS site for greatest compatibility with client discovery
* Ability to add a publicly-resolvable subdomain (autodiscover.fqdn) and set appropriate firewall forwarding rules

Setup
-----

Assumes IIS. 

* Create your website and assign appropriate bindings (autodiscover.fqdn)
    * For best results, create bindings on ports 80 and 443 using a valid SSL certificate
* Place autodiscover.php under the autodiscover folder. The full path should resemble http://autodiscover.domain.com/autodiscover/autodiscover.php
* Create a URL rewrite with the following settings:
    * Name: XML to PHP
    * Requested URL: Matches the pattern
    * Using: Exact match
    * Pattern: autodiscover/autodiscover.xml 
    * Action type: Rewrite
    * Rewrite URL: autodiscover/autodiscover.php
* Edit the $config array within autodiscover.php to reflect your environment/requirements, specifically:
    * Email servers (Gmail is supplied)
    * AD hostname and root DN
    * AD bind user and password
* Profit!

What's the AD integration for?
------------------------------

Using a static XML file with Google Apps doesn't quite do enough - it works, but when Outlook asks for your password it assumes your Google Apps username is part of your email address (eg bob) instead of the full email address (bob@fqdn). 

By using AD we're able to generate on-the-fly a custom XML file for each autodiscover request, which includes the username of the account and the full name of the user. 