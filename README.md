LDAPAutoDiscover
================

Outlook-compatible autodiscover.xml generator for AD domain-connected users.

Requirements
-----

PHP5, including PHP5-LDAP
Active Directory environment
An AD user to bind with for searching
A web server (Works well with IIS)
A HTTPS site for greatest compatibility with client discovery

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