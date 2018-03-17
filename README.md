arctic-api
==========

PHP and JavaScript based interfaces to the Arctic Reservations v2 API.

PHP
---

These classes make it easy to access data from Arctic Reservations and make changes, all via a
PHP object oriented interface. The files include support for OAuth 2.0 authentication, as well
as access to the REST API to browse, create (insert), read (load), update and delete objects.

Initial classes are provided for common API endpoints, comments provide documentation on common
fields (although fields may vary based on system configuration) and examples show basic usage.

To use these functions, you must have both client credentials and API credentials. If you are
an Arctic Reservations customer, please contact support for this information.

Support for:

* Persons
* Person metadata - addresses, phone numbers, email addresses, notes
* Inquiries
* Invoices
* Invoice data - groups, items, transactions
* Activities
* Reservations
* Reservation members (guests)
* Trips
* Trip types
* Trip pricing levels
* Trip add-ons
* Guides
* Guide schedule entries
* Rentals
* Rental includes
* Rental items
* Rental item pricing levels
* Business groups

Requires PHP 5.3+ (supports PHP 7.0).

JavaScript
----------

The JavaScript reservation widget makes it easy to build custom replacements to the first step
of the online booking process, including the ability to search and filter trips, as well as to
display the initial booking form to get guest and add-on information. The JavaScript
functionality relies on an open (unauthenticated) API for online reservations.

### Version

Version v0.8 (beta)

Version **0.8** expands the person model to include a new method (insert or update),
adds guide and guide schedule models, improves the way references are implemented, 
as well as support for both pre-populated or on-demand single references.

Version **0.7** has added a few new models (activities, rentals and reservations), added more
methods to existing models (such as price and availability data for rental items) and
substantially improved error handling by providing different exception types based on
the HTTP status code.

Version **0.6** introduces the JavaScript reservation
widget, as well as a few example implementations.

The repository has also been renamed to remove PHP from the name.

### Authors

**L. Nathan Perkins**

- <https://github.com/nathanntg>
- <http://www.nathanntg.com>