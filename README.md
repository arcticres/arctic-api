arctic-php-api
==============

A PHP based interface to the Arctic Reservations v2 API.

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
* Trips
* Trip types
* Trip pricing levels
* Trip add-ons
* Rental items
* Rental item pricing levels
* Business groups

## Version

Version v0.5 (beta)

The API is approaching a version 1 release. The latest version introduces substantial changes
in directory and naming structure to enable PSR-4 and Composer support.


## Authors

**L. Nathan Perkins**

- <https://github.com/nathanntg>
- <http://www.nathanntg.com>