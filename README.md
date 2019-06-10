# CalDAV
CalDAV event parsing using the SimpleCalDAV PHP library

## Implementation
The core of this project is to replicate the behaviour of the existing DEI employee schedule but using the events created in each employee work calendar instead of events created using the old calendar system. In normal calendars, events are displayed in rows inside days which are displayed as columns in week view. We want to parse everyone's schedules and display them in days by ROW instead of column for the entire week. This application is solely for viewing schedule events, there will no longer be functionality to add events to the company schedule in this view, rather users should use their email calendar and add events that way.

No calendar-specific library has been used to create the UI, this has been done using the Bootstrap CSS framework.

### Original Functionality:
- Days displayed as rows in week-view, current day is highlighted
  - Day and Date are displayed
- Events are ordered by start time then end time, includes user initials and summary of event
- User can choose to view by day, week, or month, can jump forward and backwards 
- User can filter by employee

## SimpleCalDAV
This is a CalDAV-Client php library: https://github.com/wvrzel/simpleCalDAV
added as a submodule for this project
