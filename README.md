# Drupal RSVP EVENT site

## Architecture

### Address field instead of device geolocation

Using device geolocation (IP, GPS) was an option for getting the user's address, but I didn't believe it was reliable
enough to apply in this project. Instead, an address field was created with a background geocoding field that transforms
the text addresses into coordinates (the Bing geocoding API was used!). Inserting address into Event nodes works the same way.

The con is that the user must input his address manually. A research would fit well in finding a better option for that.

### Miles between the current Attendee and the Event location

The distance between the current Attendee and the Event location is being calculated through the **Haversine Formula**,
which is commonly used in navigations, providing distance between two points in a sphere.

### Grouping Events by Attendee in the Event attendees full report

**Views Merge Rows** module could have probably be used in this case to group the events by user, but there are issues
that, until now, forces you to patch the core in order get the module working properly. Instead, I decided to create a
custom global view field (EventsAttendedViewsField) to query the nodes where the user ID in the line is referenced in the
Event Attendees field in the Event content type.

## Considerations

Basically only the back-end part of the system is done now (the RSVP Events and general site config modules). I've started the
front-end part, but the time is out already.
The RSVP button form blog is also inserted in the general site config. I would add it to my custom Event content type template.

I was using Trello to help me having control over the tasks:
https://trello.com/invite/b/2MDEAMte/44dab07e64861c86b6f6c4b31362e24e/arctouch-test

### Finding the Attendees report page

The Attendees report page can be found in this path: **admin/config/rsvp_events/attendees_report**
**Administration >> Configuration >> Attendees Report**

## Assumptions

I'm commiting some files that we don't usually do just to make the testing easier. I didn't have time to build a
proper infrastructure using Docker and the powerful Makefile.

I've developed this site using **MySQL 5.7.28**, **PHP 7.3.13**, and **Httpd 2.4.41**, in **Ubuntu 19.04*.*
Also, please run **composer install** on the first time you will be testing the project.
