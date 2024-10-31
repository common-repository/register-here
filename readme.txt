=== Plugin Name ===
Contributors: Pierre Husted Sigvardsen
Donate link: none
Tags: registration, participants, tilmelding
Requires at least: 3.8 
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Registration of participants for events. 
Option for up to 10 admin-defined fields in registration.
Headers and button-texts can be changed in the settings.

== Description ==

Registration of participants for events. 

The plug-in also provides a list of participants, to show on your site.

In the settings is an option admin-defined fields in registration. There's room for up to 10 custom fields. Only the active fields are shown in the registrationform and list of participants.

The headers and button-texts can be customized.
 
 
There are plans to develop this plug-in further, adding some or all of these functions in registration:
- Agelimits - a participant must be of a certain age when the event starts. 
- Start and end of registation - the registration is only open within specified time-limits. 
- Teams - a participant can join a team or group when registring. These teams are set up in the settings. 
- Edit registration - An option to log in and edit registration. 
- Payment - Options to pay via Paypal, EWire or the like. 


== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add [register_here] in the page where you want the registration form.
1. Add [register_here list] in the page where you want the list of participants.
1. Look through the settings, to set up the rest of the plug-in.


== Frequently Asked Questions ==

= Will the plug-in be available in other languages? =

The plug-in is ready for localization. So, if you want to translate is, just go ahead - and send me the files afterwards.


== Screenshots ==

1. The registration form: register-here--registration-form.png
2. The list of participants: register-here--participants-list.png
3. The settings-panel show the extra fields: register-here--settings-extra-fields.png


== Changelog ==

= 0.3.0 =
* Added a participants list for the admin. With an option to delete participants.
* Cleaned up the header and button-texts a bit.

= 0.2.6 =
* Added email to administrator when recipients register.

= 0.2.5 =
* Added settings to change the headers and button-texts.

= 0.2.4 =
* Now the localization actually works. The plugin is still a work in progress.

= 0.2.3 =
* Added feedback setting - option to send data about installation

= 0.2.1 =
* Brushed up a lot of text

= 0.2.0 =
* Added list of participants.

= 0.1.0 =
* Very first version. Only registration, not list.


== Upgrade Notice ==

= 0.2.0 =
Upgrade to get the list of participants.


